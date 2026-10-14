@extends('layouts.admin')

@section('title', 'Models & Pricing')
@section('header-title', 'Sweeper Models & Master Pricing')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <p style="color: var(--text-muted); margin: 0; font-size: 14px;">
        Manage base equipment models, chassis configurations, and commercial &amp; cooperative pricing tiers.
    </p>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.quotation-settings.index') }}" class="btn-secondary">
            &#9998; Edit Quotation Page &amp; Pricing Basis
        </a>
        <a href="{{ route('admin.models.create') }}" class="btn-primary">
            + Add New Model
        </a>
    </div>
</div>

<div class="card">
    <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: calc(100vh - 280px); min-height: 250px; -webkit-overflow-scrolling: touch;">
        <table class="data-table">
            <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                <tr>
                    <th style="min-width: 100px;">Model Code</th>
                    <th style="min-width: 320px;">Quotation Body Description (Shared)</th>
                    <th>MSRP (Commercial)</th>
                    <th>Sourcewell List</th>
                    <th>Sourcewell Net</th>
                    <th>Configurations</th>
                    <th>Status</th>
                    <th style="text-align: right; min-width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($models as $m)
                    @php
                        $p = $m->prices->first();
                    @endphp
                    <tr>
                        <td>
                            <strong style="font-size: 15px; color: #0f172a;">{{ $m->code }}</strong>
                            <div style="font-size: 11px; color: #64748b; margin-top: 3px;">{{ $m->category }}</div>
                        </td>
                        <td>
                            <div style="font-size: 12.5px; color: #1e293b; line-height: 1.45; max-height: 58px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;" title="{{ $m->description }}">
                                {{ $m->description }}
                            </div>
                            <div style="font-size: 10.5px; color: #ac2529; font-weight: 700; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                <span>&bull; Rendered in bold on all Sales Rep Quotations</span>
                            </div>
                        </td>
                        <td><strong>${{ number_format($p?->msrp ?? 0, 0) }}</strong></td>
                        <td>${{ number_format($p?->sw_list ?? 0, 0) }}</td>
                        <td><strong>${{ number_format($p?->sw_net ?? 0, 0) }}</strong></td>
                        <td>{{ $m->configurations->count() }} chassis configs</td>
                        <td>
                            <span class="badge {{ $m->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $m->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('admin.quotation-settings.index', ['scope' => $m->code]) }}" class="btn-secondary" style="padding: 6px 10px; font-size: 12px; text-decoration: none;" title="Edit Quotation Specs, Standard Features &amp; Terms for {{ $m->code }}">
                                    Quotation Specs &rarr;
                                </a>
                                <a href="{{ route('admin.models.edit', $m) }}" class="btn-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">
                                    Edit Prices &amp; Chassis
                                </a>
                                <form action="{{ route('admin.models.toggle', $m) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-secondary" style="padding: 6px 8px; font-size: 12px; color: {{ $m->is_active ? '#991b1b' : '#166534' }};">
                                        {{ $m->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
