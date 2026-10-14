@extends('layouts.admin')

@section('title', 'Add New Model')
@section('header-title', 'Create New Sweeper Model')

@section('content')
<div class="card" style="max-width: 900px;">
    <form action="{{ route('admin.models.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
            <div class="form-group">
                <label>Model Code (e.g. MT4H, MS5H, MS6H)</label>
                <input type="text" name="code" id="modelCodeInput" class="form-control" value="{{ old('code') }}" required placeholder="e.g. MS6H" style="text-transform: uppercase; font-weight: 700;">
            </div>

            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', 'MECHANICAL BROOM SWEEPER') }}" required>
            </div>
        </div>

        <div class="form-group" style="background: #ffffff; border: 2px solid var(--red); border-radius: 8px; padding: 18px; margin: 14px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label style="font-size: 13.5px; font-weight: 700; color: var(--red); margin: 0;">
                    Machine Description / Quotation Body Specifications
                </label>
                <span class="badge" style="background: #fee2e2; color: #ac2529; font-weight: 700; font-size: 11px;">
                    Rendered in Bold on Quotations
                </span>
            </div>
            <p style="font-size: 12px; color: #64748b; margin: 0 0 10px; line-height: 1.45;">
                This description appears under the <strong>DESCRIPTION</strong> column on the quotation page whenever this model is selected.
            </p>
            <textarea name="description" class="form-control" rows="5" required placeholder="Enter sweeper standard specifications and features...">{{ old('description') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; margin: 20px 0; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
            <div class="form-group" style="margin:0;">
                <label style="font-weight:700; font-size:12px;">MSRP (Commercial USD)</label>
                <input type="number" id="create_model_msrp" name="msrp" class="form-control" value="{{ old('msrp', 0) }}" required min="0" step="1" oninput="calcCreateModelPrices('msrp')">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-weight:700; font-size:12px;">Coop List Price ($)</label>
                <input type="number" id="create_model_sw_list" name="sw_list" class="form-control" value="{{ old('sw_list', 0) }}" required min="0" step="1" oninput="calcCreateModelPrices('list')">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-weight:700; font-size:12px; color:#1e40af;">Contract Discount (%)</label>
                <input type="number" id="create_model_discount_percent" name="discount_percent" class="form-control" value="{{ old('discount_percent', '') }}" min="0" max="100" step="0.1" placeholder="e.g. 12" oninput="calcCreateModelPrices('disc')">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-weight:700; font-size:12px; color:#059669;">Coop Net Price ($)</label>
                <input type="number" id="create_model_sw_net" name="sw_net" class="form-control" value="{{ old('sw_net', 0) }}" required min="0" step="0.01" oninput="calcCreateModelPrices('net')">
            </div>
        </div>

        <!-- Chassis / Discharge Configurations -->
        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 18px; margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <label style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Chassis / Discharge Configurations</label>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        Add one or more chassis options that Sales Reps can choose for this model on the Quotation Builder page.
                    </div>
                </div>
                <button type="button" class="btn-secondary" onclick="addConfigRow()" style="font-size: 12px; padding: 6px 12px;">
                    + Add Chassis Option
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr 140px 38px; gap: 10px; margin-bottom: 6px; font-size: 11.5px; font-weight: 700; color: #475569;">
                <div>Full Chassis Description</div>
                <div>Short Label (Quote Header)</div>
                <div>Price Adj. ($)</div>
                <div></div>
            </div>

            <div id="configsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                <div class="config-row" style="display: grid; grid-template-columns: 1.5fr 1fr 140px 38px; gap: 10px; align-items: center;">
                    <input type="text" name="configurations[0][label]" class="form-control" placeholder="Full Chassis Description (e.g. MS6H - Freightliner M2 106, 26,000 GVWR, non-CDL)">
                    <input type="text" name="configurations[0][short_label]" class="form-control" placeholder="Short Label for Quote Header (e.g. MS6H Freightliner Class 6, non-CDL)">
                    <input type="number" name="configurations[0][price]" class="form-control" placeholder="0.00" value="0" min="0" step="1">
                    <button type="button" class="btn-secondary" onclick="this.closest('.config-row').remove()" style="color: #991b1b; padding: 8px 10px;">&times;</button>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn-primary">Create Model &amp; Publish to Quote Builder</button>
            <a href="{{ route('admin.models.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let cfgIdx = 1;
function addConfigRow() {
    const code = (document.getElementById('modelCodeInput')?.value || 'MODEL').toUpperCase();
    const container = document.getElementById('configsContainer');
    const div = document.createElement('div');
    div.className = 'config-row';
    div.style.cssText = 'display: grid; grid-template-columns: 1.5fr 1fr 140px 38px; gap: 10px; align-items: center;';
    div.innerHTML = `
        <input type="text" name="configurations[${cfgIdx}][label]" class="form-control" placeholder="Full Chassis Description (e.g. ${code} - Standard Chassis)">
        <input type="text" name="configurations[${cfgIdx}][short_label]" class="form-control" placeholder="Short Label for Quote Header (e.g. ${code} Standard)">
        <input type="number" name="configurations[${cfgIdx}][price]" class="form-control" placeholder="0.00" value="0" min="0" step="1">
        <button type="button" class="btn-secondary" onclick="this.closest('.config-row').remove()" style="color: #991b1b; padding: 8px 10px;">&times;</button>
    `;
    container.appendChild(div);
    cfgIdx++;
}

function calcCreateModelPrices(source) {
    const msrpEl = document.getElementById('create_model_msrp');
    const listEl = document.getElementById('create_model_sw_list');
    const discEl = document.getElementById('create_model_discount_percent');
    const netEl = document.getElementById('create_model_sw_net');

    let msrp = parseFloat(msrpEl.value) || 0;
    let list = parseFloat(listEl.value) || 0;

    if (source === 'msrp' && (!list || list === 0)) {
        list = msrp;
        listEl.value = Math.round(list);
    }

    if (source === 'disc' || source === 'msrp') {
        const disc = parseFloat(discEl.value) || 0;
        const net = list > 0 ? Math.round(list * (1 - (disc / 100)) * 100) / 100 : 0;
        netEl.value = net > 0 ? (net % 1 === 0 ? net : net.toFixed(2)) : 0;
    } else if (source === 'net') {
        const net = parseFloat(netEl.value) || 0;
        if (list > 0 && net >= 0 && list >= net) {
            const disc = Math.round((1 - (net / list)) * 1000) / 10;
            discEl.value = disc > 0 ? disc : 0;
        }
    } else if (source === 'list') {
        const disc = parseFloat(discEl.value) || 0;
        if (disc > 0) {
            const net = Math.round(list * (1 - (disc / 100)) * 100) / 100;
            netEl.value = net > 0 ? (net % 1 === 0 ? net : net.toFixed(2)) : 0;
        } else {
            const net = parseFloat(netEl.value) || 0;
            if (list > 0 && net > 0 && list >= net) {
                discEl.value = Math.round((1 - (net / list)) * 1000) / 10;
            }
        }
    }
}
</script>
@endpush
@endsection
