<x-app-layout>
    <div style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
        
        {{-- Header Navigation --}}
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">CSV Import Preview</h1>
                <p style="font-size: 0.875rem; color: #64748b; margin: 4px 0 0;">Book: <strong>{{ $book->name }}</strong></p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('transactions.import.create', $book) }}" class="btn btn-secondary" style="text-decoration: none;">
                    &larr; Upload Different CSV
                </a>
            </div>
        </div>

        {{-- Summary Metrics Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            {{-- Total Rows --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem;">
                <div style="font-size: 0.8125rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Total Rows</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $summary['total'] }}</div>
            </div>

            {{-- Ready to Import --}}
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 1.25rem;">
                <div style="font-size: 0.8125rem; font-weight: 600; color: #047857; text-transform: uppercase; letter-spacing: 0.05em;">Ready to Import</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #065f46; margin-top: 4px;">{{ $summary['ready'] }}</div>
            </div>

            {{-- Duplicates (Will Skip) --}}
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 1.25rem;">
                <div style="font-size: 0.8125rem; font-weight: 600; color: #b45309; text-transform: uppercase; letter-spacing: 0.05em;">Duplicates (Skipped)</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #92400e; margin-top: 4px;">{{ $summary['duplicates'] }}</div>
            </div>

            {{-- New Categories --}}
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 1.25rem;">
                <div style="font-size: 0.8125rem; font-weight: 600; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.05em;">New Categories</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #1e40af; margin-top: 4px;">{{ $summary['new_categories'] }}</div>
            </div>

            {{-- Invalid / Errors --}}
            @if($summary['invalid'] > 0)
                <div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 10px; padding: 1.25rem;">
                    <div style="font-size: 0.8125rem; font-weight: 600; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em;">Invalid Rows</div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: #991b1b; margin-top: 4px;">{{ $summary['invalid'] }}</div>
                </div>
            @endif
        </div>

        {{-- New Categories Banner --}}
        @if(!empty($summary['new_category_names']))
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <span style="font-size: 0.875rem; font-weight: 700; color: #1e40af;">New Categories to be created automatically:</span>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @foreach($summary['new_category_names'] as $catName)
                        <span style="background: #dbeafe; color: #1e40af; font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 9999px; border: 1px solid #93c5fd;">
                            + {{ $catName }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Table Card --}}
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">Parsed Transactions Preview</h3>
                <span style="font-size: 0.8125rem; color: #64748b;">Showing {{ count($rows) }} parsed rows</span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 0.75rem; text-transform: uppercase;">
                            <th style="padding: 0.75rem 1rem;">Row</th>
                            <th style="padding: 0.75rem 1rem;">Status</th>
                            <th style="padding: 0.75rem 1rem;">Date & Time</th>
                            <th style="padding: 0.75rem 1rem;">Type</th>
                            <th style="padding: 0.75rem 1rem; text-align: right;">Amount</th>
                            <th style="padding: 0.75rem 1rem;">Category</th>
                            <th style="padding: 0.75rem 1rem;">Party</th>
                            <th style="padding: 0.75rem 1rem;">Remark</th>
                            <th style="padding: 0.75rem 1rem;">Entry By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr style="border-bottom: 1px solid #f1f5f9; {{ $row['status'] === 'duplicate' ? 'background: #fffbeb;' : ($row['status'] === 'invalid' ? 'background: #fef2f2;' : '') }}">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #64748b;">#{{ $row['row_index'] }}</td>
                                <td style="padding: 0.75rem 1rem;">
                                    @if($row['status'] === 'ready')
                                        <span style="background: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 9999px;">
                                            Ready
                                        </span>
                                    @elseif($row['status'] === 'duplicate')
                                        <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 9999px;" title="Duplicate transaction detected (will skip)">
                                            Duplicate (Skipped)
                                        </span>
                                    @else
                                        <span style="background: #fee2e2; color: #991b1b; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 9999px;" title="{{ $row['error_message'] }}">
                                            Invalid
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #1e293b; font-weight: 500; white-space: nowrap;">
                                    {{ $row['transaction_date'] ? \Carbon\Carbon::parse($row['transaction_date'])->format('M d, Y h:i A') : ($row['raw_date'] . ' ' . $row['raw_time']) }}
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    @if($row['type'] === 'income')
                                        <span style="color: #059669; font-weight: 700; text-transform: uppercase; font-size: 0.75rem;">Income</span>
                                    @elseif($row['type'] === 'expense')
                                        <span style="color: #dc2626; font-weight: 700; text-transform: uppercase; font-size: 0.75rem;">Expense</span>
                                    @else
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: {{ $row['type'] === 'income' ? '#059669' : ($row['type'] === 'expense' ? '#dc2626' : '#334155') }};">
                                    {{ number_format($row['amount'], 2) }}
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #334155;">
                                    @if($row['category'])
                                        {{ $row['category'] }}
                                        @if($row['is_new_category'])
                                            <span style="font-size: 0.6875rem; color: #2563eb; font-weight: 700; margin-left: 4px;">[New]</span>
                                        @endif
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Uncategorized</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #334155;">{{ $row['party'] ?: '-' }}</td>
                                <td style="padding: 0.75rem 1rem; color: #475569; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $row['remark'] ?: '-' }}
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #334155; font-size: 0.8125rem;">
                                    {{ $row['user_name'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Action Footer --}}
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="font-size: 0.875rem; color: #475569;">
                Ready to import <strong>{{ $summary['ready'] }}</strong> transactions. 
                @if($summary['duplicates'] > 0)
                    <strong>{{ $summary['duplicates'] }}</strong> duplicates will be skipped.
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('books.show', $book) }}" class="btn btn-secondary">Cancel</a>
                
                @if($summary['ready'] > 0)
                    <form action="{{ route('transactions.import.store', $book) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700;">
                            Confirm & Execute Import ({{ $summary['ready'] }}) &rarr;
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;">
                        No Valid Rows to Import
                    </button>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
