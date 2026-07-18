<div wire:poll.10s>
    <a href="{{ route('notifications.index') }}"
       class="cb-nav-link {{ Route::is('notifications.index') ? 'active' : '' }}">

        {{-- Bell icon --}}
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11
                   a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341
                   C7.67 6.165 6 8.388 6 11v3.159
                   c0 .538-.214 1.055-.595 1.436L4 17h5
                   m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        <span>Notifications</span>

        @if($unreadCount > 0)
            <span class="cb-nav-badge">{{ $unreadCount }}</span>
        @endif
    </a>
</div>
