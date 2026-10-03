<x-app-layout>

<style>
/* ── Books index modern styling ──────────────── */
.books-page {
    padding: 1.5rem;
    background: var(--gray-50);
    min-height: calc(100vh - 65px);
}
@media (max-width: 768px) {
    .books-page { padding: 1rem; }
}

/* Role banner */
.role-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.25rem;
    background: #eff6ff;
    font-size: 0.8125rem;
    color: #1e40af;
    border: 1px solid #bfdbfe;
    border-radius: var(--border-radius-sm);
    margin-bottom: 1.25rem;
}
.role-banner strong { color: #1e3a8a; font-weight: 700; }

/* Books header */
.bh {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}
.bh-title { font-size: 1.375rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em; }
.bh-icons { display: flex; align-items: center; gap: 0.75rem; }

/* Toolbar */
.books-toolbar-wrap {
    background: #ffffff;
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    padding: 0.875rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.875rem;
}

.books-search-wrap { position: relative; flex: 1; min-width: 220px; max-width: 340px; }
.books-search {
    width: 100%;
    height: 38px;
    box-sizing: border-box;
    padding: 0 0.75rem 0 2.25rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius-sm);
    font-size: 0.8125rem;
    color: #0f172a;
    background: #ffffff;
    outline: none;
    font-family: inherit;
    transition: all 0.15s ease-in-out;
}
.books-search:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14); }

.books-sort-select {
    height: 38px;
    padding: 0 2rem 0 0.875rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius-sm);
    font-size: 0.8125rem;
    font-weight: 500;
    color: #334155;
    background: #ffffff;
    appearance: none;
    outline: none;
    cursor: pointer;
    font-family: inherit;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.625rem center;
    background-repeat: no-repeat;
    background-size: 1.1em;
    transition: border-color 0.15s ease;
}
.books-sort-select:focus { border-color: var(--primary-color); }

/* Book Card Items */
.books-card-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.book-card-item {
    background: #ffffff;
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    padding: 1.125rem 1.375rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    cursor: pointer;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
}

.book-card-item:hover {
    border-color: var(--gray-300);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    transform: translateY(-1px);
}

.book-card-main {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
    flex: 1;
}

.book-icon-badge {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #eff6ff;
    border: 1px solid #dbeafe;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--primary-color);
}

.book-card-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
    margin-bottom: 2px;
}

.book-card-meta {
    font-size: 0.78rem;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.book-card-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-shrink: 0;
}

.book-balance-wrap {
    text-align: right;
}

.book-balance-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.book-balance-amount {
    font-size: 1rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}

/* Mobile FAB: Add New Book */
.fab-add {
    position: fixed;
    bottom: calc(1.5rem + env(safe-area-inset-bottom));
    right: 1.5rem;
    width: 52px; height: 52px;
    background: var(--primary-color);
    color: #ffffff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
    z-index: 50;
    text-decoration: none;
    transition: transform 0.15s ease;
}
.fab-add:hover { transform: scale(1.05); }
@media (min-width: 640px) { .fab-add { display: none; } }
</style>

