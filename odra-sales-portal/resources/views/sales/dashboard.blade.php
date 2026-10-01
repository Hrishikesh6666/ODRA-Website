@extends('layouts.app')

@section('title', 'Sales Dashboard')

@push('styles')
<style>
    .welcome-banner {
        background: linear-gradient(135deg, #1b242e 0%, #2d3748 100%);
        color: white;
        padding: 28px 32px;
        border-radius: 10px;
        margin-bottom: 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .welcome-title h1 {
        margin: 0 0 8px 0;
        font-size: 24px;
        font-weight: 700;
    }
    .welcome-title p {
        margin: 0;
        color: #94a3b8;
        font-size: 14px;
    }
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }
    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    .stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .stat-num {
        font-size: 30px;
        font-weight: 700;
        color: var(--text-dark);
    }
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }
    .card {
        background: white;
        padding: 24px;
        border-radius: 8px;
        border: 1px solid var(--border);
        margin-bottom: 24px;
    }
    .card-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 16px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .data-table th {
        background: #f8fafc;
        padding: 10px 12px;
        font-weight: 600;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    .data-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border);
    }
    .btn-action {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--border);
        background: white;
        color: var(--text-dark);
    }
    .btn-action:hover { background: #f1f5f9; }
    .badge-finalized { background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
    .badge-draft { background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
    .btn-create-quote {
        background: var(--red);
        color: white;
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        transition: background 0.15s ease;
    }
    .btn-create-quote:hover { background: var(--red-hover); }
</style>
@endpush

@section('content')
<div class="welcome-banner">
    <div class="welcome-title">
        <h1>Welcome back, {{ $user->name }}</h1>
        <p>Representative Code: <strong>{{ $user->rep_code ?? 'N/A' }}</strong> &middot; Territory: {{ $user->territory ?? 'Assigned Regions' }}</p>
    </div>
    <a href="{{ route('sales.quotes.create') }}" class="btn-create-quote">
        + Launch Quote Builder
    </a>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-box">
        <div class="stat-label">Total Quotations</div>
        <div class="stat-num">{{ $totalQuotes }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Active Drafts</div>
        <div class="stat-num" style="color: #d97706;">{{ $draftQuotes }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Finalized Quotations</div>
        <div class="stat-num" style="color: #16a34a;">{{ $finalizedQuotes }}</div>
    </div>
    <div class="stat-box">
        <div class="stat-label">Product Lines Available</div>
        <div class="stat-num">{{ $models->count() }} Models</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Left Column: Recent Quotes -->
    <div class="card">
        <div class="card-title">
            <span>Recent Quotations</span>
            <a href="{{ route('sales.quotes.index') }}" style="font-size: 13px; color: var(--red); text-decoration: none; font-weight: 600;">View All Quotations &rarr;</a>
        </div>

        @if ($recentQuotes->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Customer</th>
                        <th>Model</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentQuotes as $q)
                        @php
                            $snap = $q->latestVersion?->snapshot;
                            $stClass = $q->status === 'Finalized' ? 'badge-finalized' : 'badge-draft';
                        @endphp
                        <tr>
                            <td><strong>Q-{{ $q->quote_number }}</strong></td>
                            <td>{{ $snap?->customer_company ?? 'Untitled Customer' }}</td>
                            <td>{{ $snap?->model_code ?? 'MT4H' }}</td>
                            <td><span class="{{ $stClass }}">{{ $q->status }}</span></td>
                            <td><strong>${{ number_format($snap?->total ?? 0, 0) }}</strong></td>
                            <td>
                                <a href="{{ route('sales.quotes.show', $q) }}" class="btn-action">
                                    {{ $q->status === 'Finalized' ? 'View' : 'Open' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <p>You haven't generated any quotations yet.</p>
                <a href="{{ route('sales.quotes.create') }}" class="btn-action" style="background: var(--red); color: white; border: none; padding: 8px 16px;">
                    Create Your First Quotation
                </a>
            </div>
        @endif
    </div>

    <!-- Right Column: Quick Reference & Territories -->
    <div>
        <div class="card">
            <div class="card-title">
                <span>My Assigned Territories</span>
            </div>
            @if ($repTerritories->count() > 0)
                <ul style="padding-left: 20px; margin: 0; font-size: 13px; line-height: 1.8;">
                    @foreach ($repTerritories as $t)
                        <li>
                            <strong>{{ $t->label }}</strong> (<code>{{ $t->code }}</code>)
                        </li>
                    @endforeach
                </ul>
            @else
                <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                    All 50 US States &amp; municipal territories available in the Quote Builder.
                </p>
            @endif
        </div>

        <div class="card">
            <div class="card-title">
                <span>Active Product Masters</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach ($models as $m)
                    <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border);">
                        <strong>{{ $m->code }} Sweeper</strong>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ $m->category }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
