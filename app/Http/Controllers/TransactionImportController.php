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
        try {
            $this->authorizeAdmin($book);

            $request->validate([
                'csv_file' => 'required|file|max:10240',
            ]);

            $file = $request->file('csv_file');
            $allRows = $this->readCsvRows($file->getRealPath());

            if (empty($allRows)) {
                return redirect()->back()->with('error', 'The uploaded CSV file is empty or unreadable.');
            }

            // Extract header row
            $rawHeader = array_shift($allRows);
            $headerMap = [];
            foreach ($rawHeader as $index => $col) {
                $normalized = trim(strtolower(preg_replace('/[^a-zA-Z0-9_ ]/', '', $col)));
                $headerMap[$normalized] = $index;
            }

            // Flexible column resolution helper
            $findCol = function (array $candidates) use ($headerMap) {
                foreach ($candidates as $candidate) {
                    if (isset($headerMap[$candidate])) {
                        return $headerMap[$candidate];
                    }
                }
                return null;
            };

            $dateCol = $findCol(['date', 'transaction date', 'entry date']);
            $timeCol = $findCol(['time', 'transaction time']);
            $remarkCol = $findCol(['remark', 'remarks', 'description', 'note', 'details']);
            $partyCol = $findCol(['party', 'contact', 'name', 'customer', 'vendor']);
            $categoryCol = $findCol(['category', 'type']);
            $modeCol = $findCol(['mode', 'payment mode', 'payment method']);
            $entryByCol = $findCol(['entry by', 'created by', 'user', 'entryby']);
            $cashInCol = $findCol(['cash in', 'cashin', 'in', 'income', 'credit', 'amount in', 'cash_in']);
            $cashOutCol = $findCol(['cash out', 'cashout', 'out', 'expense', 'debit', 'amount out', 'cash_out']);

            if ($dateCol === null || ($cashInCol === null && $cashOutCol === null)) {
                return redirect()->back()->with('error', 'CSV missing required headers. Found headers: "' . implode(', ', $rawHeader) . '". Required: Date, Cash In / Cash Out.');
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

            foreach ($allRows as $data) {
                $rowIndex++;
                // Skip empty lines
                if (empty(array_filter($data, 'strlen'))) {
                    continue;
                }

                $totalRows++;

                $rawDate = $data[$dateCol] ?? null;
                $rawTime = $timeCol !== null ? ($data[$timeCol] ?? '') : '';
                $remark = $remarkCol !== null ? trim($data[$remarkCol] ?? '') : '';
                $party = $partyCol !== null ? trim($data[$partyCol] ?? '') : null;
                $categoryName = $categoryCol !== null ? trim($data[$categoryCol] ?? '') : null;
                $mode = $modeCol !== null ? trim(strtolower($data[$modeCol] ?? 'cash')) : 'cash';
                $entryBy = $entryByCol !== null ? trim($data[$entryByCol] ?? '') : null;

                $cashIn = $cashInCol !== null ? $this->cleanAmount($data[$cashInCol] ?? 0) : 0;
                $cashOut = $cashOutCol !== null ? $this->cleanAmount($data[$cashOutCol] ?? 0) : 0;

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
                $parsedDateTime = $this->parseDateTime($rawDate, $rawTime);
                $status = 'ready';
                $errorMessage = null;

                if (!$type || $amount <= 0) {
                    $status = 'invalid';
                    $errorMessage = 'Missing Cash In or Cash Out amount.';
                    $invalidCount++;
                } elseif (!$parsedDateTime) {
                    $parsedDateTime = now()->format('Y-m-d H:i:s');
                    $status = 'ready';
                    $errorMessage = 'Date auto-set to current date & time (original text: "' . trim($rawDate . ' ' . $rawTime) . '")';
                    $readyCount++;
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

        } catch (\Throwable $e) {
            Log::error('CSV Preview Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error parsing CSV file: ' . $e->getMessage());
        }
    }

    /**
     * Store previewed transactions into database inside a DB transaction (Admin Only).
     */
    public function store(Request $request, Book $book)
    {
        try {
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

        } catch (\Throwable $e) {
            Log::error('CSV Store Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error storing CSV transactions: ' . $e->getMessage());
        }
    }

    /**
     * Read CSV rows safely supporting UTF-8 BOM, auto-delimiters (comma, semicolon, tab, pipe).
     */
    private function readCsvRows($filePath)
    {
        $content = file_get_contents($filePath);
        if (!$content) return [];

        // Remove UTF-8 BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Auto-detect delimiter
        $firstLine = strtok($content, "\r\n");
        $delimiters = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        foreach ($delimiters as $d => $count) {
            $delimiters[$d] = substr_count($firstLine, $d);
        }
        arsort($delimiters);
        $delimiter = key($delimiters);
        if ($delimiters[$delimiter] === 0) {
            $delimiter = ',';
        }

        // Split by lines and parse
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
        $rows = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $rows[] = str_getcsv($line, $delimiter);
        }

        return $rows;
    }

    /**
     * Parse raw date and time strings with fallback format matching.
     */
    private function parseDateTime($rawDate, $rawTime)
    {
        if (empty($rawDate) && empty($rawTime)) return null;

        $rawDate = trim((string)$rawDate);
        $rawTime = trim((string)$rawTime);

        // Normalize non-breaking spaces and invisible characters
        $rawDate = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $rawDate);
        $rawDate = trim(preg_replace('/\s+/', ' ', $rawDate));

        $rawTime = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $rawTime);
        $rawTime = trim(preg_replace('/\s+/', ' ', $rawTime));

        if (empty($rawDate) && !empty($rawTime)) {
            $rawDate = now()->format('Y-m-d');
        }

        // Handle Excel numeric serial date (e.g. 45504 or 45504.6041666667)
        if (is_numeric($rawDate)) {
            $num = (float)$rawDate;
            if ($num > 10000 && $num < 90000) {
                try {
                    if (class_exists('\PhpOffice\PhpSpreadsheet\Shared\Date')) {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($num);
                        return $dateObj->format('Y-m-d H:i:s');
                    } else {
                        $base = strtotime('1899-12-30');
                        $seconds = (int)round($num * 86400);
                        return date('Y-m-d H:i:s', $base + $seconds);
                    }
                } catch (\Exception $e) {}
            }
        }

        // Combine date and time string
        $dateStr = $rawDate;
        if (!empty($rawTime)) {
            $dateStr .= ' ' . $rawTime;
        }

        // Clean up ISO T/Z characters, quotes, commas
        $cleanStr = str_replace(['T', 'Z', '"', "'"], [' ', '', '', ''], $dateStr);
        $cleanStr = trim(preg_replace('/\s+/', ' ', $cleanStr));

        // 1. Try Carbon::parse directly
        try {
            return Carbon::parse($cleanStr)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {}

        // 2. Try PHP strtotime
        $timestamp = strtotime($cleanStr);
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        // 3. Exhaustive multi-format parsing
        $formats = [
            'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d g:i A', 'Y-m-d h:i A', 'Y-m-d',
            'Y/m/d H:i:s', 'Y/m/d H:i', 'Y/m/d g:i A', 'Y/m/d h:i A', 'Y/m/d',
            'Y.m.d H:i:s', 'Y.m.d H:i', 'Y.m.d',
            'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y g:i A', 'd/m/Y h:i A', 'd/m/Y',
            'm/d/Y H:i:s', 'm/d/Y H:i', 'm/d/Y g:i A', 'm/d/Y h:i A', 'm/d/Y',
            'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y g:i A', 'd-m-Y h:i A', 'd-m-Y',
            'd.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y',
            'd M Y H:i:s', 'd M Y H:i', 'd M Y', 'd F Y', 'M d, Y', 'Y-M-d',
            'd/m/y H:i:s', 'd/m/y H:i', 'd/m/y', 'm/d/y', 'd-m-y'
        ];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $cleanStr)->format('Y-m-d H:i:s');
            } catch (\Exception $ex) {
                continue;
            }
        }

        // 4. Regex extraction for d/m/Y or Y/m/d with separators (., /, -)
        if (preg_match('/^(\d{1,4})[\.\/\-](\d{1,2})[\.\/\-](\d{1,4})(?:\s+(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?(?:\s*(AM|PM))?)?$/i', $cleanStr, $matches)) {
            $p1 = (int)$matches[1];
            $p2 = (int)$matches[2];
            $p3 = (int)$matches[3];
            $hour = isset($matches[4]) ? (int)$matches[4] : 12;
            $min  = isset($matches[5]) ? (int)$matches[5] : 0;
            $sec  = isset($matches[6]) ? (int)$matches[6] : 0;
            $ampm = isset($matches[7]) ? strtoupper($matches[7]) : '';

            if ($ampm === 'PM' && $hour < 12) $hour += 12;
            if ($ampm === 'AM' && $hour == 12) $hour = 0;

            if ($p1 > 1000) {
                $year = $p1; $month = $p2; $day = $p3;
            } elseif ($p3 > 1000) {
                $year = $p3;
                if ($p1 > 12) {
                    $day = $p1; $month = $p2;
                } elseif ($p2 > 12) {
                    $day = $p2; $month = $p1;
                } else {
                    $day = $p1; $month = $p2;
                }
            } else {
                $year = 2000 + $p3;
                $day = $p1; $month = $p2;
            }

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $min, $sec);
            }
        }

        return null;
    }

    /**
     * Clean numeric currency values safely.
     */
    private function cleanAmount($val)
    {
        if (empty($val)) return 0;
        $val = trim((string)$val);

        // If string contains both comma and dot, e.g. 1,500.00
        if (strpos($val, '.') !== false && strpos($val, ',') !== false) {
            $val = str_replace(',', '', $val);
        } elseif (strpos($val, ',') !== false && strpos($val, '.') === false) {
            // European format e.g. 1500,00 -> change comma to dot
            if (strlen(substr(strrchr($val, ","), 1)) == 2) {
                $val = str_replace(',', '.', $val);
            } else {
                $val = str_replace(',', '', $val);
            }
        }

        $cleaned = preg_replace('/[^\d.]/', '', $val);
        return is_numeric($cleaned) ? (float)$cleaned : 0;
    }

    /**
     * Authorize Admin-only access for imports.
     */
    private function authorizeAdmin(Book $book)
    {
        $user = Auth::user();
        $role = $user->getBookRole($book);

        if (!in_array($role, ['primary_admin', 'admin'])) {
            $businessUser = DB::table('business_user')
                ->where('business_id', $book->business_id)
                ->where('user_id', $user->id)
                ->first();

            $bizRole = $businessUser->role ?? $user->role;

            if (!in_array($bizRole, ['primary_admin', 'admin'])) {
                abort(403, 'Unauthorized. CSV import is restricted to Primary Admins and Admins.');
            }
        }
    }
}
