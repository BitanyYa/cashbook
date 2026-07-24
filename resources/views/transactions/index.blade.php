<x-app-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">All Transactions</h1>
        <p class="mt-2 text-gray-600">View and manage all transactions across your books.</p>
    </div>

    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-500">{{ $transactions->total() }} total transactions</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Transaction
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="font-semibold text-gray-900">
                                    {{ $transaction->transaction_date->isToday() ? 'Today' : $transaction->transaction_date->format('M j, Y') }}
                                </div>
                                <div class="text-gray-400 text-xs mt-0.5">
                                    {{ $transaction->transaction_date->format('h:i A') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="{{ route('books.show', $transaction->book) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $transaction->book->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{-- Contact name (posted for) with type label --}}
                                @if($transaction->contact_name)
                                    <div class="font-semibold text-gray-900">
                                        <span class="font-bold">({{ $transaction->contact_name }})</span>
                                        <span class="text-gray-500 font-normal">({{ ucfirst($transaction->type) }})</span>
                                    </div>
                                @endif
                                {{-- Description / Remarks --}}
                                <div class="{{ $transaction->contact_name ? 'text-gray-700 mt-0.5' : 'font-semibold text-gray-900' }}">
                                    {{ $transaction->description ?: '—' }}
                                </div>
                                {{-- Posted by --}}
                                @if($transaction->user)
                                    <div class="text-gray-400 text-xs mt-0.5">
                                        by {{ $transaction->user->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->category?->name ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $transaction->mode ? ucfirst($transaction->mode) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $activeBusiness->currency }} {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $transaction->status === 'approved' ? 'bg-green-100 text-green-800' : ($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    @if($transaction->image_path)
                                        <a href="{{ route('transactions.receipt', $transaction) }}" class="text-indigo-600 hover:text-indigo-900">Receipt</a>
                                    @endif
                                    @can('approve', $transaction)
                                        @if($transaction->status === 'pending')
                                            <form method="POST" action="{{ route('transactions.approve', $transaction) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('transactions.reject', $transaction) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by adding your first transaction.</p>
                                <div class="mt-6">
                                    <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Transaction
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
