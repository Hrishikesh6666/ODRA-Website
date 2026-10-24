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
            height: 100dvh;
            z-index: 50;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease;
        }
        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 73px;
        }
        .sidebar-brand-inner {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .sidebar-brand img {
            height: 32px;
            background: white;
            padding: 3px 6px;
            border-radius: 4px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }
        .sidebar-brand:hover img {
            transform: scale(1.04);
        }
        .sidebar-brand-text {
            font-weight: 700;
            font-size: 15px;
            letter-spacing: -0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-close-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
            cursor: pointer;
            padding: 0;
            transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
        }
        .sidebar-close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            color: #ffffff;
        }
        .sidebar-close-btn:active {
            transform: scale(0.94);
        }
        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            gap: 3px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }
        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 4px;
            transition: background 0.2s ease;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.28);
        }
        .nav-category {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            padding: 14px 14px 6px 14px;
            font-weight: 700;
            user-select: none;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            transition: background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1),
                        color 0.2s cubic-bezier(0.4, 0, 0.2, 1),
                        box-shadow 0.2s ease,
                        transform 0.15s ease;
            user-select: none;
        }
        /* Slim animated red indicator on left */
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3.5px;
            background: #ef4444;
            border-radius: 0 3px 3px 0;
            transform: scaleY(0);
            opacity: 0;
            transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.2s ease;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
        }
        .nav-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }
        .nav-item:hover::before {
            transform: scaleY(0.5);
            opacity: 0.6;
        }
        .nav-item:active {
            transform: scale(0.99);
        }
        .nav-item.active {
            color: #ffffff;
            background: var(--red);
            font-weight: 600;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }
        .nav-item.active::before {
            transform: scaleY(1);
            opacity: 1;
            background: #ff6b6b;
            box-shadow: 0 0 10px rgba(255, 107, 107, 0.8);
        }
        .nav-item .nav-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .nav-item:hover .nav-icon {
            transform: scale(1.15);
        }

        /* Expandable Submenu Structure */
        .nav-group {
            display: flex;
            flex-direction: column;
        }
        .nav-item-container {
            display: flex;
            align-items: center;
            position: relative;
            border-radius: 6px;
        }
        .nav-item-has-submenu {
            flex: 1;
            padding-right: 38px;
        }
        .submenu-toggle-btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: #94a3b8;
            border-radius: 5px;
            cursor: pointer;
            padding: 0;
            transition: color 0.2s ease, background-color 0.2s ease;
            z-index: 2;
        }
        .submenu-toggle-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }
        .nav-item-container.active .submenu-toggle-btn {
            color: rgba(255, 255, 255, 0.85);
        }
        .nav-item-container.active .submenu-toggle-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
        }
        .submenu-chevron {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-group.open .submenu-chevron,
        .submenu-toggle-btn[aria-expanded="true"] .submenu-chevron {
            transform: rotate(180deg);
        }
        .nav-submenu {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-group.open .nav-submenu,
        .nav-submenu.expanded {
            grid-template-rows: 1fr;
        }
        .nav-submenu-inner {
            overflow: hidden;
            padding-left: 20px;
            padding-right: 6px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 3px;
            margin-bottom: 6px;
        }
        .nav-sub-link {
            font-size: 12.5px;
            color: #94a3b8;
            text-decoration: none;
            padding: 7px 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.18s ease, background-color 0.18s ease, transform 0.15s ease;
            min-height: 32px;
        }
        .nav-sub-link .sub-icon {
            font-size: 13px;
            transition: transform 0.18s ease;
            flex-shrink: 0;
        }
        .nav-sub-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.07);
            transform: translateX(3px);
        }
        .nav-sub-link:hover .sub-icon {
            transform: scale(1.18);
        }
        .nav-sub-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: rgba(0, 0, 0, 0.15);
        }
        .admin-badge {
            font-size: 12px;
            color: #cbd5e1;
            overflow: hidden;
        }
        .admin-badge strong {
            display: block;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .admin-badge span {
            font-size: 11px;
            color: #94a3b8;
        }
        .btn-signout {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fca5a5;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-signout:hover {
            background: rgba(239, 68, 68, 0.22);
            border-color: rgba(239, 68, 68, 0.4);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.25);
        }
        .btn-signout:active {
            transform: scale(0.95);
            background: rgba(239, 68, 68, 0.35);
        }

        /* Accessibility focus indicators */
        .nav-item:focus-visible,
        .btn-signout:focus-visible,
        .submenu-toggle-btn:focus-visible,
        .sidebar-close-btn:focus-visible,
        .mobile-sidebar-toggle:focus-visible,
        .nav-sub-link:focus-visible {
            outline: 2px solid #ef4444;
            outline-offset: 2px;
        }

        /* Mobile Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.28s ease;
            pointer-events: none;
        }
        .sidebar-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-sidebar-toggle {
            display: none;
        }

        /* Respect prefers-reduced-motion */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
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
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
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

        /* Mobile Drawer & Responsive Layout */
        @media (max-width: 900px) {
            body {
                flex-direction: column !important;
                position: relative;
            }
            body.mobile-nav-open {
                overflow: hidden !important;
                touch-action: none;
            }
            .mobile-sidebar-toggle {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                min-width: 44px;
                min-height: 44px;
                padding: 0;
                background: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                color: #0f172a;
                cursor: pointer;
                transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
            }
            .mobile-sidebar-toggle:hover {
                background: #f1f5f9;
                border-color: #94a3b8;
            }
            .mobile-sidebar-toggle:active {
                transform: scale(0.95);
                background: #e2e8f0;
            }
            .sidebar-backdrop {
                display: block;
            }
            .sidebar-backdrop.active {
                opacity: 1;
                pointer-events: auto;
            }
            .sidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                bottom: 0;
                width: 290px !important;
                max-width: 86vw !important;
                height: 100vh !important;
                height: 100dvh !important;
                z-index: 9999 !important;
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease;
                box-shadow: none;
                display: flex !important;
                flex-direction: column !important;
                background: var(--nav);
                overflow: hidden;
            }
            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 12px 0 32px rgba(0, 0, 0, 0.5);
            }
            .sidebar-brand {
                padding: 16px 18px;
            }
            .sidebar-close-btn {
                display: inline-flex !important;
            }
            .sidebar-nav {
                flex-direction: column !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                white-space: normal !important;
                padding: 14px 12px !important;
                gap: 4px !important;
                -webkit-overflow-scrolling: touch;
            }
            .nav-item {
                min-height: 44px; /* Accessible 44px touch target */
                padding: 11px 14px;
                font-size: 13.5px;
                white-space: normal;
                word-break: break-word;
                line-height: 1.35;
            }
            .nav-sub-link {
                min-height: 42px; /* Touch friendly submenu targets */
                padding: 10px 12px;
                font-size: 13px;
            }
            .submenu-toggle-btn {
                width: 40px;
                height: 40px;
            }
            .sidebar-footer {
                display: flex !important;
                padding: 14px 16px;
            }
            .topbar {
                padding: 12px 16px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .topbar-left {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .topbar-right {
                width: 100%;
                gap: 8px;
            }
            .main-body {
                padding: 16px 12px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>

    <!-- Admin Sidebar -->
    <aside class="sidebar" id="adminSidebar" aria-label="ODRA Administration Navigation">
        <div class="sidebar-brand">
            <div class="sidebar-brand-inner">
                <img src="{{ asset('images/logo.jpg') }}" alt="ODRA">
                <span class="sidebar-brand-text">ODRA Administration Center</span>
            </div>
            <!-- Mobile Close Button (visible on mobile only) -->
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close navigation menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav" id="sidebarNav">
            <div class="nav-category">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" {!! request()->routeIs('admin.dashboard') ? 'aria-current="page"' : '' !!}>
                <span>Dashboard</span>
            </a>

            <div class="nav-category">Catalogue Management</div>
            <a href="{{ route('models.catalog') }}" class="nav-item" target="_blank" rel="noopener noreferrer" style="color: #6ee7b7;">
                <span class="nav-icon" aria-hidden="true">&#9654;</span>
                <span>Sweeper Showcase (AMG View)</span>
            </a>
            <a href="{{ route('manager.pricing.index') }}" class="nav-item {{ request()->routeIs('manager.pricing.*') ? 'active' : '' }}" {!! request()->routeIs('manager.pricing.*') ? 'aria-current="page"' : '' !!}>
                <span>Master Pricing Console</span>
            </a>
            <a href="{{ route('admin.quotation-settings.index') }}" class="nav-item {{ request()->routeIs('admin.quotation-settings.*') || request()->routeIs('manager.quotation-settings.*') ? 'active' : '' }}" {!! (request()->routeIs('admin.quotation-settings.*') || request()->routeIs('manager.quotation-settings.*')) ? 'aria-current="page"' : '' !!}>
                <span>Quotation Configuration &amp; Pricing Basis Master</span>
            </a>
            <a href="{{ route('admin.enhancements.index') }}" class="nav-item {{ request()->routeIs('admin.enhancements.*') ? 'active' : '' }}" {!! request()->routeIs('admin.enhancements.*') ? 'aria-current="page"' : '' !!}>
                <span>Enhancements &amp; Parts</span>
            </a>
            <a href="{{ route('admin.freight.index') }}" class="nav-item {{ request()->routeIs('admin.freight.*') ? 'active' : '' }}" {!! request()->routeIs('admin.freight.*') ? 'aria-current="page"' : '' !!}>
                <span>Freight Matrix</span>
            </a>

            <div class="nav-category">Administration</div>

            {{-- Expandable Accordion Submenu for Users & Administrators --}}
            @php
                $isUsersRoute = request()->routeIs('admin.users.*');
                $currentTab = request('tab', 'admins');
            @endphp
            <div class="nav-group {{ $isUsersRoute ? 'open' : '' }}" id="usersNavGroup">
                <div class="nav-item-container {{ $isUsersRoute ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="nav-item nav-item-has-submenu {{ $isUsersRoute ? 'active' : '' }}" {!! $isUsersRoute ? 'aria-current="page"' : '' !!}>
                        <span>Users &amp; Administrators</span>
                    </a>
                    <button type="button" 
                            class="submenu-toggle-btn" 
                            id="usersSubmenuToggle" 
                            aria-expanded="{{ $isUsersRoute ? 'true' : 'false' }}" 
                            aria-controls="usersSubmenu" 
                            aria-label="Toggle Users and Administrators sub-menu">
                        <svg class="submenu-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>
                <div class="nav-submenu {{ $isUsersRoute ? 'expanded' : '' }}" id="usersSubmenu" role="region" aria-label="Users and Administrators sub-sections">
                    <div class="nav-submenu-inner">
                        <a href="{{ route('admin.users.index', ['tab' => 'admins']) }}" 
                           class="nav-sub-link {{ $isUsersRoute && $currentTab === 'admins' ? 'active' : '' }}" 
                           onclick="if(typeof switchUserTab === 'function'){ switchUserTab('admins'); return false; }">
                            <span class="sub-icon" aria-hidden="true">🛡️</span> 
                            <span>Technical Admins</span>
                        </a>
                        <a href="{{ route('admin.users.index', ['tab' => 'managers']) }}" 
                           class="nav-sub-link {{ $isUsersRoute && $currentTab === 'managers' ? 'active' : '' }}" 
                           onclick="if(typeof switchUserTab === 'function'){ switchUserTab('managers'); return false; }">
                            <span class="sub-icon" aria-hidden="true">👔</span> 
                            <span>Commercial Managers</span>
                        </a>
                        <a href="{{ route('admin.users.index', ['tab' => 'sales']) }}" 
                           class="nav-sub-link {{ $isUsersRoute && $currentTab === 'sales' ? 'active' : '' }}" 
                           onclick="if(typeof switchUserTab === 'function'){ switchUserTab('sales'); return false; }">
                            <span class="sub-icon" aria-hidden="true">💼</span> 
                            <span>Sales Representatives</span>
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.audit.index') }}" class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}" {!! request()->routeIs('admin.audit.*') ? 'aria-current="page"' : '' !!}>
                <span>Security Audit Log</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-badge">
                <strong>{{ auth()->user()->name }}</strong>
                <span>Administrator</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="btn-signout" aria-label="Sign out of administration session">Sign Out</button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="content-area">
        <header class="topbar">
            <div class="topbar-left">
                <!-- Hamburger menu button on mobile -->
                <button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-label="Open navigation menu" aria-controls="adminSidebar" aria-expanded="false">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h2>@yield('header-title', 'Administration')</h2>
            </div>
            <div class="topbar-right" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('sales.dashboard') }}" class="btn-secondary" style="font-size: 12px; padding: 6px 12px; text-decoration: none;">Dashboard</a>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="font-size: 12px; padding: 6px 12px; font-weight: 700; color: #ac2529; text-decoration: none;">Administration Center</a>
                <a href="{{ route('sales.quotes.index') }}" class="btn-secondary" style="font-size: 12px; padding: 6px 12px; text-decoration: none;">My Quotations</a>
                <a href="{{ route('sales.quotes.create') }}" class="btn-primary" style="font-size: 12px; padding: 6px 12px; text-decoration: none;">+ New Quotation</a>
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

    @include('partials.quote-inspect-modal')
    @include('partials.revision-history-modal')

    <script src="{{ asset('js/amg-animations.js') }}?v={{ file_exists(public_path('js/amg-animations.js')) ? filemtime(public_path('js/amg-animations.js')) : '1.2' }}"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('mobileSidebarToggle');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const backdrop = document.getElementById('sidebarBackdrop');
        const usersToggle = document.getElementById('usersSubmenuToggle');
        const usersGroup = document.getElementById('usersNavGroup');
        const usersSubmenu = document.getElementById('usersSubmenu');

        function openMobileSidebar() {
            if (!sidebar) return;
            sidebar.classList.add('mobile-open');
            if (backdrop) backdrop.classList.add('active');
            document.body.classList.add('mobile-nav-open');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
            if (closeBtn) closeBtn.focus();
        }

        function closeMobileSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('mobile-open');
            if (backdrop) backdrop.classList.remove('active');
            document.body.classList.remove('mobile-nav-open');
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', 'false');
                toggleBtn.focus();
            }
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (sidebar && sidebar.classList.contains('mobile-open')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                closeMobileSidebar();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                closeMobileSidebar();
            });
        }

        // Close on Escape key press
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            }
        });

        // Close drawer when a nav link is clicked on mobile
        if (sidebar) {
            const navLinks = sidebar.querySelectorAll('.nav-item:not(.nav-item-has-submenu), .nav-sub-link');
            navLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 900) {
                        closeMobileSidebar();
                    }
                });
            });
        }

        // Expandable accordion toggle for Users & Administrators
        if (usersToggle && usersGroup) {
            usersToggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = usersGroup.classList.contains('open');
                if (isOpen) {
                    usersGroup.classList.remove('open');
                    if (usersSubmenu) usersSubmenu.classList.remove('expanded');
                    usersToggle.setAttribute('aria-expanded', 'false');
                } else {
                    usersGroup.classList.add('open');
                    if (usersSubmenu) usersSubmenu.classList.add('expanded');
                    usersToggle.setAttribute('aria-expanded', 'true');
                }
            });
        }
    });
    </script>
    @stack('scripts')
</body>
</html>
