<x-app-layout>
<div style="background: #fff; min-height: 100vh; padding: 0;">

    @php
        $currentUser = Auth::user();
        $myRole = $business->users()->where('users.id', $currentUser->id)->value('role');
    @endphp

    {{-- ── Tab Header ── --}}
    <div style="border-bottom: 1px solid #e5e7eb; padding: 0 2rem;">
        <div style="display: inline-flex; align-items: center; padding: 14px 0 0; border-bottom: 2px solid #4f46e5; margin-bottom: -1px; gap: 8px; cursor: default;">
            <svg width="16" height="16" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
            </svg>
            <span style="font-size: 0.9375rem; font-weight: 600; color: #4f46e5; padding-bottom: 14px; display: inline-block;">
                All Members ({{ $members->count() }})
            </span>
        </div>
    </div>

    <div style="padding: 1.25rem 2rem 2rem;">

        {{-- ── Toolbar: Search + Actions ── --}}
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;">
            {{-- Search --}}
            <div style="position: relative; flex: 1; max-width: 420px;">
                <svg width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"
                     style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="memberSearch" placeholder="Search by name, number, employee ID..."
                       oninput="filterMembers()"
                       style="width: 100%; padding: 9px 36px 9px 32px; border: 1px solid #e5e7eb; border-radius: 7px; font-size: 0.8125rem; color: #374151; background: #fff; outline: none; font-family: inherit; box-sizing: border-box;">
                <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #9ca3af; border: 1px solid #e5e7eb; border-radius: 4px; padding: 1px 5px; pointer-events: none;">/</span>
            </div>

            {{-- Right actions --}}
            <div style="display: flex; align-items: center; gap: 10px;">
                {{-- View toggle --}}
                <button type="button" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e5e7eb; border-radius: 7px; background: #fff; cursor: pointer; color: #6b7280;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                    </svg>
                </button>

                {{-- Download button --}}
                <button type="button" onclick="downloadCSV()" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #e5e7eb; border-radius: 7px; background: #fff; cursor: pointer; font-size: 0.8125rem; font-weight: 500; color: #374151; font-family: inherit;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download
                </button>
            </div>
        </div>

        {{-- ── Filter Dropdowns ── --}}
        <div style="display: flex; gap: 8px; margin-bottom: 1.25rem;" x-data="{ inviteOpen: false, roleOpen: false, roleFilter: 'all' }">
            {{-- Invite Status --}}
            <div style="position: relative;">
                <button @click="inviteOpen = !inviteOpen" type="button"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border: 1px solid #e5e7eb; border-radius: 7px; background: #fff; font-size: 0.8125rem; color: #374151; cursor: pointer; font-family: inherit;">
                    Invite Status
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="inviteOpen" @click.away="inviteOpen = false" x-cloak
                     style="position: absolute; top: calc(100% + 4px); left: 0; background: #fff; border: 1px solid #e5e7eb; border-radius: 7px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 50; min-width: 140px; padding: 4px 0;">
                    <button type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">All</button>
                    <button type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">Accepted</button>
                    <button type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">Pending</button>
                </div>
            </div>

            {{-- Role filter --}}
            <div style="position: relative;">
                <button @click="roleOpen = !roleOpen" type="button"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border: 1px solid #e5e7eb; border-radius: 7px; background: #fff; font-size: 0.8125rem; color: #374151; cursor: pointer; font-family: inherit;">
                    Role
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="roleOpen" @click.away="roleOpen = false" x-cloak
                     style="position: absolute; top: calc(100% + 4px); left: 0; background: #fff; border: 1px solid #e5e7eb; border-radius: 7px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 50; min-width: 160px; padding: 4px 0;">
                    <button @click="roleFilter='all'; roleOpen=false; filterMembers()" type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">All Roles</button>
                    <button @click="roleFilter='primary_admin'; roleOpen=false; filterMembers()" type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">Primary Admin</button>
                    <button @click="roleFilter='admin'; roleOpen=false; filterMembers()" type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">Admin</button>
                    <button @click="roleFilter='employee'; roleOpen=false; filterMembers()" type="button" style="display: block; width: 100%; text-align: left; padding: 8px 12px; font-size: 0.8125rem; color: #374151; background: none; border: none; cursor: pointer; font-family: inherit;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='none'">Employee</button>
                </div>
            </div>
        </div>

        {{-- ── Members Table ── --}}
        <div style="border: 1px solid #f1f5f9; border-radius: 8px; overflow: hidden;">
            <table id="membersTable" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: #374151; font-size: 0.8125rem; background: #fff; white-space: nowrap; width: 22%;">Name</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: #374151; font-size: 0.8125rem; background: #fff; white-space: nowrap; width: 13%;">Employee ID</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: #374151; font-size: 0.8125rem; background: #fff; white-space: nowrap; width: 28%;">Email</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: #374151; font-size: 0.8125rem; background: #fff; white-space: nowrap; width: 20%;">Mobile Number</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 600; color: #374151; font-size: 0.8125rem; background: #fff; white-space: nowrap; width: 17%;">Role</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    @foreach($members as $member)
                        @php
                            $memberRole = $member->pivot->role;
                            $isMe = $member->id === $currentUser->id;
                            $displayName = $member->name . ($isMe ? ' (You)' : '');
                        @endphp
                        <tr class="member-row"
                            data-name="{{ strtolower($member->name) }}"
                            data-email="{{ strtolower($member->email) }}"
                            data-role="{{ $memberRole }}"
                            style="border-bottom: 1px solid #f8fafc; transition: background 0.15s;"
                            onmouseover="this.style.background='#fafafa'"
                            onmouseout="this.style.background='#fff'">

                            {{-- Name --}}
                            <td style="padding: 14px 16px; color: #111827; font-weight: 500; font-size: 0.875rem;">
                                {{ $displayName }}
                            </td>

                            {{-- Employee ID --}}
                            <td style="padding: 14px 16px; color: #9ca3af; font-size: 0.875rem;">-</td>

                            {{-- Email --}}
                            <td style="padding: 14px 16px; color: #374151; font-size: 0.875rem;">
                                {{ $member->email ?? '-' }}
                            </td>

                            {{-- Mobile Number --}}
                            <td style="padding: 14px 16px; color: #374151; font-size: 0.875rem;">
                                {{ $member->phone ?? '-' }}
                            </td>

                            {{-- Role Badge --}}
                            <td style="padding: 14px 16px;">
                                @if($memberRole === 'primary_admin')
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 5px; font-size: 0.78rem; font-weight: 600; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">Primary Admin</span>
                                @elseif($memberRole === 'admin')
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 5px; font-size: 0.78rem; font-weight: 600; background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;">Admin</span>
                                @else
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 5px; font-size: 0.78rem; font-weight: 600; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">Employee</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Collapsible Manage Section (invite, role change, remove, leave) ── --}}
        <div style="margin-top: 2rem;" x-data="{ showManage: false }">
            <button @click="showManage = !showManage" type="button"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #e5e7eb; border-radius: 7px; background: #f9fafb; font-size: 0.8125rem; font-weight: 500; color: #374151; cursor: pointer; font-family: inherit;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Manage Team & Settings
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" :style="showManage ? 'transform: rotate(180deg)' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="showManage" x-cloak style="margin-top: 1.5rem; display: grid; gap: 1.5rem;">

                {{-- Invite New Member --}}
                <div style="padding: 1.5rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <h2 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 1rem;">Invite New Member</h2>
                    <form method="post" action="{{ route('settings.invite') }}" style="max-width: 32rem;">
                        @csrf
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.8125rem; font-weight: 500; color: #374151; margin-bottom: 5px;">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email address" required
                                   style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; font-family: inherit; outline: none; box-sizing: border-box;">
                            <x-input-error :messages="$errors->get('email')" style="margin-top: 4px; font-size: 0.78rem; color: #dc2626;" />
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-size: 0.8125rem; font-weight: 500; color: #374151; margin-bottom: 5px;">Role</label>
                            <select name="role" style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; font-family: inherit; outline: none; background: #fff; max-width: 32rem; box-sizing: border-box;">
                                <option value="primary_admin">Primary Admin</option>
                                <option value="admin">Admin</option>
                                <option value="employee" selected>Employee</option>
                            </select>
                        </div>
                        <button type="submit" style="padding: 8px 18px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit;">Invite</button>
                    </form>
                </div>

                {{-- Member Role Management --}}
                <div style="padding: 1.5rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <h2 style="font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 1rem;">Member Roles</h2>
                    @foreach($members as $member)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
                            <div>
                                <div style="font-weight: 500; font-size: 0.875rem; color: #111827;">{{ $member->name }}{{ $member->id === $currentUser->id ? ' (You)' : '' }}</div>
                                <div style="font-size: 0.78rem; color: #6b7280;">{{ $member->email }}</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <form method="post" action="{{ route('settings.member.role', $member) }}">
                                    @csrf
                                    <select name="role" onchange="this.form.submit()" style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.8125rem; font-family: inherit; background: #fff; cursor: pointer;">
                                        <option value="primary_admin" @if($member->pivot->role === 'primary_admin') selected @endif>Primary Admin</option>
                                        <option value="admin" @if($member->pivot->role === 'admin') selected @endif>Admin</option>
                                        <option value="employee" @if($member->pivot->role === 'employee') selected @endif>Employee</option>
                                    </select>
                                </form>
                                <form method="post" action="{{ route('settings.member.remove', $member) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 6px 12px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; font-size: 0.8125rem; font-weight: 500; cursor: pointer; font-family: inherit;"
                                            onclick="return confirm('Remove {{ $member->name }}?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    <x-input-error :messages="$errors->get('member')" style="margin-top: 8px; font-size: 0.78rem; color: #dc2626;" />
                </div>

                {{-- Leave Business --}}
                <div style="padding: 1.5rem; background: #fff; border: 1px solid #fee2e2; border-radius: 8px;">
                    <h2 style="font-size: 1rem; font-weight: 600; color: #dc2626; margin-bottom: 6px;">Leave Business</h2>
                    <p style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 1rem;">If you leave this business, you will lose access to all of its resources.</p>
                    <form method="post" action="{{ route('settings.leave') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding: 8px 18px; background: #dc2626; color: #fff; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: inherit;"
                                onclick="return confirm('Are you sure you want to leave this business?')">Leave Business</button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function filterMembers() {
    const search = document.getElementById('memberSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.member-row');
    rows.forEach(function(row) {
        const name = row.getAttribute('data-name') || '';
        const email = row.getAttribute('data-email') || '';
        const matches = name.includes(search) || email.includes(search);
        row.style.display = matches ? '' : 'none';
    });
}

function downloadCSV() {
    const rows = document.querySelectorAll('#membersTableBody tr.member-row');
    let csv = 'Name,Employee ID,Email,Mobile Number,Role\n';
    rows.forEach(function(row) {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 5) {
            const name = cells[0].innerText.replace(/,/g, ' ').trim();
            const empId = cells[1].innerText.trim();
            const email = cells[2].innerText.trim();
            const mobile = cells[3].innerText.trim();
            const role = cells[4].innerText.trim();
            csv += `"${name}","${empId}","${email}","${mobile}","${role}"\n`;
        }
    });
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'team-members.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
</x-app-layout>
