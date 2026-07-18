<x-app-layout>
<div style="padding: 2rem; background: #fff; min-height: 100vh;">

    {{-- ── Page Header ── --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em; margin: 0;">
            {{ $activeBusiness->name ?? 'My Business' }}
        </h1>
        @if(in_array($role, ['primary_admin', 'admin']))
        <a href="{{ route('settings.index') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: #4f46e5; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: background 0.2s;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            Business Team
        </a>
        @endif
    </div>

    {{-- ── Dismissible Banner ── --}}
    <div x-data="{ open: true }" x-show="open" style="display: flex; align-items: flex-start; gap: 14px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 14px 16px; margin-bottom: 1.75rem;">
        <div style="flex-shrink: 0; width: 36px; height: 36px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20" style="color: #4338ca;">
                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4z"/>
            </svg>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; font-size: 0.9rem; color: #1e1b4b; margin-bottom: 2px;">Admin is now 'Book Admin'</div>
            <div style="font-size: 0.8125rem; color: #4b5563;">We've renamed the role to make bookkeeping permissions easier to understand.</div>
        </div>
        <button @click="open = false" type="button" style="background: none; border: none; cursor: pointer; color: #6b7280; padding: 4px; line-height: 1;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Main 2-column layout ── --}}
    <div style="display: flex; gap: 2rem; align-items: flex-start;">

        {{-- ── Left Column: Books List ── --}}
        <div style="flex: 1; min-width: 0;">

            {{-- Toolbar --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap;">
                {{-- Search + Sort --}}
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="position: relative;">
                        <svg width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" placeholder="Search by book name..." style="padding: 8px 36px 8px 32px; border: 1px solid #e2e8f0; border-radius: 7px; font-size: 0.8125rem; width: 240px; outline: none; color: #334155; background: #fff; font-family: inherit;">
                        <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #9ca3af; border: 1px solid #e2e8f0; border-radius: 4px; padding: 1px 5px;">/</span>
                    </div>

                    <div style="position: relative;">
                        <select style="padding: 8px 32px 8px 10px; border: 1px solid #e2e8f0; border-radius: 7px; font-size: 0.8125rem; color: #334155; background: #fff; appearance: none; outline: none; cursor: pointer; font-family: inherit; min-width: 175px;">
                            <option>Sort By: Last Updated</option>
                            <option>Sort By: Name (A–Z)</option>
                            <option>Sort By: Name (Z–A)</option>
                        </select>
                        <svg width="12" height="12" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Add New Book --}}
                @if(in_array($role, ['primary_admin', 'admin']))
                <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: #4f46e5; color: white; border-radius: 7px; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Book
                </a>
                @endif
            </div>

            {{-- Books List --}}
            <div style="border-top: 1px solid #f1f5f9;">
                @forelse($books as $book)
                    @php
                        $income  = $book->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
                        $expense = $book->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
                        $balance = $income - $expense;
                        $balanceColor = $balance >= 0 ? '#059669' : '#dc2626';
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 4px; border-bottom: 1px solid #f1f5f9; {{ !$book->user_has_access ? 'opacity: 0.65;' : '' }}">
                        {{-- Left: icon + name + subtitle --}}
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: #eef2ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="18" height="18" fill="none" stroke="#4f46e5" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.9375rem; color: #0f172a; letter-spacing: 0.03em; text-transform: uppercase;">{{ $book->name }}</div>
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

                            <div style="display: flex; align-items: center; gap: 14px; color: #4f46e5;">
                                @if(in_array($role, ['primary_admin', 'admin']))
                                <a href="{{ route('books.edit', $book) }}" title="Edit" style="color: #4f46e5; display: flex;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                                <button type="button" title="Duplicate" style="background: none; border: none; cursor: pointer; color: #4f46e5; display: flex; padding: 0;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                    </svg>
                                </button>
                                <a href="{{ route('books.users', $book) }}" title="Manage Team" style="color: #4f46e5; display: flex;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                </a>
                                @endif

                                @if($book->user_has_access)
                                <a href="{{ route('books.show', $book) }}" title="Open Book" style="color: #e53e3e; display: flex;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 4rem 2rem; color: #94a3b8;">
                        <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p style="font-size: 0.9375rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem;">No books yet</p>
                        <p style="font-size: 0.8125rem;">Get started by creating a new book.</p>
                        @if(in_array($role, ['primary_admin', 'admin']))
                        <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 1rem; padding: 9px 18px; background: #4f46e5; color: white; border-radius: 7px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            New Book
                        </a>
                        @endif
                    </div>
                @endforelse
            </div>

        </div>{{-- End Left Column --}}

        {{-- ── Right Column: Support Card ── --}}
        <div style="width: 220px; flex-shrink: 0;">
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div style="width: 40px; height: 40px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="22" height="22" fill="#059669" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div style="font-weight: 700; font-size: 0.9rem; color: #0f172a; margin-bottom: 5px;">Need help in business setup?</div>
                <div style="font-size: 0.78rem; color: #64748b; margin-bottom: 14px; line-height: 1.5;">Our support team will help you</div>
                <a href="#" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.8125rem; font-weight: 600; color: #4f46e5; text-decoration: none;">
                    Contact Us
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

    </div>{{-- End 2-column layout --}}

</div>
</x-app-layout>
