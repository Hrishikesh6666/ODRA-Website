@extends('layouts.app')

@section('title', ($quote ? 'Quotation Q-' . $quoteNumber : 'New Quotation'))

@push('styles')
<style>
    .builder-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 14px 20px;
        border-radius: 8px;
        border: 1px solid var(--border);
        margin-bottom: 20px;
    }
    .builder-heading h1 {
        font-size: 20px;
        margin: 0 0 4px 0;
        font-weight: 700;
    }
    .builder-heading span {
        font-size: 13px;
        color: var(--text-muted);
    }
    .builder-actions {
        display: flex;
        gap: 10px;
    }
    .btn-secondary {
        background: white;
        color: var(--text-dark);
        border: 1px solid var(--border);
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-secondary:hover { background: #f8fafc; }
    .btn-primary {
        background: var(--red);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-primary:hover { background: var(--red-hover); }
    .builder-layout {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }
    .builder-form {
        flex: 0 0 380px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--border);
        max-height: calc(100vh - 140px);
        overflow-y: auto;
    }
    .builder-preview-wrap {
        flex: 1;
        background: #e2e8f0;
        padding: 24px;
        border-radius: 8px;
        overflow-x: auto;
        display: flex;
        justify-content: center;
    }
    .quote-paper {
        background: white;
        width: 800px;
        min-height: 1050px;
        padding: 40px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        color: #1a1a1a;
        font-size: 13px;
    }
    .sec-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--red);
        border-bottom: 2px solid #fecaca;
        padding-bottom: 4px;
        margin: 18px 0 12px 0;
        letter-spacing: 0.5px;
    }
    .form-group { margin-bottom: 12px; }
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }
    .form-control {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 13px;
        box-sizing: border-box;
        background: white;
    }
    .form-control:focus { outline: none; border-color: var(--red); }
    .form-row2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .smallnote { font-size: 11px; color: #94a3b8; font-weight: normal; }
    .enh-row {
        display: grid;
        grid-template-columns: 1fr 60px;
        gap: 8px;
        margin-bottom: 6px;
    }
    /* PDF Preview Quotation Styles matching original */
    .brandbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #ac2529;
        padding-bottom: 12px;
        margin-bottom: 18px;
    }
    .brandbar img { height: 48px; }
    .brandbar h1 { font-size: 20px; color: #ac2529; margin: 0; }
    .q-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 18px;
        font-size: 12px;
    }
    .q-table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0;
        font-size: 12px;
    }
    .q-table th {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        text-align: left;
        font-weight: bold;
    }
    .q-table td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        vertical-align: top;
    }
    .legal-text {
        font-size: 10px;
        color: #475569;
        line-height: 1.4;
        margin-top: 20px;
        border-top: 1px solid #cbd5e1;
        padding-top: 12px;
    }
</style>
@endpush

@section('content')
<div class="builder-topbar">
    <div class="builder-heading">
        <h1>Quotation Q-{{ $quoteNumber }}</h1>
        <span>Version {{ $version }} &middot; {{ $isReadonly ? 'Finalized (Historical Snapshot Frozen)' : 'Draft (Editable)' }}</span>
    </div>
    <div class="builder-actions">
        <a href="{{ route('sales.quotes.index') }}" class="btn-secondary">&larr; My Quotations</a>
        @if ($isReadonly)
            <button id="btnRevise" class="btn-secondary">Create Revision</button>
            <a href="{{ route('sales.quotes.pdf', $quote) }}" class="btn-primary" target="_blank">Download PDF</a>
        @else
            <button id="btnSaveDraft" class="btn-secondary">Save Draft</button>
            <button id="btnFinalize" class="btn-primary">Finalize Quote</button>
        @endif
    </div>
</div>

