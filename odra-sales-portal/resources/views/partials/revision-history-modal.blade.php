<!-- Quotation Revision History Modal Dialog -->
<div class="amg-drawer-backdrop" id="revisionHistoryModal" style="display:none;" onclick="if(event.target===this) closeRevisionHistory()">
    <div class="amg-drawer-card" style="max-width: 820px; width: 95%;">
        <!-- Header -->
        <div style="padding: 18px 24px; border-bottom: 1px solid #334155; background: #111827; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline><path d="M4.93 4.93 3 7"></path></svg>
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: white;" id="revModalTitle">Quotation Revision History</h3>
                <span id="revModalQuoteBadge" style="font-size: 11px; font-weight: 700; background: rgba(255,255,255,0.1); color: #cbd5e1; padding: 2px 8px; border-radius: 4px;">Q-0000</span>
            </div>
            <button type="button" onclick="closeRevisionHistory()" style="background: rgba(255,255,255,0.1); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <!-- Loading Spinner Container -->
        <div id="revLoadingSpinner" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 24px; gap: 12px;">
            <div class="amg-pulse-dot pulse-amber" style="width: 16px; height: 16px;"></div>
            <div style="color: #cbd5e1; font-size: 13.5px; font-weight: 600;">Loading revision history from server...</div>
        </div>

        <!-- Error Container -->
        <div id="revErrorBox" style="display: none; padding: 24px; background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; margin: 20px;"></div>

        <!-- Revisions Table Content -->
        <div id="revModalBodyContent" style="display: none; padding: 24px; overflow-y: auto; max-height: calc(85vh - 140px); color: #cbd5e1; font-size: 13.5px;">
            <div style="margin-bottom: 16px; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; font-size: 13px; color: #94a3b8; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    All historical finalized revisions are permanently preserved and read-only.
                </div>
                <div id="revTotalCount" style="font-weight: 700; color: white;"></div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #334155; color: #94a3b8; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 10px 8px;">Revision</th>
                            <th style="padding: 10px 8px;">Date</th>
                            <th style="padding: 10px 8px;">Status</th>
                            <th style="padding: 10px 8px;">Equipment / Basis</th>
                            <th style="padding: 10px 8px; text-align: right;">Amount</th>
                            <th style="padding: 10px 8px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="revTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Actions -->
        <div style="padding: 16px 24px; border-top: 1px solid #334155; background: #111827; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div>
                <button type="button" id="revCreateNewBtn" class="btn-table-action" style="display:none; background: #dc2626; color: white; border-color: #dc2626; font-weight: 700; cursor: pointer;">
                    + Create Revised Quotation
                </button>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-table-action" onclick="closeRevisionHistory()">Close</button>
            </div>
        </div>
    </div>
</div>
