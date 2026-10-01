<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ODRA Sales Portal') }} - @yield('title', 'Sales Portal')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <style>
        :root {
            --red: #ac2529;
            --red-hover: #8e1e22;
            --nav: #1b242e;
            --bg: #f5f6f8;
            --border: #e2e5e9;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
        }
        .portal-header {
            background: var(--nav);
            color: white;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: white;
        }
        .brand img {
            height: 36px;
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .brand-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-link {
            color: #cbd5e1;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-badge {
            display: flex;
            flex-direction: column;
            text-align: right;
            font-size: 13px;
        }
        .user-name { font-weight: 600; color: white; }
        .user-role { font-size: 11px; color: #94a3b8; }
        .btn-logout {
            background: rgba(255,255,255,0.08);
            color: #fca5a5;
            border: 1px solid rgba(255,255,255,0.15);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-logout:hover {
            background: rgba(239,68,68,0.2);
            color: white;
            border-color: rgba(239,68,68,0.4);
        }
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            color: white;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            animation: slideIn 0.2s ease;
        }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
    @stack('styles')
</head>
<body>
    <header class="portal-header">
        <a href="{{ route('sales.dashboard') }}" class="brand">
            <img src="{{ asset('images/logo.jpg') }}" alt="ODRA Road Sweepers">
            <span class="brand-title">ODRA Sales Portal</span>
        </a>

        <nav class="nav-links">
            <a href="{{ route('sales.dashboard') }}" class="nav-link {{ request()->routeIs('sales.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('sales.quotes.index') }}" class="nav-link {{ request()->routeIs('sales.quotes.index') ? 'active' : '' }}">My Quotations</a>
            <a href="{{ route('sales.quotes.create') }}" class="nav-link {{ request()->routeIs('sales.quotes.create') ? 'active' : '' }}">+ New Quotation</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="nav-link" style="color: #fca5a5;">Admin Console</a>
            @endif
        </nav>

        <div class="user-menu">
            <div class="user-badge">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-role">{{ auth()->user()->role->name }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </header>

    <main class="main-container">
        @yield('content')
    </main>

    <div id="toast" class="toast"></div>

    <script>
        function showToast(msg, duration = 3500) {
            const t = document.getElementById('toast');
            if (!t) return;
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(() => { t.style.display = 'none'; }, duration);
        }
    </script>
    @stack('scripts')
</body>
</html>
