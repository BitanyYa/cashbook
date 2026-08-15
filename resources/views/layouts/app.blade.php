<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CashBook') }}</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Custom CSS & Vite -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

        <!-- jQuery and DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

        <style>
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                overflow: hidden !important;
            }
            body.app-layout {
                height: 100vh;
                display: flex;
                flex-direction: column;
                overflow: hidden !important;
            }
            .app-main {
                flex: 1;
                display: flex;
                height: calc(100vh - 60px);
                overflow: visible !important;
                min-width: 0;
            }
            .app-content {
                flex: 1;
                min-width: 0;
                height: 100%;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 2rem;
                background: var(--gray-50);
                display: flex;
                flex-direction: column;
            }

            /* ── Mobile layout ─────────────────────────── */
            @media (max-width: 767px) {
                /* hide sidebar completely on mobile */
                .app-sidebar { display: none !important; }

                /* content fills full width with bottom safe area clearance */
                .app-content {
                    padding: 0.75rem 0.75rem calc(5rem + env(safe-area-inset-bottom, 0px)) 0.75rem !important;
                    overflow-y: auto !important;
                    overflow-x: hidden !important;
                    -webkit-overflow-scrolling: touch;
                }

                /* shrink topbar on mobile */
                .app-header { padding: 0 0.75rem; }
                .header-content { gap: 0.5rem; }

                /* hide logo text, keep icon */
                .app-logo-text { display: none; }

                /* truncate business name in selector */
                .header-content .btn span {
                    max-width: 120px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    display: inline-block;
                    vertical-align: middle;
                }

                /* safe area bottom padding for fixed bars */
                .mobile-bottom-bar {
                    padding-bottom: calc(0.875rem + env(safe-area-inset-bottom, 0px));
                }
            }

            /* ── Desktop: restore sidebar ──────────────── */
            @media (min-width: 768px) {
                .mobile-only { display: none !important; }
                .desktop-only { display: flex !important; }
            }
            @media (max-width: 767px) {
                .desktop-only { display: none !important; }
                .mobile-only  { display: flex !important; }
            }
        </style>

        @livewireStyles
    </head>
    <body class="app-layout" x-data="{ sidebarOpen: false }">
        <!-- Top Navigation -->
        <header class="app-header">
            <div class="header-content">
                <!-- Left: Logo -->
                <div class="flex items-center">
                    <a href="{{ route('books.index') }}" class="app-logo-link" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                        <div style="background: #3b82f6; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.125rem; font-family: sans-serif;">C</div>
                        <span style="font-weight: 800; font-size: 1.125rem; color: #1e3a8a; letter-spacing: 0.05em; font-family: sans-serif;">CASHBOOK</span>
                    </a>
                </div>

                <!-- Center: Business Selector -->
                <div class="flex items-center">
                    @if($activeBusiness ?? null)
                        <details class="biz-switcher" onclick="this.open && event.stopPropagation()">
                            <summary style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:.875rem;color:#334155;font-weight:500;cursor:pointer;list-style:none;white-space:nowrap;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21h10.5V3.75c0-.621-.504-1.125-1.125-1.125H7.875c-.621 0-1.125.504-1.125 1.125V21z"/>
                                </svg>
                                <span style="overflow:hidden;text-overflow:ellipsis;">{{ $activeBusiness->name }}</span>
                                <svg width="12" height="12" fill="#64748b" viewBox="0 0 20 20" style="flex-shrink:0;">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </summary>
                            <div style="position:absolute;top:calc(100% + 4px);left:50%;transform:translateX(-50%);width:260px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:99999;overflow:hidden;">
                                @foreach(Auth::user()->businesses as $business)
                                <form method="POST" action="{{ route('business.switch', $business) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 14px;border:none;background:{{ $business->id === $activeBusiness->id ? '#f0f4ff' : 'transparent' }};cursor:pointer;font-family:inherit;text-align:left;">
                                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $business->id === $activeBusiness->id ? '#3b82f6' : '#cbd5e1' }};flex-shrink:0;"></div>
                                        <div style="min-width:0;">
                                            <div style="font-size:.875rem;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $business->name }}</div>
                                            <div style="font-size:.75rem;color:#64748b;">{{ $business->currency }}</div>
                                        </div>
                                    </button>
                                </form>
                                @endforeach
                                <div style="height:1px;background:#e9ecef;margin:4px 0;"></div>
                                <a href="{{ route('businesses.index') }}" style="display:block;padding:10px 14px;font-size:.875rem;color:#3b82f6;font-weight:500;text-decoration:none;">👁 View Businesses</a>
                                <a href="{{ route('businesses.create') }}" style="display:block;padding:10px 14px;font-size:.875rem;color:#3b82f6;font-weight:500;text-decoration:none;">➕ New Business</a>
                            </div>
                        </details>

                        <style>
                        .biz-switcher { position:relative; display:inline-block; }
                        .biz-switcher summary::-webkit-details-marker { display:none; }
                        .biz-switcher > div { display:none; }
                        .biz-switcher[open] > div { display:block; }
                        </style>

                        <script>
                        document.addEventListener('click', function(e) {
                            document.querySelectorAll('.biz-switcher[open]').forEach(function(el) {
                                if (!el.contains(e.target)) el.removeAttribute('open');
                            });
                        });
                        </script>
                    @else
                        <span style="color: var(--gray-500); font-size: 0.875rem;">No business selected</span>
                    @endif
                </div>

                <!-- Right: User Menu -->
                <div class="flex items-center" style="gap: 14px;">
                    <div class="dropdown" x-data="{ open: false }" style="position: relative;">
                        <button @click="open = !open" class="flex items-center" style="background: transparent; border: none; padding: 0; cursor: pointer; gap: 8px;">
                            <div style="width: 32px; height: 32px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">
                                <span>{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                            <span style="color: #334155; font-weight: 600; font-size: 0.875rem;" class="hidden sm:inline-block">
                                {{ Auth::user()->name }}
                            </span>
                            <svg style="width: 14px; height: 14px; color: #64748b;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false" class="dropdown-menu" style="left:auto;right:0;min-width:200px;">
                            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200);">
                                <div style="font-weight: 500; color: var(--gray-900);">{{ Auth::user()->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--gray-500);">{{ Auth::user()->email }}</div>
                            </div>
                            <a href="{{ route('businesses.index') }}" class="dropdown-item">Businesses</a>
                            <a href="{{ route('notifications.index') }}" class="dropdown-item">Notifications
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <span class="badge" style="background: var(--danger-color); color: white; padding: 0.25rem 0.5rem; border-radius: 9999px; margin-left: 0.5rem;">
                                        {{ Auth::user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('settings.index', $activeBusiness) }}" class="dropdown-item">Settings</a>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">Profile</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item" style="color: var(--danger-color); border: none; background: transparent; width: 100%; text-align: left; font-family: inherit;">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Content -->
        <div class="app-main">
            <!-- Sidebar -->
            @if($activeBusiness ?? null)
            @php
                $sidebarUserRole = Auth::user()->businesses()
                    ->where('business_id', $activeBusiness->id)
                    ->value('business_user.role');
            @endphp
            <aside
                class="app-sidebar"
                :class="{ 'open': sidebarOpen }"
                @click.away="sidebarOpen = false">

                {{-- ══════════════════════════════════
                     SECTION 1 — Book Keeping
                ══════════════════════════════════ --}}
                <div class="cb-sidebar-section">
                    <div class="cb-sidebar-label">
                        <span>Book Keeping</span>
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    {{-- Cashbooks top-level link --}}
                    <a href="{{ route('books.index') }}"
                       class="cb-nav-link {{ request()->routeIs('books.*') || request()->routeIs('transactions.*') || request()->routeIs('reports.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                                   C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                                   C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                                   C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Cashbooks</span>
                    </a>
                </div>

                {{-- ══════════════════════════════════
                     SECTION 2 — Settings
                     Only shown to primary_admin / admin
                ══════════════════════════════════ --}}
                @if(in_array($sidebarUserRole, ['primary_admin', 'admin']))
                <div class="cb-sidebar-section">
                    <div class="cb-sidebar-label">
                        <span>Settings</span>
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    {{-- Team → TeamController@index (settings.index) --}}
                    <a href="{{ route('settings.index', $activeBusiness) }}"
                       class="cb-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                                   c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857
                                   M7 20v-2c0-.656.126-1.283.356-1.857
                                   m0 0a5.002 5.002 0 019.288 0"/>
                        </svg>
                        <span>Team</span>
                    </a>

                    {{-- Admin Users Search & Cashbook Management --}}
                    <a href="{{ route('admin.users.index') }}"
                       class="cb-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                        </svg>
                        <span>Users</span>
                    </a>

                    {{-- Business → BusinessController@index (businesses.index) --}}
                    <a href="{{ route('businesses.index') }}"
                       class="cb-nav-link {{ request()->routeIs('businesses.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7V5a4 4 0 00-8 0v2"/>
                        </svg>
                        <span>Business Settings</span>
                    </a>

                    {{-- Subscription → dummy link --}}
                    <a href="#" class="cb-nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Subscription</span>
                    </a>
                </div>
                @endif

                {{-- ══════════════════════════════════
                     SECTION 3 — Others
                ══════════════════════════════════ --}}
                <div class="cb-sidebar-section">
                    <div class="cb-sidebar-label">
                        <span>Others</span>
                        <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    {{-- What's New --}}
                    <a href="#" class="cb-nav-link" style="display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
                        <span style="display: flex; align-items: center; gap: 0.625rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364.364l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span>What's New</span>
                        </span>
                        <span style="background: #10b981; color: white; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 4px;">New</span>
                    </a>

                    {{-- Help Docs --}}
                    <a href="#" class="cb-nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Help Docs</span>
                    </a>

                    {{-- Contact Us --}}
                    <a href="#" class="cb-nav-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Contact Us</span>
                    </a>
                </div>

                {{-- Sidebar Footer --}}
                <div class="sidebar-footer" style="padding: 1.25rem 1rem; border-top: 1px solid var(--gray-200); font-size: 0.75rem; color: #64748b; background: #ffffff; margin-top: auto;">
                    &copy; {{ date('Y') }} <strong style="color: #0f172a;">CashBook</strong>. All rights reserved.
                </div>

            </aside>
            @endif

            <!-- Global Flash Toast Notifications -->
            @if(session('success'))
                <div id="flash-success-toast" style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: #10b981; color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                    <button onclick="document.getElementById('flash-success-toast').remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 0.5rem; font-size: 1.1rem; line-height: 1;">×</button>
                </div>
                <script>setTimeout(() => { const t = document.getElementById('flash-success-toast'); if(t) t.remove(); }, 4000);</script>
            @endif

            @if(session('error'))
                <div id="flash-error-toast" style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: #ef4444; color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                    <button onclick="document.getElementById('flash-error-toast').remove()" style="background: none; border: none; color: white; cursor: pointer; margin-left: 0.5rem; font-size: 1.1rem; line-height: 1;">×</button>
                </div>
                <script>setTimeout(() => { const t = document.getElementById('flash-error-toast'); if(t) t.remove(); }, 5000);</script>
            @endif

            <!-- Main content -->
            <main class="app-content">
                <div style="flex: 1;">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @stack('scripts')
        @livewireScripts
    </body>
</html>
