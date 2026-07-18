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

        return view('books.show', compact('book','transactions','categories','bookRole','modes'));
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

        $data = $transactions->map(function ($transaction) use ($business, $book, $request) {
            return [
                'id' => $transaction->id,
                'transaction_date' => $transaction->transaction_date->format('M j, Y h:i A'),
                'description' => $transaction->description ?: '—',
                'category' => $transaction->category?->name ?: '—',
                'mode' => $transaction->mode ? ucfirst($transaction->mode) : '—',
                'type' => '<span class="badge ' . ($transaction->type === 'income' ? 'badge-success' : 'badge-danger') . '">' .
                         ucfirst($transaction->type) . '</span>',
                'amount' => '<span style="font-weight: 600; color: ' .
                           ($transaction->type === 'income' ? 'var(--success-color)' : 'var(--danger-color)') . ';">' .
                           $book->currency . ' ' . number_format($transaction->amount, 2) . '</span>',
                'status' => '<span class="badge ' .
                           ($transaction->status === 'approved' ? 'badge-success' :
                           ($transaction->status === 'pending' ? 'badge-warning' : 'badge-danger')) . '">' .
                           ucfirst($transaction->status) . '</span>',
                'user' => $transaction->user?->name ?: '—',
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
            $buttons .= '<a href="/transactions/' . $transaction->id . '/receipt" style="color: var(--primary-color); text-decoration: none; margin-right: 0.5rem;">Receipt</a>';
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
            $buttons .= '<button onclick="editTransaction(' . $transaction->id . ')" style="background: none; border: none; color: var(--primary-color); cursor: pointer; text-decoration: none; margin-right: 0.5rem;">Edit</button>';
            $buttons .= '<button onclick="deleteTransaction(' . $transaction->id . ')" style="background: none; border: none; color: var(--danger-color); cursor: pointer; text-decoration: none;">Delete</button>';
        }

        return '<div style="display: flex; justify-content: flex-end; gap: 0.5rem;">' . $buttons . '</div>';
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
