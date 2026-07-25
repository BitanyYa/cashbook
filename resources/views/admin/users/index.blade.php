<x-app-layout>
    <div style="background: #f8fafc; min-height: 100vh; padding: 2rem;">
        <div style="max-width: 1100px; margin: 0 auto;">

            {{-- Page Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">User Search & Cashbook Management</h1>
                    <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">
                        Search registered users by email and add them to a cashbook with an assigned role.
                    </p>
                </div>

                @if($books->count() > 0)
                <div style="display: flex; align-items: center; gap: 0.5rem; background: #fff; padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <label for="book-select" style="font-size: 0.8125rem; font-weight: 600; color: #475569;">Target Cashbook:</label>
                    <select id="book-select" onchange="performSearch()" style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.35rem 0.75rem; font-size: 0.875rem; color: #1e293b; outline: none; background: #fff;">
                        @foreach($books as $book)
                            <option value="{{ $book->id }}" {{ $book->id == $selectedBookId ? 'selected' : '' }}>
                                {{ $book->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            {{-- Card Container --}}
            <div style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.5rem; margin-bottom: 2rem;">
                
                {{-- Search Bar --}}
                <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; max-width: 600px;">
                    <div style="position: relative; flex: 1;">
                        <input type="email" id="search-email-input" placeholder="Type email address to search..." 
                               oninput="debounceSearch()"
                               style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; outline: none; color: #1e293b; font-family: inherit; transition: border-color 0.15s;"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                        <svg width="18" height="18" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"
                             style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="button" onclick="performSearch()" 
                            style="padding: 0.65rem 1.25rem; background: #2563eb; color: #fff; font-weight: 600; font-size: 0.875rem; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; transition: background 0.15s;"
                            onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                        Search
                    </button>
                </div>

                {{-- Loading Indicator --}}
                <div id="search-loading" style="display: none; padding: 2rem; text-align: center; color: #64748b; font-size: 0.875rem;">
                    Searching registered users...
                </div>

                {{-- Notification Banner --}}
                <div id="alert-banner" style="display: none; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 500;"></div>

                {{-- Results Table Container --}}
                <div id="results-container">
                    <div id="empty-initial-state" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.875rem;">
                        <svg width="40" height="40" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin: 0 auto 0.75rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        Type an email address above to search registered users.
                    </div>

                    <div id="no-user-found" style="display: none; padding: 3rem 1rem; text-align: center; color: #ef4444; font-size: 0.9375rem; font-weight: 600;">
                        No user found.
                    </div>

                    <table id="users-table" style="display: none; width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #f1f5f9; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                <th style="padding: 0.75rem 1rem;">Name</th>
                                <th style="padding: 0.75rem 1rem;">Email</th>
                                <th style="padding: 0.75rem 1rem;">Registered</th>
                                <th style="padding: 0.75rem 1rem;">Status</th>
                                <th style="padding: 0.75rem 1rem;">Assign Role</th>
                                <th style="padding: 0.75rem 1rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            {{-- Rows populated via JavaScript --}}
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

    <script>
        let searchTimer = null;

        function debounceSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(performSearch, 300);
        }

        function performSearch() {
            const email = document.getElementById('search-email-input').value.trim();
            const bookId = document.getElementById('book-select')?.value || '';
            const loading = document.getElementById('search-loading');
            const emptyInitial = document.getElementById('empty-initial-state');
            const noUserFound = document.getElementById('no-user-found');
            const usersTable = document.getElementById('users-table');
            const tableBody = document.getElementById('users-table-body');

            if (!email) {
                loading.style.display = 'none';
                emptyInitial.style.display = 'block';
                noUserFound.style.display = 'none';
                usersTable.style.display = 'none';
                return;
            }

            emptyInitial.style.display = 'none';
            loading.style.display = 'block';

            fetch(`/admin/users/search?email=${encodeURIComponent(email)}&book_id=${encodeURIComponent(bookId)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                loading.style.display = 'none';

                if (data.success && data.users.length > 0) {
                    noUserFound.style.display = 'none';
                    tableBody.innerHTML = '';

                    data.users.forEach(user => {
                        const tr = document.createElement('tr');
                        tr.style.cssText = 'border-bottom: 1px solid #f1f5f9; transition: background 0.15s;';
                        tr.onmouseover = () => tr.style.background = '#f8fafc';
                        tr.onmouseout = () => tr.style.background = '#ffffff';

                        let actionHtml = '';
                        if (user.is_member) {
                            actionHtml = `
                                <span style="display: inline-block; padding: 0.35rem 0.75rem; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                    Already a member
                                </span>
                            `;
                        } else {
                            actionHtml = `
                                <button type="button" onclick="addUserToCashbook(${user.id}, this)"
                                        style="padding: 0.4rem 0.85rem; background: #16a34a; color: #fff; font-weight: 600; font-size: 0.8125rem; border: none; border-radius: 6px; cursor: pointer; font-family: inherit; transition: background 0.15s;"
                                        onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                                    Add to Cashbook
                                </button>
                            `;
                        }

                        tr.innerHTML = `
                            <td style="padding: 1rem; color: #0f172a; font-weight: 600;">${escapeHtml(user.name)}</td>
                            <td style="padding: 1rem; color: #334155;">${escapeHtml(user.email)}</td>
                            <td style="padding: 1rem; color: #64748b;">${escapeHtml(user.created_at)}</td>
                            <td style="padding: 1rem;">
                                <span style="display: inline-block; padding: 0.2rem 0.5rem; background: #dcfce7; color: #166534; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                    ${escapeHtml(user.status)}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                ${user.is_member ? `
                                    <span style="font-size: 0.8125rem; color: #475569; font-weight: 500;">
                                        Role: ${escapeHtml(formatRole(user.current_role))}
                                    </span>
                                ` : `
                                    <select id="role-select-${user.id}" style="padding: 0.3rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.8125rem; outline: none; background: #fff;">
                                        <option value="operator" selected>Operator</option>
                                        <option value="admin">Admin</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                `}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                ${actionHtml}
                            </td>
                        `;

                        tableBody.appendChild(tr);
                    });

                    usersTable.style.display = 'table';
                } else {
                    usersTable.style.display = 'none';
                    noUserFound.style.display = 'block';
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                showAlert('Error searching users', 'error');
            });
        }

        function addUserToCashbook(userId, btnElement) {
            const bookId = document.getElementById('book-select')?.value;
            const roleSelect = document.getElementById(`role-select-${userId}`);
            const role = roleSelect ? roleSelect.value : 'employee';

            if (!bookId) {
                showAlert('Please select a target Cashbook first.', 'error');
                return;
            }

            const originalText = btnElement.textContent;
            btnElement.disabled = true;
            btnElement.textContent = 'Adding...';

            fetch(`/books/${bookId}/members`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: userId,
                    role: role
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    performSearch(); // Refresh search results table
                } else {
                    showAlert(data.message || 'Error adding member to cashbook.', 'error');
                    btnElement.disabled = false;
                    btnElement.textContent = originalText;
                }
            })
            .catch(err => {
                showAlert('Error adding member to cashbook.', 'error');
                btnElement.disabled = false;
                btnElement.textContent = originalText;
            });
        }

        function showAlert(msg, type) {
            const alert = document.getElementById('alert-banner');
            alert.textContent = msg;
            alert.style.display = 'block';

            if (type === 'success') {
                alert.style.background = '#dcfce7';
                alert.style.color = '#166534';
                alert.style.border = '1px solid #bbf7d0';
            } else {
                alert.style.background = '#fee2e2';
                alert.style.color = '#991b1b';
                alert.style.border = '1px solid #fca5a5';
            }

            setTimeout(() => {
                alert.style.display = 'none';
            }, 4000);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function formatRole(role) {
            if (!role) return 'Employee';
            return role.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        }
    </script>
</x-app-layout>
