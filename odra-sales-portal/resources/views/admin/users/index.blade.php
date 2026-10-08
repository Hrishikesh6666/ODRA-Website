@extends('layouts.admin')

@section('title', 'Users & Access Control')
@section('header-title', 'User Accounts & Access Control')

@section('content')
<!-- SECTION 1: TECHNICAL ADMINISTRATORS -->
<div class="card" style="margin-bottom: 32px; border-left: 4px solid var(--red);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                    🛡️ Technical Administrators
                </h3>
                <span style="background: #fee2e2; color: #991b1b; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                    {{ count($admins) }} {{ count($admins) === 1 ? 'Admin' : 'Admins' }}
                </span>
            </div>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--text-muted);">
                Technical administrators manage catalog models, options catalog, freight matrices, user accounts, and audit trails. 
                <strong style="color: #991b1b;">(Strictly shielded: No financial totals or revenue visibility)</strong>
            </p>
        </div>

        <button type="button" class="btn-primary" onclick="openAddAdminModal()">
            + Add Technical Admin
        </button>
    </div>

    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 480px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th>Administrator Name</th>
                    <th>Office Email</th>
                    <th>Company ID</th>
                    <th>Role Scope</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($admins as $admin)
                    <tr>
                        <td>
                            <strong>{{ $admin->name }}</strong>
                            @if ($admin->id === auth()->id())
                                <span style="margin-left: 6px; font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 600;">You</span>
                            @endif
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->company_id ?? '&mdash;' }}</td>
                        <td>
                            <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                Technical Admin
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $admin->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $admin->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td>{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px; align-items: center;">
                                <button class="btn-secondary" onclick="openEditModal({{ json_encode($admin) }}, 'admin')">Edit</button>
                                
                                @if ($admin->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle', $admin) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="color: {{ $admin->is_active ? '#991b1b' : '#166534' }};">
                                            {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <button type="button" class="btn-secondary" style="color: #991b1b; border-color: #fecaca;" onclick="confirmDeleteUser({{ $admin->id }}, '{{ addslashes($admin->name) }}', 'technical administrator')">
                                        Remove
                                    </button>
                                @else
                                    <span style="font-size: 12px; color: #94a3b8; font-style: italic; padding: 0 4px;">Current Session</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No technical administrators found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 2: COMMERCIAL MANAGERS -->
<div class="card" style="margin-bottom: 32px; border-left: 4px solid #3b82f6;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                    👔 Commercial Managers
                </h3>
                <span style="background: #dbeafe; color: #1e40af; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                    {{ count($managers) }} {{ count($managers) === 1 ? 'Manager' : 'Managers' }}
                </span>
            </div>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--text-muted);">
                Executive leadership and sales directors with price adjustment authority, executive dashboards, quotation inspection, and Excel analytics.
                <strong style="color: #1e40af;">(Exclusive financial visibility group)</strong>
            </p>
        </div>

        <button type="button" class="btn-primary" style="background: #2563eb;" onclick="openAddManagerModal()">
            + Add Commercial Manager
        </button>
    </div>

    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 480px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th>Manager Name</th>
                    <th>Office Email</th>
                    <th>Company ID</th>
                    <th>Scope / Title</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($managers as $mgr)
                    <tr>
                        <td><strong>{{ $mgr->name }}</strong></td>
                        <td>{{ $mgr->email }}</td>
                        <td>{{ $mgr->company_id ?? '&mdash;' }}</td>
                        <td>
                            <span style="background: #eff6ff; color: #1d4ed8; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                {{ $mgr->territory ?? 'Commercial Management' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $mgr->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $mgr->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td>{{ $mgr->last_login_at ? $mgr->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px; align-items: center;">
                                <button class="btn-secondary" onclick="openEditModal({{ json_encode($mgr) }}, 'manager')">Edit</button>
                                <form action="{{ route('admin.users.toggle', $mgr) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-secondary" style="color: {{ $mgr->is_active ? '#991b1b' : '#166534' }};">
                                        {{ $mgr->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <button type="button" class="btn-secondary" style="color: #991b1b; border-color: #fecaca;" onclick="confirmDeleteUser({{ $mgr->id }}, '{{ addslashes($mgr->name) }}', 'commercial manager')">
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No commercial managers configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 3: SALES REPRESENTATIVES -->
<div class="card" style="border-left: 4px solid #10b981;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="margin: 0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                    💼 Sales Representatives
                </h3>
                <span style="background: #f1f5f9; color: #475569; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                    {{ $users->total() }} Reps
                </span>
            </div>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--text-muted);">
                Field sales representatives configure sweeper builds, generate customer proposals, and manage quotations in their assigned territories.
            </p>
        </div>

        <button type="button" class="btn-primary" style="background: #059669;" onclick="openAddRepModal()">
            + Add Sales Representative
        </button>
    </div>

    <!-- Filter & Search Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #f8fafc; padding: 12px 16px; border-radius: 6px; border: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                style="width: 260px;" 
                placeholder="Search name, email, code..." 
                value="{{ request('search') }}"
            >
            <select name="status" class="form-control" style="width: 140px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Deactivated</option>
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 480px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th>Representative Name</th>
                    <th>Rep Code</th>
                    <th>Office Email</th>
                    <th>Company ID</th>
                    <th>Assigned Territory</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td><code>{{ $u->rep_code }}</code></td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->company_id ?? '&mdash;' }}</td>
                        <td>{{ $u->territory ?? 'Assigned Territories' }}</td>
                        <td>
                            <span class="badge {{ $u->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $u->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td>{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px; align-items: center;">
                                <button class="btn-secondary" onclick="openEditModal({{ json_encode($u) }}, 'sales')">Edit</button>
                                <form action="{{ route('admin.users.toggle', $u) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-secondary" style="color: {{ $u->is_active ? '#991b1b' : '#166534' }};">
                                        {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <button type="button" class="btn-secondary" style="color: #991b1b; border-color: #fecaca;" onclick="confirmDeleteUser({{ $u->id }}, '{{ addslashes($u->name) }}', 'sales representative')">
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 24px;">No sales representatives found matching your search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 16px;">
        {{ $users->links() }}
    </div>
</div>

<!-- Modal 1: Add Administrator Modal -->
<div id="addAdminModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:500px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                🛡️ Register Technical Administrator
            </h3>
            <button type="button" onclick="closeModal('addAdminModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: -8px; margin-bottom: 16px;">
            This user receives technical catalog control, freight matrix, and user provisioning. Strictly isolated from financial totals.
        </p>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role_type" value="admin">
            
            <div class="form-group">
                <label>Administrator Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Dev Raj">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Corporate / Office Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="cc@odrasweeper.com">
                </div>
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. ADMIN-DEV">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addAdminModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create Technical Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Add Commercial Manager Modal -->
<div id="addManagerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:500px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                👔 Register Commercial Manager
            </h3>
            <button type="button" onclick="closeModal('addManagerModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: -8px; margin-bottom: 16px;">
            This user receives executive dashboards, company-wide quote oversight, price adjustment authority, and Excel reporting access.
        </p>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role_type" value="manager">
            
            <div class="form-group">
                <label>Manager Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Alex Byczynski">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Corporate Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="alex@odrasweeper.com">
                </div>
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. MGR-ALEX">
                </div>
            </div>

            <div class="form-group">
                <label>Scope / Title</label>
                <input type="text" name="territory" class="form-control" placeholder="e.g. Executive Management / Sales Director">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addManagerModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #2563eb;">Create Manager</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add Sales Representative Modal -->
<div id="addRepModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:520px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                💼 Register Sales Representative
            </h3>
            <button type="button" onclick="closeModal('addRepModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role_type" value="sales">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. John Doe">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Office Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="john.doe@odrasweeper.com">
                </div>
                <div class="form-group">
                    <label>Quote Rep Code (2-4 letters)</label>
                    <input type="text" name="rep_code" class="form-control" required maxlength="4" placeholder="e.g. JD">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. REP-JD">
                </div>
                <div class="form-group">
                    <label>Territory / Region</label>
                    <input type="text" name="territory" class="form-control" placeholder="e.g. Midwest, Illinois">
                </div>
            </div>
            <div class="form-group">
                <label>Initial Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addRepModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #059669;">Register Representative</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit User Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:520px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px;" id="editModalTitle">Edit User Account</h3>
            <button type="button" onclick="closeModal('editModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Office Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                <div class="form-group" id="edit_rep_code_group">
                    <label>Quote Rep Code</label>
                    <input type="text" id="edit_rep_code" name="rep_code" class="form-control" maxlength="4">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Company ID</label>
                    <input type="text" id="edit_company_id" name="company_id" class="form-control">
                </div>
                <div class="form-group" id="edit_territory_group">
                    <label id="edit_territory_label">Territory / Region</label>
                    <input type="text" id="edit_territory" name="territory" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Reset Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" minlength="8" placeholder="Enter new password to reset">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteUserForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
function openAddAdminModal() {
    document.getElementById('addAdminModal').style.display = 'flex';
}

function openAddManagerModal() {
    document.getElementById('addManagerModal').style.display = 'flex';
}

function openAddRepModal() {
    document.getElementById('addRepModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openEditModal(user, roleSlug) {
    document.getElementById('editForm').action = `/admin/users/${user.id}`;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_company_id').value = user.company_id || '';
    
    const repCodeGroup = document.getElementById('edit_rep_code_group');
    const repCodeInput = document.getElementById('edit_rep_code');
    const territoryGroup = document.getElementById('edit_territory_group');
    const territoryLabel = document.getElementById('edit_territory_label');
    const territoryInput = document.getElementById('edit_territory');
    const modalTitle = document.getElementById('editModalTitle');

    if (roleSlug === 'admin') {
        modalTitle.innerText = 'Edit Technical Administrator';
        repCodeGroup.style.display = 'none';
        repCodeInput.required = false;
        repCodeInput.value = '';
        territoryLabel.innerText = 'Technical Scope';
        territoryInput.value = user.territory || 'Technical Operations';
    } else if (roleSlug === 'manager') {
        modalTitle.innerText = 'Edit Commercial Manager';
        repCodeGroup.style.display = 'none';
        repCodeInput.required = false;
        repCodeInput.value = '';
        territoryLabel.innerText = 'Manager Scope / Title';
        territoryInput.value = user.territory || 'Commercial Management';
    } else {
        modalTitle.innerText = 'Edit Sales Representative';
        repCodeGroup.style.display = 'block';
        repCodeInput.required = true;
        repCodeInput.value = user.rep_code || '';
        territoryLabel.innerText = 'Assigned Territory';
        territoryInput.value = user.territory || '';
    }

    document.getElementById('editModal').style.display = 'flex';
}

function confirmDeleteUser(userId, userName, roleType) {
    if (confirm(`Are you sure you want to permanently remove ${roleType} "${userName}"?\n\nThis action cannot be undone.`)) {
        const form = document.getElementById('deleteUserForm');
        form.action = `/admin/users/${userId}`;
        form.submit();
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    const modals = ['addAdminModal', 'addManagerModal', 'addRepModal', 'editModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
@endsection
