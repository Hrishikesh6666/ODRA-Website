@extends('layouts.admin')

@section('title', 'Freight Matrix')
@section('header-title', 'Sweeper Transport & Freight Matrix')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.freight.index') }}" style="display: flex; gap: 12px; align-items: center;">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            style="width: 260px;" 
            placeholder="Search city, state, or ZIP..." 
            value="{{ request('search') }}"
        >
        <select name="state" class="form-control" style="width: 180px;" onchange="this.form.submit()">
            <option value="">All States</option>
            @foreach ($states as $st)
                <option value="{{ $st }}" {{ request('state') == $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'state']))
            <a href="{{ route('admin.freight.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>

    <button type="button" class="btn-primary" onclick="document.getElementById('addModal').style.display='flex'">
        + Add Destination
    </button>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>State</th>
                <th>City / Delivery Destination</th>
                <th>Customer Rate (Commercial)</th>
                <th>Municipality Rate (Cooperative)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($freightRules as $rule)
                <tr>
                    <td><strong>{{ $rule->state }}</strong></td>
                    <td>{{ $rule->city }}</td>
                    <td><strong>${{ number_format($rule->customer_rate, 0) }}</strong></td>
                    <td><strong>${{ number_format($rule->municipality_rate, 0) }}</strong></td>
                    <td>
                        <button class="btn-secondary" onclick="openEditModal({{ json_encode($rule) }})">Edit Rate</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px;">
        {{ $freightRules->links() }}
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:480px; max-width:90%;">
        <h3 style="margin-top:0;">Add Freight Destination</h3>
        <form action="{{ route('admin.freight.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>State</label>
                <input type="text" name="state" class="form-control" required placeholder="e.g. Texas">
            </div>
            <div class="form-group">
                <label>City &amp; ZIP</label>
                <input type="text" name="city" class="form-control" required placeholder="e.g. Dallas, TX 75247">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Customer Rate (USD)</label>
                    <input type="number" name="customer_rate" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>Municipality Rate (USD)</label>
                    <input type="number" name="municipality_rate" class="form-control" required min="0" step="1">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('addModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Add Destination</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:480px; max-width:90%;">
        <h3 style="margin-top:0;">Edit Freight Rates</h3>
        <p id="edit_city_label" style="font-size:13px; color:var(--text-muted); margin-top:-8px;"></p>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Customer Rate (USD)</label>
                    <input type="number" id="edit_customer_rate" name="customer_rate" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>Municipality Rate (USD)</label>
                    <input type="number" id="edit_municipality_rate" name="municipality_rate" class="form-control" required min="0" step="1">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary">Save Rates</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(rule) {
    document.getElementById('editForm').action = `/admin/freight/${rule.id}`;
    document.getElementById('edit_city_label').textContent = `${rule.city}, ${rule.state}`;
    document.getElementById('edit_customer_rate').value = rule.customer_rate;
    document.getElementById('edit_municipality_rate').value = rule.municipality_rate;
    document.getElementById('editModal').style.display = 'flex';
}
</script>
@endsection
