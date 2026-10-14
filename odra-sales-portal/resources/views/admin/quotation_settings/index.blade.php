@extends('layouts.admin')

@section('title', 'Quotation Page & Pricing Basis Editor' . ($scope !== 'GLOBAL' ? ' - ' . $scope : ''))
@section('header-title', 'Quotation Page & Pricing Basis Master Editor' . ($scope !== 'GLOBAL' ? ' (' . $scope . ')' : ''))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <p style="color: var(--text-muted); margin: 0; font-size: 14px;">
        Configure every section of customer quotation sheets — Company Header, Cooperative Pricing Basis contracts &amp; discounts, Standard Features, and Legal Terms globally or per sweeper model.
    </p>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('sales.quotes.create', $scope !== 'GLOBAL' ? ['model' => $scope] : []) }}" target="_blank" class="btn-secondary">
            &#128065; Preview Live Quote Builder
        </a>
        @if($scope === 'GLOBAL')
            <form action="{{ route('admin.quotation-settings.reset') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Reset all quotation template sections and pricing basis rules to factory defaults?');">
                @csrf
                <button type="submit" class="btn-secondary" style="color: #991b1b;">
                    Reset to Factory Defaults
                </button>
            </form>
        @else
            @if(!empty($settings['is_custom']))
                <form action="{{ route('admin.quotation-settings.reset-model', ['model' => $scope]) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Revert {{ $scope }} to inherit all features, terms, and contract rules from Global Defaults?');">
                    @csrf
                    <button type="submit" class="btn-secondary" style="color: #991b1b; font-weight: 600;">
                        &#8634; Revert {{ $scope }} to Global Defaults
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>

<!-- Scope Selector Card -->
<div class="card" style="padding: 16px 20px; background: white; border: 1px solid var(--border); margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-bottom: 4px;">
                Quotation Configuration Scope
            </div>
            <div style="font-size: 17px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                @if($scope === 'GLOBAL')
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="font-size: 20px;">&#127758;</span> Global Default Template
                    </span>
                    <span class="badge badge-active" style="font-size: 11px;">Applies to all models unless overridden</span>
                @else
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="font-size: 20px;">&#128667;</span> Model Override: <span style="color: var(--red);">{{ $scope }}</span>
                    </span>
                    @if(!empty($settings['is_custom']))
                        <span class="badge badge-active" style="background: #0284c7; color: white; font-size: 11px; font-weight: 700;">
                            &bull; Custom Model Overrides Active
                        </span>
                    @else
                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px; border: 1px solid #cbd5e1;">
                            Currently Inheriting Global Defaults
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <!-- Scope Switcher Tabs -->
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <a href="{{ route('admin.quotation-settings.index', ['scope' => 'global']) }}"
               class="{{ $scope === 'GLOBAL' ? 'btn-primary' : 'btn-secondary' }}"
               style="font-size: 12.5px; padding: 7px 14px; text-decoration: none;">
               &#127758; Global Defaults
            </a>
            @foreach($models as $m)
                @php
                    $isCustomModel = !empty($settings['models'][$m->code]);
                @endphp
                <a href="{{ route('admin.quotation-settings.index', ['scope' => $m->code]) }}"
                   class="{{ $scope === $m->code ? 'btn-primary' : 'btn-secondary' }}"
                   style="font-size: 12.5px; padding: 7px 14px; text-decoration: none; position: relative;">
                   {{ $m->code }}
                   @if($isCustomModel)
                       <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #38bdf8; margin-left: 4px; vertical-align: middle;" title="Has custom overrides"></span>
                   @endif
                </a>
            @endforeach
        </div>
    </div>

    @if($scope !== 'GLOBAL')
        <div style="margin-top: 14px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 12.5px; color: #166534; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <span>
                <strong>Model-Specific Scope:</strong> You are editing Pricing Bases, Standard Features, and Legal Terms specifically for <strong>{{ $scope }}</strong>. Any new model added in the future can also be customized here.
            </span>
            <a href="{{ route('admin.models.edit', $models->firstWhere('code', $scope)?->id ?? 1) }}" style="color: #15803d; font-weight: 600; text-decoration: underline;">
                View {{ $scope }} Base MSRP &amp; Chassis Options &rarr;
            </a>
        </div>
    @endif
</div>

