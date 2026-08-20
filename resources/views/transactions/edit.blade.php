<x-app-layout>
    <div class="mb-8" style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--gray-900);">Edit Transaction</h1>
                <p style="color: var(--gray-600); font-size: 0.875rem; margin-top: 0.25rem;">Update details for entry in {{ $transaction->book->name }}</p>
            </div>
            <a href="{{ request('return_to', route('books.show', $transaction->book)) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Book
            </a>
        </div>
    </div>

    <div style="max-width: 800px; margin: 0 auto;">
        <div class="card" style="background: #fff; border-radius: 12px; border: 1px solid var(--gray-200); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div class="card-body" style="padding: 1.75rem;">
                <form method="POST" action="{{ route('transactions.update', $transaction) }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="return_to" value="{{ request('return_to', route('books.show', $transaction->book)) }}">
                    <input type="hidden" name="book_id" value="{{ $transaction->book_id }}">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="book_name" class="form-label">Book</label>
                            <input type="text" id="book_name" class="form-input" value="{{ $transaction->book->name }}" readonly disabled style="background: var(--gray-100);" />
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="type" class="form-label">Type <span style="color: var(--danger-color);">*</span></label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="income" {{ old('type', $transaction->type) === 'income' ? 'selected' : '' }}>Income (Cash In)</option>
                                <option value="expense" {{ old('type', $transaction->type) === 'expense' ? 'selected' : '' }}>Expense (Cash Out)</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="amount" class="form-label">Amount <span style="color: var(--danger-color);">*</span></label>
                            <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="form-input" value="{{ old('amount', $transaction->amount) }}" required placeholder="0.00" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="transaction_date" class="form-label">Date & Time <span style="color: var(--danger-color);">*</span></label>
                            <input id="transaction_date" name="transaction_date" type="datetime-local" class="form-input" value="{{ old('transaction_date', $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required />
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="contact_name" class="form-label">Contact Name</label>
                            <input id="contact_name" name="contact_name" type="text" class="form-input" value="{{ old('contact_name', $transaction->contact_name) }}" placeholder="e.g. Supplier or Customer name" />
                            <x-input-error :messages="$errors->get('contact_name')" class="mt-2" />
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <label for="mode" class="form-label">Payment Mode</label>
                            <input id="mode" name="mode" type="text" class="form-input" value="{{ old('mode', $transaction->mode) }}" placeholder="e.g. Cash, Bank, Telebirr…" />
                            <x-input-error :messages="$errors->get('mode')" class="mt-2" />
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label for="new_category" class="form-label">Or Create New Category</label>
                            <input id="new_category" name="new_category" type="text" class="form-input" value="{{ old('new_category') }}" placeholder="Enter new category name…" />
                        </div>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label for="description" class="form-label">Description / Remarks</label>
                        <textarea id="description" name="description" rows="3" class="form-input" placeholder="Optional transaction notes…">{{ old('description', $transaction->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Receipt / Attachments</label>
                        <input id="receipts" name="receipts[]" type="file" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx" multiple class="form-input" />
                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem;">Upload files or photos (JPG, PNG, PDF). Max 100MB per file.</p>
                    </div>

                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; padding-top: 1.25rem; border-top: 1px solid var(--gray-200); margin-top: 0.5rem;">
                        <a href="{{ request('return_to', route('books.show', $transaction->book)) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" name="action" value="save_and_add" class="btn btn-secondary" style="background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; font-weight: 600;">
                            Save &amp; Add New
                        </button>
                        <button type="submit" name="action" value="update" class="btn btn-primary">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
