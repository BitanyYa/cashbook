<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\User;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransactionImportController extends Controller
{
    /**
     * Show the CSV upload form (Admin Only).
     */
    public function create(Book $book)
    {
        $this->authorizeAdmin($book);
        return view('transactions.import', compact('book'));
    }

    /**
     * Process uploaded CSV file and display interactive preview (Admin Only).
     */
    public function preview(Request $request, Book $book)
    {
        $this->authorizeAdmin($book);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'The uploaded CSV file is empty.');
        }

        // Normalize header columns
        $headerMap = [];
        foreach ($header as $index => $col) {
            $normalized = trim(strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $col)));
            $headerMap[$normalized] = $index;
        }

        // Check essential headers
        if (!isset($headerMap['date']) || (!isset($headerMap['cash in']) && !isset($headerMap['cash out']))) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV header missing required columns: Date, Cash In / Cash Out.');
        }

        $existingCategories = Category::where('business_id', $book->business_id)
            ->pluck('id', 'name')
            ->toArray();

        $allUsers = User::all();

        $rows = [];
        $rowIndex = 1;
        $totalRows = 0;
        $readyCount = 0;
        $duplicateCount = 0;
        $invalidCount = 0;
        $newCategoriesToCreate = [];

        while (($data = fgetcsv($handle)) !== false) {
            $rowIndex++;
            // Skip completely empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            $totalRows++;

            $rawDate = $data[$headerMap['date']] ?? null;
            $rawTime = isset($headerMap['time']) ? ($data[$headerMap['time']] ?? '00:00') : '00:00';
            $remark = isset($headerMap['remark']) ? trim($data[$headerMap['remark']]) : '';
            $party = isset($headerMap['party']) ? trim($data[$headerMap['party']]) : null;
            $categoryName = isset($headerMap['category']) ? trim($data[$headerMap['category']]) : null;
            $mode = isset($headerMap['mode']) ? trim(strtolower($data[$headerMap['mode']])) : 'cash';
            $entryBy = isset($headerMap['entry by']) ? trim($data[$headerMap['entry by']]) : null;

            $cashIn = isset($headerMap['cash in']) ? $this->cleanAmount($data[$headerMap['cash in']]) : 0;
            $cashOut = isset($headerMap['cash out']) ? $this->cleanAmount($data[$headerMap['cash out']]) : 0;

            // Determine Type & Amount
            $type = null;
            $amount = 0;
            if ($cashIn > 0) {
                $type = 'income';
                $amount = $cashIn;
            } elseif ($cashOut > 0) {
                $type = 'expense';
                $amount = $cashOut;
            }

            // Parse Date & Time
            $parsedDateTime = null;
            $status = 'ready';
            $errorMessage = null;

            if (!$type || $amount <= 0) {
                $status = 'invalid';
                $errorMessage = 'Missing Cash In or Cash Out amount.';
                $invalidCount++;
            } else {
                try {
                    $dateTimeString = trim($rawDate . ' ' . $rawTime);
                    $parsedDateTime = Carbon::parse($dateTimeString)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $status = 'invalid';
                    $errorMessage = 'Invalid Date/Time format: ' . $rawDate;
                    $invalidCount++;
                }
            }

            // Match Entry By User
            $assignedUserId = Auth::id();
            $assignedUserName = Auth::user()->name;
            if (!empty($entryBy)) {
                $matchedUser = $allUsers->first(function ($u) use ($entryBy) {
                    return strcasecmp($u->name, $entryBy) === 0 || strcasecmp($u->email, $entryBy) === 0;
                });
                if ($matchedUser) {
                    $assignedUserId = $matchedUser->id;
                    $assignedUserName = $matchedUser->name;
                }
            }

            // Category check
            $isNewCategory = false;
            if (!empty($categoryName) && !isset($existingCategories[$categoryName])) {
                $isNewCategory = true;
                if (!in_array($categoryName, $newCategoriesToCreate)) {
                    $newCategoriesToCreate[] = $categoryName;
                }
            }

            // Duplicate Detection
            $isDuplicate = false;
            if ($status === 'ready' && $parsedDateTime) {
                $isDuplicate = Transaction::where('book_id', $book->id)
                    ->where('transaction_date', $parsedDateTime)
                    ->where('amount', $amount)
                    ->where('type', $type)
                    ->where('description', $remark)
                    ->exists();

                if ($isDuplicate) {
                    $status = 'duplicate';
                    $duplicateCount++;
                } else {
                    $readyCount++;
                }
            }

            $rows[] = [
                'row_index' => $rowIndex,
                'raw_date' => $rawDate,
                'raw_time' => $rawTime,
                'transaction_date' => $parsedDateTime,
                'type' => $type,
                'amount' => $amount,
                'remark' => $remark,
                'party' => $party,
                'category' => $categoryName,
                'is_new_category' => $isNewCategory,
                'mode' => !empty($mode) ? $mode : 'cash',
                'entry_by' => $entryBy,
                'user_id' => $assignedUserId,
                'user_name' => $assignedUserName,
                'status' => $status,
                'error_message' => $errorMessage,
            ];
        }

        fclose($handle);

        session(['import_rows_' . $book->id => $rows]);

        $summary = [
            'total' => $totalRows,
            'ready' => $readyCount,
            'duplicates' => $duplicateCount,
            'invalid' => $invalidCount,
            'new_categories' => count($newCategoriesToCreate),
            'new_category_names' => $newCategoriesToCreate,
        ];

        return view('transactions.preview', compact('book', 'rows', 'summary'));
    }

    /**
     * Store previewed transactions into database inside a DB transaction (Admin Only).
     */
    public function store(Request $request, Book $book)
    {
        $this->authorizeAdmin($book);

        $sessionKey = 'import_rows_' . $book->id;
        $rows = session($sessionKey);

        if (!$rows || empty($rows)) {
            return redirect()->route('transactions.import.create', $book)
                ->with('error', 'Import session expired. Please upload your CSV file again.');
        }

        $importedCount = 0;
        $skippedDuplicates = 0;
        $newCategoriesCount = 0;
        $errorCount = 0;
        $createdCategoryMap = [];

        DB::transaction(function () use ($book, $rows, &$importedCount, &$skippedDuplicates, &$newCategoriesCount, &$errorCount, &$createdCategoryMap) {
            foreach ($rows as $row) {
                if ($row['status'] === 'duplicate') {
                    $skippedDuplicates++;
                    continue;
                }

                if ($row['status'] === 'invalid') {
                    $errorCount++;
                    continue;
                }

                // Handle Category Creation
                $categoryId = null;
                if (!empty($row['category'])) {
                    $categoryName = $row['category'];
                    if (!isset($createdCategoryMap[$categoryName])) {
                        $cat = Category::firstOrCreate(
                            ['name' => $categoryName, 'business_id' => $book->business_id],
                            ['type' => $row['type']]
                        );
                        if ($cat->wasRecentlyCreated) {
                            $newCategoriesCount++;
                        }
                        $createdCategoryMap[$categoryName] = $cat->id;
                    }
                    $categoryId = $createdCategoryMap[$categoryName];
                }

                // Create Transaction
                $transaction = Transaction::create([
                    'business_id' => $book->business_id,
                    'book_id' => $book->id,
                    'user_id' => $row['user_id'] ?: Auth::id(),
                    'transaction_date' => $row['transaction_date'],
                    'type' => $row['type'],
                    'status' => 'approved',
                    'amount' => $row['amount'],
                    'description' => $row['remark'],
                    'contact_name' => $row['party'],
                    'category_id' => $categoryId,
                    'mode' => strtolower($row['mode'] ?: 'cash'),
                ]);

                // Log Activity
                ActivityLog::create([
                    'action' => 'transaction.imported',
                    'user_id' => Auth::id(),
                    'business_id' => $book->business_id,
                    'subject_type' => Transaction::class,
                    'subject_id' => $transaction->id,
                    'details' => [
                        'amount' => $transaction->amount,
                        'type' => $row['type'],
                        'book' => $book->name,
                    ],
                ]);

                $importedCount++;
            }

            $book->touch();
        });

        // Clear session key
        session()->forget($sessionKey);

        $notification = [
            'imported' => $importedCount,
            'duplicates' => $skippedDuplicates,
            'new_categories' => $newCategoriesCount,
            'errors' => $errorCount,
        ];

        session()->flash('import_summary', $notification);

        return redirect()->route('books.show', $book)->with('success', "Import complete: {$importedCount} imported, {$skippedDuplicates} skipped duplicates.");
    }

    /**
     * Clean numeric currency values.
     */
    private function cleanAmount($val)
    {
        if (empty($val)) return 0;
        $cleaned = preg_replace('/[^\d.]/', '', (string)$val);
        return is_numeric($cleaned) ? (float)$cleaned : 0;
    }

    /**
     * Authorize Admin-only access for imports.
     */
    private function authorizeAdmin(Book $book)
    {
        $user = Auth::user();
        $this->authorize('update', $book);

        // Check if user is primary_admin or admin in business
        $businessUser = DB::table('business_user')
            ->where('business_id', $book->business_id)
            ->where('user_id', $user->id)
            ->first();

        $role = $businessUser->role ?? $user->role;

        if (!in_array($role, ['primary_admin', 'admin'])) {
            abort(403, 'Unauthorized. CSV import is restricted to Primary Admins and Admins.');
        }
    }
}
