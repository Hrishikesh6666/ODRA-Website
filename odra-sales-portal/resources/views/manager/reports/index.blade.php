@extends('layouts.manager')

@section('title', 'Quotation Reports & Excel Export')
@section('header-title', 'Commercial Reporting & Excel Export')

@push('styles')
<style>
    .filter-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        background: white;
        color: var(--text-dark);
    }
    .form-control:focus {
        outline: 2px solid var(--red);
        outline-offset: 1px;
    }

    /* Aggregate Metric Ribbon */
    .summary-ribbon {
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }
    .metric-pill {
        display: flex;
        flex-direction: column;
    }
    .metric-pill-label {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
    }
    .metric-pill-value {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-dark);
    }
    .metric-pill-value.green { color: #059669; }
    .metric-pill-value.amber { color: #d97706; }
</style>
@endpush

@section('content')
<!-- Filter Control Form -->
<div class="filter-card">
    <form action="{{ route('manager.reports.index') }}" method="GET">
        <div class="filter-grid">
            <!-- Sales Rep Filter -->
            <div class="form-group">
                <label>Sales Representative</label>
                <select name="rep_id" class="form-control">
                    <option value="">-- All Representatives --</option>
                    @foreach ($repsList as $r)
                        <option value="{{ $r->id }}" {{ ($filters['rep_id'] ?? '') == $r->id ? 'selected' : '' }}>
                            {{ $r->name }} ({{ $r->rep_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Destination State / Territory Filter -->
            <div class="form-group">
                <label>State / Territory</label>
                <select name="state" class="form-control">
                    <option value="">-- All States --</option>
                    @foreach ($statesList as $st)
                        @php
                            $stateVal = is_object($st) ? ($st->state_name ?? '') : (string)$st;
                        @endphp
                        <option value="{{ $stateVal }}" {{ ($filters['state'] ?? '') == $stateVal ? 'selected' : '' }}>
                            {{ $stateVal }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="form-group">
                <label>Quotation Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All Statuses --</option>
                    <option value="Finalized" {{ ($filters['status'] ?? '') == 'Finalized' ? 'selected' : '' }}>Finalized (Firm)</option>
                    <option value="Draft" {{ ($filters['status'] ?? '') == 'Draft' ? 'selected' : '' }}>Draft (Open Pipeline)</option>
                    <option value="Revised" {{ ($filters['status'] ?? '') == 'Revised' ? 'selected' : '' }}>Revised</option>
                </select>
            </div>

            <!-- Pricing Basis Filter -->
            <div class="form-group">
                <label>Pricing Contract</label>
                <select name="pricing_basis" class="form-control">
                    <option value="">-- All Contracts --</option>
                    @foreach ($pricingBases as $pb)
                        <option value="{{ $pb->name }}" {{ ($filters['pricing_basis'] ?? '') == $pb->name ? 'selected' : '' }}>
                            {{ $pb->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range From -->
            <div class="form-group">
                <label>Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <!-- Date Range To -->
            <div class="form-group">
                <label>Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px;">
            <a href="{{ route('manager.reports.index') }}" class="btn-secondary" style="font-size: 12px;">Reset All Filters</a>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary">Apply Filters</button>
            </div>
        </div>
    </form>
</div>

<!-- Aggregated Summary Ribbon -->
<div class="summary-ribbon">
    <div style="display: flex; gap: 32px; flex-wrap: wrap;">
        <div class="metric-pill">
            <span class="metric-pill-label">Matching Quotes</span>
            <span class="metric-pill-value">{{ number_format($stats->total_count ?? 0) }}</span>
        </div>
        <div class="metric-pill">
            <span class="metric-pill-label">Total Filtered Value</span>
            <span class="metric-pill-value">${{ number_format($stats->total_value ?? 0, 0) }}</span>
        </div>
        <div class="metric-pill">
            <span class="metric-pill-label">Average Deal Size</span>
            <span class="metric-pill-value">${{ number_format($stats->avg_value ?? 0, 0) }}</span>
        </div>
        <div class="metric-pill">
            <span class="metric-pill-label">Finalized Amount</span>
            <span class="metric-pill-value green">${{ number_format($stats->finalized_value ?? 0, 0) }}</span>
        </div>
        <div class="metric-pill">
            <span class="metric-pill-label">Draft Pipeline</span>
            <span class="metric-pill-value amber">${{ number_format($stats->draft_value ?? 0, 0) }}</span>
        </div>
    </div>

    <!-- One-Click Excel / CSV Export Button -->
    <a href="{{ route('manager.reports.export', request()->query()) }}" class="btn-success" style="padding: 10px 20px; font-size: 14px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Download Excel / CSV
    </a>
</div>

<!-- Filtered Quotes Table -->
<div class="card">
    <div style="margin-bottom: 16px; font-weight: 700; font-size: 15px;">
        Filtered Quotation Details
    </div>

    @if ($quotes->count() > 0)
        <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 350px); min-height: 250px; -webkit-overflow-scrolling: touch;">
            <table class="data-table">
                <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                    <tr>
                        <th>Quote #</th>
                        <th>Date</th>
                        <th>Sales Rep</th>
                        <th>Customer Company</th>
                        <th>State</th>
                        <th>Model</th>
                        <th>Pricing Basis</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total Amount</th>
                        <th style="text-align: right; width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotes as $q)
                        @php
                            $dispVer = $q->displayVersion();
                            $snap = $dispVer?->snapshot ?? $q->latestVersion?->snapshot;
                            $versionNum = $dispVer?->version_number ?? $q->current_version;
                            $versionStatus = $dispVer?->status ?? $q->status;
                            $stClass = $versionStatus === 'Finalized' ? 'badge-active' : ($versionStatus === 'Draft' ? 'badge-draft' : 'badge-inactive');
                        @endphp
                        <tr>
                            <td>
                                <strong>Q-{{ $q->quote_number }}</strong>
                                <span class="badge" style="font-size: 10px; padding: 2px 5px; background: #f1f5f9; color: #475569; margin-left: 3px;">Rev {{ $versionNum }}</span>
                            </td>
                            <td>{{ $q->created_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $q->user->name }}</strong>
                                <span style="font-size: 11px; color: #64748b; margin-left: 3px;">({{ $q->user->rep_code }})</span>
                            </td>
                            <td>{{ $snap?->customer_company ?? 'Draft Deal' }}</td>
                            <td>{{ $snap?->rep_territory ?? '&mdash;' }}</td>
                            <td><code>{{ $snap?->model_code ?? 'MT4H' }}</code></td>
                            <td><span style="font-size: 12px; color: #475569;">{{ $snap?->pricing_basis ?? 'MSRP' }}</span></td>
                            <td><span class="badge {{ $stClass }}">{{ $versionStatus }}</span></td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                ${{ number_format($snap?->total ?? 0, 2) }}
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px; justify-content: flex-end;">
                                    <button type="button" class="btn-secondary" style="padding: 4px 8px; font-size: 11px; cursor: pointer;" onclick="openRevisionHistory({{ $q->id }})" title="View Revision History">History</button>
                                    <a href="{{ route('sales.quotes.show', ['quote' => $q, 'version' => $versionNum]) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px;">Inspect</a>
                                    @if ($versionStatus === 'Finalized')
                                        <a href="{{ route('sales.quotes.pdf', ['quote' => $q, 'version' => $versionNum]) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px;" target="_blank">PDF</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $quotes->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 48px 24px; color: var(--text-muted);">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.5;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <div style="font-weight: 600; font-size: 16px; color: var(--text-dark);">No quotations match your filters</div>
            <p style="font-size: 13px; margin-top: 4px;">Try broadening your date range, state, or search criteria.</p>
        </div>
    @endif
</div>
@endsection
