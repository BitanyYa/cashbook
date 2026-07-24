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

    {{-- ── Role Banner ── --}}
    <div style="display: flex; align-items: center; gap: 8px; background: #ecfdf5; border-radius: 6px; padding: 8px 12px; margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #10b981; color: white; flex-shrink: 0;">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
        </div>
        <span style="font-size: 0.8125rem; color: #065f46; font-weight: 500;">
            Your Role: <strong style="font-weight: 700;">{{ ucfirst(str_replace('_', ' ', $role)) }}</strong> 
            <a href="#" style="color: #059669; text-decoration: underline; margin-left: 4px; font-weight: 600;">View</a>
        </span>
    </div>

    {{-- ── Dismissible Lavender Carousel Banner ── --}}
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.25rem; overflow: visible;">
        <!-- The Lavender Banner -->
        <div x-data="{ open: true }" x-show="open" style="flex: 1; display: flex; align-items: center; gap: 12px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px; padding: 12px 14px; position: relative;">
            <div style="flex-shrink: 0; width: 34px; height: 34px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925-3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 003 9.75c0 1.205.568 2.277 1.455 2.966a3.75 3.75 0 005.109 5.284zm0 0h.008v.008H12V18zm-.375.375h.008v.008h-.008v-.008z" />
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 700; font-size: 0.85rem; color: #1e1b4b; margin-bottom: 2px;">Admin is now 'Book Admin'</div>
                <div style="font-size: 0.78rem; color: #4c1d95; line-height: 1.45;">We've renamed the role to make bookkeeping permissions easier to understand.</div>
            </div>
            <button @click="open = false" type="button" style="background: none; border: none; cursor: pointer; color: #7c3aed; padding: 2px; line-height: 1; margin-left: 8px; display: flex; align-items: center;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Carousel Next Button -->
        <button type="button" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center; cursor: pointer; color: #334155; transition: transform 0.2s;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Carousel Peek Slide -->
        <div style="flex-shrink: 0; width: 40px; height: 60px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 8px 0 0 8px; opacity: 0.6; display: flex; align-items: center; justify-content: center; overflow: hidden; padding-left: 6px;">
            <span style="font-size: 0.7rem; font-weight: 700; color: #4c1d95; white-space: nowrap;">New ro...</span>
        </div>
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
                        <svg width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2.2" viewBox="0 0 24 24" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" placeholder="Search by book name..." style="padding: 7px 32px 7px 30px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.8125rem; width: 240px; outline: none; color: #334155; background: #fff; font-family: inherit;">
                        <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.65rem; color: #9ca3af; border: 1px solid #e2e8f0; border-radius: 4px; padding: 1px 5px;">/</span>
                    </div>

                    <div style="position: relative;">
                        <select style="padding: 7px 32px 7px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.8125rem; color: #334155; background: #fff; appearance: none; outline: none; cursor: pointer; font-family: inherit; min-width: 175px;">
                            <option>Sort By: Last Updated</option>
                            <option>Sort By: Name (A–Z)</option>
                            <option>Sort By: Name (Z–A)</option>
                        </select>
                        <svg width="12" height="12" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
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

            {{-- Books List --}}
            <div style="border-top: 1px solid #e2e8f0; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 0 1rem;">
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
                                <button type="button" title="Duplicate" onclick="event.stopPropagation()" style="background: none; border: none; cursor: pointer; color: #3b82f6; display: flex; padding: 0;">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="9" y="9" width="11" height="11" rx="2" ry="2" />
                                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" />
                                    </svg>
                                </button>
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
                    <div style="text-align: center; padding: 4rem 2rem; color: #94a3b8;">
                        <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p style="font-size: 0.9375rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem;">No books yet</p>
                        <p style="font-size: 0.8125rem;">Get started by creating a new book.</p>
                        @if(in_array($role, ['primary_admin', 'admin']))
                        <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 1rem; padding: 9px 18px; background: #3b82f6; color: white; border-radius: 6px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
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
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); display: flex; flex-direction: column; align-items: flex-start;">
                <div style="width: 42px; height: 42px; background: #e8fbf3; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                    <svg width="24" height="24" fill="#10b981" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-bottom: 4px; line-height: 1.35;">Need help in business setup?</div>
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px; line-height: 1.4;">Our support team will help you</div>
                <a href="#" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; font-weight: 700; color: #3b82f6; text-decoration: none;">
                    Contact Us
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

    </div>{{-- End 2-column layout --}}

</div>
</x-app-layout>
