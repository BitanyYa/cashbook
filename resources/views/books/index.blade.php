<x-app-layout>

<style>
/* ── Books index mobile-first ──────────────── */
.books-page {
    padding: 0;
    background: #f5f5f5;
    min-height: 100vh;
}

/* Role banner */
.role-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .625rem 1rem;
    background: #eef2ff;
    font-size: .8125rem;
    color: #334155;
    border-bottom: 1px solid #e0e7ff;
}
.role-banner strong { color: #1e40af; }

/* Books header */
.bh {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .875rem 1rem .5rem;
    background: #fff;
}
.bh-title { font-size: 1.0625rem; font-weight: 700; color: #0f172a; margin: 0; }
.bh-icons { display: flex; align-items: center; gap: .75rem; }
.bh-icon  { color: #3b82f6; display: flex; background: none; border: none; cursor: pointer; padding: 0; }

/* Toolbar */
.books-toolbar {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    padding: .75rem 1rem;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
}
@media (min-width: 600px) {
    .books-toolbar {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}
.books-search-wrap { position: relative; flex: 1; max-width: 100%; }
@media (min-width: 600px) { .books-search-wrap { max-width: 260px; } }
.books-search {
    width: 100%;
    box-sizing: border-box;
    padding: .55rem .75rem .55rem 2.25rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: .875rem;
    color: #334155;
    background: #f8fafc;
    outline: none;
    font-family: inherit;
    transition: border-color .15s;
}
.books-search:focus { border-color: #3b82f6; background: #fff; }

/* Book list */
.books-list {
    background: #fff;
    margin: .75rem;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
@media (min-width: 600px) { .books-list { margin: 1rem 1.5rem; } }

/* Each book row */
.book-row {
    display: flex;
    align-items: center;
    gap: .875rem;
    padding: .875rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background .1s;
}
.book-row:last-child { border-bottom: none; }
.book-row:active, .book-row:hover { background: #f8fafc; }

.book-icon-wrap {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: #eff6ff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.book-info { flex: 1; min-width: 0; }
.book-name {
    font-size: .9375rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
@media (min-width: 400px) {
    .book-name { white-space: normal; }
}
.book-meta {
    font-size: .75rem;
    color: #94a3b8;
    margin-top: .2rem;
}

/* Add book FAB on mobile */
.fab-add {
    position: fixed;
    bottom: calc(1.25rem + env(safe-area-inset-bottom));
    right: 1.25rem;
    width: 52px; height: 52px;
    background: #3b82f6;
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(59,130,246,.4);
    z-index: 50;
    text-decoration: none;
}
@media (min-width: 600px) { .fab-add { display: none; } }
.add-btn-desktop {
    display: none;
}
@media (min-width: 600px) {
    .add-btn-desktop {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #3b82f6;
        color: #fff;
        border-radius: 8px;
        font-size: .875rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }
}
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
        <a href="{{ route('settings.index', $activeBusiness) }}"
           style="font-size:.8125rem;font-weight:600;color:#3b82f6;text-decoration:none;">View</a>
        @endif
    </div>
    @endif

    {{-- ── Header ── --}}
    <div class="bh">
        <h1 class="bh-title">Your Books</h1>
        <div class="bh-icons">
            {{-- Sort icon --}}
            <button class="bh-icon" title="Sort" onclick="document.getElementById('bookSort').focus()">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M6 12h12M10 17h4"/>
                </svg>
            </button>
            {{-- Search toggle --}}
            <button class="bh-icon" title="Search" onclick="document.getElementById('bookSearchInput').focus()">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
            </button>
            {{-- Desktop Business Team button --}}
            @if(in_array($role, ['primary_admin','admin']))
            <a href="{{ route('settings.index', $activeBusiness) }}" class="desktop-only"
               style="display:none;align-items:center;gap:5px;padding:5px 12px;border:1px solid #c7d2fe;border-radius:6px;background:#fff;color:#4338ca;font-size:.8rem;font-weight:600;text-decoration:none;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0112.75 21.5h-1.5a2.25 2.25 0 01-2.25-2.263V19.13m0-13.076c.725.318 1.488.485 2.25.485h1.5c.762 0 1.525-.167 2.25-.485m-7.5 0a3 3 0 106 0m-6 0a3.001 3.001 0 003 2.87"/>
                </svg>
                Business Team
            </a>
            @endif
        </div>
    </div>

    {{-- ── Toolbar ── --}}
    <form id="booksFilterForm" method="GET" action="{{ route('books.index') }}">
        <div class="books-toolbar">
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
                <select name="sort" id="bookSort" onchange="fetchBooks(1)"
                        style="padding:6px 28px 6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.8125rem;color:#334155;background:#fff;appearance:none;outline:none;cursor:pointer;font-family:inherit;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E\");background-position:right .4rem center;background-repeat:no-repeat;background-size:1.25em;">
                    <option value="updated_at_desc" {{ request('sort','updated_at_desc')=='updated_at_desc'?'selected':'' }}>Last Updated</option>
                    <option value="name_asc"        {{ request('sort')=='name_asc'       ?'selected':'' }}>Name A–Z</option>
                    <option value="name_desc"       {{ request('sort')=='name_desc'      ?'selected':'' }}>Name Z–A</option>
                    <option value="updated_at_asc"  {{ request('sort')=='updated_at_asc' ?'selected':'' }}>Oldest</option>
                </select>
                <a href="{{ route('books.create') }}" class="add-btn-desktop">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Book
                </a>
            </div>
        </div>
    </form>

    {{-- ── Books list ── --}}
    <div id="books-list-container" style="position:relative;">
        <div id="books-loading-overlay"
             style="display:none;position:absolute;inset:0;background:rgba(255,255,255,.7);z-index:10;align-items:center;justify-content:center;border-radius:12px;">
            <span style="font-size:.85rem;font-weight:600;color:#3b82f6;">Loading...</span>
        </div>

        <div id="books-rows" class="books-list">
            @forelse($books as $book)
            @php
                $income  = $book->transactions()->where('type','income')->where('status','approved')->sum('amount');
                $expense = $book->transactions()->where('type','expense')->where('status','approved')->sum('amount');
                $balance = $income - $expense;
            @endphp
            <div class="book-row"
                 onclick="window.location='{{ route('books.show', $book) }}'">
                <div class="book-icon-wrap">
                    <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.97 5.97 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94-3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <div class="book-info">
                    <div class="book-name">{{ $book->name }}</div>
                    <div class="book-meta">
                        {{ $book->users()->count() }} {{ Str::plural('Member', $book->users()->count()) }}
                        &nbsp;&middot;&nbsp;
                        Updated on {{ $book->updated_at->format('M d Y') }}
                    </div>
                </div>
                {{-- desktop only: show balance + icons --}}
                <div class="desktop-only" style="align-items:center;gap:.875rem;flex-shrink:0;">
                    <span style="font-weight:700;font-size:.9375rem;color:{{ $balance>=0?'#10b981':'#ef4444' }};">
                        {{ number_format(abs($balance)) }}
                    </span>
                    @if(in_array($role,['primary_admin','admin']))
                    <a href="{{ route('books.edit',$book) }}" style="color:#3b82f6;display:flex;" onclick="event.stopPropagation()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                    </a>
                    @endif
                    <a href="{{ route('books.show',$book) }}" style="color:#ef4444;display:flex;" onclick="event.stopPropagation()">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:3.5rem 2rem;color:#94a3b8;">
                <svg width="44" height="44" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p style="font-size:.9375rem;font-weight:600;color:#475569;margin-bottom:.4rem;">No books yet</p>
                <p style="font-size:.8125rem;">Create your first cashbook to get started.</p>
                @if(in_array($role,['primary_admin','admin']))
                <a href="{{ route('books.create') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:1rem;padding:9px 20px;background:#3b82f6;color:#fff;border-radius:8px;font-size:.875rem;font-weight:600;text-decoration:none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Book
                </a>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div id="books-pagination" style="margin:.75rem 1rem 5rem;">
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
        c.innerHTML = `<div style="text-align:center;padding:3.5rem 2rem;color:#94a3b8;">
            <p style="font-size:.9375rem;font-weight:600;color:#475569;margin-bottom:.4rem;">No books found</p>
            <p style="font-size:.8125rem;">Try adjusting your search.</p></div>`;
        return;
    }
    const isAdmin = ['primary_admin','admin'].includes(userRole);
    c.innerHTML = books.map(b => `
        <div class="book-row" onclick="window.location='${b.url}'">
            <div class="book-icon-wrap">
                <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.97 5.97 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94-3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
            </div>
            <div class="book-info">
                <div class="book-name">${esc(b.name)}</div>
                <div class="book-meta">${b.members_count} ${b.members_count===1?'Member':'Members'} &middot; Updated on ${b.updated_formatted||b.updated_human}</div>
            </div>
            <div class="desktop-only" style="align-items:center;gap:.875rem;flex-shrink:0;">
                <span style="font-weight:700;font-size:.9375rem;color:${b.balance_color};">${b.balance_formatted}</span>
                ${isAdmin?`<a href="${b.edit_url}" style="color:#3b82f6;display:flex;" onclick="event.stopPropagation()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                </a>`:''}
                <a href="${b.url}" style="color:#ef4444;display:flex;" onclick="event.stopPropagation()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
            </div>
        </div>`).join('');
}
</script>
</x-app-layout>
