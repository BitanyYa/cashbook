<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #1a202c;">Edit Book</h1>
        <p style="margin-top: 0.5rem; color: #4a5568;">Update the details for "{{ $book->name }}".</p>
    </div>

    <div >
        <div style="background-color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06); border-radius: 0.5rem;">
            <!-- Main Update Form -->
            <form method="POST" action="{{ route('books.update', $book) }}" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" value="Book Name" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.95rem; color: #1a202c; background: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                        value="{{ old('name', $book->name) }}"
                        required
                        autofocus
                        placeholder="e.g., General Ledger, Marketing Campaign, Q4 Operations"
                    />
                    <x-input-error :messages="$errors->get('name')" style="margin-top: 0.5rem; display: block;" />
                </div>

                <div>
                    <x-input-label for="description" value="Description (Optional)" />
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        style="width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); padding: 0.5rem; font-size: 1rem; line-height: 1.5; color: #4a5568; resize: vertical; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                        placeholder="Describe what this book will be used for..."
                    >{{ old('description', $book->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" style="margin-top: 0.5rem; display: block;" />
                </div>

                <div>
                    <x-input-label for="currency" :value="__('Default Currency')" />
                    <select
                        id="currency"
                        name="currency"
                        required
                        style="width: 100%; margin-top: 0.25rem; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background-color: white; font-size: 1rem; color: #1a202c; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                    >
                        <option value="ETB" {{ old('currency', $book->currency) == 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                        <option value="USD" {{ old('currency', $book->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    </select>
                    <x-input-error :messages="$errors->get('currency')" style="margin-top: 0.5rem; display: block;" />
                    <p style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">This will be used as the default currency for all transactions in this book.</p>
                </div>

                <!-- Form Actions (Update and Cancel) -->
                <div style="display: flex; justify-content: flex-end; align-items: center; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="{{ route('books.index') }}"
                           style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: #fff; border: 1px solid #cbd5e0; border-radius: 0.375rem; font-weight: 600; font-size: 0.875rem; color: #4a5568; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 1px 2px rgba(0,0,0,0.05); text-decoration: none; transition: background-color 0.2s, color 0.2s; cursor: pointer;"
                           onmouseover="this.style.backgroundColor='#f9fafb';"
                           onmouseout="this.style.backgroundColor='#fff';"
                        >
                            Cancel
                        </a>
                        <x-primary-button style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background-color: #082e7f; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 1px 2px rgba(0,0,0,0.1); transition: background-color 0.2s, color 0.2s; cursor: pointer;">
                            Update Book
                        </x-primary-button>
                    </div>
                </div>
            </form>

            <!-- Delete Form (Separate) -->
            <div style="padding: 1.5rem 1.5rem 1.5rem 1.5rem; border-top: 1px solid #e5e7eb;">
                <h2 style="font-size: 1.25rem; font-weight: 600; color: #e53e3e;">Delete Book</h2>
                <p style="margin-top: 0.5rem; color: #4a5568; font-size: 0.875rem; margin-bottom: 1rem;">Once deleted, all data associated with this book will be permanently removed. This action cannot be undone. Please proceed with caution. </p>
                <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: #e53e3e; border: none; border-radius: 0.375rem; font-weight: 600; font-size: 0.875rem; color: #fff; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 1px 2px rgba(0,0,0,0.1); cursor: pointer; transition: background-color 0.2s;"
                        onmouseover="this.style.backgroundColor='#c53030';"
                        onmouseout="this.style.backgroundColor='#e53e3e';"
                    >
                        Delete Book
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
