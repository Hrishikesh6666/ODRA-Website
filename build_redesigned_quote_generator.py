# -*- coding: utf-8 -*-
"""
Generator script to build the redesigned ODRA Quote Generator HTML file.
"""
import os
import re
import json

src_path = r"C:\Users\HrishikeshS\Desktop\xyz\ODRA_Quote_Generator_Rep_1 1.html"
backup_path = r"C:\Users\HrishikeshS\Desktop\xyz\ODRA_Quote_Generator_Rep_1 1.html.bak"

with open(src_path, "r", encoding="utf-8") as f:
    orig = f.read()

# 1. Extract html2pdf block
html2pdf_match = re.search(r"(\/\*![\s\S]*?sourceMappingURL=html2pdf\.bundle\.min\.js\.map<\/script>)", orig)
if not html2pdf_match:
    raise Exception("html2pdf bundle not found")
html2pdf_block = html2pdf_match.group(1)

# 2. Extract ODRA Logo
odra_logo_match = re.search(r'(data:image/jpeg;base64,[A-Za-z0-9+/=]+)', orig)
if not odra_logo_match:
    raise Exception("ODRA Logo not found")
odra_logo = odra_logo_match.group(1)

# 3. Extract SOURCEWELL_LOGO
sw_match = re.search(r"const SOURCEWELL_LOGO = '([^']+)';", orig)
if not sw_match:
    raise Exception("SOURCEWELL_LOGO not found")
sourcewell_logo = sw_match.group(1)

# 4. Extract HGAC_LOGO
hgac_match = re.search(r"const HGAC_LOGO = '([^']+)';", orig)
if not hgac_match:
    raise Exception("HGAC_LOGO not found")
hgac_logo = hgac_match.group(1)

# 5. Extract BUYBOARD_LOGO
bb_match = re.search(r"const BUYBOARD_LOGO = '([^']+)';", orig)
if not bb_match:
    raise Exception("BUYBOARD_LOGO not found")
buyboard_logo = bb_match.group(1)

# 6. Extract ITEMS
items_match = re.search(r"(let ITEMS = \[[\s\S]*?\];)", orig)
if not items_match:
    raise Exception("ITEMS not found")
items_code = items_match.group(1)

# 7. Extract FREIGHT_ESTIMATES
freight_match = re.search(r"(const FREIGHT_ESTIMATES = \[[\s\S]*?\];)", orig)
if not freight_match:
    raise Exception("FREIGHT_ESTIMATES not found")
freight_code = freight_match.group(1)

# 8. Extract SR_CODES and TERRITORIES
territories_match = re.search(r"(const SR_CODES = {[\s\S]*?const TERRITORIES = \[[\s\S]*?\];)", orig)
if not territories_match:
    raise Exception("TERRITORIES not found")
territories_code = territories_match.group(1)

print("All components successfully extracted.")

