<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Business;
use App\Models\Category;
use App\Models\ActivityLog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Notifications\TransactionApproved;
use App\Notifications\TransactionRejected;
use App\Notifications\TransactionSubmitted;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();
        $role = $user->getBusinessRole($business);

        $query = Transaction::where('business_id', $business->id)->with(['book','category','user']);

        if ($role === 'employee') {
            $assignedBookIds = $user->belongsToMany(Book::class, 'book_user')->pluck('books.id');
            $query->whereIn('book_id', $assignedBookIds);
        }

        // Filter by book
        if ($request->filled('book')) {
            $bookId = (int) $request->get('book');
            $query->where('book_id', $bookId);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by search term
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderByDesc('transaction_date')->paginate(15)->appends($request->query());
        $books = Book::where('business_id', $business->id)->get();

        return view('transactions.index', compact('transactions', 'books'));
    }

    public function create(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);

        // Get books where user can add transactions (exclude employee-only access)
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            // Primary admins and admins can add transactions to any book
            $books = Book::where('business_id', $business->id)->get();
        } else {
            // For employees, only include books where they have book-level access
            $bookIds = $user->books()
                ->where('business_id', $business->id)
                ->wherePivotIn('role', ['primary_admin', 'admin', 'employee'])
                ->pluck('books.id');

            $books = Book::where('business_id', $business->id)
                ->whereIn('id', $bookIds)
                ->get();
        }

        // If no books available for transaction creation, show error
        if ($books->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add transactions to any books.'
                ], 403);
            }

            abort(403, 'You do not have permission to add transactions to any books.');
        }

        $categories = Category::where('business_id', $business->id)->get();
        return view('transactions.create', compact('books','categories'));
    }

    public function store(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);

        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'category_id' => 'nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'mode' => 'nullable|string|max:50',
            'type' => 'required|in:income,expense',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx|max:102400',
            'receipts' => 'nullable|array',
            'receipts.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx|max:102400',
        ]);

        // If new category is provided, create or find it
        if (!empty($data['new_category'])) {
            $category = Category::firstOrCreate(
                ['name' => $data['new_category'], 'business_id' => $business->id],
                ['type' => $data['type']]
            );
            $data['category_id'] = $category->id;
        }

        // Check book access and get user's role in this specific book
        $book = Book::findOrFail($data['book_id']);

        // Ensure book belongs to the current business
        abort_unless($book->business_id === $business->id, 404);

        // Determine user's access and role for this book
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            // Primary admins and admins always have primary_admin-level access to all books
            $bookRole = 'primary_admin';
            $hasAccess = true;
        } else {
            // For employees, check their specific role in this book
            $bookUser = $user->books()->where('books.id', $data['book_id'])->first();

            if (!$bookUser) {
                abort(403, 'You do not have access to this book');
            }

            $bookRole = $bookUser->pivot->role;
            $hasAccess = true;
        }

        // Check permissions based on book role
        if ($bookRole === 'employee') {
            // Employees may still create transactions, but their status is pending
        }

        // Set transaction status to approved so entries appear immediately in the book
        $status = 'approved';

        $transaction = new Transaction([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'book_id' => $data['book_id'],
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'type' => $data['type'],
            'mode' => $data['mode'] ?? null,
            'transaction_date' => $data['transaction_date'],
            'description' => $data['description'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'status' => $status,
        ]);

        $uploadedPaths = [];
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            if ($file && $file->isValid()) {
                $uploadedPaths[] = $file->store("receipts/{$business->id}");
            }
        }
        if ($request->hasFile('receipts')) {
            $files = $request->file('receipts');
            $filesArray = is_array($files) ? $files : [$files];
            foreach ($filesArray as $file) {
                if ($file && $file->isValid()) {
                    $uploadedPaths[] = $file->store("receipts/{$business->id}");
                }
            }
        }
        if (!empty($uploadedPaths)) {
            $transaction->image_path = count($uploadedPaths) === 1 ? $uploadedPaths[0] : json_encode(array_values($uploadedPaths));
        }

        $transaction->save();

        ActivityLog::create([
            'action' => 'transaction.created',
            'user_id' => $user->id,
            'business_id' => $business->id,
            'subject_type' => Transaction::class,
            'subject_id' => $transaction->id,
            'details' => [
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'mode' => $transaction->mode,
                'book_role' => $bookRole
            ],
        ]);

        // update book updated_at timestamp
        $book->touch();

        // Notify admins when transaction needs approval (status is pending)
        if ($status === 'pending') {
            $notifiables = $business->users()->wherePivotIn('role', ['primary_admin','admin'])->get();
            foreach ($notifiables as $n) {
                if (method_exists($n, 'wantsNotification') ? $n->wantsNotification('transaction_submitted') : true) {
                    $n->notify(new TransactionSubmitted($transaction));
                }
            }
        }

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'transaction' => $transaction->load(['category', 'book']),
                'status' => $status
            ]);
        }

        // Prefer returning to provided URL (e.g., book page modal) if safe
        $returnTo = $request->input('return_to');
        if ($returnTo && str_starts_with($returnTo, url('/'))) {
            return redirect($returnTo);
        }
        return redirect()->route('transactions.index');
    }

    public function edit(Request $request, Transaction $transaction)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($transaction->business_id === $business->id, 404);

        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);

        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            $canEdit = true;
        } else {
            $bookUser = $user->books()->where('books.id', $transaction->book_id)->first();
            if (!$bookUser) {
                Log::info('Book Not Found');
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this book'
                    ], 403);
                }
                abort(403, 'You do not have access to this book');
            }

            $bookRole = $bookUser->pivot->role;

            // Correct combined logic
            $canEdit = in_array($bookRole, ['primary_admin', 'admin']) || ($bookRole === 'employee' && $transaction->user_id === $user->id);

            if (!$canEdit) {
                Log::info('User does not have permission to edit this transaction', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'book_id' => $transaction->book_id,
                    'book_role' => $bookRole,
                    'business_id' => $business->id
                ]);
            }
        }

        abort_unless($canEdit, 403);

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'mode' => $transaction->mode,
                    'amount' => $transaction->amount,
                    'transaction_date' => $transaction->transaction_date->format('Y-m-d\TH:i'),
                    'category_id' => $transaction->category_id,
                    'description' => $transaction->description,
                    'contact_name' => $transaction->contact_name,
                    'image_path' => $transaction->image_path
                ]
            ]);
        }

        $books = Book::where('business_id', $business->id)->get();
        $categories = Category::where('business_id', $business->id)->get();
        return view('transactions.edit', compact('transaction', 'books', 'categories'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($transaction->business_id === $business->id, 404);

        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);

        // Check book-level permissions (Only Admins / Primary Admins can edit posted entries)
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            $canEdit = true;
        } else {
            $bookUser = $user->books()->where('books.id', $transaction->book_id)->first();
            if (!$bookUser) {
                abort(403, 'You do not have access to this book');
            }
            $bookRole = $bookUser->pivot->role;
            $canEdit = in_array($bookRole, ['primary_admin', 'admin']);
        }

        abort_unless($canEdit, 403, 'Employees do not have permission to edit posted transactions.');

        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'category_id' => 'nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'mode' => 'nullable|string|max:50',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx|max:102400',
            'receipts' => 'nullable|array',
            'receipts.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf,doc,docx,xls,xlsx|max:102400',
        ]);

        $existingPaths = self::parseImagePaths($transaction->image_path);
        $keepPaths = $request->input('keep_receipts', null);
        $keptExisting = is_array($keepPaths)
            ? array_values(array_intersect($existingPaths, $keepPaths))
            : $existingPaths;

        // Delete any existing files that were removed by user
        $removedPaths = array_diff($existingPaths, $keptExisting);
        foreach ($removedPaths as $removed) {
            $trimmed = trim($removed, " \t\n\r\0\x0B\"'");
            if ($trimmed && Storage::exists($trimmed)) {
                Storage::delete($trimmed);
            }
        }

        $newUploadedPaths = [];
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            if ($file && $file->isValid()) {
                $newUploadedPaths[] = $file->store("receipts/{$business->id}");
            }
        }
        if ($request->hasFile('receipts')) {
            $files = $request->file('receipts');
            $filesArray = is_array($files) ? $files : [$files];
            foreach ($filesArray as $file) {
                if ($file && $file->isValid()) {
                    $newUploadedPaths[] = $file->store("receipts/{$business->id}");
                }
            }
        }

        $allPaths = array_values(array_unique(array_merge($keptExisting, $newUploadedPaths)));
        if (!empty($allPaths)) {
            $data['image_path'] = count($allPaths) === 1 ? $allPaths[0] : json_encode($allPaths);
        } else {
            $data['image_path'] = null;
        }

        // If new category is provided, create or find it
        if (!empty($data['new_category'])) {
            $category = Category::firstOrCreate(
                ['name' => $data['new_category'], 'business_id' => $business->id],
                ['type' => $data['type'] ?? $transaction->type]
            );
            $data['category_id'] = $category->id;
        }

        $transaction->update($data);

        ActivityLog::create([
            'action' => 'transaction.updated',
            'user_id' => $user->id,
            'business_id' => $business->id,
            'subject_type' => Transaction::class,
            'subject_id' => $transaction->id,
            'details' => [ 'amount' => $transaction->amount, 'type' => $transaction->type ],
        ]);

        // update book updated_at timestamp
        $book = $transaction->book;
        $book->touch();

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'transaction' => $transaction->load(['category', 'book'])
            ]);
        }

        return redirect()->route('transactions.index');
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($transaction->business_id === $business->id, 404);

        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);

        // Check book-level permissions
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            // Primary admins and admins can delete any transaction
            $canDelete = true;
        } else {
            // For employees, check their role in the specific book
            $bookUser = $user->books()->where('books.id', $transaction->book_id)->first();

            if (!$bookUser) {
                abort(403, 'You do not have access to this book');
            }

            $bookRole = $bookUser->pivot->role;

            // Primary admins/admins can delete any transaction; employees can delete only their own
            $canDelete = in_array($bookRole, ['primary_admin', 'admin']) || ($bookRole === 'employee' && $transaction->user_id === $user->id);
        }

        abort_unless($canDelete, 403);

        // Delete receipt file(s) if exist
        $this->deleteTransactionFiles($transaction->image_path);

        $transaction->delete();

        ActivityLog::create([
            'action' => 'transaction.deleted',
            'user_id' => $user->id,
            'business_id' => $business->id,
            'subject_type' => Transaction::class,
            'subject_id' => $transaction->id,
            'details' => [ 'amount' => $transaction->amount, 'type' => $transaction->type ],
        ]);

        // update book updated_at timestamp
        $book = $transaction->book;
        $book->touch();

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        }

        return redirect()->route('transactions.index');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:transactions,id',
        ]);

        $user = $request->user();
        $business = $request->attributes->get('activeBusiness');
        $transactions = Transaction::whereIn('id', $request->ids)->get();

        // First, authorize all transactions before deleting any
        foreach ($transactions as $transaction) {
            // Ensure transaction belongs to the active business
            if ($transaction->business_id !== $business->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more transactions do not belong to the active business.'
                ], 403);
            }

            // Check permissions using the same logic as the single destroy method
            $businessRole = $user->getBusinessRole($business);
            $canDelete = false;

            if (in_array($businessRole, ['primary_admin', 'admin'])) {
                $canDelete = true;
            } else {
                $bookUser = $user->books()->where('books.id', $transaction->book_id)->first();
                if ($bookUser) {
                    $bookRole = $bookUser->pivot->role;
                    if (in_array($bookRole, ['primary_admin', 'admin']) || ($bookRole === 'employee' && $transaction->user_id === $user->id)) {
                        $canDelete = true;
                    }
                }
            }

            if (!$canDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete one or more of the selected transactions.'
                ], 403);
            }
        }

        // If authorization passes for all, proceed with deletion
        foreach ($transactions as $transaction) {
            // Delete receipt file(s) if exist
            $this->deleteTransactionFiles($transaction->image_path);

            // Log the deletion activity for each transaction
            ActivityLog::create([
                'action'       => 'transaction.deleted',
                'user_id'      => $user->id,
                'business_id'  => $business->id,
                'subject_type' => Transaction::class,
                'subject_id'   => $transaction->id,
                'details'      => ['amount' => $transaction->amount, 'type' => $transaction->type],
            ]);
        }

        // update book updated_at timestamp
        $book = $transaction->book;
        $book->touch();

        // Perform the bulk delete from the database
        Transaction::destroy($request->ids);

        return response()->json([
            'success' => true,
            'message' => 'Selected transactions have been deleted successfully.'
        ]);
    }

    public function contacts(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $q = trim($request->get('q', ''));

        $contacts = Transaction::where('business_id', $business->id)
            ->whereNotNull('contact_name')
            ->where('contact_name', '!=', '')
            ->when($q, fn($query) => $query->where('contact_name', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('contact_name')
            ->pluck('contact_name')
            ->take(20)
            ->values();

        return response()->json(['contacts' => $contacts]);
    }

    public function approve(Request $request, Transaction $transaction)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($transaction->business_id === $business->id, 404);
        $this->authorize('approve', $transaction);
        $transaction->update(['status' => 'approved', 'approved_by' => $request->user()->id]);
        ActivityLog::create([
            'action' => 'transaction.approved',
            'user_id' => $request->user()->id,
            'business_id' => $business->id,
            'subject_type' => Transaction::class,
            'subject_id' => $transaction->id,
        ]);
        // Notify creator
        if ($transaction->user && (method_exists($transaction->user, 'wantsNotification') ? $transaction->user->wantsNotification('transaction_approved') : true)) {
            $transaction->user->notify(new TransactionApproved($transaction));
        }

        // update book updated_at timestamp
        $book = $transaction->book;
        $book->touch();

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully'
            ]);
        }

        return back();
    }

    public function reject(Request $request, Transaction $transaction)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($transaction->business_id === $business->id, 404);
        $this->authorize('approve', $transaction);
        $transaction->update(['status' => 'rejected', 'approved_by' => null]);
        ActivityLog::create([
            'action' => 'transaction.rejected',
            'user_id' => $request->user()->id,
            'business_id' => $business->id,
            'subject_type' => Transaction::class,
            'subject_id' => $transaction->id,
        ]);
        // Notify creator
        if ($transaction->user && (method_exists($transaction->user, 'wantsNotification') ? $transaction->user->wantsNotification('transaction_rejected') : true)) {
            $transaction->user->notify(new TransactionRejected($transaction));
        }

        // update book updated_at timestamp
        $book = $transaction->book;
        $book->touch();

        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully'
            ]);
        }

        return back();
    }

    public static function parseImagePaths(?string $imagePath): array
    {
        if (empty($imagePath)) {
            return [];
        }

        $trimmed = trim($imagePath);
        $decoded = json_decode($trimmed, true);

        if (is_array($decoded)) {
            return array_values(array_filter(array_map(function($item) {
                return is_string($item) ? trim($item, " \t\n\r\0\x0B\"'") : '';
            }, $decoded)));
        }

        $clean = trim($trimmed, " \t\n\r\0\x0B\"'");
        return !empty($clean) ? [$clean] : [];
    }

    public function receipt(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        $business = $transaction->business;
        abort_unless($business, 404);

        $role = $user->getBusinessRole($business);
        if (!$role) {
            abort(403, 'Unauthorized access to transaction business');
        }

        if ($role === 'employee') {
            abort_unless($user->belongsToMany(Book::class, 'book_user')->where('books.id', $transaction->book_id)->exists(), 403);
        }

        abort_unless($transaction->image_path, 404);

        $paths = self::parseImagePaths($transaction->image_path);
        $index = (int) $request->get('index', 0);
        $targetPath = $paths[$index] ?? $paths[0] ?? '';

        if (empty($targetPath)) {
            abort(404);
        }

        $fullPath = null;
        if (Storage::exists($targetPath)) {
            $fullPath = Storage::path($targetPath);
        } elseif (file_exists(storage_path('app/' . $targetPath))) {
            $fullPath = storage_path('app/' . $targetPath);
        } elseif (file_exists(storage_path('app/private/' . $targetPath))) {
            $fullPath = storage_path('app/private/' . $targetPath);
        } elseif (file_exists(storage_path('app/public/' . $targetPath))) {
            $fullPath = storage_path('app/public/' . $targetPath);
        }

        abort_unless($fullPath && file_exists($fullPath), 404, 'File attachment not found on server. Files uploaded prior to volume setup or server deployment may have been reset.');

        return response()->file($fullPath);
    }

    protected function deleteTransactionFiles(?string $imagePath): void
    {
        $paths = self::parseImagePaths($imagePath);
        foreach ($paths as $path) {
            if ($path && Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }

    public function detail(Request $request, Transaction $transaction)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($transaction->business_id === $business->id, 404);

        $user = $request->user();
        $role = $user->getBusinessRole($business);

        // Check permissions
        if ($role === 'employee') {
            $assigned = $user->belongsToMany(Book::class, 'book_user')->where('books.id', $transaction->book_id)->exists();
            abort_unless($assigned, 403);
        }

        // Get activity logs for this transaction
        $activities = ActivityLog::where('subject_type', Transaction::class)
            ->where('subject_id', $transaction->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'type' => $this->getActivityType($log->action),
                    'title' => $this->getActivityTitle($log->action),
                    'description' => $this->getActivityDescription($log->action, $log->details),
                    'user_name' => $log->user->name ?? 'System',
                    'created_at' => $log->created_at->toISOString()
                ];
            });

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d\TH:i'),
                'category' => $transaction->category,
                'description' => $transaction->description,
                'status' => $transaction->status,
                'image_path' => $transaction->image_path,
                'user' => $transaction->user,
                'book' => $transaction->book
            ],
            'activities' => $activities
        ]);
    }

    private function getActivityType($action)
    {
        switch ($action) {
            case 'transaction.created':
                return 'created';
            case 'transaction.updated':
                return 'updated';
            case 'transaction.approved':
                return 'approved';
            case 'transaction.rejected':
                return 'rejected';
            case 'transaction.deleted':
                return 'deleted';
            case 'transaction.imported':
                return 'created';
            default:
                return 'other';
        }
    }

    private function getActivityTitle($action)
    {
        switch ($action) {
            case 'transaction.created':
                return 'Transaction Created';
            case 'transaction.updated':
                return 'Transaction Updated';
            case 'transaction.approved':
                return 'Transaction Approved';
            case 'transaction.rejected':
                return 'Transaction Rejected';
            case 'transaction.deleted':
                return 'Transaction Deleted';
            case 'transaction.imported':
                return 'Transaction Imported';
            default:
                return 'Activity';
        }
    }

    private function getActivityDescription($action, $details)
    {
        switch ($action) {
            case 'transaction.created':
                $amount = $details['amount'] ?? 'N/A';
                $type = $details['type'] ?? 'N/A';
                return "Created {$type} transaction for amount {$amount}";
            case 'transaction.updated':
                $amount = $details['amount'] ?? 'N/A';
                $type = $details['type'] ?? 'N/A';
                return "Updated transaction details - {$type} for amount {$amount}";
            case 'transaction.approved':
                return "Transaction has been approved and is now active";
            case 'transaction.rejected':
                return "Transaction has been rejected";
            case 'transaction.deleted':
                $amount = $details['amount'] ?? 'N/A';
                return "Deleted transaction for amount {$amount}";
            case 'transaction.imported':
                $amount = $details['amount'] ?? 'N/A';
                $type = $details['type'] ?? 'N/A';
                return "Imported transaction - {$type} for amount {$amount}";
            default:
                return "Activity performed on transaction";
        }
    }
}
