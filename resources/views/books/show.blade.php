<x-app-layout>
@php
    $userRole = Auth::user()->books()->where('book_id', $book->id)->first()->pivot->role ?? 'employee';
    $allTransactions = $book->transactions;
    $totalIncome  = $allTransactions->where('type', 'income')->sum('amount');
    $totalExpense = $allTransactions->where('type', 'expense')->sum('amount');
    $netBalance   = $totalIncome - $totalExpense;
@endphp

{{-- ══════════════════════════════════════════
     1. PAGE HEADER
══════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;gap:1rem;flex-wrap:wrap;">

    {{-- Left: back + title + icon buttons --}}
    <div style="display:flex;align-items:center;gap:0.625rem;min-width:0;">
        <a href="{{ route('books.index') }}"
           style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;color:var(--gray-600);transition:background .12s;"
           onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>

        <h1 style="font-size:1.25rem;font-weight:800;color:var(--gray-900);letter-spacing:.02em;text-transform:uppercase;margin:0;">
            {{ $book->name }}
        </h1>

        @if(in_array($userRole, ['primary_admin', 'admin']))
            {{-- Gear / settings --}}
            <a href="{{ route('books.edit', $book) }}"
               title="Book Settings"
               style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:6px;color:var(--gray-400);transition:background .12s,color .12s;"
               onmouseover="this.style.background='var(--gray-100)';this.style.color='var(--primary-color)'"
               onmouseout="this.style.background='transparent';this.style.color='var(--gray-400)'">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/>
                </svg>
            </a>

            {{-- Team / manage users --}}
            <button @click="$dispatch('open-modal', 'manage-users')"
                    title="Manage Members"
                    style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:6px;color:var(--gray-400);border:none;background:transparent;cursor:pointer;transition:background .12s,color .12s;"
                    onmouseover="this.style.background='var(--gray-100)';this.style.color='var(--primary-color)'"
                    onmouseout="this.style.background='transparent';this.style.color='var(--gray-400)'">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </button>
        @endif
    </div>

    {{-- Right: Add Bulk Entries + Reports --}}
    @if($bookRole !== 'employee')
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a href="{{ route('transactions.import.create', $book) }}" class="btn btn-secondary btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Add Bulk Entries
        </a>
        <a href="{{ route('reports.index', $book) }}" class="btn btn-secondary btn-sm">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Reports
        </a>
    </div>
    @endif
</div>

{{-- ══ 2. FILTER PILLS ══ --}}
@php
$pillStyle = "display:inline-flex;align-items:center;padding:6px 28px 6px 12px;font-size:0.8125rem;font-weight:500;color:#334155;background:#fff;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;font-family:inherit;appearance:none;-webkit-appearance:none;background-image:url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e\");background-position:right 8px center;background-repeat:no-repeat;background-size:1.2rem;outline:none;";
@endphp

<div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.5rem;">
    <select id="filter-duration" onchange="reloadTable()" style="{{ $pillStyle }}">
        <option value="">Duration: All Time</option>
        <option value="today">Today</option>
        <option value="yesterday">Yesterday</option>
        <option value="this_week">This Week</option>
        <option value="last_week">Last Week</option>
        <option value="this_month">This Month</option>
        <option value="last_month">Last Month</option>
        <option value="this_year">This Year</option>
    </select>
    <select id="filter-type" onchange="reloadTable()" style="{{ $pillStyle }}">
        <option value="">Types: All</option>
        <option value="income">Income</option>
        <option value="expense">Expense</option>
    </select>
    <select id="filter-contact" onchange="reloadTable()" style="{{ $pillStyle }}">
        <option value="">Contacts: All</option>
        @foreach($contacts as $c)
            <option value="{{ $c }}">{{ $c }}</option>
        @endforeach
    </select>
    <select id="filter-member" onchange="reloadTable()" style="{{ $pillStyle }}">
        <option value="">Members: All</option>
        @foreach($book->business->users as $u)
            <option value="{{ $u->id }}">{{ $u->name }}</option>
        @endforeach
    </select>
    <select id="filter-mode" onchange="reloadTable()" style="{{ $pillStyle }}">
        <option value="">Payment Modes: All</option>
        @foreach($modes as $mode)
            <option value="{{ $mode }}">{{ strtoupper($mode) }}</option>
        @endforeach
    </select>
</div>
<div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem;">
    <select id="filter-category" onchange="reloadTable()" style="{{ $pillStyle }}">
        <option value="">Categories: All</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="button" onclick="clearAllFilters()" style="padding:.35rem .75rem;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;">Clear Filters</button>
</div>

{{-- ══ 3. SEARCH + CASH IN / CASH OUT ══ --}}
<div style="display:flex;align-items:center;gap:.625rem;margin-bottom:1.125rem;flex-wrap:wrap;">
    <div style="position:relative;flex:1;min-width:200px;">
        <svg style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--gray-400);"
             width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input id="filter-search" type="text" placeholder="Search by remark or amount..."
               oninput="debounceSearch()"
               style="width:100%;padding:.45rem 2.5rem .45rem 2.1rem;border:1px solid var(--gray-300);border-radius:6px;font-size:.8125rem;font-family:inherit;outline:none;color:var(--gray-700);background:#fff;transition:border-color .15s;"
               onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--gray-300)'">
        <span style="position:absolute;right:.65rem;top:50%;transform:translateY(-50%);font-size:.75rem;color:var(--gray-400);pointer-events:none;">/</span>
    </div>
    @if($bookRole !== 'employee')
    <button id="cash-in-btn"
            style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.25rem;background:#047857;color:#fff;border:none;border-radius:6px;font-size:.875rem;font-weight:700;cursor:pointer;font-family:inherit;white-space:nowrap;box-shadow: 0 1px 2px rgba(4, 120, 87, 0.25);">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Cash In
    </button>
    <button id="cash-out-btn"
            style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.25rem;background:#be123c;color:#fff;border:none;border-radius:6px;font-size:.875rem;font-weight:700;cursor:pointer;font-family:inherit;white-space:nowrap;box-shadow: 0 1px 2px rgba(190, 18, 60, 0.25);">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
        </svg>
        Cash Out
    </button>
    <button id="bulk-delete-btn" onclick="bulkDeleteTransactions()"
            style="display:none;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--danger-color);color:#fff;border:none;border-radius:6px;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:inherit;">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Delete (<span id="selected-count">0</span>)
    </button>
    @endif
</div>

