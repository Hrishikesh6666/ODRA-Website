@extends('layouts.app')

@section('title', ($quote ? 'Quotation Q-' . $quoteNumber : 'New Quotation'))

@push('styles')
<style>
:root {
  --red: #AC2529;
  --red-dark: #8a1e22;
  --gray: #585858;
  --line: #d9d9d9;
  --bg: #f4f4f5;
  --paper: #ffffff;
  --ink: #232323;
  --muted: #767676;
  --yellow: #fff8e1;
}

/* Builder Navigation Bar */
.builder-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 12px 20px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  margin-bottom: 16px;
}
.builder-heading h1 {
  font-size: 19px;
  margin: 0 0 3px 0;
  font-weight: 700;
  color: #0f172a;
}
.builder-heading span {
  font-size: 13px;
  color: #64748b;
}
.builder-topbar-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}
.btn-nav {
  background: white;
  color: #334155;
  border: 1px solid #cbd5e1;
  padding: 7px 14px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s ease;
}
.btn-nav:hover {
  background: #f8fafc;
  border-color: #94a3b8;
}

/* Main Builder Layout */
.app-builder-layout {
  display: flex;
  gap: 20px;
  max-width: 1560px;
  margin: 0 auto;
  align-items: flex-start;
}
.panel {
  background: var(--paper);
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 18px 20px;
}

/* Left Form Pane */
.builder {
  flex: 0 0 430px;
  max-height: calc(100vh - 140px);
  overflow-y: auto;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f8fafc;
}
.builder::-webkit-scrollbar, .previewWrap::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
.builder::-webkit-scrollbar-track, .previewWrap::-webkit-scrollbar-track {
  background: #f8fafc;
  border-radius: 4px;
}
.builder::-webkit-scrollbar-thumb, .previewWrap::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.builder::-webkit-scrollbar-thumb:hover, .previewWrap::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.brandbar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}
.brandbar img {
  height: 34px;
  object-fit: contain;
}
.brandbar h1 {
  font-size: 18px;
  margin: 0;
  color: var(--red);
  letter-spacing: .3px;
  font-weight: 700;
}

h2.sec {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--red);
  border-bottom: 2px solid var(--red);
  padding-bottom: 5px;
  margin: 18px 0 10px;
  font-weight: 700;
}
h2.sec:first-of-type {
  margin-top: 0;
}

.builder label {
  display: block;
  font-size: 11.5px;
  font-weight: bold;
  color: var(--gray);
  margin: 9px 0 3px;
}
.builder input[type=text],
.builder input[type=number],
.builder input[type=date],
.builder select,
.builder textarea {
  width: 100%;
  padding: 6px 8px;
  font-size: 13px;
  border: 1px solid #c9c9c9;
  border-radius: 5px;
  background: var(--yellow);
  font-family: inherit;
  color: var(--ink);
  box-sizing: border-box;
}
.builder input:focus, .builder select:focus {
  outline: 2px solid #ac2529;
  border-color: #ac2529;
}
.row2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.enhRow {
  display: grid;
  grid-template-columns: 1fr 56px;
  gap: 6px;
  align-items: end;
  margin-bottom: 6px;
}
.enhRow label {
  margin: 6px 0 3px;
}
.smallnote {
  font-size: 10.5px;
  color: var(--muted);
  margin-top: 2px;
  font-weight: normal;
}
.btnrow {
  display: flex;
  gap: 10px;
  margin-top: 18px;
  position: sticky;
  bottom: 0;
  background: var(--paper);
  padding-top: 12px;
  flex-wrap: wrap;
  z-index: 10;
}
.btnrow button, .btnrow a {
  cursor: pointer;
  border: none;
  border-radius: 6px;
  padding: 10px 14px;
  font-size: 13px;
  font-weight: bold;
  text-align: center;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: 0.15s;
}
.btn-primary {
  background: var(--red);
  color: #fff;
  flex: 1;
}
.btn-primary:hover {
  background: var(--red-dark);
}
.btn-secondary {
  background: #eee;
  color: #333;
}
.btn-secondary:hover {
  background: #ddd;
}
.btn-success {
  background: #2d7150;
  color: #fff;
}
.btn-success:hover {
  background: #23593f;
}

/* Right Preview Pane */
.previewWrap {
  flex: 1;
  min-width: 0;
  max-height: calc(100vh - 140px);
  overflow-y: auto;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  background: #e2e8f0;
  border-radius: 10px;
  padding: 24px 20px;
  display: block; /* Avoid flexbox negative centering offset */
  box-sizing: border-box;
}

#preview {
  background: #ffffff;
  padding: 34px 38px;
  font-size: 12px;
  line-height: 1.45;
  color: #1a1a1a;
  min-height: 400px;
  width: 100%;
  max-width: 820px;
  margin: 0 auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  border-radius: 6px;
  box-sizing: border-box;
  overflow: hidden; /* Guarantee nothing bleeds outside sheet */
}

/* Document Header */
.docHeaderTop {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 3px solid var(--red);
  padding-bottom: 10px;
  margin-bottom: 12px;
}
.docHeaderTop .co img {
  height: 48px;
  display: block;
  margin-bottom: 4px;
  object-fit: contain;
}
.docHeaderTop .co .addr {
  font-size: 11px;
  color: var(--gray);
  line-height: 1.35;
}
.docHeaderTop .qbox {
  text-align: right;
}
.docHeaderTop .qbox .ttl {
  font-size: 21px;
  font-weight: 800;
  color: var(--red);
  letter-spacing: 1.2px;
}
.docHeaderTop .qbox table {
  margin-left: auto;
  font-size: 11.5px;
  border-collapse: collapse;
}
.docHeaderTop .qbox td {
  padding: 1px 0 1px 10px !important;
  text-align: right !important;
  border: none !important;
  white-space: nowrap !important;
}
.docHeaderTop .qbox td.k {
  color: var(--gray);
  text-align: right;
  padding-left: 14px !important;
  font-weight: 600;
}
.disclaimerLine {
  font-size: 11px;
  font-style: italic;
  color: var(--gray);
  margin-bottom: 10px;
}
.infoGrid {
  display: flex;
  gap: 24px;
  font-size: 11.5px;
  margin-bottom: 14px;
}
.infoGrid .col {
  flex: 1;
  min-width: 0;
}
.infoGrid .col>div {
  margin-bottom: 3px;
}
.infoGrid .k {
  color: var(--gray);
  font-weight: 700;
  display: inline-block;
  min-width: 120px;
}
.infoGrid .toBlock .toLines {
  display: inline-block;
  vertical-align: top;
}
.infoGrid .toBlock .toLines>div {
  margin-bottom: 2px;
}

/* Strict Table Items Styling - Column & Row Format */
table.items {
  width: 100% !important;
  max-width: 100% !important;
  table-layout: fixed !important; /* Critical: strict column widths, prevents horizontal blowing out */
  border-collapse: collapse !important;
  margin-top: 8px;
  margin-bottom: 6px;
  box-sizing: border-box !important;
}

table.items th,
table.items td {
  white-space: normal !important; /* Critical: overrides portal.css global td/th nowrap */
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  word-break: normal !important;
  box-sizing: border-box !important;
}

table.items th {
  background: var(--red) !important;
  color: #ffffff !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.25px !important;
  padding: 8px 6px !important;
  text-align: left !important;
  border: none !important;
  box-sizing: border-box !important;
  white-space: nowrap !important;
}
table.items th.num {
  text-align: right !important;
  white-space: nowrap !important;
  padding-right: 6px !important;
  padding-left: 4px !important;
}

table.items td {
  padding: 7px 6px !important;
  border-bottom: 1px solid #eeeeee !important;
  font-size: 11px !important;
  vertical-align: top !important;
  color: #1a1a1a !important;
  line-height: 1.45 !important;
  box-sizing: border-box !important;
}
table.items td.num {
  text-align: right !important;
  white-space: nowrap !important;
  font-variant-numeric: tabular-nums !important;
  padding-right: 6px !important;
  padding-left: 4px !important;
}

table.items tr.baseRow td {
  background: #ffffff !important;
  vertical-align: top !important;
}
table.items tr.baseRow td:first-child,
table.items tr.baseRow td.descCol {
  font-weight: 700 !important;
  color: #1a1a1a !important;
  line-height: 1.45 !important;
  padding-right: 14px !important;
  white-space: normal !important;
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
}
table.items tr.baseRow td.num {
  font-weight: 700 !important;
}

table.items tr.sectionRow td {
  font-weight: 700 !important;
  color: var(--red) !important;
  font-size: 11.5px !important;
  border-bottom: 1px solid #eeeeee !important;
  padding-top: 14px !important;
  padding-bottom: 6px !important;
  background: transparent !important;
}

table.items tr.coopRow td {
  border-bottom: 2px solid #1a1a1a !important;
}

table.items tr.subtotalRow td {
  border-top: 2px solid #1a1a1a !important;
  font-weight: 700 !important;
  padding-top: 10px !important;
  padding-bottom: 6px !important;
  white-space: nowrap !important;
}

table.items tr.totalRow td {
  font-weight: 700 !important;
  font-size: 14px !important;
  color: var(--red) !important;
  border-top: 1px solid #cccccc !important;
  padding-top: 10px !important;
  padding-bottom: 10px !important;
  white-space: nowrap !important;
}

.legal {
  margin-top: 18px;
  border-top: 1px solid var(--line);
  padding-top: 12px;
}
.legal p {
  font-size: 9.8px;
  color: var(--gray);
  margin: 0 0 6px;
  line-height: 1.4;
  white-space: normal !important;
  word-wrap: break-word !important;
}
.legal p b {
  color: #1a1a1a;
}

@media (max-width: 991px) {
  .builder-topbar {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
    padding: 14px 16px;
  }
  .builder-heading h1 {
    font-size: 17px;
  }
  .builder-topbar-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    width: 100%;
  }
  .builder-topbar-actions .btn-nav {
    flex: 1 1 auto;
    justify-content: center;
    font-size: 12px;
    padding: 8px 10px;
    text-align: center;
  }
  .app-builder-layout {
    flex-direction: column;
    gap: 16px;
  }
  .builder {
    flex: 1 1 100%;
    width: 100%;
    max-height: none;
    box-sizing: border-box;
    padding: 16px;
  }
  .previewWrap {
    flex: 1 1 100%;
    width: 100%;
    max-height: none;
    padding: 16px 8px;
    box-sizing: border-box;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  #preview {
    padding: 20px 14px;
    min-width: 600px;
    box-sizing: border-box;
  }
  .mobile-jump-bar {
    display: flex !important;
    gap: 8px;
    margin-bottom: 12px;
  }
  .mobile-jump-btn {
    flex: 1;
    text-align: center;
    padding: 9px 12px;
    background: #e2e8f0;
    color: #1e293b;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
  }
}

.mobile-jump-bar {
  display: none;
}

