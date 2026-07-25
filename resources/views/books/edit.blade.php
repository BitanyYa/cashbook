<x-app-layout>
<div style="padding: 1.5rem 2rem; background: #f8fafc; min-height: 100vh;">

    {{-- ── Breadcrumb & Page Header ── --}}
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('books.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8125rem; font-weight: 600; color: #3b82f6; text-decoration: none; margin-bottom: 0.5rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Books
        </a>
        <h1 style="font-size: 1.375rem; font-weight: 700; color: #1e293b; margin: 0;">Edit Book</h1>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">
            Update details and settings for "{{ $book->name }}".
        </p>
    </div>

    {{-- ── Form Card ── --}}
    <div style="max-width: 600px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.75rem;">
        <form method="POST" action="{{ route('books.update', $book) }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            @method('PUT')

            {{-- Book Name --}}
            <div>
                <label for="name" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Book Name <span style="color: #ef4444;">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $book->name) }}"
                    required
                    autofocus
                    placeholder="e.g., General Ledger, Marketing Campaign, Q4 Operations"
                    style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 0.875rem; color: #0f172a; background: #fff; outline: none; font-family: inherit; box-sizing: border-box; transition: border-color 0.2s;"
                />
                <x-input-error :messages="$errors->get('name')" style="margin-top: 4px; font-size: 0.78rem; color: #dc2626;" />
            </div>

            {{-- Description --}}
            <div>
                <label for="description" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Description (Optional)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Describe what this book will be used for..."
                    style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 0.875rem; color: #0f172a; background: #fff; outline: none; font-family: inherit; box-sizing: border-box; resize: vertical; transition: border-color 0.2s;"
                >{{ old('description', $book->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" style="margin-top: 4px; font-size: 0.78rem; color: #dc2626;" />
            </div>

            {{-- Currency --}}
            <div>
                <label for="currency" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Default Currency <span style="color: #ef4444;">*</span>
                </label>
                <select
                    id="currency"
                    name="currency"
                    required
                    style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 0.875rem; color: #0f172a; background: #fff; outline: none; font-family: inherit; box-sizing: border-box; cursor: pointer;"
                >
                    <option value="ETB" {{ old('currency', $book->currency) == 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                    <option value="USD" {{ old('currency', $book->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                </select>
                <x-input-error :messages="$errors->get('currency')" style="margin-top: 4px; font-size: 0.78rem; color: #dc2626;" />
            </div>

            {{-- Footer Actions --}}
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; margin-top: 0.5rem;">
                <a href="{{ route('books.index') }}"
                   style="padding: 8px 16px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.8125rem; font-weight: 600; color: #475569; text-decoration: none; font-family: inherit; transition: background 0.2s;"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    Cancel
                </a>
                <button type="submit"
                        style="padding: 8px 18px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-size: 0.85rem; font-weight: 700; cursor: pointer; font-family: inherit; box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2); transition: background 0.2s;"
                        onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                    Update Book
                </button>
            </div>
        </form>

        {{-- Danger Zone: Delete Book --}}
        @can('delete', $book)
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #fee2e2;">
            <h2 style="font-size: 0.9375rem; font-weight: 700; color: #dc2626; margin-bottom: 4px;">Delete Book</h2>
            <p style="color: #64748b; font-size: 0.8125rem; margin-bottom: 1rem;">
                Once deleted, all data associated with this book will be permanently removed.
            </p>
            <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        style="padding: 8px 16px; background: #dc2626; color: #fff; border: none; border-radius: 6px; font-size: 0.8125rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: background 0.2s;"
                        onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                    Delete Book
                </button>
            </form>
        </div>
        @endcan
    </div>

</div>
</x-app-layout>
