@extends('layouts.admin')

@section('title', 'Models & Pricing')
@section('header-title', 'Sweeper Models & Master Pricing')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <p style="color: var(--text-muted); margin: 0; font-size: 14px;">
        Manage base equipment models, chassis configurations, and commercial &amp; cooperative pricing tiers.
    </p>
    <a href="{{ route('admin.models.create') }}" class="btn-primary">
        + Add New Model
    </a>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Model Code</th>
                <th>Category</th>
                <th>MSRP (Commercial)</th>
                <th>Sourcewell List</th>
                <th>Sourcewell Net</th>
                <th>Configurations</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($models as $m)
                @php
                    $p = $m->prices->first();
                @endphp
                <tr>
                    <td><strong>{{ $m->code }}</strong></td>
                    <td>{{ $m->category }}</td>
                    <td><strong>${{ number_format($p?->msrp ?? 0, 0) }}</strong></td>
                    <td>${{ number_format($p?->sw_list ?? 0, 0) }}</td>
                    <td><strong>${{ number_format($p?->sw_net ?? 0, 0) }}</strong></td>
                    <td>{{ $m->configurations->count() }} chassis configs</td>
                    <td>
                        <span class="badge {{ $m->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $m->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.models.edit', $m) }}" class="btn-secondary">Edit &amp; Prices</a>
                            <form action="{{ route('admin.models.toggle', $m) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-secondary" style="color: {{ $m->is_active ? '#991b1b' : '#166534' }};">
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
@endsection
