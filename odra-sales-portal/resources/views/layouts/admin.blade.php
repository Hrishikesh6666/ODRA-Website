<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ODRA Sales Portal') }} - Admin - @yield('title', 'Console')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/amg-luxury.css') }}">
    <style>
        :root {
            --red: #ac2529;
            --red-hover: #8e1e22;
            --nav: #1b242e;
            --nav-hover: #2d3748;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--nav);
            color: white;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-brand img {
            height: 32px;
            background: white;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .sidebar-brand-text {
            font-weight: 700;
            font-size: 15px;
            letter-spacing: -0.2px;
        }
        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .nav-item:hover, .nav-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.08);
        }
        .nav-item.active {
            color: white;
            background: var(--red);
            font-weight: 600;
        }
        .nav-category {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            padding: 14px 14px 6px 14px;
            font-weight: 700;
        }
        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-badge {
            font-size: 12px;
            color: #cbd5e1;
        }
        .admin-badge strong { display: block; color: white; }
        .btn-signout {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fca5a5;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
        /* Main Content */
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-y: auto;
        }
        .topbar {
            background: white;
            padding: 16px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .btn-view-portal {
            font-size: 13px;
            color: #475569;
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-weight: 500;
        }
        .main-body {
            padding: 32px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }
        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .btn-primary {
            background: var(--red);
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary:hover { background: var(--red-hover); }
        .btn-secondary {
            background: white;
            color: var(--text-dark);
            border: 1px solid #cbd5e1;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
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
        .data-table tr:hover { background: #f8fafc; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        .form-control { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: var(--red); }

        /* User-Friendly Pagination */
        .portal-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 16px;
            margin-top: 12px;
            border-top: 1px solid var(--border);
            font-size: 13px;
            color: #64748b;
        }
        .portal-pagination .pagination-info {
            font-size: 13px;
            color: #475569;
        }
        .portal-pagination .pagination-info span.font-medium {
            font-weight: 700;
            color: #0f172a;
        }
        .portal-pagination .pagination-buttons {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .portal-pagination .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .portal-pagination .pagination-btn:hover:not(.disabled):not(.active) {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }
        .portal-pagination .pagination-btn.active {
            background: var(--red);
            border-color: var(--red);
            color: white;
            cursor: default;
        }
        .portal-pagination .pagination-btn.disabled {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
            cursor: not-allowed;
            opacity: 0.7;
        }
        .portal-pagination .pagination-dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 34px;
            color: #94a3b8;
            font-weight: bold;
        }

        /* Hard guard against any unstyled Tailwind SVG chevron arrows blowing up */
        nav[role="navigation"] svg,
        .portal-pagination svg,
        .w-5.h-5,
        svg.w-5 {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        @media (max-width: 900px) {
            body {
                flex-direction: column !important;
            }
            .sidebar {
                width: 100% !important;
                height: auto !important;
                position: relative !important;
            }
            .sidebar-brand {
                padding: 14px 18px;
            }
            .sidebar-nav {
                flex-direction: row;
                overflow-x: auto;
                white-space: nowrap;
                padding: 8px 12px;
                -webkit-overflow-scrolling: touch;
                gap: 6px;
            }
            .nav-item {
                flex-shrink: 0;
                padding: 8px 12px;
                font-size: 13px;
            }
            .sidebar-footer {
                display: none;
            }
            .topbar {
                padding: 12px 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .main-body {
                padding: 16px 12px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Admin Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.jpg') }}" alt="ODRA">
            <span class="sidebar-brand-text">ODRA Admin HQ</span>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-category">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <div class="nav-category">Catalogue Management</div>
            <a href="{{ route('models.catalog') }}" class="nav-item" target="_blank" style="color: #6ee7b7;">
                &#9654; Sweeper Showcase (AMG View)
            </a>
            <a href="{{ route('admin.models.index') }}" class="nav-item {{ request()->routeIs('admin.models.*') ? 'active' : '' }}">
                Models &amp; Master Setup
            </a>
            <a href="{{ route('manager.pricing.index') }}" class="nav-item {{ request()->routeIs('manager.pricing.*') ? 'active' : '' }}">
                Master Pricing Console
            </a>
            <a href="{{ route('admin.quotation-settings.index') }}" class="nav-item {{ request()->routeIs('admin.quotation-settings.*') ? 'active' : '' }}">
                Quotation Page &amp; Pricing Basis
            </a>
            <a href="{{ route('admin.enhancements.index') }}" class="nav-item {{ request()->routeIs('admin.enhancements.*') ? 'active' : '' }}">
                Enhancements &amp; Parts
            </a>
            <a href="{{ route('admin.freight.index') }}" class="nav-item {{ request()->routeIs('admin.freight.*') ? 'active' : '' }}">
                Freight Matrix
            </a>

            <div class="nav-category">Administration</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                Users &amp; Administrators
            </a>
            @if(request()->routeIs('admin.users.*'))
                <div style="padding-left: 18px; display: flex; flex-direction: column; gap: 2px; margin-top: 3px; margin-bottom: 8px;">
                    <a href="{{ route('admin.users.index', ['tab' => 'admins']) }}" 
                       class="nav-sub-link" 
                       onclick="if(typeof switchUserTab === 'function'){ switchUserTab('admins'); return false; }"
                       style="font-size: 12.5px; color: #94a3b8; text-decoration: none; padding: 5px 10px; border-radius: 4px; display: flex; align-items: center; gap: 6px; transition: color 0.15s ease;">
                        <span>🛡️</span> Technical Admins
                    </a>
                    <a href="{{ route('admin.users.index', ['tab' => 'managers']) }}" 
                       class="nav-sub-link" 
                       onclick="if(typeof switchUserTab === 'function'){ switchUserTab('managers'); return false; }"
                       style="font-size: 12.5px; color: #94a3b8; text-decoration: none; padding: 5px 10px; border-radius: 4px; display: flex; align-items: center; gap: 6px; transition: color 0.15s ease;">
                        <span>👔</span> Commercial Managers
                    </a>
                    <a href="{{ route('admin.users.index', ['tab' => 'sales']) }}" 
                       class="nav-sub-link" 
                       onclick="if(typeof switchUserTab === 'function'){ switchUserTab('sales'); return false; }"
                       style="font-size: 12.5px; color: #94a3b8; text-decoration: none; padding: 5px 10px; border-radius: 4px; display: flex; align-items: center; gap: 6px; transition: color 0.15s ease;">
                        <span>💼</span> Sales Representatives
                    </a>
                </div>
            @endif
            <a href="{{ route('admin.audit.index') }}" class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                Security Audit Log
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-badge">
                <strong>{{ auth()->user()->name }}</strong>
                <span>Administrator</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-signout">Sign Out</button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="content-area">
        <header class="topbar">
            <h2>@yield('header-title', 'Administration')</h2>
            <div class="topbar-right">
                <a href="{{ route('sales.quotes.index') }}" class="btn-view-portal" target="_blank">
                    &rarr; Open Sales Portal
                </a>
            </div>
        </header>

        <main class="main-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    <script src="{{ asset('js/amg-animations.js') }}"></script>
    @stack('scripts')
</body>
</html>
