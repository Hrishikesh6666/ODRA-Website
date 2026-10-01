@extends('layouts.admin')

@section('title', 'Sales Representatives')
@section('header-title', 'Sales Representatives & User Accounts')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 12px; align-items: center;">
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

    <button type="button" class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        + Add Sales Representative
    </button>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Representative Name</th>
                <th>Rep Code</th>
                <th>Office Email</th>
                <th>Company ID</th>
                <th>Territory</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
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
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-secondary" onclick="openEditModal({{ json_encode($u) }})">Edit</button>
                            <form action="{{ route('admin.users.toggle', $u) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-secondary" style="color: {{ $u->is_active ? '#991b1b' : '#166534' }};">
                                    {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px;">
        {{ $users->links() }}
    </div>
</div>

<!-- Add Representative Modal -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:520px; max-width:90%;">
        <h3 style="margin-top:0;">Register Sales Representative</h3>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
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
                    <label>Quote Rep Code (2 letters)</label>
                    <input type="text" name="rep_code" class="form-control" required maxlength="4" placeholder="e.g. JD">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. REP017">
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
                <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Register Representative</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Representative Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:520px; max-width:90%;">
        <h3 style="margin-top:0;">Edit Representative Account</h3>
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
                <div class="form-group">
                    <label>Quote Rep Code</label>
                    <input type="text" id="edit_rep_code" name="rep_code" class="form-control" required maxlength="4">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Company ID</label>
                    <input type="text" id="edit_company_id" name="company_id" class="form-control">
                </div>
                <div class="form-group">
                    <label>Territory / Region</label>
                    <input type="text" id="edit_territory" name="territory" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Reset Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" minlength="8" placeholder="Enter new password to reset">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(user) {
    document.getElementById('editForm').action = `/admin/users/${user.id}`;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_rep_code').value = user.rep_code || '';
    document.getElementById('edit_company_id').value = user.company_id || '';
    document.getElementById('edit_territory').value = user.territory || '';
    document.getElementById('editModal').style.display = 'flex';
}
</script>
@endsection
