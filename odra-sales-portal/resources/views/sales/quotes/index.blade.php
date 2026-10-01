@extends('layouts.app')

@section('title', 'My Quotations')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-title h1 {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 6px 0;
    }
    .page-title p {
        color: var(--text-muted);
        margin: 0;
        font-size: 14px;
    }
    .btn-primary {
        background: var(--red);
        color: white;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .btn-primary:hover {
        background: var(--red-hover);
    }
    .filters-card {
        background: white;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid var(--border);
        margin-bottom: 20px;
        display: flex;
        gap: 16px;
        align-items: center;
    }
    .search-input {
        flex: 1;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
    }
    .select-input {
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        background: white;
    }
    .table-card {
        background: white;
        border-radius: 8px;
        border: 1px solid var(--border);
        overflow: hidden;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        text-align: left;
    }
    .data-table th {
        background: #f8fafc;
        padding: 12px 16px;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid var(--border);
    }
    .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .data-table tr:hover {
        background: #f8fafc;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-draft { background: #fef3c7; color: #92400e; }
    .badge-finalized { background: #dcfce7; color: #166534; }
    .badge-revised { background: #e0f2fe; color: #075985; }
    .actions-cell {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .btn-action {
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--border);
        background: white;
        color: var(--text-dark);
        cursor: pointer;
    }
    .btn-action:hover {
        background: #f1f5f9;
    }
    .btn-action.btn-pdf {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>My Quotations</h1>
        <p>Manage your saved commercial and cooperative sweeper quotations.</p>
    </div>
    <a href="{{ route('sales.quotes.create') }}" class="btn-primary">
        + Create New Quotation
    </a>
</div>

<form method="GET" action="{{ route('sales.quotes.index') }}" class="filters-card">
    <input 
        type="text" 
        name="search" 
        class="search-input" 
        placeholder="Search by quote #, customer name, or model..." 
        value="{{ request('search') }}"
    >
    <select name="status" class="select-input" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
        <option value="Finalized" {{ request('status') == 'Finalized' ? 'selected' : '' }}>Finalized</option>
        <option value="Revised" {{ request('status') == 'Revised' ? 'selected' : '' }}>Revised</option>
    </select>
    <button type="submit" class="btn-action" style="padding: 9px 16px;">Filter</button>
    @if (request()->hasAny(['search', 'status']))
        <a href="{{ route('sales.quotes.index') }}" class="btn-action" style="padding: 9px 16px;">Clear</a>
    @endif
</form>

<div class="table-card">
    @if ($quotes->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quote #</th>
                    <th>Customer / Account</th>
                    <th>Model</th>
                    <th>Version</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotes as $quote)
                    @php
                        $snapshot = $quote->latestVersion?->snapshot;
                        $statusClass = match($quote->status) {
                            'Finalized' => 'badge-finalized',
                            'Revised' => 'badge-revised',
                            default => 'badge-draft',
                        };
                    @endphp
                    <tr>
                        <td><strong>Q-{{ $quote->quote_number }}</strong></td>
                        <td>
                            <strong>{{ $snapshot?->customer_company ?? 'Untitled Customer' }}</strong>
                            @if ($snapshot?->customer_contact)
                                <div style="font-size: 12px; color: var(--text-muted);">Attn: {{ $snapshot->customer_contact }}</div>
                            @endif
                        </td>
                        <td>{{ $snapshot?->model_code ?? 'MT4H' }}</td>
                        <td>v{{ $quote->current_version }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $quote->status }}</span></td>
                        <td><strong>${{ number_format($snapshot?->total ?? 0, 0) }}</strong></td>
                        <td>{{ $quote->updated_at->format('M d, Y') }}</td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('sales.quotes.show', $quote) }}" class="btn-action">
                                    {{ $quote->status === 'Finalized' ? 'View Quote' : 'Edit Draft' }}
                                </a>
                                @if ($snapshot?->pdf_path)
                                    <a href="{{ route('sales.quotes.pdf', $quote) }}" class="btn-action btn-pdf" target="_blank">
                                        Download PDF
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 16px;">
            {{ $quotes->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>No quotations found</h3>
            <p>You haven't created any quotations matching your criteria yet.</p>
            <a href="{{ route('sales.quotes.create') }}" class="btn-primary" style="margin-top: 12px;">
                Create Your First Quotation
            </a>
        </div>
    @endif
</div>
@endsection