<!-- Quick Jump Bar for All Quotation Sections -->
<div class="card" style="padding: 14px 20px; background: #f8fafc; border-left: 4px solid var(--red); margin-bottom: 20px;">
    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #475569; margin-bottom: 8px;">
        Quotation Sections (Editing for: <strong style="color: var(--red);">{{ $scope === 'GLOBAL' ? 'Global Default' : $scope }}</strong>)
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        @if($scope === 'GLOBAL')
            <a href="#sec-header" class="btn-secondary" style="font-size: 12px; padding: 5px 10px;">1. Company Header &amp; Table Labels</a>
        @endif
        <a href="#sec-pricing-basis" class="btn-secondary" style="font-size: 12px; padding: 5px 10px;">2. Pricing Basis &amp; Cooperative Contracts</a>
        <a href="#sec-standard-features" class="btn-secondary" style="font-size: 12px; padding: 5px 10px;">3. Standard Features Section</a>
        <a href="#sec-legal-terms" class="btn-secondary" style="font-size: 12px; padding: 5px 10px;">4. Legal &amp; Payment Terms Footer</a>
        <a href="{{ route('admin.models.index') }}" class="btn-secondary" style="font-size: 12px; padding: 5px 10px; border-color: var(--red); color: var(--red);">5. Sweeper Models &amp; Chassis ({{ $models->count() }}) &rarr;</a>
        <a href="{{ route('admin.enhancements.index') }}" class="btn-secondary" style="font-size: 12px; padding: 5px 10px;">6. Enhancements &amp; Options &rarr;</a>
        <a href="{{ route('admin.freight.index') }}" class="btn-secondary" style="font-size: 12px; padding: 5px 10px;">7. Freight Matrix &rarr;</a>
    </div>
</div>

