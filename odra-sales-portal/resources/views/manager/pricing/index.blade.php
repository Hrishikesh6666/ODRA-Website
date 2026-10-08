@extends('layouts.manager')

@section('title', 'Master Pricing Management')
@section('header-title', 'Equipment & Option Pricing Master')

@section('content')
<!-- Base Model Equipment Pricing -->
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Base Equipment Master Pricing</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">MSRP, Sourcewell List, and Sourcewell Net base pricing for complete sweeper units.</p>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Model Code</th>
                    <th>Category</th>
                    <th style="text-align: right;">Commercial MSRP</th>
                    <th style="text-align: right;">Sourcewell List</th>
                    <th style="text-align: right;">Sourcewell Net</th>
                    <th>Chassis Options</th>
                    <th style="text-align: right; width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($models as $m)
                    @php
                        $p = $m->prices->first();
                    @endphp
                    <tr>
                        <td><strong>{{ $m->code }}</strong></td>
                        <td>{{ $m->category }}</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;">
                            ${{ number_format($p?->msrp ?? 0, 0) }}
                        </td>
                        <td style="text-align: right; color: #475569;">
                            ${{ number_format($p?->sw_list ?? 0, 0) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #059669;">
                            ${{ number_format($p?->sw_net ?? 0, 0) }}
                        </td>
                        <td>{{ $m->configurations->count() }} configurations</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn-secondary" style="padding: 4px 10px; font-size: 12px;" onclick="openModelPriceModal({{ json_encode($m) }}, {{ json_encode($p) }})">
                                Edit Price
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Catalog Enhancements & Parts Pricing -->
<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Options &amp; Enhancements Pricing</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">Master pricing for lights, brooms, water, hoppers, warranties, and special options.</p>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Model</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th style="text-align: right;">MSRP ($)</th>
                    <th style="text-align: right;">SW List ($)</th>
                    <th style="text-align: right;">SW Net ($)</th>
                    <th style="text-align: right; width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enhancements as $enh)
                    <tr>
                        <td><code>{{ $enh->sweeperModel?->code ?? 'ALL' }}</code></td>
                        <td><span style="font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $enh->category }}</span></td>
                        <td>{{ $enh->description }}</td>
                        <td style="text-align: right; font-weight: 600;">${{ number_format($enh->msrp, 2) }}</td>
                        <td style="text-align: right; color: #475569;">${{ number_format($enh->sw_list, 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: #059669;">${{ number_format($enh->sw_net, 2) }}</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn-secondary" style="padding: 4px 10px; font-size: 12px;" onclick="openEnhPriceModal({{ json_encode($enh) }})">
                                Edit Price
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Edit Model Price -->
<div id="modelPriceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="modalModelTitle" style="margin: 0 0 16px 0; font-size: 18px;">Update Model Price</h3>
        <form id="modelPriceForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 4px;">Commercial MSRP ($)</label>
                    <input type="number" id="modelMsrpInput" name="msrp" class="form-control" required min="0" step="1">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 4px;">Sourcewell List Price ($)</label>
                    <input type="number" id="modelSwListInput" name="sw_list" class="form-control" required min="0" step="1">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 4px;">Sourcewell Net Contract Price ($)</label>
                    <input type="number" id="modelSwNetInput" name="sw_net" class="form-control" required min="0" step="1">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModelPriceModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Pricing</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Enhancement Price -->
<div id="enhPriceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 id="modalEnhTitle" style="margin: 0 0 16px 0; font-size: 18px;">Update Option Price</h3>
        <form id="enhPriceForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 4px;">Commercial MSRP ($)</label>
                    <input type="number" id="enhMsrpInput" name="msrp" class="form-control" required min="0" step="0.01">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 4px;">Sourcewell List Price ($)</label>
                    <input type="number" id="enhSwListInput" name="sw_list" class="form-control" required min="0" step="0.01">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 4px;">Sourcewell Net Contract Price ($)</label>
                    <input type="number" id="enhSwNetInput" name="sw_net" class="form-control" required min="0" step="0.01">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeEnhPriceModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Pricing</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModelPriceModal(model, price) {
        document.getElementById('modalModelTitle').textContent = `Update Base Price: ${model.code}`;
        document.getElementById('modelPriceForm').action = `/manager/pricing/models/${model.id}`;
        document.getElementById('modelMsrpInput').value = price ? price.msrp : 0;
        document.getElementById('modelSwListInput').value = price ? price.sw_list : 0;
        document.getElementById('modelSwNetInput').value = price ? price.sw_net : 0;
        document.getElementById('modelPriceModal').style.display = 'flex';
    }

    function closeModelPriceModal() {
        document.getElementById('modelPriceModal').style.display = 'none';
    }

    function openEnhPriceModal(enh) {
        document.getElementById('modalEnhTitle').textContent = `Update Option: ${enh.description}`;
        document.getElementById('enhPriceForm').action = `/manager/pricing/enhancements/${enh.id}`;
        document.getElementById('enhMsrpInput').value = enh.msrp;
        document.getElementById('enhSwListInput').value = enh.sw_list;
        document.getElementById('enhSwNetInput').value = enh.sw_net;
        document.getElementById('enhPriceModal').style.display = 'flex';
    }

    function closeEnhPriceModal() {
        document.getElementById('enhPriceModal').style.display = 'none';
    }
</script>
@endpush
