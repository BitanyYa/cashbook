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

        if (!$business) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'User is not yet assigned to any cashbook.'], 403);
            }
            return redirect()->route('unassigned');
        }

        // Get user's role in the business
        $role = $user->getBusinessRole($business);

        $query = Book::where('business_id', $business->id);

        if ($role !== 'primary_admin') {
            // Book admins and employees only see books they are explicitly assigned to
            $assignedBookIds = $user->books()->where('business_id', $business->id)->pluck('books.id');
            $query->whereIn('id', $assignedBookIds);
        }

        // Apply Search Filter
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply Sorting
        $sort = $request->get('sort', 'updated_at_desc');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'updated_at_asc':
                $query->orderBy('updated_at', 'asc');
                break;
            case 'updated_at_desc':
            default:
                $query->orderBy('updated_at', 'desc');
                break;
        }

        $userBookIds = $user->books()->where('business_id', $business->id)->pluck('books.id')->toArray();
        $books = $query->paginate(12)->appends($request->query());

        $books->getCollection()->transform(function($book) use ($userBookIds, $role) {
            $book->user_has_access = in_array($role, ['primary_admin', 'admin']) ? in_array($book->id, $userBookIds) : true;
            $book->hashId = CommonHelper::encodeId($book->id);
            return $book;
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'books' => $books->map(function($book) {
                    $income  = $book->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
                    $expense = $book->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
                    $balance = $income - $expense;
                    return [
                        'id' => $book->id,
                        'name' => $book->name,
                        'description' => $book->description,
                        'balance' => $balance,
                        'balance_formatted' => number_format(abs($balance)),
                        'balance_color' => $balance >= 0 ? '#10b981' : '#ef4444',
                        'members_count' => $book->users()->count(),
                        'updated_human' => $book->updated_at->diffForHumans(),
                        'user_has_access' => $book->user_has_access,
                        'url' => route('books.show', $book),
                        'edit_url' => route('books.edit', $book),
                        'users_url' => route('books.users', $book),
                    ];
                }),
                'pagination' => (string) $books->links()
            ]);
        }

        return view('books.index', compact('books', 'role', 'sort'));
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

        // Add the creator as a primary admin of the newly created book
        $syncUsers = [
            $request->user()->id => ['role' => 'primary_admin'],
        ];

        // Ensure the business Primary Admin is also attached as primary admin
        $primaryAdmin = $business->users()->wherePivot('role', 'primary_admin')->first();
        if ($primaryAdmin) {
            $syncUsers[$primaryAdmin->id] = ['role' => 'primary_admin'];
        }

        $book->users()->syncWithoutDetaching($syncUsers);

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

        if (!$business || $book->business_id !== $business->id) {
            abort(404);
        }
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

        $user = $request->user();
        $businessRole = $user->businesses()->where('business_id', $business->id)->value('role');
        $bookUser = $user->books()->where('books.id', $book->id)->first();
        $bookRole = $bookUser ? $bookUser->pivot->role : null;
        $effectiveRole = in_array($businessRole, ['primary_admin', 'admin']) ? $businessRole : $bookRole;

        $query = $book->transactions()->with(['category', 'user']);

        // Non-admin users (employee, operator, viewer) should ONLY see their own posted transactions
        if (!in_array($effectiveRole, ['primary_admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

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

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->appends($request->query());

        $initialCardData = $transactions->map(function ($t) use ($user) {
            return [
                'id' => $t->id,
                'raw_date_group' => $t->transaction_date->format('d F Y'),
                'raw_time' => strtolower($t->transaction_date->format('g:i a')),
                'raw_type' => $t->type,
                'raw_amount' => number_format($t->amount, 0),
                'raw_user_name' => $t->user_id === $user->id ? 'You' : ($t->user->name ?? 'User'),
                'raw_mode' => $t->mode ? ucfirst($t->mode) : 'Cash',
            ];
        });

        $categories = Category::where('business_id', $business->id)->get();
        $modes = $book->transactions()->distinct()->pluck('mode')->filter()->values();
        $contacts = $book->transactions()->whereNotNull('contact_name')->where('contact_name', '!=', '')->distinct()->pluck('contact_name')->values();

        return view('books.show', compact('book','transactions','initialCardData','categories','bookRole','modes','contacts'));
    }

    public function transactionsData(Request $request, Book $book)
    {
        // Ensure the book belongs to the active business and the user can view it
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('view', $book);

        $query = $book->transactions()->with(['category', 'user']);

        // Non-admin users (employee, operator, viewer) only see their own transactions
        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);
        $bookRole = $user->getBookRole($book);
        $effectiveRole = in_array($businessRole, ['primary_admin', 'admin']) ? $businessRole : $bookRole;

        if (!in_array($effectiveRole, ['primary_admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

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
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 50);

        // DataTable sends order[0][column] and order[0][dir] as nested arrays
        $orderColumnIdx = (int) ($request->input('order.0.column') ?? ($request->input('order')[0]['column'] ?? 1));
        $orderDir       = in_array(strtolower($request->input('order.0.dir') ?? ($request->input('order')[0]['dir'] ?? 'desc')), ['asc','desc'])
                          ? strtolower($request->input('order.0.dir') ?? ($request->input('order')[0]['dir'] ?? 'desc'))
                          : 'desc';

        // Column index map matches DataTable columns:
        // 0=checkbox, 1=transaction_date, 2=details, 3=category, 4=mode, 5=bill, 6=amount, 7=balance, 8=actions
        $columnMap = [
            0 => 'transaction_date',
            1 => 'transaction_date',
            2 => 'transaction_date',
            3 => 'category',
            4 => 'mode',
            5 => 'transaction_date',
            6 => 'amount',
            7 => 'transaction_date',
            8 => 'transaction_date',
        ];
        $orderBy = $columnMap[$orderColumnIdx] ?? 'transaction_date';

        if ($orderBy === 'category') {
            $query->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
                  ->orderBy('categories.name', $orderDir)
                  ->orderBy('transactions.id', 'desc')
                  ->select('transactions.*');
        } elseif ($orderBy === 'amount') {
            $query->orderBy('amount', $orderDir)->orderBy('id', 'desc');
        } elseif ($orderBy === 'mode') {
            $query->orderBy('mode', $orderDir)->orderBy('id', 'desc');
        } else {
            // Default: sort by transaction_date
            $query->orderBy('transaction_date', $orderDir)
                  ->orderBy('created_at', 'desc')
                  ->orderBy('id', 'desc');
        }

        $totalRecords = $book->transactions()->count();
        $filteredRecords = $query->count();

        // Calculate filtered summary totals directly from query before pagination
        $summaryIncome = (clone $query)->where('type', 'income')->sum('amount');
        $summaryExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $summaryBalance = $summaryIncome - $summaryExpense;

        $transactions = $query->skip($start)->take($length)->get();

        // Fast raw DB query for running balances (avoids heavy Eloquent model instantiation)
        $allBookTransactions = \Illuminate\Support\Facades\DB::table('transactions')
            ->where('book_id', $book->id)
            ->select('id', 'type', 'amount')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

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

        $data = $transactions->map(function ($transaction) use ($business, $book, $request, $runningBalances, $user) {
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
                'actions' => $this->generateActionButtons($transaction, $request),
                'raw_date_group' => $transaction->transaction_date->format('d F Y'),
                'raw_time' => strtolower($transaction->transaction_date->format('g:i a')),
                'raw_type' => $transaction->type,
                'raw_amount' => number_format($transaction->amount, 0),
                'raw_user_name' => $transaction->user_id === $user->id ? 'You' : ($transaction->user->name ?? 'User'),
                'raw_mode' => $transaction->mode ? ucfirst($transaction->mode) : 'Cash',
            ];
        });

        return response()->json([
            'draw' => (int) $request->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
            'summary' => [
                'income' => number_format($summaryIncome, 0),
                'expense' => number_format($summaryExpense, 0),
                'balance' => ($summaryBalance >= 0 ? '' : '-') . number_format(abs($summaryBalance), 0),
                'balance_color' => $summaryBalance >= 0 ? '#16a34a' : '#dc2626',
            ]
        ]);
    }

    public function summary(Request $request, Book $book)
    {
        // Ensure the book belongs to the active business and the user can view it
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);
        $this->authorize('view', $book);

        $query = $book->transactions();

        // Employees only see their own transactions in summary too
        $user = $request->user();
        $businessRole = $user->getBusinessRole($business);
        $bookRole = $user->getBookRole($book);
        $effectiveRole = in_array($businessRole, ['primary_admin', 'admin']) ? $businessRole : $bookRole;

        if (!in_array($effectiveRole, ['primary_admin', 'admin'])) {
            $query->where('user_id', $user->id);
        }

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
        $businessRole = $user->getBusinessRole($business);

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

        // Edit/Delete buttons - for primary_admins and admins
        $canEdit = in_array($bookRole, ['primary_admin', 'admin']);
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

        $search = trim($request->get('q', ''));

        if (strlen($search) < 1) {
            return response()->json([
                'success' => true,
                'users' => []
            ]);
        }

        // Get users who are not already assigned to this book
        $bookUserIds = $book->users()->pluck('users.id');

        // Search ALL users in the system (case-insensitive)
        $users = User::where(function($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->whereNotIn('id', $bookUserIds)
            ->where('id', '!=', $request->user()->id) // Exclude current user
            ->limit(15)
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
            'role' => 'nullable|in:primary_admin,admin,employee'
        ]);

        $role = $data['role'] ?? 'employee';

        // Check if user is already assigned to this book
        if ($book->users()->where('users.id', $data['user_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already assigned to this book'
            ], 400);
        }

        // Ensure user is attached to the business as a member if not already present
        if (!$business->users()->where('users.id', $data['user_id'])->exists()) {
            $business->users()->attach($data['user_id'], ['role' => 'employee']);
        }

        $book->users()->attach($data['user_id'], ['role' => $role]);

        $addedUser = User::find($data['user_id']);
        $userName = $addedUser ? $addedUser->name : 'User';
        return response()->json(['success' => true, 'message' => "{$userName} added to book successfully with role " . ucfirst($role) . "."]);
    }

    public function updateUserRole(Request $request, Book $book, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);

        $currentUser = $request->user();
        $currentUserRole = $currentUser->getBookRole($book);

        // ONLY Primary Admin can promote or demote members
        if ($currentUserRole !== 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only the Primary Admin can promote or demote members.'
            ], 403);
        }

        $data = $request->validate([
            'role' => 'required|in:primary_admin,admin,employee'
        ]);

        if ($data['role'] === 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Use Transfer Ownership to assign a new Primary Admin.'
            ], 400);
        }

        $targetUserRole = $user->getBookRole($book);
        if ($targetUserRole === 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change role of Primary Admin directly. Please transfer ownership first.'
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

        $currentUser = $request->user();
        $currentUserRole = $currentUser->getBookRole($book);

        // Regular users cannot remove members
        if (!in_array($currentUserRole, ['primary_admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if target user is assigned to this book
        if (!$book->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User is not assigned to this book'], 404);
        }

        $targetRole = $user->getBookRole($book);

        // Primary Admin cannot be removed under any circumstances
        if ($targetRole === 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the Primary Admin. Ownership must be transferred first.'
            ], 403);
        }

        // Admins CANNOT remove other Admins or Primary Admins
        if ($currentUserRole === 'admin' && in_array($targetRole, ['admin', 'primary_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Admins can only remove regular users.'
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

    public function transferOwnership(Request $request, Book $book)
    {
        $business = $request->attributes->get('activeBusiness');
        abort_unless($book->business_id === $business->id, 404);

        $currentUser = $request->user();
        if ($currentUser->getBookRole($book) !== 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only the Primary Admin can transfer ownership of this cashbook.'
            ], 403);
        }

        $data = $request->validate([
            'new_owner_id' => 'required|exists:users,id'
        ]);

        $newOwnerId = (int) $data['new_owner_id'];
        if ($newOwnerId === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are already the Primary Admin.'
            ], 400);
        }

        // Target user must be a member of the cashbook
        if (!$book->users()->where('users.id', $newOwnerId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Target user must be a member of this cashbook first.'
            ], 400);
        }

        // 1. Promote new owner to primary_admin
        $book->users()->updateExistingPivot($newOwnerId, ['role' => 'primary_admin']);

        // 2. Demote current owner to admin
        $book->users()->updateExistingPivot($currentUser->id, ['role' => 'admin']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ownership transferred successfully. You are now an Admin of this cashbook.'
            ]);
        }

        return back()->with('success', 'Ownership transferred successfully.');
    }
}
