<x-app-layout>
    <div style="max-width: 800px; margin: 2rem auto; padding: 0 1rem;">
        
        {{-- Header Navigation --}}
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Bulk Import Transactions</h1>
                <p style="font-size: 0.875rem; color: #64748b; margin: 4px 0 0;">Book: <strong>{{ $book->name }}</strong></p>
            </div>
            <a href="{{ route('books.show', $book) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                &larr; Back to Book
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('error'))
            <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Upload Card --}}
        <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 2rem;">
            <form action="{{ route('transactions.import.preview', $book) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- Drop Area --}}
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="csv_file" class="form-label" style="font-weight: 600; color: #334155; margin-bottom: 0.5rem; display: block;">Select CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv, .txt" class="form-input" required style="padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 8px; width: 100%;">
                    @error('csv_file')
                        <div class="form-error" style="color: #ef4444; font-size: 0.8125rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Expected CSV Format Info Box --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.9375rem; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem;">Accepted CSV Format & Columns:</h4>
                    <p style="font-size: 0.84rem; color: #475569; margin: 0 0 0.75rem; line-height: 1.5;">
                        Your CSV file should contain the following column headers (case-insensitive):
                    </p>
                    
                    <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 0.75rem; font-family: monospace; font-size: 0.8125rem; color: #2563eb; word-break: break-all;">
                        Date, Time, Remark, Party, Category, Mode, Entry By, Cash In, Cash Out, Balance
                    </div>

                    <ul style="font-size: 0.8125rem; color: #64748b; margin: 0.75rem 0 0 1.25rem; padding: 0; line-height: 1.5;">
                        <li><strong>Cash In / Cash Out:</strong> If <em>Cash In</em> has a value, it creates an <strong>Income</strong> transaction. If <em>Cash Out</em> has a value, it creates an <strong>Expense</strong> transaction.</li>
                        <li><strong>Balance:</strong> Automatically ignored. System recalculates balances dynamically.</li>
                        <li><strong>Category:</strong> Automatically matched by name or created if missing.</li>
                        <li><strong>Entry By:</strong> Matched to an existing user; defaults to your Admin user if unmatched.</li>
                        <li><strong>Duplicate Protection:</strong> Rows matching an existing date, time, remark, and amount are detected and skipped automatically.</li>
                    </ul>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <a href="{{ route('books.show', $book) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                        Preview Import &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
