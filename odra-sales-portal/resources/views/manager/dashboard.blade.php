@extends('layouts.manager')

@section('title', 'Executive Dashboard')
@section('header-title', 'Commercial Overview & Financials')

@push('styles')
<style>
    /* Metric Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .kpi-card {
        background: white;
        padding: 22px;
        border-radius: 8px;
        border: 1px solid var(--border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .kpi-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }
    .kpi-value.green { color: #059669; }
    .kpi-value.amber { color: #d97706; }
    .kpi-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
        display: flex;
        justify-content: space-between;
    }

    /* Grid Layouts */
    .dashboard-grid-2 {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 1100px) {
        .dashboard-grid-2 { grid-template-columns: 1fr; }
    }

    .card-header-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }
    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Leaderboard Styles */
    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
    }
    .rank-1 { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .rank-2 { background: #e2e8f0; color: #334155; }
    .rank-3 { background: #ffedd5; color: #c2410c; }

    /* Trend Bar */
    .trend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .trend-item:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<!-- Financial KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card" style="border-top: 4px solid var(--red);">
        <div>
            <div class="kpi-title">Total Quoted Value</div>
            <div class="kpi-value">${{ number_format($totalQuotedValue, 0) }}</div>
        </div>
        <div class="kpi-sub">
            <span>{{ $totalQuotesCount }} Total Proposals Generated</span>
            <span>All Teams</span>
        </div>
    </div>

    <div class="kpi-card" style="border-top: 4px solid #059669;">
        <div>
            <div class="kpi-title">Finalized Commercial Value</div>
            <div class="kpi-value green">${{ number_format($finalizedValue, 0) }}</div>
        </div>
        <div class="kpi-sub">
            <span>{{ $finalizedQuotesCount }} Finalized / Signed Deals</span>
            <span>Firm Pricing</span>
        </div>
    </div>

    <div class="kpi-card" style="border-top: 4px solid #d97706;">
        <div>
            <div class="kpi-title">Open Draft Pipeline</div>
            <div class="kpi-value amber">${{ number_format($draftValue, 0) }}</div>
        </div>
        <div class="kpi-sub">
            <span>{{ $draftQuotesCount }} Active Draft Quotations</span>
            <span>In Negotiation</span>
        </div>
    </div>

    <div class="kpi-card" style="border-top: 4px solid #2563eb;">
        <div>
            <div class="kpi-title">Average Quotation Size</div>
            <div class="kpi-value">${{ number_format($averageQuoteValue, 0) }}</div>
        </div>
        <div class="kpi-sub">
            <span>Per sweeper proposal package</span>
            <span>Across all contracts</span>
        </div>
    </div>
</div>

<div class="dashboard-grid-2">
    <!-- Sales Representative Leaderboard -->
    <div class="card">
        <div class="card-header-flex">
            <div class="card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
                Sales Representative Leaderboard (Volume &amp; Value)
            </div>
            <a href="{{ route('manager.reports.index') }}" class="btn-secondary" style="font-size: 12px; padding: 4px 10px;">Full Breakdown &rarr;</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">Rank</th>
                        <th>Sales Representative</th>
                        <th>Territory</th>
                        <th style="text-align: center;">Total Quotes</th>
                        <th style="text-align: right;">Finalized ($)</th>
                        <th style="text-align: right;">Total Quoted ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesReps as $index => $rep)
                        @php
                            $rank = $index + 1;
                            $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : ''));
                        @endphp
                        <tr>
                            <td><span class="rank-badge {{ $rankClass }}">{{ $rank }}</span></td>
                            <td>
                                <strong>{{ $rep['name'] }}</strong>
                                <span style="font-size: 11px; color: #64748b; margin-left: 4px;">({{ $rep['rep_code'] ?? 'REP' }})</span>
                            </td>
                            <td><span style="font-size: 12px; color: #475569;">{{ $rep['territory'] ?? 'Assigned' }}</span></td>
                            <td style="text-align: center;">
                                <strong>{{ $rep['total_quotes'] }}</strong>
                                <small style="display: block; font-size: 11px; color: #94a3b8;">({{ $rep['finalized_quotes'] }} firm)</small>
                            </td>
                            <td style="text-align: right; color: #059669; font-weight: 600;">
                                ${{ number_format($rep['finalized_value'], 0) }}
                            </td>
                            <td style="text-align: right; font-weight: 700;">
                                ${{ number_format($rep['total_value'], 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No sales rep data recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Trend & Top Quoting States -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Month-over-Month Trend -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header-flex">
                <div class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    Month-over-Month Quoted Trend
                </div>
            </div>

            @if ($monthlyTrend->count() > 0)
                @foreach ($monthlyTrend as $m)
                    <div class="trend-item">
                        <div>
                            <strong>{{ $m->month_label }}</strong>
                            <div style="font-size: 11px; color: #94a3b8;">{{ $m->quote_count }} proposals issued</div>
                        </div>
                        <div style="text-align: right;">
                            <strong style="color: var(--text-dark);">${{ number_format($m->total_value, 0) }}</strong>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: var(--text-muted); font-size: 13px;">No monthly history available yet.</p>
            @endif
        </div>

        <!-- Top States Quoting -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header-flex">
                <div class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                    Quotation Totals by State / Region
                </div>
            </div>

            @if ($stateBreakdown->count() > 0)
                @foreach ($stateBreakdown as $sb)
                    <div class="trend-item">
                        <div>
                            <strong>{{ $sb->state_name }}</strong>
                            <div style="font-size: 11px; color: #94a3b8;">{{ $sb->quotes_count }} quotations</div>
                        </div>
                        <div style="text-align: right;">
                            <strong style="color: #059669;">${{ number_format($sb->total_amount, 0) }}</strong>
                        </div>
                    </div>
                @endforeach
            @else
                <p style="color: var(--text-muted); font-size: 13px;">No state data recorded.</p>
            @endif
        </div>
    </div>
</div>

<!-- Team Recent Quotations Stream -->
<div class="card">
    <div class="card-header-flex">
        <div class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            Recent Company Quotations (Live Feed)
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('manager.reports.export') }}" class="btn-success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export All to Excel
            </a>
            <a href="{{ route('manager.quotes.index') }}" class="btn-secondary">View All Quotations &rarr;</a>
        </div>
    </div>

    @if ($recentQuotes->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Sales Rep</th>
                        <th>Customer Company</th>
                        <th>Model</th>
                        <th>Pricing Basis</th>
                        <th>Status</th>
                        <th style="text-align: right;">Quotation Total</th>
                        <th style="text-align: right; width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentQuotes as $q)
                        @php
                            $snap = $q->latestVersion?->snapshot;
                            $stClass = $q->status === 'Finalized' ? 'badge-active' : ($q->status === 'Draft' ? 'badge-draft' : 'badge-inactive');
                        @endphp
                        <tr>
                            <td><strong>Q-{{ $q->quote_number }}</strong></td>
                            <td>{{ $q->user->name }}</td>
                            <td>{{ $snap?->customer_company ?? 'Draft Opportunity' }}</td>
                            <td><code>{{ $snap?->model_code ?? 'MT4H' }}</code></td>
                            <td><span style="font-size: 12px; color: #475569;">{{ $snap?->pricing_basis ?? 'MSRP' }}</span></td>
                            <td><span class="badge {{ $stClass }}">{{ $q->status }}</span></td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                ${{ number_format($snap?->total ?? 0, 2) }}
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('sales.quotes.show', $q) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px;">Inspect</a>
                                @if ($q->status === 'Finalized')
                                    <a href="{{ route('sales.quotes.pdf', $q) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px;" target="_blank">PDF</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 13px;">No quotations have been generated yet.</p>
    @endif
</div>
@endsection
