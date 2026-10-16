@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Quotation Configuration & Pricing Basis Master')
@section('header-title', 'Quotation Configuration & Pricing Basis Master')

@push('styles')
<style>
.config-header-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
}
.hierarchy-bar {
    display: grid;
    grid-template-columns: 1fr 1.6fr auto;
    gap: 20px;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 18px 20px;
    margin-top: 14px;
}
.hierarchy-step {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.step-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #475569;
}
.step-select {
    width: 100%;
    padding: 9px 12px;
    font-size: 14px;
    font-weight: 600;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    background-color: white;
    color: #0f172a;
    transition: border-color 0.15s;
}
.step-select:focus {
    border-color: var(--red);
    outline: none;
    box-shadow: 0 0 0 3px rgba(172, 37, 41, 0.15);
}
.pricing-table-wrap {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    border: 1px solid var(--border);
}
.pricing-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
.pricing-table th {
    background: #f1f5f9;
    padding: 12px 14px;
    font-weight: 700;
    color: #334155;
    border-bottom: 1.5px solid #cbd5e1;
    white-space: nowrap;
}
.pricing-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}
.pricing-table tbody tr:hover {
    background: #fafafa;
}
.table-input {
    width: 100%;
    padding: 7px 10px;
    font-size: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    box-sizing: border-box;
}
.table-input:focus {
    border-color: var(--red);
    outline: none;
}
.table-input-currency {
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    text-align: right;
}
.calc-price-badge {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 700;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
    display: inline-block;
    white-space: nowrap;
}
.logo-preview-box {
    width: 36px;
    height: 36px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    padding: 2px;
    flex-shrink: 0;
}
.logo-preview-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.btn-outline-red {
    background: white;
    color: #b91c1c;
    border: 1px solid #f87171;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-outline-red:hover {
    background: #fee2e2;
}
.feature-item-card {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 14px;
    margin-bottom: 10px;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(2px);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-card {
    background: white;
    border-radius: 10px;
    max-width: 580px;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
    overflow: hidden;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.modal-body {
    padding: 20px;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.pill-tab {
    padding: 7px 16px;
    font-size: 12.5px;
    font-weight: 600;
    border-radius: 20px;
    cursor: pointer;
    border: 1px solid #cbd5e1;
    background: white;
    color: #475569;
    transition: all 0.15s ease;
}
.pill-tab.active {
    background: var(--red);
    color: white;
    border-color: var(--red);
}
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div>
        <p style="color: var(--text-muted); margin: 0; font-size: 14px;">
            Simplified, model-based quotation hierarchy: <strong>Model &rarr; Chassis Configuration &rarr; Pricing Basis</strong>.
            Configure dedicated prices, discounts, chassis descriptions, logos, and standard features.
        </p>
        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-top: 8px;">
            Quotation Configuration Scope:
            @if($scope === 'GLOBAL')
                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-left: 4px;">Global Default Template</span>
                <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; margin-left: 6px; padding: 2px 8px; border-radius: 4px;">Applies to all models unless overridden</span>
            @else
                <span style="font-size: 13px; font-weight: 600; color: #1e293b; margin-left: 4px;">
                    Model Override: <strong style="color: var(--red);">{{ $scope }}</strong>
                </span>
                @if(!empty($settings['is_custom']))
                    <span class="badge" style="background: #0284c7; color: white; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; margin-left: 6px;">
                        &bull; Custom Model Overrides Active
                    </span>
                @else
                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 4px; margin-left: 6px;">
                        Currently Inheriting Global Defaults
                    </span>
                @endif
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('sales.quotes.create', ['model' => $selectedModelCode]) }}" target="_blank" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <span>&#128065;</span> Open Live Quote Generator
        </a>
        <form action="{{ route('admin.quotation-settings.reset') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Reset all quotation template sections and pricing basis rules to factory defaults?');">
            @csrf
            <button type="submit" class="btn-secondary" style="color: #991b1b;">
                Reset Factory Defaults
            </button>
        </form>
    </div>
</div>

<!-- SECTION 1: Model Selection & Chassis Configuration Hierarchy -->
<div class="config-header-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
        <div>
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>&#128667;</span> 1. Product Configuration Hierarchy
            </h3>
            <span style="font-size: 12.5px; color: #64748b;">
                Select a Sweeper Model and Chassis / Discharge Configuration to manage its dedicated prices, description, and features.
            </span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn-secondary" onclick="openAddModelModal()" style="font-size: 12.5px; padding: 7px 14px;">
                + Add Model
            </button>
            <button type="button" class="btn-secondary" onclick="openAddConfigModal()" style="font-size: 12.5px; padding: 7px 14px;">
                + Add Configuration
            </button>
        </div>
    </div>

    <div class="hierarchy-bar">
        <!-- Level A: Model -->
        <div class="hierarchy-step">
            <span class="step-badge">
                <span style="color: var(--red); font-size: 14px;">A.</span> Model Selection
            </span>
            <div style="display: flex; gap: 8px; align-items: center;">
                <select id="modelSelector" class="step-select" onchange="onModelChanged(this.value)">
                    @foreach($models as $m)
                        <option value="{{ $m->code }}" {{ $m->code === $selectedModelCode ? 'selected' : '' }}>
                            {{ $m->code }} &mdash; {{ $m->category ?? 'Mechanical Broom Sweeper' }}
                        </option>
                    @endforeach
                </select>
                <a id="editModelLink" href="{{ route('admin.models.edit', $models->firstWhere('code', $selectedModelCode)?->id ?? 1) }}" class="btn-secondary" style="padding: 9px 12px; white-space: nowrap;" title="Edit Model Info">
                    Edit &rarr;
                </a>
            </div>
        </div>

        <!-- Level B: Chassis / Discharge Configuration -->
        <div class="hierarchy-step">
            <span class="step-badge">
                <span style="color: var(--red); font-size: 14px;">B.</span> Chassis / Discharge Configuration
            </span>
            <select id="chassisSelector" class="step-select" onchange="onChassisChanged(this.value)">
                <!-- Dynamically populated via JavaScript -->
            </select>
        </div>

        <!-- Status Summary Badge -->
        <div style="text-align: right; min-width: 140px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b;">Active Context</div>
            <div id="activeContextBadge" style="font-size: 13px; font-weight: 700; color: var(--red); margin-top: 2px;">
                {{ $selectedModelCode }}
            </div>
        </div>
    </div>
</div>

<form id="quotationConfigForm" action="{{ route('admin.quotation-settings.update') }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="scope" id="formScope" value="{{ $selectedModelCode }}">
    <input type="hidden" name="selected_model" id="formSelectedModel" value="{{ $selectedModelCode }}">
    <input type="hidden" name="selected_chassis" id="formSelectedChassis" value="">
    <input type="hidden" name="configurations_json" id="formConfigurationsJson" value="">

    <!-- SECTION 2: Independent Pricing and Discount Table -->
    <div class="card" id="sec-pricing-table" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>&#128178;</span> 2. Pricing Basis &amp; Discounts for <span id="pricingSectionTitle" style="color: var(--red);">{{ $selectedModelCode }}</span>
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Each pricing basis has its own independent MSRP, discount %, calculated price, contract caption, logo, and municipality freight rule.
                </span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-secondary" onclick="addPricingBasisRow()" style="font-size: 12px; padding: 7px 14px;">
                    + Add Pricing Basis
                </button>
            </div>
        </div>

        <div class="pricing-table-wrap">
            <table class="pricing-table">
                <thead>
                    <tr>
                        <th style="min-width: 160px;">Pricing Basis</th>
                        <th style="min-width: 110px;">MSRP ($)</th>
                        <th style="min-width: 90px;">Discount (%)</th>
                        <th style="min-width: 130px;">Calculated Price</th>
                        <th style="min-width: 170px;">Subtotal Label</th>
                        <th style="min-width: 220px;">Contract Caption / Terms</th>
                        <th style="min-width: 190px;">Logo</th>
                        <th style="min-width: 80px; text-align: center;">Muni Freight</th>
                        <th style="width: 50px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="pricingTableBody">
                    <!-- Rows dynamically populated by renderPricingTable() -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 3: Dedicated Product Description Editor for Selected Configuration -->
    <div class="card" id="sec-product-desc" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>&#128221;</span> 3. Product Description for Selected Chassis Configuration
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Controls the base unit description printed on customer quotations for <strong id="descConfigBadge" style="color: var(--red);"></strong>. Changing this does not overwrite other chassis configurations.
                </span>
            </div>
            <span class="badge badge-active">Chassis Specific</span>
        </div>

        <div class="form-group" style="margin-bottom: 6px;">
            <textarea id="chassisDescriptionTextarea" name="chassis_description" class="form-control" rows="4" style="line-height: 1.5; font-size: 13.5px;" oninput="onDescriptionEdited(this.value)"></textarea>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: #64748b;">
            <span>This description flows directly into Quotation Page preview and generated PDF.</span>
            <span id="descWordCount">0 characters</span>
        </div>
    </div>

    <!-- SECTION 4: Standard Features per Pricing Basis -->
    <div class="card" id="sec-standard-features" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>&#10003;</span> 4. Standard Features per Pricing Basis (Standard Features Section for {{ $scope === 'GLOBAL' ? 'Global Default' : $scope }})
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Standard features printed below the pricing table. Each pricing basis can have its own customized standard features.
                </span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="btn-secondary" onclick="copyFeaturesFromCommercial()" style="font-size: 12px; padding: 7px 14px; font-weight: 600; color: #0369a1;">
                    &#128203; Copy from Commercial
                </button>
                <button type="button" class="btn-secondary" onclick="addStandardFeatureItem()" style="font-size: 12px; padding: 7px 14px;">
                    + Add Feature
                </button>
            </div>
        </div>

        <!-- Basis Selector Tabs for Standard Features -->
        <div style="margin-bottom: 16px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-bottom: 8px;">
                Select Pricing Basis to Edit Standard Features:
            </div>
            <div id="featureBasisPills" style="display: flex; gap: 8px; flex-wrap: wrap;">
                <!-- Pills populated dynamically -->
            </div>
        </div>

        <div id="featureItemsContainer">
            <!-- Feature items populated dynamically -->
        </div>
    </div>

    <!-- SECTION 5: Company Header & Global Legal Terms (Collapsible) -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleLegalSection()">
            <div>
                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>&#9878;</span> 5. Company Header &amp; Legal Terms Disclaimers (1. Quotation Header, Address &amp; Table Labels)
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Global company header, deposit requirements, transport estimates, and budgetary validity terms.
                </span>
            </div>
            <span id="legalToggleIcon" style="font-size: 18px; color: #64748b;">&#9660;</span>
        </div>

        <div id="legalSectionContent" style="display: none; margin-top: 18px; border-top: 1px solid var(--border); padding-top: 16px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group" style="margin: 0;">
                    <label>Company Name</label>
                    <input type="text" name="header[company_name]" class="form-control" value="{{ $settings['header']['company_name'] ?? 'ODRA Road Sweepers' }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Document Title Badge</label>
                    <input type="text" name="header[quote_title]" class="form-control" value="{{ $settings['header']['quote_title'] ?? 'QUOTATION' }}">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div class="form-group" style="margin: 0;">
                    <label>Address Line 1</label>
                    <input type="text" name="header[address_line_1]" class="form-control" value="{{ $settings['header']['address_line_1'] ?? '705 South 48th Street, Grand Forks, ND 58201' }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Contact Line 2</label>
                    <input type="text" name="header[address_line_2]" class="form-control" value="{{ $settings['header']['address_line_2'] ?? 't: 844-888-6372 • e: info@odrasweeper.com • odrasweeper.com' }}">
                </div>
            </div>
            <div class="form-group">
                <label>Delivery Transport Disclaimer</label>
                <textarea name="legal[legal_p1]" class="form-control" rows="2">{{ $settings['legal']['legal_p1'] ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label>Taxes, Registration &amp; Titling Responsibility</label>
                <textarea name="legal[legal_p2]" class="form-control" rows="2">{{ $settings['legal']['legal_p2'] ?? '' }}</textarea>
            </div>
            <div class="form-group">
                <label>Deposit &amp; Payment Terms</label>
                <textarea name="legal[legal_p3]" class="form-control" rows="2">{{ $settings['legal']['legal_p3'] ?? '' }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Budgetary Validity Disclaimer</label>
                <textarea name="legal[legal_p4]" class="form-control" rows="2">{{ $settings['legal']['legal_p4'] ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <!-- Sticky Bottom Save Bar -->
    <div style="display: flex; gap: 12px; position: sticky; bottom: 16px; background: white; padding: 16px 24px; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 20; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="submit" class="btn-primary" style="padding: 11px 26px; font-size: 15px; font-weight: 700;">
                Save Quotation Configuration
            </button>
            <span id="saveStatusText" style="font-size: 12.5px; color: #64748b;">
                Changes take effect immediately across all Quote Builder sessions and PDF generation.
            </span>
        </div>
        <a href="{{ route('sales.quotes.create', ['model' => $selectedModelCode]) }}" target="_blank" class="btn-secondary" style="display: inline-flex; align-items: center; text-decoration: none;">
            Preview Live Quotation Page &rarr;
        </a>
    </div>
</form>

<!-- MODAL: Upload New Cooperative Logo -->
<div id="uploadLogoModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Upload Cooperative / Program Logo</h4>
            <button type="button" class="btn-secondary" onclick="closeUploadLogoModal()" style="padding: 4px 8px; font-size: 14px;">&times;</button>
        </div>
        <form id="uploadLogoForm" onsubmit="handleLogoUpload(event)">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Logo / Program Display Name</label>
                    <input type="text" id="logoDisplayName" class="form-control" placeholder="e.g. NASPO ValuePoint" required>
                </div>
                <div class="form-group">
                    <label>Select Logo File (PNG, JPG, SVG, WebP &mdash; transparent background recommended)</label>
                    <input type="file" id="logoFileInput" class="form-control" accept="image/png,image/jpeg,image/svg+xml,image/webp" required onchange="previewSelectedLogoFile(this)">
                </div>
                <div id="logoUploadPreviewContainer" style="display: none; margin-top: 14px; text-align: center; padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 6px;">Live Logo Preview</div>
                    <img id="logoUploadPreviewImg" src="" style="max-height: 50px; max-width: 280px; object-fit: contain; margin: 0 auto; display: block;" alt="Uploaded Logo Preview">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeUploadLogoModal()">Cancel</button>
                <button type="submit" id="uploadLogoSubmitBtn" class="btn-primary">Upload &amp; Apply Logo</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add New Model -->
<div id="addModelModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Add New Sweeper Model</h4>
            <button type="button" class="btn-secondary" onclick="closeAddModelModal()" style="padding: 4px 8px; font-size: 14px;">&times;</button>
        </div>
        <form action="{{ route('admin.models.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px;">
                    <div class="form-group" style="margin: 0;">
                        <label>Model Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. MT6H" required style="text-transform: uppercase;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control" value="MECHANICAL BROOM SWEEPER" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px;">
                    <div class="form-group" style="margin: 0;">
                        <label>Commercial MSRP ($)</label>
                        <input type="number" name="msrp" class="form-control" placeholder="e.g. 295000" required step="1">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>Sourcewell List ($)</label>
                        <input type="number" name="sw_list" class="form-control" placeholder="e.g. 345000" required step="1">
                    </div>
                </div>
                <div class="form-group">
                    <label>Model Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter standard sweeper model description..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAddModelModal()">Cancel</button>
                <button type="submit" class="btn-primary">Create Model &rarr;</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add New Configuration -->
<div id="addConfigModal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <div class="modal-header">
            <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">Add Chassis / Discharge Configuration</h4>
            <button type="button" class="btn-secondary" onclick="closeAddConfigModal()" style="padding: 4px 8px; font-size: 14px;">&times;</button>
        </div>
        <form id="addConfigForm" onsubmit="handleAddConfiguration(event)">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" id="addConfigModelCode" class="form-control" readonly style="background: #f1f5f9; font-weight: 700;">
                </div>
                <div class="form-group">
                    <label>Full Chassis Configuration Label</label>
                    <input type="text" id="addConfigLabel" class="form-control" placeholder="e.g. MT4H - Isuzu NRR cabover, Class 5 - Dual Steer, Right Side Dump" required>
                </div>
                <div class="form-group">
                    <label>Short Label (for Quotation Table &amp; PDF)</label>
                    <input type="text" id="addConfigShortLabel" class="form-control" placeholder="e.g. MT4H Isuzu Cabover - Dual Steer RHD">
                </div>
                <div class="form-group">
                    <label>Chassis Product Description</label>
                    <textarea id="addConfigDescription" class="form-control" rows="3" placeholder="Dedicated description for this chassis configuration..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeAddConfigModal()">Cancel</button>
                <button type="submit" class="btn-primary">+ Add Configuration</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Server injected configuration database
const MODELS_LIST = @json($modelsList ?? []);

const CONFIG_STORE = @json($allConfigurationsData);
const AVAILABLE_LOGOS = @json($availableLogos);

let currentModel = "{{ $selectedModelCode }}";
let currentChassis = "";
let currentFeatureBasis = "Commercial";

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initHierarchy();
});

function initHierarchy() {
    populateChassisDropdown(currentModel);
    renderActiveConfiguration();
}

function onModelChanged(modelCode) {
    saveCurrentFormToStore();
    currentModel = modelCode;
    document.getElementById('formScope').value = currentModel;
    document.getElementById('formSelectedModel').value = currentModel;
    document.getElementById('pricingSectionTitle').textContent = currentModel;
    document.getElementById('activeContextBadge').textContent = currentModel;

    // Update edit link
    const modelObj = MODELS_LIST.find(m => m.code === currentModel);
    if (modelObj) {
        document.getElementById('editModelLink').href = `/admin/models/${modelObj.id}/edit`;
    }

    populateChassisDropdown(currentModel);
    renderActiveConfiguration();
}

function populateChassisDropdown(modelCode) {
    const sel = document.getElementById('chassisSelector');
    sel.innerHTML = '';

    const modelObj = MODELS_LIST.find(m => m.code === modelCode);
    let configs = (modelObj && modelObj.configurations && modelObj.configurations.length)
        ? modelObj.configurations
        : (CONFIG_STORE[modelCode] ? Object.keys(CONFIG_STORE[modelCode]) : []);

    if (!configs.length) {
        configs = [`${modelCode} - Standard Commercial Truck Chassis Configuration`];
    }

    configs.forEach(cfgLabel => {
        const opt = document.createElement('option');
        opt.value = cfgLabel;
        opt.textContent = cfgLabel;
        sel.appendChild(opt);
    });

    currentChassis = configs[0] || '';
    sel.value = currentChassis;
    document.getElementById('formSelectedChassis').value = currentChassis;
}

function onChassisChanged(chassisLabel) {
    saveCurrentFormToStore();
    currentChassis = chassisLabel;
    document.getElementById('formSelectedChassis').value = currentChassis;
    renderActiveConfiguration();
}

function getActiveConfigData() {
    if (!CONFIG_STORE[currentModel]) {
        CONFIG_STORE[currentModel] = {};
    }
    if (!CONFIG_STORE[currentModel][currentChassis]) {
        // Fallback initialization
        CONFIG_STORE[currentModel][currentChassis] = {
            label: currentChassis,
            description: getDefaultDescFallback(currentModel, currentChassis),
            pricing_bases: {},
            pricing_bases_list: []
        };
    }
    return CONFIG_STORE[currentModel][currentChassis];
}

function renderActiveConfiguration() {
    const data = getActiveConfigData();

    // 1. Update Chassis Badge
    document.getElementById('descConfigBadge').textContent = currentChassis;

    // 2. Update Product Description
    const descTextarea = document.getElementById('chassisDescriptionTextarea');
    descTextarea.value = data.description || '';
    updateWordCount(descTextarea.value);

    // 3. Update Pricing Table
    renderPricingTable(data.pricing_bases_list || []);

    // 4. Update Standard Features
    renderFeatureBasisPills(data.pricing_bases_list || []);
    renderFeatureItems();
}

function updateWordCount(text) {
    const el = document.getElementById('descWordCount');
    if (el) {
        el.textContent = `${(text || '').length} characters`;
    }
}

function onDescriptionEdited(val) {
    updateWordCount(val);
    const data = getActiveConfigData();
    data.description = val;
}

/* ================= PRICING TABLE LOGIC ================= */
function renderPricingTable(basesList) {
    const tbody = document.getElementById('pricingTableBody');
    tbody.innerHTML = '';

    if (!basesList || !basesList.length) {
        // Ensure standard initial rows if empty
        basesList = getDefaultInitialBases(currentModel);
        getActiveConfigData().pricing_bases_list = basesList;
    }

    basesList.forEach((basis, idx) => {
        const tr = document.createElement('tr');
        tr.dataset.idx = idx;

        const isCoop = basis.price_tier === 'sw_net' || !!basis.is_coop;
        const msrp = Number(basis.msrp) || 0;
        const disc = Number(basis.discount_percent) || 0;
        const calcPrice = basis.calculated_price && Number(basis.calculated_price) > 0
            ? Number(basis.calculated_price)
            : Math.round(msrp * (1 - (disc / 100)));

        const logoKey = basis.logo_key || 'none';
        const logoUrl = AVAILABLE_LOGOS[logoKey] ? AVAILABLE_LOGOS[logoKey].url : '';

        tr.innerHTML = `
            <td>
                <input type="text" name="pricing_bases[${idx}][label]" class="table-input" value="${escapeHtml(basis.label || basis.name || basis.key)}" required style="font-weight:700;" oninput="updateBasisField(${idx}, 'label', this.value)">
                <input type="hidden" name="pricing_bases[${idx}][key]" value="${escapeHtml(basis.key || basis.label)}">
                <input type="hidden" name="pricing_bases[${idx}][price_tier]" value="${isCoop ? 'sw_net' : 'msrp'}">
            </td>
            <td>
                <input type="number" name="pricing_bases[${idx}][msrp]" class="table-input table-input-currency" value="${msrp}" min="0" step="1" oninput="onMsrpOrDiscChange(${idx})" required>
            </td>
            <td>
                <input type="number" name="pricing_bases[${idx}][discount_percent]" class="table-input table-input-currency" value="${disc}" min="0" max="100" step="0.1" oninput="onMsrpOrDiscChange(${idx})">
            </td>
            <td>
                <div style="display:flex; align-items:center; gap:6px;">
                    <span class="calc-price-badge" id="calcPriceBadge_${idx}">$${calcPrice.toLocaleString()}</span>
                    <input type="hidden" name="pricing_bases[${idx}][calculated_price]" id="calcPriceInput_${idx}" value="${calcPrice}">
                </div>
            </td>
            <td>
                <input type="text" name="pricing_bases[${idx}][subtotal_label]" class="table-input" value="${escapeHtml(basis.subtotal_label || '')}" required oninput="updateBasisField(${idx}, 'subtotal_label', this.value)">
            </td>
            <td>
                <input type="text" name="pricing_bases[${idx}][caption]" class="table-input" value="${escapeHtml(basis.caption || basis.description_template || '')}" placeholder="e.g. Referenced under Sourcewell #062425-ODRA" oninput="updateBasisField(${idx}, 'caption', this.value)">
            </td>
            <td>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div class="logo-preview-box" id="logoPreviewBox_${idx}">
                        ${logoUrl ? `<img src="${logoUrl}" alt="Logo">` : '<span style="font-size:10px;color:#94a3b8;">None</span>'}
                    </div>
                    <select name="pricing_bases[${idx}][logo_key]" class="table-input logo-select" onchange="onLogoSelectChange(${idx}, this.value)" style="flex:1;">
                        ${buildLogoOptionsHtml(logoKey)}
                    </select>
                    <button type="button" class="btn-secondary" onclick="openUploadLogoModalForBasis(${idx})" style="padding:6px 9px;" title="Upload Logo">&#8679;</button>
                </div>
            </td>
            <td style="text-align:center;">
                <input type="checkbox" name="pricing_bases[${idx}][use_municipality_freight]" value="1" ${basis.use_municipality_freight ? 'checked' : ''} onchange="updateBasisField(${idx}, 'use_municipality_freight', this.checked)">
            </td>
            <td style="text-align:center;">
                <button type="button" class="btn-outline-red" onclick="removePricingBasisRow(${idx})" title="Remove Basis">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
    });
}

function buildLogoOptionsHtml(selectedKey) {
    let html = '';
    for (const [k, obj] of Object.entries(AVAILABLE_LOGOS)) {
        html += `<option value="${k}" ${k === selectedKey ? 'selected' : ''}>${escapeHtml(obj.label)}</option>`;
    }
    return html;
}

function onMsrpOrDiscChange(idx) {
    const tr = document.querySelector(`tr[data-idx="${idx}"]`);
    if (!tr) return;

    const msrpInput = tr.querySelector(`input[name="pricing_bases[${idx}][msrp]"]`);
    const discInput = tr.querySelector(`input[name="pricing_bases[${idx}][discount_percent]"]`);
    const calcBadge = document.getElementById(`calcPriceBadge_${idx}`);
    const calcInput = document.getElementById(`calcPriceInput_${idx}`);

    const msrp = parseFloat(msrpInput.value) || 0;
    const disc = parseFloat(discInput.value) || 0;
    const calc = Math.round(msrp * (1 - (disc / 100)));

    if (calcBadge) calcBadge.textContent = `$${calc.toLocaleString()}`;
    if (calcInput) calcInput.value = calc;

    const data = getActiveConfigData();
    if (data.pricing_bases_list && data.pricing_bases_list[idx]) {
        data.pricing_bases_list[idx].msrp = msrp;
        data.pricing_bases_list[idx].discount_percent = disc;
        data.pricing_bases_list[idx].calculated_price = calc;
    }
}

function updateBasisField(idx, field, val) {
    const data = getActiveConfigData();
    if (data.pricing_bases_list && data.pricing_bases_list[idx]) {
        data.pricing_bases_list[idx][field] = val;
    }
}

function onLogoSelectChange(idx, logoKey) {
    updateBasisField(idx, 'logo_key', logoKey);
    const box = document.getElementById(`logoPreviewBox_${idx}`);
    if (box) {
        const logoUrl = AVAILABLE_LOGOS[logoKey] ? AVAILABLE_LOGOS[logoKey].url : '';
        box.innerHTML = logoUrl ? `<img src="${logoUrl}" alt="Logo">` : '<span style="font-size:10px;color:#94a3b8;">None</span>';
    }
}

function addPricingBasisRow() {
    const data = getActiveConfigData();
    if (!data.pricing_bases_list) data.pricing_bases_list = [];

    const defaultMsrp = currentModel === 'MS5H' ? 411533 : 307378;
    const newBasis = {
        key: `Contract_${Date.now()}`,
        name: 'New Cooperative Contract',
        label: 'New Cooperative Contract',
        price_tier: 'sw_net',
        msrp: defaultMsrp,
        discount_percent: 12.0,
        calculated_price: Math.round(defaultMsrp * (1 - 0.12)),
        subtotal_label: 'Contract Price',
        caption: 'Referenced under Contract # ...',
        logo_key: 'none',
        use_municipality_freight: true,
        is_active: true,
        standard_features: getDefaultStandardFeatures(currentModel)
    };

    data.pricing_bases_list.push(newBasis);
    renderPricingTable(data.pricing_bases_list);
    renderFeatureBasisPills(data.pricing_bases_list);
}

function removePricingBasisRow(idx) {
    const data = getActiveConfigData();
    if (!data.pricing_bases_list || data.pricing_bases_list.length <= 1) {
        alert('At least one pricing basis must be configured.');
        return;
    }
    data.pricing_bases_list.splice(idx, 1);
    renderPricingTable(data.pricing_bases_list);
    renderFeatureBasisPills(data.pricing_bases_list);
    renderFeatureItems();
}

/* ================= STANDARD FEATURES LOGIC ================= */
function renderFeatureBasisPills(basesList) {
    const container = document.getElementById('featureBasisPills');
    container.innerHTML = '';

    if (!basesList.length) return;

    // Check if currentFeatureBasis is still in list
    const exists = basesList.some(b => (b.label || b.name || b.key) === currentFeatureBasis);
    if (!exists) {
        currentFeatureBasis = basesList[0].label || basesList[0].name || basesList[0].key;
    }

    basesList.forEach(b => {
        const name = b.label || b.name || b.key;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `pill-tab ${name === currentFeatureBasis ? 'active' : ''}`;
        btn.textContent = name;
        btn.onclick = () => {
            currentFeatureBasis = name;
            renderFeatureBasisPills(basesList);
            renderFeatureItems();
        };
        container.appendChild(btn);
    });
}

function renderFeatureItems() {
    const container = document.getElementById('featureItemsContainer');
    container.innerHTML = '';

    const data = getActiveConfigData();
    const basis = (data.pricing_bases_list || []).find(b => (b.label || b.name || b.key) === currentFeatureBasis);

    if (!basis) return;

    if (!basis.standard_features || !Array.isArray(basis.standard_features)) {
        basis.standard_features = getDefaultStandardFeatures(currentModel);
    }

    basis.standard_features.forEach((feat, fIdx) => {
        const div = document.createElement('div');
        div.className = 'feature-item-card';
        div.innerHTML = `
            <span style="font-weight:700; color:#64748b; font-size:12px; margin-top:6px;">#${fIdx + 1}</span>
            <textarea class="form-control" rows="2" style="flex:1; font-size:13px;" oninput="onFeatureEdited(${fIdx}, this.value)">${escapeHtml(feat)}</textarea>
            <button type="button" class="btn-outline-red" onclick="removeFeatureItem(${fIdx})" title="Remove Feature">&times;</button>
        `;
        container.appendChild(div);
    });
}

function onFeatureEdited(fIdx, val) {
    const data = getActiveConfigData();
    const basis = (data.pricing_bases_list || []).find(b => (b.label || b.name || b.key) === currentFeatureBasis);
    if (basis && basis.standard_features) {
        basis.standard_features[fIdx] = val;
    }
}

function addStandardFeatureItem() {
    const data = getActiveConfigData();
    const basis = (data.pricing_bases_list || []).find(b => (b.label || b.name || b.key) === currentFeatureBasis);
    if (!basis) return;
    if (!basis.standard_features) basis.standard_features = [];
    basis.standard_features.push("New Standard Feature paragraph (HTML <b> tags supported)...");
    renderFeatureItems();
}

function removeFeatureItem(fIdx) {
    const data = getActiveConfigData();
    const basis = (data.pricing_bases_list || []).find(b => (b.label || b.name || b.key) === currentFeatureBasis);
    if (basis && basis.standard_features) {
        basis.standard_features.splice(fIdx, 1);
        renderFeatureItems();
    }
}

function copyFeaturesFromCommercial() {
    const data = getActiveConfigData();
    const commercialBasis = (data.pricing_bases_list || []).find(b =>
        (b.label || b.key || '').toLowerCase().includes('commercial') ||
        (b.label || b.key || '').toLowerCase().includes('msrp')
    );

    if (!commercialBasis || !commercialBasis.standard_features || !commercialBasis.standard_features.length) {
        alert('No Commercial features found to copy.');
        return;
    }

    const currentBasis = (data.pricing_bases_list || []).find(b => (b.label || b.name || b.key) === currentFeatureBasis);
    if (!currentBasis) return;

    currentBasis.standard_features = JSON.parse(JSON.stringify(commercialBasis.standard_features));
    renderFeatureItems();
    showToast(`Standard features copied from Commercial to ${currentFeatureBasis}!`);
}

/* ================= MODALS & ACTIONS ================= */
let targetLogoUploadIdx = null;

function openUploadLogoModalForBasis(idx) {
    targetLogoUploadIdx = idx;
    document.getElementById('logoDisplayName').value = '';
    document.getElementById('logoFileInput').value = '';
    document.getElementById('logoUploadPreviewContainer').style.display = 'none';
    document.getElementById('uploadLogoModal').style.display = 'flex';
}

function closeUploadLogoModal() {
    document.getElementById('uploadLogoModal').style.display = 'none';
    targetLogoUploadIdx = null;
}

function previewSelectedLogoFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logoUploadPreviewImg').src = e.target.result;
            document.getElementById('logoUploadPreviewContainer').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

async function handleLogoUpload(e) {
    e.preventDefault();
    const fileInput = document.getElementById('logoFileInput');
    const nameInput = document.getElementById('logoDisplayName');

    if (!fileInput.files || !fileInput.files[0]) {
        alert('Please choose an image file.');
        return;
    }

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('logo', fileInput.files[0]);
    formData.append('name', nameInput.value);

    const submitBtn = document.getElementById('uploadLogoSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Uploading...';

    try {
        const resp = await fetch('{{ route("admin.quotation-settings.upload-logo") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const res = await resp.json();
        if (res.success) {
            // Register logo in local AVAILABLE_LOGOS map
            AVAILABLE_LOGOS[res.key] = {
                key: res.key,
                label: res.label,
                url: res.url
            };

            // Update all logo selects
            document.querySelectorAll('.logo-select').forEach((sel, sIdx) => {
                sel.innerHTML = buildLogoOptionsHtml(sIdx === targetLogoUploadIdx ? res.key : sel.value);
            });

            if (targetLogoUploadIdx !== null) {
                onLogoSelectChange(targetLogoUploadIdx, res.key);
            }

            closeUploadLogoModal();
            showToast(`Logo '${res.label}' uploaded and applied!`);
        } else {
            alert(res.message || 'Error uploading logo.');
        }
    } catch (err) {
        alert('Upload failed: ' + err.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Upload & Apply Logo';
    }
}

function openAddModelModal() {
    document.getElementById('addModelModal').style.display = 'flex';
}
function closeAddModelModal() {
    document.getElementById('addModelModal').style.display = 'none';
}

function openAddConfigModal() {
    document.getElementById('addConfigModelCode').value = currentModel;
    document.getElementById('addConfigLabel').value = '';
    document.getElementById('addConfigShortLabel').value = '';
    document.getElementById('addConfigDescription').value = '';
    document.getElementById('addConfigModal').style.display = 'flex';
}
function closeAddConfigModal() {
    document.getElementById('addConfigModal').style.display = 'none';
}

async function handleAddConfiguration(e) {
    e.preventDefault();
    const label = document.getElementById('addConfigLabel').value.trim();
    const shortLabel = document.getElementById('addConfigShortLabel').value.trim();
    const desc = document.getElementById('addConfigDescription').value.trim();

    if (!label) return;

    try {
        const resp = await fetch('{{ route("admin.quotation-settings.add-configuration") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                model_code: currentModel,
                label: label,
                short_label: shortLabel,
                description: desc
            })
        });

        const res = await resp.json();
        if (res.success) {
            // Update local MODELS_LIST
            const modelObj = MODELS_LIST.find(m => m.code === currentModel);
            if (modelObj) {
                if (!modelObj.configurations) modelObj.configurations = [];
                modelObj.configurations.push(label);
            }

            // Update CONFIG_STORE
            if (!CONFIG_STORE[currentModel]) CONFIG_STORE[currentModel] = {};
            CONFIG_STORE[currentModel][label] = {
                label: label,
                description: desc || getDefaultDescFallback(currentModel, label),
                pricing_bases: {},
                pricing_bases_list: getDefaultInitialBases(currentModel)
            };

            closeAddConfigModal();
            populateChassisDropdown(currentModel);
            document.getElementById('chassisSelector').value = label;
            onChassisChanged(label);
            showToast(`Chassis configuration '${label}' added successfully!`);
        }
    } catch (err) {
        alert('Failed to add configuration: ' + err.message);
    }
}

function toggleLegalSection() {
    const sec = document.getElementById('legalSectionContent');
    const icon = document.getElementById('legalToggleIcon');
    if (sec.style.display === 'none') {
        sec.style.display = 'block';
        icon.innerHTML = '&#9650;';
    } else {
        sec.style.display = 'none';
        icon.innerHTML = '&#9660;';
    }
}

function saveCurrentFormToStore() {
    const data = getActiveConfigData();
    data.description = document.getElementById('chassisDescriptionTextarea').value;
}

document.getElementById('quotationConfigForm').addEventListener('submit', function(e) {
    saveCurrentFormToStore();

    // Serialize currentModel's configurations into configurations_json
    const modelConfigs = CONFIG_STORE[currentModel] || {};
    document.getElementById('formConfigurationsJson').value = JSON.stringify(modelConfigs);
    document.getElementById('saveStatusText').textContent = 'Saving configuration...';
});

/* ================= HELPERS ================= */
function escapeHtml(str) {
    return (str === undefined || str === null) ? '' : String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(msg) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.cssText = 'position:fixed;bottom:80px;right:24px;background:#0f172a;color:white;padding:12px 20px;border-radius:8px;font-size:13.5px;box-shadow:0 10px 25px rgba(0,0,0,0.2);z-index:9999;transition:opacity 0.3s;';
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function getDefaultDescFallback(model, chassis) {
    if (model === 'MT4H') {
        const brand = chassis.includes('Ford F-550XL') ? 'Ford F-550XL' : 'Isuzu NRR';
        const gvwr = chassis.includes('Ford F-550XL') ? '19,000' : '19,500';
        const side = chassis.toUpperCase().includes('RIGHT') ? 'Right' : 'Left';
        return `MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. ${side}-side dump sweeper. Chassis is a brand new 2027MY ${brand}, ${gvwr} GVWR.`;
    }
    if (model === 'MS5H') {
        const isCdl = chassis.toUpperCase().includes('CLASS 7') || chassis.toUpperCase().includes('CDL-REQUIRED');
        const cdlText = isCdl ? 'CDL-required, 148" WB Class 7' : 'non-CDL, 148" WB Class 6';
        return `MS5H Mechanical Broom Sweeper is a single engine sweeper with a 5-cubic yard hopper, mounted on a ${cdlText} commercial truck chassis with a 300HP Cummins engine with air brakes and full LED chassis lighting. The water spray system features a 355-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white and red with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Freightliner M2 Plus, 26,000 GVWR.`;
    }
    return `${model} Mechanical Broom Sweeper with high performance sweeping package.`;
}

function getDefaultStandardFeatures(model) {
    const feats = [
        '<b>Standard Features:</b> ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.',
        'Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled from inside the cab.',
        'Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control.'
    ];
    if (model !== 'MS5H') {
        feats.push('PM-10 compliant (SCAQMD Rule 1186).');
    }
    return feats;
}

function getDefaultInitialBases(model) {
    const isMS5H = model === 'MS5H';
    const msrp = isMS5H ? 349149 : 257493;
    const swList = isMS5H ? 411533 : 307378;
    const feats = getDefaultStandardFeatures(model);

    return [
        {
            key: 'Commercial',
            label: 'Commercial',
            name: 'Commercial',
            price_tier: 'msrp',
            msrp: msrp,
            discount_percent: 0.0,
            calculated_price: msrp,
            subtotal_label: 'MSRP Subtotal',
            caption: '',
            logo_key: 'none',
            use_municipality_freight: false,
            is_active: true,
            standard_features: [...feats]
        },
        {
            key: 'Sourcewell',
            label: 'Sourcewell',
            name: 'Sourcewell',
            price_tier: 'sw_net',
            msrp: swList,
            discount_percent: 12.0,
            calculated_price: Math.round(swList * (1 - 0.12)),
            subtotal_label: 'Sourcewell Contract Price',
            caption: 'Referenced under Sourcewell<br>Contract # 062425-ODRA',
            logo_key: 'sourcewell',
            use_municipality_freight: true,
            is_active: true,
            standard_features: [...feats]
        },
        {
            key: 'HGAC',
            label: 'H-GAC / HGACBuy',
            name: 'H-GAC / HGACBuy',
            price_tier: 'sw_net',
            msrp: swList,
            discount_percent: 15.0,
            calculated_price: Math.round(swList * (1 - 0.15)),
            subtotal_label: 'H-GAC Contract Price',
            caption: 'Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26',
            logo_key: 'hgac',
            use_municipality_freight: true,
            is_active: true,
            standard_features: [...feats]
        },
        {
            key: 'Minnesota',
            label: 'State of Minnesota',
            name: 'State of Minnesota',
            price_tier: 'sw_net',
            msrp: swList,
            discount_percent: 12.0,
            calculated_price: Math.round(swList * (1 - 0.12)),
            subtotal_label: 'Minnesota Contract Price',
            caption: 'Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))',
            logo_key: 'none',
            use_municipality_freight: true,
            is_active: true,
            standard_features: [...feats]
        },
        {
            key: 'Buyboard',
            label: 'BuyBoard',
            name: 'BuyBoard',
            price_tier: 'sw_net',
            msrp: swList,
            discount_percent: 12.0,
            calculated_price: Math.round(swList * (1 - 0.12)),
            subtotal_label: 'BuyBoard Contract Price',
            caption: 'Referenced under BuyBoard<br>ODRA Contract # 788-25',
            logo_key: 'buyboard',
            use_municipality_freight: true,
            is_active: true,
            standard_features: [...feats]
        }
    ];
}
</script>
@endpush
@endsection