@media print {
  body { background: #fff !important; }
  .portal-header, .builder-topbar, .builder, .btnrow, .mobile-jump-bar { display: none !important; }
  .app-builder-layout { display: block !important; padding: 0 !important; max-width: none !important; }
  .previewWrap { background: transparent !important; padding: 0 !important; max-height: none !important; overflow: visible !important; display: block !important; }
  #preview { box-shadow: none !important; border: none !important; padding: 0 !important; max-width: 100% !important; width: 100% !important; }
  table.items { width: 100% !important; table-layout: fixed !important; }
  table.items td, table.items th { white-space: normal !important; }
  table.items td.num, table.items th.num { white-space: nowrap !important; }
}
</style>
@endpush

@section('content')
<div class="builder-topbar">
    <div class="builder-heading">
        <h1>Quotation Q-{{ $quoteNumber }}</h1>
        <span>Revision {{ $version }} &middot; {{ $isManagerViewingOther ? (($viewingVersion?->isFinalized() ?? false) ? 'Finalized (Manager Read-Only)' : 'Draft (Manager Read-Only)') : ($isReadonly ? 'Finalized (Historical Snapshot Frozen)' : 'Draft (Editable)') }}</span>
    </div>
    <div class="builder-topbar-actions">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.models.index') }}" class="btn-nav" style="border-color:var(--red);color:var(--red);" title="Add or Edit Sweeper Models & Master Prices">
                &#9881; Edit Models
            </a>
            <a href="{{ route('admin.quotation-settings.index') }}" class="btn-nav" style="border-color:var(--red);color:var(--red);" title="Edit Every Quotation Section & Pricing Basis Rules">
                &#9998; Edit Quotation Sections &amp; Pricing Basis
            </a>
        @endif

        @if(auth()->user()->isManager())
            <a href="{{ route('manager.quotes.index') }}" class="btn-nav">&larr; Team Quotations</a>
        @else
            <a href="{{ route('sales.quotes.index') }}" class="btn-nav">&larr; My Quotations</a>
        @endif

        @if($quote)
            <button type="button" class="btn-nav" onclick="openRevisionHistory({{ $quote->id }})" title="View Complete Revision History">
                &#128340; Revision History
            </button>
        @endif

        <button type="button" class="btn-nav" onclick="window.print()" title="Print Quotation">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>

        @if ($isManagerViewingOther)
            @if (($viewingVersion?->isFinalized() ?? false) || $quote?->status === 'Finalized')
                <a href="{{ route('sales.quotes.pdf', ['quote' => $quote, 'version' => $version]) }}" class="btn-nav" style="background:#059669;color:white;border-color:#059669;" target="_blank">Download PDF</a>
            @endif
        @elseif ($isReadonly)
            @if ($canRevise ?? true)
                <button type="button" id="btnRevise" class="btn-nav" style="background:#dc2626;color:white;border-color:#dc2626;font-weight:700;">Create Revised Quotation</button>
            @endif
            <a href="{{ route('sales.quotes.pdf', ['quote' => $quote, 'version' => $version]) }}" class="btn-nav" style="background:#059669;color:white;border-color:#059669;" target="_blank">Download PDF</a>
        @else
            @if ($canModify ?? true)
                <button type="button" id="btnSaveDraft" class="btn-nav">Save Draft</button>
                <button type="button" id="btnFinalize" class="btn-nav" style="background:var(--red);color:white;border-color:var(--red);">Finalize Quote</button>
            @endif
        @endif
    </div>
</div>

@if($isManagerViewingOther)
<div style="background: #f8fafc; border-left: 4px solid #64748b; border-radius: 6px; padding: 12px 18px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 20px;">&#128274;</span>
        <div>
            <strong style="color: #0f172a; font-size: 14px;">Viewing Quotation Revision {{ $version }} &middot; Created by {{ $quoteOwnerName }} (Read-Only)</strong>
            <div style="font-size: 12.5px; color: #475569; margin-top: 2px;">
                This quotation belongs to <strong>{{ $quoteOwnerName }}</strong>. As a manager, you have commercial inspection, printing, and PDF download permissions. Modification controls and revision creation are reserved for the quotation creator.
            </div>
        </div>
    </div>
    @if($quote)
        <button type="button" onclick="openRevisionHistory({{ $quote->id }})" class="btn-nav" style="background: white; border-color: #cbd5e1; font-size: 12px; padding: 6px 14px; white-space: nowrap;">
            View All Revisions &rarr;
        </button>
    @endif
</div>
@elseif($isReadonly)
<div style="background: #f0fdf4; border-left: 4px solid #16a34a; border-radius: 6px; padding: 12px 18px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 20px;">&#128274;</span>
        <div>
            <strong style="color: #166534; font-size: 14px;">Viewing Finalized Quotation Revision {{ $version }} (Read-Only)</strong>
            <div style="font-size: 12.5px; color: #4b5563; margin-top: 2px;">
                Original pricing, line items, and financial totals are permanently preserved. To modify requirements, click <strong>Create Revised Quotation</strong> to start a new editable draft.
            </div>
        </div>
    </div>
    @if($quote)
        <button type="button" onclick="openRevisionHistory({{ $quote->id }})" class="btn-nav" style="background: white; border-color: #cbd5e1; font-size: 12px; padding: 6px 14px; white-space: nowrap;">
            View All Revisions &rarr;
        </button>
    @endif
</div>
@endif

@php
    $tplHeader = $catalog['quote_template']['header'] ?? [];
    $maxShowDiscount = (int) ($tplHeader['max_show_discount'] ?? 20000);
    $defaultFob = $tplHeader['default_fob'] ?? 'Transport, estimate only noted in quote';
    $exwFob = $tplHeader['exw_fob'] ?? 'EXW, Factory pick-up';
    $catalogModels = $catalog['models'] ?? [];
    $catalogBases = $catalog['pricing_bases'] ?? [];
@endphp
<div class="mobile-jump-bar">
    <a href="#builderPanel" class="mobile-jump-btn">&#9998; Edit Specifications</a>
    <a href="#preview" class="mobile-jump-btn" style="background:var(--red);color:white;">&#128196; View Live Proposal</a>
</div>

<div class="app-builder-layout">
  <!-- Left Builder Form Panel -->
  <div class="panel builder" id="builderPanel">
    <div class="brandbar">
      <img src="{{ asset('images/logo.jpg') }}" alt="ODRA Logo">
      <div><h1>Quote Builder</h1></div>
    </div>

    <h2 class="sec">Quote Header</h2>
    <div class="row2">
      <div style="grid-column: 1/-1;">
        <label>Quote No.</label>
        <input type="text" id="quoteSeq" maxlength="4" value="{{ $quoteNumber }}" {{ $isReadonly ? 'readonly' : '' }}>
      </div>
    </div>
    <div class="row2">
      <div style="grid-column: 1/-1;">
        <label>Date</label>
        <input type="date" id="quoteDate" {{ $isReadonly ? 'disabled' : '' }}>
      </div>
    </div>

    <h2 class="sec">Unit &amp; Pricing</h2>
    <label>Model</label>
    <select id="model" {{ $isReadonly ? 'disabled' : '' }}>
      @if(!empty($catalogModels))
        @foreach($catalogModels as $cm)
          <option value="{{ $cm['code'] }}">{{ $cm['code'] }}</option>
        @endforeach
      @else
        <option value="MT4H">MT4H</option>
        <option value="MS5H">MS5H</option>
      @endif
    </select>

    <label>Pricing Basis</label>
    <select id="basis" {{ $isReadonly ? 'disabled' : '' }}>
      @if(!empty($catalogBases))
        @foreach($catalogBases as $pb)
          <option value="{{ $pb['key'] ?? $pb['name'] }}">{{ $pb['label'] ?? $pb['name'] }}</option>
        @endforeach
      @else
        <option value="MSRP (Commercial)">MSRP (Commercial)</option>
        <option value="Sourcewell">Sourcewell</option>
        <option value="HGAC">H-GAC / HGACBuy</option>
        <option value="Minnesota">State of Minnesota</option>
        <option value="Buyboard">BuyBoard</option>
      @endif
    </select>

    <label>Chassis / Discharge Configuration</label>
    <select id="chassis" {{ $isReadonly ? 'disabled' : '' }}></select>

    <h2 class="sec">Customer &amp; Order Info</h2>
    <div class="row2">
      <div>
        <label>P.O. No.</label>
        <input type="text" id="po" {{ $isReadonly ? 'disabled' : '' }}>
      </div>
      <div>
        <label>Customer Acct #</label>
        <input type="text" id="acct" {{ $isReadonly ? 'disabled' : '' }}>
      </div>
    </div>
    <div class="row2">
      <div>
        <label>Sales Rep</label>
        @if(!$quote && $user && $user->isAdmin())
          <select id="salesRep" {{ $isReadonly ? 'disabled' : '' }}>
            <option value="">&mdash; Select &mdash;</option>
            @foreach($salesReps as $rep)
              @php $rName = is_array($rep) ? $rep['name'] : $rep->name; @endphp
              <option value="{{ $rName }}">{{ $rName }}</option>
            @endforeach
          </select>
        @else
          <div style="position: relative; display: flex; align-items: center;">
            <input type="text" id="salesRep" value="{{ $initialState['salesRep'] ?: ($quoteOwnerName ?? ($user ? $user->name : '')) }}" readonly style="background: #f1f5f9 !important; color: #1e293b !important; font-weight: 600 !important; cursor: not-allowed !important; border: 1px solid #cbd5e1 !important; padding-right: 32px !important;" title="Quotation creator: {{ $initialState['salesRep'] ?: ($quoteOwnerName ?? ($user ? $user->name : '')) }}">
            <span style="position: absolute; right: 10px; font-size: 13px; color: #64748b; pointer-events: none;" title="Original Quotation Creator">&#128274;</span>
          </div>
        @endif
      </div>
      <div>
        <label>FOB / Transport</label>
        <select id="fob" {{ $isReadonly ? 'disabled' : '' }}>
          <option value="{{ $defaultFob }}">{{ $defaultFob }}</option>
          <option value="{{ $exwFob }}">{{ $exwFob }}</option>
        </select>
      </div>
    </div>

    <label>To</label>
    <input type="text" id="toCompany" placeholder="Company Name" {{ $isReadonly ? 'disabled' : '' }}>
    <input type="text" id="toAddr1" placeholder="Address 1" style="margin-top:6px;" {{ $isReadonly ? 'disabled' : '' }}>
    <input type="text" id="toAddr2" placeholder="Address 2" style="margin-top:6px;" {{ $isReadonly ? 'disabled' : '' }}>
    <input type="text" id="toContact" placeholder="Contact Name" style="margin-top:6px;" {{ $isReadonly ? 'disabled' : '' }}>

    <h2 class="sec">Base Unit Qty</h2>
    <input type="number" id="baseQty" value="1" min="1" {{ $isReadonly ? 'disabled' : '' }}>

    <label style="margin-top:10px;display:block;">Show Extra Discount On Quote (USD) <span class="smallnote">optional &mdash; max ${{ number_format($maxShowDiscount) }}</span></label>
    <input type="number" id="baseShowDiscount" value="0" min="0" max="{{ $maxShowDiscount }}" {{ $isReadonly ? 'disabled' : '' }}>
    <div id="discountWarning" style="display:none;color:#b3261e;font-size:11px;margin-top:4px;">Maximum limit allowed is ${{ number_format($maxShowDiscount) }}.</div>

    <h2 class="sec">Enhancements <span class="smallnote">(pick up to 13 options)</span></h2>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;margin:10px 0 14px 0;">
      <label for="enhancementPricingMode" style="font-weight:700;color:#1e293b;display:block;margin-bottom:6px;">Enhancement Price Display</label>
      <select id="enhancementPricingMode" style="width:100%;padding:8px 10px;border-radius:5px;border:1px solid #cbd5e1;font-size:13px;font-weight:600;background:white;" {{ $isReadonly ? 'disabled' : '' }}>
        <option value="itemized" {{ ($initialState['enhancementPricingMode'] ?? 'itemized') === 'itemized' ? 'selected' : '' }}>Itemized</option>
        <option value="bundled" {{ ($initialState['enhancementPricingMode'] ?? 'itemized') === 'bundled' ? 'selected' : '' }}>Bundled into Machine Price</option>
      </select>
      <div style="font-size:11px;color:#64748b;margin-top:4px;">
        Choose whether enhancement prices appear individually or are bundled into the machine package price.
      </div>
    </div>
    <div id="enhList"></div>

    <h2 class="sec">Freight &amp; Delivery Destination</h2>
    <div style="margin-bottom:8px; font-size:11.5px; color:#475569;">
      Select the customer delivery state and city from your assigned coverage territory:
    </div>

    <div class="row2">
      <div>
        <label>Delivery State / Territory</label>
        <select id="destState" {{ $isReadonly ? 'disabled' : '' }}>
          <option value="">&mdash; Select State &mdash;</option>
        </select>
      </div>
      <div>
        <label>Delivery City / Destination</label>
        <select id="destCity" {{ $isReadonly ? 'disabled' : '' }}>
          <option value="">&mdash; Select City &mdash;</option>
        </select>
      </div>
    </div>

    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px; margin-top:10px; margin-bottom:10px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
            <span style="font-size:11px; font-weight:700; color:#1e293b; text-transform:uppercase; letter-spacing:0.5px;">Selected Delivery Destination</span>
            <span id="freightStatusBadge" style="font-size:11px; padding:2px 8px; border-radius:10px; font-weight:700; background:#dcfce7; color:#15803d;">Matrix Linked</span>
        </div>
        <div id="freightDestDisplay" style="font-size:13px; font-weight:600; color:#0f172a;">
            Loading destination...
        </div>
        <div id="freightBasisDisplay" style="font-size:11.5px; color:#64748b; margin-top:3px;">
            <!-- Rate type & price -->
        </div>
    </div>

    <!-- Hidden compatibility input -->
    <input type="hidden" id="repTerritory" value="">

    <label style="margin-top:10px;display:block;">Sweeper Transport / Freight Estimate (USD) <span class="smallnote">automatically populated from Freight Matrix</span></label>
    <input type="number" id="freight" value="0" min="0" readonly>
    <div id="exwFreightNote" style="display:none;color:#666;font-size:11px;margin-top:4px;font-style:italic;">FOB is set to {{ $exwFob }} &mdash; freight is $0.00.</div>

    <!-- Critical Freight Rate Error Banner (Prevents quotation generation) -->
    <div id="freightErrorBanner" style="display:none; background:#fee2e2; border:1px solid #fca5a5; border-radius:6px; padding:10px 12px; margin-top:10px; color:#991b1b; font-size:12px; line-height:1.45;">
        <div style="font-weight:700; margin-bottom:2px;">⚠️ Freight Rate Error &mdash; Quote Generation Disabled</div>
        <span id="freightErrorText">A valid freight rate could not be found for this sales representative's assigned delivery destination in the Freight Matrix.</span>
    </div>

  </div>

  <!-- Right Quotation Paper Preview Panel -->
  <div class="previewWrap">
    <div id="preview"></div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
const CATALOG = @json($catalog);
const INITIAL_STATE = @json($initialState);
const IS_READONLY = @json($isReadonly);
const CURRENT_QUOTE_ID = @json($quote?->id);
const SALES_REPS = @json($salesReps);
const IS_ADMIN = @json($user ? $user->isAdmin() : false);
const CURRENT_USER_NAME = @json($user ? $user->name : '');
const QUOTE_OWNER_NAME = @json($quoteOwnerName ?? ($quote?->user?->name ?: ''));

let ITEMS = CATALOG.items || [];
let CHASSIS = CATALOG.chassis || [];
let CHASSIS_BY_MODEL = CATALOG.chassis_by_model || {};
let CHASSIS_SHORT = Object.assign({
  "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper": "MT4H Isuzu cabover, Class 5, non-CDL - LHD",
  "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - RIGHT side dumping hopper": "MT4H Isuzu cabover, Class 5, non-CDL - RHD",
  "MT4H - Ford F-550XL conventional, 19,000 GVWR, non-CDL - LEFT side dumping hopper": "MT4H Ford F-550XL, non-CDL - LHD",
  "MS5H - Conventional Class 6, 25,500 GVWR, 148\" WB, 300HP Cummins, non-CDL": "MS5H Conventional Class 6, non-CDL",
  "MS5H - Conventional Class 7, CDL-required configuration, 300HP Cummins - no price difference": "MS5H Conventional Class 7, CDL-required"
}, CATALOG.chassis_short || {});

let FREIGHT_ESTIMATES = CATALOG.freight || [];
let TERRITORIES = CATALOG.territories || [];
let QUOTE_TEMPLATE = CATALOG.quote_template || {};
let TEMPLATE_HEADER = QUOTE_TEMPLATE.header || {};
let TEMPLATE_LEGAL = QUOTE_TEMPLATE.legal || {};
let MODEL_TEMPLATES = CATALOG.model_templates || {};

const SOURCEWELL_LOGO = "{{ asset('images/logo-sourcewell.png') }}";
const HGAC_LOGO = "{{ asset('images/logo-hgac.png') }}";
const BUYBOARD_LOGO = "{{ asset('images/logo-buyboard.png') }}";

const LOGO_MAP = {
  'sourcewell': SOURCEWELL_LOGO,
  'hgac': HGAC_LOGO,
  'buyboard': BUYBOARD_LOGO
};

// Build dynamic Pricing Basis lookup table from Admin configuration + fallback defaults
const PRICING_BASES_MAP = {};
const DEFAULT_BASES = [
  {
    key: 'MSRP (Commercial)',
    label: 'MSRP (Commercial)',
    slug: 'msrp',
    price_tier: 'msrp',
    subtotal_label: 'MSRP Subtotal',
    disclaimer: '',
    logo_key: null,
    caption: '',
    is_coop: false,
    use_municipality_freight: false
  },
  {
    key: 'Sourcewell',
    label: 'Sourcewell',
    slug: 'sourcewell',
    price_tier: 'sw_net',
    subtotal_label: 'Sourcewell Contract Price',
    disclaimer: 'Quoted under cooperative contract: Sourcewell #062425-ODRA',
    logo_key: 'sourcewell',
    caption: 'Referenced under Sourcewell<br>Contract # 062425-ODRA',
    is_coop: true,
    use_municipality_freight: true
  },
  {
    key: 'HGAC',
    label: 'H-GAC / HGACBuy',
    slug: 'hgac',
    price_tier: 'sw_net',
    subtotal_label: 'H-GAC Contract Price',
    disclaimer: 'Quoted under cooperative contract: H-GAC/HGACBuy Master Agreement SW04-26',
    logo_key: 'hgac',
    caption: 'Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26',
    is_coop: true,
    use_municipality_freight: true
  },
  {
    key: 'Minnesota',
    label: 'State of Minnesota',
    slug: 'minnesota',
    price_tier: 'sw_net',
    subtotal_label: 'Minnesota Contract Price',
    disclaimer: 'Quoted under cooperative contract: State of Minnesota Contract 285158, Release S-843(5)',
    logo_key: null,
    caption: 'Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))',
    is_coop: true,
    use_municipality_freight: true
  },
  {
    key: 'Buyboard',
    label: 'BuyBoard',
    slug: 'buyboard',
    price_tier: 'sw_net',
    subtotal_label: 'BuyBoard Contract Price',
    disclaimer: 'Quoted under cooperative contract: ODRA BuyBoard Contract #788-25',
    logo_key: 'buyboard',
    caption: 'Referenced under BuyBoard<br>ODRA Contract # 788-25',
    is_coop: true,
    use_municipality_freight: true
  }
];

const activeBasesList = (CATALOG.pricing_bases && CATALOG.pricing_bases.length)
  ? CATALOG.pricing_bases
  : DEFAULT_BASES;

activeBasesList.forEach(b => {
  const key = b.key || b.name || b.label;
  const label = b.label || b.name || key;
  const slug = (b.slug || '').toLowerCase();
  const tier = b.price_tier || b.price_field || (b.is_coop ? 'sw_net' : 'msrp');
  const isCoop = b.is_coop !== undefined ? !!b.is_coop : (tier === 'sw_net');
  const logoUrl = b.logo_key ? (LOGO_MAP[String(b.logo_key).toLowerCase()] || null) : null;
  const discPct = (b.discount_percent !== undefined && b.discount_percent !== null && !isNaN(b.discount_percent))
    ? Number(b.discount_percent)
    : (isCoop ? 12.00 : 0.00);
  const entry = {
    key: key,
    label: label,
    slug: slug,
    priceTier: tier,
    isCoop: isCoop,
    discountPercent: discPct,
    descriptionTemplate: b.description_template || b.caption || '',
    subtotalLabel: b.subtotal_label || (isCoop ? (label + ' Contract Price') : 'MSRP Subtotal'),
    disclaimer: b.disclaimer || '',
    logo: logoUrl,
    caption: b.caption || '',
    useMunicipalityFreight: b.use_municipality_freight !== undefined ? !!b.use_municipality_freight : isCoop
  };
  PRICING_BASES_MAP[key] = entry;
  PRICING_BASES_MAP[key.toLowerCase()] = entry;
  PRICING_BASES_MAP[label] = entry;
  PRICING_BASES_MAP[label.toLowerCase()] = entry;
  if (slug) PRICING_BASES_MAP[slug] = entry;
});
// Common aliases from Catalog showcase
if (PRICING_BASES_MAP['msrp']) {
  PRICING_BASES_MAP['msrp commercial'] = PRICING_BASES_MAP['msrp'];
  PRICING_BASES_MAP['commercial'] = PRICING_BASES_MAP['msrp'];
}

function getBasesForModel(m) {
  const modelCode = (m || (typeof state !== 'undefined' ? state.model : '') || '').toUpperCase();
  if (MODEL_TEMPLATES[modelCode] && Array.isArray(MODEL_TEMPLATES[modelCode].pricing_bases) && MODEL_TEMPLATES[modelCode].pricing_bases.length) {
    return MODEL_TEMPLATES[modelCode].pricing_bases;
  }
  return activeBasesList;
}

function getLegalForModel(m) {
  const modelCode = (m || (typeof state !== 'undefined' ? state.model : '') || '').toUpperCase();
  if (MODEL_TEMPLATES[modelCode] && MODEL_TEMPLATES[modelCode].legal) {
    return MODEL_TEMPLATES[modelCode].legal;
  }
  return TEMPLATE_LEGAL;
}

function resolveBasis(basisVal, modelCode) {
  const mCode = modelCode || (typeof state !== 'undefined' ? state.model : null);
  const chassisStr = typeof state !== 'undefined' ? state.chassis : null;
  const searchVal = String(basisVal || '').trim().toLowerCase();

  // 1. Check configuration-specific pricing bases first
  if (mCode && chassisStr && CATALOG.configurations_data && CATALOG.configurations_data[mCode] && CATALOG.configurations_data[mCode][chassisStr]) {
    const cfgData = CATALOG.configurations_data[mCode][chassisStr];
    const cfgBases = cfgData.pricing_bases_list || Object.values(cfgData.pricing_bases || {});
    if (cfgBases && cfgBases.length > 0) {
      let foundCfg = null;
      if (searchVal) {
        foundCfg = cfgBases.find(b => {
          const k = String(b.key || b.name || b.label || '').toLowerCase().trim();
          const l = String(b.label || b.name || '').toLowerCase().trim();
          const s = String(b.slug || '').toLowerCase().trim();
          return k === searchVal || l === searchVal || s === searchVal;
        });
      } else {
        foundCfg = cfgBases[0];
      }

      if (foundCfg) {
        const key = foundCfg.key || foundCfg.name || foundCfg.label;
        const label = foundCfg.label || foundCfg.name || key;
        const tier = foundCfg.price_tier || foundCfg.price_field || (foundCfg.is_coop ? 'sw_net' : 'msrp');
        const isCoop = foundCfg.is_coop !== undefined ? !!foundCfg.is_coop : (tier === 'sw_net');
        let logoUrl = null;
        if (foundCfg.logo_key && foundCfg.logo_key !== 'none') {
          logoUrl = (CATALOG.available_logos && CATALOG.available_logos[foundCfg.logo_key])
            ? CATALOG.available_logos[foundCfg.logo_key].url
            : (LOGO_MAP[String(foundCfg.logo_key).toLowerCase()] || foundCfg.logo_key);
        }
        const msrp = Number(foundCfg.msrp) || 0;
        const discPct = Number(foundCfg.discount_percent) || 0;
        const calcPrice = (foundCfg.calculated_price !== undefined && foundCfg.calculated_price !== null && Number(foundCfg.calculated_price) > 0)
          ? Number(foundCfg.calculated_price)
          : Math.round(msrp * (1 - (discPct / 100)));

        return {
          key: key,
          label: label,
          slug: foundCfg.slug || String(key).toLowerCase(),
          priceTier: tier,
          isCoop: isCoop,
          msrp: msrp,
          discountPercent: discPct,
          calculatedPrice: calcPrice,
          descriptionTemplate: foundCfg.description_template || foundCfg.caption || '',
          subtotalLabel: foundCfg.subtotal_label || (isCoop ? (label + ' Contract Price') : 'MSRP Subtotal'),
          disclaimer: foundCfg.disclaimer || '',
          logo: logoUrl,
          caption: foundCfg.caption || '',
          useMunicipalityFreight: foundCfg.use_municipality_freight !== undefined ? !!foundCfg.use_municipality_freight : isCoop,
          standardFeatures: foundCfg.standard_features || []
        };
      }
    }
  }

  // 2. Fallback to model/global pricing bases
  const mBases = getBasesForModel(mCode);

  if (searchVal) {
    const found = mBases.find(b => {
      const k = String(b.key || b.name || b.label || '').toLowerCase().trim();
      const l = String(b.label || b.name || '').toLowerCase().trim();
      const s = String(b.slug || '').toLowerCase().trim();
      return k === searchVal || l === searchVal || s === searchVal;
    });

    if (found) {
      const key = found.key || found.name || found.label;
      const label = found.label || found.name || key;
      const slug = (found.slug || '').toLowerCase();
      const tier = found.price_tier || found.price_field || (found.is_coop ? 'sw_net' : 'msrp');
      const isCoop = found.is_coop !== undefined ? !!found.is_coop : (tier === 'sw_net');
      let logoUrl = null;
      if (found.logo_key && found.logo_key !== 'none') {
        logoUrl = (CATALOG.available_logos && CATALOG.available_logos[found.logo_key])
          ? CATALOG.available_logos[found.logo_key].url
          : (LOGO_MAP[String(found.logo_key).toLowerCase()] || found.logo_key);
      }
      const discPct = (found.discount_percent !== undefined && found.discount_percent !== null && !isNaN(found.discount_percent))
        ? Number(found.discount_percent)
        : (isCoop ? 12.00 : 0.00);
      return {
        key: key,
        label: label,
        slug: slug,
        priceTier: tier,
        isCoop: isCoop,
        discountPercent: discPct,
        descriptionTemplate: found.description_template || found.caption || '',
        subtotalLabel: found.subtotal_label || (isCoop ? (label + ' Contract Price') : 'MSRP Subtotal'),
        disclaimer: found.disclaimer || '',
        logo: logoUrl,
        caption: found.caption || '',
        useMunicipalityFreight: found.use_municipality_freight !== undefined ? !!found.use_municipality_freight : isCoop,
        standardFeatures: found.standard_features || []
      };
    }
  }

  if (!basisVal) {
    if (mBases && mBases.length > 0) {
      const firstB = mBases[0];
      return resolveBasis(firstB.key || firstB.name || firstB.label, mCode);
    }
    return PRICING_BASES_MAP['MSRP (Commercial)'] || activeBasesList[0];
  }

  return PRICING_BASES_MAP[basisVal]
    || PRICING_BASES_MAP[String(basisVal).toLowerCase()]
    || {
      key: basisVal,
      label: basisVal,
      priceTier: 'msrp',
      isCoop: false,
      discountPercent: 0,
      descriptionTemplate: '',
      subtotalLabel: 'MSRP Subtotal',
      disclaimer: '',
      logo: null,
      caption: '',
      useMunicipalityFreight: false
    };
}

const SR_CODES = Object.assign({
  "Bud Earp":"BE", "Carlos Silva":"CS", "Chris Norlin":"CN", "Fernando Oliveira":"FO",
  "Jason Coover":"JC", "John Wells":"JW", "Juan Oliver":"JO", "Kra Whitmore":"KW",
  "Marcin Strojny":"MS", "Paul Piotrowski":"PP", "Robert Yuska":"RY", "Ryan Khosrofian":"RK",
  "Sales Management":"SM", "Sean Chenoweth":"SC", "Shawn Herringer":"SH", "Ted Bartolo":"TB",
  "Troy Fisher":"TF", "Alex Byczynski":"AB"
}, CATALOG.sr_codes || {});

let state = {
  date: todayISO(),
  model: (CATALOG.models && CATALOG.models[0] ? CATALOG.models[0].code : 'MT4H'),
  basis: (activeBasesList[0] ? (activeBasesList[0].key || activeBasesList[0].name) : 'MSRP (Commercial)'),
  chassis: '',
  po: '',
  fob: TEMPLATE_HEADER.default_fob || 'Transport, estimate only noted in quote',
  acct: '',
  salesRep: '',
  toContact: '',
  toCompany: '',
  toAddr1: '',
  toAddr2: '',
  baseQty: 1,
  freight: 0,
  baseShowDiscount: 0,
  destState: '',
  destCity: '',
  repTerritory: '',
  quoteSeq: '3001',
  enhancementPricingMode: 'itemized',
  enh: Array.from({length: 13}, () => ({desc: '', qty: 1}))
};

// Safely merge initial state
if (INITIAL_STATE && typeof INITIAL_STATE === 'object') {
  Object.assign(state, INITIAL_STATE);
}

// For new quotations created by non-admins, default salesRep to the logged-in user.
// Existing quotations MUST retain their original creator identity permanently.
if (!CURRENT_QUOTE_ID) {
  if (!IS_ADMIN && CURRENT_USER_NAME) {
    state.salesRep = CURRENT_USER_NAME;
  }
} else {
  state.salesRep = QUOTE_OWNER_NAME || state.salesRep || (INITIAL_STATE && INITIAL_STATE.salesRep) || '';
}

// Normalize state.basis to canonical key
if (state.basis) {
  const resolved = resolveBasis(state.basis);
  if (resolved && resolved.key) {
    state.basis = resolved.key;
  }
}

// Support pre-selecting model, chassis, pricing basis, and enhancements from Catalog showcase URL
const urlParams = new URLSearchParams(window.location.search);
if (!CURRENT_QUOTE_ID) {
  if (urlParams.has('model')) {
    const paramModel = urlParams.get('model').trim().toUpperCase();
    const knownModels = (CATALOG.models || []).map(m => m.code.toUpperCase());
    if (knownModels.includes(paramModel) || ITEMS.some(i => i.model === paramModel)) {
      state.model = paramModel;
      state.chassis = '';
    }
  }
  if (urlParams.has('chassis')) {
    state.chassis = urlParams.get('chassis');
  }
  if (urlParams.has('basis')) {
    const resolvedParamBasis = resolveBasis(urlParams.get('basis'));
    if (resolvedParamBasis && resolvedParamBasis.key) {
      state.basis = resolvedParamBasis.key;
    }
  }
  if (urlParams.has('enhancements')) {
    const optionsList = urlParams.get('enhancements').split('||').map(s => s.trim()).filter(Boolean);
    state.enh = Array.from({length: 13}, () => ({desc: '', qty: 1}));
    optionsList.forEach((opt, idx) => {
      if (idx < 13) {
        state.enh[idx] = { desc: opt, qty: 1 };
      }
    });
  }
}

// Guarantee enh is always an array of 13 rows
if (!state.enh || !Array.isArray(state.enh)) {
  state.enh = Array.from({length: 13}, () => ({desc: '', qty: 1}));
}
while (state.enh.length < 13) {
  state.enh.push({desc: '', qty: 1});
}

function shortChassisLabel(c) {
  return CHASSIS_SHORT[c] || c || 'Standard Configuration';
}

function todayISO() {
  const d = new Date();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

function fmtDateDisplay(iso) {
  if (!iso) return '';
  const parts = String(iso).split('-');
  if (parts.length !== 3) return iso;
  const [y, m, d] = parts;
  const dt = new Date(Number(y), Number(m) - 1, Number(d));
  if (isNaN(dt.getTime())) return iso;
  const day = String(dt.getDate()).padStart(2, '0');
  const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
  const month = monthNames[dt.getMonth()];
  const year = dt.getFullYear();
  return `${day}-${month}-${year}`;
}

function fmt(n) {
  if (n === null || n === undefined || isNaN(n)) return '';
  return Number(n).toLocaleString('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fmtMSRP(n) {
  if (n === null || n === undefined || isNaN(n)) return '';
  const num = Number(n);
  if (Math.floor(num) === num) {
    return num.toLocaleString('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
  }
  return num.toLocaleString('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function pctFmt(n) {
  if (n === null || n === undefined || isNaN(n) || Math.abs(n) < 0.0005) return '';
  const val = Math.round(n * 1000) / 10;
  return (val % 1 === 0 ? val.toFixed(0) : val.toFixed(1)) + '%';
}

function esc(s) {
  return (s === undefined || s === null) ? '' : String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function itemsForModel(m) {
  return ITEMS.filter(i => i.model === m);
}

function baseItem(m) {
  return itemsForModel(m).find(i => i.isBase) || { desc: '', msrp: 0, swList: 0, swNet: 0 };
}

function enhOptions(m) {
  return itemsForModel(m).filter(i => !i.isBase);
}

function chassisForModel(m) {
  if (CHASSIS_BY_MODEL && Array.isArray(CHASSIS_BY_MODEL[m]) && CHASSIS_BY_MODEL[m].length) {
    return CHASSIS_BY_MODEL[m];
  }
  const filtered = CHASSIS.filter(c => c.indexOf(m) === 0);
  return filtered.length ? filtered : [m + ' - Standard Commercial Truck Chassis Configuration'];
}

function priceForItem(item, basisOverride) {
  if (!item) return { list: 0, unit: 0, msrp: 0, discountPercent: 0 };
  const basisInfo = basisOverride ? resolveBasis(basisOverride) : resolveBasis(state.basis);
  const isCoop = !!(basisInfo && (basisInfo.isCoop || basisInfo.priceTier === 'sw_net'));

  if (isCoop) {
    if (item.isBase) {
      // BASE EQUIPMENT / SWEEPER MACHINE
      const msrp = (basisInfo && basisInfo.msrp > 0) ? basisInfo.msrp : (Number(item.swList) || Number(item.msrp) || 0);
      let discPct = (basisInfo && basisInfo.discountPercent !== undefined && basisInfo.discountPercent !== null)
        ? Number(basisInfo.discountPercent)
        : 12.00;
      if (item.discount_percent !== undefined && item.discount_percent !== null && !isNaN(item.discount_percent)) {
        discPct = Number(item.discount_percent);
      }
      // Unit Price = calculatedPrice if present or MSRP * (1 - Discount / 100)
      const unitPrice = (basisInfo && basisInfo.calculatedPrice > 0)
        ? basisInfo.calculatedPrice
        : Math.round((msrp * (1 - (discPct / 100))) * 100) / 100;
      return {
        list: msrp,
        msrp: msrp,
        unit: unitPrice,
        discountPercent: discPct
      };
    } else {
      // ENHANCEMENT / OPTION ITEM:
      // Uses the enhancement's own configured contract pricing and discount.
      // Must NEVER inherit the base machine's 12% or 15% discount!
      const list = Number(item.swList) || Number(item.msrp) || 0;
      let unit = Number(item.swNet);
      let discPct = 0;

      if (item.discount_percent !== undefined && item.discount_percent !== null && !isNaN(item.discount_percent) && Number(item.discount_percent) >= 0) {
        discPct = Number(item.discount_percent);
        unit = Math.round((list * (1 - (discPct / 100))) * 100) / 100;
      } else if (!isNaN(unit) && unit > 0 && list > 0) {
        discPct = (list >= unit) ? Math.round((1 - (unit / list)) * 1000) / 10 : 0;
      } else {
        unit = list;
        discPct = 0;
      }

      return {
        list: list,
        msrp: Number(item.msrp) || list,
        unit: unit,
        discountPercent: discPct
      };
    }
  }

  // Commercial / MSRP (Discount is 0 / Hidden)
  const msrp = (item.isBase && basisInfo && basisInfo.msrp > 0) ? basisInfo.msrp : (Number(item.msrp) || 0);
  const unit = (item.isBase && basisInfo && basisInfo.calculatedPrice > 0) ? basisInfo.calculatedPrice : msrp;
  return {
    list: msrp,
    msrp: msrp,
    unit: unit,
    discountPercent: 0
  };
}

function dynamicBaseDesc(base, chassisStr, model, basisVal) {
  const mCode = model || (typeof state !== 'undefined' ? state.model : '');
  const cStr = chassisStr || (typeof state !== 'undefined' ? state.chassis : '');
  const cfgData = (mCode && cStr && CATALOG.configurations_data) ? CATALOG.configurations_data?.[mCode]?.[cStr] : null;

  let desc = cfgData?.description || (base && base.desc ? base.desc : '');
  if (!desc) {
    desc = mCode === 'MS5H' ? MS5H_BASE_FALLBACK_DESC : MT4H_BASE_FALLBACK_DESC;
  }
  if (!cfgData?.description && cStr) {
    const sideMatch = cStr.match(/(LEFT|RIGHT) side dumping hopper/i);
    if (sideMatch) {
      const side = /RIGHT/i.test(sideMatch[1]) ? 'Right' : 'Left';
      desc = desc.replace(/(Left|Right)-side dump sweeper/i, `${side}-side dump sweeper`);
    }
    if (/Ford F-550XL/i.test(cStr)) {
      desc = desc.replace(/Isuzu NRR/gi, 'Ford F-550XL');
      desc = desc.replace(/19,500/gi, '19,000');
    } else if (/Isuzu NRR/i.test(cStr)) {
      desc = desc.replace(/Ford F-550XL/gi, 'Isuzu NRR');
    }
    if (/Class 7|CDL-required/i.test(cStr)) {
      desc = desc.replace(/non-CDL,\s*148"\s*WB\s*Class\s*6/i, 'CDL-required, 148" WB Class 7');
    }
  }

  // Automatically append contract reference description note when pricing basis changes
  const basisInfo = resolveBasis(basisVal || state.basis, mCode);
  if (basisInfo && basisInfo.isCoop) {
    const rawNote = basisInfo.descriptionTemplate || basisInfo.caption || basisInfo.disclaimer || '';
    if (rawNote) {
      const cleanNote = rawNote.replace(/<br\s*\/?>/gi, ' ').replace(/<[^>]*>/g, '').trim();
      if (cleanNote && !desc.includes(cleanNote)) {
        desc += `\n[${cleanNote}]`;
      }
    }
  }

  return desc;
}

function populateChassis() {
  const sel = document.getElementById('chassis');
  if (!sel) return;
  const opts = chassisForModel(state.model);
  const keep = opts.includes(state.chassis) ? state.chassis : (opts[0] || '');
  sel.innerHTML = opts.map(c => {
    const rawPrice = (CATALOG.chassis_prices && CATALOG.chassis_prices[c]) ? Number(CATALOG.chassis_prices[c]) : 0;
    let label = shortChassisLabel(c);
    if (rawPrice > 0) {
      label += ` (+${fmt(rawPrice)})`;
    }
    return `<option value="${esc(c)}" ${c === keep ? 'selected' : ''}>${esc(label)}</option>`;
  }).join('');
  state.chassis = keep;
}

let freightValid = true;

function getSelectedRep() {
  const repName = (state.salesRep || '').trim();
  if (!repName) return null;
  return SALES_REPS.find(r => r.name === repName || (r.name && r.name.toLowerCase() === repName.toLowerCase())) || null;
}

function getAvailableStatesForRep(rep) {
  if (rep && Array.isArray(rep.assigned_states) && rep.assigned_states.length > 0) {
    return rep.assigned_states;
  }
  if (rep && rep.state) {
    return [rep.state];
  }
  // Fallback (for Admins or reps without restrictions): all distinct states in FREIGHT_ESTIMATES
  const allStates = [...new Set(FREIGHT_ESTIMATES.map(f => f.state).filter(Boolean))].sort();
  return allStates;
}

function getCitiesForState(stateName) {
  if (!stateName) return [];
  const sName = String(stateName).trim().toLowerCase();
  return FREIGHT_ESTIMATES.filter(f => String(f.state).trim().toLowerCase() === sName);
}

function populateDestStateDropdown() {
  const stateSel = document.getElementById('destState');
  if (!stateSel) return;

  const rep = getSelectedRep();
  const availableStates = getAvailableStatesForRep(rep);

  stateSel.innerHTML = '';
  if (!availableStates.length) {
    stateSel.innerHTML = '<option value="">&mdash; No Assigned States &mdash;</option>';
    populateDestCityDropdown();
    return;
  }

  let selectedState = state.destState;
  if (!selectedState || !availableStates.includes(selectedState)) {
    if (rep && rep.state && availableStates.includes(rep.state)) {
      selectedState = rep.state;
    } else {
      selectedState = availableStates[0];
    }
  }
  state.destState = selectedState;

  availableStates.forEach(st => {
    const opt = document.createElement('option');
    opt.value = st;
    opt.textContent = st;
    if (st === selectedState) opt.selected = true;
    stateSel.appendChild(opt);
  });

  populateDestCityDropdown();
}

function populateDestCityDropdown() {
  const citySel = document.getElementById('destCity');
  if (!citySel) return;

  citySel.innerHTML = '';
  const cities = getCitiesForState(state.destState);

  if (!cities.length) {
    citySel.innerHTML = '<option value="">&mdash; No Destinations Found &mdash;</option>';
    state.destCity = '';
    return;
  }

  const rep = getSelectedRep();
  let selectedCity = state.destCity;
  const cityNames = cities.map(c => c.city);

  if (!selectedCity || !cityNames.includes(selectedCity)) {
    if (rep && rep.city && cityNames.includes(rep.city)) {
      selectedCity = rep.city;
    } else {
      selectedCity = cities[0].city;
    }
  }
  state.destCity = selectedCity;

  cities.forEach(c => {
    const opt = document.createElement('option');
    opt.value = c.city;
    const commRate = Number(c.customer || 0).toLocaleString();
    const coopRate = Number(c.municipality || 0).toLocaleString();
    opt.textContent = `${c.city} (Comm: $${commRate} | Co-op: $${coopRate})`;
    if (c.city === selectedCity) opt.selected = true;
    citySel.appendChild(opt);
  });
}

function getSelectedFreightRule() {
  if (!state.destState || !state.destCity) return null;
  const sName = String(state.destState).trim().toLowerCase();
  const cName = String(state.destCity).trim().toLowerCase();

  return FREIGHT_ESTIMATES.find(r => 
    String(r.state).trim().toLowerCase() === sName &&
    String(r.city).trim().toLowerCase() === cName
  ) || null;
}

function applyRepFreight() {
  const freightEl = document.getElementById('freight');
  const exwNoteEl = document.getElementById('exwFreightNote');
  const errorBanner = document.getElementById('freightErrorBanner');
  const errorText = document.getElementById('freightErrorText');
  const destDisplay = document.getElementById('freightDestDisplay');
  const basisDisplay = document.getElementById('freightBasisDisplay');
  const statusBadge = document.getElementById('freightStatusBadge');
  const saveDraftBtn = document.getElementById('btnSaveDraft');
  const finalizeBtn = document.getElementById('btnFinalize');

  const exwFobLabel = TEMPLATE_HEADER.exw_fob || 'EXW, Factory pick-up';
  const isEXW = (state.fob === exwFobLabel || state.fob === 'EXW, Factory pick-up');

  const rep = getSelectedRep();
  const basisInfo = resolveBasis(state.basis);
  const useMunicipality = !!(basisInfo && basisInfo.useMunicipalityFreight);

  if (isEXW) {
    state.freight = 0;
    if (freightEl) freightEl.value = 0;
    if (exwNoteEl) exwNoteEl.style.display = 'block';
    if (errorBanner) errorBanner.style.display = 'none';
    if (statusBadge) {
      statusBadge.style.background = '#e2e8f0';
      statusBadge.style.color = '#475569';
      statusBadge.textContent = 'EXW (Pick-up)';
    }
    if (destDisplay) {
      destDisplay.textContent = 'Factory Pick-up (Freight $0.00)';
    }
    if (basisDisplay) {
      basisDisplay.textContent = 'Customer arranged transport';
    }
    freightValid = true;
    if (saveDraftBtn) { saveDraftBtn.disabled = false; saveDraftBtn.style.opacity = '1'; saveDraftBtn.style.cursor = 'pointer'; }
    if (finalizeBtn) { finalizeBtn.disabled = false; finalizeBtn.style.opacity = '1'; finalizeBtn.style.cursor = 'pointer'; }
    return;
  }

  if (exwNoteEl) exwNoteEl.style.display = 'none';

  if (!rep) {
    if (destDisplay) destDisplay.textContent = 'Please select a Sales Representative above';
    if (basisDisplay) basisDisplay.textContent = '';
    if (statusBadge) {
      statusBadge.style.background = '#fef3c7';
      statusBadge.style.color = '#92400e';
      statusBadge.textContent = 'Rep Required';
    }
    if (errorBanner) {
      errorBanner.style.display = 'block';
      if (errorText) errorText.textContent = 'Please select a Sales Representative to calculate freight from the Freight Matrix.';
    }
    state.freight = 0;
    if (freightEl) freightEl.value = 0;
    freightValid = false;
    if (saveDraftBtn) { saveDraftBtn.disabled = true; saveDraftBtn.style.opacity = '0.5'; saveDraftBtn.style.cursor = 'not-allowed'; }
    if (finalizeBtn) { finalizeBtn.disabled = true; finalizeBtn.style.opacity = '0.5'; finalizeBtn.style.cursor = 'not-allowed'; }
    return;
  }

  const rule = getSelectedFreightRule();
  if (!rule) {
    const locText = (state.destCity || state.destState) ? `${state.destCity || ''}, ${state.destState || ''}` : 'Unselected destination';
    if (destDisplay) destDisplay.textContent = locText;
    if (basisDisplay) basisDisplay.textContent = 'Please select a delivery state and city destination from the Freight Matrix';
    if (statusBadge) {
      statusBadge.style.background = '#fee2e2';
      statusBadge.style.color = '#991b1b';
      statusBadge.textContent = 'Destination Required';
    }
    if (errorBanner) {
      errorBanner.style.display = 'block';
      if (errorText) errorText.textContent = `No valid freight rate found for destination "${locText}" in the Freight Matrix. Quotation generation is disabled.`;
    }
    state.freight = 0;
    if (freightEl) freightEl.value = 0;
    freightValid = false;
    if (saveDraftBtn) { saveDraftBtn.disabled = true; saveDraftBtn.style.opacity = '0.5'; saveDraftBtn.style.cursor = 'not-allowed'; }
    if (finalizeBtn) { finalizeBtn.disabled = true; finalizeBtn.style.opacity = '0.5'; finalizeBtn.style.cursor = 'not-allowed'; }
    return;
  }

  // Rate lookup based on pricing basis
  const rate = useMunicipality ? (Number(rule.municipality) || 0) : (Number(rule.customer) || 0);
  const rateLabel = useMunicipality ? 'Municipality Rate (Cooperative)' : 'Customer Rate (Commercial)';

  if (rate <= 0) {
    if (destDisplay) destDisplay.textContent = `${rule.city}, ${rule.state}`;
    if (basisDisplay) basisDisplay.textContent = `${rateLabel}: $0.00`;
    if (statusBadge) {
      statusBadge.style.background = '#fee2e2';
      statusBadge.style.color = '#991b1b';
      statusBadge.textContent = '$0 Rate Error';
    }
    if (errorBanner) {
      errorBanner.style.display = 'block';
      if (errorText) errorText.textContent = `The freight rate for ${rule.city}, ${rule.state} is $0.00 in the Freight Matrix. Quotation generation is disabled.`;
    }
    state.freight = 0;
    if (freightEl) freightEl.value = 0;
    freightValid = false;
    if (saveDraftBtn) { saveDraftBtn.disabled = true; saveDraftBtn.style.opacity = '0.5'; saveDraftBtn.style.cursor = 'not-allowed'; }
    if (finalizeBtn) { finalizeBtn.disabled = true; finalizeBtn.style.opacity = '0.5'; finalizeBtn.style.cursor = 'not-allowed'; }
    return;
  }

  // All valid!
  freightValid = true;
  state.freight = rate;
  state.repTerritory = `${rule.city}, ${rule.state}`;
  if (freightEl) freightEl.value = rate;
  if (errorBanner) errorBanner.style.display = 'none';
  if (statusBadge) {
    statusBadge.style.background = '#dcfce7';
    statusBadge.style.color = '#15803d';
    statusBadge.textContent = 'Matrix Linked';
  }
  if (destDisplay) {
    destDisplay.innerHTML = `<strong>${esc(rule.city)}</strong>, ${esc(rule.state)}`;
  }
  if (basisDisplay) {
    basisDisplay.innerHTML = `<strong>${rateLabel}:</strong> ${fmt(rate)}`;
  }
  if (saveDraftBtn) { saveDraftBtn.disabled = false; saveDraftBtn.style.opacity = '1'; saveDraftBtn.style.cursor = 'pointer'; }
  if (finalizeBtn) { finalizeBtn.disabled = false; finalizeBtn.style.opacity = '1'; finalizeBtn.style.cursor = 'pointer'; }
}

function quoteCodePrefix() {
  const srCode = SR_CODES[state.salesRep] || (state.salesRep ? state.salesRep.slice(0, 2).toUpperCase() : '');
  let ddmmyyyy = '';
  if (state.date) {
    const parts = state.date.split('-');
    if (parts.length === 3) {
      ddmmyyyy = `${parts[2]}${parts[1]}${parts[0]}`;
    }
  }
  return `${srCode}${ddmmyyyy}`;
}

function currentQuoteCode() {
  return `${quoteCodePrefix()}${state.quoteSeq || ''}`;
}

function standardInclusionsHtml(model) {
  const mCode = model || (typeof state !== 'undefined' ? state.model : '');
  const cfgData = (mCode && typeof state !== 'undefined' && state.chassis && CATALOG.configurations_data)
    ? CATALOG.configurations_data?.[mCode]?.[state.chassis]
    : null;
  const basisKey = typeof state !== 'undefined' ? state.basis : null;
  let customFeatures = null;

  if (cfgData && basisKey) {
    const basisObj = cfgData.pricing_bases?.[basisKey] || (cfgData.pricing_bases_list || []).find(b => (b.label || b.name || b.key) === basisKey);
    if (basisObj && Array.isArray(basisObj.standard_features) && basisObj.standard_features.length > 0) {
      customFeatures = basisObj.standard_features;
    }
  }

  if (customFeatures && customFeatures.length > 0) {
    return customFeatures.map(f => `<p>${f}</p>`).join('\n  ');
  }

  const legal = getLegalForModel(model);
  const p1 = legal.standard_features_p1 || TEMPLATE_LEGAL.standard_features_p1 || '<b>Standard Features:</b> ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.';
  const p2 = legal.standard_features_p2 || TEMPLATE_LEGAL.standard_features_p2 || 'Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled from inside the cab.';
  const p3 = legal.standard_features_p3 || TEMPLATE_LEGAL.standard_features_p3 || 'Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control.';
  const pm10Text = legal.pm10_note !== undefined ? legal.pm10_note : (TEMPLATE_LEGAL.pm10_note !== undefined ? TEMPLATE_LEGAL.pm10_note : 'PM-10 compliant (SCAQMD Rule 1186).');
  const pm10 = (model === 'MS5H' || !pm10Text || !pm10Text.trim()) ? '' : (' ' + pm10Text.trim());
  return `<p>${p1}</p>
  <p>${p2}</p>
  <p>${p3}${pm10}</p>`;
}

function legalHtml(model) {
  const legal = getLegalForModel(model);
  const l1 = legal.legal_p1 || TEMPLATE_LEGAL.legal_p1 || 'Delivery transport charge, shown as an estimate ONLY. Actual rate to be determined at time of invoicing.';
  const l2 = legal.legal_p2 || TEMPLATE_LEGAL.legal_p2 || 'Charges relating to taxes, registration, titling, and other fees are the responsibility of the customer; not included in this quotation. Customer takes responsibility for adherence to local and state laws for chassis, emissions, and registration relating to the sweeper.';
  const l3 = legal.legal_p3 || TEMPLATE_LEGAL.legal_p3 || 'A <b>$10,000 deposit and Purchase Order number</b> is requested at time of quotation acceptance. ODRA will issue a detailed sales acknowledgement for signature to confirm the order prior to assembly. Balance will be due prior to shipping, unless otherwise confirmed in writing by an authorized ODRA representative.';
  const l4 = legal.legal_p4 || TEMPLATE_LEGAL.legal_p4 || 'This quote is <b>BUDGETARY ONLY</b>, valid 21 days from date of quote. ODRA LLC may modify or cancel without notice. Prices and availability are subject to change.';
  return `
  <div class="legal">
    ${standardInclusionsHtml(model)}
    <p>${l1}</p>
    <p>${l2}</p>
    <p>${l3}</p>
    <p>${l4}</p>
  </div>`;
}

function buildEnhRows() {
  const wrap = document.getElementById('enhList');
  if (!wrap) return;
  wrap.innerHTML = '';
  const opts = enhOptions(state.model);

  state.enh.forEach((row, idx) => {
    if (!opts.find(o => o.desc === row.desc)) row.desc = '';
    const selectedItem = opts.find(o => o.desc === row.desc);
    const p = selectedItem ? priceForItem(selectedItem) : null;
    const qty = row.qty || 1;
    const ext = p ? (p.unit * qty) : 0;
    const discInfo = (p && p.discountPercent > 0) ? ` &middot; ${p.discountPercent}% off` : '';
    const priceBadge = p ? `<span style="font-size:11px;color:#0f172a;font-weight:600;">${fmt(p.unit)}${discInfo} &rarr; Ext: ${fmt(ext)}</span>` : '';

    const div = document.createElement('div');
    div.className = 'enhRow';
    div.innerHTML = `
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <label>${idx === 0 ? 'Option' : ''}</label>
          ${priceBadge}
        </div>
        <select data-idx="${idx}" class="enhDesc" ${IS_READONLY ? 'disabled' : ''}>
          <option value="">&mdash; none &mdash;</option>
          ${opts.map(o => {
            const op = priceForItem(o);
            return `<option value="${esc(o.desc)}" ${o.desc === row.desc ? 'selected' : ''}>${esc(o.desc)} (${fmt(op.unit)})</option>`;
          }).join('')}
        </select>
      </div>
      <div>
        <label>${idx === 0 ? 'Qty' : ''}</label>
        <input type="number" min="1" class="enhQty" data-idx="${idx}" value="${qty}" ${IS_READONLY ? 'disabled' : ''}>
      </div>
    `;
    wrap.appendChild(div);
  });

  wrap.querySelectorAll('.enhDesc').forEach(el => el.addEventListener('change', e => {
    const i = +e.target.dataset.idx;
    state.enh[i].desc = e.target.value;
    if (e.target.value && !state.enh[i].qty) state.enh[i].qty = 1;
    render();
  }));

  wrap.querySelectorAll('.enhQty').forEach(el => el.addEventListener('input', e => {
    const i = +e.target.dataset.idx;
    state.enh[i].qty = Math.max(1, parseInt(e.target.value) || 1);
    render();
  }));
}

function bindSimple(id, key, parser) {
  const el = document.getElementById(id);
  if (!el) return;
  const eventName = (el.tagName === 'SELECT' || el.type === 'date') ? 'change' : 'input';
  el.addEventListener(eventName, e => {
    state[key] = parser ? parser(e.target.value) : e.target.value;
    render();
  });
}

function calculateSnapshotData() {
  const base = baseItem(state.model);
  const bp = priceForItem(base);
  const baseQty = Number(state.baseQty) || 1;
  const maxDisc = Number(TEMPLATE_HEADER.max_show_discount) || 20000;
  const rawDiscount = Number(state.baseShowDiscount) || 0;
  const showDiscount = Math.min(maxDisc, Math.max(0, rawDiscount));

  // Base MSRP / List is the TRUE price - never artificially inflated
  const baseMsrp = bp.msrp || base.msrp || 0;
  const baseList = bp.list || baseMsrp;
  // Unit price customer pays is discounted by the extra discount
  const baseUnit = Math.max(0, (bp.unit || baseList) - showDiscount);
  const baseExt = baseQty * baseUnit;

  // Chassis upgrade if priced
  const chassisPrice = (CATALOG.chassis_prices && CATALOG.chassis_prices[state.chassis]) ? Number(CATALOG.chassis_prices[state.chassis]) : 0;
  const basisInfo = resolveBasis(state.basis);
  const isCoop = !!(basisInfo && (basisInfo.isCoop || basisInfo.priceTier === 'sw_net'));
  const chassisDiscount = isCoop ? (Number(basisInfo.discountPercent) || 12) : 0;
  const chassisUnit = chassisPrice > 0 ? (Math.round((chassisPrice * (1 - (chassisDiscount / 100))) * 100) / 100) : 0;
  const chassisExt = chassisUnit * baseQty;

  let enhTotal = 0;
  const enhRows = [];

  for (const row of state.enh) {
    if (!row.desc) continue;
    const item = enhOptions(state.model).find(x => x.desc === row.desc);
    if (item) {
      const p = priceForItem(item);
      const qty = Number(row.qty) || 1;
      const ext = qty * p.unit;
      enhTotal += ext;
      enhRows.push({
        desc: row.desc,
        category: item.cat,
        qty: qty,
        msrp: item.msrp,
        list: p.list,
        unit: p.unit,
        extended: ext
      });
    }
  }

  if (chassisPrice > 0) {
    enhRows.unshift({
      desc: `Chassis Option: ${shortChassisLabel(state.chassis)}`,
      category: 'CHASSIS CONFIGURATION',
      qty: baseQty,
      msrp: chassisPrice,
      list: chassisPrice,
      unit: chassisUnit,
      extended: chassisExt
    });
  }

  const freightVal = Number(state.freight) || 0;
  const subtotalVal = baseExt + chassisExt + enhTotal;
  const totalVal = subtotalVal + freightVal;

  return {
    base_msrp: baseMsrp,
    base_list_price: baseList,
    base_unit_price: baseUnit,
    subtotal: subtotalVal,
    total: totalVal,
    enhancements: enhRows,
    quote_code: currentQuoteCode(),
    customer: state.toCompany,
    dest_state: state.destState,
    dest_city: state.destCity,
    rep_territory: state.repTerritory
  };
}

function renderPreview() {
  const p = document.getElementById('preview');
  if (!p) return;

  try {
    const qcode = currentQuoteCode();
  const seqEl = document.getElementById('quoteSeq');
  if (seqEl && document.activeElement !== seqEl) seqEl.value = state.quoteSeq || '';

  const base = baseItem(state.model);
  const bp = priceForItem(base);
  const maxDisc = Number(TEMPLATE_HEADER.max_show_discount) || 20000;
  const rawDiscount = Number(state.baseShowDiscount) || 0;
  const showDiscount = Math.min(maxDisc, Math.max(0, rawDiscount));
  const discWarnEl = document.getElementById('discountWarning');
  if (discWarnEl) {
    discWarnEl.style.display = rawDiscount > maxDisc ? 'block' : 'none';
    if (rawDiscount > maxDisc) {
      discWarnEl.textContent = `Maximum limit allowed is $${maxDisc.toLocaleString()}. You entered $${rawDiscount.toLocaleString()}.`;
    }
  }

  // True MSRP / List - never inflated
  const displayBaseList = bp.list || bp.msrp || 0;
  const baseUnit = Math.max(0, (bp.unit || displayBaseList) - showDiscount);
  const baseExt = state.baseQty * baseUnit;
  const baseDisc = displayBaseList > 0 ? (1 - (baseUnit / displayBaseList)) : 0;

  // Chassis configuration upgrade if priced
  const chassisRawPrice = (CATALOG.chassis_prices && CATALOG.chassis_prices[state.chassis]) ? Number(CATALOG.chassis_prices[state.chassis]) : 0;
  const basisInfo = resolveBasis(state.basis);
  const isCoop = !!(basisInfo && (basisInfo.isCoop || basisInfo.priceTier === 'sw_net'));
  const chassisDiscPct = isCoop ? (Number(basisInfo.discountPercent) || 12) : 0;
  const chassisUnitPrice = chassisRawPrice > 0 ? Math.round((chassisRawPrice * (1 - (chassisDiscPct / 100))) * 100) / 100 : 0;
  const chassisExtPrice = chassisUnitPrice * state.baseQty;

  let enhExtTotal = 0;
  let enhListTotal = 0;
  const enhRowData = [];
  state.enh.forEach(row => {
    if (!row.desc) return;
    const item = enhOptions(state.model).find(o => o.desc === row.desc);
    if (!item) return;
    const p = priceForItem(item);
    const ext = row.qty * p.unit;
    enhExtTotal += ext;
    enhListTotal += (row.qty * (p.list || p.msrp || p.unit));
    const disc = p.discountPercent > 0 ? (p.discountPercent / 100) : (p.list ? 1 - (p.unit / p.list) : 0);
    enhRowData.push({ desc: item.desc, qty: row.qty, list: p.list, unit: p.unit, ext, disc });
  });

  const isBundled = (state.enhancementPricingMode === 'bundled');

  // When bundled into machine price:
  // Base machine net selling amount + all selected enhancements net selling amounts combined into package price.
  // The contract discount percentage must remain identical between Itemized and Bundled modes.
  const packageUnit = baseUnit + (state.baseQty > 0 ? (enhExtTotal / state.baseQty) : enhExtTotal);
  const packageExt = baseExt + enhExtTotal;
  const packageListUnit = displayBaseList + (state.baseQty > 0 ? (enhListTotal / state.baseQty) : enhListTotal);
  const packageListExt = packageListUnit * state.baseQty;
  const packageDisc = baseDisc;

  const anyDisc = isBundled
    ? (Math.abs(packageDisc) >= 0.0005 || chassisDiscPct > 0)
    : (Math.abs(baseDisc) >= 0.0005 || chassisDiscPct > 0 || enhRowData.some(r => Math.abs(r.disc) >= 0.0005));
  const cols = anyDisc ? 6 : 5;
  const discTh = anyDisc ? '<th class="num">DISCOUNT (%)</th>' : '';

  const exwFobLabel = TEMPLATE_HEADER.exw_fob || 'EXW, Factory pick-up';
  const addrLine1 = esc(TEMPLATE_HEADER.address_line_1 || '705 South 48th Street, Grand Forks, ND 58201');
  const addrLine2 = esc(TEMPLATE_HEADER.address_line_2 || 't: 844-888-6372 • e: info@odrasweeper.com • odrasweeper.com');
  const quoteTitle = esc(TEMPLATE_HEADER.quote_title || 'QUOTATION');
  const sectionHeading = esc(TEMPLATE_HEADER.section_heading || 'Including Enhancements');
  const freightRowLabel = esc(TEMPLATE_HEADER.freight_row_label || 'Sweeper Transport / Freight') + (state.destCity && !(state.fob === exwFobLabel || state.fob === 'EXW, Factory pick-up') ? ` &mdash; ${esc(state.destCity)}, ${esc(state.destState)}` : '');
  const totalRowLabel = esc(TEMPLATE_HEADER.total_row_label || 'TOTAL (USD)');

  const chassisRowHtml = chassisRawPrice > 0 ? `
    <tr class="chassisRow">
      <td class="descCol"><b>Chassis Option:</b> ${esc(shortChassisLabel(state.chassis))}</td>
      <td class="num">${state.baseQty}</td>
      <td class="num">${fmtMSRP(chassisRawPrice)}</td>
      ${anyDisc ? `<td class="num">${chassisDiscPct > 0 ? (chassisDiscPct + '%') : '<span style="color:#888;">—</span>'}</td>` : ''}
      <td class="num">${fmt(chassisUnitPrice)}</td>
      <td class="num">${fmt(chassisExtPrice)}</td>
    </tr>
  ` : '';

  const baseDescHtml = esc(dynamicBaseDesc(base, state.chassis, state.model, state.basis)).replace(/\n/g, '<br>');
  const baseRowHtml = `
    <tr class="baseRow">
      <td class="descCol">${baseDescHtml}</td>
      <td class="num">${state.baseQty}</td>
      <td class="num">${fmtMSRP(isBundled ? packageListUnit : displayBaseList)}</td>
      ${anyDisc ? `<td class="num">${baseDisc > 0 ? pctFmt(baseDisc) : '<span style="color:#888;">—</span>'}</td>` : ''}
      <td class="num">${fmt(isBundled ? packageUnit : baseUnit)}</td>
      <td class="num">${fmt(isBundled ? packageExt : baseExt)}</td>
    </tr>
  `;

  const sectionRowHtml = isBundled
    ? ''
    : `<tr class="sectionRow"><td colspan="${cols}">${sectionHeading}</td></tr>`;

  const enhRowsHtml = isBundled
    ? ''
    : (enhRowData.length > 0
        ? enhRowData.map(r => `
          <tr>
            <td class="descCol">${esc(r.desc)}</td>
            <td class="num">${r.qty}</td>
            <td class="num">${fmtMSRP(r.list)}</td>
            ${anyDisc ? `<td class="num">${r.disc > 0 ? pctFmt(r.disc) : '<span style="color:#888;">—</span>'}</td>` : ''}
            <td class="num">${fmt(r.unit)}</td>
            <td class="num">${fmt(r.ext)}</td>
          </tr>
        `).join('')
        : `<tr><td colspan="${cols}" style="color:#999;font-style:italic;">No enhancements selected</td></tr>`
      );

  const freight = Number(state.freight) || 0;
  const subtotal = baseExt + chassisExtPrice + enhExtTotal;
  const total = subtotal + freight;
  const subtotalLabel = basisInfo ? basisInfo.subtotalLabel : 'MSRP Subtotal';
  const disclaimer = basisInfo ? basisInfo.disclaimer : '';

  const coopLogo = basisInfo ? basisInfo.logo : null;
  const coopCaption = basisInfo ? basisInfo.caption : '';
  const coopRowHtml = coopLogo ? `
    <tr class="coopRow">
      <td colspan="${cols - 2}" style="text-align:center;padding:12px 6px;">
        <img src="${coopLogo}" style="height:36px;max-width:260px;display:block;margin:0 auto 8px auto;object-fit:contain;" alt="Cooperative Logo">
        <b style="font-size:11.5px;line-height:1.4;">${coopCaption}</b>
      </td>
      <td class="num"></td>
      <td class="num"></td>
    </tr>
  ` : '';

  const toLines = [state.toCompany, state.toAddr1, state.toAddr2, state.toContact].filter(Boolean).map(esc);
  const toBlockHtml = toLines.length 
    ? `<span class="toLines">${toLines.map(l => `<div>${l}</div>`).join('')}</span>`
    : ' &mdash;';

  p.innerHTML = `
    <div class="docHeaderTop">
      <div class="co">
        <img src="{{ asset('images/logo.jpg') }}" alt="${esc(TEMPLATE_HEADER.company_name || 'ODRA Road Sweepers')}">
        <div class="addr">
          ${addrLine1}<br>
          ${addrLine2}
        </div>
      </div>
      <div class="qbox">
        <div class="ttl">${quoteTitle}</div>
        <table>
          <tr><td class="k">Quote Code</td><td>${esc(qcode)}</td></tr>
          <tr><td class="k">Date</td><td>${fmtDateDisplay(state.date)}</td></tr>
        </table>
      </div>
    </div>

    ${disclaimer ? `<div class="disclaimerLine">${esc(disclaimer)}</div>` : ''}

    <div class="infoGrid">
      <div class="col">
        <div><span class="k">Model:</span> ${esc(state.model)}</div>
        <div><span class="k">Chassis / Config:</span> ${esc(shortChassisLabel(state.chassis))}</div>
        <div><span class="k">FOB / Transport:</span> ${esc(state.fob)}</div>
        <div class="toBlock"><span class="k">To:</span>${toBlockHtml}</div>
      </div>
      <div class="col">
        <div><span class="k">Pricing Basis:</span> ${esc(basisInfo ? basisInfo.label : state.basis)}</div>
        <div><span class="k">P.O. No.:</span> ${esc(state.po) || '&mdash;'}</div>
        <div><span class="k">Customer Acct #:</span> ${esc(state.acct) || '&mdash;'}</div>
        <div><span class="k">Sales Rep:</span> ${esc(state.salesRep) || '&mdash;'}</div>
      </div>
    </div>

    <table class="items">
      <colgroup>
        ${anyDisc ? `
          <col style="width: 38%;">
          <col style="width: 6%;">
          <col style="width: 14%;">
          <col style="width: 13%;">
          <col style="width: 14%;">
          <col style="width: 15%;">
        ` : `
          <col style="width: 48%;">
          <col style="width: 7%;">
          <col style="width: 15%;">
          <col style="width: 15%;">
          <col style="width: 15%;">
        `}
      </colgroup>
      <thead>
        <tr>
          <th>PRICING BASIS / DESCRIPTION</th>
          <th class="num">QTY</th>
          <th class="num">MSRP ($)</th>
          ${discTh}
          <th class="num">UNIT PRICE ($)</th>
          <th class="num">EXTENDED ($)</th>
        </tr>
      </thead>
      <tbody>
        ${baseRowHtml}
        ${chassisRowHtml}
        ${sectionRowHtml}
        ${enhRowsHtml}
        ${coopRowHtml}
        <tr class="subtotalRow">
          <td colspan="${cols - 1}" class="num">${esc(subtotalLabel)}</td>
          <td class="num">${fmt(subtotal)}</td>
        </tr>
        <tr>
          <td class="descCol">${freightRowLabel}</td>
          <td class="num">${(state.fob === exwFobLabel || state.fob === 'EXW, Factory pick-up') ? 0 : 1}</td>
          <td class="num"></td>
          ${anyDisc ? '<td class="num"></td>' : ''}
          <td class="num">${fmt(freight)}</td>
          <td class="num">${fmt(freight)}</td>
        </tr>
        <tr class="totalRow">
          <td colspan="${cols - 1}" class="num">${totalRowLabel}</td>
          <td class="num">${fmt(total)}</td>
        </tr>
      </tbody>
    </table>

    ${legalHtml(state.model)}
  `;
  } catch (err) {
    console.error('Error rendering quotation preview:', err);
    p.innerHTML = `<div style="padding:24px; color:#991b1b; background:#fee2e2; border-radius:6px; font-family:sans-serif;">
      <h3 style="margin-top:0;">Preview Render Error</h3>
      <p>${err.message}</p>
    </div>`;
  }
}

function render() {
  populateChassis();
  applyRepFreight();
  buildEnhRows();
  renderPreview();
}

function populatePricingBases() {
  const basisEl = document.getElementById('basis');
  if (!basisEl) return;

  const mCode = state.model;
  const chassisStr = state.chassis;
  const cfgData = (mCode && chassisStr && CATALOG.configurations_data) ? CATALOG.configurations_data[mCode]?.[chassisStr] : null;
  const mBases = (cfgData && cfgData.pricing_bases_list && cfgData.pricing_bases_list.length > 0)
    ? cfgData.pricing_bases_list
    : getBasesForModel(state.model);

  const currentVal = state.basis;

  basisEl.innerHTML = '';
  mBases.forEach(b => {
    if (b.is_active === false) return;
    const key = b.key || b.name || b.label;
    const label = b.label || b.name || key;
    const opt = document.createElement('option');
    opt.value = key;
    opt.textContent = label;
    basisEl.appendChild(opt);
  });

  const matchingOpt = Array.from(basisEl.options).find(o => 
    o.value === currentVal || 
    o.value.toLowerCase() === String(currentVal).toLowerCase() ||
    o.textContent.toLowerCase() === String(currentVal).toLowerCase()
  );

  if (matchingOpt) {
    basisEl.value = matchingOpt.value;
    state.basis = matchingOpt.value;
  } else if (basisEl.options.length > 0) {
    basisEl.value = basisEl.options[0].value;
    state.basis = basisEl.value;
  }
}

function initForm() {
  // Populate form fields with current state
  const quoteDateEl = document.getElementById('quoteDate');
  if (quoteDateEl) {
    quoteDateEl.value = state.date || todayISO();
    quoteDateEl.addEventListener('change', e => {
      state.date = e.target.value;
      render();
    });
  }

  const quoteSeqEl = document.getElementById('quoteSeq');
  if (quoteSeqEl) {
    quoteSeqEl.value = state.quoteSeq || '3001';
    quoteSeqEl.addEventListener('input', e => {
      state.quoteSeq = e.target.value.replace(/[^0-9]/g, '').slice(0, 4);
      render();
    });
  }

  const modelEl = document.getElementById('model');
  if (modelEl) {
    if (state.model && !Array.from(modelEl.options).some(o => o.value === state.model)) {
      const opt = document.createElement('option');
      opt.value = state.model;
      opt.textContent = state.model;
      modelEl.appendChild(opt);
    }
    modelEl.value = state.model || (modelEl.options[0] ? modelEl.options[0].value : 'MT4H');
    state.model = modelEl.value;
    modelEl.addEventListener('change', e => {
      state.model = e.target.value;
      populateChassis();
      populatePricingBases();
      buildEnhRows();
      render();
    });
  }

  const basisEl = document.getElementById('basis');
  if (basisEl) {
    populatePricingBases();
    basisEl.addEventListener('change', e => {
      state.basis = e.target.value;
      applyRepFreight();
      render();
    });
  }

  const chassisEl = document.getElementById('chassis');
  if (chassisEl) {
    chassisEl.addEventListener('change', e => {
      state.chassis = e.target.value;
      populatePricingBases();
      render();
    });
  }

  const salesRepEl = document.getElementById('salesRep');
  if (salesRepEl) {
    if (salesRepEl.tagName === 'SELECT') {
      salesRepEl.value = state.salesRep || '';
      salesRepEl.addEventListener('change', e => {
        state.salesRep = e.target.value;
        populateDestStateDropdown();
        applyRepFreight();
        render();
      });
    } else {
      // Locked input field for Sales Representative: strictly fixed to quotation creator
      salesRepEl.value = CURRENT_QUOTE_ID ? (QUOTE_OWNER_NAME || state.salesRep) : ((!IS_ADMIN && CURRENT_USER_NAME) ? CURRENT_USER_NAME : (state.salesRep || ''));
      state.salesRep = salesRepEl.value;
    }
  }

  const destStateEl = document.getElementById('destState');
  if (destStateEl) {
    destStateEl.addEventListener('change', e => {
      state.destState = e.target.value;
      populateDestCityDropdown();
      applyRepFreight();
      render();
    });
  }

  const destCityEl = document.getElementById('destCity');
  if (destCityEl) {
    destCityEl.addEventListener('change', e => {
      state.destCity = e.target.value;
      applyRepFreight();
      render();
    });
  }

  bindSimple('po', 'po');
  bindSimple('acct', 'acct');
  bindSimple('toContact', 'toContact');
  bindSimple('toCompany', 'toCompany');
  bindSimple('toAddr1', 'toAddr1');
  bindSimple('toAddr2', 'toAddr2');
  bindSimple('baseQty', 'baseQty', v => Math.max(1, parseInt(v) || 1));
  bindSimple('baseShowDiscount', 'baseShowDiscount', v => Math.max(0, parseFloat(v) || 0));

  const enhPricingEl = document.getElementById('enhancementPricingMode');
  if (enhPricingEl) {
    if (state.enhancementPricingMode) {
      enhPricingEl.value = state.enhancementPricingMode;
    }
    enhPricingEl.addEventListener('change', e => {
      state.enhancementPricingMode = e.target.value;
      render();
    });
  }

  const fobEl = document.getElementById('fob');
  if (fobEl) {
    if (state.fob && !Array.from(fobEl.options).some(o => o.value === state.fob)) {
      const opt = document.createElement('option');
      opt.value = state.fob;
      opt.textContent = state.fob;
      fobEl.appendChild(opt);
    }
    if (state.fob) fobEl.value = state.fob;
    fobEl.addEventListener('change', e => {
      state.fob = e.target.value;
      applyRepFreight();
      render();
    });
  }

  // Pre-fill input values from state
  const inputsToSync = ['po', 'acct', 'toCompany', 'toContact', 'toAddr1', 'toAddr2', 'baseQty', 'baseShowDiscount', 'enhancementPricingMode'];
  inputsToSync.forEach(id => {
    const el = document.getElementById(id);
    if (el && state[id] !== undefined) {
      el.value = state[id];
    }
  });

  populateDestStateDropdown();
  render();

  // Automatic Print if URL contains ?print=1
  if (urlParams.get('print') === '1') {
    setTimeout(() => {
      window.print();
    }, 600);
  }
}

// API Interactions for Save Draft & Finalize Quote
async function saveQuote(isFinalize = false) {
  if (!state.toCompany || !state.toCompany.trim()) {
    alert('Please enter a Customer Company name before saving.');
    document.getElementById('toCompany')?.focus();
    return;
  }

  const exwFobLabel = TEMPLATE_HEADER.exw_fob || 'EXW, Factory pick-up';
  const isEXW = (state.fob === exwFobLabel || state.fob === 'EXW, Factory pick-up');
  if (!isEXW && (!freightValid || Number(state.freight) <= 0)) {
    alert('Quotation generation is blocked: A valid freight rate could not be found for the selected delivery destination in the Freight Matrix.\n\nPlease select a valid delivery State and City destination from your assigned coverage area.');
    return;
  }

  const rawDiscount = Number(state.baseShowDiscount) || 0;
  const maxDisc = Number(TEMPLATE_HEADER.max_show_discount) || 20000;
  if (rawDiscount > maxDisc) {
    alert(`Extra discount cannot exceed the maximum permitted limit of $${maxDisc.toLocaleString()}. Please adjust before saving.`);
    document.getElementById('baseShowDiscount')?.focus();
    return;
  }

  if (isFinalize && !confirm('Finalize this quotation? The saved prices and snapshot will be permanently frozen.')) {
    return;
  }

  if (!CURRENT_QUOTE_ID && !IS_ADMIN && CURRENT_USER_NAME) {
    state.salesRep = CURRENT_USER_NAME;
  } else if (CURRENT_QUOTE_ID) {
    state.salesRep = QUOTE_OWNER_NAME || state.salesRep;
  }

  const snap = calculateSnapshotData();
  const base = baseItem(state.model);
  const payload = {
    quote_number: state.quoteSeq,
    customer: state.toCompany,
    model_description: dynamicBaseDesc(base, state.chassis, state.model, state.basis),
    enhancement_pricing_mode: state.enhancementPricingMode || 'itemized',
    base_msrp: snap.base_msrp,
    base_list_price: snap.base_list_price,
    base_unit_price: snap.base_unit_price,
    subtotal: snap.subtotal,
    total: snap.total,
    quote_code: snap.quote_code,
    enhancements: snap.enhancements,
    state: state
  };

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const url = CURRENT_QUOTE_ID 
    ? (isFinalize ? `/api/quotes/${CURRENT_QUOTE_ID}/finalize` : `/api/quotes/${CURRENT_QUOTE_ID}`)
    : '/api/quotes';
  const method = CURRENT_QUOTE_ID ? (isFinalize ? 'POST' : 'PUT') : 'POST';

  try {
    const res = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(payload)
    });

    const result = await res.json();
    if (!res.ok) throw new Error(result.error || result.message || 'Failed to save quotation');

    alert(result.message);

    if (isFinalize) {
      // Generate and upload PDF
      const element = document.getElementById('preview');
      const opt = {
        margin: [10, 8, 10, 8],
        filename: `ODRA_Quotation_Q-${state.quoteSeq}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' }
      };

      const pdfBase64 = await html2pdf().set(opt).from(element).outputPdf('datauristring');
      const quoteId = result.quote?.id || CURRENT_QUOTE_ID;

      await fetch(`/api/quotes/${quoteId}/pdf`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ pdf: pdfBase64 })
      });

      window.location.href = `/sales/quotes/${quoteId}`;
    } else if (!CURRENT_QUOTE_ID && result.quote) {
      window.location.href = `/sales/quotes/${result.quote.id}`;
    }
  } catch (err) {
    alert('Error: ' + err.message);
  }
}

document.getElementById('btnSaveDraft')?.addEventListener('click', () => saveQuote(false));
document.getElementById('btnFinalize')?.addEventListener('click', () => saveQuote(true));

document.getElementById('btnRevise')?.addEventListener('click', async () => {
  if (!confirm('Create a new draft revision from this finalized quote?')) return;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  try {
    const res = await fetch(`/api/quotes/${CURRENT_QUOTE_ID}/revise`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.error || 'Failed to create revision');
    alert(result.message);
    window.location.href = `/sales/quotes/${CURRENT_QUOTE_ID}`;
  } catch (err) {
    alert('Error: ' + err.message);
  }
});

// Run initialization immediately and on load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initForm);
} else {
  initForm();
}
</script>
@endpush
