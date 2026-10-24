@extends('layouts.app')

@section('title', 'Sales Executive Dashboard')

@push('styles')
<style>
/* Dashboard Luxury Styling */
.welcome-hero {
    background: radial-gradient(circle at 85% 20%, #1e293b 0%, #0a0e17 80%);
    color: white;
    padding: 32px 36px;
    border-radius: 14px;
    margin-bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.35);
    position: relative;
    overflow: hidden;
}
.welcome-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--amg-red-bright), transparent);
}
.welcome-title h1 {
    margin: 0 0 6px 0;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.4px;
}
.welcome-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 13px;
    color: #94a3b8;
    flex-wrap: wrap;
}
.welcome-badge-live {
    display: inline-flex;
    align-items: center;
    font-size: 11.5px;
    color: #34d399;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    padding: 3px 10px;
    border-radius: 999px;
    font-weight: 600;
}
.welcome-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-hero-primary {
    background: var(--amg-red);
    color: white;
    text-decoration: none;
    padding: 12px 22px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--amg-red-bright);
    box-shadow: 0 4px 14px var(--amg-border-glow);
    transition: all 0.2s ease;
}
.btn-hero-primary:hover {
    background: #8e1e22;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(172, 37, 41, 0.5);
}
.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.08);
    color: #f1f5f9;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13.5px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    transition: all 0.2s ease;
}
.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    transform: translateY(-2px);
}

/* Quotation Pipeline Summary Section */
.pipeline-summary-container {
    margin-bottom: 28px;
}
.pipeline-summary-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 14px;
}
.pipeline-summary-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-dark);
    margin: 0;
    letter-spacing: -0.3px;
}
.pipeline-summary-subtitle {
    font-size: 12.5px;
    color: var(--text-muted);
    margin: 2px 0 0 0;
}
.pipeline-conversion-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}
.pipeline-cards-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media (max-width: 900px) {
    .pipeline-cards-row {
        grid-template-columns: 1fr;
    }
}

