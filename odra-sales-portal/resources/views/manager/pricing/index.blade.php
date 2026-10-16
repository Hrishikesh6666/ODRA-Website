@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Master Pricing Management')
@section('header-title', 'Master Equipment, Options & Delivery Pricing')

@section('content')
<!-- Top Quick-Action Bar & Info Guide -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #0f172a;">Commercial &amp; Cooperative Master Pricing Console</h2>
        <p style="margin: 4px 0 0; font-size: 13.5px; color: var(--text-muted);">
            Manage base sweeper prices, option upgrades, and delivery rates for both Commercial MSRP and Cooperative Purchasing contracts (Sourcewell, H-GAC, Minnesota, BuyBoard).
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('sales.quotes.create') }}" target="_blank" class="btn-primary" style="padding: 9px 16px; font-size: 13px;">
            &#128065; Preview Live Quote Builder
        </a>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.quotation-settings.index') : route('manager.quotation-settings.index') }}" class="btn-secondary" style="padding: 9px 14px; font-size: 13px;">
            &#9998; Quotation Configuration &amp; Pricing Basis Master
        </a>
    </div>
</div>

<!-- Visual Explanation Guide: How Pricing Works on Quotes -->
<div class="card" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-left: 5px solid var(--red); padding: 20px; margin-bottom: 24px;">
    <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #1e293b; margin-bottom: 8px;">
        &#9432; How Pricing Basis Controls the Customer Quotation Sheet
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; font-size: 13px; color: #334155; line-height: 1.5;">
        <div style="background: white; border: 1px solid var(--border); border-radius: 6px; padding: 14px;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #0284c7;"></span>
                <span>Commercial MSRP Basis</span>
            </div>
            <div>When Sales selects <strong>MSRP (Commercial)</strong>:</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                &bull; MSRP column = <strong>Commercial MSRP</strong><br>
                &bull; UNIT PRICE column = <strong>Commercial MSRP</strong><br>
                &bull; DISCOUNT column = Hidden (0% default)<br>
                &bull; Freight = Standard Customer Rate
            </div>
        </div>

        <div style="background: white; border: 1px solid var(--border); border-radius: 6px; padding: 14px;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #059669;"></span>
                <span>Cooperative Contract Basis</span>
            </div>
            <div>When Sales selects <strong>Sourcewell / H-GAC / Minnesota / BuyBoard</strong>:</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                &bull; MSRP column = <strong>Coop List Price</strong><br>
                &bull; UNIT PRICE column = <strong>Coop Net Price</strong><br>
                &bull; DISCOUNT column = <strong>Automatic % Discount</strong><br>
                &bull; Freight = Municipality Rate
            </div>
        </div>

        <div style="background: white; border: 1px solid var(--border); border-radius: 6px; padding: 14px;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #d97706;"></span>
                <span>Automatic Discount Formula</span>
            </div>
            <div>Discount % is calculated dynamically:</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                <code>Discount = 1 - (Coop Net &divide; Coop List)</code><br>
                <em>Example:</em> $307,378 List with $270,493 Net = <strong style="color: #059669;">12% DISCOUNT</strong> shown to customer!
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: Options & Enhancements Master Pricing -->
<div class="card" id="enhancementsPricingSection">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;">1. Options &amp; Enhancements Master Pricing</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">
                Pricing for brooms, lighting, water flush, stainless steel hoppers, extended warranties, and dual steering conversions.
            </p>
        </div>
        <div style="font-size: 13px; color: #64748b;">
            Showing <strong>{{ count($enhancements) }}</strong> options
        </div>
    </div>

    <!-- Filter Bar for Enhancements -->
    <form method="GET" action="{{ route('manager.pricing.index') }}" style="background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; margin: 0;">Model:</label>
            <select name="enh_model" class="form-control" style="width: auto; padding: 6px 10px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">-- All Models --</option>
                @foreach ($models as $mOpt)
                    <option value="{{ $mOpt->code }}" {{ $selectedModel === $mOpt->code ? 'selected' : '' }}>{{ $mOpt->code }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 12px; font-weight: 700; color: #475569; margin: 0;">Category:</label>
            <select name="enh_category" class="form-control" style="width: auto; padding: 6px 10px; font-size: 13px;" onchange="this.form.submit()">
                <option value="">-- All Categories --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ $selectedCategory === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;">
            <input type="text" name="enh_search" class="form-control" placeholder="Search option description..." value="{{ $enhSearch }}" style="padding: 6px 10px; font-size: 13px;">
            <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 13px;">Filter</button>
            @if ($selectedModel || $selectedCategory || $enhSearch)
                <a href="{{ route('manager.pricing.index') }}" class="btn-secondary" style="padding: 6px 10px; font-size: 12px; color: #991b1b;">Clear</a>
            @endif
        </div>
    </form>

    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 520px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th style="min-width: 90px;">Model</th>
                    <th style="min-width: 110px;">Category</th>
                    <th style="min-width: 260px;">Option Description</th>
                    <th style="text-align: right; min-width: 120px;">
                        <div>Commercial MSRP</div>
                        <span style="font-size: 10px; font-weight: normal; color: #64748b;">(MSRP Basis)</span>
                    </th>
                    <th style="text-align: right; min-width: 120px;">
                        <div>Coop List Price</div>
                        <span style="font-size: 10px; font-weight: normal; color: #64748b;">(Quote MSRP col)</span>
                    </th>
                    <th style="text-align: right; min-width: 120px;">
                        <div>Coop Net Price</div>
                        <span style="font-size: 10px; font-weight: normal; color: #64748b;">(Quote Unit Price col)</span>
                    </th>
                    <th style="text-align: center; min-width: 90px;">
                        <div>Discount</div>
                    </th>
                    <th style="text-align: right; min-width: 110px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enhancements as $enh)
                    @php
                        $enhMsrp = (float) $enh->msrp;
                        $enhSwList = (float) $enh->sw_list;
                        $enhSwNet = (float) $enh->sw_net;
                        $enhDisc = ($enhSwList > 0 && $enhSwNet > 0 && $enhSwList >= $enhSwNet)
                            ? round((1 - ($enhSwNet / $enhSwList)) * 100)
                            : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong style="color: #0f172a;">{{ $enh->sweeperModel?->code ?? 'ALL' }}</strong>
                        </td>
                        <td>
                            <span style="font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 7px; border-radius: 4px; border: 1px solid var(--border);">
                                {{ $enh->category }}
                            </span>
                        </td>
                        <td>
                            <strong style="color: #1e293b; font-size: 13.5px;">{{ $enh->description }}</strong>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #0f172a;">
                            ${{ number_format($enhMsrp, 2) }}
                        </td>
                        <td style="text-align: right; color: #475569;">
                            ${{ number_format($enhSwList, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #059669;">
                            ${{ number_format($enhSwNet, 2) }}
                        </td>
                        <td style="text-align: center;">
                            @if ($enhDisc > 0)
                                <span class="badge" style="background: #ecfdf5; color: #047857; font-size: 11px;">
                                    {{ $enhDisc }}%
                                </span>
                            @else
                                <span style="color: #94a3b8; font-size: 11px;">0%</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick='openEnhPriceModal(@json($enh))'>
                                &#9998; Edit Price
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">
                            No enhancements match your filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- SECTION 2: Freight Delivery Rates Matrix -->
<div class="card" id="freightPricingSection" style="margin-top: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;">2. Delivery &amp; Transport Freight Pricing Matrix</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">
                Customer rate (applied on Commercial MSRP quotes) and Municipality rate (applied on Cooperative Contract quotes).
            </p>
        </div>
        <form method="GET" action="{{ route('manager.pricing.index') }}" style="display: flex; gap: 8px;">
            <input type="text" name="freight_search" class="form-control" placeholder="Search state or city..." value="{{ request('freight_search') }}" style="width: 220px; padding: 6px 10px; font-size: 13px;">
            <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 13px;">Search</button>
        </form>
    </div>

    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 400px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th style="min-width: 140px;">State</th>
                    <th style="min-width: 240px;">Destination (City, ST ZIP)</th>
                    <th style="text-align: right; min-width: 140px;">Customer Rate (MSRP Basis)</th>
                    <th style="text-align: right; min-width: 140px;">Municipality Rate (Coop Basis)</th>
                    <th style="text-align: right; min-width: 110px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($freightRules as $fr)
                    <tr>
                        <td><strong>{{ $fr->state }}</strong></td>
                        <td>{{ $fr->city }}</td>
                        <td style="text-align: right; font-weight: 600; color: #0f172a;">
                            ${{ number_format($fr->customer_rate, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #059669;">
                            ${{ number_format($fr->municipality_rate, 2) }}
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick='openFreightModal(@json($fr))'>
                                &#9998; Edit Rate
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($freightRules->hasPages())
        <div style="margin-top: 14px;">
            {{ $freightRules->links('pagination::custom') }}
        </div>
    @endif
</div>

<!-- ========================================== -->
<!-- MODAL 1: Edit Enhancement Price with Live Calculator -->
<!-- ========================================== -->
<div id="enhPriceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 10px; width: 100%; max-width: 540px; padding: 26px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <h3 id="modalEnhTitle" style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">Update Option Price</h3>
            <button type="button" onclick="closeEnhPriceModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form id="enhPriceForm" method="POST">
            @csrf
            @method('PUT')

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 14px;">
                    <label style="font-size: 13px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 2px;">
                        1. Commercial MSRP ($)
                    </label>
                    <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 6px;">
                        Option price on <strong>MSRP (Commercial)</strong> basis quotes.
                    </span>
                    <input type="number" id="enhMsrpInput" name="msrp" class="form-control" required min="0" step="0.01" oninput="calculateEnhPreview('msrp')">
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px;">
                    <label style="font-size: 13px; font-weight: 700; color: #166534; display: block; margin-bottom: 2px;">
                        2. Cooperative List Price ($) &mdash; <span style="font-weight: normal;">Quote MSRP column</span>
                    </label>
                    <span style="font-size: 11px; color: #166534; display: block; margin-bottom: 6px;">
                        Printed in MSRP column on Sourcewell, H-GAC, Minnesota, BuyBoard quotes.
                    </span>
                    <input type="number" id="enhSwListInput" name="sw_list" class="form-control" required min="0" step="0.01" oninput="calculateEnhPreview('list')">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px;">
                        <label style="font-size: 13px; font-weight: 700; color: #1e40af; display: block; margin-bottom: 2px;">
                            3. Contract Discount (%)
                        </label>
                        <span style="font-size: 11px; color: #1e40af; display: block; margin-bottom: 6px;">
                            Printed in <strong>DISCOUNT column</strong>.
                        </span>
                        <input type="number" id="enhDiscountInput" name="discount_percent" class="form-control" min="0" max="100" step="0.1" placeholder="e.g. 12" oninput="calculateEnhPreview('disc')">
                    </div>

                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px;">
                        <label style="font-size: 13px; font-weight: 700; color: #166534; display: block; margin-bottom: 2px;">
                            4. Coop Net Unit Price ($)
                        </label>
                        <span style="font-size: 11px; color: #166534; display: block; margin-bottom: 6px;">
                            Printed in <strong>UNIT PRICE column</strong>.
                        </span>
                        <input type="number" id="enhSwNetInput" name="sw_net" class="form-control" required min="0" step="0.01" oninput="calculateEnhPreview('net')">
                    </div>
                </div>

                <!-- Live Quote Impact Calculator Preview -->
                <div style="background: #1e293b; color: white; border-radius: 8px; padding: 14px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; font-weight: 700; margin-bottom: 6px;">
                        &#128065; Live Quote Preview Calculator
                    </div>
                    <div id="enhLivePreview" style="font-size: 13px; line-height: 1.5;">
                        <!-- Updated dynamically via JS -->
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px;">
                <button type="button" class="btn-secondary" onclick="closeEnhPriceModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">Save &amp; Update All Quotes</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: Edit Freight Delivery Rates -->
<!-- ========================================== -->
<div id="freightModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; border-radius: 10px; width: 100%; max-width: 500px; padding: 26px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <h3 id="modalFreightTitle" style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">Update Freight Rate</h3>
            <button type="button" onclick="closeFreightModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form id="freightForm" method="POST">
            @csrf
            @method('PUT')

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">
                        Customer Delivery Rate ($) &mdash; <span style="font-weight: normal;">MSRP Basis</span>
                    </label>
                    <input type="number" id="freightCustInput" name="customer_rate" class="form-control" required min="0" step="0.01">
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 14px;">
                    <label style="font-size: 12.5px; font-weight: 700; color: #166534; display: block; margin-bottom: 4px;">
                        Municipality Delivery Rate ($) &mdash; <span style="font-weight: normal;">Coop Contract Basis</span>
                    </label>
                    <input type="number" id="freightMuniInput" name="municipality_rate" class="form-control" required min="0" step="0.01">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px;">
                <button type="button" class="btn-secondary" onclick="closeFreightModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">Save Freight Rates</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function fmtDollar(n) {
        return '$' + Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: 2 });
    }


    // Modal 2: Enhancement Price
    function openEnhPriceModal(enh) {
        document.getElementById('modalEnhTitle').textContent = `Update Option: ${enh.description}`;
        document.getElementById('enhPriceForm').action = `/manager/pricing/enhancements/${enh.id}`;
        const msrp = parseFloat(enh.msrp) || 0;
        const list = parseFloat(enh.sw_list) || msrp;
        const net = parseFloat(enh.sw_net) || list;
        let disc = 0;
        if (enh.discount_percent !== undefined && enh.discount_percent !== null && enh.discount_percent !== '') {
            disc = parseFloat(enh.discount_percent) || 0;
        } else if (list > 0 && net >= 0 && list >= net) {
            disc = Math.round((1 - (net / list)) * 1000) / 10;
        }

        document.getElementById('enhMsrpInput').value = msrp.toFixed(2);
        document.getElementById('enhSwListInput').value = list.toFixed(2);
        document.getElementById('enhDiscountInput').value = disc > 0 ? disc : '';
        document.getElementById('enhSwNetInput').value = net.toFixed(2);
        calculateEnhPreview('init');
        document.getElementById('enhPriceModal').style.display = 'flex';
    }

    function closeEnhPriceModal() {
        document.getElementById('enhPriceModal').style.display = 'none';
    }

    function calculateEnhPreview(source) {
        const msrpEl = document.getElementById('enhMsrpInput');
        const listEl = document.getElementById('enhSwListInput');
        const discEl = document.getElementById('enhDiscountInput');
        const netEl = document.getElementById('enhSwNetInput');

        let msrp = parseFloat(msrpEl.value) || 0;
        let list = parseFloat(listEl.value) || 0;

        if (source === 'msrp' && (!list || list === 0)) {
            list = msrp;
            listEl.value = list.toFixed(2);
        }

        if (source === 'disc' || source === 'msrp') {
            const disc = parseFloat(discEl.value) || 0;
            const net = list > 0 ? Math.round(list * (1 - (disc / 100)) * 100) / 100 : 0;
            netEl.value = net.toFixed(2);
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
                netEl.value = net.toFixed(2);
            } else {
                const net = parseFloat(netEl.value) || 0;
                if (list > 0 && net > 0 && list >= net) {
                    discEl.value = Math.round((1 - (net / list)) * 1000) / 10;
                }
            }
        }

        const currentNet = parseFloat(netEl.value) || 0;
        const currentList = parseFloat(listEl.value) || 0;
        const currentDisc = (currentList > 0 && currentNet >= 0 && currentList >= currentNet)
            ? Math.round((1 - (currentNet / currentList)) * 1000) / 10
            : (parseFloat(discEl.value) || 0);

        document.getElementById('enhLivePreview').innerHTML = `
            <div>&bull; <strong>MSRP Basis:</strong> Ext. Price = <span style="color:#38bdf8;font-weight:700;">${fmtDollar(msrp)}</span></div>
            <div>&bull; <strong>Coop Contracts:</strong> Quote MSRP: <span style="color:#cbd5e1;">${fmtDollar(currentList)}</span> &rarr; Unit Price: <span style="color:#4ade80;font-weight:700;">${fmtDollar(currentNet)}</span></div>
            <div style="margin-top:4px;">&bull; <strong>Quotation DISCOUNT Column:</strong> <span style="background:#059669;color:white;padding:2px 8px;border-radius:4px;font-weight:bold;">${currentDisc}% DISCOUNT</span></div>
        `;
    }

    // Modal 3: Freight Rate
    function openFreightModal(fr) {
        document.getElementById('modalFreightTitle').textContent = `Update Freight: ${fr.city}`;
        document.getElementById('freightForm').action = `/manager/pricing/freight/${fr.id}`;
        document.getElementById('freightCustInput').value = fr.customer_rate;
        document.getElementById('freightMuniInput').value = fr.municipality_rate;
        document.getElementById('freightModal').style.display = 'flex';
    }

    function closeFreightModal() {
        document.getElementById('freightModal').style.display = 'none';
    }

    // Close modals on Escape key or backdrop click
    window.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeEnhPriceModal();
            closeFreightModal();
        }
    });
</script>
@endpush
