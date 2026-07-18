<x-app-layout>
    <!-- Redesigned Page Header -->
    <div class="page-header-redesigned">

        <!-- Left side: Back Arrow, Title, and Icons -->
        <div class="page-header-left">
            <a href="{{ route('books.index') }}" class="page-header-back-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="page-header-title-group">
                <h1 class="page-title">{{ $book->name }}</h1>

                @php
                    $userRole = Auth::user()->books()->where('book_id', $book->id)->first()->pivot->role ?? 'employee';
                @endphp
                @if(in_array($userRole, ['primary_admin', 'admin']))
                    <a href="{{ route('books.edit', $book) }}" class="page-header-icon-btn" title="Edit Book Settings">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </a>

                    <button @click="$dispatch('open-modal', 'manage-users')" class="page-header-icon-btn" title="Manage Users">
                        <svg height="800px" width="800px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="0 0 60.671 60.671" xml:space="preserve">
                        <g>
                            <g>
                                <ellipse style="fill:#010002;" cx="30.336" cy="12.097" rx="11.997" ry="12.097"/>
                                <path style="fill:#010002;" d="M35.64,30.079H25.031c-7.021,0-12.714,5.739-12.714,12.821v17.771h36.037V42.9
                                    C48.354,35.818,42.661,30.079,35.64,30.079z"/>
                            </g>
                        </g>
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- Right side: Action Buttons -->
        @if ($bookRole !== 'employee')
            <div class="page-header-right">
                <a href="{{ route('transactions.import.create', $book) }}" class="btn btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Bulk Entries
                </a>
                <a href="{{ route('reports.index', $book) }}" class="btn btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Reports
                </a>
            </div>
        @endif

    </div>

    <!-- Add Alpine.js state to manage filter visibility -->
    <div x-data="{ showFilters: false }">

        <!-- Filter Toggle Button (visible only on small screens) -->
        <button
            @click="showFilters = !showFilters"
            class="btn btn-primary md:hidden mb-4"
            aria-expanded="false"
            aria-controls="filter-section">
            Filter
            <svg :class="{'transform rotate-180': showFilters}" class="inline-block w-4 h-4 ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Filters and Summary Section -->
        <div id="filter-section" class="card mb-4" :class="{'block': showFilters, 'hidden': !showFilters, 'md:block': true}" >
            <div class="card-body">
                <!-- Filter Options -->
                <div class="grid grid-cols-1 md:grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-4 mb-6">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Duration</label>
                        <select class="form-select" style="font-size: 0.875rem;">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Types</label>
                        <select class="form-select" style="font-size: 0.875rem;">
                            <option value="">All</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Members</label>
                        <select class="form-select" style="font-size: 0.875rem;">
                            <option value="">All</option>
                            @foreach($book->business->users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Payment Modes</label>
                        <select class="form-select" style="font-size: 0.875rem;">
                            <option value="">All</option>
                            @foreach($modes as $mode)
                                <option value="{{ $mode }}">{{ ucfirst($mode) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.875rem; margin-bottom: 0.5rem;">Categories</label>
                        <select class="form-select" style="font-size: 0.875rem;">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-input" placeholder="Search by remark or amount..." style="font-size: 0.875rem;">
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Summary Section -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">

            <!-- Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <!-- Cash In Summary -->
                <div class="cash-in-card" style="background: linear-gradient(135deg, var(--success-color), #10b981); color: white; padding: 1.5rem; border-radius: var(--border-radius); text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-weight: 600;">Cash In</span>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" class="summary-amount">
                        @php
                            // Get totals from all transactions, not just paginated ones
                            $allTransactions = $book->transactions;
                            $totalIncome = $allTransactions->where('type', 'income')->sum('amount');
                        @endphp
                        {{ $book->currency }} {{ number_format($totalIncome, 2, '.', ',') }}
                    </div>
                    {{-- <div style="font-size: 0.75rem; opacity: 0.9;">Total Income</div> --}}
                </div>

                <!-- Cash Out Summary -->
                <div class="cash-out-card" style="background: linear-gradient(135deg, var(--danger-color), #ef4444); color: white; padding: 1.5rem; border-radius: var(--border-radius); text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-weight: 600;">Cash Out</span>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" class="summary-amount">
                        @php
                            $totalExpense = $allTransactions->where('type', 'expense')->sum('amount');
                        @endphp
                        {{ $book->currency }} {{ number_format($totalExpense, 2, '.', ',') }}
                    </div>
                    {{-- <div style="font-size: 0.75rem; opacity: 0.9;">Total Expense</div> --}}
                </div>

                <!-- Net Balance Summary -->
                <div class="net-balance-card" style="background: linear-gradient(135deg, var(--primary-color), #3b82f6); color: white; padding: 1.5rem; border-radius: var(--border-radius); text-align: center;">
                    <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        <span style="font-weight: 600;">Net Balance</span>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" class="summary-amount">
                        @php
                            $netBalance = ($totalIncome ?? 0) - ($totalExpense ?? 0);
                        @endphp
                        {{ $book->currency }} {{ $netBalance >= 0 ? '' : '-' }}{{ number_format(abs($netBalance), 2, '.', ',') }}
                    </div>
                    {{-- <div style="font-size: 0.75rem; opacity: 0.9;">{{ $netBalance >= 0 ? 'Profit' : 'Loss' }}</div> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div x-data="{}" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
            @if($bookRole !== 'employee')
            <button @click="$dispatch('open-modal', 'add-transaction'); $nextTick(() => { document.getElementById('type').value = 'income'; document.getElementById('transaction-form').reset(); document.getElementById('type').value = 'income'; })" class="btn btn-success">
                <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Cash In
            </button>
            <button @click="$dispatch('open-modal', 'add-transaction'); $nextTick(() => { document.getElementById('type').value = 'expense'; document.getElementById('transaction-form').reset(); document.getElementById('type').value = 'expense'; })" class="btn btn-danger">
                <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
                Cash Out
            </button>
            @endif
            @if(in_array($userRole, ['primary_admin', 'admin']))
            <button @click="$dispatch('open-modal', 'manage-users')" class="btn btn-secondary">
                <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                Manage Users
            </button>
            @endif
            @if($bookRole !== 'employee')
                <!-- New Bulk Delete Button -->
                <button id="bulk-delete-btn" class="btn btn-danger" style="display: none;" onclick="bulkDeleteTransactions()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Delete Selected (<span id="selected-count">0</span>)
                </button>
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
            <span style="font-size: 0.875rem; color: var(--gray-500);">{{ $transactions->total() }} total transactions</span>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-body" style="padding: 0">
            <table id="transactions-table" class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-checkbox"></th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Mode</th>
                        <th>Type</th>
                        <th style="text-align: right;">Amount</th>
                        <th>Status</th>
                        <th>User</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable will populate this -->
                </tbody>
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
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Add New Transaction</h3>
            <form id="transaction-form" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <input type="hidden" name="return_to" value="{{ route('books.show', $book) }}">
                <input type="hidden" id="transaction_id" name="transaction_id" value="">
                <input type="hidden" id="form_method" name="_method" value="POST">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <label for="amount" class="form-label">Amount</label>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="form-input" placeholder="Enter amount..." required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="transaction_date" class="form-label">Date & Time</label>
                        <input id="transaction_date" name="transaction_date" type="datetime-local" class="form-input" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                        <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <label for="mode" class="form-label">Payment Mode</label>
                        <input id="mode" name="mode" type="text" class="form-input" placeholder="Enter payment mode..." />
                        <x-input-error :messages="$errors->get('mode')" class="mt-2" />
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="category_id" class="form-label">Category</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <label for="new_category" class="form-label">Or Add New Category</label>
                        <input id="new_category" name="new_category" type="text" class="form-input" placeholder="Enter new category name..." />
                        <x-input-error :messages="$errors->get('new_category')" class="mt-2" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-input" placeholder="Enter transaction description..."></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="form-group">
                    <label for="receipt" class="form-label">Receipt (optional)</label>
                    <input id="receipt" name="receipt" type="file" accept="image/*,application/pdf" class="form-input" style="padding: 0.5rem;" />
                    <x-input-error :messages="$errors->get('receipt')" class="mt-2" />
                </div>

                <div style="
                    display: flex;
                    justify-content: flex-end;
                    gap: 0.75rem;
                    border-top: 1px solid #ddd;
                    padding: 1rem;
                    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
                    background: #fff;
                    position: sticky;
                    bottom: 0;">
                    <button type="button" @click="$dispatch('close-modal', 'add-transaction')" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" id="submit-btn" class="btn btn-primary">
                        Save Transaction
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Edit Transaction Modal -->
    <x-modal name="edit-transaction" :show="false" class="modal-hidden">
        <div style="padding: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Edit Transaction</h3>
            <form id="edit-transaction-form" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                @method('PUT')
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <input type="hidden" id="edit_transaction_id" name="transaction_id" value="">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_type" class="form-label">Type</label>
                        <select id="edit_type" name="type" class="form-select" required>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_amount" class="form-label">Amount</label>
                        <input id="edit_amount" name="amount" type="number" step="0.01" min="0.01" class="form-input" required />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_transaction_date" class="form-label">Date & Time</label>
                        <input id="edit_transaction_date" name="transaction_date" type="datetime-local" class="form-input" required />
                    </div>

                    <div class="form-group">
                        <label for="edit_mode" class="form-label">Payment Mode</label>
                        <input id="edit_mode" name="mode" type="text" class="form-input" placeholder="Enter payment mode..." />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_category_id" class="form-label">Category</label>
                        <select id="edit_category_id" name="category_id" class="form-select">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_new_category" class="form-label">Or Add New Category</label>
                        <input id="edit_new_category" name="new_category" type="text" class="form-input" placeholder="Enter new category name..." />
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_description" class="form-label">Description</label>
                    <textarea id="edit_description" name="description" rows="3" class="form-input" placeholder="Enter transaction description..."></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_receipt" class="form-label">Receipt (optional)</label>
                    <input id="edit_receipt" name="receipt" type="file" accept="image/*,application/pdf" class="form-input" style="padding: 0.5rem;" />
                    <div id="current-receipt" style="margin-top: 0.5rem; display: none;">
                        <span style="font-size: 0.875rem; color: var(--gray-600);">Current receipt: <a id="receipt-link" href="#" target="_blank" style="color: var(--primary-color);">View</a></span>
                    </div>
                </div>

                <div style="
                        display: flex;
                        justify-content: flex-end;
                        gap: 0.75rem;
                        border-top: 1px solid #ddd;
                        padding: 1rem;
                        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
                        background: #fff;
                        position: sticky;
                        bottom: 0;">
                    <button type="button" @click="$dispatch('close-modal', 'edit-transaction')" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Update Transaction
                    </button>
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
                            <option value="employee">Employee - Can add/edit own transactions</option>
                            <option value="admin">Admin - Can manage books, members, and transactions</option>
                            <option value="primary_admin">Primary Admin - Full access to book and business management</option>
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

        // Initialize DataTable
        $(document).ready(function() {
            dataTable = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("books.transactions.data", $book) }}',
                    type: 'GET',
                    data: function(d) {
                        d.duration = $('select:eq(0)').val();
                        d.type = $('select:eq(1)').val();
                        d.member = $('select:eq(2)').val();
                        d.mode = $('select:eq(3)').val();
                        d.category = $('select:eq(4)').val();
                        d.search = $('input[placeholder*="Search"]').val();
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
                    { data: 'description', name: 'description' },
                    { data: 'category', name: 'category.name' },
                    { data: 'mode', name: 'mode' },
                    { data: 'type', name: 'type' },
                    { data: 'amount', name: 'amount', className: 'text-right' },
                    { data: 'status', name: 'status' },
                    { data: 'user', name: 'user.name' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                responsive: false,
                lengthMenu: [10, 25, 50, 75, 100],
                autoWidth: true,
                scrollY: '80vh',
                scrollCollapse: true,
                scrollX: true,
                fixedHeader: true,
                language: {
                    processing: 'Loading transactions...',
                    emptyTable: 'No transactions found',
                    zeroRecords: 'No matching transactions found'
                },
                initComplete: function() {
                    const api = this.api();

                    // Row click (only once)
                    $('#transactions-table tbody').on('click', 'tr', function(e) {
                        const data = api.row(this).data();
                        if (data && data.id) {
                            showTransactionDetail(data.id);
                        }
                    });

                    // Prevent checkbox clicks from triggering row click
                    $('#transactions-table tbody').on('click', 'input[type="checkbox"]', function(e) {
                        e.stopPropagation();
                    });

                    // Select All checkbox
                    $('#select-all-checkbox').on('change', function() {
                        const isChecked = this.checked;
                        api.rows({ search: 'applied' }).nodes()
                            .to$()
                            .find('input[type="checkbox"]')
                            .prop('checked', isChecked);
                        updateSelectedCount();
                    });

                    // Individual checkbox change
                    $('#transactions-table tbody').on('change', 'input[type="checkbox"]', function() {
                        const selectAll = $('#select-all-checkbox').get(0);
                        if (!this.checked && selectAll && selectAll.checked && ('indeterminate' in selectAll)) {
                            selectAll.indeterminate = true;
                        }
                        updateSelectedCount();
                    });

                    // Filter events
                    $('.card-body select').on('change', function() {
                        api.ajax.reload();
                        updateSummaryCards();
                    });

                    const searchInput = $('input[placeholder*="Search"]');
                    if (searchInput.length) {
                        searchInput.on('input', debounce(function() {
                            api.ajax.reload();
                        }, 300));
                    }

                    // Initial summary update
                    updateSummaryCards();
                },
                preDrawCallback: function() {
                    $('#selected-count').text('0');
                    $('#bulk-delete-btn').hide();
                },
                drawCallback: function() {
                    updateSelectedCount();
                }
            });

            // Handle "Select All" checkbox
            $('#select-all-checkbox').on('click', function() {
                const rows = dataTable.rows({ 'search': 'applied' }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
                updateSelectedCount();
            });

            // Handle individual row checkbox clicks
            $('#transactions-table tbody').on('change', 'input[type="checkbox"]', function() {
                if (!this.checked) {
                    const selectAll = $('#select-all-checkbox').get(0);
                    if (selectAll && selectAll.checked && ('indeterminate' in selectAll)) {
                        selectAll.indeterminate = true;
                    }
                }
                updateSelectedCount();
            });

            // Update checkboxes on table draw
            dataTable.on('draw', function() {
                updateSelectedCount();
            });

            // Filter event listeners
            const filterSelects = document.querySelectorAll('.card-body select');
            const searchInput = document.querySelector('input[placeholder*="Search"]');

            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    dataTable.ajax.reload();
                    updateSummaryCards();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    dataTable.ajax.reload();
                }, 300));
            }

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
            // Get current filter values
            const filters = {
                duration: $('select:eq(0)').val(),
                type: $('select:eq(1)').val(),
                member: $('select:eq(2)').val(),
                mode: $('select:eq(3)').val(),
                category: $('select:eq(4)').val(),
                search: $('input[placeholder*="Search"]').val()
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
                    document.getElementById('edit_type').value = transaction.type;
                    document.getElementById('edit_amount').value = transaction.amount;
                    // Format datetime for datetime-local input (YYYY-MM-DDTHH:MM)
                    const transactionDate = new Date(transaction.transaction_date);
                    const formattedDate = transactionDate.toISOString().slice(0, 16);
                    document.getElementById('edit_transaction_date').value = formattedDate;
                    document.getElementById('edit_category_id').value = transaction.category_id || '';
                    document.getElementById('edit_description').value = transaction.description || '';
                    document.getElementById('edit_mode').value = transaction.mode || '';
                    document.getElementById('edit_new_category').value = '';

                    // Handle current receipt
                    const currentReceipt = document.getElementById('current-receipt');
                    const receiptLink = document.getElementById('receipt-link');

                    if (transaction.image_path) {
                        receiptLink.href = `/transactions/${transaction.id}/receipt`;
                        currentReceipt.style.display = 'block';
                    } else {
                        currentReceipt.style.display = 'none';
                    }

                    // Open edit modal
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'edit-transaction'
                    }));
                } else {
                    showNotification('Error loading transaction data: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading transaction data: ' + error.message, 'error');
            });
        }

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

        // Edit transaction function
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
                    document.getElementById('edit_type').value = transaction.type;
                    document.getElementById('edit_amount').value = transaction.amount;
                    // Format datetime for datetime-local input (YYYY-MM-DDTHH:MM)
                    const transactionDate = new Date(transaction.transaction_date);
                    const formattedDate = transactionDate.toISOString().slice(0, 16);
                    document.getElementById('edit_transaction_date').value = formattedDate;
                    document.getElementById('edit_category_id').value = transaction.category_id || '';
                    document.getElementById('edit_description').value = transaction.description || '';
                    document.getElementById('edit_mode').value = transaction.mode || '';

                    // Handle current receipt
                    const currentReceipt = document.getElementById('current-receipt');
                    const receiptLink = document.getElementById('receipt-link');

                    if (transaction.image_path) {
                        receiptLink.href = `/transactions/${transaction.id}/receipt`;
                        currentReceipt.style.display = 'block';
                    } else {
                        currentReceipt.style.display = 'none';
                    }

                    // Open edit modal using Alpine.js dispatch
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: 'edit-transaction'
                    }));
                } else {
                    showNotification('Error loading transaction data: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading transaction data: ' + error.message, 'error');
            });
        }

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

            if (users.length === 0) {
                container.innerHTML = '<p style="color: var(--gray-500); text-align: center; padding: 1rem;">No users assigned to this book yet.</p>';
                return;
            }

            users.forEach(user => {
                const userElement = document.createElement('div');
                userElement.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white; border: 1px solid var(--gray-200); border-radius: var(--border-radius); margin-bottom: 0.5rem;';

                userElement.innerHTML = `
                    <div>
                        <div style="font-weight: 600; color: var(--gray-900);">${user.name}</div>
                        <div style="font-size: 0.875rem; color: var(--gray-500);">${user.email}</div>
                        <div style="font-size: 0.75rem; color: var(--gray-400);">Added on ${user.assigned_at}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <select onchange="updateUserRole(${user.id}, this.value)" style="font-size: 0.875rem; padding: 0.25rem 0.5rem; border: 1px solid var(--gray-300); border-radius: 0.25rem;">
                            <option value="employee" ${user.role === 'employee' ? 'selected' : ''}>Employee</option>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="primary_admin" ${user.role === 'primary_admin' ? 'selected' : ''}>Primary Admin</option>
                        </select>
                        <button onclick="removeUserFromBook(${user.id})" style="background: none; border: none; color: var(--danger-color); cursor: pointer; padding: 0.25rem;">
                            <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                `;

                container.appendChild(userElement);
            });
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

            fetch(`/books/{{ $book->id }}/users/invite`, {
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