.kpi-count-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    margin-top: 4px;
    margin-bottom: 4px;
}
.pill-draft {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.pill-finalized {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.pill-total {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #e2e8f0;
}

.kpi-card {
    background: white;
    padding: 22px;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.12), 0 0 16px rgba(172, 37, 41, 0.1);
    border-color: #cbd5e1;
}
.kpi-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    transition: background 0.25s ease;
}
.kpi-card.kpi-total:hover::after { background: var(--amg-red); }
.kpi-card.kpi-draft:hover::after { background: #f59e0b; }
.kpi-card.kpi-finalized:hover::after { background: #10b981; }

.kpi-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.kpi-label {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.kpi-icon-badge {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}
.kpi-val {
    font-size: 32px;
    font-weight: 800;
    color: var(--text-dark);
    letter-spacing: -0.6px;
    line-height: 1.1;
    margin-bottom: 6px;
}
.kpi-filter-hint {
    font-size: 11px;
    color: var(--amg-red);
    font-weight: 600;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0.8;
}

/* Bottom Grid for Territories & Cooperatives */
.dashboard-bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 4px;
}
@media (max-width: 900px) {
    .dashboard-bottom-grid {
        grid-template-columns: 1fr;
    }
}

.btn-table-download {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
    font-weight: 700 !important;
}
.btn-table-download:hover {
    background: #0369a1 !important;
    color: #ffffff !important;
    border-color: #0369a1 !important;
}

/* Interactive Card Container */
.dash-card {
    background: white;
    border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    margin-bottom: 28px;
    overflow: hidden;
}
.dash-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.dash-card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}
.dash-card-title-badge {
    font-size: 11px;
    background: #f1f5f9;
    color: #475569;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 600;
}

/* Interactive Table Toolbar */
.table-toolbar {
    padding: 16px 24px;
    background: #fafbfc;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.filter-pills-row {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.quote-filter-pill {
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: white;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
}
.quote-filter-pill:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.quote-filter-pill.active {
    background: var(--amg-red);
    color: white;
    border-color: var(--amg-red);
}
.search-input-wrap {
    position: relative;
    min-width: 240px;
}
.search-input-wrap input {
    width: 100%;
    padding: 7px 12px 7px 34px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.15s ease;
}
.search-input-wrap input:focus {
    border-color: var(--amg-red);
}
.search-input-wrap svg {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #94a3b8;
}

/* Quotation Data Table */
.data-table-interactive {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table-interactive th {
    background: #f8fafc;
    padding: 12px 18px;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #475569;
    border-bottom: 1px solid var(--border);
    text-align: left;
}
.data-table-interactive td {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.data-table-interactive tr {
    transition: background 0.15s ease;
}
.data-table-interactive tbody tr:hover {
    background: #f8fafc;
}
.badge-status-finalized {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}
.badge-status-draft {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

/* Action Buttons inside Table */
.btn-table-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--border);
    background: white;
    color: #334155;
    transition: all 0.15s ease;
    cursor: pointer;
}
.btn-table-action:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}
.btn-table-primary {
    background: var(--amg-red);
    color: white;
    border-color: var(--amg-red);
}
.btn-table-primary:hover {
    background: #8e1e22;
    color: white;
}

/* Pipeline Ratio Progress Widget */
.ratio-progress-bar {
    display: flex;
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    background: #e2e8f0;
    margin: 12px 0 6px;
}
.ratio-fill-finalized {
    background: #10b981;
    transition: width 0.8s ease;
}
.ratio-fill-draft {
    background: #f59e0b;
    transition: width 0.8s ease;
}

/* Right Column: Mercedes-AMG Sweeper Showcase Cards */
.sweeper-mini-card {
    background: radial-gradient(circle at 50% 0%, #1e293b 0%, #0d131f 90%);
    color: white;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 18px;
    margin-bottom: 16px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}
.sweeper-mini-card:hover {
    transform: translateY(-3px);
    border-color: rgba(172, 37, 41, 0.4);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.4), 0 0 16px rgba(172, 37, 41, 0.2);
}
.sweeper-mini-img-wrap {
    background: rgba(0, 0, 0, 0.25);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    margin-bottom: 14px;
    overflow: hidden;
}
.sweeper-mini-img {
    max-width: 100%;
    height: 120px;
    object-fit: contain;
    transition: transform 0.4s ease;
}
.sweeper-mini-card:hover .sweeper-mini-img {
    transform: scale(1.08);
}
.sweeper-mini-title {
    font-size: 16px;
    font-weight: 800;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.sweeper-mini-desc {
    font-size: 12px;
    color: #94a3b8;
    margin: 0 0 12px;
    line-height: 1.4;
}
.sweeper-mini-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 14px;
}
.sweeper-chip {
    font-size: 10.5px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: #cbd5e1;
    padding: 3px 8px;
    border-radius: 4px;
}
.sweeper-mini-actions {
    display: flex;
    gap: 8px;
}
.btn-mini-primary {
    flex: 1;
    background: var(--amg-red);
    color: white;
    text-align: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid var(--amg-red-bright);
    transition: all 0.15s ease;
}
.btn-mini-primary:hover {
    background: #8e1e22;
}
.btn-mini-secondary {
    background: rgba(255, 255, 255, 0.08);
    color: #e2e8f0;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.15s ease;
}
.btn-mini-secondary:hover {
    background: rgba(255, 255, 255, 0.16);
    color: white;
}

/* Assigned Territories Chips */
.territory-chip-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.territory-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
}
.territory-code {
    background: #e2e8f0;
    color: #0f172a;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
}
</style>
@endpush

