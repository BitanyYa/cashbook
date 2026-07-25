<x-app-layout>
<div style="padding: 1.5rem 2rem; background: #f8fafc; min-height: 100vh;">

    {{-- ── Page Header ── --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1 style="font-size: 1.375rem; font-weight: 700; color: #1e293b; margin: 0;">
            {{ $activeBusiness->name ?? 'My Business' }}
        </h1>
        @if(in_array($role, ['primary_admin', 'admin']))
        <a href="{{ route('settings.index', $activeBusiness) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border: 1px solid #c7d2fe; border-radius: 6px; background: #fff; color: #4338ca; font-size: 0.8125rem; font-weight: 600; text-decoration: none; transition: background 0.2s;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0112.75 21.5h-1.5a2.25 2.25 0 01-2.25-2.263V19.13m0-13.076c.725.318 1.488.485 2.25.485h1.5c.762 0 1.525-.167 2.25-.485m-7.5 0a3 3 0 106 0m-6 0a3.001 3.001 0 003 2.87m0 .13V16.25" />
            </svg>
            Business Team
        </a>
        @endif
    </div>

    <div>
        {{-- ── Books List Column ── --}}
        <div style="width: 100%;">
            {{-- Toolbar --}}
            <form id="booksFilterForm" method="GET" action="{{ route('books.index') }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap;">
                    {{-- Search + Sort --}}
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <div style="position: relative;">
                            <svg width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2.2" viewBox="0 0 24 24" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="q" id="bookSearch" value="{{ request('q') }}" placeholder="Search by book name..." 
                                   oninput="debouncedSearch()"
                                   style="padding: 7px 32px 7px 30px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.8125rem; width: 240px; outline: none; color: #334155; background: #fff; font-family: inherit;">
                            <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.65rem; color: #9ca3af; border: 1px solid #e2e8f0; border-radius: 4px; padding: 1px 5px;">/</span>
                        </div>

                        <div style="position: relative;">
                            <select name="sort" id="bookSort" onchange="fetchBooks(1)" style="padding: 7px 32px 7px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.8125rem; color: #334155; background: #fff; appearance: none; outline: none; cursor: pointer; font-family: inherit; min-width: 175px;">
                                <option value="updated_at_desc" {{ request('sort', 'updated_at_desc') == 'updated_at_desc' ? 'selected' : '' }}>Sort By: Last Updated</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Sort By: Name (A–Z)</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Sort By: Name (Z–A)</option>
                                <option value="updated_at_asc" {{ request('sort') == 'updated_at_asc' ? 'selected' : '' }}>Sort By: Oldest Updated</option>
                            </select>
                            <svg width="12" height="12" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        @if(request()->filled('q') || (request()->has('sort') && request('sort') !== 'updated_at_desc'))
                            <a href="{{ route('books.index') }}" style="font-size: 0.8125rem; font-weight: 600; color: #ef4444; text-decoration: none;">Clear Search</a>
                        @endif
                    </div>

                    {{-- Add New Book --}}
                    @if(in_array($role, ['primary_admin', 'admin']))
                    <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #3b82f6; color: white; border-radius: 6px; font-size: 0.85rem; font-weight: 700; text-decoration: none; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2);">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Book
                    </a>
                    @endif
                </div>
            </form>

            {{-- Books List Container --}}
            <div id="books-list-container" style="border-top: 1px solid #e2e8f0; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 0 1rem; position: relative;">
                <div id="books-loading-overlay" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.6); z-index: 10; align-items: center; justify-content: center;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #3b82f6;">Loading books...</div>
                </div>
                <div id="books-rows">
                    @forelse($books as $book)
                        @php
                            $income  = $book->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
                            $expense = $book->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
                            $balance = $income - $expense;
                            $balanceColor = $balance >= 0 ? '#10b981' : '#ef4444';
                        @endphp
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 4px; border-bottom: 1px solid #f1f5f9; {{ !$book->user_has_access ? 'opacity: 0.65;' : '' }} cursor: pointer;"
                             onclick="window.location='{{ route('books.show', $book) }}'"
                             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            {{-- Left: icon + name + subtitle --}}
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.97 5.97 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94-3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 0.9375rem; color: #0f172a; letter-spacing: 0.02em; text-transform: uppercase;">{{ $book->name }}</div>
                                    <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">
                                        {{ $book->users()->count() }} members &bull; Updated {{ $book->updated_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            {{-- Right: balance + action icons --}}
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <span style="font-weight: 700; font-size: 0.9375rem; color: {{ $balanceColor }}; min-width: 60px; text-align: right;">
                                    {{ number_format(abs($balance)) }}
                                </span>

                                <div style="display: flex; align-items: center; gap: 14px;">
                                    @if(in_array($role, ['primary_admin', 'admin']))
                                    <a href="{{ route('books.edit', $book) }}" title="Edit" style="color: #3b82f6; display: flex;" onclick="event.stopPropagation()">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('books.users', $book) }}" title="Manage Team" style="color: #3b82f6; display: flex;" onclick="event.stopPropagation()">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235A10.18 10.18 0 0112.5 12.75c2.478 0 4.795.886 6.6 2.36M12.5 21H3v-.83a6 6 0 016-6h1.5a6 6 0 013 1.1" />
                                        </svg>
                                    </a>
                                    @endif

                                    @if($book->user_has_access)
                                    <a href="{{ route('books.show', $book) }}" title="Open Book" style="color: #ef4444; display: flex;" onclick="event.stopPropagation()">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 4rem 2rem; color: #94a3b8;">
                            <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="display: block; margin-bottom: 1rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p style="font-size: 0.9375rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem;">No books found</p>
                            <p style="font-size: 0.8125rem;">Try adjusting your search criteria or create a new book.</p>
                            @if(in_array($role, ['primary_admin', 'admin']))
                            <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 1rem; padding: 9px 18px; background: #3b82f6; color: white; border-radius: 6px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                New Book
                            </a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination Links --}}
            <div id="books-pagination" style="margin-top: 1.5rem;">
                {{ $books->links() }}
            </div>

        </div>{{-- End Books List Column --}}
    </div>