<form action="{{ route('admin.quotation-settings.update') }}" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="scope" value="{{ $scope }}">

    @if($scope === 'GLOBAL')
        <!-- SECTION 1: Company Header & Quotation Table Labels (Global only) -->
        <div class="card" id="sec-header">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">1. Quotation Header, Address &amp; Table Labels</h3>
                    <span style="font-size: 12px; color: #64748b;">Controls the top company banner, FOB shipping options, discount cap, and summary row labels on the quotation sheet.</span>
                </div>
                <span class="badge badge-active">Top Banner &amp; Table</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Company Name (Alt / Header)</label>
                    <input type="text" name="header[company_name]" class="form-control" value="{{ old('header.company_name', $settings['header']['company_name']) }}" required>
                </div>
                <div class="form-group">
                    <label>Document Title Badge (Top Right)</label>
                    <input type="text" name="header[quote_title]" class="form-control" value="{{ old('header.quote_title', $settings['header']['quote_title']) }}" required>
                </div>
                <div class="form-group">
                    <label>Company Address Line 1</label>
                    <input type="text" name="header[address_line_1]" class="form-control" value="{{ old('header.address_line_1', $settings['header']['address_line_1']) }}" required>
                </div>
                <div class="form-group">
                    <label>Company Contact Line 2 (Phone, Email, Website)</label>
                    <input type="text" name="header[address_line_2]" class="form-control" value="{{ old('header.address_line_2', $settings['header']['address_line_2']) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; margin-top: 8px;">
                <div class="form-group">
                    <label>Enhancements Section Row Title</label>
                    <input type="text" name="header[section_heading]" class="form-control" value="{{ old('header.section_heading', $settings['header']['section_heading']) }}" required>
                </div>
                <div class="form-group">
                    <label>Freight Line Item Label</label>
                    <input type="text" name="header[freight_row_label]" class="form-control" value="{{ old('header.freight_row_label', $settings['header']['freight_row_label']) }}" required>
                </div>
                <div class="form-group">
                    <label>Grand Total Row Label</label>
                    <input type="text" name="header[total_row_label]" class="form-control" value="{{ old('header.total_row_label', $settings['header']['total_row_label']) }}" required>
                </div>
                <div class="form-group">
                    <label>Max Extra Discount Cap (USD)</label>
                    <input type="number" name="header[max_show_discount]" class="form-control" value="{{ old('header.max_show_discount', $settings['header']['max_show_discount']) }}" required min="0" step="100">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Default FOB / Transport Option (Delivered)</label>
                    <input type="text" name="header[default_fob]" class="form-control" value="{{ old('header.default_fob', $settings['header']['default_fob']) }}" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Alternate FOB Option (Factory Pick-up / $0 Freight)</label>
                    <input type="text" name="header[exw_fob]" class="form-control" value="{{ old('header.exw_fob', $settings['header']['exw_fob']) }}" required>
                </div>
            </div>
        </div>
    @else
        <!-- Information Notice for Section 1 when editing a model scope -->
        <div class="card" style="padding: 14px 18px; background: #f8fafc; border: 1px dashed #cbd5e1; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 13px; color: #475569;">
                    <strong>Company Header &amp; Table Labels:</strong> The top company logo, address, phone number, and document title are configured at the Global level and shared across all models.
                </div>
                <a href="{{ route('admin.quotation-settings.index', ['scope' => 'global']) }}#sec-header" class="btn-secondary" style="font-size: 12px; padding: 5px 12px;">
                    Edit Global Header &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- SECTION 2: Pricing Basis & Cooperative Contract Rules -->
    <div class="card" id="sec-pricing-basis">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                    2. Pricing Basis &amp; Cooperative Contracts for {{ $scope === 'GLOBAL' ? 'Global Default' : $scope }}
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Controls how changing the <strong>Pricing Basis</strong> dropdown updates prices (MSRP vs Cooperative List &amp; Net), discount %, subtotal label, contract banner, logo, and municipality freight for {{ $scope === 'GLOBAL' ? 'all sweeper models' : $scope }}.
                </span>
            </div>
            <button type="button" class="btn-secondary" onclick="addPricingBasisRow()" style="font-size: 12px; padding: 7px 14px;">
                + Add Pricing Basis
            </button>
        </div>

        <div id="pricingBasesContainer" style="display: flex; flex-direction: column; gap: 16px;">
            @foreach ($settings['pricing_bases'] as $idx => $basis)
                <div class="pricing-basis-card" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; position: relative;">
                    <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr 1fr 1.2fr auto; gap: 12px; align-items: end; margin-bottom: 12px;">
                        <div class="form-group" style="margin: 0;">
                            <label>Basis Code / Key</label>
                            <input type="text" name="pricing_bases[{{ $idx }}][key]" class="form-control" value="{{ $basis['key'] }}" required>
                            <input type="hidden" name="pricing_bases[{{ $idx }}][slug]" value="{{ $basis['slug'] ?? '' }}">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Display Name on Quote</label>
                            <input type="text" name="pricing_bases[{{ $idx }}][label]" class="form-control" value="{{ $basis['label'] }}" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Price Formula Tier</label>
                            <select name="pricing_bases[{{ $idx }}][price_tier]" class="form-control">
                                <option value="msrp" {{ ($basis['price_tier'] ?? '') === 'msrp' ? 'selected' : '' }}>Commercial MSRP</option>
                                <option value="sw_net" {{ ($basis['price_tier'] ?? '') === 'sw_net' ? 'selected' : '' }}>Cooperative Contract</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Discount Rate (%)</label>
                            <input type="number" name="pricing_bases[{{ $idx }}][discount_percent]" class="form-control" value="{{ $basis['discount_percent'] ?? ($basis['is_coop'] ? 12.0 : 0.0) }}" min="0" max="100" step="0.1" placeholder="0.0">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Subtotal Row Label</label>
                            <input type="text" name="pricing_bases[{{ $idx }}][subtotal_label]" class="form-control" value="{{ $basis['subtotal_label'] }}" required>
                        </div>
                        <div>
                            <button type="button" class="btn-secondary" onclick="if(document.querySelectorAll('.pricing-basis-card').length > 1) this.closest('.pricing-basis-card').remove();" style="color: #991b1b; padding: 9px 12px;" title="Remove Pricing Basis">&times;</button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr 0.8fr 0.8fr; gap: 12px; align-items: center;">
                        <div class="form-group" style="margin: 0;">
                            <label>Description Note (Appends to Model Desc)</label>
                            <input type="text" name="pricing_bases[{{ $idx }}][description_template]" class="form-control" value="{{ $basis['description_template'] ?? $basis['caption'] ?? '' }}" placeholder="e.g. Referenced under Sourcewell Contract # 062425-ODRA">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Red Disclaimer Banner (Top of Quote)</label>
                            <input type="text" name="pricing_bases[{{ $idx }}][disclaimer]" class="form-control" value="{{ $basis['disclaimer'] ?? '' }}" placeholder="Leave blank for commercial MSRP">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Contract Caption in Table</label>
                            <input type="text" name="pricing_bases[{{ $idx }}][caption]" class="form-control" value="{{ $basis['caption'] ?? '' }}" placeholder="e.g. Referenced under Sourcewell<br>Contract # 062425-ODRA">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Cooperative Logo</label>
                            <select name="pricing_bases[{{ $idx }}][logo_key]" class="form-control">
                                <option value="none" {{ empty($basis['logo_key']) ? 'selected' : '' }}>No Logo</option>
                                <option value="sourcewell" {{ ($basis['logo_key'] ?? '') === 'sourcewell' ? 'selected' : '' }}>Sourcewell Logo</option>
                                <option value="hgac" {{ ($basis['logo_key'] ?? '') === 'hgac' ? 'selected' : '' }}>H-GAC Logo</option>
                                <option value="buyboard" {{ ($basis['logo_key'] ?? '') === 'buyboard' ? 'selected' : '' }}>BuyBoard Logo</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 18px 0 0;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px;">
                                <input type="checkbox" name="pricing_bases[{{ $idx }}][use_municipality_freight]" value="1" {{ !empty($basis['use_municipality_freight']) ? 'checked' : '' }}>
                                <span>Municipality Freight</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- SECTION 3: Standard Features Section -->
    <div class="card" id="sec-standard-features">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                    3. Standard Features Section for {{ $scope === 'GLOBAL' ? 'Global Default' : $scope }} (Quotation Body Footer)
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Controls the Standard Features paragraphs printed immediately below the pricing table on {{ $scope === 'GLOBAL' ? 'all sweeper quotations by default' : 'quotations for ' . $scope }}.
                </span>
            </div>
            <span class="badge badge-active">Standard Inclusions</span>
        </div>

        <div class="form-group">
            <label>Standard Features — Paragraph 1 (HTML &lt;b&gt; allowed)</label>
            <textarea name="legal[standard_features_p1]" class="form-control" rows="2" required>{{ old('legal.standard_features_p1', $settings['legal']['standard_features_p1']) }}</textarea>
        </div>

        <div class="form-group">
            <label>Standard Features — Paragraph 2 (Sweeping Operation, Elevator, Side Brooms, Water System)</label>
            <textarea name="legal[standard_features_p2]" class="form-control" rows="3" required>{{ old('legal.standard_features_p2', $settings['legal']['standard_features_p2']) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Standard Features — Paragraph 3 (In-Cab Controls, Cameras &amp; Safety LED Lighting)</label>
                <textarea name="legal[standard_features_p3]" class="form-control" rows="2" required>{{ old('legal.standard_features_p3', $settings['legal']['standard_features_p3']) }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>PM-10 Compliance Suffix (Appended to Paragraph 3)</label>
                <textarea name="legal[pm10_note]" class="form-control" rows="2" placeholder="e.g. PM-10 compliant (SCAQMD Rule 1186). Leave blank for models like MS5H">{{ old('legal.pm10_note', $settings['legal']['pm10_note']) }}</textarea>
                <small style="color: #64748b; font-size: 11px;">Notice: MS5H omits PM-10 compliance per manufacturer specification.</small>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Legal Terms & Conditions Footer -->
    <div class="card" id="sec-legal-terms">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                    4. Legal, Delivery, Deposit &amp; Validity Terms for {{ $scope === 'GLOBAL' ? 'Global Default' : $scope }}
                </h3>
                <span style="font-size: 12px; color: #64748b;">
                    Controls the 4 legal disclaimer paragraphs at the bottom of the quotation document for {{ $scope === 'GLOBAL' ? 'all sweeper models' : $scope }}.
                </span>
            </div>
            <span class="badge badge-active">Legal Terms</span>
        </div>

        <div class="form-group">
            <label>Delivery Transport Estimate Disclaimer</label>
            <textarea name="legal[legal_p1]" class="form-control" rows="2" required>{{ old('legal.legal_p1', $settings['legal']['legal_p1']) }}</textarea>
        </div>

        <div class="form-group">
            <label>Taxes, Registration &amp; Titling Responsibility</label>
            <textarea name="legal[legal_p2]" class="form-control" rows="2" required>{{ old('legal.legal_p2', $settings['legal']['legal_p2']) }}</textarea>
        </div>

        <div class="form-group">
            <label>Deposit, Purchase Order &amp; Payment Terms (HTML &lt;b&gt; allowed)</label>
            <textarea name="legal[legal_p3]" class="form-control" rows="2" required>{{ old('legal.legal_p3', $settings['legal']['legal_p3']) }}</textarea>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label>Budgetary Quote Validity Notice (HTML &lt;b&gt; allowed)</label>
            <textarea name="legal[legal_p4]" class="form-control" rows="2" required>{{ old('legal.legal_p4', $settings['legal']['legal_p4']) }}</textarea>
        </div>
    </div>

    <div style="display: flex; gap: 12px; position: sticky; bottom: 16px; background: white; padding: 16px 24px; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 20; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="submit" class="btn-primary" style="padding: 11px 24px; font-size: 15px;">
                Save Quotation Settings for {{ $scope === 'GLOBAL' ? 'Global Defaults' : $scope }}
            </button>
            <span style="font-size: 12px; color: #64748b;">
                Changes take effect immediately across all Quote Builder sessions.
            </span>
        </div>
        <a href="{{ route('sales.quotes.create', $scope !== 'GLOBAL' ? ['model' => $scope] : []) }}" target="_blank" class="btn-secondary" style="display: inline-flex; align-items: center; text-decoration: none;">
            Open Live Quotation Page &rarr;
        </a>
    </div>
</form>

@push('scripts')
<script>
let pbIdx = {{ count($settings['pricing_bases']) }};
function addPricingBasisRow() {
    const container = document.getElementById('pricingBasesContainer');
    const div = document.createElement('div');
    div.className = 'pricing-basis-card';
    div.style.cssText = 'background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; position: relative;';
    div.innerHTML = `
        <div style="display: grid; grid-template-columns: 1fr 1.2fr 1fr 1fr 1.2fr auto; gap: 12px; align-items: end; margin-bottom: 12px;">
            <div class="form-group" style="margin: 0;">
                <label>Basis Code / Key</label>
                <input type="text" name="pricing_bases[${pbIdx}][key]" class="form-control" placeholder="e.g. NASPO" required>
                <input type="hidden" name="pricing_bases[${pbIdx}][slug]" value="">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Display Name on Quote</label>
                <input type="text" name="pricing_bases[${pbIdx}][label]" class="form-control" placeholder="e.g. NASPO ValuePoint" required>
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Price Formula Tier</label>
                <select name="pricing_bases[${pbIdx}][price_tier]" class="form-control">
                    <option value="sw_net" selected>Cooperative Contract</option>
                    <option value="msrp">Commercial MSRP</option>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Discount Rate (%)</label>
                <input type="number" name="pricing_bases[${pbIdx}][discount_percent]" class="form-control" value="12.0" min="0" max="100" step="0.1" placeholder="0.0">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Subtotal Row Label</label>
                <input type="text" name="pricing_bases[${pbIdx}][subtotal_label]" class="form-control" placeholder="e.g. NASPO Contract Price" required>
            </div>
            <div>
                <button type="button" class="btn-secondary" onclick="this.closest('.pricing-basis-card').remove();" style="color: #991b1b; padding: 9px 12px;">&times;</button>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 1fr 0.8fr 0.8fr; gap: 12px; align-items: center;">
            <div class="form-group" style="margin: 0;">
                <label>Description Note (Appends to Model Desc)</label>
                <input type="text" name="pricing_bases[${pbIdx}][description_template]" class="form-control" placeholder="e.g. Referenced under NASPO Contract # 12345">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Red Disclaimer Banner (Top of Quote)</label>
                <input type="text" name="pricing_bases[${pbIdx}][disclaimer]" class="form-control" placeholder="e.g. Quoted under cooperative contract: NASPO #12345">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Contract Caption in Table</label>
                <input type="text" name="pricing_bases[${pbIdx}][caption]" class="form-control" placeholder="e.g. Referenced under NASPO<br>Contract # 12345">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Cooperative Logo</label>
                <select name="pricing_bases[${pbIdx}][logo_key]" class="form-control">
                    <option value="none">No Logo</option>
                    <option value="sourcewell">Sourcewell Logo</option>
                    <option value="hgac">H-GAC Logo</option>
                    <option value="buyboard">BuyBoard Logo</option>
                </select>
            </div>
            <div class="form-group" style="margin: 18px 0 0;">
                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px;">
                    <input type="checkbox" name="pricing_bases[${pbIdx}][use_municipality_freight]" value="1" checked>
                    <span>Municipality Freight</span>
                </label>
            </div>
        </div>
    `;
    container.appendChild(div);
    pbIdx++;
}
</script>
@endpush
@endsection
