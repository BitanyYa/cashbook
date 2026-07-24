<x-app-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New Book</h1>
        <p class="mt-2 text-gray-600">
            Books help you organize transactions by project, department, or any category you choose.
        </p>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg">
            <form method="POST" action="{{ route('books.store') }}" class="p-6 space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" value="Book Name" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--gray-300, #d1d5db); border-radius: 0.5rem; font-size: 0.95rem; color: var(--gray-900); background: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                        value="{{ old('name', request()->input('name')) }}"
                        required
                        autofocus
                        placeholder="e.g., General Ledger, Marketing Campaign, Q4 Operations"
                    />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="description" value="Description (Optional)" />
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-textarea w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Describe what this book will be used for..."
                    >{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    <p class="mt-2 text-sm text-gray-500">
                        Provide a brief description to help you and your team understand the purpose of this book.
                    </p>
                </div>

                <div class="mt-4">
                    <x-input-label for="currency" :value="__('Default Currency')" />
                    <select id="currency" name="currency" class="form-select" required>
                        <option value="ETB" {{ old('currency', 'ETB') == 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                    <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem;">This will be used as the default currency for all transactions in this book.</p>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a
                        href="{{ route('books.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        style="color: #4a5568; background-color: #fff; border-color: #cbd5e0; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; padding: 0.5rem 1rem; transition: background-color 0.2s, color 0.2s;"
                    >
                        Cancel
                    </a>
                    <x-primary-button style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background: #082e7fff; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.01); transition: background 0.2s, color 0.2s; cursor: pointer;">
                        Create Book
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
