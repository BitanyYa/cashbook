<x-app-layout>

{{-- ═══════════════════════════════════════════
     STATE 1 — No business selected
═══════════════════════════════════════════ --}}
@if(!$activeBusiness)
    <div style="display:flex;align-items:center;justify-content:center;min-height:55vh;">
        <div style="text-align:center;max-width:380px;">
            <div style="width:64px;height:64px;background:var(--primary-color);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;opacity:.15;">
                <svg width="30" height="30" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h2 style="font-size:1.125rem;font-weight:700;color:var(--gray-900);margin-bottom:.5rem;">No Business Selected</h2>
            <p style="font-size:.875rem;color:var(--gray-500);margin-bottom:1.5rem;line-height:1.6;">
                Create or select a business to start managing your cashbooks.
            </p>
            <a href="{{ route('businesses.create') }}" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Create New Business
            </a>
        </div>
    </div>

{{-- ═══════════════════════════════════════════
     STATE 2 — Employee with no book access
═══════════════════════════════════════════ --}}
@elseif(isset($hasAccess) && !$hasAccess)
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $activeBusiness->name }}</h1>
            <p class="page-subtitle">{{ now()->format('l, d F Y') }}</p>
        </div>
    </div>
    <div class="card" style="text-align:center;padding:3rem 2rem;">
        <svg style="width:3rem;height:3rem;color:var(--gray-300);margin:0 auto 1rem;display:block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <h3 style="font-size:1rem;font-weight:600;color:var(--gray-800);margin-bottom:.5rem;">No Books Assigned</h3>
        <p style="font-size:.875rem;color:var(--gray-500);max-width:380px;margin:0 auto 1rem;line-height:1.6;">
            You don't have access to any books in this business yet.<br>
            Contact your administrator to get access.
        </p>
        <span class="badge badge-primary" style="font-size:.75rem;">Role: {{ ucfirst($role ?? 'employee') }}</span>
    </div>

{{-- ═══════════════════════════════════════════
     STATE 3 — Main dashboard
═══════════════════════════════════════════ --}}
@else

{{-- ── 1. Header ── --}}
@php
    $netBalance  = $totalIncome - $totalExpense;
    $userRole    = Auth::user()->businesses()->where('business_id', $activeBusiness->id)->value('role');
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">{{ $activeBusiness->name }}</h1>
        <p class="page-subtitle">{{ now()->format('l, d F Y') }}</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @if(in_array($userRole, ['primary_admin','admin']))
        <a href="{{ route('settings.index', $activeBusiness) }}" class="btn btn-secondary btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
            </svg>
            Business Team
        </a>
        @endif
    </div>
</div>

{{-- ── 2. Summary Cards ── --}}
<div class="stats-grid" style="margin-bottom:1.5rem;">

    {{-- Net Balance --}}
    <div class="stat-card {{ $netBalance >= 0 ? '' : 'danger' }}">
        <div class="stat-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:middle;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Net Balance (30 days)
        </div>
        <div class="stat-value" style="color:{{ $netBalance >= 0 ? 'var(--success-color)' : 'var(--danger-color)' }};">
            {{ $netBalance >= 0 ? '' : '-' }}{{ number_format(abs($netBalance), 2) }}
        </div>
    </div>

    {{-- Total Income --}}
    <div class="stat-card success">
        <div class="stat-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:middle;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Total Income (30 days)
        </div>
        <div class="stat-value" style="color:var(--success-color);">
            {{ number_format($totalIncome, 2) }}
        </div>
    </div>

    {{-- Total Expense --}}
    <div class="stat-card danger">
        <div class="stat-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:middle;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
            Total Expense (30 days)
        </div>
        <div class="stat-value" style="color:var(--danger-color);">
            {{ number_format($totalExpense, 2) }}
        </div>
    </div>

    {{-- Active Books --}}
    <div class="stat-card warning">
        <div class="stat-label">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:middle;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Cashbooks
        </div>
        <div class="stat-value">{{ $accessibleBooks->count() }}</div>
    </div>

</div>

