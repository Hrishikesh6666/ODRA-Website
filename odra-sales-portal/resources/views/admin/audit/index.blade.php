@extends('layouts.admin')

@section('title', 'Security Audit Trail')
@section('header-title', 'Enterprise Security & Activity Audit Trail')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.audit.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <input 
            type="text" 
            name="search" 
            class="form-control" 
            style="width: 240px;" 
            placeholder="Search action, IP, notes..." 
            value="{{ request('search') }}"
        >
        <select name="action" class="form-control" style="width: 200px;" onchange="this.form.submit()">
            <option value="">All Action Types</option>
            @foreach ($actions as $act)
                <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $act }}</option>
            @endforeach
        </select>
        <select name="user_id" class="form-control" style="width: 180px;" onchange="this.form.submit()">
            <option value="">All Users</option>
            @foreach ($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
        @if (request()->hasAny(['search', 'action', 'user_id']))
            <a href="{{ route('admin.audit.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Action</th>
                <th>User / Representative</th>
                <th>IP Address</th>
                <th>Session / Device</th>
                <th>Notes / Event Payload</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td style="white-space: nowrap; font-size: 12px;">
                        <strong>{{ $log->created_at->format('Y-m-d H:i:s') }}</strong>
                        <div style="color: #94a3b8; font-size: 11px;">{{ $log->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        <code style="background: #f1f5f9; padding: 3px 6px; border-radius: 4px; font-weight: bold; color: var(--red);">
                            {{ $log->action }}
                        </code>
                    </td>
                    <td>
                        <strong>{{ $log->user?->name ?? 'System / Anonymous' }}</strong>
                        @if ($log->user)
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $log->user->email }}</div>
                        @endif
                    </td>
                    <td><span style="font-size: 12px; font-family: monospace;">{{ $log->ip_address ?? 'N/A' }}</span></td>
                    <td style="font-size: 11px; color: var(--text-muted); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $log->user_agent }}
                    </td>
                    <td>
                        <div style="font-size: 13px;">{{ $log->notes }}</div>
                        @if ($log->new_value)
                            <details style="margin-top: 4px; font-size: 11px; color: #475569;">
                                <summary style="cursor: pointer; color: var(--red);">View JSON payload</summary>
                                <pre style="background: #f8fafc; padding: 8px; border-radius: 4px; overflow-x: auto;">{{ json_encode($log->new_value, JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        No audit records found matching criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding: 16px;">
        {{ $logs->links() }}
    </div>
</div>
@endsection