{{-- ══ 4. SUMMARY ROW ══ --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;margin-bottom:1.5rem;background:#fff;padding:1.25rem 1.75rem;box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
    <!-- Cash In Card -->
    <div class="cash-in-card" style="display:flex;align-items:center;gap:.875rem;">
        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e6f4ea; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 800; color: #137333; font-size: 1.125rem;">+</div>
        <div>
            <div style="font-size:.78rem;color:var(--gray-500);font-weight:600;margin-bottom:.1rem;">Cash In</div>
            <div class="summary-amount" style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1.2;">
                {{ number_format($totalIncome, $totalIncome == round($totalIncome) ? 0 : 2) }}
            </div>
        </div>
    </div>
    <!-- Cash Out Card -->
    <div class="cash-out-card" style="display:flex;align-items:center;gap:.875rem;justify-content:center;">
        <div style="width: 32px; height: 32px; border-radius: 50%; background: #fce8e6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 800; color: #c5221f; font-size: 1.125rem;">-</div>
        <div>
            <div style="font-size:.78rem;color:var(--gray-500);font-weight:600;margin-bottom:.1rem;">Cash Out</div>
            <div class="summary-amount" style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1.2;">
                {{ number_format($totalExpense, $totalExpense == round($totalExpense) ? 0 : 2) }}
            </div>
        </div>
    </div>
    <!-- Net Balance Card -->
    <div class="net-balance-card" style="display:flex;align-items:center;gap:.875rem;justify-content:flex-end;">
        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e8f0fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 800; color: #1a73e8; font-size: 1.125rem;">=</div>
        <div style="text-align:right;">
            <div style="font-size:.78rem;color:var(--gray-500);font-weight:600;margin-bottom:.1rem;text-align:right;">Net Balance</div>
            <div class="summary-amount" style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1.2;">
                {{ number_format($netBalance, $netBalance == round($netBalance) ? 0 : 2) }}
            </div>
        </div>
    </div>
</div>

{{-- ══ 5. TRANSACTIONS TABLE ══ --}}
<style>
/* Show action icons on row hover */
#transactions-table tbody tr:hover .txn-actions { opacity: 1 !important; }
#transactions-table tbody tr { transition: background .1s; }
.txn-actions-cell { white-space: nowrap; text-align: right; padding-right: 0.75rem !important; }
</style>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;font-size:0.875rem;color:var(--gray-600);flex-wrap:wrap;gap:0.5rem;">
    <div id="custom-table-info" style="font-size: 0.85rem; color: #475569; font-weight: 500;">Showing 0 - 0 of 0 entries</div>
    <div style="display:flex;align-items:center;gap:8px;">
        <select id="custom-page-select" style="padding:5px 28px 5px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:0.8125rem;font-weight:500;color:#334155;background:#fff;appearance:none;-webkit-appearance:none;background-image:url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;);background-position:right 8px center;background-repeat:no-repeat;background-size:1.1rem;cursor:pointer;outline:none;">
            <option value="0">Page 1</option>
        </select>
        <span style="color:#64748b;font-size:0.8125rem;font-weight:500;">of <span id="custom-total-pages">1</span></span>
        <button id="custom-prev-btn" style="width:32px;height:32px;border:1px solid #d1d5db;background:#fff;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;outline:none;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button id="custom-next-btn" style="width:32px;height:32px;border:1px solid #d1d5db;background:#fff;border-radius:6px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;outline:none;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        </button>
    </div>
</div>

<div class="card" style="box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 1.5rem;">
    <div class="card-body" style="padding:0;">
        <table id="transactions-table" class="table" style="width:100%;">
            <thead>
                <tr style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="width:36px; padding: 12px 16px;"><input type="checkbox" id="select-all-checkbox"></th>
                    <th style="padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Date &amp; Time</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Details</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Category</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Mode</th>
                    <th style="padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Bill</th>
                    <th style="text-align:right; padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Amount</th>
                    <th style="text-align:right; padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.8125rem;">Balance</th>
                    <th style="width:70px; padding: 12px 16px;"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>


    <!-- Right Side Transaction Detail Modal -->
    <div id="transaction-detail-modal" class="transaction-detail-modal" style="display: none;">
        <!-- Modal Backdrop -->
        <div class="modal-backdrop" onclick="closeTransactionDetail()"></div>

        <!-- Modal Content -->
        <div class="modal-content" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="modal-header">
                <h3 id="detail-title">Transaction Details</h3>
                <button type="button" onclick="closeTransactionDetail()" class="close-btn">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Transaction Info -->
                <div class="detail-section">
                    <h4>Transaction Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Type</label>
                            <span id="detail-type" class="badge"></span>
                        </div>
                        <div class="detail-item">
                            <label>Amount</label>
                            <span id="detail-amount" class="amount"></span>
                        </div>
                        <div class="detail-item">
                            <label>Date</label>
                            <span id="detail-date"></span>
                        </div>
                        <div class="detail-item">
                            <label>Status</label>
                            <span id="detail-status" class="badge"></span>
                        </div>
                        <div class="detail-item">
                            <label>Category</label>
                            <span id="detail-category"></span>
                        </div>
                        <div class="detail-item">
                            <label>Description</label>
                            <span id="detail-description"></span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Section -->
                <div class="detail-section" id="receipt-section" style="display: none;">
                    <h4>Receipt</h4>
                    <div class="receipt-container">
                        <a id="receipt-link" href="#" target="_blank" class="btn btn-sm btn-secondary">View Receipt</a>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="detail-section">
                    <h4>Activity Timeline</h4>
                    <div id="activity-timeline" class="timeline">
                        <!-- Timeline items will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Sticky Action Buttons -->
            <div class="modal-footer" style="
                    display: flex;
                    justify-content: flex-end;
                    gap: 0.75rem;
                    border-top: 1px solid #ddd;
                    padding: 1rem;
                    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
                    background: #fff;
                    position: sticky;
                    bottom: 0;">
                <div class="detail-actions">
                    @if($bookRole !== 'employee')
                        <button id="edit-transaction-btn" class="btn btn-primary" onclick="editTransactionFromDetail()">
                            Edit Transaction
                        </button>
                        <button id="delete-transaction-btn" class="btn btn-danger" onclick="deleteTransactionFromDetail()">
                            Delete Transaction
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <x-modal name="add-transaction" :show="false" class="modal-hidden">
        <div style="padding: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">
                Add Transaction
            </h3>
            <form id="transaction-form" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:.875rem;">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <input type="hidden" name="return_to" value="{{ route('books.show', $book) }}">
                {{-- type is set by whichever button opens the modal --}}
                <input type="hidden" id="type" name="type" value="income">

                {{-- Row 1: Amount + Date --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.875rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="amount" class="form-label">Amount <span style="color:var(--danger-color);">*</span></label>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01"
                               class="form-input" placeholder="0.00" required />
                        <x-input-error :messages="$errors->get('amount')" class="form-error" />
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="transaction_date" class="form-label">Date &amp; Time <span style="color:var(--danger-color);">*</span></label>
                        <input id="transaction_date" name="transaction_date" type="datetime-local"
                               class="form-input" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                        <x-input-error :messages="$errors->get('transaction_date')" class="form-error" />
                    </div>
                </div>

                {{-- Contact Name with autocomplete --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label for="contact_name" class="form-label">Contact Name</label>
                    <div style="position:relative;">
                        <input id="contact_name" name="contact_name" type="text"
                               class="form-input"
                               placeholder="Type to search or add a new contact…"
                               autocomplete="off"
                               oninput="searchContacts('contact_name','contact_suggestions')"/>
                        <div id="contact_suggestions"
                             style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;
                                    background:#fff;border:1px solid var(--gray-300);border-top:none;
                                    border-radius:0 0 6px 6px;max-height:180px;overflow-y:auto;
                                    box-shadow:0 4px 8px rgba(0,0,0,.08);">
                        </div>
                    </div>
                    <p style="font-size:.75rem;color:var(--gray-400);margin-top:.25rem;">
                        Pick an existing contact or type a new name.
                    </p>
                </div>

                {{-- Row 2: Category + Mode --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.875rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="category_id" class="form-label">Category</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label for="new_category" class="form-label">Or New Category</label>
                        <input id="new_category" name="new_category" type="text"
                               class="form-input" placeholder="Add new…" />
                    </div>
                </div>

                {{-- Payment Mode --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label for="mode" class="form-label">Payment Mode</label>
                    <input id="mode" name="mode" type="text" class="form-input" placeholder="e.g. Cash, Bank, UPI…" />
                </div>

                {{-- Description --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="2"
                              class="form-input" placeholder="Optional notes…"></textarea>
                </div>

                {{-- Receipt --}}
                <div class="form-group" style="margin-bottom:0;">
                    <label for="receipt" class="form-label">Receipt (optional)</label>
                    <input id="receipt" name="receipt" type="file"
                           accept="image/*,application/pdf" class="form-input" style="padding:.4rem;" />
                </div>

                <div style="display:flex;justify-content:flex-end;gap:.625rem;border-top:1px solid var(--gray-200);
                            padding:1rem 0 0;position:sticky;bottom:0;background:#fff;padding-top:.875rem;">
                    <button type="button" @click="$dispatch('close-modal','add-transaction')" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" id="submit-btn" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Edit Transaction Modal -->
    <x-modal name="edit-transaction" :show="false" class="modal-hidden">
        <div style="padding:1.5rem 1.75rem 1.75rem;">
            <h3 style="font-size:1.0625rem;font-weight:700;color:var(--gray-900);margin:0 0 1.25rem;">Edit Entry</h3>

            <form id="edit-transaction-form" method="POST" enctype="multipart/form-data"
                  style="display:flex;flex-direction:column;gap:1.125rem;">
                @csrf
                @method('PUT')
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <input type="hidden" id="edit_transaction_id" name="transaction_id" value="">
                <input type="hidden" id="edit_type" name="type" value="income">

                {{-- Toggle --}}
                <div style="display:flex;gap:.5rem;">
                    <button type="button" id="edit-cash-in-btn" onclick="setEditType('income')"
                            style="padding:.45rem 1.25rem;border-radius:50px;border:1.5px solid var(--success-color);background:var(--success-color);font-size:.875rem;font-weight:600;color:#fff;cursor:pointer;transition:all .15s;">
                        Cash In
                    </button>
                    <button type="button" id="edit-cash-out-btn" onclick="setEditType('expense')"
                            style="padding:.45rem 1.25rem;border-radius:50px;border:1.5px solid var(--gray-300);background:#fff;font-size:.875rem;font-weight:600;color:var(--gray-700);cursor:pointer;transition:all .15s;">
                        Cash Out
                    </button>
                </div>

                {{-- Date + Time row --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;align-items:end;">
                    <div>
                        <label style="font-size:.72rem;font-weight:600;color:var(--gray-700);display:block;margin-bottom:.15rem;">
                            Date <span style="color:var(--danger-color);">*</span>
                        </label>
                        <div style="display:flex;align-items:center;gap:.3rem;border:1.5px solid var(--gray-300);border-radius:6px;padding:.3rem .45rem;background:#fff;">
                            <svg width="13" height="13" fill="none" stroke="var(--primary-color)" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/>
                                <path stroke-linecap="round" stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                            <input id="edit_date_only" name="date_only" type="date"
                                   style="border:none;outline:none;font-size:.78rem;font-weight:600;color:var(--gray-900);background:transparent;width:100%;font-family:inherit;"
                                   required />
                        </div>
                    </div>
                    <div>
                        <label style="font-size:.72rem;font-weight:600;color:var(--gray-700);display:block;margin-bottom:.15rem;">Time</label>
                        <div style="display:flex;align-items:center;gap:.3rem;border:1.5px solid var(--gray-300);border-radius:6px;padding:.3rem .45rem;background:#fff;">
                            <svg width="13" height="13" fill="none" stroke="var(--gray-500)" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                                <path stroke-linecap="round" stroke-width="2" d="M12 7v5l3 3"/>
                            </svg>
                            <input id="edit_time_only" name="time_only" type="time"
                                   style="border:none;outline:none;font-size:.78rem;color:var(--gray-700);background:transparent;width:100%;font-family:inherit;" />
                        </div>
                    </div>
                </div>
                {{-- Hidden combined datetime field --}}
                <input type="hidden" id="edit_transaction_date" name="transaction_date" required />

                {{-- Amount --}}
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.15rem;">
                        <label style="font-size:.72rem;font-weight:600;color:var(--gray-700);">
                            Amount <span style="color:var(--danger-color);">*</span>
                        </label>
                        <svg width="13" height="13" fill="none" stroke="var(--gray-400)" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9" stroke-width="1.5"/>
                            <path stroke-linecap="round" stroke-width="1.5" d="M12 8v4m0 4h.01"/>
                        </svg>
                    </div>
                    <input id="edit_amount" name="amount" type="number" step="0.01" min="0.01"
                           style="width:100%;padding:.38rem .55rem;border:1.5px solid var(--primary-color);border-radius:6px;font-size:.85rem;font-weight:600;color:var(--gray-900);outline:none;box-sizing:border-box;font-family:inherit;background:#fff;"
                           placeholder="Amount" required />
                </div>

                {{-- Contact Name --}}
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.15rem;">
                        <label style="font-size:.72rem;font-weight:600;color:var(--gray-700);">Contact Name</label>
                        <svg width="13" height="13" fill="none" stroke="var(--primary-color)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                        </svg>
                    </div>
                    <div style="position:relative;">
                        <div style="display:flex;align-items:center;border:1.5px solid var(--gray-300);border-radius:6px;background:#fff;overflow:hidden;">
                            <input id="edit_contact_name" name="contact_name" type="text"
                                   style="flex:1;padding:.35rem .5rem;border:none;outline:none;font-size:.78rem;color:var(--gray-900);background:transparent;font-family:inherit;"
                                   placeholder="Contact..."
                                   autocomplete="off"
                                   oninput="searchContacts('edit_contact_name','edit_contact_suggestions')"/>
                            <button type="button" onclick="document.getElementById('edit_contact_name').value=''"
                                    style="padding:0 .35rem;border:none;background:transparent;cursor:pointer;color:var(--gray-400);font-size:.8rem;line-height:1;">✕</button>
                            <div style="width:1px;height:16px;background:var(--gray-200);"></div>
                            <button type="button" onclick="toggleEditContactDropdown()"
                                    style="padding:0 .45rem;border:none;background:transparent;cursor:pointer;color:var(--gray-500);">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                        <div id="edit_contact_suggestions"
                             style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;
                                    background:#fff;border:1px solid var(--gray-300);border-top:none;
                                    border-radius:0 0 6px 6px;max-height:140px;overflow-y:auto;
                                    box-shadow:0 4px 8px rgba(0,0,0,.08);">
                        </div>
                    </div>
                </div>

                {{-- Remarks --}}
                <div>
                    <label style="font-size:.75rem;font-weight:600;color:var(--gray-700);display:block;margin-bottom:.2rem;">Remarks</label>
                    <textarea id="edit_description" name="description" rows="2"
                              style="width:100%;padding:.4rem .6rem;border:1.5px solid var(--gray-300);border-radius:6px;font-size:.8rem;color:var(--gray-900);resize:vertical;outline:none;box-sizing:border-box;font-family:inherit;background:#fff;transition:border-color .15s;"
                              onfocus="this.style.borderColor='var(--primary-color)'"
                              onblur="this.style.borderColor='var(--gray-300)'"
                              placeholder="Add remarks…"></textarea>
                </div>

                {{-- Category + Payment Mode --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                    {{-- Category --}}
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.2rem;">
                            <label style="font-size:.75rem;font-weight:600;color:var(--gray-700);">Category</label>
                            <svg width="13" height="13" fill="none" stroke="var(--primary-color)" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <div style="position:relative;">
                            <div style="display:flex;align-items:center;border:1.5px solid var(--gray-300);border-radius:6px;background:#fff;overflow:hidden;">
                                <input id="edit_category_search" type="text"
                                       style="flex:1;padding:.4rem .55rem;border:none;outline:none;font-size:.78rem;color:var(--gray-900);background:transparent;font-family:inherit;"
                                       placeholder="Search or Select"
                                       autocomplete="off"
                                       oninput="filterEditCategories(this.value)" />
                                <button type="button" onclick="toggleEditCategoryDropdown()"
                                        style="padding:0 .45rem;border:none;background:transparent;cursor:pointer;color:var(--gray-500);">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                            <input type="hidden" id="edit_category_id" name="category_id" value="">
                            <div id="edit_category_dropdown"
                                 style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;
                                        background:#fff;border:1px solid var(--gray-300);border-top:none;
                                        border-radius:0 0 6px 6px;max-height:140px;overflow-y:auto;
                                        box-shadow:0 4px 8px rgba(0,0,0,.08);">
                                <div onclick="selectEditCategory('','')"
                                     style="padding:.4rem .6rem;font-size:.78rem;color:var(--gray-500);cursor:pointer;border-bottom:1px solid var(--gray-100);"
                                     onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='#fff'">
                                    None
                                </div>
                                @foreach($categories as $category)
                                    <div onclick="selectEditCategory('{{ $category->id }}','{{ addslashes($category->name) }}')"
                                         data-cat-id="{{ $category->id }}" data-cat-name="{{ $category->name }}"
                                         style="padding:.4rem .6rem;font-size:.78rem;color:var(--gray-800);cursor:pointer;"
                                         onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='#fff'">
                                        {{ $category->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Payment Mode --}}
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.2rem;">
                            <label style="font-size:.75rem;font-weight:600;color:var(--gray-700);">Payment Mode</label>
                            <svg width="13" height="13" fill="none" stroke="var(--primary-color)" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <div style="position:relative;">
                            <div style="display:flex;align-items:center;border:1.5px solid var(--gray-300);border-radius:6px;background:#fff;overflow:hidden;">
                                <input id="edit_mode_display" type="text"
                                       style="flex:1;padding:.4rem .55rem;border:none;outline:none;font-size:.78rem;color:var(--gray-900);background:transparent;font-family:inherit;"
                                       placeholder="Select mode"
                                       autocomplete="off"
                                       oninput="filterEditModes(this.value)" />
                                <button type="button" onclick="document.getElementById('edit_mode_display').value='';document.getElementById('edit_mode').value=''"
                                        style="padding:0 .35rem;border:none;background:transparent;cursor:pointer;color:var(--gray-400);font-size:.85rem;line-height:1;">✕</button>
                                <div style="width:1px;height:16px;background:var(--gray-200);"></div>
                                <button type="button" onclick="toggleEditModeDropdown()"
                                        style="padding:0 .45rem;border:none;background:transparent;cursor:pointer;color:var(--gray-500);">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                            <input type="hidden" id="edit_mode" name="mode" value="">
                            <div id="edit_mode_dropdown"
                                 style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;
                                        background:#fff;border:1px solid var(--gray-300);border-top:none;
                                        border-radius:0 0 6px 6px;max-height:130px;overflow-y:auto;
                                        box-shadow:0 4px 8px rgba(0,0,0,.08);">
                                @foreach($modes as $m)
                                    <div onclick="selectEditMode('{{ $m }}')"
                                         data-mode="{{ $m }}"
                                         style="padding:.4rem .6rem;font-size:.78rem;color:var(--gray-800);cursor:pointer;"
                                         onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='#fff'">
                                        {{ ucfirst($m) }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attach Bills --}}
                <div>
                    <label style="display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .75rem;border:1.5px solid var(--gray-300);border-radius:6px;cursor:pointer;font-size:.78rem;font-weight:600;color:var(--primary-color);background:#fff;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Attach Bills
                        <input id="edit_receipt" name="receipt" type="file"
                               accept="image/*,application/pdf" style="display:none;" multiple />
                    </label>
                    <p style="font-size:.72rem;color:#16a34a;margin-top:.2rem;">Attach up to 4 images or PDF files</p>
                    <div id="current-receipt" style="margin-top:.3rem;display:none;">
                        <span style="font-size:.75rem;color:var(--gray-600);">
                            Current:
                            <a id="edit-receipt-link" href="#" target="_blank"
                               style="color:var(--primary-color);text-decoration:none;">View receipt</a>
                        </span>
                    </div>
                </div>

                <div id="edit-more-fields-banner"
                     style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;
                            background:#eef2ff;border-radius:8px;padding:.55rem .75rem;position:relative;">
                    <div style="display:flex;align-items:center;gap:.35rem;">
                        <span style="font-size:.78rem;font-weight:700;color:var(--primary-color);">Add more fields</span>
                        <span style="background:#16a34a;color:#fff;font-size:.6rem;font-weight:700;padding:.1rem .3rem;border-radius:4px;letter-spacing:.03em;">New</span>
                    </div>
                    <button type="button"
                            style="padding:.3rem .85rem;background:var(--primary-color);color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;"
                            onclick="addCustomField()">Add Field</button>
                    <button type="button" onclick="document.getElementById('edit-more-fields-banner').style.display='none'"
                            style="position:absolute;top:.25rem;right:.25rem;width:18px;height:18px;border-radius:50%;border:1px solid var(--gray-400);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.65rem;color:var(--gray-500);line-height:1;padding:0;">✕</button>
                </div>

                <div id="custom-fields-container" class="form-group"></div>

                {{-- Form Footer --}}
                <div style="display:flex;justify-content:flex-end;gap:.625rem;border-top:1px solid var(--gray-200);
                            padding:1rem 0 0;position:sticky;bottom:0;background:#fff;padding-top:.875rem;margin-top:.5rem;">
                    <button type="button" @click="$dispatch('close-modal','edit-transaction')" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </x-modal>


    <!-- Manage Users Modal -->
    <x-modal name="manage-users" :show="false" class="modal-hidden">
        <div style="padding: 1.5rem; max-width: 800px;">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Manage Book Users</h3>

            <!-- Add User Section -->
            <div style="background: var(--gray-50); padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                <h4 style="font-size: 1rem; font-weight: 600; color: var(--gray-800); margin-bottom: 1rem;">Add User to Book</h4>
                <form id="add-user-form" style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Searchable User Input -->
                    <div class="form-group">
                        <label for="user_search" class="form-label">Search User</label>
                        <div style="position: relative;">
                            <input type="text" id="user_search" placeholder="Type name or email to search..." class="form-input" autocomplete="off" />
                            <input type="hidden" id="selected_user_id" name="user_id" value="" />
                            <div id="user_search_results" style="
                                position: absolute;
                                top: 100%;
                                left: 0;
                                right: 0;
                                background: white;
                                border: 1px solid var(--gray-300);
                                border-top: none;
                                border-radius: 0 0 var(--border-radius) var(--border-radius);
                                max-height: 200px;
                                overflow-y: auto;
                                z-index: 1000;
                                display: none;
                                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                            "></div>
                        </div>
                        <div id="selected_user_display" style="
                            margin-top: 0.5rem;
                            padding: 0.75rem;
                            background: var(--success-color);
                            color: white;
                            border-radius: var(--border-radius);
                            font-size: 0.875rem;
                            display: none;
                            position: relative;
                        ">
                            <span id="selected_user_text"></span>
                            <button type="button" onclick="clearSelectedUser()" style="
                                position: absolute;
                                top: 0.5rem;
                                right: 0.75rem;
                                background: none;
                                border: none;
                                color: white;
                                cursor: pointer;
                                font-size: 1.25rem;
                                line-height: 1;
                            ">×</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_role" class="form-label">Role</label>
                        <select id="user_role" name="role" class="form-select" required>
                            <option value="operator" selected>Operator - Can add & edit transactions</option>
                            <option value="admin">Admin - Can manage book, members, and transactions</option>
                            <option value="viewer">Viewer - Read-only access to transactions & reports</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" disabled id="add-user-btn">Add User</button>
                </form>
            </div>

            <!-- Current Users List -->
            <div>
                <h4 style="font-size: 1rem; font-weight: 600; color: var(--gray-800); margin-bottom: 1rem;">Current Users</h4>
                <div id="users-list" style="space-y: 0.75rem;">
                    <!-- Users will be loaded here -->
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--gray-200); margin-top: 1.5rem;">
                <button type="button" @click="$dispatch('close-modal', 'manage-users')" class="btn btn-secondary">
                    Close
                </button>
            </div>
        </div>
    </x-modal>

    <script>
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let dataTable;
        let currentTransactionId = null;

        function addCustomField() {
            const container = document.getElementById('custom-fields-container');
            const index = container.children.length + 1;
            const wrapper = document.createElement('div');
            wrapper.className = 'form-group';
            wrapper.style.marginTop = '.75rem';
            wrapper.innerHTML = `
                <label class="form-label" for="custom_field_${index}"
                       style="font-size:.8125rem;font-weight:600;color:var(--gray-700);display:block;margin-bottom:.35rem;">
                    Custom Field ${index}
                </label>
                <input type="text"
                       id="custom_field_${index}"
                       name="custom_fields[${index}]"
                       class="form-input"
                       placeholder="Enter field value..."
                       style="font-size:0.75rem;" />
            `;
            container.appendChild(wrapper);
        }

        // ── Wire up new Cash In / Cash Out buttons ──────────
        const cashInBtn  = document.getElementById('cash-in-btn');
        const cashOutBtn = document.getElementById('cash-out-btn');
        if (cashInBtn) {
            cashInBtn.addEventListener('click', function() {
                document.getElementById('transaction-form').reset();
                document.getElementById('type').value = 'income';
                document.getElementById('transaction_date').value = new Date().toISOString().slice(0,16);
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-transaction' }));
            });
        }
        if (cashOutBtn) {
            cashOutBtn.addEventListener('click', function() {
                document.getElementById('transaction-form').reset();
                document.getElementById('type').value = 'expense';
                document.getElementById('transaction_date').value = new Date().toISOString().slice(0,16);
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-transaction' }));
            });
        }

        // ── Contact autocomplete ──────────────────────────
        function searchContacts(inputId, dropdownId) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            const q = input.value.trim();

            if (q.length === 0) { dropdown.style.display = 'none'; return; }

            fetch(`{{ route('transactions.contacts') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                const contacts = data.contacts || [];
                if (contacts.length === 0) { dropdown.style.display = 'none'; return; }

                dropdown.innerHTML = contacts.map(c => `
                    <div onclick="pickContact('${inputId}','${dropdownId}','${c.replace(/'/g,"\\'")}')\"
                         style="padding:.6rem .875rem;cursor:pointer;font-size:.875rem;color:var(--gray-800);
                                border-bottom:1px solid var(--gray-100);"
                         onmouseover="this.style.background='var(--gray-50)'"
                         onmouseout="this.style.background='#fff'">
                        ${c}
                    </div>
                `).join('');
                dropdown.style.display = 'block';
            })
            .catch(() => { dropdown.style.display = 'none'; });
        }

        function pickContact(inputId, dropdownId, name) {
            document.getElementById(inputId).value = name;
            document.getElementById(dropdownId).style.display = 'none';
        }

        // Hide dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            ['contact_suggestions','edit_contact_suggestions'].forEach(function(id) {
                const el = document.getElementById(id);
                const inp = document.getElementById(id === 'contact_suggestions' ? 'contact_name' : 'edit_contact_name');
                if (el && inp && !el.contains(e.target) && e.target !== inp) {
                    el.style.display = 'none';
                }
            });
        });

        // ── reloadTable — called by pill filter onchange ──
        function reloadTable(resetPage = true) {
            if (dataTable) {
                if (resetPage) {
                    dataTable.page('first');
                }
                dataTable.ajax.reload(null, false);
                updateSummaryCards();
            }
        }

        // ── debounce for search input ─────────────────────
        let searchTimer;
        function debounceSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                if (dataTable) {
                    dataTable.page('first');
                    dataTable.ajax.reload(null, false);
                    updateSummaryCards();
                }
            }, 300);
        }

        function clearAllFilters() {
            ['filter-duration', 'filter-type', 'filter-contact', 'filter-member', 'filter-mode', 'filter-category', 'filter-search'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            reloadTable(true);
        }

        // Initialize DataTable
        $(document).ready(function() {
            dataTable = $('#transactions-table').DataTable({
                processing: false,
                serverSide: true,
                dom: 'rt', // Hide default DataTables search, page length and paginator
                ajax: {
                    url: '{{ route("books.transactions.data", $book) }}',
                    type: 'GET',
                    data: function(d) {
                        d.duration = document.getElementById('filter-duration')?.value || '';
                        d.type     = document.getElementById('filter-type')?.value     || '';
                        d.contact  = document.getElementById('filter-contact')?.value  || '';
                        d.member   = document.getElementById('filter-member')?.value   || '';
                        d.mode     = document.getElementById('filter-mode')?.value     || '';
                        d.category = document.getElementById('filter-category')?.value || '';
                        d.search   = document.getElementById('filter-search')?.value   || '';
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX error:', xhr.status, thrown, xhr.responseText);
                        showNotification('Failed to load transactions: ' + (thrown || 'server error'), 'error');
                    }
                },
                columns: [
                    {
                        data: 'id', name: 'id', orderable: false, searchable: false,
                        render: function(data) {
                            return '<input type="checkbox" class="transaction-checkbox" value="' + data + '">';
                        }
                    },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'details', name: 'description', orderable: false,
                      render: function(data) { return data || '—'; } },
                    { data: 'category',         name: 'category.name' },
                    { data: 'mode',             name: 'mode' },
                    { data: 'bill',             name: 'image_path', orderable: false, searchable: false },
                    { data: 'amount',           name: 'amount', className: 'text-right' },
                    { data: 'balance',          name: 'balance', orderable: false, searchable: false, className: 'text-right',
                      render: function(data) { return data || '—'; } },
                    { data: 'actions',          name: 'actions', orderable: false, searchable: false,
                      className: 'txn-actions-cell', render: function(data) { return data || ''; } }
                ],
                order: [[1, 'desc']],
                pageLength: 50,
                responsive: false,
                autoWidth: true,
                scrollX: true,
                fixedHeader: true,
                language: {
                    processing: 'Loading transactions...',
                    emptyTable: 'No transactions found',
                    zeroRecords: 'No matching transactions found'
                },
                initComplete: function() {
                    const api = this.api();

                    // Row click → detail panel (only if not clicking action buttons)
                    $('#transactions-table tbody').on('click', 'tr', function(e) {
                        if ($(e.target).closest('.txn-actions, button, a, input[type="checkbox"]').length) return;
                        const data = api.row(this).data();
                        if (data && data.id) { showTransactionDetail(data.id); }
                    });

                    // Show/hide action icons on row hover
                    $('#transactions-table tbody').on('mouseenter', 'tr', function() {
                        $(this).find('.txn-actions').css('opacity', '1');
                    }).on('mouseleave', 'tr', function() {
                        $(this).find('.txn-actions').css('opacity', '0');
                    });

                    // Prevent checkbox clicks bubbling to row click
                    $('#transactions-table tbody').on('click', 'input[type="checkbox"]', function(e) {
                        e.stopPropagation();
                    });

                    // Select All
                    $('#select-all-checkbox').on('change', function() {
                        api.rows({ search: 'applied' }).nodes()
                           .to$().find('input[type="checkbox"]')
                           .prop('checked', this.checked);
                        updateSelectedCount();
                    });

                    // Individual checkbox
                    $('#transactions-table tbody').on('change', 'input[type="checkbox"]', function() {
                        const sel = $('#select-all-checkbox').get(0);
                        if (!this.checked && sel && sel.checked && ('indeterminate' in sel)) {
                            sel.indeterminate = true;
                        }
                        updateSelectedCount();
                    });

                    updateSummaryCards();
                },
                preDrawCallback: function() {
                    $('#selected-count').text('0');
                    $('#bulk-delete-btn').hide();
                },
                drawCallback: function() { 
                    updateSelectedCount(); 
                    
                    const api = this.api();
                    const info = api.page.info();
                    
                    // Update custom pagination text
                    const start = info.recordsDisplay === 0 ? 0 : info.start + 1;
                    const end = info.end;
                    const total = info.recordsDisplay;
                    $('#custom-table-info').text(`Showing ${start} - ${end} of ${total} entries`);
                    
                    // Update dropdown select and total pages label
                    const totalPages = info.pages;
                    $('#custom-total-pages').text(totalPages || 1);
                    
                    let selectHtml = '';
                    for (let i = 0; i < totalPages; i++) {
                        selectHtml += `<option value="${i}" ${info.page === i ? 'selected' : ''}>Page ${i + 1}</option>`;
                    }
                    $('#custom-page-select').html(selectHtml || '<option value="0">Page 1</option>');
                    
                    // Enable/disable navigation buttons
                    $('#custom-prev-btn').prop('disabled', !api.page.hasPrevious()).css('opacity', api.page.hasPrevious() ? 1 : 0.5);
                    $('#custom-next-btn').prop('disabled', !api.page.hasNext()).css('opacity', api.page.hasNext() ? 1 : 0.5);
                }
            });

            // Bind filter-search input listener to debounceSearch
            $('#filter-search').on('input', function() {
                debounceSearch();
            });

            // Custom pagination control listeners
            $('#custom-page-select').on('change', function() {
                if (dataTable) {
                    dataTable.page(parseInt($(this).val())).draw('page');
                }
            });
            $('#custom-prev-btn').on('click', function() {
                if (dataTable && dataTable.page.info().page > 0) {
                    dataTable.page('previous').draw('page');
                }
            });
            $('#custom-next-btn').on('click', function() {
                if (dataTable && dataTable.page.info().page < dataTable.page.info().pages - 1) {
                    dataTable.page('next').draw('page');
                }
            });

            // Select All click
            $('#select-all-checkbox').on('click', function() {
                const rows = dataTable.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
                updateSelectedCount();
            });

            // Individual checkbox changes
            $('#transactions-table tbody').on('change', 'input[type="checkbox"]', function() {
                if (!this.checked) {
                    const sel = $('#select-all-checkbox').get(0);
                    if (sel && sel.checked && ('indeterminate' in sel)) sel.indeterminate = true;
                }
                updateSelectedCount();
            });

            dataTable.on('draw', function() { updateSelectedCount(); });

            document.getElementById('new_category').addEventListener('input', function() {
                const categorySelect = document.getElementById('category_id');
                if (this.value.trim() !== '') {
                    categorySelect.disabled = true;
                } else {
                    categorySelect.disabled = false;
                }
            });

            document.getElementById('category_id').addEventListener('change', function() {
                const newCategoryInput = document.getElementById('new_category');
                if (this.value !== '') {
                    newCategoryInput.disabled = true;
                    newCategoryInput.value = '';
                } else {
                    newCategoryInput.disabled = false;
                }
            });

            document.getElementById('edit_new_category').addEventListener('input', function() {
                const editCategorySelect = document.getElementById('edit_category_id');
                if (this.value.trim() !== '') {
                    editCategorySelect.disabled = true;
                } else {
                    editCategorySelect.disabled = false;
                }
            });

            document.getElementById('edit_category_id').addEventListener('change', function() {
                const editNewCategoryInput = document.getElementById('edit_new_category');
                if (this.value !== '') {
                    editNewCategoryInput.disabled = true;
                    editNewCategoryInput.value = '';
                } else {
                    editNewCategoryInput.disabled = false;
                }
            });
        });

        function updateSelectedCount() {
            const selectedCount = $('#transactions-table tbody input[type="checkbox"]:checked').length;
            $('#selected-count').text(selectedCount);

            if (selectedCount > 0) {
                $('#bulk-delete-btn').show();
            } else {
                $('#bulk-delete-btn').hide();
            }
        }

        function bulkDeleteTransactions() {
            const selectedIds = [];
            $('#transactions-table tbody input[type="checkbox"]:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                showNotification('Please select at least one transaction to delete.', 'error');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selectedIds.length} selected transactions? This action cannot be undone.`)) {
                fetch('{{ route("transactions.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        dataTable.ajax.reload();
                        updateSummaryCards();
                    } else {
                        showNotification(data.message || 'An error occurred.', 'error');
                    }
                })
                .catch(error => {
                    showNotification('An error occurred while deleting transactions.', 'error');
                });
            }
        }

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Show transaction detail in right modal
        function showTransactionDetail(id) {
            currentTransactionId = id;

            fetch(`/transactions/${id}/detail`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    populateTransactionDetail(data.transaction, data.activities);
                    openTransactionDetailModal();
                } else {
                    showNotification('Error loading transaction details: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading transaction details: ' + error.message, 'error');
            });
        }

        // Populate transaction detail modal
        function populateTransactionDetail(transaction, activities) {
            // Set basic info
            document.getElementById('detail-title').textContent = `Transaction #${transaction.id}`;

            // Type badge
            const typeBadge = document.getElementById('detail-type');
            typeBadge.textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
            typeBadge.className = `badge ${transaction.type === 'income' ? 'badge-success' : 'badge-danger'}`;

            // Amount
            const amountElement = document.getElementById('detail-amount');
            amountElement.textContent = `{{ $book->currency }} ${parseFloat(transaction.amount).toFixed(2)}`;
            amountElement.style.color = transaction.type === 'income' ? 'var(--success-color)' : 'var(--danger-color)';

            // Date
            document.getElementById('detail-date').textContent = new Date(transaction.transaction_date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Status badge
            const statusBadge = document.getElementById('detail-status');
            statusBadge.textContent = transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1);
            statusBadge.className = `badge ${
                transaction.status === 'approved' ? 'badge-success' :
                (transaction.status === 'pending' ? 'badge-warning' : 'badge-danger')
            }`;

            // Category
            document.getElementById('detail-category').textContent = transaction.category?.name || '—';

            // Description
            document.getElementById('detail-description').textContent = transaction.description || 'No description';

            // Receipt section
            const receiptSection = document.getElementById('receipt-section');
            if (transaction.image_path) {
                receiptSection.style.display = 'block';
                document.getElementById('receipt-link').href = `/transactions/${transaction.id}/receipt`;
            } else {
                receiptSection.style.display = 'none';
            }

            // Activity timeline
            populateActivityTimeline(activities);

            // Action buttons
            const bookRole = '<?php echo $bookRole; ?>';

            // Only show buttons for non-employees
            if (bookRole !== 'employee') {
                document.getElementById('edit-transaction-btn').style.display = 'inline-block';
                document.getElementById('delete-transaction-btn').style.display = 'inline-block';
                // Employees can only edit/delete if they created the transaction
                if (bookRole === 'employee') {
                    if (transaction.user.id === {{ auth()->id() }}) {
                        document.getElementById('edit-transaction-btn').onclick = () => editTransactionFromDetail();
                        document.getElementById('delete-transaction-btn').onclick = () => deleteTransactionFromDetail();
                    } else {
                        // Hide buttons if employee didn't create it
                        document.getElementById('edit-transaction-btn').style.display = 'none';
                        document.getElementById('delete-transaction-btn').style.display = 'none';
                    }
                } else {
                    // Other roles can always edit/delete
                    document.getElementById('edit-transaction-btn').onclick = () => editTransactionFromDetail();
                    document.getElementById('delete-transaction-btn').onclick = () => deleteTransactionFromDetail();
                }
            }

        }

        // Populate activity timeline
        function populateActivityTimeline(activities) {
            const timeline = document.getElementById('activity-timeline');
            timeline.innerHTML = '';

            if (!activities || activities.length === 0) {
                timeline.innerHTML = '<p style="color: var(--gray-500); font-style: italic;">No activity recorded</p>';
                return;
            }

            activities.forEach(activity => {
                const timelineItem = document.createElement('div');
                timelineItem.className = `timeline-item ${activity.type}`;

                timelineItem.innerHTML = `
                    <div class="timeline-content">
                        <div class="timeline-title">${activity.title}</div>
                        <div class="timeline-description">${activity.description}</div>
                        <div class="timeline-meta">
                            <span>By ${activity.user_name}</span>
                            <span>${new Date(activity.created_at).toLocaleString()}</span>
                        </div>
                    </div>
                `;

                timeline.appendChild(timelineItem);
            });
        }

        // Open transaction detail modal
        function openTransactionDetailModal() {
            const modal = document.getElementById('transaction-detail-modal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        // Close transaction detail modal
        function closeTransactionDetail() {
            const modal = document.getElementById('transaction-detail-modal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                currentTransactionId = null;
            }, 300);
        }

        // Edit transaction from detail modal
        function editTransactionFromDetail() {
            if (currentTransactionId) {
                closeTransactionDetail();
                editTransaction(currentTransactionId);
            }
        }

        // Delete transaction from detail modal
        function deleteTransactionFromDetail() {
            if (currentTransactionId) {
                closeTransactionDetail();
                deleteTransaction(currentTransactionId);
            }
        }

        // Update summary cards based on filters
        function updateSummaryCards() {
            const filters = {
                duration: document.getElementById('filter-duration')?.value || '',
                type:     document.getElementById('filter-type')?.value     || '',
                member:   document.getElementById('filter-member')?.value   || '',
                mode:     document.getElementById('filter-mode')?.value     || '',
                category: document.getElementById('filter-category')?.value || '',
                search:   document.getElementById('filter-search')?.value   || ''
            };

            fetch('{{ route("books.summary", $book) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filters)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update summary cards with filtered data
                    updateSummaryCard('.cash-in-card', data.total_income);
                    updateSummaryCard('.cash-out-card', data.total_expense);
                    updateSummaryCard('.net-balance-card', data.net_balance);
                }
            })
            .catch(error => {
                showNotification('Error updating summary cards', 'error');
            });
        }

        function updateSummaryCard(selector, amount) {
            const card = document.querySelector(selector);
            if (card) {
                const amountElement = card.querySelector('.summary-amount');
                if (amountElement) {
                    // Force US-style formatting to match PHP number_format
                    amountElement.textContent = `{{ $book->currency }} ${Number(amount).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    })}`;
                }
            }
        }

        // Alpine.js integration functions
        function openCashInModal() {
            const form = document.getElementById('transaction-form');
            form.reset();
            document.getElementById('type').value = 'income';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-transaction' }));
        }

        function openCashOutModal() {
            const form = document.getElementById('transaction-form');
            form.reset();
            document.getElementById('type').value = 'expense';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'add-transaction' }));
        }

        function closeAddTransactionModal() {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'add-transaction' }));
        }

        // Alpine.js availability check
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                if (window.Alpine) {
                    console.log('Alpine.js is available');
                } else {
                    console.log('Alpine.js is NOT available');
                }
            }, 1000);
        });

        // Add transaction via AJAX
        document.getElementById('transaction-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.textContent;

            submitBtn.textContent = 'Saving...';
            submitBtn.disabled = true;

            fetch('{{ route("transactions.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal using Alpine.js dispatch
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'add-transaction'
                    }));

                    // Reset form
                    this.reset();

                    // Show success message
                    showNotification('Transaction added successfully!', 'success');

                    // Reload DataTable to show new transaction
                    dataTable.ajax.reload();

                    // Update summary cards
                    updateSummaryCards();
                } else {
                    showNotification(data.message || 'Error adding transaction', 'error');
                }
            })
            .catch(error => {
                showNotification('Error adding transaction', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });

        // Edit, Delete, and other transaction functions
        function editTransaction(id) {

            fetch(`/transactions/${id}/edit`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const transaction = data.transaction;

                    // Populate edit form
                    document.getElementById('edit_transaction_id').value = transaction.id;
                    document.getElementById('edit_amount').value = transaction.amount;
                    document.getElementById('edit_description').value = transaction.description || '';

                    // Split date and time into separate fields
                    const transactionDate = new Date(transaction.transaction_date);
                    const yyyy = transactionDate.getFullYear();
                    const mm   = String(transactionDate.getMonth() + 1).padStart(2, '0');
                    const dd   = String(transactionDate.getDate()).padStart(2, '0');
                    const hh   = String(transactionDate.getHours()).padStart(2, '0');
                    const min  = String(transactionDate.getMinutes()).padStart(2, '0');
                    document.getElementById('edit_date_only').value = `${yyyy}-${mm}-${dd}`;
                    document.getElementById('edit_time_only').value = `${hh}:${min}`;
                    document.getElementById('edit_transaction_date').value = `${yyyy}-${mm}-${dd}T${hh}:${min}`;

                    // Set Cash In / Cash Out toggle
                    setEditType(transaction.type || 'income');

                    // Contact name
                    document.getElementById('edit_contact_name').value = transaction.contact_name || '';

                    // Category — set hidden value and display text
                    document.getElementById('edit_category_id').value = transaction.category_id || '';
                    const catSearch = document.getElementById('edit_category_search');
                    if (catSearch) {
                        if (transaction.category_id) {
                            const catEl = document.querySelector(`#edit_category_dropdown [data-cat-id="${transaction.category_id}"]`);
                            catSearch.value = catEl ? catEl.dataset.catName : '';
                        } else {
                            catSearch.value = '';
                        }
                    }

                    // Mode — set hidden value and display text
                    const modeVal = transaction.mode || '';
                    document.getElementById('edit_mode').value = modeVal;
                    const modeDisplay = document.getElementById('edit_mode_display');
                    if (modeDisplay) {
                        modeDisplay.value = modeVal
                            ? modeVal.charAt(0).toUpperCase() + modeVal.slice(1)
                            : '';
                    }

                    // Handle current receipt
                    const currentReceipt = document.getElementById('current-receipt');
                    const receiptLink    = document.getElementById('edit-receipt-link');
                    if (transaction.image_path) {
                        if (receiptLink)    receiptLink.href            = `/transactions/${transaction.id}/receipt`;
                        if (currentReceipt) currentReceipt.style.display = 'block';
                    } else {
                        if (currentReceipt) currentReceipt.style.display = 'none';
                    }

                    // Open edit modal
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-transaction' }));
                } else {
                    showNotification('Error loading transaction data: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading transaction data: ' + error.message, 'error');
            });
        }

        // ── Edit modal helper functions ───────────────────────────────────

        function setEditType(type) {
            document.getElementById('edit_type').value = type;
            const cashInBtn  = document.getElementById('edit-cash-in-btn');
            const cashOutBtn = document.getElementById('edit-cash-out-btn');
            if (type === 'income') {
                cashInBtn.style.background  = 'var(--success-color)';
                cashInBtn.style.color       = '#fff';
                cashInBtn.style.borderColor = 'var(--success-color)';
                cashOutBtn.style.background  = '#fff';
                cashOutBtn.style.color       = 'var(--gray-700)';
                cashOutBtn.style.borderColor = 'var(--gray-300)';
            } else {
                cashOutBtn.style.background  = 'var(--danger-color)';
                cashOutBtn.style.color       = '#fff';
                cashOutBtn.style.borderColor = 'var(--danger-color)';
                cashInBtn.style.background  = '#fff';
                cashInBtn.style.color       = 'var(--gray-700)';
                cashInBtn.style.borderColor = 'var(--gray-300)';
            }
        }

        function toggleEditContactDropdown() {
            const input = document.getElementById('edit_contact_name');
            input.focus();
            searchContacts('edit_contact_name', 'edit_contact_suggestions');
        }

        function toggleEditCategoryDropdown() {
            const dd = document.getElementById('edit_category_dropdown');
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        }

        function filterEditCategories(q) {
            const items = document.querySelectorAll('#edit_category_dropdown [data-cat-id]');
            items.forEach(item => {
                item.style.display = item.dataset.catName.toLowerCase().includes(q.toLowerCase()) ? 'block' : 'none';
            });
            document.getElementById('edit_category_dropdown').style.display = 'block';
        }

        function selectEditCategory(id, name) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_search').value = name;
            document.getElementById('edit_category_dropdown').style.display = 'none';
        }

        function toggleEditModeDropdown() {
            const dd = document.getElementById('edit_mode_dropdown');
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        }

        function filterEditModes(q) {
            const items = document.querySelectorAll('#edit_mode_dropdown [data-mode]');
            items.forEach(item => {
                item.style.display = item.dataset.mode.toLowerCase().includes(q.toLowerCase()) ? 'block' : 'none';
            });
            document.getElementById('edit_mode_dropdown').style.display = 'block';
        }

        function selectEditMode(mode) {
            document.getElementById('edit_mode').value = mode;
            document.getElementById('edit_mode_display').value = mode.charAt(0).toUpperCase() + mode.slice(1);
            document.getElementById('edit_mode_dropdown').style.display = 'none';
        }



        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            ['edit_category_dropdown', 'edit_mode_dropdown'].forEach(function(id) {
                const dd = document.getElementById(id);
                if (dd && !dd.contains(e.target) && !e.target.closest('#' + id.replace('_dropdown','') + '_search, #edit_mode_display')) {
                    dd.style.display = 'none';
                }
            });
        });



        function deleteTransaction(id) {
            if (!confirm('Are you sure you want to delete this transaction?')) {
                return;
            }

            fetch(`/transactions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Transaction deleted successfully!', 'success');
                    dataTable.ajax.reload();
                    updateSummaryCards();
                } else {
                    showNotification(data.message || 'Error deleting transaction', 'error');
                }
            })
            .catch(error => {
                showNotification('Error deleting transaction', 'error');
            });
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                color: white;
                font-weight: 500;
                z-index: 9999;
                max-width: 300px;
                background-color: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            `;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }



        // Edit transaction via AJAX
        document.getElementById('edit-transaction-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Combine date + time into hidden datetime field before creating FormData
            const d = document.getElementById('edit_date_only').value;
            const t = document.getElementById('edit_time_only').value || '00:00';
            document.getElementById('edit_transaction_date').value = d + 'T' + t;

            const transactionId = document.getElementById('edit_transaction_id').value;
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            // Disable submit button and show loading state
            submitBtn.textContent = 'Updating...';
            submitBtn.disabled = true;

            fetch(`/transactions/${transactionId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal using Alpine.js dispatch
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'edit-transaction'
                    }));

                    // Show success message
                    showNotification('Transaction updated successfully!', 'success');

                    // Reload DataTable to show updated transaction
                    dataTable.ajax.reload();

                    // Update summary cards
                    updateSummaryCards();
                } else {
                    showNotification(data.message || 'Error updating transaction', 'error');
                }
            })
            .catch(error => {
                showNotification('Error updating transaction: ' + error.message, 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });



        // Delete transaction function
        function deleteTransaction(id) {
            if (!confirm('Are you sure you want to delete this transaction?')) {
                return;
            }

            fetch(`/transactions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Transaction deleted successfully!', 'success');
                    // Reload DataTable to reflect deletion
                    dataTable.ajax.reload();
                    // Update summary cards
                    updateSummaryCards();
                } else {
                    showNotification(data.message || 'Error deleting transaction', 'error');
                }
            })
            .catch(error => {
                showNotification('Error deleting transaction', 'error');
            });
        }

        // Approve transaction function
        function approveTransaction(id) {
            fetch(`/transactions/${id}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Transaction approved successfully!', 'success');
                    // Reload DataTable to reflect status change
                    dataTable.ajax.reload();
                    // Update summary cards
                    updateSummaryCards();
                } else {
                    showNotification(data.message || 'Error approving transaction', 'error');
                }
            })
            .catch(error => {
                showNotification('Error approving transaction', 'error');
            });
        }

        // Reject transaction function
        function rejectTransaction(id) {
            fetch(`/transactions/${id}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Transaction rejected successfully!', 'success');
                    // Reload DataTable to reflect status change
                    dataTable.ajax.reload();
                    // Update summary cards
                    updateSummaryCards();
                } else {
                    showNotification(data.message || 'Error rejecting transaction', 'error');
                }
            })
            .catch(error => {
                showNotification('Error rejecting transaction', 'error');
            });
        }

        // Notification function
        function showNotification(message, type = 'success') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                color: white;
                font-weight: 500;
                z-index: 9999;
                max-width: 300px;
                background-color: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            `;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }

        // User Management Functions
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for manage users modal open event
            window.addEventListener('open-modal', function(event) {
                if (event.detail === 'manage-users') {
                    loadBookUsers();
                    initializeUserSearch();
                }
            });

            // Add user form submission
            document.getElementById('add-user-form').addEventListener('submit', function(e) {
                e.preventDefault();
                addUserToBook();
            });
        });

        function initializeUserSearch() {
            const searchInput = document.getElementById('user_search');
            const resultsDiv = document.getElementById('user_search_results');
            const selectedUserIdInput = document.getElementById('selected_user_id');
            const selectedUserDisplay = document.getElementById('selected_user_display');
            const addBtn = document.getElementById('add-user-btn');
            let searchTimeout;

            // Clear any previous state
            clearSelectedUser();

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    resultsDiv.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    searchUsers(query);
                }, 300);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                    resultsDiv.style.display = 'none';
                }
            });
        }

        function searchUsers(query) {
            const resultsDiv = document.getElementById('user_search_results');

            fetch(`/books/{{ $book->id }}/users/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.users);
                } else {
                    resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--gray-500);">No users found</div>';
                    resultsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--danger-color);">Error searching users</div>';
                resultsDiv.style.display = 'block';
            });
        }

        function displaySearchResults(users) {
            const resultsDiv = document.getElementById('user_search_results');

            if (users.length === 0) {
                resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--gray-500);">No users found</div>';
            } else {
                resultsDiv.innerHTML = users.map(user => `
                    <div onclick="selectUser(${user.id}, '${user.display.replace(/'/g, "\\'")}', '${user.name.replace(/'/g, "\\'")}', '${user.email.replace(/'/g, "\\'")}')"
                         style="
                             padding: 0.75rem;
                             cursor: pointer;
                             border-bottom: 1px solid var(--gray-100);
                             transition: background-color 0.2s;
                         "
                         onmouseover="this.style.backgroundColor='var(--gray-50)'"
                         onmouseout="this.style.backgroundColor='white'">
                        <div style="font-weight: 600; color: var(--gray-900);">${user.name}</div>
                        <div style="font-size: 0.875rem; color: var(--gray-500);">${user.email}</div>
                        ${!user.is_business_member ?
                            '<div style="font-size: 0.75rem; color: var(--warning-color); font-weight: 500;">⚠ Will be added to business</div>' :
                            ''
                        }
                    </div>
                `).join('');
            }

            resultsDiv.style.display = 'block';
        }

        function selectUser(userId, display, name, email) {
            const searchInput = document.getElementById('user_search');
            const resultsDiv = document.getElementById('user_search_results');
            const selectedUserIdInput = document.getElementById('selected_user_id');
            const selectedUserDisplay = document.getElementById('selected_user_display');
            const selectedUserText = document.getElementById('selected_user_text');
            const addBtn = document.getElementById('add-user-btn');

            // Set the selected user
            selectedUserIdInput.value = userId;
            selectedUserText.textContent = display;

            // Hide search input and show selected user display
            searchInput.style.display = 'none';
            resultsDiv.style.display = 'none';
            selectedUserDisplay.style.display = 'block';

            // Enable the add button
            addBtn.disabled = false;
        }

        function clearSelectedUser() {
            const searchInput = document.getElementById('user_search');
            const resultsDiv = document.getElementById('user_search_results');
            const selectedUserIdInput = document.getElementById('selected_user_id');
            const selectedUserDisplay = document.getElementById('selected_user_display');
            const addBtn = document.getElementById('add-user-btn');

            // Clear all fields
            searchInput.value = '';
            searchInput.style.display = 'block';
            selectedUserIdInput.value = '';
            resultsDiv.style.display = 'none';
            selectedUserDisplay.style.display = 'none';
            addBtn.disabled = true;
        }

        function loadBookUsers() {
            fetch(`/books/{{ $book->id }}/users`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBookUsers(data.bookUsers);
                } else {
                    showNotification('Error loading users', 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading users', 'error');
            });
        }

        function populateAvailableUsers(users) {
            // This function is no longer needed with searchable input
        }

        function displayBookUsers(users) {
            const container = document.getElementById('users-list');
            container.innerHTML = '';

            const currentUserRole = "{{ $userRole }}";
            const currentUserId = {{ Auth::id() }};

            if (users.length === 0) {
                container.innerHTML = '<p style="color: var(--gray-500); text-align: center; padding: 1rem;">No users assigned to this book yet.</p>';
                return;
            }

            users.forEach(user => {
                const userElement = document.createElement('div');
                userElement.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white; border: 1px solid var(--gray-200); border-radius: var(--border-radius); margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;';

                let roleControlHtml = '';

                if (user.role === 'primary_admin') {
                    roleControlHtml = `
                        <span style="display: inline-block; padding: 0.25rem 0.6rem; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 0.78rem; font-weight: 700;">
                            Primary Admin (Owner)
                        </span>
                    `;
                } else if (currentUserRole === 'primary_admin') {
                    // Primary admin can change role or transfer ownership
                    roleControlHtml = `
                        <select onchange="updateUserRole(${user.id}, this.value)" style="font-size: 0.8125rem; padding: 0.35rem 0.5rem; border: 1px solid var(--gray-300); border-radius: 6px; outline: none; background: #fff;">
                            <option value="operator" ${user.role === 'operator' || user.role === 'employee' ? 'selected' : ''}>Operator</option>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="viewer" ${user.role === 'viewer' ? 'selected' : ''}>Viewer</option>
                        </select>
                        <button type="button" onclick="transferOwnershipToUser(${user.id}, '${escapeJsString(user.name)}')" 
                                title="Transfer Ownership to this member"
                                style="padding: 0.35rem 0.65rem; background: #ea580c; color: #fff; border: none; border-radius: 6px; font-weight: 600; font-size: 0.75rem; cursor: pointer; font-family: inherit;">
                            Make Owner
                        </button>
                        <button type="button" onclick="removeUserFromBook(${user.id})" title="Remove Member" style="background: none; border: none; color: var(--danger-color); cursor: pointer; padding: 0.25rem;">
                            <svg style="width: 1.1rem; height: 1.1rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    `;
                } else if (currentUserRole === 'admin') {
                    // Admin can see roles, but can only remove regular non-admin users
                    const roleLabel = user.role === 'admin' ? 'Admin' : (user.role === 'viewer' ? 'Viewer' : 'Operator');
                    const canRemove = user.role !== 'admin' && user.role !== 'primary_admin';

                    roleControlHtml = `
                        <span style="font-size: 0.8125rem; font-weight: 600; color: #475569; padding: 0.25rem 0.5rem; background: #f1f5f9; border-radius: 4px;">
                            ${roleLabel}
                        </span>
                        ${canRemove ? `
                            <button type="button" onclick="removeUserFromBook(${user.id})" title="Remove Member" style="background: none; border: none; color: var(--danger-color); cursor: pointer; padding: 0.25rem;">
                                <svg style="width: 1.1rem; height: 1.1rem;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        ` : ''}
                    `;
                } else {
                    // Regular user view
                    const roleLabel = user.role === 'admin' ? 'Admin' : (user.role === 'viewer' ? 'Viewer' : 'Operator');
                    roleControlHtml = `
                        <span style="font-size: 0.8125rem; font-weight: 600; color: #475569; padding: 0.25rem 0.5rem; background: #f1f5f9; border-radius: 4px;">
                            ${roleLabel}
                        </span>
                    `;
                }

                userElement.innerHTML = `
                    <div>
                        <div style="font-weight: 600; color: var(--gray-900);">${escapeHtmlString(user.name)} ${user.id === currentUserId ? ' (You)' : ''}</div>
                        <div style="font-size: 0.875rem; color: var(--gray-500);">${escapeHtmlString(user.email)}</div>
                        <div style="font-size: 0.75rem; color: var(--gray-400);">Added on ${user.assigned_at}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        ${roleControlHtml}
                    </div>
                `;

                container.appendChild(userElement);
            });
        }

        function transferOwnershipToUser(userId, userName) {
            if (!confirm(`Are you sure you want to transfer Primary Admin ownership of this cashbook to ${userName}? You will become an Admin of this cashbook.`)) {
                return;
            }

            fetch(`/books/{{ $book->id }}/transfer-ownership`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ new_owner_id: userId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Error transferring ownership', 'error');
                }
            })
            .catch(err => {
                showNotification('Error transferring ownership', 'error');
            });
        }

        function escapeJsString(str) {
            if (!str) return '';
            return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
        }

        function escapeHtmlString(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function addUserToBook() {
            const form = document.getElementById('add-user-form');
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            // Check if user is selected
            if (!document.getElementById('selected_user_id').value) {
                showNotification('Please select a user first', 'error');
                return;
            }

            submitBtn.textContent = 'Adding...';
            submitBtn.disabled = true;

            fetch(`/books/{{ $book->id }}/members`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('User added successfully!', 'success');
                    clearSelectedUser(); // Clear the selected user
                    document.getElementById('user_role').value = 'employee'; // Reset role to default
                    loadBookUsers(); // Reload the users list
                } else {
                    showNotification(data.message || 'Error adding user', 'error');
                }
            })
            .catch(error => {
                showNotification('Error adding user', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        function updateUserRole(userId, newRole) {
            fetch(`/books/{{ $book->id }}/users/${userId}/role`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ role: newRole })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('User role updated successfully!', 'success');
                } else {
                    showNotification(data.message || 'Error updating role', 'error');
                    loadBookUsers(); // Reload to reset the select
                }
            })
            .catch(error => {
                showNotification('Error updating role', 'error');
                loadBookUsers(); // Reload to reset the select
            });
        }

        function removeUserFromBook(userId) {
            if (!confirm('Are you sure you want to remove this user from the book?')) {
                return;
            }

            fetch(`/books/{{ $book->id }}/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('User removed successfully!', 'success');
                    loadBookUsers(); // Reload the users list
                } else {
                    showNotification(data.message || 'Error removing user', 'error');
                }
            })
            .catch(error => {
                showNotification('Error removing user', 'error');
            });
        }
    </script>
</x-app-layout>