{{-- ── 3. Two-column body: Quick Actions + Charts ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    {{-- Quick Actions card --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Quick Actions</h3>
                <p class="card-subtitle">Common tasks at a glance</p>
            </div>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">

                {{-- New Income --}}
                @if($accessibleBooks->isNotEmpty())
                <a href="{{ route('books.show', $accessibleBooks->first()) }}"
                   class="btn btn-success"
                   style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span style="font-size:.8125rem;">New Income</span>
                </a>
                @else
                <button class="btn btn-success" disabled
                        style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;opacity:.45;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span style="font-size:.8125rem;">New Income</span>
                </button>
                @endif

                {{-- New Expense --}}
                @if($accessibleBooks->isNotEmpty())
                <a href="{{ route('books.show', $accessibleBooks->first()) }}"
                   class="btn btn-danger"
                   style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    <span style="font-size:.8125rem;">New Expense</span>
                </a>
                @else
                <button class="btn btn-danger" disabled
                        style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;opacity:.45;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    <span style="font-size:.8125rem;">New Expense</span>
                </button>
                @endif

                {{-- View Reports --}}
                @if($accessibleBooks->isNotEmpty())
                <a href="{{ route('reports.index', $accessibleBooks->first()) }}"
                   class="btn btn-secondary"
                   style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span style="font-size:.8125rem;">View Reports</span>
                </a>
                @else
                <button class="btn btn-secondary" disabled
                        style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;opacity:.45;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span style="font-size:.8125rem;">View Reports</span>
                </button>
                @endif

                {{-- Manage Team (admin only) --}}
                @if(in_array($userRole, ['primary_admin','admin']))
                <a href="{{ route('settings.index', $activeBusiness) }}"
                   class="btn btn-secondary"
                   style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                    </svg>
                    <span style="font-size:.8125rem;">Manage Team</span>
                </a>
                @else
                <a href="{{ route('dashboard') }}"
                   class="btn btn-secondary"
                   style="flex-direction:column;gap:.3rem;padding:1rem;height:auto;justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span style="font-size:.8125rem;">Dashboard</span>
                </a>
                @endif

            </div>
        </div>
    </div>

    {{-- Income vs Expense Chart --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Income vs Expense</h3>
                <p class="card-subtitle">Last 30 days — approved transactions</p>
            </div>
        </div>
        <div class="card-body" style="padding:.75rem 1rem 1rem;">
            @if(array_sum($incomeSeries) > 0 || array_sum($expenseSeries) > 0)
                <canvas id="incomeExpenseChart" style="max-height:200px;"></canvas>
            @else
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:180px;gap:.5rem;">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--gray-300);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p style="font-size:.8125rem;color:var(--gray-400);">No transaction data yet</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ── 4. Recent Transactions ── --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Recent Transactions</h3>
            <p class="card-subtitle">Latest 10 approved transactions across all your cashbooks</p>
        </div>
        @if($accessibleBooks->isNotEmpty())
        <a href="{{ route('books.show', $accessibleBooks->first()) }}" class="btn btn-secondary btn-sm">
            View All
        </a>
        @endif
    </div>
    <div class="card-body" style="padding:0;">
        @if($recentTransactions->isEmpty())
            <div style="text-align:center;padding:2.5rem;color:var(--gray-400);">
                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto .75rem;display:block;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p style="font-size:.875rem;">No transactions yet. Open a cashbook and add your first entry.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Book</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th style="text-align:right;">Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $txn)
                        <tr onclick="window.location='{{ route('books.show', $txn->book) }}'"
                            style="cursor:pointer;">
                            <td style="white-space:nowrap;font-size:.8125rem;color:var(--gray-600);">
                                {{ \Carbon\Carbon::parse($txn->transaction_date)->format('d M Y') }}
                            </td>
                            <td style="font-size:.8125rem;color:var(--gray-500);max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $txn->book->name }}
                            </td>
                            <td style="font-size:.875rem;color:var(--gray-800);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $txn->description ?: '—' }}
                            </td>
                            <td style="font-size:.8125rem;color:var(--gray-500);">
                                {{ optional($txn->category)->name ?? '—' }}
                            </td>
                            <td>
                                @if($txn->type === 'income')
                                    <span class="badge badge-success">Income</span>
                                @else
                                    <span class="badge badge-danger">Expense</span>
                                @endif
                            </td>
                            <td style="text-align:right;font-weight:600;white-space:nowrap;
                                color:{{ $txn->type === 'income' ? 'var(--success-color)' : 'var(--danger-color)' }};">
                                {{ $txn->type === 'income' ? '+' : '-' }}{{ number_format($txn->amount, 2) }}
                            </td>
                            <td>
                                @if($txn->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($txn->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ── 5. Monthly Cash Flow Chart ── --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title">Monthly Cash Flow</h3>
            <p class="card-subtitle">Daily income vs expense — last 30 days</p>
        </div>
    </div>
    <div class="card-body" style="padding:.75rem 1rem 1rem;">
        @if(array_sum($incomeSeries) > 0 || array_sum($expenseSeries) > 0)
            <canvas id="cashFlowChart" style="max-height:220px;"></canvas>
        @else
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:180px;gap:.5rem;">
                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--gray-300);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                </svg>
                <p style="font-size:.8125rem;color:var(--gray-400);">No cash flow data for the last 30 days</p>
            </div>
        @endif
    </div>
</div>

@endif {{-- end STATE 3 (closes the @if(!$activeBusiness) chain) --}}

</x-app-layout>

@if($activeBusiness && isset($hasAccess) && $hasAccess)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const lineLabels  = @json($lineLabels);
    const incomeData  = @json($incomeSeries);
    const expenseData = @json($expenseSeries);

    // Shorten labels to "dd Mon"
    const shortLabels = lineLabels.map(d => {
        const dt = new Date(d);
        return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
    });

    // ── Income vs Expense bar chart ──────────────────
    const barCtx = document.getElementById('incomeExpenseChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: shortLabels,
                datasets: [
                    {
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: 'rgba(5,150,105,.75)',
                        borderRadius: 3,
                        borderSkipped: false,
                    },
                    {
                        label: 'Expense',
                        data: expenseData,
                        backgroundColor: 'rgba(220,38,38,.65)',
                        borderRadius: 3,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 10, font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.05)' },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // ── Cash flow line chart ─────────────────────────
    const lineCtx = document.getElementById('cashFlowChart');
    if (lineCtx) {
        // Cumulative net running total
        let running = 0;
        const netSeries = incomeData.map((inc, i) => {
            running += (inc - expenseData[i]);
            return parseFloat(running.toFixed(2));
        });

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: shortLabels,
                datasets: [
                    {
                        label: 'Income',
                        data: incomeData,
                        borderColor: 'rgba(5,150,105,1)',
                        backgroundColor: 'rgba(5,150,105,.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        borderWidth: 2,
                    },
                    {
                        label: 'Expense',
                        data: expenseData,
                        borderColor: 'rgba(220,38,38,1)',
                        backgroundColor: 'rgba(220,38,38,.06)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        borderWidth: 2,
                    },
                    {
                        label: 'Net (cumulative)',
                        data: netSeries,
                        borderColor: 'rgba(37,99,235,1)',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 2,
                        borderWidth: 2,
                        borderDash: [5, 3],
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 10, font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.05)' },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif
