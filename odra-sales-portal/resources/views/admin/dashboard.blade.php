@extends('layouts.admin')

@section('title', 'HQ Dashboard')
@section('header-title', 'Headquarters Overview')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .stat-card-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .stat-card-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    .stat-card-desc {
        font-size: 12px;
        color: #64748b;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .section-heading {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .audit-item {
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .audit-action {
        font-weight: 600;
        color: var(--text-dark);
    }
    .audit-meta {
        font-size: 11px;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-card-title">Active Sweeper Models</div>
            <div class="stat-card-value">{{ $activeModelsCount }}</div>
        </div>
        <div class="stat-card-desc">MT4H &amp; MS5H Commercial line</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-title">Catalog Enhancements</div>
            <div class="stat-card-value">{{ $enhancementsCount }}</div>
        </div>
        <div class="stat-card-desc">Model-specific options &amp; parts</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-title">Active Sales Representatives</div>
            <div class="stat-card-value">{{ $salesRepsCount }}</div>
        </div>
        <div class="stat-card-desc">Authorized sales team accounts</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-title">Total Quotations</div>
            <div class="stat-card-value">{{ $totalQuotesCount }}</div>
        </div>
        <div class="stat-card-desc">Draft &amp; finalized proposals</div>
    </div>
</div>

<div class="grid-2">
    <!-- Recent Quotations across company -->
    <div class="card">
        <div class="section-heading">
            <span>Recent Quotations</span>
            <a href="{{ route('sales.quotes.index') }}" style="font-size: 12px; color: var(--red); text-decoration: none; font-weight: 600;">View all &rarr;</a>
        </div>
        @if ($recentQuotes->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Rep</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentQuotes as $q)
                        @php
                            $snap = $q->latestVersion?->snapshot;
                            $stClass = $q->status === 'Finalized' ? 'badge-active' : 'badge-inactive';
                        @endphp
                        <tr>
                            <td><strong>Q-{{ $q->quote_number }}</strong></td>
                            <td>{{ $q->user->name }}</td>
                            <td>{{ $snap?->customer_company ?? 'Untitled' }}</td>
                            <td><span class="badge {{ $stClass }}">{{ $q->status }}</span></td>
                            <td><strong>${{ number_format($snap?->total ?? 0, 0) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: var(--text-muted); font-size: 13px;">No quotations generated yet.</p>
        @endif
    </div>

    <!-- Live Security Audit Stream -->
    <div class="card">
        <div class="section-heading">
            <span>Security Audit Trail</span>
            <a href="{{ route('admin.audit.index') }}" style="font-size: 12px; color: var(--red); text-decoration: none; font-weight: 600;">Full log &rarr;</a>
        </div>
        @if ($recentAuditLogs->count() > 0)
            @foreach ($recentAuditLogs as $log)
                <div class="audit-item">
                    <div>
                        <div class="audit-action">{{ $log->action }}</div>
                        <div style="font-size: 12px; color: #64748b;">{{ $log->notes ?? ($log->user?->name ?? 'System') }}</div>
                    </div>
                    <div class="audit-meta">
                        {{ $log->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        @else
            <p style="color: var(--text-muted); font-size: 13px;">No audit events recorded.</p>
        @endif
    </div>
</div>
@endsection
