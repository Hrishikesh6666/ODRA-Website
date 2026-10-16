<!-- Quick Quote Inspection Modal Dialog -->
<div class="amg-drawer-backdrop" id="quickQuoteModal" onclick="if(event.target===this) closeQuickQuoteModal()">
    <div class="amg-drawer-card">
        <!-- Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid #334155; background: #111827; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: white;" id="modalQuoteNumber">Quote Inspection</h3>
                <span id="modalQuoteVersion" style="font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.1); color: #cbd5e1; padding: 2px 7px; border-radius: 4px;">v1</span>
                <span id="modalQuoteStatus" class="badge">Draft</span>
            </div>
            <button type="button" onclick="closeQuickQuoteModal()" style="background: rgba(255,255,255,0.1); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <!-- Loading Spinner Container -->
        <div id="modalLoadingSpinner" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 24px; gap: 12px;">
            <div class="amg-pulse-dot pulse-amber" style="width: 16px; height: 16px;"></div>
            <div style="color: #cbd5e1; font-size: 13.5px; font-weight: 600;">Loading quotation details from server...</div>
        </div>

        <!-- Error Container -->
        <div id="modalErrorBox" style="display: none; padding: 24px; background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; margin: 20px;"></div>

        <!-- Details Content -->
        <div id="modalBodyContent" style="display: none; padding: 24px; overflow-y: auto; color: #cbd5e1; font-size: 13.5px; line-height: 1.6;">
            <div id="modalQuoteDates" style="font-size: 11.5px; color: #94a3b8; margin-bottom: 14px;"></div>

            <!-- Customer Card -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px;">Customer / Purchasing Agency</div>
                <div style="font-size: 16px; font-weight: 700; color: white;" id="modalQuoteCustomer">City of Miami Public Works</div>
                <div style="color: #94a3b8; margin-top: 2px;">Attention / Contact: <span id="modalQuoteContact" style="color:white; font-weight: 600;">Direct</span></div>
                <div style="color: #94a3b8; margin-top: 2px;">Delivery Destination: <span id="modalQuoteAddress" style="color:#e2e8f0;">Miami, FL</span></div>
                <div style="color: #94a3b8; margin-top: 2px;">PO / Reference #: <span id="modalQuotePo" style="color:#e2e8f0;">None</span></div>
            </div>

            <!-- Equipment Card -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px;">Configured Equipment &amp; Pricing Contract</div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size: 16px; font-weight: 700; color: white;" id="modalQuoteModel">ODRA MT4H Sweeper</div>
                    <span id="modalQuoteBasis" style="background: rgba(255,255,255,0.1); color: #38bdf8; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;">Commercial</span>
                </div>
                <div style="color: #cbd5e1; margin-top: 4px;" id="modalQuoteChassis">Isuzu NRR Cabover 19,500 GVWR - Left Dump</div>
            </div>

            <!-- Optional Enhancements List -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 6px;">Included Options &amp; Enhancements</div>
                <div id="modalQuoteEnhancementsList"></div>
            </div>

            <!-- Pricing Breakdown -->
            <div style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 16px;">
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
                    <span style="color:#94a3b8;">Base Equipment Price</span>
                    <strong style="color:white;" id="modalQuoteBaseUnit">$0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.06);margin-top:2px;">
                    <span style="color:#94a3b8;">Equipment Subtotal</span>
                    <strong style="color:white;" id="modalQuoteSubtotal">$0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.06);margin-top:2px;">
                    <span style="color:#94a3b8;">Freight / Delivery <span id="modalQuoteFob" style="font-size:11px;color:#64748b;"></span></span>
                    <strong style="color:white;" id="modalQuoteFreight">$0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0 2px;margin-top:6px;font-size:17px;">
                    <span style="color:white;font-weight:700;">Total Quoted Value</span>
                    <strong style="color:#34d399;" id="modalQuoteTotal">$0</strong>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div style="padding: 16px 24px; border-top: 1px solid #334155; background: #111827; display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn-table-action" onclick="closeQuickQuoteModal()">Close</button>
            <a id="modalQuotePrintBtn" href="#" class="btn-table-action" target="_blank" style="background:#f1f5f9;color:#0f172a;border-color:#cbd5e1;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Print</span>
            </a>
            <a id="modalQuotePdfBtn" href="#" class="btn-table-action btn-table-download" target="_blank" style="display:none;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Download PDF</span>
            </a>
            <a id="modalQuoteViewBtn" href="#" class="btn-table-action btn-table-primary">
                <span>Open in Quote Builder &rarr;</span>
            </a>
        </div>
    </div>
</div>
