<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ODRA Sales Portal') }} - Manager - @yield('title', 'Executive Overview')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/amg-luxury.css') }}">
    <style>
        :root {
            --red: #ac2529;
            --red-hover: #8e1e22;
            --emerald: #059669;
            --emerald-bg: #ecfdf5;
            --nav: #0f172a;
            --nav-hover: #1e293b;
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
        /* Manager Sidebar */
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
            z-index: 100;
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
        .sidebar-brand-badge {
            font-size: 10px;
            background: #059669;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-left: auto;
        }
        .sidebar-nav {
            padding: 20px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            overflow-y: auto;
        }
        .nav-heading {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            padding: 12px 12px 6px 12px;
            font-weight: 700;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .nav-link:hover {
            background: var(--nav-hover);
            color: white;
        }
        .nav-link.active {
            background: var(--red);
            color: white;
            font-weight: 600;
        }
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-info {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .user-name {
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role {
            font-size: 11px;
            color: #10b981;
            font-weight: 600;
        }
        .logout-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-btn:hover {
            color: white;
            background: var(--nav-hover);
        }

        /* Main Content Area */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow-x: hidden;
        }
        .topbar {
            height: 64px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .topbar-badge {
            font-size: 11px;
            background: var(--emerald-bg);
            color: var(--emerald);
            border: 1px solid #a7f3d0;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
        }
        .content {
            padding: 32px;
            flex: 1;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* Generic Cards & Controls */
        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .btn-primary {
            background: var(--red);
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s ease;
        }
        .btn-primary:hover { background: var(--red-hover); }
        .btn-success {
            background: #059669;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s ease;
        }
        .btn-success:hover { background: #047857; }
        .btn-secondary {
            background: white;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-secondary:hover { background: #f8fafc; }
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
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .data-table tr:hover { background: #f8fafc; }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-active { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-inactive { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-draft { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }

        @media (max-width: 900px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; height: auto; position: static; }
            .sidebar-nav { flex-direction: row; overflow-x: auto; white-space: nowrap; padding: 8px 12px; -webkit-overflow-scrolling: touch; gap: 6px; }
            .sidebar-nav .nav-heading { display: none; }
            .sidebar-nav .nav-link { flex-shrink: 0; padding: 8px 12px; font-size: 13px; }
            .sidebar-footer { display: none; }
            .topbar { padding: 12px 16px; flex-direction: column; align-items: flex-start; gap: 10px; }
            .content { padding: 16px 12px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Manager Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo.jpg') }}" alt="ODRA">
            <div class="sidebar-brand-text">ODRA Executive</div>
            <div class="sidebar-brand-badge">Financials</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-heading">Commercial Intelligence</div>
            <a href="{{ route('manager.dashboard') }}" class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Executive Dashboard
            </a>
            <a href="{{ route('manager.reports.index') }}" class="nav-link {{ request()->routeIs('manager.reports.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Reports &amp; Excel Export
            </a>

            <div class="nav-heading">Quotation Oversight</div>
            <a href="{{ route('manager.quotes.index') }}" class="nav-link {{ request()->routeIs('manager.quotes.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                All Team Quotations
            </a>
            <a href="{{ route('sales.quotes.create') }}" class="nav-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Quotation Builder
            </a>

            <div class="nav-heading">Commercial Master</div>
            <a href="{{ route('manager.models') }}" class="nav-link {{ request()->routeIs('manager.models*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                Sweeper Models Showcase
            </a>
            <a href="{{ route('manager.pricing.index') }}" class="nav-link {{ request()->routeIs('manager.pricing.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path><line x1="12" y1="18" x2="12" y2="6"></line></svg>
                Model &amp; Option Pricing
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">Manager / Financial Authority</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-btn" title="Sign out">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main View Content -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-title">
                @yield('header-title', 'Executive Management Console')
                <span class="topbar-badge">Financial Authority Active</span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 13px; color: var(--text-muted);">
                    {{ auth()->user()->territory ?? 'Executive Office' }}
                </span>
            </div>
        </header>

        <main class="content">
            @if (session('success'))
                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px; font-size: 14px; font-weight: 500;">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px; font-size: 14px; font-weight: 500;">
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
