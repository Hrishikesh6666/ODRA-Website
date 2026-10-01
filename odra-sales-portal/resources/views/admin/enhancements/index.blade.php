@extends('layouts.admin')

@section('title', 'Enhancements & Parts')
@section('header-title', 'Enhancements, Options & Parts')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.enhancements.index') }}" style="display: flex; gap: 12px; align-items: center;">
        <select name="model" class="form-control" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Models</option>
            @foreach ($models as $m)
                <option value="{{ $m->code }}" {{ request('model') == $m->code ? 'selected' : '' }}>{{ $m->code }}</option>
            @endforeach
        </select>
        <select name="category" class="form-control" style="width: 180px;" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @if (request()->hasAny(['model', 'category']))
            <a href="{{ route('admin.enhancements.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>

    <button type="button" class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        + Add Enhancement
    </button>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Model</th>
                <th>Category</th>
                <th>Description</th>
                <th>MSRP</th>
                <th>SW List</th>
                <th>SW Net</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($enhancements as $enh)
                <tr>
                    <td><strong>{{ $enh->model->code }}</strong></td>
                    <td><span style="font-size: 11px; font-weight: bold; background: #e2e8f0; padding: 3px 6px; border-radius: 4px;">{{ $enh->category }}</span></td>
                    <td>{{ $enh->description }}</td>
                    <td>${{ number_format($enh->msrp, 0) }}</td>
                    <td>${{ number_format($enh->sw_list, 0) }}</td>
                    <td><strong>${{ number_format($enh->sw_net, 0) }}</strong></td>
                    <td>
                        <span class="badge {{ $enh->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $enh->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-secondary" onclick="openEditModal({{ json_encode($enh) }})">Edit</button>
                            <form action="{{ route('admin.enhancements.toggle', $enh) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-secondary" style="color: {{ $enh->is_active ? '#991b1b' : '#166534' }};">
                                    {{ $enh->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px;">
        {{ $enhancements->links() }}
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:540px; max-width:90%;">
        <h3 style="margin-top:0;">Add Enhancement Option</h3>
        <form action="{{ route('admin.enhancements.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Sweeper Model</label>
                <select name="sweeper_model_id" class="form-control" required>
                    @foreach ($models as $m)
                        <option value="{{ $m->id }}">{{ $m->code }} &mdash; {{ $m->category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Category (e.g. LIGHTS, BROOMS, WATER, HOPPER)</label>
                <input type="text" name="category" class="form-control" required placeholder="e.g. LIGHTS">
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" required placeholder="Enhancement name/description">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>MSRP (USD)</label>
                    <input type="number" name="msrp" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>SW List</label>
                    <input type="number" name="sw_list" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>SW Net</label>
                    <input type="number" name="sw_net" class="form-control" required min="0" step="1">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Add to Catalog</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:540px; max-width:90%;">
        <h3 style="margin-top:0;">Edit Enhancement</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Category</label>
                <input type="text" id="edit_category" name="category" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" id="edit_description" name="description" class="form-control" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                <div class="form-group">
                    <label>MSRP (USD)</label>
                    <input type="number" id="edit_msrp" name="msrp" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>SW List</label>
                    <input type="number" id="edit_sw_list" name="sw_list" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>SW Net</label>
                    <input type="number" id="edit_sw_net" name="sw_net" class="form-control" required min="0" step="1">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(enh) {
    document.getElementById('editForm').action = `/admin/enhancements/${enh.id}`;
    document.getElementById('edit_category').value = enh.category;
    document.getElementById('edit_description').value = enh.description;
    document.getElementById('edit_msrp').value = enh.msrp;
    document.getElementById('edit_sw_list').value = enh.sw_list;
    document.getElementById('edit_sw_net').value = enh.sw_net;
    document.getElementById('editModal').style.display = 'flex';
}
</script>
@endsection
