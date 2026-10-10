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
  padding: 24px 16px;
  display: flex;
  justify-content: flex-start;
}

#preview {
  background: #fff;
  padding: 34px 38px;
  font-size: 12px;
  line-height: 1.4;
  color: #1a1a1a;
  min-height: 400px;
  width: 100%;
  min-width: 780px;
  max-width: 820px;
  margin: 0 auto;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  border-radius: 4px;
  flex-shrink: 0;
}

.docHeaderTop {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  border-bottom: 3px solid var(--red);
  padding-bottom: 10px;
  margin-bottom: 8px;
}
.docHeaderTop .co img {
  height: 46px;
  display: block;
  margin-bottom: 4px;
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
  font-size: 20px;
  font-weight: bold;
  color: var(--red);
  letter-spacing: 1px;
}
.docHeaderTop .qbox table {
  margin-left: auto;
  font-size: 11.5px;
  border-collapse: collapse;
}
.docHeaderTop .qbox td {
  padding: 1px 0 1px 10px;
  text-align: right;
}
.docHeaderTop .qbox td.k {
  color: var(--gray);
  text-align: right;
  padding-left: 14px;
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
  margin-bottom: 2px;
}
.infoGrid .k {
  color: var(--gray);
  font-weight: bold;
  display: inline-block;
  min-width: 110px;
}
.infoGrid .toBlock .toLines {
  display: inline-block;
  vertical-align: top;
}
.infoGrid .toBlock .toLines>div {
  margin-bottom: 2px;
}

table.items {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 4px;
}
table.items th {
  background: var(--red);
  color: #fff;
  font-size: 10.5px;
  text-transform: uppercase;
  padding: 7px 8px;
  text-align: left;
}
table.items th.num, table.items td.num {
  text-align: right;
}
table.items td {
  padding: 6px 8px;
  border-bottom: 1px solid #eee;
  font-size: 11.5px;
  vertical-align: top;
}
table.items tr.baseRow td {
  font-weight: bold;
  background: #faf6f6;
}
table.items tr.sectionRow td {
  font-weight: bold;
  color: var(--red);
  border-bottom: 1px solid #eee;
  padding-top: 10px;
}
table.items tr.subtotalRow td {
  border-top: 2px solid var(--ink);
  font-weight: bold;
  padding-top: 8px;
}
table.items tr.totalRow td {
  font-weight: bold;
  font-size: 13.5px;
  color: var(--red);
  border-top: 1px solid #ccc;
}
table.items tr.subtotalRow td, table.items tr.totalRow td {
  white-space: nowrap;
}
.legal {
  margin-top: 16px;
  border-top: 1px solid var(--line);
  padding-top: 10px;
}
.legal p {
  font-size: 9.7px;
  color: var(--gray);
  margin: 0 0 6px;
  line-height: 1.35;
}
.legal p b {
  color: #1a1a1a;
}

