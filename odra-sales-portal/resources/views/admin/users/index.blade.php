@extends('layouts.admin')

@section('title', 'Users & Access Control')
@section('header-title', 'User Accounts & Access Control')

@push('styles')
<style>
/* Role Top Navigation Bar */
.user-role-nav-container {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 8px 12px;
    margin-bottom: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.user-role-nav-bar {
    display: flex;
    gap: 8px;
    align-items: center;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding: 2px 0;
}
.role-nav-tab {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: transparent;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    text-decoration: none;
    user-select: none;
}
.role-nav-tab:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.role-nav-tab .role-nav-badge {
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11.5px;
    font-weight: 700;
    transition: all 0.18s ease;
}
.role-nav-tab .badge-admins { background: #fee2e2; color: #991b1b; }
.role-nav-tab .badge-managers { background: #dbeafe; color: #1e40af; }
.role-nav-tab .badge-sales { background: #d1fae5; color: #065f46; }
.role-nav-tab .badge-all { background: #f1f5f9; color: #475569; }

/* Active States */
.role-nav-tab.active {
    color: white !important;
}
.role-nav-tab.active[data-tab="admins"] {
    background: var(--red);
    box-shadow: 0 2px 8px rgba(172,37,41,0.28);
}
.role-nav-tab.active[data-tab="managers"] {
    background: #2563eb;
    box-shadow: 0 2px 8px rgba(37,99,235,0.28);
}
.role-nav-tab.active[data-tab="sales"] {
    background: #059669;
    box-shadow: 0 2px 8px rgba(5,150,105,0.28);
}
.role-nav-tab.active[data-tab="all"] {
    background: #1e293b;
    box-shadow: 0 2px 8px rgba(30,41,59,0.28);
}
.role-nav-tab.active .role-nav-badge {
    background: rgba(255,255,255,0.22) !important;
    color: white !important;
}

/* Page View Containers (Simulated Sub-pages) */
.role-page-view {
    animation: fadeIn 0.2s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.role-header-banner {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 16px;
}
.role-header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}
.role-header-title h3 {
    margin: 0;
    font-size: 19px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
}
.role-header-desc {
    margin: 0;
    font-size: 13.5px;
    color: var(--text-muted);
    line-height: 1.5;
    max-width: 900px;
}

.table-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    background: #f8fafc;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 12px;
}

.search-input-wrap {
    position: relative;
    flex: 1;
    max-width: 360px;
}
.search-input-wrap input {
    padding-left: 36px;
}
.search-input-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

/* Overview Summary Cards */
.overview-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}
.overview-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 22px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.overview-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
}
</style>
@endpush

@section('content')
<!-- SUB-NAVIGATION BAR (SWITCH ROLES LIKE DIFFERENT PAGES ON SAME PAGE) -->
<div class="user-role-nav-container">
    <nav class="user-role-nav-bar" aria-label="Role Directory Navigation">
        <!-- Tab 1: Technical Administrators -->
        <button type="button" class="role-nav-tab active" data-tab="admins" onclick="switchUserTab('admins')">
            <span style="font-size: 16px;">🛡️</span>
            <span>Technical Administrators</span>
            <span class="role-nav-badge badge-admins">{{ count($admins) }} Admins</span>
        </button>

        <!-- Tab 2: Commercial Managers -->
        <button type="button" class="role-nav-tab" data-tab="managers" onclick="switchUserTab('managers')">
            <span style="font-size: 16px;">👔</span>
            <span>Commercial Managers</span>
            <span class="role-nav-badge badge-managers">{{ count($managers) }} Managers</span>
        </button>

        <!-- Tab 3: Sales Representatives -->
        <button type="button" class="role-nav-tab" data-tab="sales" onclick="switchUserTab('sales')">
            <span style="font-size: 16px;">💼</span>
            <span>Sales Representatives</span>
            <span class="role-nav-badge badge-sales">{{ $users->total() }} Reps</span>
        </button>

        <!-- Tab 4: All Accounts Overview -->
        <button type="button" class="role-nav-tab" data-tab="all" onclick="switchUserTab('all')">
            <span style="font-size: 16px;">👥</span>
            <span>All Accounts Overview</span>
            <span class="role-nav-badge badge-all">{{ count($admins) + count($managers) + $users->total() }} Total</span>
        </button>
    </nav>
</div>


<!-- ========================================================================= -->
<!-- SUB-PAGE 1: TECHNICAL ADMINISTRATORS                                      -->
<!-- ========================================================================= -->
<div id="view-admins" class="role-page-view">
    <div class="card" style="border-left: 4px solid var(--red); margin-bottom: 24px;">
        <div class="role-header-banner">
            <div>
                <div class="role-header-title">
                    <h3>🛡️ Technical Administrators</h3>
                    <span style="background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                        {{ count($admins) }} Active
                    </span>
                </div>
                <p class="role-header-desc">
                    Technical administrators manage catalog models, options catalog, freight matrices, user provisioning, and audit trails. 
                    <strong style="color: #991b1b;">(Strictly shielded: No financial totals, quote margins, or corporate revenue visibility)</strong>
                </p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-primary" onclick="openAddAdminModal()">
                    + Add Technical Admin
                </button>
            </div>
        </div>

        <!-- Live Instant Search Bar for Admins -->
        <div class="table-filter-bar">
            <div class="search-input-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    id="adminSearchInput" 
                    class="form-control" 
                    placeholder="Quick search admins by name, email, company ID..."
                    oninput="filterTableRows('adminTableBody', this.value)"
                >
            </div>
            <div style="font-size: 13px; color: var(--text-muted);">
                Showing <strong>{{ count($admins) }}</strong> technical administrators
            </div>
        </div>

        <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 520px; -webkit-overflow-scrolling: touch;">
            <table class="data-table">
                <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                    <tr>
                        <th>Administrator Name</th>
                        <th>Office Email</th>
                        <th>Company ID</th>
                        <th>Role Scope</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                    @forelse ($admins as $admin)
                        <tr>
                            <td>
                                <strong>{{ $admin->name }}</strong>
                                @if ($admin->id === auth()->id())
                                    <span style="margin-left: 6px; font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 600;">You</span>
                                @endif
                            </td>
                            <td>{{ $admin->email }}</td>
                            <td><code>{{ $admin->company_id ?? '&mdash;' }}</code></td>
                            <td>
                                <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                    Technical Admin
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $admin->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $admin->is_active ? 'Active' : 'Deactivated' }}
                                </span>
                            </td>
                            <td>{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <button class="btn-secondary" onclick="openEditModal({{ json_encode($admin) }}, 'admin')">Edit</button>
                                    
                                    @if ($admin->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle', $admin) }}" method="POST" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="btn-secondary" style="color: {{ $admin->is_active ? '#991b1b' : '#166534' }};">
                                                {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn-secondary" style="color: #991b1b; border-color: #fecaca;" onclick="confirmDeleteUser({{ $admin->id }}, '{{ addslashes($admin->name) }}', 'technical administrator')">
                                            Remove
                                        </button>
                                    @else
                                        <span style="font-size: 12px; color: #94a3b8; font-style: italic; padding: 0 4px;">Current Session</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No technical administrators found.</td>
                        </tr>
                    @endforelse
                    <tr id="adminTableBody-no-results" style="display:none;" class="empty-row">
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No administrators found matching your quick filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- SUB-PAGE 2: COMMERCIAL MANAGERS                                           -->
<!-- ========================================================================= -->
<div id="view-managers" class="role-page-view" style="display: none;">
    <div class="card" style="border-left: 4px solid #2563eb; margin-bottom: 24px;">
        <div class="role-header-banner">
            <div>
                <div class="role-header-title">
                    <h3>👔 Commercial Managers</h3>
                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                        {{ count($managers) }} Managers
                    </span>
                </div>
                <p class="role-header-desc">
                    Executive leadership and sales directors with price adjustment authority, executive dashboards, quotation inspection, and Excel analytics.
                    <strong style="color: #1e40af;">(Exclusive financial visibility group)</strong>
                </p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-primary" style="background: #2563eb;" onclick="openAddManagerModal()">
                    + Add Commercial Manager
                </button>
            </div>
        </div>

        <!-- Live Instant Search Bar for Managers -->
        <div class="table-filter-bar">
            <div class="search-input-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input 
                    type="text" 
                    id="managerSearchInput" 
                    class="form-control" 
                    placeholder="Quick search managers by name, email, scope / title..."
                    oninput="filterTableRows('managerTableBody', this.value)"
                >
            </div>
            <div style="font-size: 13px; color: var(--text-muted);">
                Showing <strong>{{ count($managers) }}</strong> commercial managers
            </div>
        </div>

        <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 520px; -webkit-overflow-scrolling: touch;">
            <table class="data-table">
                <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                    <tr>
                        <th>Manager Name</th>
                        <th>Office Email</th>
                        <th>Company ID</th>
                        <th>Scope / Title</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="managerTableBody">
                    @forelse ($managers as $mgr)
                        <tr>
                            <td><strong>{{ $mgr->name }}</strong></td>
                            <td>{{ $mgr->email }}</td>
                            <td><code>{{ $mgr->company_id ?? '&mdash;' }}</code></td>
                            <td>
                                <span style="background: #eff6ff; color: #1d4ed8; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                    {{ $mgr->territory ?? 'Commercial Management' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $mgr->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $mgr->is_active ? 'Active' : 'Deactivated' }}
                                </span>
                            </td>
                            <td>{{ $mgr->last_login_at ? $mgr->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <button class="btn-secondary" onclick="openEditModal({{ json_encode($mgr) }}, 'manager')">Edit</button>
                                    <form action="{{ route('admin.users.toggle', $mgr) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="color: {{ $mgr->is_active ? '#991b1b' : '#166534' }};">
                                            {{ $mgr->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <button type="button" class="btn-secondary" style="color: #991b1b; border-color: #fecaca;" onclick="confirmDeleteUser({{ $mgr->id }}, '{{ addslashes($mgr->name) }}', 'commercial manager')">
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No commercial managers configured.</td>
                        </tr>
                    @endforelse
                    <tr id="managerTableBody-no-results" style="display:none;" class="empty-row">
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No managers found matching your quick filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- SUB-PAGE 3: SALES REPRESENTATIVES                                         -->
<!-- ========================================================================= -->
<div id="view-sales" class="role-page-view" style="display: none;">
    <div class="card" style="border-left: 4px solid #059669; margin-bottom: 24px;">
        <div class="role-header-banner">
            <div>
                <div class="role-header-title">
                    <h3>💼 Sales Representatives</h3>
                    <span style="background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                        {{ $users->total() }} Field Reps
                    </span>
                </div>
                <p class="role-header-desc">
                    Field sales representatives configure sweeper builds, generate customer proposals, and manage quotations in their assigned North America territories.
                </p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn-primary" style="background: #059669;" onclick="openAddRepModal()">
                    + Add Sales Representative
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar with Tab Persistence -->
        <div class="table-filter-bar">
            <form method="GET" action="{{ route('admin.users.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; flex: 1;">
                <input type="hidden" name="tab" value="sales">
                <div class="search-input-wrap">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Search rep name, email, code, territory..." 
                        value="{{ request('search') }}"
                    >
                </div>
                <select name="status" class="form-control" style="width: 150px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Deactivated</option>
                </select>
                <button type="submit" class="btn-secondary">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.users.index', ['tab' => 'sales']) }}" class="btn-secondary">Clear</a>
                @endif
            </form>

            <div style="font-size: 13px; color: var(--text-muted);">
                Showing <strong>{{ $users->count() }}</strong> of <strong>{{ $users->total() }}</strong> representatives
            </div>
        </div>

        <div class="table-scroll-container" style="overflow-x: auto; overflow-y: auto; max-height: 520px; -webkit-overflow-scrolling: touch;">
            <table class="data-table">
                <thead style="position: sticky; top: 0; z-index: 5; background: #f8fafc;">
                    <tr>
                        <th>Representative Name</th>
                        <th>Rep Code</th>
                        <th>Office Email</th>
                        <th>Company ID</th>
                        <th>Assigned Territory</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td><strong>{{ $u->name }}</strong></td>
                            <td><code>{{ $u->rep_code }}</code></td>
                            <td>{{ $u->email }}</td>
                            <td><code>{{ $u->company_id ?? '&mdash;' }}</code></td>
                            <td>{{ $u->territory ?? 'Assigned Territories' }}</td>
                            <td>
                                <span class="badge {{ $u->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $u->is_active ? 'Active' : 'Deactivated' }}
                                </span>
                            </td>
                            <td>{{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <button class="btn-secondary" onclick="openEditModal({{ json_encode($u) }}, 'sales')">Edit</button>
                                    <form action="{{ route('admin.users.toggle', $u) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="color: {{ $u->is_active ? '#991b1b' : '#166534' }};">
                                            {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <button type="button" class="btn-secondary" style="color: #991b1b; border-color: #fecaca;" onclick="confirmDeleteUser({{ $u->id }}, '{{ addslashes($u->name) }}', 'sales representative')">
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 24px;">No sales representatives found matching your search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px;">
            {{ $users->appends(array_merge(request()->except('page'), ['tab' => 'sales']))->links() }}
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- SUB-PAGE 4: ALL ACCOUNTS OVERVIEW                                         -->
<!-- ========================================================================= -->
<div id="view-all" class="role-page-view" style="display: none;">
    <!-- Metric Cards -->
    <div class="overview-card-grid">
        <!-- Admins Summary Card -->
        <div class="overview-card" style="border-top: 4px solid var(--red);" onclick="switchUserTab('admins')">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 24px;">🛡️</span>
                    <span style="background: #fee2e2; color: #991b1b; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                        Technical Access
                    </span>
                </div>
                <h4 style="margin: 0 0 6px 0; font-size: 16px; color: #0f172a;">Technical Administrators</h4>
                <p style="margin: 0 0 16px 0; font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                    Catalog models, options catalog, freight matrices, system audit trails, and user management.
                </p>
            </div>
            <div>
                <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                    {{ count($admins) }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Accounts</span>
                </div>
                <button type="button" class="btn-secondary" style="width: 100%; justify-content: center; font-weight: 600;">
                    Manage Technical Admins &rarr;
                </button>
            </div>
        </div>

        <!-- Managers Summary Card -->
        <div class="overview-card" style="border-top: 4px solid #2563eb;" onclick="switchUserTab('managers')">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 24px;">👔</span>
                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                        Financial Control
                    </span>
                </div>
                <h4 style="margin: 0 0 6px 0; font-size: 16px; color: #0f172a;">Commercial Managers</h4>
                <p style="margin: 0 0 16px 0; font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                    Executive revenue dashboards, team quote inspection, price adjustments, and Excel exports.
                </p>
            </div>
            <div>
                <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                    {{ count($managers) }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Managers</span>
                </div>
                <button type="button" class="btn-secondary" style="width: 100%; justify-content: center; font-weight: 600;">
                    Manage Commercial Managers &rarr;
                </button>
            </div>
        </div>

        <!-- Sales Reps Summary Card -->
        <div class="overview-card" style="border-top: 4px solid #059669;" onclick="switchUserTab('sales')">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 24px;">💼</span>
                    <span style="background: #d1fae5; color: #065f46; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                        Field Sales
                    </span>
                </div>
                <h4 style="margin: 0 0 6px 0; font-size: 16px; color: #0f172a;">Sales Representatives</h4>
                <p style="margin: 0 0 16px 0; font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                    Configure sweeper configurations, enhancements, customer proposals, and pricing bases.
                </p>
            </div>
            <div>
                <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                    {{ $users->total() }} <span style="font-size: 14px; font-weight: 500; color: var(--text-muted);">Representatives</span>
                </div>
                <button type="button" class="btn-secondary" style="width: 100%; justify-content: center; font-weight: 600;">
                    Manage Sales Reps &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Quick User Directory Summary -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 17px; color: #0f172a;">All Corporate Accounts Overview</h3>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--text-muted);">
                    Total {{ count($admins) + count($managers) + $users->total() }} user accounts across all 3 access groups.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-secondary" onclick="openAddAdminModal()">+ Add Admin</button>
                <button type="button" class="btn-secondary" onclick="openAddManagerModal()">+ Add Manager</button>
                <button type="button" class="btn-secondary" onclick="openAddRepModal()">+ Add Rep</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Admin Preview -->
            <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 20px;">🛡️</span>
                    <div>
                        <strong style="color: #991b1b; font-size: 14px;">Technical Administrators ({{ count($admins) }})</strong>
                        <div style="font-size: 12px; color: #7f1d1d;">Shielded from financial totals</div>
                    </div>
                </div>
                <button type="button" class="btn-secondary" onclick="switchUserTab('admins')" style="font-size: 12.5px;">Open Page &rarr;</button>
            </div>

            <!-- Manager Preview -->
            <div style="background: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 20px;">👔</span>
                    <div>
                        <strong style="color: #1e40af; font-size: 14px;">Commercial Managers ({{ count($managers) }})</strong>
                        <div style="font-size: 12px; color: #1e3a8a;">Financial totals &amp; price overrides</div>
                    </div>
                </div>
                <button type="button" class="btn-secondary" onclick="switchUserTab('managers')" style="font-size: 12.5px;">Open Page &rarr;</button>
            </div>

            <!-- Sales Preview -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 20px;">💼</span>
                    <div>
                        <strong style="color: #065f46; font-size: 14px;">Sales Representatives ({{ $users->total() }})</strong>
                        <div style="font-size: 12px; color: #047857;">North American territory proposal generators</div>
                    </div>
                </div>
                <button type="button" class="btn-secondary" onclick="switchUserTab('sales')" style="font-size: 12.5px;">Open Page &rarr;</button>
            </div>
        </div>
    </div>
</div>


<!-- ========================================================================= -->
<!-- MODALS                                                                    -->
<!-- ========================================================================= -->

<!-- Modal 1: Add Administrator Modal -->
<div id="addAdminModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:500px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                🛡️ Register Technical Administrator
            </h3>
            <button type="button" onclick="closeModal('addAdminModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: -8px; margin-bottom: 16px;">
            This user receives technical catalog control, freight matrix, and user provisioning. Strictly isolated from financial totals.
        </p>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role_type" value="admin">
            
            <div class="form-group">
                <label>Administrator Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Dev Raj">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Corporate / Office Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="cc@odrasweeper.com">
                </div>
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. ADMIN-DEV">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addAdminModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create Technical Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Add Commercial Manager Modal -->
<div id="addManagerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:500px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                👔 Register Commercial Manager
            </h3>
            <button type="button" onclick="closeModal('addManagerModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: -8px; margin-bottom: 16px;">
            This user receives executive dashboards, company-wide quote oversight, price adjustment authority, and Excel reporting access.
        </p>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role_type" value="manager">
            
            <div class="form-group">
                <label>Manager Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Alex Byczynski">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Corporate Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="alex@odrasweeper.com">
                </div>
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. MGR-ALEX">
                </div>
            </div>

            <div class="form-group">
                <label>Scope / Title</label>
                <input type="text" name="territory" class="form-control" placeholder="e.g. Executive Management / Sales Director">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addManagerModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #2563eb;">Create Manager</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add Sales Representative Modal -->
<div id="addRepModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:520px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                💼 Register Sales Representative
            </h3>
            <button type="button" onclick="closeModal('addRepModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role_type" value="sales">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. John Doe">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Office Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="john.doe@odrasweeper.com">
                </div>
                <div class="form-group">
                    <label>Quote Rep Code (2-4 letters)</label>
                    <input type="text" name="rep_code" class="form-control" required maxlength="4" placeholder="e.g. JD">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Company ID (Optional)</label>
                    <input type="text" name="company_id" class="form-control" placeholder="e.g. REP-JD">
                </div>
                <div class="form-group">
                    <label>Territory / Region</label>
                    <input type="text" name="territory" class="form-control" placeholder="e.g. Midwest, Illinois">
                </div>
            </div>
            <div class="form-group">
                <label>Initial Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addRepModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #059669;">Register Representative</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit User Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:24px; border-radius:8px; width:520px; max-width:90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px;" id="editModalTitle">Edit User Account</h3>
            <button type="button" onclick="closeModal('editModal')" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Office Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                <div class="form-group" id="edit_rep_code_group">
                    <label>Quote Rep Code</label>
                    <input type="text" id="edit_rep_code" name="rep_code" class="form-control" maxlength="4">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Company ID</label>
                    <input type="text" id="edit_company_id" name="company_id" class="form-control">
                </div>
                <div class="form-group" id="edit_territory_group">
                    <label id="edit_territory_label">Territory / Region</label>
                    <input type="text" id="edit_territory" name="territory" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Reset Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control" minlength="8" placeholder="Enter new password to reset">
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteUserForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
/**
 * Switch between Role Sub-Pages on the same page.
 * Updates visible panel, active nav tab, URL query & hash, and localStorage.
 */
function switchUserTab(tabName, updateUrl = true) {
    if (!['admins', 'managers', 'sales', 'all'].includes(tabName)) {
        tabName = 'admins';
    }

    // Hide all role page views
    const allViews = document.querySelectorAll('.role-page-view');
    allViews.forEach(v => v.style.display = 'none');

    // Remove active class from all tabs
    const allTabs = document.querySelectorAll('.role-nav-tab');
    allTabs.forEach(t => t.classList.remove('active'));

    // Show the target page view
    const targetView = document.getElementById(`view-${tabName}`);
    if (targetView) {
        targetView.style.display = 'block';
    }

    // Set active tab in navbar
    const activeBtn = document.querySelector(`.role-nav-tab[data-tab="${tabName}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }

    // Save to localStorage
    try {
        localStorage.setItem('odra_user_active_tab', tabName);
    } catch (e) {}

    // Update browser URL query & hash without full page reload
    if (updateUrl && window.history && window.history.replaceState) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({ tab: tabName }, '', url.toString());
    }
}

/**
 * Client-Side Instant Table Filter for Admins and Managers
 */
function filterTableRows(tableBodyId, query) {
    const q = (query || '').toLowerCase().trim();
    const rows = document.querySelectorAll(`#${tableBodyId} tr:not(.empty-row)`);
    let visibleCount = 0;

    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            r.style.display = '';
            visibleCount++;
        } else {
            r.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById(`${tableBodyId}-no-results`);
    if (noResultsRow) {
        noResultsRow.style.display = (visibleCount === 0 && q) ? '' : 'none';
    }
}

function openAddAdminModal() {
    document.getElementById('addAdminModal').style.display = 'flex';
}

function openAddManagerModal() {
    document.getElementById('addManagerModal').style.display = 'flex';
}

function openAddRepModal() {
    document.getElementById('addRepModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openEditModal(user, roleSlug) {
    document.getElementById('editForm').action = `/admin/users/${user.id}`;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_company_id').value = user.company_id || '';
    
    const repCodeGroup = document.getElementById('edit_rep_code_group');
    const repCodeInput = document.getElementById('edit_rep_code');
    const territoryGroup = document.getElementById('edit_territory_group');
    const territoryLabel = document.getElementById('edit_territory_label');
    const territoryInput = document.getElementById('edit_territory');
    const modalTitle = document.getElementById('editModalTitle');

    if (roleSlug === 'admin') {
        modalTitle.innerText = 'Edit Technical Administrator';
        repCodeGroup.style.display = 'none';
        repCodeInput.required = false;
        repCodeInput.value = '';
        territoryLabel.innerText = 'Technical Scope';
        territoryInput.value = user.territory || 'Technical Operations';
    } else if (roleSlug === 'manager') {
        modalTitle.innerText = 'Edit Commercial Manager';
        repCodeGroup.style.display = 'none';
        repCodeInput.required = false;
        repCodeInput.value = '';
        territoryLabel.innerText = 'Manager Scope / Title';
        territoryInput.value = user.territory || 'Commercial Management';
    } else {
        modalTitle.innerText = 'Edit Sales Representative';
        repCodeGroup.style.display = 'block';
        repCodeInput.required = true;
        repCodeInput.value = user.rep_code || '';
        territoryLabel.innerText = 'Assigned Territory';
        territoryInput.value = user.territory || '';
    }

    document.getElementById('editModal').style.display = 'flex';
}

function confirmDeleteUser(userId, userName, roleType) {
    if (confirm(`Are you sure you want to permanently remove ${roleType} "${userName}"?\n\nThis action cannot be undone.`)) {
        const form = document.getElementById('deleteUserForm');
        form.action = `/admin/users/${userId}`;
        form.submit();
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    const modals = ['addAdminModal', 'addManagerModal', 'addRepModal', 'editModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Restore active tab on initial page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || urlParams.get('role');
    const hashParam = window.location.hash.replace('#', '');
    const serverTab = '{{ $activeTab ?? "admins" }}';

    let initialTab = 'admins';
    if (tabParam && ['admins', 'managers', 'sales', 'all'].includes(tabParam)) {
        initialTab = tabParam;
    } else if (hashParam && ['admins', 'managers', 'sales', 'all'].includes(hashParam)) {
        initialTab = hashParam;
    } else if (urlParams.has('search') || urlParams.has('status') || urlParams.has('page')) {
        initialTab = 'sales';
    } else if (serverTab && ['admins', 'managers', 'sales', 'all'].includes(serverTab)) {
        initialTab = serverTab;
    } else {
        try {
            const stored = localStorage.getItem('odra_user_active_tab');
            if (stored && ['admins', 'managers', 'sales', 'all'].includes(stored)) {
                initialTab = stored;
            }
        } catch (e) {}
    }

    switchUserTab(initialTab, false);
});
</script>
@endsection
