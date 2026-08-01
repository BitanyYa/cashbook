<x-app-layout>
    <div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; padding: 2rem;">
        <div style="max-width: 520px; width: 100%; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); padding: 2.5rem; text-align: center;">
            
            {{-- Pending Clock/User Icon --}}
            <div style="width: 72px; height: 72px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="36" height="36" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h2 style="font-size: 1.375rem; font-weight: 700; color: #0f172a; margin: 0 0 0.75rem;">
                Pending Cashbook Assignment
            </h2>

            <p style="font-size: 0.9375rem; color: #475569; line-height: 1.6; margin: 0 0 2rem;">
                You haven't been added to a cashbook yet. Please contact your administrator.
            </p>

            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('businesses.create') }}"
                   style="padding: 0.65rem 1.25rem; background: #2563eb; color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; text-decoration: none; display: inline-block;">
                    ➕ Create New Business
                </a>

                <button type="button" onclick="window.location.href='/dashboard'"
                        style="padding: 0.65rem 1.25rem; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; transition: background 0.15s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                    Refresh Status
                </button>

                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" 
                            style="padding: 0.65rem 1.25rem; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; transition: background 0.15s;"
                            onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