@section('content')
<div class="amg-fade-in">

    <!-- Mercedes-AMG Style Welcome Hero -->
    <div class="welcome-hero amg-slide-up">
        <div class="welcome-title">
            <h1>Welcome back, {{ $user->name }}</h1>
            <div class="welcome-meta">
                <span>Rep Code: <strong style="color:white;">{{ $user->rep_code ?? 'HQ' }}</strong></span>
                <span>&bull;</span>
                <span>Territory: <strong style="color:white;">{{ $user->territory ?? 'All Assigned Territories' }}</strong></span>
                <span>&bull;</span>
                <span class="welcome-badge-live">
                    <span class="amg-pulse-dot pulse-emerald"></span> Live Commercial Pricing Active
                </span>
            </div>
        </div>

        <div class="welcome-actions">
            <a href="{{ route('sales.quotes.create') }}" class="btn-hero-primary amg-btn-shimmer">
                <span>+ Generate New Quote</span>
            </a>
        </div>
    </div>

    <!-- Quotation Pipeline Summary Section -->
    <div class="pipeline-summary-container amg-slide-up amg-delay-1">
        <div class="pipeline-summary-header">
            <div>
                <h2 class="pipeline-summary-title">Quotation Pipeline Summary</h2>
                <p class="pipeline-summary-subtitle">Real-time commercial valuation and deal breakdown for your sales territory</p>
            </div>
            @if ($totalQuotedCount > 0)
                <div class="pipeline-conversion-badge">
                    <span class="amg-pulse-dot pulse-emerald"></span>
                    <span>{{ $conversionRate }}% Conversion Rate ({{ $finalizedQuotesCount }} of {{ $totalQuotedCount }})</span>
                </div>
            @endif
        </div>

        <div class="pipeline-cards-row">
            <!-- Card A: Draft Quotation Value -->
            <div class="kpi-card kpi-draft kpi-trigger" data-kpi-filter="draft" title="Click to filter active draft quotations">
                <div class="kpi-header">
                    <span class="kpi-label">Draft Quotation Value</span>
                    <div class="kpi-icon-badge" style="background:#fef3c7;color:#b45309;">&bull;</div>
                </div>
                <div class="kpi-val amg-counter" style="color:#d97706;" data-target="{{ $draftQuotedValue }}" data-currency="true">
                    ${{ number_format($draftQuotedValue) }}
                </div>
                <div class="kpi-count-pill pill-draft">
                    {{ $draftQuotesCount }} {{ Str::plural('Draft Quotation', $draftQuotesCount) }}
                </div>
                <div class="kpi-filter-hint" style="color:#d97706;">&rarr; Filter drafts only</div>
            </div>

            <!-- Card B: Finalized Quotation Value -->
            <div class="kpi-card kpi-finalized kpi-trigger" data-kpi-filter="finalized" title="Click to filter finalized quotations">
                <div class="kpi-header">
                    <span class="kpi-label">Finalized Quotation Value</span>
                    <div class="kpi-icon-badge" style="background:#dcfce7;color:#15803d;">&#10003;</div>
                </div>
                <div class="kpi-val amg-counter" style="color:#16a34a;" data-target="{{ $finalizedQuotedValue }}" data-currency="true">
                    ${{ number_format($finalizedQuotedValue) }}
                </div>
                <div class="kpi-count-pill pill-finalized">
                    {{ $finalizedQuotesCount }} {{ Str::plural('Finalized Quotation', $finalizedQuotesCount) }}
                </div>
                <div class="kpi-filter-hint" style="color:#16a34a;">&rarr; Filter finalized only</div>
            </div>

            <!-- Card C: Total Quoted Value -->
            <div class="kpi-card kpi-total kpi-trigger" data-kpi-filter="all" title="Click to show all quotations">
                <div class="kpi-header">
                    <span class="kpi-label">Total Quoted Value</span>
                    <div class="kpi-icon-badge" style="background:#fee2e2;color:#ac2529;">$</div>
                </div>
                <div class="kpi-val amg-counter" style="color:#0f172a;" data-target="{{ $totalQuotedValue }}" data-currency="true">
                    ${{ number_format($totalQuotedValue) }}
                </div>
                <div class="kpi-count-pill pill-total">
                    {{ $totalQuotedCount }} {{ Str::plural('Quotation', $totalQuotedCount) }}
                </div>
                <div class="kpi-filter-hint">&rarr; Show all quotations</div>
            </div>
        </div>
    </div>

    <!-- Recent Quotations Full-Width Command Panel -->
    <div class="amg-slide-up amg-delay-2" style="margin-bottom: 28px;">
        <div class="dash-card" id="quotesTableCard">
            <div class="dash-card-header">
                <h3 class="dash-card-title">
                    <span>Recent Quotations</span>
                    <span class="dash-card-title-badge" id="filteredQuotesCount">Showing {{ $recentQuotes->count() }} quotes</span>
                </h3>
                <a href="{{ route('sales.quotes.index') }}" style="font-size: 13px; color: var(--amg-red); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <span>View All Quotations</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <!-- Table Filter & Live Search Toolbar -->
            <div class="table-toolbar">
                <div class="filter-pills-row">
                    <button type="button" class="quote-filter-pill active" data-filter="all">All ({{ $totalQuotes }})</button>
                    <button type="button" class="quote-filter-pill" data-filter="finalized">Finalized ({{ $finalizedQuotes }})</button>
                    <button type="button" class="quote-filter-pill" data-filter="draft">Drafts ({{ $draftQuotes }})</button>
                </div>

                <div class="search-input-wrap">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="quoteTableSearch" placeholder="Search by #, customer, sales person, or model...">
                </div>
            </div>

            <!-- Table Content -->
            <div class="table-scroll-container">
                <table class="data-table-interactive" id="recentQuotesTable">
                    <thead>
                        <tr>
                            <th>Quote #</th>
                            <th>Customer &amp; Agency</th>
                            <th>Sales Person</th>
                            <th>Sweeper Model</th>
                            <th>Status</th>
                            <th style="text-align:right;">Total Quoted</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentQuotes as $q)
                            @php
                                $snap = $q->latestVersion?->snapshot;
                            @endphp
                            <tr data-status="{{ strtolower($q->status) }}">
                                <td>
                                    <strong style="color:#0f172a;">{{ $q->quote_number }}</strong>
                                    <div style="font-size:11px;color:#94a3b8;">v{{ $q->current_version }} &middot; {{ $q->updated_at->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:#1e293b;">{{ $snap?->customer_company ?? 'Draft Customer' }}</div>
                                    <div style="font-size:11.5px;color:#64748b;">{{ $snap?->customer_contact ?? 'Direct Inquiry' }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:#1e293b;">{{ $snap?->sales_rep_name ?: ($q->user?->name ?? 'Sales Representative') }}</div>
                                    @if ($q->user?->rep_code)
                                        <div style="font-size:11px;color:#64748b;">Code: {{ $q->user->rep_code }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9;color:#334155;font-weight:700;">{{ $snap?->model_code ?? 'MT4H' }}</span>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ Str::limit($snap?->chassis_configuration ?? 'Standard Chassis', 36) }}</div>
                                </td>
                                <td>
                                    @if ($q->status === 'Finalized')
                                        <span class="badge-status-finalized">&#10003; Finalized</span>
                                    @else
                                        <span class="badge-status-draft">&bull; Draft</span>
                                    @endif
                                </td>
                                <td style="text-align:right;font-weight:700;color:#0f172a;">
                                    ${{ number_format($snap?->total ?? 0) }}
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;">
                                        {{-- 0. Revision History button --}}
                                        <button type="button" class="btn-table-action" onclick="openRevisionHistory({{ $q->id }})" title="View Complete Revision History" style="background:#f8fafc;color:#334155;border-color:#cbd5e1;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline><path d="M4.93 4.93 3 7"></path></svg>
                                            <span>History</span>
                                        </button>

                                        {{-- 1. Inspect button (works for both Draft & Finalized) --}}
                                        <button type="button" class="btn-table-action" onclick="inspectQuote({{ $q->id }})" title="Inspect Quotation Details">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                            <span>Inspect</span>
                                        </button>

                                        {{-- 2. Status-based buttons: Print & Download for Finalized, Open for Draft --}}
                                        @if ($q->status === 'Finalized')
                                            <a href="{{ route('sales.quotes.show', $q) }}?print=1" class="btn-table-action" target="_blank" title="Print Quotation" style="background:#f8fafc;color:#0f172a;border-color:#cbd5e1;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                                <span>Print</span>
                                            </a>
                                            <a href="{{ route('sales.quotes.pdf', $q) }}" class="btn-table-action btn-table-download" title="Download Finalized PDF" download="ODRA_Quotation_{{ $q->quote_number }}.pdf">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                                <span>Download</span>
                                            </a>
                                        @else
                                            <a href="{{ route('sales.quotes.show', $q) }}" class="btn-table-action btn-table-primary" title="Open and Edit Draft Quotation">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                <span>Open</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center;padding:36px;color:#94a3b8;">
                                    No quotations found. <a href="{{ route('sales.quotes.create') }}" style="color:var(--amg-red);font-weight:600;">Create your first quote &rarr;</a>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noQuotesMatchRow" style="display:none;">
                            <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">
                                No quotations match the active search or status filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
    </div>

</div>
@endsection
