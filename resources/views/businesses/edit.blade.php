<x-app-layout>
    <div style="margin-bottom: 2.5rem;">
        @if (session('status'))
            <div style="margin-bottom: 1.5rem; background: #ecfdf5; border: 1px solid #bbf7d0; color: #15803d; border-radius: 0.5rem; padding: 1rem 1.5rem; font-weight: 500;">
                {{ session('status') }}
            </div>
        @endif

        <div style="display: flex; align-items: center; gap: 1.25rem;">
            <a href="{{ route('businesses.show', $business) }}"
               style="color: #9ca3af; text-decoration: none; display: flex; align-items: center;"
               onmouseover="this.style.color='#4b5563'"
               onmouseout="this.style.color='#9ca3af'">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; color: #111827; margin: 0;">Edit Business</h1>
                <p style="margin-top: 0.5rem; color: #4b5563; margin-bottom: 0;">Update {{ $business->name }} information.</p>
            </div>
        </div>
    </div>

    <div style="max-width: 40rem; margin: 0 auto;">
        <form method="POST" action="{{ route('businesses.update', $business) }}"
              style="background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 0.75rem; overflow: hidden;">
            @csrf
            @method('PUT')

            <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
                <h3 style="font-size: 1.125rem; font-weight: 500; color: #111827; margin: 0;">Business Information</h3>
                <p style="margin-top: 0.25rem; color: #4b5563; font-size: 0.95rem;">Update the details for your business.</p>
            </div>

            <div style="padding: 2rem; display: flex; flex-direction: column; gap: 2rem;">
                <div>
                    <label for="name" style="display: block; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Business Name</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $business->name) }}"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem; color: #111827;"
                           required autofocus>
                    @error('name')
                        <p style="color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="currency" style="display: block; font-weight: 500; color: #374151; margin-bottom: 0.25rem;">Default Currency</label>
                    <select id="currency" name="currency"
                            style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem; color: #111827;">
                        <option value="ETB" {{ old('currency', $business->currency) == 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                        <option value="USD" {{ old('currency', $business->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    </select>
                    @error('currency')
                        <p style="color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="padding: 1.5rem 2rem; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 1rem;">
                <a href="{{ route('businesses.show', $business) }}"
                   style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: #fff; border: 1px solid #d1d5db; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #374151; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 1px 2px rgba(0,0,0,0.01);"
                   onmouseover="this.style.background='#f3f4f6'"
                   onmouseout="this.style.background='#fff'">
                    Cancel
                </a>
                <button type="submit"
                        style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: #4f46e5; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer;"
                        onmouseover="this.style.background='#4338ca'"
                        onmouseout="this.style.background='#4f46e5'">
                    Update Business
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