<div class="builder-layout">
    <!-- Builder Controls -->
    <div class="builder-form">
        <div class="sec-title">Quote Header</div>
        <div class="form-row2">
            <div class="form-group">
                <label>Quote No.</label>
                <input type="text" id="quoteSeq" class="form-control" value="{{ $quoteNumber }}" readonly>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" id="quoteDate" class="form-control">
            </div>
        </div>

        <div class="sec-title">Unit &amp; Pricing</div>
        <div class="form-group">
            <label>Model</label>
            <select id="model" class="form-control"></select>
        </div>
        <div class="form-group">
            <label>Pricing Basis</label>
            <select id="basis" class="form-control"></select>
        </div>
        <div class="form-group">
            <label>Chassis / Discharge Configuration</label>
            <select id="chassis" class="form-control"></select>
        </div>

        <div class="sec-title">Customer &amp; Order Info</div>
        <div class="form-row2">
            <div class="form-group">
                <label>P.O. No.</label>
                <input type="text" id="po" class="form-control">
            </div>
            <div class="form-group">
                <label>Customer Acct #</label>
                <input type="text" id="acct" class="form-control">
            </div>
        </div>
        <div class="form-row2">
            <div class="form-group">
                <label>Sales Rep</label>
                <input type="text" id="salesRep" class="form-control" value="{{ $user->name }}" readonly>
            </div>
            <div class="form-group">
                <label>FOB / Transport</label>
                <select id="fob" class="form-control">
                    <option value="Transport, estimate only noted in quote">Transport, estimate only noted in quote</option>
                    <option value="EXW, Factory pick-up">EXW, Factory pick-up</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>To (Company Name) <span style="color:var(--red);">*</span></label>
            <input type="text" id="toCompany" class="form-control" placeholder="Company Name" required>
        </div>
        <div class="form-group">
            <input type="text" id="toAddr1" class="form-control" placeholder="Address Line 1">
        </div>
        <div class="form-group">
            <input type="text" id="toAddr2" class="form-control" placeholder="Address Line 2">
        </div>
        <div class="form-group">
            <input type="text" id="toContact" class="form-control" placeholder="Contact Name / Attn">
        </div>

        <div class="sec-title">Base Unit Qty &amp; Discount</div>
        <div class="form-group">
            <label>Base Quantity</label>
            <input type="number" id="baseQty" class="form-control" value="1" min="1">
        </div>
        <div class="form-group">
            <label>Show Extra Discount On Quote (USD) <span class="smallnote">optional &mdash; inflates displayed List Price</span></label>
            <input type="number" id="baseShowDiscount" class="form-control" value="0" min="0" max="20000">
        </div>

        <div class="sec-title">Enhancements <span class="smallnote">(pick up to 13 options)</span></div>
        <div id="enhList"></div>

        <div class="sec-title">Freight Calculation</div>
        <div class="form-group">
            <label>Rep Territory <span class="smallnote">auto-calculates freight</span></label>
            <select id="repTerritory" class="form-control"></select>
        </div>
        <div class="form-group">
            <label>Freight Estimate (USD)</label>
            <input type="number" id="freight" class="form-control" value="0" min="0" readonly>
            <div id="exwFreightNote" style="display:none;color:#64748b;font-size:11px;margin-top:4px;font-style:italic;">FOB is set to EXW, Factory pick-up &mdash; freight is $0.</div>
        </div>
    </div>

    <!-- Quotation Preview -->
    <div class="builder-preview-wrap">
        <div id="preview" class="quote-paper"></div>
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

let state = { ...INITIAL_STATE };
let ITEMS = CATALOG.items || [];
let CHASSIS = CATALOG.chassis || [];
let CHASSIS_SHORT = CATALOG.chassis_short || {};
let FREIGHT_ESTIMATES = CATALOG.freight || [];
let TERRITORIES = CATALOG.territories || [];
let PRICING_BASES = CATALOG.pricing_bases || [];

const COOP_BASES = {
  'Sourcewell': { priceField: 'swNet', subtotalLabel: 'Sourcewell Subtotal', logo: 'sourcewell', showDiscount: true },
  'H-GAC / HGACBuy': { priceField: 'swNet', subtotalLabel: 'H-GAC Subtotal', logo: 'hgac', showDiscount: true },
  'State of Minnesota': { priceField: 'swNet', subtotalLabel: 'State Contract Subtotal', logo: 'minnesota', showDiscount: true },
  'Buyboard': { priceField: 'swNet', subtotalLabel: 'BuyBoard Subtotal', logo: 'buyboard', showDiscount: true }
};

const SR_CODES = {
  "Carlos Silva":"CS", "John Wells":"JW", "Kerry Briggs":"KB",
  "Paul Piotrowski":"PP", "Ryan Khosrofian":"RK", "Shawn Herringer":"SH", "Juan Oliver":"JO",
  "Robert Yuska":"RY", "Chris Norlin":"CN", "Kra Whitmore":"KW", "Fernando Oliveira":"FO",
  "Troy Fisher":"TF", "Sean Chenoweth":"SC", "Jason Coover":"JC", "Alex Byczynski":"AB",
  "JJ Rickis":"JR"
};

