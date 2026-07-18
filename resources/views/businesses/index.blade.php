<x-app-layout>
<div style="padding: 2rem;">

    {{-- ── Page header ── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Business Settings</h1>
            <p class="page-subtitle">Manage your business entities and switch between them.</p>
        </div>
        <a href="{{ route('businesses.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Business
        </a>
    </div>

    {{-- ── Active Business Settings form ── --}}
    @if($activeBusiness ?? null)
    <div class="card" style="margin-bottom: 2rem; max-width: 34rem;">
        <div class="card-header">
            <div>
                <h3 class="card-title">Business Settings</h3>
                <p class="card-subtitle">Update your active business name and currency.</p>
            </div>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('settings.business.update') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Business Name</label>
                    <input type="text" name="name" value="{{ old('name', $activeBusiness->name) }}"
                           required class="form-input">
                    <x-input-error :messages="$errors->get('name')" class="form-error" />
                </div>
                <div class="form-group">
                    <label class="form-label">
                        Currency
                        <span style="font-size: 0.75rem; color: var(--gray-400); font-weight: 400;">(3-letter code, e.g. ETB, USD)</span>
                    </label>
                    <input type="text" name="currency" value="{{ old('currency', $activeBusiness->currency) }}"
                           required maxlength="3" class="form-input">
                    <x-input-error :messages="$errors->get('currency')" class="form-error" />
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
    @endif

    {{-- ── All Businesses list ── --}}
    <div class="page-header" style="margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0;">All Businesses</h2>
            <p class="page-subtitle" style="font-size: 0.8125rem;">Manage your business entities and switch between them.</p>
        </div>
    </div>

    @if($businesses->count() > 0)
    <div class="card" style="overflow: hidden;">
        @foreach($businesses as $business)
            @php
                $isActive = Session::get('active_business_id') == $business->id;
            @endphp
            <div style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 0.875rem 1.25rem;
                    border-bottom: 1px solid var(--gray-200);
                    background: #fff;
                    transition: background 0.12s;
                    gap: 1rem;"
                 onmouseover="this.style.background='var(--gray-50)'"
                 onmouseout="this.style.background='#fff'">

                {{-- Left: icon + name + meta --}}
                <div style="display: flex; align-items: center; gap: 0.875rem; min-width: 0; flex: 1;">
                    <div style="
                            width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
                            background: {{ $isActive ? '#eef2ff' : 'var(--gray-100)' }};
                            border: 1px solid {{ $isActive ? '#c7d2fe' : 'var(--gray-200)' }};
                            display: flex; align-items: center; justify-content: center;">
                        <svg width="17" height="17" fill="none"
                             stroke="{{ $isActive ? 'var(--primary-color)' : 'var(--gray-400)' }}"
                             stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h3"/>
                        </svg>
                    </div>
                    <div style="min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span style="font-weight: 700; font-size: 0.9375rem; color: var(--gray-900); letter-spacing: 0.01em;">
                                {{ $business->name }}
                            </span>
                            @if($isActive)
                                <span class="badge badge-success" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Active
                                </span>
                            @endif
                        </div>
                        <div style="font-size: 0.78rem; color: var(--gray-400); margin-top: 0.125rem;">
                            {{ $business->currency }}
                            &bull; {{ $business->books->count() }} {{ Str::plural('book', $business->books->count()) }}
                            &bull; Since {{ $business->created_at->format('Y') }}
                        </div>
                    </div>
                </div>

                {{-- Right: action buttons --}}
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                    @if(!$isActive)
                    <form method="POST" action="{{ route('business.switch', $business) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Switch</button>
                    </form>
                    @endif

                    <a href="{{ route('businesses.edit', $business) }}" class="btn btn-secondary btn-sm">
                        Edit
                    </a>

                    @if($businesses->count() > 1)
                    <form method="POST" action="{{ route('businesses.destroy', $business) }}" style="margin: 0;"
                          onsubmit="return confirm('Delete {{ addslashes($business->name) }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm"
                                style="background: #fef2f2; color: var(--danger-color); border-color: #fecaca;">
                            Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Remove bottom border from last item --}}
        <style>
            .business-list-item:last-child { border-bottom: none !important; }
        </style>
    </div>

    <div style="margin-top: 1.25rem;">
        {{ $businesses->links() }}
    </div>

    @else
    {{-- Empty state --}}
    <div class="card" style="text-align: center; padding: 3.5rem 2rem;">
        <div style="width: 56px; height: 56px; background: #eef2ff; border-radius: 50%;
                    display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
            <svg width="26" height="26" fill="none" stroke="var(--primary-color)" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h3"/>
            </svg>
        </div>
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--gray-700); margin: 0 0 0.375rem;">No businesses yet</h3>
        <p style="font-size: 0.8125rem; color: var(--gray-500); margin: 0 0 1.25rem;">
            Get started by creating your first business.
        </p>
        <a href="{{ route('businesses.create') }}" class="btn btn-primary btn-sm">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Create Business
        </a>
    </div>
    @endif

</div>
</x-app-layout>
