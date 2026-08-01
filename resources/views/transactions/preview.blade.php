<x-app-layout>
    <div style="width: 100%; max-width: 100%; box-sizing: border-box;">
        
        {{-- Header Navigation --}}
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">CSV Import Preview</h1>
                <p style="font-size: 0.875rem; color: #64748b; margin: 4px 0 0;">Book: <strong>{{ $book->name }}</strong> &bull; Click any row to view full details</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('transactions.import.create', $book) }}" class="btn btn-secondary" style="text-decoration: none;">
                    &larr; Upload Different CSV
                </a>
            </div>
        </div>

        {{-- Summary Metrics Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
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

        {{-- Table Card with Contained Horizontal & Vertical Scrollbar --}}
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; width: 100%;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">Parsed Transactions Preview</h3>
                <span style="font-size: 0.8125rem; color: #64748b;">Showing {{ count($rows) }} parsed rows (Click any row to open)</span>
            </div>

            <div style="max-height: 520px; overflow-x: auto; overflow-y: auto; width: 100%;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; min-width: 900px;">
                    <thead style="position: sticky; top: 0; z-index: 10; background: #f8fafc; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
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
                            <tr onclick="showRowDetails({{ json_encode($row) }})"
                                style="cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; {{ $row['status'] === 'duplicate' ? 'background: #fffbeb;' : ($row['status'] === 'invalid' ? 'background: #fef2f2;' : '') }}"
                                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
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

    {{-- Interactive Row Detail Modal --}}
    <div id="rowDetailModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; border-radius: 16px; max-width: 540px; width: 100%; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); overflow: hidden;">
            
            {{-- Modal Header --}}
            <div style="padding: 1.25rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 id="modalRowTitle" style="font-size: 1.125rem; font-weight: 700; color: #0f172a; margin: 0;">Transaction Row Details</h3>
                    <span id="modalRowBadge" style="display: inline-block; margin-top: 4px;"></span>
                </div>
                <button type="button" onclick="closeRowModal()" style="background: transparent; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            {{-- Modal Content Grid --}}
            <div style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                
                {{-- Reason / Error Box if invalid or duplicate --}}
                <div id="modalAlertBox" style="display: none; padding: 0.875rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.875rem;">
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Transaction Type</label>
                        <div id="modalRowType" style="font-weight: 700; margin-top: 2px;"></div>
                    </div>

                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Amount</label>
                        <div id="modalRowAmount" style="font-weight: 800; font-size: 1.125rem; margin-top: 2px;"></div>
                    </div>

                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Date & Time</label>
                        <div id="modalRowDate" style="color: #1e293b; font-weight: 600; margin-top: 2px;"></div>
                    </div>

                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Payment Mode</label>
                        <div id="modalRowMode" style="color: #1e293b; font-weight: 600; margin-top: 2px; text-transform: capitalize;"></div>
                    </div>

                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Category</label>
                        <div id="modalRowCategory" style="color: #1e293b; font-weight: 600; margin-top: 2px;"></div>
                    </div>

                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Contact (Party)</label>
                        <div id="modalRowParty" style="color: #1e293b; font-weight: 600; margin-top: 2px;"></div>
                    </div>
                </div>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Remark / Description</label>
                    <div id="modalRowRemark" style="color: #334155; font-size: 0.875rem; margin-top: 4px; background: #f8fafc; padding: 0.75rem; border-radius: 6px; border: 1px solid #e2e8f0; white-space: pre-wrap;"></div>
                </div>

                <div style="margin-top: 1rem; display: flex; justify-content: space-between; font-size: 0.8125rem; color: #64748b;">
                    <span>Entry By: <strong id="modalRowUser" style="color: #0f172a;"></strong></span>
                    <span>Raw Date: <span id="modalRawDate"></span></span>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
                <button type="button" onclick="closeRowModal()" class="btn btn-secondary">Close</button>
            </div>

        </div>
    </div>

    <script>
        function showRowDetails(row) {
            document.getElementById('modalRowTitle').innerText = 'Row #' + row.row_index + ' Details';
            
            // Badge
            const badgeEl = document.getElementById('modalRowBadge');
            const alertBox = document.getElementById('modalAlertBox');

            if (row.status === 'ready') {
                badgeEl.innerHTML = '<span style="background: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 9999px;">Ready to Import</span>';
                alertBox.style.display = 'none';
            } else if (row.status === 'duplicate') {
                badgeEl.innerHTML = '<span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 9999px;">Duplicate (Skipped)</span>';
                alertBox.style.display = 'block';
                alertBox.style.background = '#fffbeb';
                alertBox.style.border = '1px solid #fde68a';
                alertBox.style.color = '#92400e';
                alertBox.innerHTML = '⚠️ Duplicate Transaction: A transaction with identical date, time, remark, and amount already exists in this book. This row will be skipped.';
            } else {
                badgeEl.innerHTML = '<span style="background: #fee2e2; color: #991b1b; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 9999px;">Invalid Row</span>';
                alertBox.style.display = 'block';
                alertBox.style.background = '#fef2f2';
                alertBox.style.border = '1px solid #fca5a5';
                alertBox.style.color = '#991b1b';
                alertBox.innerHTML = '❌ ' + (row.error_message || 'Invalid transaction row.');
            }

            // Type
            const typeEl = document.getElementById('modalRowType');
            if (row.type === 'income') {
                typeEl.innerHTML = '<span style="color: #059669; text-transform: uppercase;">Income</span>';
            } else if (row.type === 'expense') {
                typeEl.innerHTML = '<span style="color: #dc2626; text-transform: uppercase;">Expense</span>';
            } else {
                typeEl.innerText = '-';
            }

            // Amount
            const amtEl = document.getElementById('modalRowAmount');
            amtEl.innerText = parseFloat(row.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            amtEl.style.color = row.type === 'income' ? '#059669' : (row.type === 'expense' ? '#dc2626' : '#334155');

            // Date, Mode, Category, Party, Remark, User
            document.getElementById('modalRowDate').innerText = row.transaction_date ? row.transaction_date : (row.raw_date + ' ' + row.raw_time);
            document.getElementById('modalRowMode').innerText = row.mode || 'cash';
            
            const catEl = document.getElementById('modalRowCategory');
            if (row.category) {
                catEl.innerHTML = row.category + (row.is_new_category ? ' <span style="color:#2563eb; font-weight:700;">[New Category]</span>' : '');
            } else {
                catEl.innerText = 'Uncategorized';
            }

            document.getElementById('modalRowParty').innerText = row.party || '-';
            document.getElementById('modalRowRemark').innerText = row.remark || '(No remark provided)';
            document.getElementById('modalRowUser').innerText = row.user_name || 'Admin';
            document.getElementById('modalRawDate').innerText = row.raw_date + ' ' + row.raw_time;

            document.getElementById('rowDetailModal').style.display = 'flex';
        }

        function closeRowModal() {
            document.getElementById('rowDetailModal').style.display = 'none';
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeRowModal();
        });
    </script>
</x-app-layout>
