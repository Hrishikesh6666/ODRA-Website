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
    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 280px); min-height: 250px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th>Model</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>MSRP</th>
                    <th>Discount (%)</th>
                    <th>Unit Price (Net)</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enhancements as $enh)
                    @php
                        $disc = $enh->discount_percent !== null 
                            ? (float) $enh->discount_percent 
                            : ($enh->sw_list > 0 ? round((1 - ((float)$enh->sw_net / (float)$enh->sw_list)) * 100, 1) : 0);
                    @endphp
                    <tr>
                        <td><strong>{{ $enh->model?->code ?? 'ALL' }}</strong></td>
                        <td><span style="font-size: 11px; font-weight: bold; background: #e2e8f0; padding: 3px 6px; border-radius: 4px;">{{ $enh->category }}</span></td>
                        <td>{{ $enh->description }}</td>
                        <td>${{ number_format($enh->msrp, 2) }}</td>
                        <td>
                            @if($disc > 0)
                                <span style="background: #dcfce7; color: #166534; font-weight: 700; padding: 2px 7px; border-radius: 4px; font-size: 11.5px;">
                                    {{ $disc }}% OFF
                                </span>
                            @else
                                <span style="color: #94a3b8; font-size: 11.5px;">0%</span>
                            @endif
                        </td>
                        <td><strong style="color: #059669;">${{ number_format($enh->sw_net, 2) }}</strong></td>
                        <td>
                            <span class="badge {{ $enh->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $enh->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                <button class="btn-secondary" onclick='openEditModal(@json($enh))'>Edit</button>
                                <form action="{{ route('admin.enhancements.toggle', $enh) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-secondary" style="color: {{ $enh->is_active ? '#991b1b' : '#166534' }};">
                                        {{ $enh->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.enhancements.destroy', $enh) }}" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to permanently remove this enhancement option?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="color: #dc2626;" title="Remove enhancement">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding: 16px;">
        {{ $enhancements->links() }}
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:580px; max-width:90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <h3 style="margin:0; font-size: 18px; font-weight: 700; color: #0f172a;">Add Enhancement Option</h3>
            <button type="button" onclick="document.getElementById('addModal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <form action="{{ route('admin.enhancements.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label style="font-weight: 700; font-size: 12.5px;">Sweeper Model</label>
                <select name="sweeper_model_id" class="form-control" required>
                    @foreach ($models as $m)
                        <option value="{{ $m->id }}">{{ $m->code }} &mdash; {{ $m->category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label style="font-weight: 700; font-size: 12.5px;">Category (e.g. LIGHTS, BROOMS, WATER, HOPPER, STEERING, WARRANTY)</label>
                <input type="text" name="category" class="form-control" required placeholder="e.g. LIGHTS">
            </div>
            <div class="form-group">
                <label style="font-weight: 700; font-size: 12.5px;">Description</label>
                <input type="text" name="description" class="form-control" required placeholder="Enhancement name/description">
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                <div class="form-group">
                    <label style="font-weight: 700; font-size: 12px;">MSRP ($)</label>
                    <input type="number" id="add_msrp" name="msrp" class="form-control" required min="0" step="0.01" placeholder="0.00" oninput="calcAddPrices('msrp')">
                </div>
                <div class="form-group">
                    <label style="font-weight: 700; font-size: 12px;">Discount Percentage (%)</label>
                    <input type="number" id="add_discount_percent" name="discount_percent" class="form-control" min="0" max="100" step="0.1" placeholder="e.g. 12" oninput="calcAddPrices('disc')">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 6px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border);">
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight: 700; font-size: 11.5px; color: #475569;">Cooperative List Price ($)</label>
                    <input type="number" id="add_sw_list" name="sw_list" class="form-control" min="0" step="0.01" placeholder="Auto-filled from MSRP" oninput="calcAddPrices('list')">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight: 700; font-size: 11.5px; color: #059669;">Discounted Unit Price ($)</label>
                    <input type="number" id="add_sw_net" name="sw_net" class="form-control" min="0" step="0.01" placeholder="Auto-calculated" oninput="calcAddPrices('net')">
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
    <div style="background:white; padding:24px; border-radius:8px; width:580px; max-width:90%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <h3 style="margin:0; font-size: 18px; font-weight: 700; color: #0f172a;">Edit Enhancement</h3>
            <button type="button" onclick="document.getElementById('editModal').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label style="font-weight: 700; font-size: 12.5px;">Category</label>
                <input type="text" id="edit_category" name="category" class="form-control" required>
            </div>
            <div class="form-group">
                <label style="font-weight: 700; font-size: 12.5px;">Description</label>
                <input type="text" id="edit_description" name="description" class="form-control" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                <div class="form-group">
                    <label style="font-weight: 700; font-size: 12px;">MSRP ($)</label>
                    <input type="number" id="edit_msrp" name="msrp" class="form-control" required min="0" step="0.01" oninput="calcEditPrices('msrp')">
                </div>
                <div class="form-group">
                    <label style="font-weight: 700; font-size: 12px;">Discount Percentage (%)</label>
                    <input type="number" id="edit_discount_percent" name="discount_percent" class="form-control" min="0" max="100" step="0.1" oninput="calcEditPrices('disc')">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 6px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border);">
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight: 700; font-size: 11.5px; color: #475569;">Cooperative List Price ($)</label>
                    <input type="number" id="edit_sw_list" name="sw_list" class="form-control" min="0" step="0.01" oninput="calcEditPrices('list')">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="font-weight: 700; font-size: 11.5px; color: #059669;">Discounted Unit Price ($)</label>
                    <input type="number" id="edit_sw_net" name="sw_net" class="form-control" min="0" step="0.01" oninput="calcEditPrices('net')">
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
function calcAddPrices(source) {
    const msrpEl = document.getElementById('add_msrp');
    const discEl = document.getElementById('add_discount_percent');
    const listEl = document.getElementById('add_sw_list');
    const netEl = document.getElementById('add_sw_net');

    let msrp = parseFloat(msrpEl.value) || 0;
    if (source === 'msrp' && (!listEl.value || parseFloat(listEl.value) === 0)) {
        listEl.value = msrp.toFixed(2);
    }
    let list = parseFloat(listEl.value) || msrp;

    if (source === 'disc' || source === 'msrp' || source === 'list') {
        const disc = parseFloat(discEl.value) || 0;
        const net = list * (1 - (disc / 100));
        netEl.value = net > 0 ? net.toFixed(2) : '0.00';
    } else if (source === 'net') {
        const net = parseFloat(netEl.value) || 0;
        if (list > 0 && list >= net) {
            discEl.value = (((1 - (net / list)) * 100)).toFixed(1);
        }
    }
}

function calcEditPrices(source) {
    const msrpEl = document.getElementById('edit_msrp');
    const discEl = document.getElementById('edit_discount_percent');
    const listEl = document.getElementById('edit_sw_list');
    const netEl = document.getElementById('edit_sw_net');

    let msrp = parseFloat(msrpEl.value) || 0;
    if (source === 'msrp' && (!listEl.value || parseFloat(listEl.value) === 0)) {
        listEl.value = msrp.toFixed(2);
    }
    let list = parseFloat(listEl.value) || msrp;

    if (source === 'disc' || source === 'msrp' || source === 'list') {
        const disc = parseFloat(discEl.value) || 0;
        const net = list * (1 - (disc / 100));
        netEl.value = net > 0 ? net.toFixed(2) : '0.00';
    } else if (source === 'net') {
        const net = parseFloat(netEl.value) || 0;
        if (list > 0 && list >= net) {
            discEl.value = (((1 - (net / list)) * 100)).toFixed(1);
        }
    }
}

function openEditModal(enh) {
    document.getElementById('editForm').action = `/admin/enhancements/${enh.id}`;
    document.getElementById('edit_category').value = enh.category || '';
    document.getElementById('edit_description').value = enh.description || '';
    document.getElementById('edit_msrp').value = parseFloat(enh.msrp || 0).toFixed(2);
    document.getElementById('edit_sw_list').value = parseFloat(enh.sw_list || enh.msrp || 0).toFixed(2);
    document.getElementById('edit_sw_net').value = parseFloat(enh.sw_net || 0).toFixed(2);

    let disc = 0;
    if (enh.discount_percent !== null && enh.discount_percent !== undefined) {
        disc = parseFloat(enh.discount_percent);
    } else if (enh.sw_list > 0 && enh.sw_net > 0 && enh.sw_list >= enh.sw_net) {
        disc = ((1 - (enh.sw_net / enh.sw_list)) * 100);
    }
    document.getElementById('edit_discount_percent').value = disc.toFixed(1);

    document.getElementById('editModal').style.display = 'flex';
}
</script>
@endsection
