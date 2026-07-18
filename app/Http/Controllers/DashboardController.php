<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();
        $from = Carbon::now()->subDays(29)->startOfDay();
        $to = Carbon::now()->endOfDay();

        $days = collect(range(0,29))->map(fn($i) => Carbon::now()->subDays(29 - $i)->format('Y-m-d'));

        // If there is no active business yet, render an empty dashboard gracefully
        if (!$business) {
            return view('dashboard', [
                'lineLabels' => $days,
                'incomeSeries' => array_fill(0, 30, 0.0),
                'expenseSeries' => array_fill(0, 30, 0.0),
                'categoryLabels' => collect(),
                'categorySeries' => collect(),
                'hasAccess' => false,
                'accessibleBooks' => collect(),
                'totalIncome' => 0,
                'totalExpense' => 0,
                'recentTransactions' => collect(),
            ]);
        }

        // Get user's role in the business
        $role = $user->businesses()->where('business_id', $business->id)->value('role');

        // Determine which books the user can access
        if (in_array($role, ['primary_admin', 'admin'])) {
    // Primary admins and admins can see all business data, and always have
    // access so they can create the business's first book.
    $accessibleBooks = Book::where('business_id', $business->id)->latest('updated_at')->get();
    $accessibleBookIds = $accessibleBooks->pluck('id');
    $hasAccess = true;
        } else {
    // Employees can only see data from books they are assigned to
    $accessibleBooks = $user->books()->where('business_id', $business->id)->get();
    $accessibleBookIds = $accessibleBooks->pluck('id');
    $hasAccess = $accessibleBookIds->isNotEmpty();
    }
    
        $totals = Transaction::where('business_id', $business->id)
            ->whereIn('book_id', $accessibleBookIds)
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$from, $to])
            ->get()
            ->groupBy(fn($t) => Carbon::parse($t->transaction_date)->format('Y-m-d'));

        $income = [];
        $expense = [];
        foreach ($days as $d) {
            $day = $totals->get($d, collect());
            $income[] = (float) $day->where('type', 'income')->sum('amount');
            $expense[] = (float) $day->where('type', 'expense')->sum('amount');
        }

        $monthStart = Carbon::now()->startOfMonth();
        $byCategory = Transaction::where('business_id', $business->id)
            ->whereIn('book_id', $accessibleBookIds)
            ->where('status', 'approved')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $to])
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn($g) => [
                'name' => optional($g->first()->category)->name ?? 'Uncategorized',
                'total' => (float) $g->sum('amount')
            ]);

        // Calculate totals for the current period
        $totalIncome = Transaction::where('business_id', $business->id)
            ->whereIn('book_id', $accessibleBookIds)
            ->where('status', 'approved')
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $totalExpense = Transaction::where('business_id', $business->id)
            ->whereIn('book_id', $accessibleBookIds)
            ->where('status', 'approved')
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        // Get recent transactions
        $recentTransactions = Transaction::where('business_id', $business->id)
            ->whereIn('book_id', $accessibleBookIds)
            ->where('status', 'approved')
            ->with(['book', 'category', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'lineLabels' => $days,
            'incomeSeries' => $income,
            'expenseSeries' => $expense,
            'categoryLabels' => $byCategory->pluck('name')->values(),
            'categorySeries' => $byCategory->pluck('total')->values(),
            'hasAccess' => true,
            'role' => $role,
            'accessibleBookIds' => $accessibleBookIds,
            'accessibleBooks' => $accessibleBooks,
            'totalIncome' => (float) $totalIncome,
            'totalExpense' => (float) $totalExpense,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
