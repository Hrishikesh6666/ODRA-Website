@extends('layouts.manager')

@section('title', 'All Company Quotations')
@section('header-title', 'Team Quotations Oversight')

@section('content')
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form action="{{ route('manager.quotes.index') }}" method="GET" style="display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <!-- Search -->
        <div style="flex: 1; min-width: 260px;">
            <input type="text" name="search" class="form-control" placeholder="Search by Quote #, Customer, or Rep Name..." value="{{ $search }}">
        </div>

        <!-- Status Filter Pills -->
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('manager.quotes.index') }}" class="btn-secondary" style="{{ empty($currentStatus) ? 'background: #0f172a; color: white;' : '' }}">All Quotes</a>
            <a href="{{ route('manager.quotes.index', ['status' => 'Finalized']) }}" class="btn-secondary" style="{{ $currentStatus === 'Finalized' ? 'background: #059669; color: white;' : '' }}">Finalized</a>
            <a href="{{ route('manager.quotes.index', ['status' => 'Draft']) }}" class="btn-secondary" style="{{ $currentStatus === 'Draft' ? 'background: #d97706; color: white;' : '' }}">Drafts</a>
            <a href="{{ route('manager.quotes.index', ['status' => 'Revised']) }}" class="btn-secondary" style="{{ $currentStatus === 'Revised' ? 'background: #2563eb; color: white;' : '' }}">Revised</a>
        </div>

        <button type="submit" class="btn-primary" style="padding: 8px 16px;">Search</button>
    </form>
</div>

<div class="card">
    @if ($quotes->count() > 0)
        <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 280px); min-height: 250px; -webkit-overflow-scrolling: touch;">
            <table class="data-table">
                <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                    <tr>
                        <th>Quote #</th>
                        <th>Created</th>
                        <th>Sales Rep</th>
                        <th>Customer Company</th>
                        <th>Model</th>
                        <th>Pricing Basis</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total Quoted ($)</th>
                        <th style="text-align: right; width: 200px;">Actions</th>
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
                                <span class="badge" style="font-size: 10px; padding: 2px 5px; background: #f1f5f9; color: #475569; margin-left: 4px;">Rev {{ $versionNum }}</span>
                            </td>
                            <td>{{ $q->created_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $q->user->name }}</strong>
                                <span style="font-size: 11px; color: #64748b; margin-left: 2px;">({{ $q->user->rep_code }})</span>
                            </td>
                            <td>{{ $snap?->customer_company ?? 'Draft Deal' }}</td>
                            <td><code>{{ $snap?->model_code ?? 'MT4H' }}</code></td>
                            <td><span style="font-size: 12px; color: #475569;">{{ $snap?->pricing_basis ?? 'MSRP' }}</span></td>
                            <td><span class="badge {{ $stClass }}">{{ $versionStatus }}</span></td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                ${{ number_format($snap?->total ?? 0, 2) }}
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 4px; justify-content: flex-end;">
                                    <button type="button" class="btn-secondary" style="padding: 4px 8px; font-size: 11px; cursor: pointer;" onclick="openRevisionHistory({{ $q->id }})" title="View Revision History">History</button>
                                    <a href="{{ route('sales.quotes.show', ['quote' => $q, 'version' => $versionNum]) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" title="Inspect Quotation">Inspect</a>
                                    <a href="{{ route('sales.quotes.show', ['quote' => $q, 'version' => $versionNum, 'print' => 1]) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" target="_blank" title="Print Quotation">Print</a>
                                    @if ($versionStatus === 'Finalized')
                                        <a href="{{ route('sales.quotes.pdf', ['quote' => $q, 'version' => $versionNum]) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px; background: #ecfdf5; color: #065f46; border-color: #a7f3d0; text-decoration: none;" target="_blank" download="ODRA_Quotation_{{ $q->quote_number }}_Rev{{ $versionNum }}.pdf" title="Download Finalized PDF">Download</a>
                                    @else
                                        <a href="{{ route('sales.quotes.show', $q) }}" class="btn-secondary" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" title="Open in Quote Builder">Open</a>
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
            <div style="font-weight: 600; font-size: 16px; color: var(--text-dark);">No quotations found</div>
            <p style="font-size: 13px; margin-top: 4px;">No quotations match your search criteria.</p>
        </div>
    @endif
</div>
@endsection