</div>

<script>
let searchTimer = null;
let currentController = null;
const userRole = "{{ $role }}";

function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        fetchBooks(1);
    }, 300);
}

function fetchBooks(page = 1) {
    if (currentController) {
        currentController.abort(); // Cancel out-of-order pending request
    }
    currentController = new AbortController();

    const q = document.getElementById('bookSearch')?.value || '';
    const sort = document.getElementById('bookSort')?.value || 'updated_at_desc';
    const overlay = document.getElementById('books-loading-overlay');
    if (overlay) overlay.style.display = 'flex';

    const params = new URLSearchParams({ q: q, sort: sort, page: page });
    const fetchUrl = `{{ route('books.index') }}?` + params.toString();

    // Update browser URL without reloading page
    history.pushState({}, '', fetchUrl);

    fetch(fetchUrl, {
        signal: currentController.signal,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (overlay) overlay.style.display = 'none';
        if (data.success) {
            renderBooksRows(data.books);
            const pagEl = document.getElementById('books-pagination');
            if (pagEl && data.pagination) {
                pagEl.innerHTML = data.pagination;
            }
        }
    })
    .catch(err => {
        if (err.name !== 'AbortError') {
            if (overlay) overlay.style.display = 'none';
            console.error('Fetch books error:', err);
        }
    });
}

function renderBooksRows(books) {
    const container = document.getElementById('books-rows');
    if (!container) return;

    if (!books || books.length === 0) {
        container.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 4rem 2rem; color: #94a3b8;">
                <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="display: block; margin-bottom: 1rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p style="font-size: 0.9375rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem;">No books found</p>
                <p style="font-size: 0.8125rem;">Try adjusting your search criteria or create a new book.</p>
            </div>
        `;
        return;
    }

    const isAdmin = ['primary_admin', 'admin'].includes(userRole);

    container.innerHTML = books.map(book => `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 4px; border-bottom: 1px solid #f1f5f9; ${!book.user_has_access ? 'opacity: 0.65;' : ''} cursor: pointer;"
             onclick="window.location='${book.url}'"
             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.97 5.97 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94-3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.9375rem; color: #0f172a; letter-spacing: 0.02em; text-transform: uppercase;">${escapeHtml(book.name)}</div>
                    <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">
                        ${book.members_count} members &bull; Updated ${book.updated_human}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="font-weight: 700; font-size: 0.9375rem; color: ${book.balance_color}; min-width: 60px; text-align: right;">
                    ${book.balance_formatted}
                </span>
                <div style="display: flex; align-items: center; gap: 14px;">
                    ${isAdmin ? `
                        <a href="${book.edit_url}" title="Edit" style="color: #3b82f6; display: flex;" onclick="event.stopPropagation()">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </a>
                        <a href="${book.users_url}" title="Manage Team" style="color: #3b82f6; display: flex;" onclick="event.stopPropagation()">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235A10.18 10.18 0 0112.5 12.75c2.478 0 4.795.886 6.6 2.36M12.5 21H3v-.83a6 6 0 016-6h1.5a6 6 0 013 1.1" />
                            </svg>
                        </a>
                    ` : ''}
                    ${book.user_has_access ? `
                        <a href="${book.url}" title="Open Book" style="color: #ef4444; display: flex;" onclick="event.stopPropagation()">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    ` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
</x-app-layout>
