<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem;">{{ $book->name }} - User Management</h1>
                <p style="color: var(--gray-600); margin-top: 0.5rem;">Manage users and their roles for this book</p>
            </div>
            <a href="{{ route('books.show', $book) }}" class="btn btn-secondary">
                <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Book
            </a>
        </div>
    </div>

    <!-- Role Information Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Role Permissions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div style="padding: 1rem; background: var(--gray-50); border-radius: var(--border-radius);">
                    <h4 style="font-weight: 600; color: var(--success-color); margin-bottom: 0.5rem;">Primary Admin</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.875rem; color: var(--gray-600);">
                        <li>• Add, edit, delete transactions</li>
                        <li>• Approve/reject transactions</li>
                        <li>• View all transactions</li>
                        <li>• Export reports</li>
                    </ul>
                </div>
                <div style="padding: 1rem; background: var(--gray-50); border-radius: var(--border-radius);">
                    <h4 style="font-weight: 600; color: var(--primary-color); margin-bottom: 0.5rem;">Admin</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.875rem; color: var(--gray-600);">
                        <li>• Add, edit own transactions</li>
                        <li>• View all transactions</li>
                        <li>• Export reports</li>
                        <li>• Cannot approve/reject</li>
                    </ul>
                </div>
                <div style="padding: 1rem; background: var(--gray-50); border-radius: var(--border-radius);">
                    <h4 style="font-weight: 600; color: var(--gray-500); margin-bottom: 0.5rem;">Employee</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.875rem; color: var(--gray-600);">
                        <li>• View all transactions</li>
                        <li>• Export reports</li>
                        <li>• Cannot add/edit</li>
                        <li>• Cannot approve/reject</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Section -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Add User to Book</h3>

            <form id="add-user-form" action="{{ route('books.users.invite', $book) }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf

                <!-- Searchable User Input -->
                <div class="form-group">
                    <label for="user_search" class="form-label">Search User</label>
                    <div style="position: relative;">
                        <input type="text" id="user_search" placeholder="Type name or email to search..." class="form-input" autocomplete="off" />
                        <input type="hidden" id="selected_user_id" name="user_id" value="" />
                        <div id="user_search_results" style="
                            position: absolute;
                            top: 100%;
                            left: 0;
                            right: 0;
                            background: white;
                            border: 1px solid var(--gray-300);
                            border-top: none;
                            border-radius: 0 0 var(--border-radius) var(--border-radius);
                            max-height: 200px;
                            overflow-y: auto;
                            z-index: 1000;
                            display: none;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        "></div>
                    </div>
                    <div id="selected_user_display" style="
                        margin-top: 0.5rem;
                        padding: 0.75rem;
                        background: var(--success-color);
                        color: white;
                        border-radius: var(--border-radius);
                        font-size: 0.875rem;
                        display: none;
                        position: relative;
                    ">
                        <span id="selected_user_text"></span>
                        <button type="button" onclick="clearSelectedUser()" style="
                            position: absolute;
                            top: 0.5rem;
                            right: 0.75rem;
                            background: none;
                            border: none;
                            color: white;
                            cursor: pointer;
                            font-size: 1.25rem;
                            line-height: 1;
                        ">×</button>
                    </div>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="employee">Employee - Can add/edit own transactions</option>
                        <option value="admin">Admin - Can manage books, members, and transactions</option>
                        <option value="primary_admin">Primary Admin - Full access to book and business management</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <button type="submit" class="btn btn-primary" disabled id="add-user-btn">Add User</button>
            </form>
        </div>
    </div>

    <!-- Current Users List -->
    <div class="card">
        <div class="card-body">
            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-900); margin-bottom: 1rem;">Current Users ({{ $bookUsers->count() }})</h3>

            @if($bookUsers->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($bookUsers as $user)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: var(--gray-50); border-radius: var(--border-radius);">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-900); margin-bottom: 0.25rem;">{{ $user->name }}</div>
                                <div style="font-size: 0.875rem; color: var(--gray-500); margin-bottom: 0.25rem;">{{ $user->email }}</div>
                                <div style="font-size: 0.75rem; color: var(--gray-400);">
                                    Added on {{ $user->pivot->created_at->format('M j, Y') }}
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <!-- Role Badge -->
                                <span class="badge {{
                                    $user->pivot->role === 'primary_admin' ? 'badge-success' :
                                    ($user->pivot->role === 'admin' ? 'badge-primary' : 'badge-secondary')
                                }}">
                                    {{ ucfirst($user->pivot->role) }}
                                </span>

                                <!-- Role Update Form -->
                                <form action="{{ route('books.users.role', [$book, $user]) }}" method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()" class="form-select" style="font-size: 0.875rem; padding: 0.25rem 0.5rem; width: auto;">
                                        <option value="employee" {{ $user->pivot->role === 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="admin" {{ $user->pivot->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="primary_admin" {{ $user->pivot->role === 'primary_admin' ? 'selected' : '' }}>Primary Admin</option>
                                    </select>
                                </form>

                                <!-- Remove User Form -->
                                <form action="{{ route('books.users.remove', [$book, $user]) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to remove this user from the book?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--danger-color); cursor: pointer; padding: 0.25rem;" title="Remove user">
                                        <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 2rem; background: var(--gray-50); border-radius: var(--border-radius);">
                    <svg style="width: 3rem; height: 3rem; color: var(--gray-400); margin: 0 auto 1rem;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p style="color: var(--gray-500); margin-bottom: 0.5rem;">No users assigned to this book yet.</p>
                    <p style="font-size: 0.875rem; color: var(--gray-400);">Add business members to this book to get started.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.addEventListener('DOMContentLoaded', function() {
            initializeUserSearch();
        });

        function initializeUserSearch() {
            const searchInput = document.getElementById('user_search');
            const resultsDiv = document.getElementById('user_search_results');
            const selectedUserIdInput = document.getElementById('selected_user_id');
            const selectedUserDisplay = document.getElementById('selected_user_display');
            const addBtn = document.getElementById('add-user-btn');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    resultsDiv.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    searchUsers(query);
                }, 300);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                    resultsDiv.style.display = 'none';
                }
            });

            // Form submission
            document.getElementById('add-user-form').addEventListener('submit', function(e) {
                if (!selectedUserIdInput.value) {
                    e.preventDefault();
                    alert('Please select a user first');
                }
            });
        }

        function searchUsers(query) {
            const resultsDiv = document.getElementById('user_search_results');

            fetch(`/books/{{ $book->id }}/users/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.users);
                } else {
                    resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--gray-500);">No users found</div>';
                    resultsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error searching users:', error);
                resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--danger-color);">Error searching users</div>';
                resultsDiv.style.display = 'block';
            });
        }

        function displaySearchResults(users) {
            const resultsDiv = document.getElementById('user_search_results');

            if (users.length === 0) {
                resultsDiv.innerHTML = '<div style="padding: 0.75rem; color: var(--gray-500);">No users found</div>';
            } else {
                resultsDiv.innerHTML = users.map(user => `
                    <div onclick="selectUser(${user.id}, '${user.display.replace(/'/g, "\\'")}', '${user.name.replace(/'/g, "\\'")}', '${user.email.replace(/'/g, "\\'")}')"
                         style="
                             padding: 0.75rem;
                             cursor: pointer;
                             border-bottom: 1px solid var(--gray-100);
                             transition: background-color 0.2s;
                         "
                         onmouseover="this.style.backgroundColor='var(--gray-50)'"
                         onmouseout="this.style.backgroundColor='white'">
                        <div style="font-weight: 600; color: var(--gray-900);">${user.name}</div>
                        <div style="font-size: 0.875rem; color: var(--gray-500);">${user.email}</div>
                        ${!user.is_business_member ?
                            '<div style="font-size: 0.75rem; color: var(--warning-color); font-weight: 500;">⚠ Will be added to business</div>' :
                            ''
                        }
                    </div>
                `).join('');
            }

            resultsDiv.style.display = 'block';
        }

        function selectUser(userId, display, name, email) {
            const searchInput = document.getElementById('user_search');
            const resultsDiv = document.getElementById('user_search_results');
            const selectedUserIdInput = document.getElementById('selected_user_id');
            const selectedUserDisplay = document.getElementById('selected_user_display');
            const selectedUserText = document.getElementById('selected_user_text');
            const addBtn = document.getElementById('add-user-btn');

            // Set the selected user
            selectedUserIdInput.value = userId;
            selectedUserText.textContent = display;

            // Hide search input and show selected user display
            searchInput.style.display = 'none';
            resultsDiv.style.display = 'none';
            selectedUserDisplay.style.display = 'block';

            // Enable the add button
            addBtn.disabled = false;
        }

        function clearSelectedUser() {
            const searchInput = document.getElementById('user_search');
            const resultsDiv = document.getElementById('user_search_results');
            const selectedUserIdInput = document.getElementById('selected_user_id');
            const selectedUserDisplay = document.getElementById('selected_user_display');
            const addBtn = document.getElementById('add-user-btn');

            // Clear all fields
            searchInput.value = '';
            searchInput.style.display = 'block';
            selectedUserIdInput.value = '';
            resultsDiv.style.display = 'none';
            selectedUserDisplay.style.display = 'none';
            addBtn.disabled = true;
        }
    </script>
</x-app-layout>