<div class="books-page">

    {{-- ── Role banner ── --}}
    @if(isset($role))
    <div class="role-banner">
        <span>
            <svg width="15" height="15" fill="#3b82f6" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:5px;">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2a10 10 0 100 20A10 10 0 0012 2zm1 14H11v-2h2v2zm0-4H11V7h2v5z"/>
            </svg>
            Your Role: <strong>{{ ucfirst(str_replace('_', ' ', $role)) }}</strong>
        </span>
        @if(in_array($role, ['primary_admin','admin']))
        <a href="{{ route('settings.index', $activeBusiness) }}" style="font-size:.8125rem;font-weight:600;color:#2563eb;text-decoration:none;">Manage Team</a>
        @endif
    </div>
    @endif

    {{-- ── Header ── --}}
    <div class="bh">
        <h1 class="bh-title">Your CashBooks</h1>
        <div class="bh-icons">
            @if(in_array($role, ['primary_admin','admin']))
            <a href="{{ route('books.create') }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Book
            </a>
            @endif
        </div>
    </div>

    {{-- ── Toolbar ── --}}
    <form id="booksFilterForm" method="GET" action="{{ route('books.index') }}">
        <div class="books-toolbar-wrap">
            <div class="books-search-wrap">
                <svg width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"
                     style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input id="bookSearchInput" name="q" type="text" value="{{ request('q') }}"
                       placeholder="Search by book name..."
                       oninput="debouncedSearch()"
                       class="books-search">
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;">
                <select name="sort" id="bookSort" onchange="fetchBooks(1)" class="books-sort-select">
                    <option value="updated_at_desc" {{ (request('sort')=='updated_at_desc' || (!request('sort') && ($sort ?? '')=='updated_at_desc'))?'selected':'' }}>Last Updated</option>
                    <option value="name_asc"        {{ (request('sort')=='name_asc'        || (!request('sort') && ($sort ?? '')=='name_asc'))?'selected':'' }}>Name A–Z</option>
                    <option value="name_desc"       {{ (request('sort')=='name_desc'       || (!request('sort') && ($sort ?? '')=='name_desc'))?'selected':'' }}>Name Z–A</option>
                    <option value="updated_at_asc"  {{ (request('sort')=='updated_at_asc'  || (!request('sort') && ($sort ?? '')=='updated_at_asc'))?'selected':'' }}>Oldest</option>
                </select>
            </div>
        </div>
    </form>

    {{-- ── Books list ── --}}
    <div id="books-list-container" style="position:relative;">
        <div id="books-loading-overlay"
             style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.7);z-index:10;align-items:center;justify-content:center;border-radius:12px;">
            <span style="font-size:.85rem;font-weight:600;color:#2563eb;">Loading...</span>
        </div>

        <div id="books-rows" class="books-card-grid">
            @forelse($books as $book)
            @php
                $income  = $book->transactions()->where('type','income')->where('status','approved')->sum('amount');
                $expense = $book->transactions()->where('type','expense')->where('status','approved')->sum('amount');
                $balance = $income - $expense;
            @endphp
            <div class="book-card-item" onclick="window.location='{{ route('books.show', $book) }}'">
                <div class="book-card-main">
                    <div class="book-icon-badge">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div style="min-width:0;flex:1;">
                        <div class="book-card-title">{{ $book->name }}</div>
                        <div class="book-card-meta">
                            <span>{{ $book->users()->count() }} {{ Str::plural('Member', $book->users()->count()) }}</span>
                            <span>&middot;</span>
                            <span>Updated {{ $book->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="book-card-right">
                    <div class="book-balance-wrap">
                        <div class="book-balance-label">Net Balance</div>
                        <div class="book-balance-amount" style="color:{{ $balance>=0?'#059669':'#dc2626' }};">
                            {{ $balance>=0?'':'-' }}{{ number_format(abs($balance)) }}
                        </div>
                    </div>
                    @if(in_array($role,['primary_admin','admin']))
                    <a href="{{ route('books.edit', $book) }}" class="btn btn-secondary btn-sm" style="padding:0.35rem 0.625rem;" onclick="event.stopPropagation()" title="Settings">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                    </a>
                    @endif
                    <a href="{{ route('books.show', $book) }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
                        Open
                    </a>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:3.5rem 2rem;color:#94a3b8;background:#fff;border-radius:12px;border:1px solid #e2e8f0;">
                <svg width="44" height="44" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p style="font-size:.9375rem;font-weight:600;color:#475569;margin-bottom:.4rem;">No books yet</p>
                <p style="font-size:.8125rem;">Create your first cashbook to get started.</p>
                @if(in_array($role,['primary_admin','admin']))
                <a href="{{ route('books.create') }}" class="btn btn-primary btn-sm" style="margin-top:1rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Book
                </a>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div id="books-pagination" style="margin:1.25rem 0 3rem;">
        {{ $books->links() }}
    </div>

</div>

{{-- Mobile FAB: Add New Book --}}
@if(in_array($role,['primary_admin','admin']))
<a href="{{ route('books.create') }}" class="fab-add" title="Add New Book">
    <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
</a>
@endif

<script>
let searchTimer = null;
let currentController = null;
const userRole = "{{ $role }}";

function debouncedSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchBooks(1), 350);
}

function fetchBooks(page = 1) {
    if (currentController) currentController.abort();
    currentController = new AbortController();

    const q    = document.getElementById('bookSearchInput')?.value || '';
    const sort = document.getElementById('bookSort')?.value        || 'updated_at_desc';
    const overlay = document.getElementById('books-loading-overlay');
    if (overlay) overlay.style.display = 'flex';

    const params = new URLSearchParams({ q, sort, page });
    const url    = `{{ route('books.index') }}?` + params.toString();
    history.pushState({}, '', url);

    fetch(url, {
        signal: currentController.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (overlay) overlay.style.display = 'none';
        if (data.success) {
            renderBooks(data.books);
            const pagEl = document.getElementById('books-pagination');
            if (pagEl && data.pagination) pagEl.innerHTML = data.pagination;
        }
    })
    .catch(err => {
        if (err.name !== 'AbortError' && overlay) overlay.style.display = 'none';
    });
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function renderBooks(books) {
    const c = document.getElementById('books-rows');
    if (!c) return;
    if (!books || !books.length) {
        c.innerHTML = `<div style="text-align:center;padding:3.5rem 2rem;color:#94a3b8;background:#fff;border-radius:12px;border:1px solid #e2e8f0;">
            <p style="font-size:.9375rem;font-weight:600;color:#475569;margin-bottom:.4rem;">No books found</p>
            <p style="font-size:.8125rem;">Try adjusting your search.</p></div>`;
        return;
    }
    const isAdmin = ['primary_admin','admin'].includes(userRole);
    c.innerHTML = books.map(b => `
        <div class="book-card-item" onclick="window.location='${b.url}'">
            <div class="book-card-main">
                <div class="book-icon-badge">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div style="min-width:0;flex:1;">
                    <div class="book-card-title">${esc(b.name)}</div>
                    <div class="book-card-meta">
                        <span>${b.members_count} ${b.members_count===1?'Member':'Members'}</span>
                        <span>&middot;</span>
                        <span>Updated ${esc(b.updated_formatted||b.updated_human)}</span>
                    </div>
                </div>
            </div>

            <div class="book-card-right">
                <div class="book-balance-wrap">
                    <div class="book-balance-label">Net Balance</div>
                    <div class="book-balance-amount" style="color:${b.balance_color};">
                        ${esc(b.balance_formatted)}
                    </div>
                </div>
                ${isAdmin?`<a href="${b.edit_url}" class="btn btn-secondary btn-sm" style="padding:0.35rem 0.625rem;" onclick="event.stopPropagation()" title="Settings">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </a>`:''}
                <a href="${b.url}" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
                    Open
                </a>
            </div>
        </div>`).join('');
}

document.addEventListener('click', function(e) {
    const link = e.target.closest('#books-pagination a');
    if (link && link.href) {
        e.preventDefault();
        try {
            const urlObj = new URL(link.href);
            const page = urlObj.searchParams.get('page') || 1;
            fetchBooks(page);
        } catch (err) {
            window.location.href = link.href;
        }
    }
});
</script>
</x-app-layout>
