<x-app-layout>
    @if($activeBusiness)
        @if(isset($hasAccess) && !$hasAccess)
            <!-- No Access Message for Employees -->
            <div style="margin-bottom: 1.5rem;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Dashboard</h1>
                    <p style="font-size: 1rem; color: #4b5563; margin: 0;">Welcome to {{ $activeBusiness->name }}.</p>
                </div>
            </div>

            <div style="text-align: center; padding: 3rem; margin-top: 2rem; border: 1px solid #e5e7eb; border-radius: 0.5rem;">
                <svg style="width: 4rem; height: 4rem; color: #9ca3af; margin: 0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;">No Books Assigned</h3>
                <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
                    You don't have access to any books in this business yet.<br>
                    Contact your business primary admin or administrator to get access to books so you can view transactions and data.
                </p>
                <p style="font-size: 0.75rem; color: #9ca3af;">
                    Role: {{ ucfirst($role ?? 'employee') }}
                </p>
            </div>
        @else
            <!-- Page Header -->
            <div style="margin-bottom: 1.5rem;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">Dashboard</h1>
                    <p style="font-size: 1rem; color: #4b5563; margin: 0;">
                        Welcome back! Here's what's happening with {{ $activeBusiness->name }}.
                    </p>
                </div>
            </div>

            <div style="padding: 20px; font-family: Arial, sans-serif; display: flex; gap: 20px; flex-wrap: wrap;">

                {{-- Search & Sort --}}
                <div style="flex: 1 1 100%; margin-bottom: 1rem; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <input id="bookSearchInput" type="text" placeholder="Search by book name..." style="flex: 1 1 250px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; outline-offset: 2px;">

                    <select id="bookSortSelect" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; cursor: pointer;">
                        <option value="updated_desc">Sort By : Last Updated</option>
                        <option value="name_asc">Sort By : Name (A-Z)</option>
                        <option value="net_high_low">Sort By : Net Balance (High to Low)</option>
                        <option value="net_low_high">Sort By : Net Balance (Low to High)</option>
                        <option value="created_desc">Sort By : Last Created</option>
                    </select>

                    <a href="{{ route('books.create') }}"
                       style="padding: 8px 16px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: bold; text-align: center; white-space: nowrap;">
                        Add New Book
                    </a>
                </div>

                <div style="display: flex; flex: 1 1 100%; gap: 20px; flex-wrap: wrap;">

                    {{-- Left Side (Books List) --}}
                    <div style="flex: 1 1 60%; min-width: 320px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; box-sizing: border-box;">

                        <div id="booksContainer" style="max-height: 600px; overflow-y: auto;">
                            @forelse($accessibleBooks as $book)
                                @php
                                    $allTransactions = $book->transactions;
                                    $totalIncome = $allTransactions->where('type', 'income')->sum('amount');
                                    $totalExpense = $allTransactions->where('type', 'expense')->sum('amount');
                                    $netBalance = ($totalIncome ?? 0) - ($totalExpense ?? 0);
                                @endphp
                                <div
                                    class="book-item"
                                    data-name="{{ strtolower($book->name) }}"
                                    data-updated="{{ $book->updated_at->timestamp }}"
                                    data-created="{{ $book->created_at->timestamp }}"
                                    data-netbalance="{{ $netBalance }}"
                                    style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 12px 0; cursor: pointer;">

                                    <a href="{{ route('books.show', $book->id) }}" class="book-item-link" style="text-decoration: none; color: inherit; display: flex; justify-content: space-between; flex: 1; align-items: center;">

                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="background: #f3e8ff; color: #7c3aed; border-radius: 50%; padding: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <img src="{{ Storage::url('images/logo.svg') }}" alt="Book Logo" style="width: 24px; height: 24px; object-fit: cover;">
                                            </div>
                                            <div style="display: flex; flex-direction: column;">
                                                <p style="margin: 0; font-weight: bold;">{{ $book['name'] }}</p>
                                                <p style="margin: 0; color: gray; font-size: 12px;">Updated {{ $book['updated_at']->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div style="font-weight: bold; color: {{ $netBalance < 0 ? '#dc2626' : '#16a34a' }};">
                                            {{ $book->currency }} {{ $netBalance >= 0 ? '' : '-' }} {{ number_format(abs($netBalance), 0) }}
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        @if($accessibleBooks->isEmpty())
                            <div style="text-align: center; padding: 20px; color: gray;">
                                <p>No books available. Click the button above to add your first book.</p>
                                <a href="{{ route('books.create') }}" style="color: #2563eb; text-decoration: none; font-weight: bold; border: 1px solid #2563eb; padding: 8px 16px; border-radius: 6px; display: inline-block; margin-top: 10px;">
                                    Add Your First Book
                                </a>
                            </div>
                        @endif

                        {{-- Quick Add --}}
                        <div style="border: 1px solid #ddd; border-radius: 8px; margin-top: 20px; padding: 16px; background: white;">
                            <h3 style="margin-top: 0; font-weight: bold;">Add New Book</h3>
                            <p style="color: gray; font-size: 13px; margin-bottom: 12px;">Click to quickly add books for</p>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                <a href="{{ route('books.create', ['name' => Date::now()->monthName . ' Expenses']) }}" style="padding: 6px 14px; border-radius: 20px; border: 1px solid #93c5fd; color: #2563eb; background: white; cursor: pointer; text-decoration: none;">
                                    {{ Date::now()->monthName }} Expenses
                                </>
                                <a href="{{ route('books.create', ['name' => 'Project Book']) }}" style="padding: 6px 14px; border-radius: 20px; border: 1px solid #93c5fd; color: #2563eb; background: white; cursor: pointer; text-decoration: none;">
                                    Project Book
                                </a>
                                <a href="{{ route('books.create', ['name' => Date::now()->year . ' Ledger']) }}" style="padding: 6px 14px; border-radius: 20px; border: 1px solid #93c5fd; color: #2563eb; background: white; cursor: pointer; text-decoration: none;">
                                    {{ Date::now()->year }} Ledger
                                </a>
                                <a href="{{ route('books.create', ['name' => 'Payable Book']) }}" style="padding: 6px 14px; border-radius: 20px; border: 1px solid #93c5fd; color: #2563eb; background: white; cursor: pointer; text-decoration: none;">
                                    Payable Book
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side (Help Box) --}}
                    <div style="flex: 1 1 35%; min-width: 280px; max-width: 360px; border: 1px solid #ddd; border-radius: 8px; padding: 16px; background: white; box-sizing: border-box;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <div style="background: #f3f4f6; color: #4b5563; border-radius: 50%; padding: 8px; font-size: 18px; line-height: 1;">
                                ❓
                            </div>
                            <h3 style="margin: 0; font-weight: bold;">Need Help?</h3>
                        </div>
                        <p style="color: gray; font-size: 13px; margin-bottom: 12px;">
                            If you have any questions or need assistance, our support team is here to help you 24/7.
                        </p>
                        <button style="padding: 8px 16px; border-radius: 6px; border: none; background: #2563eb; color: white; cursor: pointer; font-weight: 600; font-size: 14px; width: 100%;">
                            Contact Support
                        </button>
                        <p style="font-size: 12px; color: gray; margin-top: 12px; text-align: center;">
                            Or visit our <a href="#" style="color: #2563eb; text-decoration: none;">Help Center</a> for FAQs and guides.
                        </p>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const searchInput = document.getElementById('bookSearchInput');
                    const sortSelect = document.getElementById('bookSortSelect');
                    const booksContainer = document.getElementById('booksContainer');
                    let bookItems = Array.from(booksContainer.querySelectorAll('.book-item'));

                    function filterAndSortBooks() {
                        const query = searchInput.value.toLowerCase();
                        const sortBy = sortSelect.value;

                        // Filter items by search query
                        let filtered = bookItems.filter(item => {
                            return item.dataset.name.includes(query);
                        });

                        // Sort filtered items
                        filtered.sort((a, b) => {
                            switch (sortBy) {
                                case 'name_asc':
                                    return a.dataset.name.localeCompare(b.dataset.name);
                                case 'net_high_low':
                                    return parseFloat(b.dataset.netbalance) - parseFloat(a.dataset.netbalance);
                                case 'net_low_high':
                                    return parseFloat(a.dataset.netbalance) - parseFloat(b.dataset.netbalance);
                                case 'created_desc':
                                    return parseInt(b.dataset.created) - parseInt(a.dataset.created);
                                case 'updated_desc':
                                default:
                                    return parseInt(b.dataset.updated) - parseInt(a.dataset.updated);
                            }
                        });

                        // Clear container and re-append filtered & sorted items
                        booksContainer.innerHTML = '';
                        filtered.forEach(item => booksContainer.appendChild(item));
                    }

                    // Run on input/select change
                    searchInput.addEventListener('input', filterAndSortBooks);
                    sortSelect.addEventListener('change', filterAndSortBooks);

                    // Initial call
                    filterAndSortBooks();
                });
            </script>
        @endif
    @else
        <!-- No Business Selected -->
        <div style="text-align: center; padding: 4rem 2rem; margin-top: 2rem; background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%); border: 2px dashed #d1d5db; border-radius: 0.5rem;">
            <div style="max-width: 400px; margin: 0 auto;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);">
                    <svg fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="width: 36px; height: 36px;">
                        <path d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h2 style="font-weight: 700; font-size: 1.5rem; margin-bottom: 0.5rem;">No Business Selected</h2>
                <p style="color: #6b7280; margin-bottom: 1.5rem;">
                    Please select a business to continue. You can create a new business or choose one from the list.
                </p>
                <a href="{{ route('businesses.create') }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: white; font-weight: 600; border-radius: 8px; text-decoration: none; font-size: 14px;">Create New Business</a>
            </div>
        </div>
    @endif
</x-app-layout>