# Build HTML template
html_template = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>ODRA Quote Generator &mdash; Internal Sales Tool</title>
<script>
""" + html2pdf_block + """
<style>
:root{
  --red:#AC2529; --red-dark:#8a1e22; --gray:#585858; --line:#d9d9d9; --bg:#f4f4f5;
  --paper:#ffffff; --ink:#232323; --muted:#767676; --yellow:#fff8e1; --blue:#1d63b8;
  --green:#1b7e36;
}
*{box-sizing:border-box;}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:var(--ink);}
.app{display:flex;gap:20px;max-width:1540px;margin:0 auto;padding:20px;align-items:flex-start;}
.panel{background:var(--paper);border-radius:8px;padding:22px;box-shadow:0 1px 4px rgba(0,0,0,.08);}
.builder{width:540px;flex-shrink:0;}
.previewWrap{flex:1;min-width:0;}
.brandbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid #eee;}
.brandbar .brand-info{display:flex;align-items:center;gap:12px;}
.brandbar img{height:34px;}
.brandbar h1{font-size:16px;margin:0;color:var(--red);letter-spacing:.3px;}
.brandbar .tag{font-size:11px;color:var(--muted);margin-top:2px;}
.btn-config{background:#fff;color:var(--red);border:1.5px solid var(--red);border-radius:6px;padding:7px 13px;font-size:12.5px;font-weight:bold;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all .15s ease;}
.btn-config:hover{background:var(--red);color:#fff;}
h2.sec{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:var(--red);border-bottom:2px solid var(--red);padding-bottom:5px;margin:18px 0 10px;}
h2.sec:first-of-type{margin-top:0;}
label{display:block;font-size:11.5px;font-weight:bold;color:var(--gray);margin:9px 0 3px;}
input[type=text],input[type=number],input[type=date],select,textarea{
  width:100%;padding:6px 8px;font-size:13px;border:1px solid #c9c9c9;border-radius:5px;background:var(--yellow);font-family:inherit;
}
textarea{resize:vertical;min-height:46px;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.enhRow{display:grid;grid-template-columns:1fr 56px 92px;gap:6px;align-items:end;margin-bottom:6px;}
.enhRow label{margin:6px 0 3px;}
.smallnote{font-size:10.5px;color:var(--muted);margin-top:2px;font-weight:normal;}
.btnrow{display:flex;gap:10px;margin-top:18px;position:sticky;bottom:0;background:var(--paper);padding-top:12px;}
button{cursor:pointer;border:none;border-radius:6px;padding:10px 14px;font-size:13px;font-weight:bold;}
.btn-primary{background:var(--red);color:#fff;flex:1;}
.btn-primary:hover{background:var(--red-dark);}
.btn-secondary{background:#eee;color:#333;}
.btn-secondary:hover{background:#ddd;}

/* Quick pricing card in builder */
.pricing-summary-card{margin-top:10px;padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;font-size:11.5px;}
.pricing-summary-card .grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center;}
.pricing-summary-card .stat-label{font-size:10px;color:var(--muted);text-transform:uppercase;font-weight:600;}
.pricing-summary-card .stat-val{font-size:13px;font-weight:bold;color:#1a1a1a;margin-top:2px;}
.pricing-summary-card .stat-val.highlight{color:var(--red);}
.pricing-summary-card .card-foot{display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding-top:8px;border-top:1px dashed #e5e7eb;}
.badge-tag{display:inline-block;padding:2px 7px;border-radius:4px;font-size:10.5px;font-weight:bold;}
.badge-muni{background:#e0f2fe;color:#0369a1;}
.badge-comm{background:#f3f4f6;color:#4b5563;}
.btn-link-sm{background:none;border:none;color:var(--red);font-size:11px;font-weight:bold;padding:0;cursor:pointer;text-decoration:underline;}

/* Preview / printable document */
#preview{background:#fff;padding:34px 38px;font-size:12px;line-height:1.4;color:#1a1a1a;min-height:400px;}
.docHeaderTop{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid var(--red);padding-bottom:10px;margin-bottom:8px;}
.docHeaderTop .co img{height:46px;display:block;margin-bottom:4px;}
.docHeaderTop .co .addr{font-size:11px;color:var(--gray);}
.docHeaderTop .qbox{text-align:right;}
.docHeaderTop .qbox .ttl{font-size:20px;font-weight:bold;color:var(--red);letter-spacing:1px;}
.docHeaderTop .qbox table{margin-left:auto;font-size:11.5px;border-collapse:collapse;}
.docHeaderTop .qbox td{padding:1px 0 1px 10px;text-align:right;}
.docHeaderTop .qbox td.k{color:var(--gray);text-align:right;padding-left:14px;}
.disclaimerLine{font-size:11px;font-style:italic;color:var(--gray);margin-bottom:10px;}
.infoGrid{display:flex;gap:24px;font-size:11.5px;margin-bottom:14px;}
.infoGrid .col{flex:1;min-width:0;}
.infoGrid .col>div{margin-bottom:2px;}
.infoGrid .k{color:var(--gray);font-weight:bold;display:inline-block;min-width:110px;}
.infoGrid .toBlock .toLines{display:inline-block;vertical-align:top;}.infoGrid .toBlock .toLines>div{margin-bottom:2px;}
table.items{width:100%;border-collapse:collapse;margin-bottom:4px;}
table.items th{background:var(--red);color:#fff;font-size:10.5px;text-transform:uppercase;padding:7px 8px;text-align:left;}
table.items th.num,table.items td.num{text-align:right;}
table.items td{padding:6px 8px;border-bottom:1px solid #eee;font-size:11.5px;vertical-align:top;}
table.items tr.baseRow td{font-weight:bold;background:#faf6f6;}
table.items tr.noteRow td{font-style:italic;color:var(--muted);font-size:10.5px;border-bottom:1px solid #eee;padding-top:2px;padding-bottom:8px;}
table.items tr.sectionRow td{font-weight:bold;color:var(--red);border-bottom:1px solid #eee;padding-top:10px;}
table.items tr.subtotalRow td{border-top:2px solid var(--ink);font-weight:bold;padding-top:8px;}
table.items tr.totalRow td{font-weight:bold;font-size:13.5px;color:var(--red);border-top:1px solid #ccc;}
table.items tr.subtotalRow td,table.items tr.totalRow td{white-space:nowrap;}
.legal{margin-top:16px;border-top:1px solid var(--line);padding-top:10px;}
.legal p{font-size:9.7px;color:var(--gray);margin:0 0 6px;}
.legal p b{color:#1a1a1a;}
.emptyState{color:var(--muted);text-align:center;padding:60px 10px;font-size:13px;}

/* ---- CONFIGURATION MODAL ---- */
.cfg-modal-backdrop{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.65);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px);}
.cfg-modal-window{background:#fff;border-radius:10px;box-shadow:0 12px 40px rgba(0,0,0,0.3);width:100%;max-width:1250px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;animation:cfgFadeIn .2s ease-out;}
@keyframes cfgFadeIn{from{opacity:0;transform:scale(0.97);}to{opacity:1;transform:scale(1);}}
.cfg-modal-header{padding:16px 22px;background:#f8f9fa;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;}
.cfg-modal-header h2{margin:0;font-size:17px;color:var(--red);display:flex;align-items:center;gap:8px;}
.cfg-modal-header .cfg-sub{font-size:12px;color:var(--muted);margin-top:2px;}
.cfg-modal-close{background:none;border:none;font-size:24px;color:#888;cursor:pointer;line-height:1;padding:0 4px;}
.cfg-modal-close:hover{color:var(--red);}
.cfg-modal-body{padding:20px 22px;overflow-y:auto;flex:1;}
.cfg-card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 18px;margin-bottom:16px;box-shadow:0 1px 2px rgba(0,0,0,.03);}
.cfg-card-title{font-size:13px;font-weight:bold;color:#1e293b;text-transform:uppercase;letter-spacing:.04em;margin:0 0 12px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding-bottom:8px;}
.cfg-card-title .tag-num{background:var(--red);color:#fff;width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;margin-right:6px;}
.cfg-grid-top{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.cfg-btn-sm{padding:5px 10px;font-size:11.5px;border-radius:5px;font-weight:600;}
.cfg-btn-outline{background:#fff;border:1px solid #cbd5e1;color:#334155;}
.cfg-btn-outline:hover{background:#f1f5f9;}
.cfg-btn-danger{background:#fee2e2;border:1px solid #fca5a5;color:#b91c1c;}
.cfg-btn-danger:hover{background:#fecaca;}
.cfg-btn-success{background:#ecfdf5;border:1px solid #6ee7b7;color:#047857;}
.cfg-btn-success:hover{background:#d1fae5;}
.cfg-btn-primary-sm{background:var(--red);color:#fff;}
.cfg-btn-primary-sm:hover{background:var(--red-dark);}

/* Table in modal */
.cfg-table-wrap{overflow-x:auto;margin-top:10px;border:1px solid #e2e8f0;border-radius:6px;}
.cfg-table{width:100%;border-collapse:collapse;font-size:12px;background:#fff;}
.cfg-table th{background:#f8fafc;color:#475569;font-weight:700;text-transform:uppercase;font-size:10.5px;letter-spacing:.03em;padding:9px 10px;border-bottom:1px solid #cbd5e1;text-align:left;white-space:nowrap;}
.cfg-table td{padding:8px 10px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.cfg-table tr:hover td{background:#fafafa;}
.cfg-table input[type=text],.cfg-table input[type=number],.cfg-table select{
  padding:5px 7px;font-size:12px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;width:100%;
}
.cfg-table input[type=text]:focus,.cfg-table input[type=number]:focus,.cfg-table select:focus{
  border-color:var(--red);outline:none;background:#fffdfa;
}
.calc-badge{font-weight:bold;color:#0f766e;background:#ccfbf1;padding:4px 8px;border-radius:4px;display:inline-block;white-space:nowrap;font-size:12px;}
.logo-thumb{height:22px;max-width:70px;vertical-align:middle;object-fit:contain;margin-left:6px;border:1px solid #eee;border-radius:2px;background:#fff;padding:1px;}

/* Standard Features in modal */
.feat-list{display:flex;flex-direction:column;gap:8px;margin-top:10px;}
.feat-row{display:flex;gap:8px;align-items:flex-start;}
.feat-row textarea{font-size:12px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;padding:6px 8px;flex:1;min-height:48px;}

/* Modal footer */
.cfg-modal-footer{padding:14px 22px;background:#f8f9fa;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;}
.cfg-toast{position:fixed;bottom:24px;right:24px;background:#1e293b;color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.25);z-index:999999;display:none;animation:cfgToastIn .2s ease;}
@keyframes cfgToastIn{from{transform:translateY(15px);opacity:0;}to{transform:translateY(0);opacity:1;}}

@media print{
  body{background:#fff;}
  .builder,.btnrow,.internalPanel,.app-toolbar,.btn-config,.cfg-modal-backdrop{display:none !important;}
  .app{display:block;padding:0;max-width:none;}
  .previewWrap{width:100%;}
}
</style>
</head>
<body>
<div class="app">
  <div class="panel builder">
    <div class="brandbar">
      <div class="brand-info">
        <img src='""" + odra_logo + """' alt="ODRA Logo">
        <div>
          <h1>Quote Builder</h1>
          <div class="tag">Internal Sales Quoting System</div>
        </div>
      </div>
      <button type="button" class="btn-config" id="openConfigBtn" title="Redesign & manage Model, Chassis, Pricing and Features">
        ⚙️ Quotation Configuration
      </button>
    </div>

    <h2 class="sec">Quote Header</h2>
    <div class="row2">
      <label style="grid-column:1/-1">Quote No.</label>
      <input style="grid-column:1/-1" type="text" id="quoteSeq" maxlength="4" value="3001">
    </div>
    <div class="row2">
      <label style="grid-column:1/-1">Date</label>
      <input style="grid-column:1/-1" type="date" id="quoteDate">
    </div>

    <h2 class="sec">Unit &amp; Pricing</h2>
    <label>Model</label>
    <select id="model"></select>

    <label>Chassis / Discharge Configuration</label>
    <select id="chassis"></select>

    <label>Pricing Basis</label>
    <select id="basis"></select>

    <div class="pricing-summary-card" id="pricingSummaryCard">
      <div class="grid4">
        <div>
          <div class="stat-label">Base MSRP</div>
          <div class="stat-val" id="statMsrp">$0</div>
        </div>
        <div>
          <div class="stat-label">Discount %</div>
          <div class="stat-val" id="statDiscount">0%</div>
        </div>
        <div>
          <div class="stat-label">Base Unit Price</div>
          <div class="stat-val highlight" id="statUnitPrice">$0</div>
        </div>
        <div>
          <div class="stat-label">Contract Logo</div>
          <div class="stat-val" id="statLogo"><span style="color:#aaa;font-size:11px;">None</span></div>
        </div>
      </div>
      <div class="card-foot">
        <div>Freight Mode: <span class="badge-tag" id="statFreightBadge">Standard</span></div>
        <button type="button" class="btn-link-sm" id="quickConfigLink">⚙️ Configure this Model</button>
      </div>
    </div>

    <h2 class="sec">Customer &amp; Order Info</h2>
    <div class="row2">
      <div><label>P.O. No.</label><input type="text" id="po"></div>
      <div><label>Customer Acct #</label><input type="text" id="acct"></div>
    </div>
    <div class="row2">
      <div>
        <label>Sales Rep</label>
        <select id="salesRep">
          <option value="">&mdash; Select &mdash;</option>
          <option value="Carlos Silva">Carlos Silva</option>
          <option value="Chris Norlin">Chris Norlin</option>
          <option value="Fernando Oliveira">Fernando Oliveira</option>
          <option value="JJ Rickis">JJ Rickis</option>
          <option value="John Wells">John Wells</option>
          <option value="Juan Oliver">Juan Oliver</option>
          <option value="Kerry Briggs">Kerry Briggs</option>
          <option value="Kra Whitmore">Kra Whitmore</option>
          <option value="Paul Piotrowski">Paul Piotrowski</option>
          <option value="Robert Yuska">Robert Yuska</option>
          <option value="Ryan Khosrofian">Ryan Khosrofian</option>
          <option value="Sean Chenoweth">Sean Chenoweth</option>
          <option value="Shawn Herringer">Shawn Herringer</option>
          <option value="Troy Fisher">Troy Fisher</option>
          <option value="Jason Coover">Jason Coover</option>
          <option value="Alex Byczynski">Alex Byczynski</option>
        </select>
      </div>
      <div>
        <label>FOB / Transport</label>
        <select id="fob">
          <option value="Transport, estimate only noted in quote">Transport, estimate only noted in quote</option>
          <option value="EXW, Factory pick-up">EXW, Factory pick-up</option>
        </select>
      </div>
    </div>
    <label>To</label>
    <input type="text" id="toCompany" placeholder="Company Name">
    <input type="text" id="toAddr1" placeholder="Address 1" style="margin-top:6px;">
    <input type="text" id="toAddr2" placeholder="Address 2" style="margin-top:6px;">
    <input type="text" id="toContact" placeholder="Contact Name" style="margin-top:6px;">

    <h2 class="sec">Base Unit Qty</h2>
    <input type="number" id="baseQty" value="1" min="1">

    <label style="margin-top:10px;display:block;">Show Extra Discount On Quote (USD) <span class="smallnote">optional &mdash; inflates the base unit's displayed List Price by this amount and shows it as additional discount. Unit Price / Extended / Total charged to the customer are NOT affected.</span></label>
    <input type="number" id="baseShowDiscount" value="0" min="0" max="20000">
    <div id="discountWarning" style="display:none;color:#b3261e;font-size:11px;margin-top:4px;">Maximum limit allowed is $20,000 &mdash; amounts above this will be capped.</div>

    <h2 class="sec">Enhancements <span class="smallnote">(pick up to 13 options)</span></h2>
    <div id="enhList"></div>

    <h2 class="sec">Freight</h2>
    <label>Rep Territory <span class="smallnote">internal only &mdash; auto-calculates freight below; never shown on the customer quote</span></label>
    <select id="repTerritory" required></select>
    <label style="margin-top:10px;display:block;">Sweeper Transport / Freight Estimate (USD) <span class="smallnote">auto-fills to the highest rate in the selected Rep Territory; editable &mdash; re-selecting Rep Territory or changing Pricing Basis recomputes and overwrites it</span></label>
    <input type="number" id="freight" value="0" min="0" readonly>
    <div id="exwFreightNote" style="display:none;color:#666;font-size:11px;margin-top:4px;font-style:italic;">FOB is set to EXW, Factory pick-up &mdash; freight is $0 and does not apply to this quote.</div>

    <div class="btnrow">
      <button class="btn-primary" id="downloadBtn">Download PDF</button>
      <button class="btn-secondary" id="printBtn">Print</button>
    </div>
  </div>

  <div class="panel previewWrap">
    <div id="preview"></div>
  </div>
</div>

<!-- ==================== CONFIGURATION MODAL ==================== -->
<div id="configModalOverlay" class="cfg-modal-backdrop" style="display:none;">
  <div class="cfg-modal-window">
    <div class="cfg-modal-header">
      <div>
        <h2>⚙️ Quotation Configuration Manager</h2>
        <div class="cfg-sub">Simplified Model &rarr; Chassis Configuration &rarr; Pricing Basis &amp; Features Hierarchy</div>
      </div>
      <button type="button" class="cfg-modal-close" id="cfgCloseX">&times;</button>
    </div>

    <div class="cfg-modal-body">
      <!-- Top Grid: Model & Chassis Selection -->
      <div class="cfg-grid-top">
        <!-- Model Selection Card -->
        <div class="cfg-card">
          <div class="cfg-card-title">
            <span><span class="tag-num">1</span> Model Selection</span>
            <div style="display:flex;gap:6px;">
              <button type="button" class="cfg-btn-sm cfg-btn-primary-sm" id="cfgAddModelBtn">+ Add Model</button>
              <button type="button" class="cfg-btn-sm cfg-btn-danger" id="cfgDelModelBtn" title="Delete current model">Delete</button>
            </div>
          </div>
          <label style="margin-top:0;">Active Model</label>
          <select id="cfgModelSelect"></select>
          <div class="smallnote" style="margin-top:6px;">Each model maintains its own isolated chassis configurations, pricing bases, and descriptions.</div>
        </div>

        <!-- Chassis Configuration Selection Card -->
        <div class="cfg-card">
          <div class="cfg-card-title">
            <span><span class="tag-num">2</span> Chassis / Discharge Configuration</span>
            <div style="display:flex;gap:6px;">
              <button type="button" class="cfg-btn-sm cfg-btn-primary-sm" id="cfgAddChassisBtn">+ Add Config</button>
              <button type="button" class="cfg-btn-sm cfg-btn-danger" id="cfgDelChassisBtn" title="Delete current configuration">Delete</button>
            </div>
          </div>
          <label style="margin-top:0;">Configuration</label>
          <select id="cfgChassisSelect"></select>
          <div class="smallnote" style="margin-top:6px;">Configurations are separated cleanly so modifications never bleed into other chassis choices.</div>
        </div>
      </div>

      <!-- Dedicated Product Description -->
      <div class="cfg-card">
        <div class="cfg-card-title">
          <span>Dedicated Product Description (For Selected Configuration)</span>
        </div>
        <textarea id="cfgChassisDesc" rows="3" style="background:#fff;border:1px solid #cbd5e1;" placeholder="Enter specific description for this chassis configuration..."></textarea>
        <div class="smallnote" style="margin-top:4px;">This exact text will appear on quotations and generated PDFs when this configuration is selected.</div>
      </div>

      <!-- Independent Pricing Table Card -->
      <div class="cfg-card">
        <div class="cfg-card-title">
          <span><span class="tag-num">3</span> Pricing Bases &amp; Discounts for Selected Configuration</span>
          <button type="button" class="cfg-btn-sm cfg-btn-success" id="cfgAddBasisBtn">+ Add Pricing Basis</button>
        </div>
        <div class="smallnote" style="margin-bottom:8px;">Prices calculate automatically via <code>MSRP &times; (1 &minus; Discount/100)</code>. Modifying values here never affects other models or configurations.</div>
        <div class="cfg-table-wrap">
          <table class="cfg-table" id="cfgPricingTable">
            <thead>
              <tr>
                <th style="min-width:140px;">Pricing Basis</th>
                <th style="width:115px;">MSRP ($)</th>
                <th style="width:90px;">Discount (%)</th>
                <th style="width:130px;">Calculated Price</th>
                <th style="min-width:150px;">Subtotal Label</th>
                <th style="min-width:190px;">Contract Caption / Disclaimer</th>
                <th style="min-width:140px;">Logo</th>
                <th style="width:90px;text-align:center;">Muni Freight</th>
                <th style="width:60px;text-align:center;">Action</th>
              </tr>
            </thead>
            <tbody id="cfgPricingTbody"></tbody>
          </table>
        </div>
      </div>

      <!-- Standard Features Card -->
      <div class="cfg-card">
        <div class="cfg-card-title">
          <span><span class="tag-num">4</span> Standard Features</span>
          <div style="display:flex;gap:6px;">
            <button type="button" class="cfg-btn-sm cfg-btn-outline" id="cfgCopyCommercialBtn" title="Copy features from Commercial basis of this config">📋 Copy from Commercial</button>
            <button type="button" class="cfg-btn-sm cfg-btn-primary-sm" id="cfgAddFeatureBtn">+ Add Feature Bullet</button>
          </div>
        </div>
        <div class="smallnote">Features configured for the active Pricing Basis (<b><span id="cfgActiveBasisLabel">Commercial</span></b>):</div>
        <div class="feat-list" id="cfgFeatureList"></div>
      </div>

      <!-- Custom Logo Upload Card -->
      <div class="cfg-card" style="margin-bottom:0;">
        <div class="cfg-card-title">
          <span>Upload Custom Logo</span>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
          <input type="file" id="cfgLogoFileInput" accept="image/png,image/jpeg,image/svg+xml" style="background:#fff;border:1px solid #cbd5e1;padding:5px;font-size:12px;width:auto;">
          <input type="text" id="cfgLogoNameInput" placeholder="Logo Name (e.g. Texas DIR)" style="max-width:220px;background:#fff;border:1px solid #cbd5e1;font-size:12px;">
          <button type="button" class="cfg-btn-sm cfg-btn-primary-sm" id="cfgUploadLogoBtn">Add Logo to Registry</button>
          <div id="cfgLogoUploadPreview" style="display:none;align-items:center;gap:6px;">
            <img id="cfgLogoUploadThumb" src="" style="height:26px;border:1px solid #ddd;padding:2px;border-radius:3px;">
            <span style="font-size:11px;color:#16a34a;font-weight:600;">Uploaded!</span>
          </div>
        </div>
      </div>
    </div>

    <div class="cfg-modal-footer">
      <div>
        <button type="button" class="cfg-btn-sm cfg-btn-outline" id="cfgResetDefaultsBtn" style="color:#b91c1c;">🔄 Reset to Factory Defaults</button>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="button" class="btn-secondary" id="cfgCancelBtn" style="padding:8px 16px;">Cancel</button>
        <button type="button" class="btn-primary" id="cfgSaveBtn" style="padding:8px 20px;background:var(--red);">💾 Save &amp; Apply Changes</button>
      </div>
    </div>
  </div>
</div>

<div id="cfgToast" class="cfg-toast">Configuration updated successfully!</div>

<script>
""" + items_code + """
""" + freight_code + """
""" + territories_code + """

const SOURCEWELL_LOGO = '""" + sourcewell_logo + """';
const HGAC_LOGO = '""" + hgac_logo + """';
const BUYBOARD_LOGO = '""" + buyboard_logo + """';

// Logo Registry
const BUILTIN_LOGOS = {
  "none": { name: "No Logo", data: null },
  "sourcewell": { name: "Sourcewell", data: SOURCEWELL_LOGO },
  "hgac": { name: "H-GAC / HGACBuy", data: HGAC_LOGO },
  "buyboard": { name: "BuyBoard", data: BUYBOARD_LOGO }
};

function loadCustomLogos(){
  try{
    const raw = localStorage.getItem('ODRA_CUSTOM_LOGOS');
    if(raw) return JSON.parse(raw);
  }catch(e){}
  return {};
}
function saveCustomLogos(logos){
  localStorage.setItem('ODRA_CUSTOM_LOGOS', JSON.stringify(logos));
}
let CUSTOM_LOGOS = loadCustomLogos();

function getLogoData(key){
  if(!key || key === 'none') return null;
  if(BUILTIN_LOGOS[key]) return BUILTIN_LOGOS[key].data;
  if(CUSTOM_LOGOS[key]) return CUSTOM_LOGOS[key].data;
  return null;
}
function getAllLogoOptions(){
  const opts = [];
  for(const k in BUILTIN_LOGOS){
    opts.push({ key: k, name: BUILTIN_LOGOS[k].name, data: BUILTIN_LOGOS[k].data });
  }
  for(const k in CUSTOM_LOGOS){
    opts.push({ key: k, name: CUSTOM_LOGOS[k].name, data: CUSTOM_LOGOS[k].data });
  }
  return opts;
}

// -------------------------------------------------------------
// DEFAULT HIERARCHICAL CONFIGURATION STORE
// Model -> Chassis Configuration -> Dedicated Description & Pricing Bases
// -------------------------------------------------------------
const DEFAULT_CONFIG_STORE = {
  "MT4H": {
    code: "MT4H",
    name: "MT4H Mechanical Broom Sweeper",
    configurations: {
      "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper": {
        label: "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper",
        shortLabel: "MT4H Isuzu cabover, Class 5, non-CDL - LHD",
        description: "MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109\" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Isuzu NRR, 19,500 GVWR.",
        pricingBases: {
          "Commercial": {
            name: "MSRP (Commercial)",
            msrp: 257493,
            discount: 0,
            calculatedPrice: 257493,
            subtotalLabel: "MSRP Subtotal",
            caption: "",
            logoKey: "none",
            useMuniFreight: false,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Sourcewell": {
            name: "Sourcewell",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "Sourcewell Contract Price",
            caption: "Referenced under Sourcewell<br>Contract # 062425-ODRA",
            logoKey: "sourcewell",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "HGAC": {
            name: "H-GAC / HGACBuy",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "H-GAC Contract Price",
            caption: "Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26",
            logoKey: "hgac",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Minnesota": {
            name: "State of Minnesota",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "Minnesota Contract Price",
            caption: "Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))",
            logoKey: "none",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Buyboard": {
            name: "BuyBoard",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "BuyBoard Contract Price",
            caption: "Referenced under BuyBoard<br>ODRA Contract # 788-25",
            logoKey: "buyboard",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          }
        }
      },
      "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - RIGHT side dumping hopper": {
        label: "MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - RIGHT side dumping hopper",
        shortLabel: "MT4H Isuzu cabover, Class 5, non-CDL - RHD",
        description: "MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109\" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. Right-side dump sweeper. Chassis is a brand new 2027MY Isuzu NRR, 19,500 GVWR.",
        pricingBases: {
          "Commercial": {
            name: "MSRP (Commercial)",
            msrp: 257493,
            discount: 0,
            calculatedPrice: 257493,
            subtotalLabel: "MSRP Subtotal",
            caption: "",
            logoKey: "none",
            useMuniFreight: false,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Sourcewell": {
            name: "Sourcewell",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "Sourcewell Contract Price",
            caption: "Referenced under Sourcewell<br>Contract # 062425-ODRA",
            logoKey: "sourcewell",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "HGAC": {
            name: "H-GAC / HGACBuy",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "H-GAC Contract Price",
            caption: "Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26",
            logoKey: "hgac",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Minnesota": {
            name: "State of Minnesota",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "Minnesota Contract Price",
            caption: "Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))",
            logoKey: "none",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Buyboard": {
            name: "BuyBoard",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "BuyBoard Contract Price",
            caption: "Referenced under BuyBoard<br>ODRA Contract # 788-25",
            logoKey: "buyboard",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          }
        }
      },
      "MT4H - Ford F-550XL conventional, 19,000 GVWR, non-CDL - LEFT side dumping hopper": {
        label: "MT4H - Ford F-550XL conventional, 19,000 GVWR, non-CDL - LEFT side dumping hopper",
        shortLabel: "MT4H Ford F-550XL, non-CDL - LHD",
        description: "MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109\" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Ford F-550XL, 19,000 GVWR.",
        pricingBases: {
          "Commercial": {
            name: "MSRP (Commercial)",
            msrp: 257493,
            discount: 0,
            calculatedPrice: 257493,
            subtotalLabel: "MSRP Subtotal",
            caption: "",
            logoKey: "none",
            useMuniFreight: false,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Sourcewell": {
            name: "Sourcewell",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "Sourcewell Contract Price",
            caption: "Referenced under Sourcewell<br>Contract # 062425-ODRA",
            logoKey: "sourcewell",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "HGAC": {
            name: "H-GAC / HGACBuy",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "H-GAC Contract Price",
            caption: "Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26",
            logoKey: "hgac",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Minnesota": {
            name: "State of Minnesota",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "Minnesota Contract Price",
            caption: "Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))",
            logoKey: "none",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          },
          "Buyboard": {
            name: "BuyBoard",
            msrp: 307378,
            discount: 12.00,
            calculatedPrice: 270493,
            subtotalLabel: "BuyBoard Contract Price",
            caption: "Referenced under BuyBoard<br>ODRA Contract # 788-25",
            logoKey: "buyboard",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control. PM-10 compliant (SCAQMD Rule 1186)."
            ]
          }
        }
      }
    }
  },
  "MS5H": {
    code: "MS5H",
    name: "MS5H Mechanical Broom Sweeper",
    configurations: {
      "MS5H - Conventional Class 6, 25,500 GVWR, 148\" WB, 300HP Cummins, non-CDL": {
        label: "MS5H - Conventional Class 6, 25,500 GVWR, 148\" WB, 300HP Cummins, non-CDL",
        shortLabel: "MS5H Conventional Class 6, non-CDL",
        description: "MS5H Mechanical Broom Sweeper is a single engine sweeper with a 5-cubic yard hopper, mounted on a non-CDL, 148\" WB Class 6 commercial truck chassis with a 300HP Cummins engine with air brakes and full LED chassis lighting. The water spray system features a 355-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white and red with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Freightliner M2 Plus, 26,000 GVWR.",
        pricingBases: {
          "Commercial": {
            name: "MSRP (Commercial)",
            msrp: 349149,
            discount: 0,
            calculatedPrice: 349149,
            subtotalLabel: "MSRP Subtotal",
            caption: "",
            logoKey: "none",
            useMuniFreight: false,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "Sourcewell": {
            name: "Sourcewell",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "Sourcewell Contract Price",
            caption: "Referenced under Sourcewell<br>Contract # 062425-ODRA",
            logoKey: "sourcewell",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "HGAC": {
            name: "H-GAC / HGACBuy",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "H-GAC Contract Price",
            caption: "Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26",
            logoKey: "hgac",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "Minnesota": {
            name: "State of Minnesota",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "Minnesota Contract Price",
            caption: "Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))",
            logoKey: "none",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "Buyboard": {
            name: "BuyBoard",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "BuyBoard Contract Price",
            caption: "Referenced under BuyBoard<br>ODRA Contract # 788-25",
            logoKey: "buyboard",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          }
        }
      },
      "MS5H - Conventional Class 7, CDL-required configuration, 300HP Cummins - no price difference": {
        label: "MS5H - Conventional Class 7, CDL-required configuration, 300HP Cummins - no price difference",
        shortLabel: "MS5H Conventional Class 7, CDL-required",
        description: "MS5H Mechanical Broom Sweeper is a single engine sweeper with a 5-cubic yard hopper, mounted on a CDL-required, 148\" WB Class 7 commercial truck chassis with a 300HP Cummins engine with air brakes and full LED chassis lighting. The water spray system features a 355-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white and red with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Freightliner M2 Plus, 26,000 GVWR.",
        pricingBases: {
          "Commercial": {
            name: "MSRP (Commercial)",
            msrp: 349149,
            discount: 0,
            calculatedPrice: 349149,
            subtotalLabel: "MSRP Subtotal",
            caption: "",
            logoKey: "none",
            useMuniFreight: false,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "Sourcewell": {
            name: "Sourcewell",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "Sourcewell Contract Price",
            caption: "Referenced under Sourcewell<br>Contract # 062425-ODRA",
            logoKey: "sourcewell",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "HGAC": {
            name: "H-GAC / HGACBuy",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "H-GAC Contract Price",
            caption: "Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26",
            logoKey: "hgac",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "Minnesota": {
            name: "State of Minnesota",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "Minnesota Contract Price",
            caption: "Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))",
            logoKey: "none",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          },
          "Buyboard": {
            name: "BuyBoard",
            msrp: 411533,
            discount: 12.00,
            calculatedPrice: 362149,
            subtotalLabel: "BuyBoard Contract Price",
            caption: "Referenced under BuyBoard<br>ODRA Contract # 788-25",
            logoKey: "buyboard",
            useMuniFreight: true,
            features: [
              "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
              "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
              "Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control."
            ]
          }
        }
      }
    }
  }
};

function loadConfigStore(){
  try{
    const raw = localStorage.getItem('ODRA_CONFIG_STORE_V2');
    if(raw) return JSON.parse(raw);
  }catch(e){
    console.error('Error loading config store from localStorage', e);
  }
  return JSON.parse(JSON.stringify(DEFAULT_CONFIG_STORE));
}
function saveConfigStore(store){
  localStorage.setItem('ODRA_CONFIG_STORE_V2', JSON.stringify(store));
}

let CONFIG_STORE = loadConfigStore();

// Helper to calculate price
function calcPrice(msrp, discount){
  const m = Number(msrp) || 0;
  const d = Number(discount) || 0;
  return Math.round(m * (1 - d / 100));
}

function getActiveModelConfig(){
  return CONFIG_STORE[state.model] || Object.values(CONFIG_STORE)[0] || null;
}
function getActiveChassisConfig(){
  const m = getActiveModelConfig();
  if(!m || !m.configurations) return null;
  return m.configurations[state.chassis] || Object.values(m.configurations)[0] || null;
}
function getActiveBasisConfig(){
  const c = getActiveChassisConfig();
  if(!c || !c.pricingBases) return null;
  return c.pricingBases[state.basis] || Object.values(c.pricingBases)[0] || null;
}

// -------------------------------------------------------------
// APP STATE
// -------------------------------------------------------------
const state = {
  date: todayISO(),
  model: 'MT4H',
  chassis: 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
  basis: 'Commercial',
  po: '',
  fob: 'Transport, estimate only noted in quote',
  acct: '',
  salesRep: '',
  toCompany: '',
  toAddr1: '',
  toAddr2: '',
  toContact: '',
  baseQty: 1,
  freight: 0,
  baseShowDiscount: 0,
  repTerritory: '',
  quoteSeq: '3001',
  enh: Array.from({length:13}, ()=>({desc:'', qty:1}))
};

function itemsForModel(m){ return ITEMS.filter(i=>i.model===m); }
function baseItem(m){ return itemsForModel(m).find(i=>i.isBase); }
function enhOptions(m){ return itemsForModel(m).filter(i=>!i.isBase); }

function priceForItem(item){
  const basisCfg = getActiveBasisConfig();
  if(item.isBase){
    if(basisCfg){
      return { list: Number(basisCfg.msrp)||0, unit: Number(basisCfg.calculatedPrice)||0 };
    }
    return { list: item.msrp, unit: item.msrp };
  }
  // For enhancements: if municipal/contract pricing basis
  if(basisCfg && basisCfg.useMuniFreight){
    return { list: item.swList, unit: item.swNet };
  }
  return { list: item.msrp, unit: item.msrp };
}

function dynamicBaseDesc(base, chassisStr, model){
  const cfg = getActiveChassisConfig();
  if(cfg && cfg.description && cfg.description.trim()){
    return cfg.description.trim();
  }
  if(model==='MT4H'){
    const s = chassisStr || '';
    const brand = /Ford F-550XL/.test(s) ? 'Ford F-550XL' : 'Isuzu NRR';
    const gvwrMatch = s.match(/([\d,]+)\s*GVWR/);
    const gvwr = gvwrMatch ? gvwrMatch[1] : '19,500';
    const sideMatch = s.match(/(LEFT|RIGHT) side dumping hopper/i);
    const side = (sideMatch && /RIGHT/i.test(sideMatch[1])) ? 'Right' : 'Left';
    return `MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. ${side}-side dump sweeper. Chassis is a brand new 2027MY ${brand}, ${gvwr} GVWR.`;
  }
  if(model==='MS5H'){
    const s = chassisStr || '';
    const isCDL = /Class 7|CDL-required/i.test(s);
    if(isCDL && base && base.desc){
      return base.desc.replace(/non-CDL,\s*148"\s*WB\s*Class\s*6/i, 'CDL-required, 148" WB Class 7');
    }
    return base ? base.desc : '';
  }
  return base ? base.desc : '';
}

function populateRepTerritory(){
  const sel = document.getElementById('repTerritory');
  if(!sel) return;
  const rep = state.salesRep;
  const repHasTerritories = TERRITORIES.some(t=>t.rep===rep);
  const opts = repHasTerritories ? TERRITORIES.filter(t=>t.rep===rep) : TERRITORIES;
  const keep = opts.some(o=>o.code===state.repTerritory) ? state.repTerritory : (opts[0] ? opts[0].code : '');
  sel.innerHTML = opts.map(t=>`<option value="${t.code}" ${t.code===keep?'selected':''}>${esc(t.rep)} \u2014 ${esc(t.label)}</option>`).join('');
  sel.value = keep;
  state.repTerritory = keep;
}

function territoryMaxFreight(code, useMunicipality){
  const t = TERRITORIES.find(x=>x.code===code);
  if(!t) return null;
  const rows = t.cities
    ? FREIGHT_ESTIMATES.filter(r=>t.cities.includes(r.city))
    : FREIGHT_ESTIMATES.filter(r=>t.states.includes(r.state));
  if(!rows.length) return null;
  const vals = rows.map(r=> useMunicipality ? r.municipality : r.customer);
  return Math.max(...vals);
}

function applyTerritoryFreight(){
  const freightEl = document.getElementById('freight');
  const exwNoteEl = document.getElementById('exwFreightNote');
  if(state.fob === 'EXW, Factory pick-up'){
    state.freight = 0;
    if(freightEl) freightEl.value = 0;
    if(exwNoteEl) exwNoteEl.style.display = 'block';
    return;
  }
  if(exwNoteEl) exwNoteEl.style.display = 'none';
  if(!state.repTerritory) return;
  const basisCfg = getActiveBasisConfig();
  const useMunicipality = basisCfg ? !!basisCfg.useMuniFreight : false;
  const val = territoryMaxFreight(state.repTerritory, useMunicipality);
  if(val===null) return;
  state.freight = val;
  if(freightEl) freightEl.value = val;
}

function quoteCodePrefix(){
  const srCode = SR_CODES[state.salesRep] || '';
  let ddmmyyyy = '';
  if(state.date){
    const parts = state.date.split('-');
    ddmmyyyy = `${parts[2]}${parts[1]}${parts[0]}`;
  }
  return `${srCode}${ddmmyyyy}`;
}
function computeQuoteCode(){ return `${quoteCodePrefix()}${state.quoteSeq||''}`; }
function currentQuoteCode(){ return computeQuoteCode(); }

function fmt(n){ if(n===null||n===undefined||isNaN(n)) return ''; return n.toLocaleString('en-US',{style:'currency',currency:'USD',maximumFractionDigits:0}); }
function pctFmt(n){ if(n===null||n===undefined||isNaN(n)||Math.abs(n)<0.0005) return ''; return Math.round(n*100)+'%'; }
function esc(s){ return (s===undefined||s===null)?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function todayISO(){ const d=new Date(); const mm=String(d.getMonth()+1).padStart(2,'0'); const dd=String(d.getDate()).padStart(2,'0'); return d.getFullYear()+'-'+mm+'-'+dd; }
function fmtDateDisplay(iso){ if(!iso) return ''; const [y,m,d]=iso.split('-'); const dt=new Date(Number(y),Number(m)-1,Number(d)); const day=String(dt.getDate()).padStart(2,'0'); const month=dt.toLocaleDateString('en-US',{month:'long'}); return `${day}-${month}-${dt.getFullYear()}`; }

function standardInclusionsHtml(model){
  const basisCfg = getActiveBasisConfig();
  let feats = (basisCfg && basisCfg.features && basisCfg.features.length) ? basisCfg.features : [];
  if(!feats.length){
    const pm10 = model === 'MS5H' ? '' : ' PM-10 compliant (SCAQMD Rule 1186).';
    feats = [
      "ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.",
      "Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled form inside the cab.",
      `Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control.${pm10}`
    ];
  }
  return `<p><b>Standard Features:</b> ${esc(feats[0]||'')}</p>` + feats.slice(1).map(f=>`<p>${esc(f)}</p>`).join('');
}

function legalHtml(model){
  return `
<div class="legal">
  ${standardInclusionsHtml(model)}
  <p>Delivery transport charge, shown as an estimate ONLY. Actual rate to be determined at time of invoicing.</p>
  <p>Charges relating to taxes, registration, titling, and other fees are the responsibility of the customer; not included in this quotation. Customer takes responsibility for adherence to local and state laws for chassis, emissions, and registration relating to the sweeper. If applicable fees, duties or any charges not shown on quotation are due, they will be added as soon as known.</p>
  <p>A <b>$10,000 deposit and Purchase Order number</b> is requested at time of order. Balance due upon delivery or pick-up. Lead time is subject to change at time of order placement.</p>
  <p>This quote is <b>BUDGETARY ONLY</b>, valid 21 days from date of quote. ODRA Sweeper Company, LLC reserves the right to make product and specification changes at any time.</p>
</div>
`;
}

// -------------------------------------------------------------
// POPULATE DROPDOWNS: Model -> Chassis -> Pricing Basis
// -------------------------------------------------------------
function syncDropdowns(){
  // 1. Model Dropdown
  const modelSel = document.getElementById('model');
  const models = Object.keys(CONFIG_STORE);
  if(!models.includes(state.model)) state.model = models[0] || 'MT4H';
  modelSel.innerHTML = models.map(m=>`<option value="${esc(m)}" ${m===state.model?'selected':''}>${esc(m)}</option>`).join('');

  // 2. Chassis Dropdown
  const chassisSel = document.getElementById('chassis');
  const modelCfg = CONFIG_STORE[state.model] || {};
  const configs = modelCfg.configurations ? Object.keys(modelCfg.configurations) : [];
  if(!configs.includes(state.chassis)) state.chassis = configs[0] || '';
  chassisSel.innerHTML = configs.map(c=>{
    const item = modelCfg.configurations[c];
    const shortLbl = (item && item.shortLabel) ? item.shortLabel : c;
    return `<option value="${esc(c)}" ${c===state.chassis?'selected':''}>${esc(shortLbl)}</option>`;
  }).join('');

  // 3. Pricing Basis Dropdown
  const basisSel = document.getElementById('basis');
  const chassisCfg = (modelCfg.configurations && state.chassis) ? modelCfg.configurations[state.chassis] : null;
  const bases = chassisCfg && chassisCfg.pricingBases ? Object.keys(chassisCfg.pricingBases) : [];
  if(!bases.includes(state.basis)) state.basis = bases[0] || 'Commercial';
  basisSel.innerHTML = bases.map(b=>{
    const bItem = chassisCfg.pricingBases[b];
    const name = (bItem && bItem.name) ? bItem.name : b;
    return `<option value="${esc(b)}" ${b===state.basis?'selected':''}>${esc(name)}</option>`;
  }).join('');

  // Update Summary Card
  updatePricingSummaryCard();
}

function updatePricingSummaryCard(){
  const basisCfg = getActiveBasisConfig();
  if(!basisCfg){
    document.getElementById('statMsrp').textContent = '$0';
    document.getElementById('statDiscount').textContent = '0%';
    document.getElementById('statUnitPrice').textContent = '$0';
    document.getElementById('statLogo').innerHTML = '<span style="color:#aaa;">None</span>';
    return;
  }
  document.getElementById('statMsrp').textContent = fmt(basisCfg.msrp);
  document.getElementById('statDiscount').textContent = (basisCfg.discount > 0 ? (basisCfg.discount + '%') : '0%');
  document.getElementById('statUnitPrice').textContent = fmt(basisCfg.calculatedPrice);
  
  const logoData = getLogoData(basisCfg.logoKey);
  if(logoData){
    document.getElementById('statLogo').innerHTML = `<img src="${logoData}" style="height:20px;max-width:65px;vertical-align:middle;object-fit:contain;">`;
  } else {
    document.getElementById('statLogo').innerHTML = '<span style="color:#888;font-size:11px;">None</span>';
  }

  const badge = document.getElementById('statFreightBadge');
  if(basisCfg.useMuniFreight){
    badge.className = 'badge-tag badge-muni';
    badge.textContent = 'Municipal Rate';
  } else {
    badge.className = 'badge-tag badge-comm';
    badge.textContent = 'Commercial Rate';
  }
}

function buildEnhRows(){
  const wrap = document.getElementById('enhList');
  wrap.innerHTML = '';
  const opts = enhOptions(state.model);
  state.enh.forEach((row,idx)=>{
    if(!opts.find(o=>o.desc===row.desc)) row.desc='';
    const div = document.createElement('div');
    div.className='enhRow';
    div.innerHTML = `
      <div><label>${idx===0?'Option':''}</label>
        <select data-idx="${idx}" class="enhDesc">
          <option value="">&mdash; none &mdash;</option>
          ${opts.map(o=>`<option value="${esc(o.desc)}" ${o.desc===row.desc?'selected':''}>${esc(o.desc)}</option>`).join('')}
        </select>
      </div>
      <div><label>${idx===0?'Qty':''}</label><input type="number" min="1" class="enhQty" data-idx="${idx}" value="${row.qty}"></div>
      <div></div>`;
    wrap.appendChild(div);
  });
  wrap.querySelectorAll('.enhDesc').forEach(el=>el.addEventListener('change', e=>{
    const i=+e.target.dataset.idx; state.enh[i].desc=e.target.value; if(e.target.value && !state.enh[i].qty) state.enh[i].qty=1; render();
  }));
  wrap.querySelectorAll('.enhQty').forEach(el=>el.addEventListener('input', e=>{
    const i=+e.target.dataset.idx; state.enh[i].qty=Math.max(1, parseInt(e.target.value)||1); render();
  }));
}

function bindSimple(id, key, parser){
  document.getElementById(id).addEventListener('input', e=>{ state[key] = parser?parser(e.target.value):e.target.value; render(); });
}

// -------------------------------------------------------------
// RENDER QUOTATION PREVIEW
// -------------------------------------------------------------
function renderPreview(){
  const qcode = currentQuoteCode();
  const seqEl = document.getElementById('quoteSeq');
  if(seqEl && document.activeElement!==seqEl) seqEl.value = state.quoteSeq||'';

  const base = baseItem(state.model);
  const bp = priceForItem(base);
  const rawDiscount = Number(state.baseShowDiscount)||0;
  const showDiscount = Math.min(20000, rawDiscount);
  const discWarnEl = document.getElementById('discountWarning');
  if(discWarnEl) discWarnEl.style.display = rawDiscount > 20000 ? 'block' : 'none';

  const displayBaseList = (bp.list||0) + showDiscount;
  const baseExt = state.baseQty * bp.unit;
  const baseDisc = displayBaseList ? 1-(bp.unit/displayBaseList) : 0;

  let enhExtTotal = 0;
  const enhRowData = [];
  state.enh.forEach(row=>{
    if(!row.desc) return;
    const item = enhOptions(state.model).find(o=>o.desc===row.desc);
    if(!item) return;
    const p = priceForItem(item);
    const ext = row.qty * p.unit;
    enhExtTotal += ext;
    const disc = p.list ? 1-(p.unit/p.list) : 0;
    enhRowData.push({desc:item.desc, qty:row.qty, list:p.list, unit:p.unit, ext, disc});
  });

  const anyDisc = Math.abs(baseDisc) >= 0.0005 || enhRowData.some(r=>Math.abs(r.disc) >= 0.0005);
  const cols = anyDisc ? 6 : 5;
  const discTh = anyDisc ? '<th class="num">Discount</th>' : '';
  const enhRowsHtml = enhRowData.map(r=>
    `<tr><td>${esc(r.desc)}</td><td class="num">${r.qty}</td><td class="num">${fmt(r.list)}</td>${anyDisc?`<td class="num">${pctFmt(r.disc)}</td>`:''}<td class="num">${fmt(r.unit)}</td><td class="num">${fmt(r.ext)}</td></tr>`
  ).join('');

  const freight = Number(state.freight)||0;
  const subtotal = baseExt + enhExtTotal;
  const total = subtotal + freight;

  const basisCfg = getActiveBasisConfig();
  const subtotalLabel = basisCfg ? (basisCfg.subtotalLabel || 'Subtotal') : 'MSRP Subtotal';
  const disclaimer = basisCfg ? (basisCfg.caption ? basisCfg.caption.replace(/<br>/gi,' ') : '') : '';
  const coopLogo = basisCfg ? getLogoData(basisCfg.logoKey) : null;
  const coopCaption = basisCfg ? (basisCfg.caption || '') : '';
  const coopRowHtml = coopLogo ? `<tr class="coopRow"><td colspan="${cols-2}" style="text-align:center;padding:12px 6px;"><img src="${coopLogo}" style="height:36px;max-width:260px;display:block;margin:0 auto 8px auto;"><b style="font-size:11.5px;line-height:1.4;">${coopCaption}</b></td><td class="num"></td><td class="num"></td></tr>` : '';

  const chassisCfg = getActiveChassisConfig();
  const shortChassis = (chassisCfg && chassisCfg.shortLabel) ? chassisCfg.shortLabel : state.chassis;
  const basisDisplayName = basisCfg ? basisCfg.name : state.basis;

  document.getElementById('preview').innerHTML = `
    <div class="docHeaderTop">
      <div class="co">
        <img src='${odra_logo}' alt="ODRA Logo">
        <div class="addr">705 South 48th Street, Grand Forks, ND 58201<br>
        t: 844-888-6372 &bull; e: info@odrasweeper.com &bull; odrasweeper.com</div>
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
        <div><span class="k">Chassis / Config:</span> ${esc(shortChassis)}</div>
        <div><span class="k">FOB / Transport:</span> ${esc(state.fob)}</div>
        <div class="toBlock"><span class="k">To:</span>${(()=>{const toLines=[state.toCompany,state.toAddr1,state.toAddr2,state.toContact].filter(Boolean).map(esc); return toLines.length ? `<span class="toLines">${toLines.map(l=>`<div>${l}</div>`).join('')}</span>` : ' &mdash;';})()}</div>
      </div>
      <div class="col">
        <div><span class="k">Pricing Basis:</span> ${esc(basisDisplayName)}</div>
        <div><span class="k">P.O. No.:</span> ${esc(state.po)||'&mdash;'}</div>
        <div><span class="k">Customer Acct #:</span> ${esc(state.acct)||'&mdash;'}</div>
        <div><span class="k">Sales Rep:</span> ${esc(state.salesRep)||'&mdash;'}</div>
      </div>
    </div>
    <table class="items">
      <thead><tr><th>Description</th><th class="num">Qty</th><th class="num">MSRP</th>${discTh}<th class="num">Unit Price</th><th class="num">Extended</th></tr></thead>
      <tbody>
        <tr class="baseRow"><td>${esc(dynamicBaseDesc(base, state.chassis, state.model))}</td><td class="num">${state.baseQty}</td><td class="num">${fmt(displayBaseList)}</td>${anyDisc?`<td class="num">${pctFmt(baseDisc)}</td>`:''}<td class="num">${fmt(bp.unit)}</td><td class="num">${fmt(baseExt)}</td></tr>
        <tr class="sectionRow"><td colspan="${cols}">Including Enhancements</td></tr>
        ${enhRowsHtml || `<tr><td colspan="${cols}" style="color:#999;font-style:italic;">No enhancements selected</td></tr>`}
        ${coopRowHtml}
        <tr class="subtotalRow"><td colspan="${cols-2}"></td><td class="num">${subtotalLabel}</td><td class="num">${fmt(subtotal)}</td></tr>
        <tr><td>Sweeper Transport / Freight</td><td class="num">${state.fob === 'EXW, Factory pick-up' ? 0 : 1}</td><td class="num"></td>${anyDisc?'<td class="num"></td>':''}<td class="num">${fmt(freight)}</td><td class="num">${fmt(freight)}</td></tr>
        <tr class="totalRow"><td colspan="${cols-2}"></td><td class="num">TOTAL (USD)</td><td class="num">${fmt(total)}</td></tr>
      </tbody>
    </table>
    ${legalHtml(state.model)}
  `;
}

function render(){
  syncDropdowns();
  populateRepTerritory();
  applyTerritoryFreight();
  buildEnhRows();
  renderPreview();
}

// -------------------------------------------------------------
// EVENT HANDLERS ON MAIN QUOTE BUILDER
// -------------------------------------------------------------
document.getElementById('quoteDate').value = state.date;
document.getElementById('quoteDate').min = todayISO();
document.getElementById('quoteDate').addEventListener('change', e=>{
  let v = e.target.value;
  if(v && v < todayISO()){ v = todayISO(); e.target.value = v; }
  state.date = v;
  render();
});
document.getElementById('quoteSeq').addEventListener('input', e=>{
  state.quoteSeq = e.target.value.replace(/[^0-9]/g,'').slice(0,4);
  render();
});
bindSimple('po','po');
bindSimple('fob','fob');
bindSimple('acct','acct');
bindSimple('salesRep','salesRep');
bindSimple('toContact','toContact');
bindSimple('toCompany','toCompany');
bindSimple('toAddr1','toAddr1');
bindSimple('toAddr2','toAddr2');
bindSimple('baseQty','baseQty', v=>Math.max(1,parseInt(v)||1));
bindSimple('baseShowDiscount','baseShowDiscount', v=>Math.max(0,parseFloat(v)||0));
bindSimple('freight','freight', v=>Math.max(0,parseFloat(v)||0));
document.getElementById('repTerritory').addEventListener('change', e=>{ state.repTerritory=e.target.value; applyTerritoryFreight(); render(); });

// Reactive Hierarchy Selections:
document.getElementById('model').addEventListener('change', e=>{
  state.model = e.target.value;
  const modelCfg = CONFIG_STORE[state.model];
  if(modelCfg && modelCfg.configurations){
    state.chassis = Object.keys(modelCfg.configurations)[0] || '';
    const chassisCfg = modelCfg.configurations[state.chassis];
    if(chassisCfg && chassisCfg.pricingBases){
      state.basis = Object.keys(chassisCfg.pricingBases)[0] || 'Commercial';
    }
  }
  render();
});

document.getElementById('chassis').addEventListener('change', e=>{
  state.chassis = e.target.value;
  const chassisCfg = getActiveChassisConfig();
  if(chassisCfg && chassisCfg.pricingBases){
    if(!chassisCfg.pricingBases[state.basis]){
      state.basis = Object.keys(chassisCfg.pricingBases)[0] || 'Commercial';
    }
  }
  render();
});

document.getElementById('basis').addEventListener('change', e=>{
  state.basis = e.target.value;
  render();
});

document.getElementById('downloadBtn').addEventListener('click', ()=>{
  const el = document.getElementById('preview');
  const opt = {
    margin: [10,8,10,8],
    filename: `ODRA_Quote_${currentQuoteCode()}.pdf`,
    image: {type:'jpeg',quality:0.98},
    html2canvas: {scale:2, useCORS:true, scrollX:0, scrollY:0, windowWidth:document.documentElement.scrollWidth, windowHeight:document.documentElement.scrollHeight},
    jsPDF: {unit:'mm', format:'letter', orientation:'portrait'},
    pagebreak: {mode:['avoid-all','css','legacy']}
  };
  html2pdf().set(opt).from(el).save();
});
document.getElementById('printBtn').addEventListener('click', ()=>window.print());

// -------------------------------------------------------------
// CONFIGURATION MODAL CONTROLLER & LOGIC
// -------------------------------------------------------------
let cfgTempStore = null;
let cfgActiveModel = 'MT4H';
let cfgActiveChassis = '';
let cfgActiveBasis = 'Commercial';

function showToast(msg){
  const t = document.getElementById('cfgToast');
  t.textContent = msg;
  t.style.display = 'block';
  setTimeout(()=>{ t.style.display='none'; }, 2600);
}

function openConfigModal(model, chassis){
  cfgTempStore = JSON.parse(JSON.stringify(CONFIG_STORE));
  cfgActiveModel = model || state.model || Object.keys(cfgTempStore)[0];
  const mCfg = cfgTempStore[cfgActiveModel] || {};
  const configs = mCfg.configurations ? Object.keys(mCfg.configurations) : [];
  cfgActiveChassis = (chassis && configs.includes(chassis)) ? chassis : (configs[0] || '');
  cfgActiveBasis = 'Commercial';

  renderCfgModal();
  document.getElementById('configModalOverlay').style.display = 'flex';
}

function closeConfigModal(){
  document.getElementById('configModalOverlay').style.display = 'none';
  cfgTempStore = null;
}

function renderCfgModal(){
  // 1. Populate Models
  const modelSel = document.getElementById('cfgModelSelect');
  const models = Object.keys(cfgTempStore);
  if(!models.includes(cfgActiveModel)) cfgActiveModel = models[0] || '';
  modelSel.innerHTML = models.map(m=>`<option value="${esc(m)}" ${m===cfgActiveModel?'selected':''}>${esc(m)} &mdash; ${esc(cfgTempStore[m].name||m)}</option>`).join('');

  // 2. Populate Chassis Configurations for active model
  const chassisSel = document.getElementById('cfgChassisSelect');
  const mCfg = cfgTempStore[cfgActiveModel] || {};
  const configs = mCfg.configurations ? Object.keys(mCfg.configurations) : [];
  if(!configs.includes(cfgActiveChassis)) cfgActiveChassis = configs[0] || '';
  chassisSel.innerHTML = configs.map(c=>{
    const item = mCfg.configurations[c];
    const shortLbl = (item && item.shortLabel) ? item.shortLabel : c;
    return `<option value="${esc(c)}" ${c===cfgActiveChassis?'selected':''}>${esc(shortLbl)}</option>`;
  }).join('');

  // 3. Dedicated Product Description for selected configuration
  const cCfg = (mCfg.configurations && cfgActiveChassis) ? mCfg.configurations[cfgActiveChassis] : null;
  const descEl = document.getElementById('cfgChassisDesc');
  descEl.value = (cCfg && cCfg.description) ? cCfg.description : '';

  // 4. Render Pricing Table
  renderCfgPricingTable();

  // 5. Render Standard Features
  renderCfgFeatures();
}

function renderCfgPricingTable(){
  const tbody = document.getElementById('cfgPricingTbody');
  tbody.innerHTML = '';
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg || !mCfg.configurations || !mCfg.configurations[cfgActiveChassis]){
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#999;padding:18px;">No configurations available.</td></tr>';
    return;
  }
  const cCfg = mCfg.configurations[cfgActiveChassis];
  const bases = cCfg.pricingBases ? Object.keys(cCfg.pricingBases) : [];
  if(!bases.includes(cfgActiveBasis)) cfgActiveBasis = bases[0] || '';

  const logoOptions = getAllLogoOptions();

  bases.forEach(bKey=>{
    const b = cCfg.pricingBases[bKey];
    const isSelected = (bKey === cfgActiveBasis);
    const tr = document.createElement('tr');
    if(isSelected) tr.style.background = '#fef2f2';

    const calc = calcPrice(b.msrp, b.discount);
    b.calculatedPrice = calc;

    const logoData = getLogoData(b.logoKey);
    const logoThumbHtml = logoData ? `<img src="${logoData}" class="logo-thumb" id="thumb_${esc(bKey)}">` : `<img src="" class="logo-thumb" id="thumb_${esc(bKey)}" style="display:none;">`;

    tr.innerHTML = `
      <td>
        <input type="text" value="${esc(b.name||bKey)}" class="cfg-basis-name" data-key="${esc(bKey)}">
      </td>
      <td>
        <input type="number" value="${b.msrp||0}" min="0" step="1" class="cfg-basis-msrp" data-key="${esc(bKey)}">
      </td>
      <td>
        <input type="number" value="${b.discount||0}" min="0" max="100" step="0.01" class="cfg-basis-disc" data-key="${esc(bKey)}">
      </td>
      <td>
        <span class="calc-badge" id="calc_${esc(bKey)}">${fmt(calc)}</span>
      </td>
      <td>
        <input type="text" value="${esc(b.subtotalLabel||'')}" class="cfg-basis-subtotal" data-key="${esc(bKey)}">
      </td>
      <td>
        <input type="text" value="${esc(b.caption||'')}" class="cfg-basis-caption" data-key="${esc(bKey)}">
      </td>
      <td>
        <div style="display:flex;align-items:center;">
          <select class="cfg-basis-logo" data-key="${esc(bKey)}" style="font-size:11px;">
            ${logoOptions.map(l=>`<option value="${esc(l.key)}" ${l.key===b.logoKey?'selected':''}>${esc(l.name)}</option>`).join('')}
          </select>
          ${logoThumbHtml}
        </div>
      </td>
      <td style="text-align:center;">
        <input type="checkbox" ${b.useMuniFreight?'checked':''} class="cfg-basis-muni" data-key="${esc(bKey)}">
      </td>
      <td style="text-align:center;">
        <button type="button" class="cfg-btn-sm cfg-btn-danger cfg-del-basis-btn" data-key="${esc(bKey)}" title="Delete pricing basis">&times;</button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  // Attach event handlers to pricing inputs for live calculation and state retention
  tbody.querySelectorAll('.cfg-basis-msrp').forEach(input=>{
    input.addEventListener('input', e=>{
      const k = e.target.dataset.key;
      const b = cCfg.pricingBases[k];
      if(b){
        b.msrp = parseFloat(e.target.value)||0;
        const calc = calcPrice(b.msrp, b.discount);
        b.calculatedPrice = calc;
        const badge = document.getElementById('calc_' + k);
        if(badge) badge.textContent = fmt(calc);
      }
    });
  });

  tbody.querySelectorAll('.cfg-basis-disc').forEach(input=>{
    input.addEventListener('input', e=>{
      const k = e.target.dataset.key;
      const b = cCfg.pricingBases[k];
      if(b){
        b.discount = parseFloat(e.target.value)||0;
        const calc = calcPrice(b.msrp, b.discount);
        b.calculatedPrice = calc;
        const badge = document.getElementById('calc_' + k);
        if(badge) badge.textContent = fmt(calc);
      }
    });
  });

  tbody.querySelectorAll('.cfg-basis-name').forEach(input=>{
    input.addEventListener('change', e=>{
      const k = e.target.dataset.key;
      if(cCfg.pricingBases[k]) cCfg.pricingBases[k].name = e.target.value;
    });
  });

  tbody.querySelectorAll('.cfg-basis-subtotal').forEach(input=>{
    input.addEventListener('change', e=>{
      const k = e.target.dataset.key;
      if(cCfg.pricingBases[k]) cCfg.pricingBases[k].subtotalLabel = e.target.value;
    });
  });

  tbody.querySelectorAll('.cfg-basis-caption').forEach(input=>{
    input.addEventListener('change', e=>{
      const k = e.target.dataset.key;
      if(cCfg.pricingBases[k]) cCfg.pricingBases[k].caption = e.target.value;
    });
  });

  tbody.querySelectorAll('.cfg-basis-muni').forEach(input=>{
    input.addEventListener('change', e=>{
      const k = e.target.dataset.key;
      if(cCfg.pricingBases[k]) cCfg.pricingBases[k].useMuniFreight = e.target.checked;
    });
  });

  tbody.querySelectorAll('.cfg-basis-logo').forEach(sel=>{
    sel.addEventListener('change', e=>{
      const k = e.target.dataset.key;
      if(cCfg.pricingBases[k]){
        cCfg.pricingBases[k].logoKey = e.target.value;
        const thumb = document.getElementById('thumb_' + k);
        const data = getLogoData(e.target.value);
        if(thumb){
          if(data){
            thumb.src = data;
            thumb.style.display = 'inline-block';
          } else {
            thumb.src = '';
            thumb.style.display = 'none';
          }
        }
      }
    });
  });

  tbody.querySelectorAll('.cfg-del-basis-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
      const k = btn.dataset.key;
      if(Object.keys(cCfg.pricingBases).length <= 1){
        alert('Each configuration must have at least one pricing basis.');
        return;
      }
      if(confirm(`Delete pricing basis "${k}"?`)){
        delete cCfg.pricingBases[k];
        renderCfgPricingTable();
        renderCfgFeatures();
      }
    });
  });
}

function renderCfgFeatures(){
  const listWrap = document.getElementById('cfgFeatureList');
  listWrap.innerHTML = '';
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg || !mCfg.configurations || !mCfg.configurations[cfgActiveChassis]) return;
  const cCfg = mCfg.configurations[cfgActiveChassis];
  const bCfg = cCfg.pricingBases[cfgActiveBasis] || Object.values(cCfg.pricingBases)[0];
  if(!bCfg) return;

  document.getElementById('cfgActiveBasisLabel').textContent = bCfg.name || cfgActiveBasis;
  if(!bCfg.features) bCfg.features = [];

  bCfg.features.forEach((feat, idx)=>{
    const row = document.createElement('div');
    row.className = 'feat-row';
    row.innerHTML = `
      <textarea rows="2" data-idx="${idx}" class="cfg-feat-text">${esc(feat)}</textarea>
      <button type="button" class="cfg-btn-sm cfg-btn-danger cfg-del-feat-btn" data-idx="${idx}" title="Remove feature">&times;</button>
    `;
    listWrap.appendChild(row);
  });

  listWrap.querySelectorAll('.cfg-feat-text').forEach(t=>{
    t.addEventListener('input', e=>{
      const i = +e.target.dataset.idx;
      bCfg.features[i] = e.target.value;
    });
  });

  listWrap.querySelectorAll('.cfg-del-feat-btn').forEach(b=>{
    b.addEventListener('click', e=>{
      const i = +b.dataset.idx;
      bCfg.features.splice(i, 1);
      renderCfgFeatures();
    });
  });
}

// Modal Action Buttons
document.getElementById('openConfigBtn').addEventListener('click', ()=>openConfigModal(state.model, state.chassis));
document.getElementById('quickConfigLink').addEventListener('click', ()=>openConfigModal(state.model, state.chassis));
document.getElementById('cfgCloseX').addEventListener('click', closeConfigModal);
document.getElementById('cfgCancelBtn').addEventListener('click', closeConfigModal);

document.getElementById('cfgModelSelect').addEventListener('change', e=>{
  cfgActiveModel = e.target.value;
  const m = cfgTempStore[cfgActiveModel];
  cfgActiveChassis = (m && m.configurations) ? Object.keys(m.configurations)[0] : '';
  cfgActiveBasis = 'Commercial';
  renderCfgModal();
});

document.getElementById('cfgChassisSelect').addEventListener('change', e=>{
  cfgActiveChassis = e.target.value;
  renderCfgModal();
});

document.getElementById('cfgChassisDesc').addEventListener('input', e=>{
  const mCfg = cfgTempStore[cfgActiveModel];
  if(mCfg && mCfg.configurations && mCfg.configurations[cfgActiveChassis]){
    mCfg.configurations[cfgActiveChassis].description = e.target.value;
  }
});

// Add Model
document.getElementById('cfgAddModelBtn').addEventListener('click', ()=>{
  const code = prompt('Enter new Model Code (e.g. MS7H or MT4H-EV):');
  if(!code) return;
  const cleanCode = code.trim().toUpperCase();
  if(!cleanCode){ alert('Model code cannot be empty.'); return; }
  if(cfgTempStore[cleanCode]){ alert(`Model "${cleanCode}" already exists.`); return; }

  const name = prompt(`Enter descriptive name for ${cleanCode}:`, `${cleanCode} Mechanical Broom Sweeper`) || cleanCode;
  
  // Clone baseline configurations from MT4H as template
  const templateConfig = JSON.parse(JSON.stringify(cfgTempStore['MT4H'] || Object.values(cfgTempStore)[0]));
  const firstCfgKey = Object.keys(templateConfig.configurations)[0];
  const sampleCfg = templateConfig.configurations[firstCfgKey];

  const newChassisKey = `${cleanCode} - Standard Commercial Chassis`;
  const newConfigurations = {};
  newConfigurations[newChassisKey] = {
    label: newChassisKey,
    shortLabel: `${cleanCode} Standard Chassis`,
    description: `${cleanCode} Mechanical Broom Sweeper with heavy duty commercial truck chassis.`,
    pricingBases: JSON.parse(JSON.stringify(sampleCfg.pricingBases))
  };

  cfgTempStore[cleanCode] = {
    code: cleanCode,
    name: name,
    configurations: newConfigurations
  };

  cfgActiveModel = cleanCode;
  cfgActiveChassis = newChassisKey;
  cfgActiveBasis = 'Commercial';
  renderCfgModal();
  showToast(`Model ${cleanCode} added successfully!`);
});

// Delete Model
document.getElementById('cfgDelModelBtn').addEventListener('click', ()=>{
  const models = Object.keys(cfgTempStore);
  if(models.length <= 1){
    alert('At least one model must be maintained.');
    return;
  }
  if(confirm(`Are you sure you want to delete model "${cfgActiveModel}" and all its configurations?`)){
    delete cfgTempStore[cfgActiveModel];
    cfgActiveModel = Object.keys(cfgTempStore)[0];
    const m = cfgTempStore[cfgActiveModel];
    cfgActiveChassis = (m && m.configurations) ? Object.keys(m.configurations)[0] : '';
    renderCfgModal();
    showToast(`Model deleted.`);
  }
});

// Add Chassis Configuration
document.getElementById('cfgAddChassisBtn').addEventListener('click', ()=>{
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg) return;
  const label = prompt('Enter new Chassis / Discharge Configuration label:');
  if(!label) return;
  const cleanLabel = label.trim();
  if(!cleanLabel){ alert('Configuration label cannot be empty.'); return; }
  if(mCfg.configurations[cleanLabel]){ alert('A configuration with this label already exists.'); return; }

  const shortLabel = prompt('Enter short label for quotation header:', cleanLabel) || cleanLabel;
  const desc = prompt('Enter dedicated product description:', `${cfgActiveModel} mounted on ${cleanLabel}.`) || '';

  // Clone pricing bases from existing configuration
  const firstKey = Object.keys(mCfg.configurations)[0];
  const sampleBases = firstKey ? JSON.parse(JSON.stringify(mCfg.configurations[firstKey].pricingBases)) : {};

  mCfg.configurations[cleanLabel] = {
    label: cleanLabel,
    shortLabel: shortLabel,
    description: desc,
    pricingBases: sampleBases
  };

  cfgActiveChassis = cleanLabel;
  renderCfgModal();
  showToast(`Configuration added.`);
});

// Delete Chassis Configuration
document.getElementById('cfgDelChassisBtn').addEventListener('click', ()=>{
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg) return;
  const configs = Object.keys(mCfg.configurations);
  if(configs.length <= 1){
    alert('Each model must have at least one configuration.');
    return;
  }
  if(confirm(`Delete configuration "${cfgActiveChassis}"?`)){
    delete mCfg.configurations[cfgActiveChassis];
    cfgActiveChassis = Object.keys(mCfg.configurations)[0];
    renderCfgModal();
    showToast(`Configuration deleted.`);
  }
});

// Add Pricing Basis
document.getElementById('cfgAddBasisBtn').addEventListener('click', ()=>{
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg || !mCfg.configurations[cfgActiveChassis]) return;
  const cCfg = mCfg.configurations[cfgActiveChassis];

  const name = prompt('Enter new Pricing Basis name (e.g. State of Texas DIR, Florida Sheriff Association):');
  if(!name) return;
  const cleanName = name.trim();
  if(!cleanName){ alert('Pricing basis name cannot be empty.'); return; }

  const key = cleanName.replace(/[^a-zA-Z0-9]/g, '_');
  if(cCfg.pricingBases[key]){ alert('This pricing basis already exists for this configuration.'); return; }

  const msrp = parseFloat(prompt('Enter MSRP ($):', '275000')) || 275000;
  const disc = parseFloat(prompt('Enter Discount (%):', '10')) || 0;
  const calc = calcPrice(msrp, disc);

  // Clone standard features from Commercial if available
  const commBasis = cCfg.pricingBases['Commercial'] || Object.values(cCfg.pricingBases)[0];
  const features = commBasis ? JSON.parse(JSON.stringify(commBasis.features||[])) : [];

  cCfg.pricingBases[key] = {
    name: cleanName,
    msrp: msrp,
    discount: disc,
    calculatedPrice: calc,
    subtotalLabel: `${cleanName} Contract Price`,
    caption: `Referenced under ${cleanName} Contract`,
    logoKey: 'none',
    useMuniFreight: true,
    features: features
  };

  cfgActiveBasis = key;
  renderCfgPricingTable();
  renderCfgFeatures();
  showToast(`Pricing basis added.`);
});

// Copy from Commercial Features
document.getElementById('cfgCopyCommercialBtn').addEventListener('click', ()=>{
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg || !mCfg.configurations[cfgActiveChassis]) return;
  const cCfg = mCfg.configurations[cfgActiveChassis];
  const commBasis = cCfg.pricingBases['Commercial'];
  if(!commBasis || !commBasis.features || !commBasis.features.length){
    alert('Commercial basis does not have features defined to copy from.');
    return;
  }
  const currBasis = cCfg.pricingBases[cfgActiveBasis] || Object.values(cCfg.pricingBases)[0];
  if(currBasis){
    currBasis.features = JSON.parse(JSON.stringify(commBasis.features));
    renderCfgFeatures();
    showToast(`Copied standard features from Commercial basis.`);
  }
});

// Add Feature Bullet
document.getElementById('cfgAddFeatureBtn').addEventListener('click', ()=>{
  const mCfg = cfgTempStore[cfgActiveModel];
  if(!mCfg || !mCfg.configurations[cfgActiveChassis]) return;
  const cCfg = mCfg.configurations[cfgActiveChassis];
  const bCfg = cCfg.pricingBases[cfgActiveBasis] || Object.values(cCfg.pricingBases)[0];
  if(!bCfg) return;
  if(!bCfg.features) bCfg.features = [];
  bCfg.features.push('New standard feature item...');
  renderCfgFeatures();
});

// Custom Logo Upload
document.getElementById('cfgUploadLogoBtn').addEventListener('click', ()=>{
  const fileInput = document.getElementById('cfgLogoFileInput');
  const nameInput = document.getElementById('cfgLogoNameInput');
  const file = fileInput.files[0];
  if(!file){ alert('Please select an image file to upload.'); return; }
  const name = nameInput.value.trim() || file.name.replace(/\\.[^/.]+$/, "");

  const reader = new FileReader();
  reader.onload = function(e){
    const dataUrl = e.target.result;
    const key = 'custom_' + Date.now();
    CUSTOM_LOGOS[key] = { name: name, data: dataUrl };
    saveCustomLogos(CUSTOM_LOGOS);

    document.getElementById('cfgLogoUploadThumb').src = dataUrl;
    document.getElementById('cfgLogoUploadPreview').style.display = 'inline-flex';
    fileInput.value = '';
    nameInput.value = '';
    renderCfgPricingTable();
    showToast(`Logo "${name}" uploaded and added to Logo options!`);
  };
  reader.readAsDataURL(file);
});

// Save & Apply
document.getElementById('cfgSaveBtn').addEventListener('click', ()=>{
  // Update description from textarea
  const mCfg = cfgTempStore[cfgActiveModel];
  if(mCfg && mCfg.configurations && mCfg.configurations[cfgActiveChassis]){
    mCfg.configurations[cfgActiveChassis].description = document.getElementById('cfgChassisDesc').value;
  }

  CONFIG_STORE = JSON.parse(JSON.stringify(cfgTempStore));
  saveConfigStore(CONFIG_STORE);

  // Sync state if current selection was altered
  if(!CONFIG_STORE[state.model]) state.model = Object.keys(CONFIG_STORE)[0];
  const curM = CONFIG_STORE[state.model];
  if(!curM.configurations[state.chassis]) state.chassis = Object.keys(curM.configurations)[0];
  const curC = curM.configurations[state.chassis];
  if(!curC.pricingBases[state.basis]) state.basis = Object.keys(curC.pricingBases)[0];

  closeConfigModal();
  render();
  showToast('Configuration saved and applied to Quotation Generator!');
});

// Reset to Defaults
document.getElementById('cfgResetDefaultsBtn').addEventListener('click', ()=>{
  if(confirm('Are you sure you want to reset all configurations to factory defaults? Any custom models, pricing or descriptions will be reverted.')){
    localStorage.removeItem('ODRA_CONFIG_STORE_V2');
    CONFIG_STORE = JSON.parse(JSON.stringify(DEFAULT_CONFIG_STORE));
    cfgTempStore = JSON.parse(JSON.stringify(DEFAULT_CONFIG_STORE));
    closeConfigModal();
    render();
    showToast('Configurations reset to factory defaults.');
  }
});

// Initial Render
render();
</script>
</body>
</html>
"""

# Write to target file
with open(src_path, "w", encoding="utf-8") as f_out:
    f_out.write(html_template)

print("Updated HTML written to", src_path)

# Also update Downloads file if it exists
downloads_path = r"C:\Users\HrishikeshS\Downloads\ODRA_Quote_Generator_Rep_1 1 (1).html"
if os.path.exists(downloads_path):
    with open(downloads_path, "w", encoding="utf-8") as f_dl:
        f_dl.write(html_template)
    print("Updated HTML also written to", downloads_path)

downloads_path2 = r"C:\Users\HrishikeshS\Downloads\ODRA_Quote_Generator_Rep_1 1.html"
if os.path.exists(downloads_path2):
    with open(downloads_path2, "w", encoding="utf-8") as f_dl2:
        f_dl2.write(html_template)
    print("Updated HTML also written to", downloads_path2)

print("All tasks completed successfully!")
