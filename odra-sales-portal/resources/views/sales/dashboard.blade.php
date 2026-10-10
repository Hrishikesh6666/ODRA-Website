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

/* Interactive KPI Grid */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}
@media (max-width: 1024px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 600px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
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
.kpi-card.kpi-pipeline:hover::after { background: #6366f1; }

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
.kpi-sub {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
}
.kpi-filter-hint {
    font-size: 11px;
    color: var(--amg-red);
    font-weight: 600;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0.8;
}

/* Dashboard Main Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 2.1fr 1fr;
    gap: 28px;
}
@media (max-width: 1080px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
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
            <a href="{{ route('sales.models') }}" class="btn-hero-secondary amg-btn-shimmer">
                <span>Explore Sweeper Lineup (Showcase)</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
            <a href="{{ route('sales.quotes.create') }}" class="btn-hero-primary amg-btn-shimmer">
                <span>+ Launch Quote Builder</span>
            </a>
        </div>
    </div>

    <!-- Interactive KPI Stat Cards (With CountUp & Click-to-Filter) -->
    <div class="stats-row">
        <!-- 1. Total Quotes -->
        <div class="kpi-card kpi-total amg-slide-up amg-delay-1 kpi-trigger" data-kpi-filter="all" title="Click to view all quotations">
            <div class="kpi-header">
                <span class="kpi-label">Total Quotations</span>
                <div class="kpi-icon-badge" style="background:#fee2e2;color:#ac2529;">&equiv;</div>
            </div>
            <div class="kpi-val amg-counter" data-target="{{ $totalQuotes }}">{{ $totalQuotes }}</div>
            <div class="kpi-sub">
                <span>All lifetime quotations generated</span>
            </div>
            <div class="kpi-filter-hint">&rarr; Click to show all</div>
        </div>

        <!-- 2. Active Drafts -->
        <div class="kpi-card kpi-draft amg-slide-up amg-delay-2 kpi-trigger" data-kpi-filter="draft" title="Click to filter active drafts">
            <div class="kpi-header">
                <span class="kpi-label">Active Drafts</span>
                <div class="kpi-icon-badge" style="background:#fef3c7;color:#b45309;">&bull;</div>
            </div>
            <div class="kpi-val amg-counter" style="color:#d97706;" data-target="{{ $draftQuotes }}">{{ $draftQuotes }}</div>
            <div class="kpi-sub">
                <span class="amg-pulse-dot pulse-amber"></span> In-progress builder drafts
            </div>
            <div class="kpi-filter-hint" style="color:#d97706;">&rarr; Filter drafts only</div>
        </div>

        <!-- 3. Finalized Quotes -->
        <div class="kpi-card kpi-finalized amg-slide-up amg-delay-3 kpi-trigger" data-kpi-filter="finalized" title="Click to filter finalized quotes">
            <div class="kpi-header">
                <span class="kpi-label">Finalized Quotations</span>
                <div class="kpi-icon-badge" style="background:#dcfce7;color:#15803d;">&#10003;</div>
            </div>
            <div class="kpi-val amg-counter" style="color:#16a34a;" data-target="{{ $finalizedQuotes }}">{{ $finalizedQuotes }}</div>
            <div class="kpi-sub">
                <span class="amg-pulse-dot pulse-emerald"></span> Locked PDFs ready for customer
            </div>
            <div class="kpi-filter-hint" style="color:#16a34a;">&rarr; Filter finalized only</div>
        </div>

        <!-- 4. Total Quoted Pipeline Value -->
        <div class="kpi-card kpi-pipeline amg-slide-up amg-delay-4" title="Total cumulative pipeline amount">
            <div class="kpi-header">
                <span class="kpi-label">Quoted Pipeline Value</span>
                <div class="kpi-icon-badge" style="background:#e0e7ff;color:#4338ca;">$</div>
            </div>
            <div class="kpi-val amg-counter" style="color:#4338ca;" data-target="{{ $totalQuotedAmount }}" data-currency="true">
                ${{ number_format($totalQuotedAmount) }}
            </div>
            <div class="kpi-sub">
                <span>{{ $conversionRate }}% Conversion Rate ({{ $finalizedQuotes }} of {{ $totalQuotes }})</span>
            </div>
            <div class="ratio-progress-bar">
                <div class="ratio-fill-finalized" style="width: {{ $conversionRate }}%;" title="Finalized: {{ $conversionRate }}%"></div>
                <div class="ratio-fill-draft" style="width: {{ 100 - $conversionRate }}%;" title="Draft: {{ 100 - $conversionRate }}%"></div>
            </div>
        </div>
    </div>

    <!-- Dashboard Main Grid -->
    <div class="dashboard-grid">

        <!-- Left Column: Interactive Quotations Command Panel -->
        <div class="amg-slide-up amg-delay-3">
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
                        <input type="text" id="quoteTableSearch" placeholder="Search by #, customer, or model...">
                    </div>
                </div>

                <!-- Table Content -->
                <div class="table-scroll-container">
                    <table class="data-table-interactive" id="recentQuotesTable">
                        <thead>
                            <tr>
                                <th>Quote #</th>
                                <th>Customer &amp; Agency</th>
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
                                    $quoteDataJson = htmlspecialchars(json_encode([
                                        'number' => $q->quote_number,
                                        'status' => $q->status,
                                        'customer' => $snap?->customer_company ?? 'Unassigned Customer',
                                        'contact' => $snap?->customer_contact ?? 'Direct',
                                        'address' => trim(($snap?->customer_address_1 ?? '') . ' ' . ($snap?->customer_address_2 ?? '')),
                                        'model' => $snap?->model_code ?? 'MT4H',
                                        'chassis' => $snap?->chassis_configuration ?? 'Standard Chassis',
                                        'subtotal' => $snap?->subtotal ?? 0,
                                        'freight' => $snap?->freight_amount ?? 0,
                                        'total' => $snap?->total ?? 0,
                                        'viewUrl' => route('sales.quotes.show', $q),
                                        'pdfUrl' => route('sales.quotes.pdf', $q),
                                    ]), ENT_QUOTES, 'UTF-8');
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
                                        <span class="badge" style="background:#f1f5f9;color:#334155;font-weight:700;">{{ $snap?->model_code ?? 'MT4H' }}</span>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ Str::limit($snap?->chassis_configuration ?? 'Standard Chassis', 28) }}</div>
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
                                        <div style="display:inline-flex;gap:4px;">
                                            <button type="button" class="btn-table-action" onclick="openQuickQuoteModal({{ $quoteDataJson }})" title="Quick Inspect">
                                                <span>Inspect</span>
                                            </button>
                                            <a href="{{ route('sales.quotes.show', $q) }}" class="btn-table-action btn-table-primary" title="Open in Quote Builder">
                                                <span>Open</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:36px;color:#94a3b8;">
                                        No quotations found. <a href="{{ route('sales.quotes.create') }}" style="color:var(--amg-red);font-weight:600;">Create your first quote &rarr;</a>
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="noQuotesMatchRow" style="display:none;">
                                <td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">
                                    No quotations match the active search or status filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Mercedes-AMG Sweeper Showcase & Territory Explorer -->
        <div class="amg-slide-up amg-delay-4">

            <!-- Card 1: Interactive Sweeper Lineup Showcase -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title">
                        <span>Sweeper Models Lineup</span>
                    </h3>
                    <a href="{{ route('sales.models') }}" style="font-size:12px;color:var(--amg-red);font-weight:700;text-decoration:none;">
                        All Specs &rarr;
                    </a>
                </div>

                <div style="padding: 16px;">
                    <!-- Mini Sweeper Card: MT4H -->
                    <div class="sweeper-mini-card">
                        <div class="sweeper-mini-img-wrap">
                            <img src="{{ asset('images/models/landing-mt4h-diesel.webp') }}" alt="ODRA MT4H" class="sweeper-mini-img">
                        </div>
                        <div class="sweeper-mini-title">
                            <span>ODRA MT4H</span>
                            <span class="badge" style="background:rgba(16,185,129,0.2);color:#34d399;font-size:10px;">NON-CDL</span>
                        </div>
                        <div class="sweeper-mini-desc">
                            Dual-engine agility with 4 yd&sup3; variable 10' high-dump on Isuzu NRR cabover.
                        </div>
                        <div class="sweeper-mini-chips">
                            <span class="sweeper-chip">4.0 yd&sup3; Hopper</span>
                            <span class="sweeper-chip">220 Gal Water</span>
                            <span class="sweeper-chip">65HP Tier 4-F</span>
                        </div>
                        <div class="sweeper-mini-actions">
                            <a href="{{ route('sales.quotes.create') }}?model=MT4H" class="btn-mini-primary">Build MT4H Quote &rarr;</a>
                            <a href="{{ route('sales.models') }}" class="btn-mini-secondary">Specs</a>
                        </div>
                    </div>

                    <!-- Mini Sweeper Card: MS5H -->
                    <div class="sweeper-mini-card">
                        <div class="sweeper-mini-img-wrap">
                            <img src="{{ asset('images/models/landing-ms5h.webp') }}" alt="ODRA MS5H" class="sweeper-mini-img">
                        </div>
                        <div class="sweeper-mini-title">
                            <span>ODRA MS5H</span>
                            <span class="badge" style="background:rgba(59,130,246,0.2);color:#60a5fa;font-size:10px;">CLASS 6/7</span>
                        </div>
                        <div class="sweeper-mini-desc">
                            Single-engine highway powerhouse powered by 300HP Cummins B6.7 on Freightliner M2 Plus.
                        </div>
                        <div class="sweeper-mini-chips">
                            <span class="sweeper-chip">5.0 yd&sup3; Hopper</span>
                            <span class="sweeper-chip">355 Gal Water</span>
                            <span class="sweeper-chip">300HP Cummins</span>
                        </div>
                        <div class="sweeper-mini-actions">
                            <a href="{{ route('sales.quotes.create') }}?model=MS5H" class="btn-mini-primary">Build MS5H Quote &rarr;</a>
                            <a href="{{ route('sales.models') }}" class="btn-mini-secondary">Specs</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: My Assigned Territories -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title">
                        <span>Assigned Territories</span>
                    </h3>
                </div>
                <div style="padding: 18px 20px;">
                    @if ($repTerritories->count() > 0)
                        <div class="territory-chip-list">
                            @foreach ($repTerritories as $t)
                                <div class="territory-badge-pill">
                                    <span>{{ $t->label }}</span>
                                    <span class="territory-code">{{ $t->code }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 10px;">
                            Nationwide municipal &amp; contractor quoting coverage active across all 50 US States.
                        </p>
                        <div class="territory-chip-list">
                            <span class="territory-badge-pill">US National Coverage <span class="territory-code">50-ST</span></span>
                            <span class="territory-badge-pill">Standard Freight Rates <span class="territory-code">AUTO</span></span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card 3: Pre-Approved Cooperatives -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3 class="dash-card-title">
                        <span>Pre-Approved Cooperatives</span>
                    </h3>
                </div>
                <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
                        <img src="{{ asset('images/logo-sourcewell.png') }}" alt="Sourcewell" style="height:22px;object-fit:contain;">
                        <div style="font-size:12px;color:#334155;"><strong>Contract #093021-ODR</strong><br><span style="color:#64748b;">Class 5-8 Street Sweepers</span></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
                        <img src="{{ asset('images/logo-hgac.png') }}" alt="H-GAC" style="height:20px;object-fit:contain;">
                        <div style="font-size:12px;color:#334155;"><strong>H-GACBuy Contract</strong><br><span style="color:#64748b;">Municipal Road Equipment</span></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
                        <img src="{{ asset('images/logo-buyboard.png') }}" alt="BuyBoard" style="height:20px;object-fit:contain;">
                        <div style="font-size:12px;color:#334155;"><strong>BuyBoard National</strong><br><span style="color:#64748b;">Local Government Purchasing</span></div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Quick Quote Inspection Modal Dialog -->
<div class="amg-drawer-backdrop" id="quickQuoteModal" onclick="if(event.target===this) closeQuickQuoteModal()">
    <div class="amg-drawer-card">
        <div style="padding: 20px 24px; border-bottom: 1px solid #334155; background: #111827; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 800; color: white;" id="modalQuoteNumber">Quote Inspection</h3>
                <span id="modalQuoteStatus" class="badge">Draft</span>
            </div>
            <button type="button" onclick="closeQuickQuoteModal()" style="background: rgba(255,255,255,0.1); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <div style="padding: 24px; overflow-y: auto; color: #cbd5e1; font-size: 13.5px; line-height: 1.6;">
            <!-- Customer Card -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 18px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px;">Customer / Agency</div>
                <div style="font-size: 16px; font-weight: 700; color: white;" id="modalQuoteCustomer">City of Miami Public Works</div>
                <div style="color: #94a3b8; margin-top: 2px;">Contact: <span id="modalQuoteContact" style="color:white;">Direct</span></div>
                <div style="color: #94a3b8; margin-top: 2px;">Delivery: <span id="modalQuoteAddress" style="color:#e2e8f0;">Miami, FL</span></div>
            </div>

            <!-- Equipment Card -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 18px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px;">Configured Equipment</div>
                <div style="font-size: 16px; font-weight: 700; color: white;" id="modalQuoteModel">ODRA MT4H Sweeper</div>
                <div style="color: #cbd5e1; margin-top: 4px;" id="modalQuoteChassis">Isuzu NRR Cabover 19,500 GVWR - Left Dump</div>
            </div>

            <!-- Pricing Breakdown -->
            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px;">
                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
                    <span style="color:#94a3b8;">Equipment Subtotal</span>
                    <strong style="color:white;" id="modalQuoteSubtotal">$0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid rgba(255,255,255,0.06);margin-top:4px;">
                    <span style="color:#94a3b8;">Freight / Delivery</span>
                    <strong style="color:white;" id="modalQuoteFreight">$0</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0 0;margin-top:6px;font-size:16px;">
                    <span style="color:white;font-weight:700;">Total Quotation Value</span>
                    <strong style="color:#34d399;" id="modalQuoteTotal">$0</strong>
                </div>
            </div>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid #334155; background: #111827; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-table-action" onclick="closeQuickQuoteModal()">Close</button>
            <a id="modalQuotePdfBtn" href="#" class="btn-table-action" target="_blank" style="background:#0284c7;color:white;border-color:#0284c7;">
                <span>Download PDF &darr;</span>
            </a>
            <a id="modalQuoteViewBtn" href="#" class="btn-table-action btn-table-primary">
                <span>Open in Quote Builder &rarr;</span>
            </a>
        </div>
    </div>
</div>

@endsection
