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

        <!-- Custom CSS -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

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
            body.app-layout {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                margin: 0;
            }
            .app-main {
                flex: 1 0 auto; /* grow and take available space */
            }
            .app-footer {
                flex-shrink: 0;
                background-color: var(--gray-100);
                border-top: 1px solid var(--gray-300);
                padding: 1rem 2rem;
                font-size: 0.875rem;
                color: var(--gray-600);
                display: flex;
                justify-content: space-between;
                align-items: center;
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
                        <div class="dropdown" x-data="{ open: false }">
                            <button @click="open = !open" class="btn btn-secondary" style="display: flex; align-items: center; padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; font-size: 0.875rem; color: #334155; font-weight: 500; gap: 8px; cursor: pointer;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="color: #64748b;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21h10.5V3.75c0-.621-.504-1.125-1.125-1.125H7.875c-.621 0-1.125.504-1.125 1.125V21z" />
                                </svg>
                                <span>{{ $activeBusiness->name }}</span>
                                <svg style="width: 14px; height: 14px; color: #64748b;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="dropdown-menu slide-down" style="min-width: 250px; left: 50%; transform: translateX(-50%);">

                                @foreach(Auth::user()->businesses as $business)
                                    <form method="POST" action="{{ route('business.switch', $business) }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item {{ $business->id === $activeBusiness->id ? 'bg-gray-50' : '' }}" style="width: 100%; text-align: left; padding: 0.5rem 1rem; border: none; background: transparent; cursor: pointer; font-family: inherit;">
                                            <div style="display: flex; align-items: center;">
                                                <div style="width: 8px; height: 8px; background: {{ $business->id === $activeBusiness->id ? 'var(--primary-color)' : 'var(--gray-400)' }}; border-radius: 50%; margin-right: 12px;"></div>
                                                <div>
                                                    <div style="font-weight: 600;">{{ $business->name }}</div>
                                                    <div style="font-size: 0.75rem; color: var(--gray-500);">{{ $business->currency }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    </form>
                                @endforeach

                                <div class="dropdown-divider"></div>

                                <!-- View Current Business Button -->
                                <a href="{{ route('businesses.index') }}" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--primary-color); font-weight: 500;">
                                    👁 View Businesses
                                </a>

                                <!-- Create New Business Button -->
                                <a href="{{ route('businesses.create') }}" class="dropdown-item" style="display: block; padding: 0.5rem 1rem; color: var(--primary-color);">
                                    ➕ New Business
                                </a>
                            </div>
                        </div>
                    @else
                        <span style="color: var(--gray-500); font-size: 0.875rem;">No business selected</span>
                    @endif
                </div>

                <!-- Right: User Menu -->
                <div class="flex items-center" style="gap: 14px;">
                    {{-- Keyboard Shortcut Icon --}}
                    <button type="button" title="Keyboard Shortcuts" style="background: none; border: none; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center; padding: 4px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7 8h.01M11 8h.01M15 8h.01M7 12h.01M11 12h.01M15 12h.01M17 12h.01M7 16h10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

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
                        <div x-show="open" x-cloak @click.away="open = false" x-transition class="dropdown-menu slide-down max-w-xs overflow-auto">
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
                    ->value('role');
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

            </aside>
            @endif

            <!-- Main content -->
            <main class="app-content">
                {{ $slot }}
            </main>
        </div>

        <!-- Footer -->
        <footer class="app-footer"
            style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; padding: 1rem; border-top: 1px solid #e5e7eb; background-color: #f9fafb; font-family: sans-serif;">

            <!-- Left Section -->
            <div class="footer-left" style="font-size: 0.75rem; color: #6b7280;">
                &copy; {{ date('Y') }} <strong style="color:#111827;">CashBook</strong>. All rights reserved.
                <div class="footer-center" style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.25rem;">
                    - Riaz
                </div>
            </div>

            <!-- Right Section -->
            <div class="footer-right" style="display: flex; align-items: center; gap: 1rem; font-size: 0.75rem; color: #4b5563;">
                <span style="white-space: nowrap;">Version 1.0.0</span>

                <a href="https://github.com/Riaz-Mahmud/cashbook" target="_blank" rel="noopener noreferrer"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.6rem; background-color: #111827; color: white; border-radius: 0.25rem; text-decoration: none; font-size: 0.75rem; transition: background-color 0.2s;">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.387.6.113.82-.258.82-.577v-2.234c-3.338.726-4.033-1.61-4.033-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.09-.745.083-.73.083-.73 1.205.085 1.84 1.238 1.84 1.238 1.07 1.835 2.807 1.305 3.492.997.108-.775.418-1.305.76-1.605-2.665-.303-5.467-1.333-5.467-5.933 0-1.31.468-2.38 1.236-3.22-.124-.303-.536-1.523.117-3.176 0 0 1.008-.322 3.301 1.23a11.5 11.5 0 013.003-.404c1.018.005 2.044.138 3.003.404 2.291-1.552 3.297-1.23 3.297-1.23.655 1.653.243 2.873.12 3.176.77.84 1.235 1.91 1.235 3.22 0 4.61-2.807 5.627-5.48 5.922.43.372.823 1.103.823 2.222v3.293c0 .319.217.694.825.576C20.565 21.796 24 17.298 24 12c0-6.627-5.373-12-12-12z"/>
                    </svg>
                    <span style="white-space: nowrap;">GitHub</span>
                </a>
            </div>
        </footer>

        @stack('scripts')
        @livewireScripts
    </body>
</html>
