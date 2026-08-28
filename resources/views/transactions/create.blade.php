<x-app-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Add New Transaction</h1>
        <p class="mt-2 text-gray-600">Record a new income or expense transaction.</p>
    </div>

    <div class="max-w-4xl">
        <div class="bg-white shadow rounded-lg">
            <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @if(request('return_to'))
                    <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="book_id" value="Book" />
                        <select id="book_id" name="book_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select a book</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" @selected(request('book') == $book->id)>{{ $book->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('book_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" value="Transaction Type" />
                        <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select type</option>
                            <option value="income" @selected(old('type') === 'income')>Income</option>
                            <option value="expense" @selected(old('type') === 'expense')>Expense</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="amount" value="Amount" />
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">{{ $activeBusiness->currency }}</span>
                            </div>
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="pl-12 block w-full" value="{{ old('amount') }}" required placeholder="0.00" />
                        </div>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="transaction_date" value="Transaction Date" />
                        <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full" value="{{ old('transaction_date', request('transaction_date') ? \Carbon\Carbon::parse(request('transaction_date'))->toDateString() : now()->toDateString()) }}" required />
                        <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="mode" value="Payment Mode" />
                        <select id="mode" name="mode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Cash" @selected(old('mode', 'Cash') === 'Cash')>Cash</option>
                            <option value="Bank" @selected(old('mode') === 'Bank')>Bank</option>
                            <option value="Online" @selected(old('mode') === 'Online')>Online</option>
                        </select>
                        <x-input-error :messages="$errors->get('mode')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category_id" value="Category (Optional)" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }} ({{ ucfirst($category->type) }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter a description for this transaction...">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="receipt" value="Receipt/Attachment (Optional)" />
                    <input id="receipt" name="receipt" type="file" accept="image/*,application/pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <p class="mt-2 text-sm text-gray-500">Upload an image (JPG, PNG) or PDF file. Maximum size: 4MB.</p>
                    <x-input-error :messages="$errors->get('receipt')" class="mt-2" />
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <x-primary-button>
                        Save Transaction
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
