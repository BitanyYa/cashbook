<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();

        // Get user's role in the business
        $role = $user->getBusinessRole($business);

       if (in_array($role, ['primary_admin', 'admin'])) {
    // Primary admins and admins can see all books with access information
    $allBooks = Book::where('business_id', $business->id)->latest('updated_at')->get();
    $userBookIds = $user->books()->where('business_id', $business->id)->pluck('books.id')->toArray();

    $books = $allBooks->map(function($book) use ($userBookIds) {
        $book->user_has_access = in_array($book->id, $userBookIds);
        $book->hashId = CommonHelper::encodeId($book->id);
        return $book;
    });
        } else {
    // Employees can only see books they are assigned to
    $assignedBookIds = $user->books()->where('business_id', $business->id)->pluck('books.id');
    $books = Book::where('business_id', $business->id)
                ->whereIn('id', $assignedBookIds)
                ->latest('updated_at')->get();

    // For employees, all visible books have access
    $books = $books->map(function($book) {
        $book->user_has_access = true;
        $book->hashId = CommonHelper::encodeId($book->id);
        return $book;
    });
    }

        return view('books.index', compact('books', 'role'));
    }

    public function create() { return view('books.create'); }

    public function store(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'currency' => 'required|string',
        ]);

        $book = Book::create($data + ['business_id' => $business->id]);

        // Add the current user as a primary admin of the newly created book
        $book->users()->syncWithoutDetaching([
            $request->user()->id => ['role' => 'primary_admin'],
        ]);

        return redirect()->route('books.index');
    }

    public function edit(Request $request, Book $book)
    {
        $this->authorize('update', $book);
        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $this->authorize('update', $book);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'currency' => 'required|string',
        ]);
        $book->update($data);
        return redirect()->route('books.index');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        $book->delete();
        return redirect()->route('books.index');
    }

    public function show(Request $request, Book $book)
    {
        // Ensure the book belongs to the active business and the user can view it
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();
        $user->getUserBookRole($book); // Ensure the user has a role in the book

        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('view', $book);

        // Get user's role in the business and book
        // $businessRole = $user->businesses()->where('business_id', $business->id)->value('role');

        // // Determine user's role for this specific book
        // if (in_array($businessRole, ['primary_admin', 'admin'])) {
        //     $bookRole = 'primary_admin'; // Business primary_admins/admins have primary_admin-level access
        // } else {
        //     $bookUser = $user->books()->where('books.id', $book->id)->first();
        //     $bookRole = $bookUser ? $bookUser->pivot->role : null;
        // }

        $bookUser = $user->books()->where('books.id', $book->id)->first();
        $bookRole = $bookUser ? $bookUser->pivot->role : null;

        $transactions = $book->transactions()->with(['category','user'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);
        $categories = Category::where('business_id', $business->id)->get();

        $modes = $book->transactions()->distinct()->pluck('mode')->filter()->values();
        $contacts = $book->transactions()->whereNotNull('contact_name')->where('contact_name', '!=', '')->distinct()->pluck('contact_name')->values();

        return view('books.show', compact('book','transactions','categories','bookRole','modes','contacts'));
    }

    public function transactionsData(Request $request, Book $book)
    {
        // Ensure the book belongs to the active business and the user can view it
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('view', $book);

        $query = $book->transactions()->with(['category', 'user']);

        // Apply filters
        if ($request->filled('duration')) {
            $this->applyDurationFilter($query, $request->duration);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('member')) {
            $query->where('user_id', $request->member);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        if ($request->filled('contact')) {
            $query->where('contact_name', $request->contact);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        // Handle DataTable parameters
        $start = $request->get('start', 0);
        $length = $request->get('length', 25);
        $orderColumn = $request->get('order.0.column', 0);
        $orderDir = $request->get('order.0.dir', 'desc');

        $columns = ['transaction_date', 'description', 'category', 'type', 'amount', 'status', 'user', 'actions'];
        $orderBy = $columns[$orderColumn] ?? 'transaction_date';

        if ($orderBy === 'category') {
            $query->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $orderDir)
                  ->orderBy('transactions.id', 'desc')
                  ->select('transactions.*'); // Ensure we only select transaction columns
        } elseif ($orderBy === 'user') {
            $query->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                  ->orderBy('users.name', $orderDir)
                  ->orderBy('transactions.id', 'desc')
                  ->select('transactions.*'); // Ensure we only select transaction columns
        } elseif ($orderBy === 'transaction_date') {
            $query->orderBy($orderBy, $orderDir)
                  ->orderBy('created_at', 'desc') // Secondary sort by creation time
                  ->orderBy('id', 'desc'); // Tertiary sort by ID for ultimate consistency
        } else {
            $query->orderBy($orderBy, $orderDir)
                  ->orderBy('id', 'desc'); // Secondary sort by ID for consistency
        }

        $totalRecords = $book->transactions()->count();
        $filteredRecords = $query->count();

        $transactions = $query->skip($start)->take($length)->get();

        // Calculate running balance for all transactions in chronological order
        $allBookTransactions = $book->transactions()
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'type', 'amount']);

        $runningBalance = 0;
        $runningBalances = [];
        foreach ($allBookTransactions as $t) {
            if ($t->type === 'income') {
                $runningBalance += $t->amount;
            } else {
                $runningBalance -= $t->amount;
            }
            $runningBalances[$t->id] = $runningBalance;
        }

        $data = $transactions->map(function ($transaction) use ($business, $book, $request, $runningBalances) {
            // Build the Date HTML
            $dateObj = $transaction->transaction_date;
            $dateStr = $dateObj->isToday() ? 'Today' : ($dateObj->isYesterday() ? 'Yesterday' : $dateObj->format('d M, Y'));
            $timeStr = $dateObj->format('h:i A');
            $dateHtml = '<div style="font-weight: 700; color: var(--gray-800);">' . $dateStr . '</div>'
                      . '<div style="font-size: 0.75rem; color: var(--gray-400); margin-top: 1px;">' . $timeStr . '</div>';

            // Build the Details cell HTML
            $detailsHtml = '';
            if ($transaction->contact_name) {
                $contactType = $transaction->type === 'income' ? 'Customer' : 'Supplier';
                $detailsHtml .= '<div style="font-weight:600;color:var(--gray-900);margin-bottom:2px;">'
                    . '<span style="font-weight:700;">(' . e($transaction->contact_name) . ')</span>'
                    . ' <span style="color:var(--gray-400);font-weight:400;font-size:0.8125rem;">(' . $contactType . ')</span>'
                    . '</div>';
            }
            $detailsHtml .= '<div style="font-weight: 700; color:' . ($transaction->contact_name ? 'var(--gray-700)' : 'var(--gray-900)') . ';font-size:0.85rem;">'
                . e($transaction->description ?: '—')
                . '</div>';
            if ($transaction->user) {
                $detailsHtml .= '<div style="font-size:0.7rem;color:var(--gray-400);margin-top:2px;">by ' . e($transaction->user->name) . '</div>';
            }

            // Build the Bill/Attachment HTML
            $billHtml = '—';
            if ($transaction->image_path) {
                $billHtml = '<div style="display:flex;flex-direction:column;align-items:flex-start;gap:1px;">'
                    . '<div style="display:flex;align-items:center;gap:4px;font-size:0.8125rem;color:var(--gray-800);font-weight:600;">'
                    . '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="color:var(--gray-500);">'
                    . '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636L12 12m4.727-4.727a6 6 0 11-8.485 8.485L12 12m1.818-1.818a3 3 0 11-4.243 4.243L12 12" />'
                    . '</svg>'
                    . '1</div>'
                    . '<div style="font-size:0.7rem;color:var(--gray-400);">Attachment</div>'
                    . '</div>';
            }

            $balVal = $runningBalances[$transaction->id] ?? 0;
            $balFormatted = number_format($balVal, $balVal == round($balVal) ? 0 : 2);
            $balanceHtml = '<span style="font-weight: 700; color: var(--gray-800);">' . $balFormatted . '</span>';

            $amtVal = $transaction->amount;
            $amtFormatted = number_format($amtVal, $amtVal == round($amtVal) ? 0 : 2);
            $amountHtml = '<span style="font-weight: 700; color: ' .
                          ($transaction->type === 'income' ? 'var(--success-color)' : 'var(--danger-color)') . ';">' .
                          $amtFormatted . '</span>';

            return [
                'id' => $transaction->id,
                'transaction_date' => $dateHtml,
                'details' => $detailsHtml,
                'category' => $transaction->category?->name ?: '—',
                'mode' => $transaction->mode ? strtoupper($transaction->mode) : '—',
                'bill' => $billHtml,
                'amount' => $amountHtml,
                'balance' => $balanceHtml,
                'actions' => $this->generateActionButtons($transaction, $request)
            ];
        });

        return response()->json([
            'draw' => $request->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function summary(Request $request, Book $book)
    {
        // Ensure the book belongs to the active business and the user can view it
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('view', $book);

        $query = $book->transactions();

        // Apply the same filters as in transactionsData
        if ($request->filled('duration')) {
            $this->applyDurationFilter($query, $request->duration);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('member')) {
            $query->where('user_id', $request->member);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        return response()->json([
            'success' => true,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $netBalance
        ]);
    }

    private function applyDurationFilter($query, $duration)
    {
        switch ($duration) {
            case 'today':
                $query->whereDate('transaction_date', today());
                break;
            case 'yesterday':
                $query->whereDate('transaction_date', today()->subDay());
                break;
            case 'this_week':
                $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'last_week':
                $query->whereBetween('transaction_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('transaction_date', now()->month)
                      ->whereYear('transaction_date', now()->year);
                break;
            case 'last_month':
                $query->whereMonth('transaction_date', now()->subMonth()->month)
                      ->whereYear('transaction_date', now()->subMonth()->year);
                break;
            case 'this_year':
                $query->whereYear('transaction_date', now()->year);
                break;
        }
    }

    private function generateActionButtons($transaction, $request)
    {
        $user = $request->user();
        $business = $request->attributes->get('activeBusiness');
        $businessRole = $user->businesses()->where('business_id', $business->id)->value('role');

        // Determine user's role for this specific book
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            $bookRole = 'primary_admin'; // Business primary_admins/admins have primary_admin-level access
        } else {
            $bookUser = $user->books()->where('books.id', $transaction->book_id)->first();
            $bookRole = $bookUser ? $bookUser->pivot->role : null;
        }

        $buttons = '';

        // Receipt link - available to all users who can view the transaction
        if ($transaction->image_path) {
            $buttons .= '<a href="/transactions/' . $transaction->id . '/receipt" target="_blank" onclick="event.stopPropagation();" style="color: var(--primary-color); text-decoration: none; margin-right: 0.5rem;">Receipt</a>';
        }

        // Edit/Delete buttons - only for primary_admins, or admins for their own transactions
        $canEdit = false;

        if ($bookRole === 'primary_admin') {
            // Primary admins can edit/delete any transaction
            $canEdit = true;
        } elseif ($bookRole === 'admin' && $transaction->user_id === $user->id) {
            // Admins can only edit/delete their own transactions
            $canEdit = true;
        }
        // Employees cannot edit/delete anything

        if ($canEdit) {
            $buttons .= '
                <button onclick="event.stopPropagation(); editTransaction(' . $transaction->id . ')"
                    title="Edit entry"
                    style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border:none;background:transparent;color:#6366f1;cursor:pointer;border-radius:5px;transition:background .12s;"
                    onmouseover="this.style.background=\'#eef2ff\'" onmouseout="this.style.background=\'transparent\'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </button>
                <button onclick="event.stopPropagation(); deleteTransaction(' . $transaction->id . ')"
                    title="Delete entry"
                    style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border:none;background:transparent;color:#ef4444;cursor:pointer;border-radius:5px;transition:background .12s;"
                    onmouseover="this.style.background=\'#fee2e2\'" onmouseout="this.style.background=\'transparent\'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>';
        }

        return '<div class="txn-actions" style="display:inline-flex;align-items:center;gap:2px;opacity:0;transition:opacity .15s;">' . $buttons . '</div>';
    }

    // Book User Management Methods
    public function users(Request $request, Book $book)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('update', $book);

        $bookUsers = $book->users()->get();
        $businessUsers = $business->users()->get();

        // Get users who are not assigned to this book yet
        $availableUsers = $businessUsers->whereNotIn('id', $bookUsers->pluck('id'));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'bookUsers' => $bookUsers->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->pivot->role,
                        'assigned_at' => $user->pivot->created_at->format('M j, Y')
                    ];
                }),
                'availableUsers' => $availableUsers->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ];
                })
            ]);
        }

        return view('books.users', compact('book', 'bookUsers', 'availableUsers'));
    }

    public function searchUsers(Request $request, Book $book)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('update', $book);

        // Check if user has primary_admin or admin role
        $user = $request->user();
        $role = $user->getBookRole($book);
        if (!in_array($role, ['primary_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([
                'success' => true,
                'users' => []
            ]);
        }

        // Get users who are not already assigned to this book
        $bookUserIds = $book->users()->pluck('users.id');

        // Search ALL users in the system, not just business members
        $users = User::where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $bookUserIds)
            ->where('id', '!=', $request->user()->id) // Exclude current user
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'users' => $users->map(function($user) use ($business) {
                $isBusinessMember = $business->users()->where('users.id', $user->id)->exists();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'display' => $user->name . ' (' . $user->email . ')' .
                               ($isBusinessMember ? '' : ' - Will be added to business'),
                    'is_business_member' => $isBusinessMember
                ];
            })
        ]);
    }

    public function inviteUser(Request $request, Book $book)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('update', $book);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:primary_admin,admin,employee'
        ]);

        // Check if user is already assigned to this book
        if ($book->users()->where('users.id', $data['user_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already assigned to this book'
            ], 400);
        }

        // Check if user is a member of the business, if not, add them
        $businessUser = $business->users()->where('users.id', $data['user_id'])->first();
        if (!$businessUser) {
            // Add user to business with 'employee' role as default
            $business->users()->attach($data['user_id'], ['role' => 'employee']);
        }

        // Add user to the book with specified role
        $book->users()->attach($data['user_id'], ['role' => $data['role']]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User added to book successfully'
            ]);
        }

        return back()->with('success', 'User added to book successfully');
    }

    public function updateUserRole(Request $request, Book $book, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('update', $book);

        $data = $request->validate([
            'role' => 'required|in:primary_admin,admin,employee'
        ]);

        // Check if user is assigned to this book
        if (!$book->users()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not assigned to this book'
            ], 404);
        }

        // if the user is the primary_admin of the business, they cannot have their role changed
        if ($user->getBusinessRole($business) === 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change role of business primary_admin'
            ], 403);
        }

        $book->users()->updateExistingPivot($user->id, ['role' => $data['role']]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully'
            ]);
        }

        return back()->with('success', 'User role updated successfully');
    }

    public function removeUser(Request $request, Book $book, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('update', $book);

        // Check if user is assigned to this book
        if (!$book->users()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not assigned to this book'
            ], 404);
        }

        // if the user is the primary_admin of the business, they cannot be removed from the book
        if ($user->getBusinessRole($business) === 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove business primary_admin from book'
            ], 403);
        }

        $book->users()->detach($user->id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User removed from book successfully'
            ]);
        }

        return back()->with('success', 'User removed from book successfully');
    }
}