@media print {
  body { background: #fff; }
  .portal-header, .builder-topbar, .builder, .btnrow { display: none !important; }
  .app-builder-layout { display: block; padding: 0; max-width: none; }
  .previewWrap { background: transparent; padding: 0; max-height: none; overflow: visible; }
  #preview { box-shadow: none; border: none; padding: 0; max-width: 100%; }
}
</style>
@endpush

@section('content')
<div class="builder-topbar">
    <div class="builder-heading">
        <h1>Quotation Q-{{ $quoteNumber }}</h1>
        <span>Version {{ $version }} &middot; {{ $isReadonly ? 'Finalized (Historical Snapshot Frozen)' : 'Draft (Editable)' }}</span>
    </div>
    <div class="builder-topbar-actions">
        @if(auth()->user()->isManager())
            <a href="{{ route('manager.quotes.index') }}" class="btn-nav">&larr; Team Quotations</a>
        @else
            <a href="{{ route('sales.quotes.index') }}" class="btn-nav">&larr; My Quotations</a>
        @endif

        @if ($isReadonly)
            <button type="button" id="btnRevise" class="btn-nav">Create Revision</button>
            <a href="{{ route('sales.quotes.pdf', $quote) }}" class="btn-nav" style="background:var(--red);color:white;border-color:var(--red);" target="_blank">Download PDF</a>
        @else
            <button type="button" id="btnSaveDraft" class="btn-nav">Save Draft</button>
            <button type="button" id="btnFinalize" class="btn-nav" style="background:var(--red);color:white;border-color:var(--red);">Finalize Quote</button>
        @endif
    </div>
</div>

<div class="app-builder-layout">
  <!-- Left Builder Form Panel -->
  <div class="panel builder">
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
      <option value="MT4H">MT4H</option>
      <option value="MS5H">MS5H</option>
    </select>

    <label>Pricing Basis</label>
    <select id="basis" {{ $isReadonly ? 'disabled' : '' }}>
      <option value="MSRP (Commercial)">MSRP (Commercial)</option>
      <option value="Sourcewell">Sourcewell</option>
      <option value="HGAC">H-GAC / HGACBuy</option>
      <option value="Minnesota">State of Minnesota</option>
      <option value="Buyboard">BuyBoard</option>
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
        <select id="salesRep" {{ $isReadonly ? 'disabled' : '' }}>
          <option value="">&mdash; Select &mdash;</option>
          @foreach($salesReps as $rep)
            <option value="{{ $rep->name }}">{{ $rep->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label>FOB / Transport</label>
        <select id="fob" {{ $isReadonly ? 'disabled' : '' }}>
          <option value="Transport, estimate only noted in quote">Transport, estimate only noted in quote</option>
          <option value="EXW, Factory pick-up">EXW, Factory pick-up</option>
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

    <label style="margin-top:10px;display:block;">Show Extra Discount On Quote (USD) <span class="smallnote">optional &mdash; max $20,000</span></label>
    <input type="number" id="baseShowDiscount" value="0" min="0" max="20000" {{ $isReadonly ? 'disabled' : '' }}>
    <div id="discountWarning" style="display:none;color:#b3261e;font-size:11px;margin-top:4px;">Maximum limit allowed is $20,000.</div>

    <h2 class="sec">Enhancements <span class="smallnote">(pick up to 13 options)</span></h2>
    <div id="enhList"></div>

    <h2 class="sec">Freight</h2>
    <label>Rep Territory <span class="smallnote">internal only &mdash; auto-calculates freight below; never shown on the customer quote</span></label>
    <select id="repTerritory" {{ $isReadonly ? 'disabled' : '' }}></select>

    <label style="margin-top:10px;display:block;">Sweeper Transport / Freight Estimate (USD) <span class="smallnote">auto-filled from territory</span></label>
    <input type="number" id="freight" value="0" min="0" readonly>
    <div id="exwFreightNote" style="display:none;color:#666;font-size:11px;margin-top:4px;font-style:italic;">FOB is set to EXW, Factory pick-up &mdash; freight is $0.</div>

    <div class="btnrow">
      <button type="button" class="btn-primary" id="downloadBtn">Download PDF</button>
      <button type="button" class="btn-secondary" id="printBtn">Print</button>
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

let state = {
  date: todayISO(),
  model: 'MT4H',
  basis: 'MSRP (Commercial)',
  chassis: '',
  po: '',
  fob: 'Transport, estimate only noted in quote',
  acct: '',
  salesRep: '',
  toContact: '',
  toCompany: '',
  toAddr1: '',
  toAddr2: '',
  baseQty: 1,
  freight: 0,
  baseShowDiscount: 0,
  repTerritory: '',
  quoteSeq: '3001',
  enh: Array.from({length: 13}, () => ({desc: '', qty: 1}))
};

// Safely merge initial state
if (INITIAL_STATE && typeof INITIAL_STATE === 'object') {
  Object.assign(state, INITIAL_STATE);
}

// Support pre-selecting model or chassis from Catalog showcase URL (?model=MT4H / ?model=MS5H)
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('model') && (!INITIAL_STATE || !INITIAL_STATE.model)) {
  const paramModel = urlParams.get('model').toUpperCase();
  if (['MT4H', 'MS5H'].includes(paramModel)) {
    state.model = paramModel;
  }
}
if (urlParams.has('chassis') && (!INITIAL_STATE || !INITIAL_STATE.chassis)) {
  state.chassis = urlParams.get('chassis');
}

// Guarantee enh is always an array of 13 rows
if (!state.enh || !Array.isArray(state.enh)) {
  state.enh = Array.from({length: 13}, () => ({desc: '', qty: 1}));
}
while (state.enh.length < 13) {
  state.enh.push({desc: '', qty: 1});
}

let ITEMS = CATALOG.items || [];
let CHASSIS = CATALOG.chassis || [];
let CHASSIS_SHORT = CATALOG.chassis_short || {
  "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper": "MT4H Isuzu cabover, Class 5, non-CDL - LHD",
  "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - RIGHT side dumping hopper": "MT4H Isuzu cabover, Class 5, non-CDL - RHD",
  "MT4H - Ford F-550XL conventional, 19,000 GVWR, non-CDL - LEFT side dumping hopper": "MT4H Ford F-550XL, non-CDL - LHD",
  "MS5H - Conventional Class 6, 25,500 GVWR, 148\" WB, 300HP Cummins, non-CDL": "MS5H Conventional Class 6, non-CDL",
  "MS5H - Conventional Class 7, CDL-required configuration, 300HP Cummins - no price difference": "MS5H Conventional Class 7, CDL-required"
};

let FREIGHT_ESTIMATES = CATALOG.freight || [];
let TERRITORIES = CATALOG.territories || [];

const SOURCEWELL_LOGO = "{{ asset('images/logo-sourcewell.png') }}";
const HGAC_LOGO = "{{ asset('images/logo-hgac.png') }}";
const BUYBOARD_LOGO = "{{ asset('images/logo-buyboard.png') }}";

const COOP_BASES = {
  'Sourcewell': {
    label: 'Sourcewell',
    subtotalLabel: 'Sourcewell Contract Price',
    disclaimer: 'Quoted under cooperative contract: Sourcewell #062425-ODRA',
    logo: SOURCEWELL_LOGO,
    caption: 'Referenced under Sourcewell<br>Contract # 062425-ODRA'
  },
  'HGAC': {
    label: 'H-GAC / HGACBuy',
    subtotalLabel: 'H-GAC Contract Price',
    disclaimer: 'Quoted under cooperative contract: H-GAC/HGACBuy Master Agreement SW04-26',
    logo: HGAC_LOGO,
    caption: 'Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26'
  },
  'H-GAC / HGACBuy': {
    label: 'H-GAC / HGACBuy',
    subtotalLabel: 'H-GAC Contract Price',
    disclaimer: 'Quoted under cooperative contract: H-GAC/HGACBuy Master Agreement SW04-26',
    logo: HGAC_LOGO,
    caption: 'Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26'
  },
  'Minnesota': {
    label: 'State of Minnesota',
    subtotalLabel: 'Minnesota Contract Price',
    disclaimer: 'Quoted under cooperative contract: State of Minnesota Contract 285158, Release S-843(5)',
    logo: null,
    caption: 'Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))'
  },
  'State of Minnesota': {
    label: 'State of Minnesota',
    subtotalLabel: 'Minnesota Contract Price',
    disclaimer: 'Quoted under cooperative contract: State of Minnesota Contract 285158, Release S-843(5)',
    logo: null,
    caption: 'Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))'
  },
  'Buyboard': {
    label: 'BuyBoard',
    subtotalLabel: 'BuyBoard Contract Price',
    disclaimer: 'Quoted under cooperative contract: ODRA BuyBoard Contract #788-25',
    logo: BUYBOARD_LOGO,
    caption: 'Referenced under BuyBoard<br>ODRA Contract # 788-25'
  },
  'BuyBoard': {
    label: 'BuyBoard',
    subtotalLabel: 'BuyBoard Contract Price',
    disclaimer: 'Quoted under cooperative contract: ODRA BuyBoard Contract #788-25',
    logo: BUYBOARD_LOGO,
    caption: 'Referenced under BuyBoard<br>ODRA Contract # 788-25'
  }
};

const SR_CODES = {
  "Carlos Silva":"CS", "John Wells":"JW", "Kerry Briggs":"KB",
  "Paul Piotrowski":"PP", "Ryan Khosrofian":"RK", "Shawn Herringer":"SH", "Juan Oliver":"JO",
  "Robert Yuska":"RY", "Chris Norlin":"CN", "Kra Whitmore":"KW", "Fernando Oliveira":"FO",
  "Troy Fisher":"TF", "Sean Chenoweth":"SC", "Jason Coover":"JC", "Alex Byczynski":"AB",
  "JJ Rickis":"JR"
};

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
  return Number(n).toLocaleString('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 });
}

function pctFmt(n) {
  if (n === null || n === undefined || isNaN(n) || Math.abs(n) < 0.0005) return '';
  return Math.round(n * 100) + '%';
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
  const filtered = CHASSIS.filter(c => c.indexOf(m) === 0);
  return filtered.length ? filtered : (CHASSIS.length ? CHASSIS : ['Standard ' + m + ' Chassis']);
}

function priceForItem(item) {
  if (!item) return { list: 0, unit: 0, msrp: 0 };
  if (COOP_BASES[state.basis]) {
    return {
      list: Number(item.swList) || Number(item.msrp) || 0,
      unit: Number(item.swNet) || Number(item.msrp) || 0,
      msrp: Number(item.msrp) || 0
    };
  }
  return {
    list: Number(item.msrp) || 0,
    unit: Number(item.msrp) || 0,
    msrp: Number(item.msrp) || 0
  };
}

function dynamicBaseDesc(base, chassisStr, model) {
  if (model === 'MT4H') {
    const s = chassisStr || '';
    const brand = /Ford F-550XL/.test(s) ? 'Ford F-550XL' : 'Isuzu NRR';
    const gvwrMatch = s.match(/([\d,]+)\s*GVWR/);
    const gvwr = gvwrMatch ? gvwrMatch[1] : '19,500';
    const sideMatch = s.match(/(LEFT|RIGHT) side dumping hopper/i);
    const side = (sideMatch && /RIGHT/i.test(sideMatch[1])) ? 'Right' : 'Left';
    return `MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. ${side}-side dump sweeper. Chassis is a brand new 2027MY ${brand}, ${gvwr} GVWR.`;
  }
  if (model === 'MS5H') {
    const s = chassisStr || '';
    const isCDL = /Class 7|CDL-required/i.test(s);
    if (isCDL && base.desc) {
      return base.desc.replace(/non-CDL,\s*148"\s*WB\s*Class\s*6/i, 'CDL-required, 148" WB Class 7');
    }
    return base.desc || '';
  }
  return base.desc || '';
}

function populateChassis() {
  const sel = document.getElementById('chassis');
  if (!sel) return;
  const opts = chassisForModel(state.model);
  const keep = opts.includes(state.chassis) ? state.chassis : (opts[0] || '');
  sel.innerHTML = opts.map(c => `<option value="${esc(c)}" ${c === keep ? 'selected' : ''}>${esc(shortChassisLabel(c))}</option>`).join('');
  state.chassis = keep;
}

function populateRepTerritory() {
  const sel = document.getElementById('repTerritory');
  if (!sel) return;
  const rep = state.salesRep;
  const repHasTerritories = TERRITORIES.some(t => t.rep === rep);
  const opts = repHasTerritories ? TERRITORIES.filter(t => t.rep === rep) : TERRITORIES;
  const keep = opts.some(o => o.code === state.repTerritory) ? state.repTerritory : (opts[0] ? opts[0].code : '');
  sel.innerHTML = opts.map(t => `<option value="${t.code}" ${t.code === keep ? 'selected' : ''}>${esc(t.rep)} \u2014 ${esc(t.label)}</option>`).join('');
  sel.value = keep;
  state.repTerritory = keep;
}

function territoryMaxFreight(code, useMunicipality) {
  const t = TERRITORIES.find(x => x.code === code);
  if (!t) return 0;
  const rows = t.cities && t.cities.length
    ? FREIGHT_ESTIMATES.filter(r => t.cities.includes(r.city))
    : FREIGHT_ESTIMATES.filter(r => t.states && t.states.includes(r.state));
  if (!rows.length) return 0;
  const vals = rows.map(r => useMunicipality ? (r.municipality || 0) : (r.customer || 0));
  return Math.max(...vals);
}

function applyTerritoryFreight() {
  const freightEl = document.getElementById('freight');
  const exwNoteEl = document.getElementById('exwFreightNote');
  if (state.fob === 'EXW, Factory pick-up') {
    state.freight = 0;
    if (freightEl) freightEl.value = 0;
    if (exwNoteEl) exwNoteEl.style.display = 'block';
    return;
  }
  if (exwNoteEl) exwNoteEl.style.display = 'none';
  if (!state.repTerritory) return;
  const useMunicipality = !!COOP_BASES[state.basis];
  const val = territoryMaxFreight(state.repTerritory, useMunicipality);
  state.freight = val;
  if (freightEl) freightEl.value = val;
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
  const pm10 = model === 'MS5H' ? '' : ' PM-10 compliant (SCAQMD Rule 1186).';
  return `<p><b>Standard Features:</b> ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.</p>
  <p>Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled from inside the cab.</p>
  <p>Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control.${pm10}</p>`;
}

function legalHtml(model) {
  return `
  <div class="legal">
    ${standardInclusionsHtml(model)}
    <p>Delivery transport charge, shown as an estimate ONLY. Actual rate to be determined at time of invoicing.</p>
    <p>Charges relating to taxes, registration, titling, and other fees are the responsibility of the customer; not included in this quotation. Customer takes responsibility for adherence to local and state laws for chassis, emissions, and registration relating to the sweeper.</p>
    <p>A <b>$10,000 deposit and Purchase Order number</b> is requested at time of quotation acceptance. ODRA will issue a detailed sales acknowledgement for signature to confirm the order prior to assembly. Balance will be due prior to shipping, unless otherwise confirmed in writing by an authorized ODRA representative.</p>
    <p>This quote is <b>BUDGETARY ONLY</b>, valid 21 days from date of quote. ODRA LLC may modify or cancel without notice. Prices and availability are subject to change.</p>
  </div>`;
}

function buildEnhRows() {
  const wrap = document.getElementById('enhList');
  if (!wrap) return;
  wrap.innerHTML = '';
  const opts = enhOptions(state.model);

  state.enh.forEach((row, idx) => {
    if (!opts.find(o => o.desc === row.desc)) row.desc = '';
    const div = document.createElement('div');
    div.className = 'enhRow';
    div.innerHTML = `
      <div>
        <label>${idx === 0 ? 'Option' : ''}</label>
        <select data-idx="${idx}" class="enhDesc" ${IS_READONLY ? 'disabled' : ''}>
          <option value="">&mdash; none &mdash;</option>
          ${opts.map(o => `<option value="${esc(o.desc)}" ${o.desc === row.desc ? 'selected' : ''}>${esc(o.desc)}</option>`).join('')}
        </select>
      </div>
      <div>
        <label>${idx === 0 ? 'Qty' : ''}</label>
        <input type="number" min="1" class="enhQty" data-idx="${idx}" value="${row.qty || 1}" ${IS_READONLY ? 'disabled' : ''}>
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
  const showDiscount = Number(state.baseShowDiscount) || 0;
  const baseList = (bp.list || 0) + showDiscount;
  const baseUnit = bp.unit || 0;
  const baseExt = baseQty * baseUnit;

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

  const freightVal = Number(state.freight) || 0;
  const subtotalVal = baseExt + enhTotal;
  const totalVal = subtotalVal + freightVal;

  return {
    base_msrp: base.msrp || 0,
    base_list_price: baseList,
    base_unit_price: baseUnit,
    subtotal: subtotalVal,
    total: totalVal,
    enhancements: enhRows,
    quote_code: currentQuoteCode(),
    customer: state.toCompany
  };
}

function renderPreview() {
  const p = document.getElementById('preview');
  if (!p) return;

  const qcode = currentQuoteCode();
  const seqEl = document.getElementById('quoteSeq');
  if (seqEl && document.activeElement !== seqEl) seqEl.value = state.quoteSeq || '';

  const base = baseItem(state.model);
  const bp = priceForItem(base);
  const rawDiscount = Number(state.baseShowDiscount) || 0;
  const showDiscount = Math.min(20000, rawDiscount);
  const discWarnEl = document.getElementById('discountWarning');
  if (discWarnEl) discWarnEl.style.display = rawDiscount > 20000 ? 'block' : 'none';

  const displayBaseList = (bp.list || 0) + showDiscount;
  const baseExt = state.baseQty * bp.unit;
  const baseDisc = displayBaseList ? 1 - (bp.unit / displayBaseList) : 0;

  let enhExtTotal = 0;
  const enhRowData = [];
  state.enh.forEach(row => {
    if (!row.desc) return;
    const item = enhOptions(state.model).find(o => o.desc === row.desc);
    if (!item) return;
    const p = priceForItem(item);
    const ext = row.qty * p.unit;
    enhExtTotal += ext;
    const disc = p.list ? 1 - (p.unit / p.list) : 0;
    enhRowData.push({ desc: item.desc, qty: row.qty, list: p.list, unit: p.unit, ext, disc });
  });

  const anyDisc = Math.abs(baseDisc) >= 0.0005 || enhRowData.some(r => Math.abs(r.disc) >= 0.0005);
  const cols = anyDisc ? 6 : 5;
  const discTh = anyDisc ? '<th class="num">Discount</th>' : '';

  const enhRowsHtml = enhRowData.map(r => `
    <tr>
      <td>${esc(r.desc)}</td>
      <td class="num">${r.qty}</td>
      <td class="num">${fmt(r.list)}</td>
      ${anyDisc ? `<td class="num">${pctFmt(r.disc)}</td>` : ''}
      <td class="num">${fmt(r.unit)}</td>
      <td class="num">${fmt(r.ext)}</td>
    </tr>
  `).join('');

  const freight = Number(state.freight) || 0;
  const subtotal = baseExt + enhExtTotal;
  const total = subtotal + freight;
  const coopBasis = COOP_BASES[state.basis] || null;
  const subtotalLabel = coopBasis ? coopBasis.subtotalLabel : 'MSRP Subtotal';
  const disclaimer = coopBasis ? coopBasis.disclaimer : '';

  const coopLogo = coopBasis ? coopBasis.logo : null;
  const coopCaption = coopBasis ? coopBasis.caption : '';
  const coopRowHtml = coopLogo ? `
    <tr class="coopRow">
      <td colspan="${cols - 2}" style="text-align:center;padding:12px 6px;">
        <img src="${coopLogo}" style="height:38px;max-width:180px;object-fit:contain;vertical-align:middle;" alt="Cooperative Logo">
      </td>
      <td colspan="2" style="font-size:10.5px;color:var(--gray);text-align:right;vertical-align:middle;padding:12px 8px;line-height:1.3;">
        ${coopCaption}
      </td>
    </tr>
  ` : '';

  const toLines = [state.toCompany, state.toAddr1, state.toAddr2, state.toContact].filter(Boolean).map(esc);
  const toBlockHtml = toLines.length 
    ? `<span class="toLines">${toLines.map(l => `<div>${l}</div>`).join('')}</span>`
    : ' &mdash;';

  p.innerHTML = `
    <div class="docHeaderTop">
      <div class="co">
        <img src="{{ asset('images/logo.jpg') }}" alt="ODRA Road Sweepers">
        <div class="addr">
          705 South 48th Street, Grand Forks, ND 58201<br>
          t: 844-888-6372 &bull; e: info@odrasweeper.com &bull; odrasweeper.com
        </div>
      </div>
      <div class="qbox">
        <div class="ttl">QUOTATION</div>
        <table>
          <tr><td class="k">Quote Code</td><td>${esc(qcode)}</td></tr>
          <tr><td class="k">Date</td><td>${fmtDateDisplay(state.date)}</td></tr>
        </table>
      </div>
    </div>

    ${disclaimer ? `<div class="disclaimerLine">${disclaimer}</div>` : ''}

    <div class="infoGrid">
      <div class="col">
        <div><span class="k">Model:</span> ${esc(state.model)}</div>
        <div><span class="k">Chassis / Config:</span> ${esc(shortChassisLabel(state.chassis))}</div>
        <div><span class="k">FOB / Transport:</span> ${esc(state.fob)}</div>
        <div class="toBlock"><span class="k">To:</span>${toBlockHtml}</div>
      </div>
      <div class="col">
        <div><span class="k">Pricing Basis:</span> ${esc(coopBasis ? coopBasis.label : state.basis)}</div>
        <div><span class="k">P.O. No.:</span> ${esc(state.po) || '&mdash;'}</div>
        <div><span class="k">Customer Acct #:</span> ${esc(state.acct) || '&mdash;'}</div>
        <div><span class="k">Sales Rep:</span> ${esc(state.salesRep) || '&mdash;'}</div>
      </div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>Description</th>
          <th class="num">Qty</th>
          <th class="num">MSRP</th>
          ${discTh}
          <th class="num">Unit Price</th>
          <th class="num">Extended</th>
        </tr>
      </thead>
      <tbody>
        <tr class="baseRow">
          <td>${esc(dynamicBaseDesc(base, state.chassis, state.model))}</td>
          <td class="num">${state.baseQty}</td>
          <td class="num">${fmt(displayBaseList)}</td>
          ${anyDisc ? `<td class="num">${pctFmt(baseDisc)}</td>` : ''}
          <td class="num">${fmt(bp.unit)}</td>
          <td class="num">${fmt(baseExt)}</td>
        </tr>
        <tr class="sectionRow">
          <td colspan="${cols}">Including Enhancements</td>
        </tr>
        ${enhRowsHtml || `<tr><td colspan="${cols}" style="color:#999;font-style:italic;">No enhancements selected</td></tr>`}
        ${coopRowHtml}
        <tr class="subtotalRow">
          <td colspan="${cols - 2}"></td>
          <td class="num">${subtotalLabel}</td>
          <td class="num">${fmt(subtotal)}</td>
        </tr>
        <tr>
          <td>Sweeper Transport / Freight</td>
          <td class="num">${state.fob === 'EXW, Factory pick-up' ? 0 : 1}</td>
          <td class="num"></td>
          ${anyDisc ? '<td class="num"></td>' : ''}
          <td class="num">${fmt(freight)}</td>
          <td class="num">${fmt(freight)}</td>
        </tr>
        <tr class="totalRow">
          <td colspan="${cols - 2}"></td>
          <td class="num">TOTAL (USD)</td>
          <td class="num">${fmt(total)}</td>
        </tr>
      </tbody>
    </table>

    ${legalHtml(state.model)}
  `;
}

function render() {
  populateChassis();
  populateRepTerritory();
  applyTerritoryFreight();
  buildEnhRows();
  renderPreview();
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
    modelEl.value = state.model || 'MT4H';
    modelEl.addEventListener('change', e => {
      state.model = e.target.value;
      render();
    });
  }

  const basisEl = document.getElementById('basis');
  if (basisEl) {
    basisEl.value = state.basis || 'MSRP (Commercial)';
    basisEl.addEventListener('change', e => {
      state.basis = e.target.value;
      applyTerritoryFreight();
      render();
    });
  }

  const chassisEl = document.getElementById('chassis');
  if (chassisEl) {
    chassisEl.addEventListener('change', e => {
      state.chassis = e.target.value;
      render();
    });
  }

  const salesRepEl = document.getElementById('salesRep');
  if (salesRepEl) {
    salesRepEl.value = state.salesRep || '';
    salesRepEl.addEventListener('change', e => {
      state.salesRep = e.target.value;
      populateRepTerritory();
      applyTerritoryFreight();
      render();
    });
  }

  bindSimple('po', 'po');
  bindSimple('fob', 'fob');
  bindSimple('acct', 'acct');
  bindSimple('toContact', 'toContact');
  bindSimple('toCompany', 'toCompany');
  bindSimple('toAddr1', 'toAddr1');
  bindSimple('toAddr2', 'toAddr2');
  bindSimple('baseQty', 'baseQty', v => Math.max(1, parseInt(v) || 1));
  bindSimple('baseShowDiscount', 'baseShowDiscount', v => Math.max(0, parseFloat(v) || 0));
  bindSimple('freight', 'freight', v => Math.max(0, parseFloat(v) || 0));

  const repTerritoryEl = document.getElementById('repTerritory');
  if (repTerritoryEl) {
    repTerritoryEl.addEventListener('change', e => {
      state.repTerritory = e.target.value;
      applyTerritoryFreight();
      render();
    });
  }

  // Pre-fill input values from state
  const inputsToSync = ['po', 'acct', 'toCompany', 'toContact', 'toAddr1', 'toAddr2', 'baseQty', 'baseShowDiscount'];
  inputsToSync.forEach(id => {
    const el = document.getElementById(id);
    if (el && state[id] !== undefined) {
      el.value = state[id];
    }
  });

  const fobEl = document.getElementById('fob');
  if (fobEl && state.fob) fobEl.value = state.fob;

  // Print button
  document.getElementById('printBtn')?.addEventListener('click', () => window.print());

  // Download PDF button
  document.getElementById('downloadBtn')?.addEventListener('click', () => {
    const el = document.getElementById('preview');
    const opt = {
      margin: [10, 8, 10, 8],
      filename: `ODRA_Quote_${currentQuoteCode()}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
      jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };
    html2pdf().set(opt).from(el).save();
  });

  render();
}

// API Interactions for Save Draft & Finalize Quote
async function saveQuote(isFinalize = false) {
  if (!state.toCompany || !state.toCompany.trim()) {
    alert('Please enter a Customer Company name before saving.');
    document.getElementById('toCompany')?.focus();
    return;
  }

  if (isFinalize && !confirm('Finalize this quotation? The saved prices and snapshot will be permanently frozen.')) {
    return;
  }

  const snap = calculateSnapshotData();
  const payload = {
    quote_number: state.quoteSeq,
    customer: state.toCompany,
    model_description: baseItem(state.model).desc,
    base_msrp: snap.base_msrp,
    base_list_price: snap.base_list_price,
    base_unit_price: snap.base_unit_price,
    subtotal: snap.subtotal,
    total: snap.total,
    quote_code: snap.quote_code,
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