function fmt(n) { return (n === null || n === undefined || isNaN(n)) ? '' : n.toLocaleString('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }); }
function esc(s) { return (s === undefined || s === null) ? '' : String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

function itemsForModel(m) { return ITEMS.filter(i => i.model === m); }
function baseItem(m) { return itemsForModel(m).find(i => i.isBase) || {}; }
function enhOptions(m) { return itemsForModel(m).filter(i => !i.isBase); }
function chassisForModel(m) { return CHASSIS.filter(c => c.indexOf(m) === 0); }

function priceForItem(item) {
  if (!item) return { list: 0, unit: 0 };
  if (COOP_BASES[state.basis]) return { list: item.swList || 0, unit: item.swNet || 0 };
  return { list: item.msrp || 0, unit: item.msrp || 0 };
}

function territoryMaxFreight(code, useMunicipality) {
  const t = TERRITORIES.find(x => x.code === code);
  if (!t) return 0;
  const rows = t.cities && t.cities.length
    ? FREIGHT_ESTIMATES.filter(r => t.cities.includes(r.city))
    : FREIGHT_ESTIMATES.filter(r => t.states && t.states.includes(r.state));
  if (!rows.length) return 0;
  const vals = rows.map(r => useMunicipality ? r.municipality : r.customer);
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

function quoteCodePrefix() {
  const srCode = SR_CODES[state.salesRep] || (state.salesRep ? state.salesRep.slice(0, 2).toUpperCase() : 'OD');
  let ddmmyyyy = '';
  if (state.date) {
    const parts = state.date.split('-');
    ddmmyyyy = `${parts[2]}${parts[1]}${parts[0]}`;
  }
  return `${srCode}${ddmmyyyy}`;
}

function computeQuoteCode() {
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
  <div class="legal-text">
    ${standardInclusionsHtml(model)}
    <p>Delivery transport charge, shown as an estimate ONLY. Actual rate to be determined at time of invoicing.</p>
    <p>Charges relating to taxes, registration, titling, and other fees are the responsibility of the customer; not included in this quotation. Customer takes responsibility for adherence to local and state laws for chassis, emissions, and registration relating to the sweeper.</p>
    <p>A <b>$10,000 deposit and Purchase Order number</b> is requested at time of quotation acceptance. Balance will be due prior to shipping.</p>
    <p>This quote is <b>BUDGETARY ONLY</b>, valid 21 days from date of quote. ODRA LLC may modify or cancel without notice.</p>
  </div>`;
}

function calculateSnapshotData() {
  const base = priceForItem(baseItem(state.model));
  const baseQty = Number(state.baseQty) || 1;
  const showDiscount = Number(state.baseShowDiscount) || 0;
  const baseList = (base.list || 0) + showDiscount;
  const baseUnit = base.unit || 0;
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
    quote_code: computeQuoteCode(),
    customer: state.toCompany
  };
}

function renderPreview() {
  const p = document.getElementById('preview');
  if (!p) return;

  const base = priceForItem(baseItem(state.model));
  const baseQty = Number(state.baseQty) || 1;
  const showDiscount = Number(state.baseShowDiscount) || 0;
  const isCoop = !!COOP_BASES[state.basis];
  const snap = calculateSnapshotData();

  let enhRowsHtml = '';
  for (const item of snap.enhancements) {
    enhRowsHtml += `
      <tr>
        <td>${esc(item.desc)}</td>
        <td style="text-align:center;">${item.qty}</td>
        ${isCoop ? `<td style="text-align:right;">${fmt(item.list)}</td>` : ''}
        <td style="text-align:right;">${fmt(item.unit)}</td>
        <td style="text-align:right;"><strong>${fmt(item.extended)}</strong></td>
      </tr>
    `;
  }

  p.innerHTML = `
    <div class="brandbar">
      <img src="{{ asset('images/logo.jpg') }}" alt="ODRA Logo">
      <div>
        <h1>QUOTATION</h1>
        <div style="font-size:12px;color:#64748b;text-align:right;">Quote Code: <strong>${esc(snap.quote_code)}</strong></div>
      </div>
    </div>

    <div class="q-meta">
      <div>
        <div><strong>To:</strong> ${esc(state.toCompany || 'Customer Name')}</div>
        ${state.toContact ? `<div><strong>Attn:</strong> ${esc(state.toContact)}</div>` : ''}
        ${state.toAddr1 ? `<div>${esc(state.toAddr1)}</div>` : ''}
        ${state.toAddr2 ? `<div>${esc(state.toAddr2)}</div>` : ''}
      </div>
      <div style="text-align:right;">
        <div><strong>Quote #:</strong> Q-${esc(state.quoteSeq)}</div>
        <div><strong>Date:</strong> ${esc(state.date)}</div>
        <div><strong>Sales Rep:</strong> ${esc(state.salesRep)}</div>
        <div><strong>Pricing Basis:</strong> ${esc(state.basis)}</div>
      </div>
    </div>

    <table class="q-table">
      <thead>
        <tr>
          <th>Description</th>
          <th style="width:50px;text-align:center;">Qty</th>
          ${isCoop ? `<th style="width:100px;text-align:right;">List Price</th>` : ''}
          <th style="width:100px;text-align:right;">Unit Price</th>
          <th style="width:110px;text-align:right;">Extended</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>${esc(state.model)} Sweeper</strong><br>
            <span style="color:#64748b;font-size:11px;">${esc(state.chassis || 'Standard Chassis Configuration')}</span>
          </td>
          <td style="text-align:center;">${baseQty}</td>
          ${isCoop ? `<td style="text-align:right;">${fmt(snap.base_list_price)}</td>` : ''}
          <td style="text-align:right;">${fmt(snap.base_unit_price)}</td>
          <td style="text-align:right;"><strong>${fmt(baseQty * snap.base_unit_price)}</strong></td>
        </tr>
        ${enhRowsHtml}
        <tr>
          <td colspan="${isCoop ? 4 : 3}" style="text-align:right;font-weight:bold;">Subtotal</td>
          <td style="text-align:right;font-weight:bold;">${fmt(snap.subtotal)}</td>
        </tr>
        <tr>
          <td colspan="${isCoop ? 4 : 3}" style="text-align:right;">Freight / Delivery Estimate (${esc(state.fob)})</td>
          <td style="text-align:right;">${fmt(state.freight)}</td>
        </tr>
        <tr style="background:#f8fafc;font-size:14px;">
          <td colspan="${isCoop ? 4 : 3}" style="text-align:right;font-weight:bold;color:var(--red);">Total (USD)</td>
          <td style="text-align:right;font-weight:bold;color:var(--red);">${fmt(snap.total)}</td>
        </tr>
      </tbody>
    </table>

    ${legalHtml(state.model)}
  `;
}

function initForm() {
  // Populate Model dropdown
  const modelSel = document.getElementById('model');
  const models = [...new Set(ITEMS.map(i => i.model))];
  modelSel.innerHTML = models.map(m => `<option value="${m}" ${m === state.model ? 'selected' : ''}>${m}</option>`).join('');

  // Populate Basis dropdown
  const basisSel = document.getElementById('basis');
  const bases = ['MSRP (Commercial)', ...PRICING_BASES.map(b => b.name)];
  basisSel.innerHTML = [...new Set(bases)].map(b => `<option value="${b}" ${b === state.basis ? 'selected' : ''}>${b}</option>`).join('');

  // Populate Chassis dropdown
  updateChassisOptions();

  // Populate Enhancements list
  updateEnhancementsList();

  // Populate Rep Territories
  populateRepTerritory();
  applyTerritoryFreight();

  // Bind values
  document.getElementById('quoteDate').value = state.date || new Date().toISOString().slice(0, 10);
  document.getElementById('toCompany').value = state.toCompany || '';
  document.getElementById('toContact').value = state.toContact || '';
  document.getElementById('toAddr1').value = state.toAddr1 || '';
  document.getElementById('toAddr2').value = state.toAddr2 || '';
  document.getElementById('po').value = state.po || '';
  document.getElementById('acct').value = state.acct || '';
  document.getElementById('fob').value = state.fob || 'Transport, estimate only noted in quote';
  document.getElementById('baseQty').value = state.baseQty || 1;
  document.getElementById('baseShowDiscount').value = state.baseShowDiscount || 0;

  // Event Listeners
  modelSel.onchange = () => {
    state.model = modelSel.value;
    updateChassisOptions();
    updateEnhancementsList();
    renderPreview();
  };

  basisSel.onchange = () => {
    state.basis = basisSel.value;
    applyTerritoryFreight();
    renderPreview();
  };

  document.getElementById('chassis').onchange = (e) => { state.chassis = e.target.value; renderPreview(); };
  document.getElementById('quoteDate').onchange = (e) => { state.date = e.target.value; renderPreview(); };
  document.getElementById('toCompany').oninput = (e) => { state.toCompany = e.target.value; renderPreview(); };
  document.getElementById('toContact').oninput = (e) => { state.toContact = e.target.value; renderPreview(); };
  document.getElementById('toAddr1').oninput = (e) => { state.toAddr1 = e.target.value; renderPreview(); };
  document.getElementById('toAddr2').oninput = (e) => { state.toAddr2 = e.target.value; renderPreview(); };
  document.getElementById('po').oninput = (e) => { state.po = e.target.value; renderPreview(); };
  document.getElementById('acct').oninput = (e) => { state.acct = e.target.value; renderPreview(); };
  document.getElementById('fob').onchange = (e) => { state.fob = e.target.value; applyTerritoryFreight(); renderPreview(); };
  document.getElementById('baseQty').oninput = (e) => { state.baseQty = e.target.value; renderPreview(); };
  document.getElementById('baseShowDiscount').oninput = (e) => { state.baseShowDiscount = e.target.value; renderPreview(); };
  document.getElementById('repTerritory').onchange = (e) => { state.repTerritory = e.target.value; applyTerritoryFreight(); renderPreview(); };

  if (IS_READONLY) {
    document.querySelectorAll('.builder-form input, .builder-form select').forEach(el => el.disabled = true);
  }

  renderPreview();
}

function updateChassisOptions() {
  const chassisSel = document.getElementById('chassis');
  const configs = chassisForModel(state.model);
  const keep = configs.includes(state.chassis) ? state.chassis : configs[0] || '';
  chassisSel.innerHTML = configs.map(c => `<option value="${c}" ${c === keep ? 'selected' : ''}>${c}</option>`).join('');
  state.chassis = keep;
}

function updateEnhancementsList() {
  const container = document.getElementById('enhList');
  const options = enhOptions(state.model);

  let html = '';
  for (let i = 0; i < 13; i++) {
    const cur = state.enh[i] || { desc: '', qty: 1 };
    html += `
      <div class="enh-row">
        <select class="form-control enh-desc" data-idx="${i}" ${IS_READONLY ? 'disabled' : ''}>
          <option value="">&mdash; Select Option &mdash;</option>
          ${options.map(opt => `<option value="${esc(opt.desc)}" ${opt.desc === cur.desc ? 'selected' : ''}>${esc(opt.cat)}: ${esc(opt.desc)} (${fmt(priceForItem(opt).unit)})</option>`).join('')}
        </select>
        <input type="number" class="form-control enh-qty" data-idx="${i}" value="${cur.qty || 1}" min="1" ${IS_READONLY ? 'disabled' : ''}>
      </div>
    `;
  }
  container.innerHTML = html;

  container.querySelectorAll('.enh-desc').forEach(el => {
    el.onchange = (e) => {
      const idx = e.target.dataset.idx;
      state.enh[idx].desc = e.target.value;
      renderPreview();
    };
  });

  container.querySelectorAll('.enh-qty').forEach(el => {
    el.oninput = (e) => {
      const idx = e.target.dataset.idx;
      state.enh[idx].qty = Number(e.target.value) || 1;
      renderPreview();
    };
  });
}

// API Interactions
async function saveQuote(isFinalize = false) {
  if (!state.toCompany || !state.toCompany.trim()) {
    alert('Please enter a Customer Company name before saving.');
    document.getElementById('toCompany').focus();
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

  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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

    showToast(result.message);

    if (isFinalize) {
      // Generate and upload PDF
      showToast('Generating official PDF proposal...');
      const element = document.getElementById('preview');
      const opt = {
        margin: 0.3,
        filename: `ODRA_Quotation_Q-${state.quoteSeq}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
      };

      const pdfBase64 = await html2pdf().set(opt).from(element).outputPdf('datauristring');
      const quoteId = result.quote.id || CURRENT_QUOTE_ID;

      await fetch(`/api/quotes/${quoteId}/pdf`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ pdf: pdfBase64 })
      });

      showToast('Quotation finalized and PDF saved.');
      setTimeout(() => { window.location.href = `/sales/quotes/${quoteId}`; }, 1200);
    } else if (!CURRENT_QUOTE_ID && result.quote) {
      setTimeout(() => { window.location.href = `/sales/quotes/${result.quote.id}`; }, 1000);
    }
  } catch (err) {
    alert('Error: ' + err.message);
  }
}

document.getElementById('btnSaveDraft')?.addEventListener('click', () => saveQuote(false));
document.getElementById('btnFinalize')?.addEventListener('click', () => saveQuote(true));

document.getElementById('btnRevise')?.addEventListener('click', async () => {
  if (!confirm('Create a new draft revision from this finalized quote?')) return;
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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
    showToast(result.message);
    setTimeout(() => { window.location.href = `/sales/quotes/${CURRENT_QUOTE_ID}`; }, 1000);
  } catch (err) {
    alert('Error: ' + err.message);
  }
});

// Initialize on DOM load
window.addEventListener('DOMContentLoaded', initForm);
</script>
@endpush
