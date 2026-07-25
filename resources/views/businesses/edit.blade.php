<x-app-layout>
    <div style="margin-bottom: 2rem;">
        @if (session('status'))
            <div style="margin-bottom: 1.5rem; background: #ecfdf5; border: 1px solid #bbf7d0; color: #15803d; border-radius: 0.5rem; padding: 1rem 1.5rem; font-weight: 500;">
                {{ session('status') }}
            </div>
        @endif

        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('businesses.show', $business) }}" style="color: var(--gray-400); text-decoration: none; display: flex; align-items: center; transition: color 0.2s ease;" onmouseover="this.style.color='var(--gray-600)'" onmouseout="this.style.color='var(--gray-400)'">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; color: var(--gray-900); margin: 0;">Edit Business</h1>
                <p style="margin-top: 0.5rem; color: var(--gray-600); margin-bottom: 0;">Update {{ $business->name }} information.</p>
            </div>
        </div>
    </div>

    <div style="max-width: 600px; margin: 0 auto;">
        <form method="POST" action="{{ route('businesses.update', $business) }}" class="card">
            @csrf
            @method('PUT')

            <div class="card-header">
                <h3 class="card-title">Business Information</h3>
                <p class="card-subtitle">Update the basic details for your business.</p>
            </div>

            <div class="card-body" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="form-group">
                    <x-input-label for="name" :value="__('Business Name')" />
                    <x-text-input id="name" name="name" type="text" class="form-input" :value="old('name', $business->name)" required autofocus placeholder="Enter business name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="form-group">
                    <x-input-label for="currency" :value="__('Default Currency')" />
                    <select id="currency" name="currency" class="form-select" required>
                        <option value="ETB" {{ old('currency', $business->currency) == 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                        <option value="USD" {{ old('currency', $business->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                    <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem;">This will be used as the default currency for all transactions in this business.</p>
                </div>
            </div>

            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <a href="{{ route('businesses.show', $business) }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Update Business
                </button>
            </div>
        </form>

        @can('delete', $business)
        <div class="card" style="margin-top: 2rem; border-top: 3px solid var(--danger-color, #ef4444);">
            <div class="card-header">
                <h3 class="card-title" style="color: var(--danger-color, #ef4444);">Danger Zone</h3>
                <p class="card-subtitle">Irreversible actions for this business.</p>
            </div>
            <div class="card-body">
                <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem;">
                    Once deleted, all data associated with this business will be permanently removed.
                </p>
                <form method="POST" action="{{ route('businesses.destroy', $business) }}" onsubmit="return confirm('Are you sure you want to delete this business? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        Delete Business
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</x-app-layout>
