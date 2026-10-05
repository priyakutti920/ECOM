@php
    $seg      = request()->segment(2) ?? '';
    $siteName = \App\Models\StoreSetting::getStoreName();
    $siteLogo = \App\Models\StoreSetting::getLogoUrl();
    $adminFavicon = \App\Models\StoreSetting::getFaviconUrl();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Dashboard') — {{ $siteName }} Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ $adminFavicon }}">
    <link rel="icon" href="{{ $adminFavicon }}">
    <link rel="apple-touch-icon" href="{{ $adminFavicon }}">

    <link rel="stylesheet" href="{{ asset('assets/css/fa-all.min.css') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

    <style>
        :root {
            --admin-primary: #3a7bd5;
            --admin-primary-dark: #2f6bc4;
            --admin-success: #10b981;
            --admin-danger: #ef4444;
            --admin-warning: #f59e0b;
            --admin-text-main: #1e293b;
            --admin-text-muted: #64748b;
            --admin-bg-page: #f8fafc;
            --admin-card-border: #e2e8f0;
            --admin-card-bg: #ffffff;
            --admin-radius: 8px;
            --admin-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            color: var(--admin-text-main);
            background-color: var(--admin-bg-page);
            padding-top: 54px;
            overflow-x: hidden;
        }

        /* ── Loading overlay ── */
        #loading {
            position: fixed;
            display: flex; justify-content: center; align-items: center;
            width: 100%; height: 100%;
            top: 0; left: 0;
            background-color: #fff;
            z-index: 99999;
        }
        .loading-bars {
            display: flex; gap: 5px;
            align-items: center; justify-content: center;
        }
        .loading-bars span {
            width: 4px; height: 36px;
            background: var(--admin-primary);
            animation: scale-bars 0.9s ease-in-out infinite;
        }
        .loading-bars span:nth-child(2) { animation-delay: -0.8s; }
        .loading-bars span:nth-child(3) { animation-delay: -0.7s; }
        .loading-bars span:nth-child(4) { animation-delay: -0.6s; }
        .loading-bars span:nth-child(5) { animation-delay: -0.5s; }
        @keyframes scale-bars {
            0%, 40%, 100% { transform: scaleY(0.1); }
            20%            { transform: scaleY(1); }
        }

        /* ── Fixed Admin Navbar ── */
        .admin-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--admin-card-border);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            margin-bottom: 0;
            height: 52px;
            min-height: 52px;
            z-index: 1030;
            position: fixed;
            top: 0; left: 0; right: 0;
            overflow: visible;
        }

        .admin-nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            height: 52px;
            width: 100%;
            gap: 12px;
        }

        .admin-brand-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .admin-drawer-toggle {
            display: none;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .admin-drawer-toggle:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .admin-brand-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none !important;
            color: #0f172a;
            font-weight: 700;
            font-size: 15px;
            padding: 4px 0;
            white-space: nowrap;
        }
        .admin-brand-logo {
            max-height: 28px;
            width: auto;
            object-fit: contain;
        }
        .admin-brand-badge {
            background: #e0f2fe;
            color: #0369a1;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Desktop Nav Items (>= 1200px) */
        .admin-desktop-nav {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 2px;
            flex-wrap: nowrap;
        }

        .admin-desktop-nav > li > a {
            color: #475569 !important;
            padding: 6px 9px;
            font-size: 12.5px;
            font-weight: 500;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none !important;
            white-space: nowrap;
            transition: background-color 0.15s, color 0.15s;
            line-height: 1.3;
        }

        .admin-desktop-nav > li > a:hover,
        .admin-desktop-nav > li.open > a {
            background: #f1f5f9 !important;
            color: #0f172a !important;
        }

        .admin-desktop-nav > li.active > a {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            font-weight: 600;
        }

        /* Right Utilities (View Store, Settings, Profile) */
        .admin-nav-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .admin-nav-util-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 500;
            color: #475569 !important;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none !important;
            height: 32px;
            transition: all 0.15s;
        }
        .admin-nav-util-btn:hover {
            background: #f1f5f9;
            color: #0f172a !important;
            border-color: #cbd5e1;
        }
        .admin-nav-util-btn.active {
            background: #e2e8f0;
            color: #0f172a !important;
            border-color: #cbd5e1;
        }

        .admin-user-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 4px 10px 4px 6px;
            font-size: 12.5px;
            font-weight: 600;
            color: #334155 !important;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            height: 32px;
            text-decoration: none !important;
            transition: all 0.15s;
        }
        .admin-user-btn:hover,
        .admin-user-dropdown.open .admin-user-btn {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a !important;
        }
        .admin-user-avatar {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--admin-primary);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        .admin-user-name {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Dropdowns */
        .dropdown-menu {
            border: 1px solid var(--admin-card-border);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            padding: 6px 0;
            min-width: 190px;
            z-index: 1050;
        }
        .dropdown-menu > li > a {
            padding: 8px 16px;
            font-size: 12.5px;
            color: #334155 !important;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s;
        }
        .dropdown-menu > li > a:hover {
            background: #f8fafc !important;
            color: var(--admin-primary) !important;
        }
        .dropdown-menu > .active > a,
        .dropdown-menu > .active > a:focus,
        .dropdown-menu > .active > a:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            font-weight: 600;
        }
        .dropdown-menu .dropdown-header {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            padding: 6px 16px 2px;
            font-weight: 700;
        }

        /* ── Mobile/Tablet Off-Canvas Drawer (< 1200px) ── */
        .admin-drawer-backdrop {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            z-index: 2000;
            display: none;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .admin-drawer-backdrop.show {
            display: block;
            opacity: 1;
        }

        .admin-mobile-drawer {
            position: fixed;
            top: 0; left: 0;
            width: 290px;
            max-width: 85vw;
            height: 100%;
            background: #ffffff;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            z-index: 2001;
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .admin-mobile-drawer.open {
            transform: translateX(0);
        }

        .drawer-header {
            padding: 16px 18px;
            border-bottom: 1px solid var(--admin-card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }
        .drawer-close-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #64748b;
            cursor: pointer;
            padding: 4px 8px;
            line-height: 1;
        }
        .drawer-body {
            padding: 12px 10px 40px;
            flex: 1;
            overflow-y: auto;
        }
        .drawer-nav-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .drawer-nav-list > li {
            margin-bottom: 2px;
        }
        .drawer-nav-list > li > a,
        .drawer-accordion-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 11px 14px;
            font-size: 13.5px;
            color: #334155;
            font-weight: 500;
            text-decoration: none !important;
            border-radius: 6px;
            background: none;
            border: none;
            text-align: left;
            transition: background 0.15s;
        }
        .drawer-nav-list > li > a:hover,
        .drawer-accordion-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .drawer-nav-list > li.active > a {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 600;
        }
        .drawer-sub-menu {
            list-style: none;
            margin: 4px 0 6px 18px;
            padding: 0;
            border-left: 2px solid #e2e8f0;
        }
        .drawer-sub-menu li a {
            display: block;
            padding: 8px 14px;
            font-size: 12.5px;
            color: #64748b;
            text-decoration: none !important;
            border-radius: 4px;
        }
        .drawer-sub-menu li a:hover,
        .drawer-sub-menu li.active a {
            color: var(--admin-primary);
            background: #f1f5f9;
            font-weight: 600;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1199px) and (min-width: 992px) {
            .admin-nav-container { padding: 0 10px; gap: 6px; }
            .admin-desktop-nav { gap: 1px; }
            .admin-desktop-nav > li > a { padding: 5px 6.5px; font-size: 11.5px; gap: 4px; }
            .admin-user-name { max-width: 75px; }
            .util-btn-text { display: none; }
        }

        @media (max-width: 991px) {
            .admin-desktop-nav { display: none !important; }
            .admin-drawer-toggle { display: inline-flex !important; }
            .admin-user-name { display: none; }
            .util-btn-text { display: none; }
        }

        /* ── Universal Media Card Styling (Used Globally) ── */
        .uni-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        @media (max-width: 576px) {
            .uni-media-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
        }

        .uni-media-card {
            background: #ffffff;
            border: 1px solid var(--admin-card-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            position: relative;
        }
        .uni-media-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
        .uni-media-card.is-trashed {
            opacity: 0.75;
            border-style: dashed;
            border-color: #f87171;
            background: #fef2f2;
        }

        .uni-card-thumb {
            position: relative;
            width: 100%;
            height: 145px;
            background: #f1f5f9;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .uni-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.25s ease;
        }
        .uni-media-card:hover .uni-card-img {
            transform: scale(1.04);
        }
        .uni-card-img.is-broken-img {
            object-fit: contain;
            padding: 14px;
            opacity: 0.85;
        }

        .uni-thumb-badges {
            position: absolute;
            top: 8px; left: 36px; right: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            pointer-events: none;
            z-index: 2;
            gap: 4px;
        }
        .uni-badge-pill {
            background: rgba(15, 23, 42, 0.75);
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 4px;
            backdrop-filter: blur(4px);
            letter-spacing: 0.3px;
        }
        .uni-badge-folder { background: rgba(58, 123, 213, 0.85); }
        .uni-badge-dim { background: rgba(15, 23, 42, 0.65); }
        .uni-badge-active { background: #10b981; }
        .uni-badge-inactive { background: #64748b; }

        .uni-thumb-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 3;
        }
        .uni-card-thumb:hover .uni-thumb-overlay {
            opacity: 1;
        }

        .uni-card-body {
            padding: 10px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .uni-card-title {
            font-weight: 600;
            font-size: 12.5px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }
        .uni-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: var(--admin-text-muted);
            gap: 6px;
        }
        .uni-card-meta .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .uni-card-footer {
            padding: 8px 10px;
            border-top: 1px solid #f1f5f9;
            background: #fafbfc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
        }
        .uni-action-btn {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11.5px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s;
            line-height: 1.4;
        }
        .uni-action-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .uni-btn-copy:hover { color: var(--admin-primary); border-color: #93c5fd; }
        .uni-btn-use:hover { color: #8b5cf6; border-color: #c4b5fd; }
        .uni-btn-replace:hover { color: #0284c7; border-color: #7dd3fc; }
        .uni-btn-delete:hover { color: var(--admin-danger); border-color: #fca5a5; background: #fff1f2; }

        @media (max-width: 576px) {
            .uni-btn-label { display: none; }
            .uni-action-btn { padding: 5px 7px; }
        }

        /* ── Page Content Wrapper ── */
        .admin-content {
            padding: 18px 16px;
            max-width: 1440px;
            margin: 0 auto;
        }

        /* ── Toasts & Alerts ── */
        #adminToastWrap {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }
        .admin-toast {
            pointer-events: auto;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: toastIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .admin-toast-success { background: #065f46; border-left: 4px solid #10b981; }
        .admin-toast-error   { background: #881337; border-left: 4px solid #f43f5e; }
        @keyframes toastIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        /* ── Universal Floating Bulk Action Bar ── */
        .admin-bulk-bar {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            opacity: 0;
            pointer-events: none;
            z-index: 1060;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
            max-width: 95vw;
        }
        .admin-bulk-bar.visible {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .admin-bulk-inner {
            display: inline-flex;
            align-items: center;
            background: #0f172a;
            color: #ffffff;
            border-radius: 9999px;
            padding: 8px 14px 8px 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
            gap: 12px;
            flex-wrap: wrap;
            backdrop-filter: blur(8px);
        }
        .bulk-count-badge {
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .bulk-count-num {
            background: var(--admin-primary);
            color: #ffffff;
            font-weight: 800;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 12px;
        }
        .bulk-divider {
            width: 1px;
            height: 22px;
            background: rgba(255, 255, 255, 0.18);
        }
        .bulk-actions-slot {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .bulk-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            background: rgba(255, 255, 255, 0.12);
            color: #f8fafc !important;
            text-decoration: none !important;
        }
        .bulk-action-btn:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff !important;
        }
        .bulk-action-btn.btn-bulk-danger {
            background: #ef4444;
            color: #ffffff !important;
        }
        .bulk-action-btn.btn-bulk-danger:hover {
            background: #dc2626;
        }
        .bulk-action-btn.btn-bulk-success {
            background: #16a34a;
            color: #ffffff !important;
        }
        .bulk-action-btn.btn-bulk-success:hover {
            background: #15803d;
        }
        .bulk-btn-close {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 50%;
            transition: color 0.15s, background 0.15s;
        }
        .bulk-btn-close:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.15);
        }

        /* Card select checkbox on media cards */
        .uni-card-select {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 10;
            cursor: pointer;
            margin: 0;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.7);
            border-radius: 4px;
            padding: 3px;
            backdrop-filter: blur(2px);
        }
        .uni-card-select input[type=checkbox] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--admin-primary);
            margin: 0;
        }
        .uni-media-card.is-selected {
            outline: 2px solid var(--admin-primary);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.15);
        }
    </style>

    @stack('styles')
</head>

<body>

{{-- Loading overlay --}}
<div id="loading">
    <div class="loading-bars">
        <span></span><span></span><span></span><span></span><span></span>
    </div>
</div>

{{-- ── GLOBAL NAVBAR ── --}}
<nav class="navbar navbar-fixed-top admin-navbar">
    <div class="admin-nav-container">

        {{-- Left: Mobile Drawer Trigger + Brand Logo --}}
        <div class="admin-brand-left">
            <button type="button" class="admin-drawer-toggle" id="adminDrawerToggle" onclick="toggleAdminDrawer()" aria-label="Toggle navigation drawer">
                <i class="fas fa-bars"></i>
            </button>
            <a class="admin-brand-link" href="{{ route('admin.dashboard') }}">
                @if($siteLogo)
                    <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="admin-brand-logo">
                @endif
                <span>{{ $siteName }}</span>
                <span class="admin-brand-badge">Admin</span>
            </a>
        </div>

        {{-- Desktop Navigation (>= 1200px) --}}
        <ul class="admin-desktop-nav">
            <li class="{{ $seg === 'dashboard' ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>

            <li class="dropdown {{ in_array($seg, ['orders','tasks','cancellations-returns']) ? 'active' : '' }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-shopping-cart"></i> Orders <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li class="{{ $seg === 'orders' ? 'active' : '' }}">
                        <a href="{{ url('admin/orders') }}"><i class="fas fa-list"></i> Order Management</a>
                    </li>
                    <li class="{{ $seg === 'cancellations-returns' ? 'active' : '' }}">
                        <a href="{{ url('admin/cancellations-returns') }}"><i class="fas fa-undo"></i> Cancellations &amp; Returns</a>
                    </li>
                    <li class="{{ $seg === 'tasks' ? 'active' : '' }}">
                        <a href="{{ url('admin/tasks') }}"><i class="fas fa-tasks"></i> Operational Tasks</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ in_array($seg, ['categories', 'products', 'inventory', 'providers', 'reviews']) ? 'active' : '' }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-box"></i> Products <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li class="{{ $seg === 'products' ? 'active' : '' }}">
                        <a href="{{ url('admin/products') }}"><i class="fas fa-box-open"></i> All Products</a>
                    </li>
                    <li class="{{ $seg === 'inventory' ? 'active' : '' }}">
                        <a href="{{ route('admin.inventory.index') }}"><i class="fas fa-warehouse"></i> Inventory &amp; Stock Ledger</a>
                    </li>
                    <li class="{{ $seg === 'categories' ? 'active' : '' }}">
                        <a href="{{ url('admin/categories') }}"><i class="fas fa-folder"></i> Categories</a>
                    </li>
                    <li class="{{ $seg === 'providers' ? 'active' : '' }}">
                        <a href="{{ url('admin/providers') }}"><i class="fas fa-truck"></i> Providers</a>
                    </li>
                    <li class="{{ $seg === 'reviews' ? 'active' : '' }}">
                        <a href="{{ route('admin.reviews.index') }}"><i class="fas fa-star"></i> Product Reviews</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ in_array($seg, ['appearance', 'files', 'banners', 'navigation']) ? 'active' : '' }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-paint-brush"></i> Appearance <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li class="{{ request()->is('admin/appearance/theme*') ? 'active' : '' }}">
                        <a href="{{ route('admin.appearance.theme.index') }}"><i class="fas fa-palette text-info"></i> Theme Customizer</a>
                    </li>
                    <li class="{{ request()->is('admin/appearance/files*') ? 'active' : '' }}">
                        <a href="{{ route('admin.appearance.files.index') }}"><i class="fas fa-photo-video text-primary"></i> Files &amp; Media Library</a>
                    </li>
                    <li class="{{ $seg === 'banners' ? 'active' : '' }}">
                        <a href="{{ url('admin/banners') }}"><i class="fas fa-image text-success"></i> Store Banners</a>
                    </li>
                    <li class="{{ request()->is('admin/appearance/sections*') ? 'active' : '' }}">
                        <a href="{{ route('admin.appearance.sections.index') }}"><i class="fas fa-layer-group text-warning"></i> Home Sections</a>
                    </li>
                    <li class="{{ request()->is('admin/appearance/footer*') ? 'active' : '' }}">
                        <a href="{{ route('admin.appearance.footer.index') }}"><i class="fas fa-shoe-prints text-muted"></i> Footer Settings</a>
                    </li>
                    <li class="{{ $seg === 'navigation' ? 'active' : '' }}">
                        <a href="{{ route('admin.navigation.index') }}"><i class="fas fa-compass"></i> Storefront Nav Bar</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ in_array($seg, ['invoices', 'templates', 'payments']) ? 'active' : '' }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-file-invoice-dollar"></i> Finance <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li class="{{ $seg === 'invoices' ? 'active' : '' }}">
                        <a href="{{ route('admin.invoices.index') }}"><i class="fas fa-list-alt"></i> All Invoices</a>
                    </li>
                    <li class="{{ $seg === 'templates' ? 'active' : '' }}">
                        <a href="{{ route('admin.templates.index') }}"><i class="fas fa-palette"></i> Invoice Templates</a>
                    </li>
                    <li class="divider"></li>
                    <li class="{{ $seg === 'payments' ? 'active' : '' }}">
                        <a href="{{ route('admin.payments.index') }}"><i class="fas fa-credit-card"></i> Payment Gateways</a>
                    </li>
                </ul>
            </li>

            <li class="dropdown {{ $seg === 'users' ? 'active' : '' }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-users"></i> Users <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ url('admin/users') }}"><i class="fas fa-user"></i> All Users &amp; Admins</a></li>
                </ul>
            </li>

            <li class="dropdown {{ in_array($seg, ['support','reports','broadcasts','coupons','bonuses','logs']) ? 'active' : '' }}">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-h"></i> More <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="{{ url('admin/support') }}"><i class="fas fa-headset text-info"></i> Support Tickets</a></li>
                    <li class="divider"></li>
                    <li><a href="{{ url('admin/reports') }}"><i class="fas fa-chart-bar"></i> Sales Reports</a></li>
                    <li><a href="{{ url('admin/broadcasts') }}"><i class="fas fa-bullhorn"></i> Broadcasts</a></li>
                    <li><a href="{{ url('admin/coupons') }}"><i class="fas fa-ticket-alt"></i> Discount Coupons</a></li>
                    <li><a href="{{ url('admin/bonuses') }}"><i class="fas fa-gift"></i> Bonuses</a></li>
                    <li><a href="{{ url('admin/logs') }}"><i class="fas fa-clipboard-list"></i> Activity Logs</a></li>
                </ul>
            </li>
        </ul>

        {{-- Right Utilities: Store Preview, Settings, User Profile --}}
        <div class="admin-nav-right">
            <a href="{{ url('/') }}" target="_blank" class="admin-nav-util-btn" title="View Storefront">
                <i class="fas fa-external-link-alt"></i> <span class="util-btn-text">Store</span>
            </a>

            <a href="{{ url('admin/settings') }}" class="admin-nav-util-btn {{ $seg === 'settings' ? 'active' : '' }}" title="Store Settings">
                <i class="fas fa-cog"></i>
            </a>

            <div class="dropdown admin-user-dropdown">
                <a href="#" class="dropdown-toggle admin-user-btn" data-toggle="dropdown">
                    <span class="admin-user-avatar"><i class="fas fa-user"></i></span>
                    <span class="admin-user-name">{{ Str::limit(auth()->user()->name ?? 'Admin', 12) }}</span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li><a href="{{ url('admin/account') }}"><i class="fas fa-user-circle"></i> Profile &amp; Account</a></li>
                    <li><a href="{{ url('admin/settings') }}"><i class="fas fa-sliders-h"></i> System Settings</a></li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" id="nav-logout-btn" onclick="event.preventDefault(); document.getElementById('nav-logout-form').submit();" class="text-danger" title="Sign out of Admin Panel">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </li>
                </ul>
                <form id="nav-logout-form" method="POST" action="{{ route('admin.logout') }}" style="display:none;">
                    @csrf
                </form>
            </div>
        </div>

    </div>
</nav>

{{-- ── OFF-CANVAS MOBILE DRAWER (< 1200px) ── --}}
<div class="admin-drawer-backdrop" id="adminDrawerBackdrop" onclick="closeAdminDrawer()"></div>
<aside class="admin-mobile-drawer" id="adminMobileDrawer" aria-label="Mobile Navigation">
    <div class="drawer-header">
        <div style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-shield-alt text-primary" style="font-size:18px;"></i>
            <span style="font-weight:700; font-size:14px;">{{ $siteName }} Admin</span>
        </div>
        <button type="button" class="drawer-close-btn" onclick="closeAdminDrawer()" aria-label="Close Navigation">&times;</button>
    </div>
    <div class="drawer-body">
        <ul class="drawer-nav-list">
            <li class="{{ $seg === 'dashboard' ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>

            <li>
                <button type="button" class="drawer-accordion-btn" onclick="toggleDrawerSub('users-sub')">
                    <span><i class="fas fa-users"></i> Users</span> <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <ul class="drawer-sub-menu" id="users-sub" style="display:none;">
                    <li><a href="{{ url('admin/users') }}"><i class="fas fa-user"></i> All Users</a></li>
                </ul>
            </li>

            <li>
                <button type="button" class="drawer-accordion-btn" onclick="toggleDrawerSub('orders-sub')">
                    <span><i class="fas fa-shopping-cart"></i> Orders</span> <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <ul class="drawer-sub-menu" id="orders-sub" style="display:none;">
                    <li><a href="{{ url('admin/orders') }}"><i class="fas fa-list"></i> Orders</a></li>
                    <li><a href="{{ url('admin/cancellations-returns') }}"><i class="fas fa-undo"></i> Cancellations &amp; Returns</a></li>
                    <li><a href="{{ url('admin/tasks') }}"><i class="fas fa-tasks"></i> Operational Tasks</a></li>
                </ul>
            </li>

            <li class="{{ $seg === 'support' ? 'active' : '' }}">
                <a href="{{ url('admin/support') }}"><i class="fas fa-headset"></i> Support Tickets</a>
            </li>

            <li>
                <button type="button" class="drawer-accordion-btn" onclick="toggleDrawerSub('products-sub')">
                    <span><i class="fas fa-box"></i> Products &amp; Catalog</span> <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <ul class="drawer-sub-menu" id="products-sub" style="display:none;">
                    <li><a href="{{ url('admin/products') }}"><i class="fas fa-box-open"></i> All Products</a></li>
                    <li><a href="{{ route('admin.inventory.index') }}"><i class="fas fa-warehouse"></i> Inventory Ledger</a></li>
                    <li><a href="{{ url('admin/categories') }}"><i class="fas fa-folder"></i> Categories</a></li>
                    <li><a href="{{ url('admin/providers') }}"><i class="fas fa-truck"></i> Providers</a></li>
                    <li><a href="{{ route('admin.reviews.index') }}"><i class="fas fa-star"></i> Reviews</a></li>
                </ul>
            </li>

            <li>
                <button type="button" class="drawer-accordion-btn" onclick="toggleDrawerSub('appearance-sub')">
                    <span><i class="fas fa-paint-brush"></i> Appearance &amp; Media</span> <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <ul class="drawer-sub-menu" id="appearance-sub" style="display:none;">
                    <li><a href="{{ route('admin.appearance.theme.index') }}"><i class="fas fa-palette"></i> Theme Customizer</a></li>
                    <li><a href="{{ route('admin.appearance.files.index') }}"><i class="fas fa-photo-video"></i> Files &amp; Media</a></li>
                    <li><a href="{{ url('admin/banners') }}"><i class="fas fa-image"></i> Banners</a></li>
                    <li><a href="{{ route('admin.appearance.sections.index') }}"><i class="fas fa-layer-group"></i> Home Sections</a></li>
                    <li><a href="{{ route('admin.appearance.footer.index') }}"><i class="fas fa-shoe-prints"></i> Footer Settings</a></li>
                    <li><a href="{{ route('admin.navigation.index') }}"><i class="fas fa-compass"></i> Storefront Nav Bar</a></li>
                </ul>
            </li>

            <li class="{{ $seg === 'payments' ? 'active' : '' }}">
                <a href="{{ route('admin.payments.index') }}"><i class="fas fa-credit-card"></i> Payments</a>
            </li>

            <li>
                <button type="button" class="drawer-accordion-btn" onclick="toggleDrawerSub('invoices-sub')">
                    <span><i class="fas fa-file-invoice-dollar"></i> Invoices</span> <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <ul class="drawer-sub-menu" id="invoices-sub" style="display:none;">
                    <li><a href="{{ route('admin.invoices.index') }}"><i class="fas fa-list-alt"></i> All Invoices</a></li>
                    <li><a href="{{ route('admin.templates.index') }}"><i class="fas fa-palette"></i> Templates</a></li>
                </ul>
            </li>

            <li>
                <button type="button" class="drawer-accordion-btn" onclick="toggleDrawerSub('more-sub')">
                    <span><i class="fas fa-ellipsis-h"></i> Marketing &amp; Logs</span> <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <ul class="drawer-sub-menu" id="more-sub" style="display:none;">
                    <li><a href="{{ url('admin/reports') }}"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="{{ url('admin/broadcasts') }}"><i class="fas fa-bullhorn"></i> Broadcasts</a></li>
                    <li><a href="{{ url('admin/coupons') }}"><i class="fas fa-ticket-alt"></i> Coupons</a></li>
                    <li><a href="{{ url('admin/bonuses') }}"><i class="fas fa-gift"></i> Bonuses</a></li>
                    <li><a href="{{ url('admin/logs') }}"><i class="fas fa-clipboard-list"></i> User Logs</a></li>
                </ul>
            </li>

            <li class="{{ $seg === 'settings' ? 'active' : '' }}">
                <a href="{{ url('admin/settings') }}"><i class="fas fa-cog"></i> Settings</a>
            </li>

            <li class="{{ $seg === 'account' ? 'active' : '' }}">
                <a href="{{ url('admin/account') }}"><i class="fas fa-user-circle"></i> {{ auth()->user()->name ?? 'Account' }}</a>
            </li>

            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('nav-logout-form').submit();" style="color:#ef4444 !important;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</aside>

{{-- ── PAGE CONTENT ── --}}
<main class="admin-content">
    @yield('content')
</main>

{{-- ── UNIVERSAL IMAGE PREVIEW MODAL ── --}}
<div class="modal fade" id="uniPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                <h4 class="modal-title" id="uniPreviewTitle" style="font-size:14px; font-weight:700; margin:0; word-break:break-all;">Image Preview</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size:24px; line-height:1;">&times;</button>
            </div>
            <div class="modal-body text-center" style="padding:24px; background:#0f172a; min-height:260px; display:flex; align-items:center; justify-content:center;">
                <img id="uniPreviewImg" src="" alt="preview" style="max-height:68vh; max-width:100%; object-fit:contain; border-radius:4px; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
            </div>
            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                <div id="uniPreviewMeta" style="font-size:12px; color:#64748b; text-align:left;"></div>
                <div>
                    <button type="button" class="btn btn-default btn-sm" id="uniPreviewCopyBtn" onclick="copyMediaUrl($('#uniPreviewImg').attr('src'), this)">
                        <i class="fas fa-link"></i> Copy URL
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── UNIVERSAL REPLACE IMAGE MODAL ── --}}
<div class="modal fade" id="uniReplaceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="font-size:14px; font-weight:700;"><i class="fas fa-exchange-alt text-primary"></i> Replace Image</h4>
            </div>
            <form id="uniReplaceForm" onsubmit="submitUniReplace(event)" enctype="multipart/form-data">
                <input type="hidden" id="uniReplaceId" value="">
                <input type="hidden" id="uniReplaceType" value="media">
                <div class="modal-body" style="padding:18px;">
                    <p style="font-size:12.5px; color:#64748b; margin-bottom:14px;">
                        Replacing <strong id="uniReplaceName" style="color:#0f172a;"></strong> will automatically update all references across Banners, Categories, Products, and Store Settings.
                    </p>
                    <div class="form-group">
                        <label style="font-weight:600; font-size:12px;">Choose New Image File (JPG, PNG, WebP, max 20MB)</label>
                        <input type="file" name="image" id="uniReplaceInput" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" required onchange="previewUniReplace(this)">
                    </div>
                    <div id="uniReplacePreviewWrap" style="display:none; text-align:center; padding:12px; background:#f1f5f9; border-radius:6px;">
                        <span style="font-size:11px; color:#64748b; display:block; margin-bottom:6px;">New Image Preview:</span>
                        <img id="uniReplacePreviewImg" src="" style="max-height:160px; max-width:100%; border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="uniReplaceSubmitBtn"><i class="fas fa-check"></i> Upload &amp; Replace</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="adminToastWrap"></div>

{{-- ── UNIVERSAL FLOATING BULK ACTIONS BAR ── --}}
<div id="adminBulkBar" class="admin-bulk-bar" role="region" aria-label="Bulk actions toolbar">
    <div class="admin-bulk-inner">
        <div class="bulk-count-badge">
            <span class="bulk-count-num" id="bulkSelectedCount">0</span> selected
        </div>
        <div class="bulk-divider"></div>
        <div class="bulk-actions-slot" id="bulkBarActions">
            {{-- Dynamic bulk action buttons will be injected or activated by the active view --}}
        </div>
        <div class="bulk-divider"></div>
        <button type="button" class="bulk-btn-close" id="bulkDeselectAllBtn" onclick="deselectAllBulk()" title="Clear Selection" aria-label="Clear Selection">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

{{-- ── SCRIPTS ── --}}
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.js"></script>

<script>
    // Setup CSRF token for all jQuery AJAX requests globally
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Loading overlay */
    $(window).on('load', function () { $('#loading').fadeOut(300); });
    if (document.readyState === 'complete') { $('#loading').fadeOut(300); }
    setTimeout(function () { $('#loading').fadeOut(300); }, 3500);

    /* Logout button */
    document.getElementById('nav-logout-btn')?.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('nav-logout-form').submit();
    });

    /* Drawer Toggle & Accessibility */
    function toggleAdminDrawer() {
        var drawer = document.getElementById('adminMobileDrawer');
        var backdrop = document.getElementById('adminDrawerBackdrop');
        if (drawer.classList.contains('open')) {
            closeAdminDrawer();
        } else {
            drawer.classList.add('open');
            backdrop.classList.add('show');
            document.getElementById('adminDrawerToggle')?.setAttribute('aria-expanded', 'true');
        }
    }

    function closeAdminDrawer() {
        document.getElementById('adminMobileDrawer')?.classList.remove('open');
        document.getElementById('adminDrawerBackdrop')?.classList.remove('show');
        document.getElementById('adminDrawerToggle')?.setAttribute('aria-expanded', 'false');
    }

    function toggleDrawerSub(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
        }
    }

    // Close drawer on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAdminDrawer();
        }
    });

    /* Toast Notification Helper */
    function adminToast(msg, type) {
        type = type || 'success';
        var toast = document.createElement('div');
        toast.className = 'admin-toast admin-toast-' + type;
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        toast.innerHTML = '<i class="fas ' + icon + '"></i> <span>' + msg + '</span>';
        document.getElementById('adminToastWrap').appendChild(toast);
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3600);
    }

    /* Universal Clipboard Copy */
    function copyMediaUrl(url, btn) {
        if (!url) return;
        var fullUrl = url.indexOf('http') === 0 ? url : (window.location.origin + '/' + url.replace(/^\//, ''));
        navigator.clipboard.writeText(fullUrl).then(function () {
            adminToast('URL copied to clipboard!');
            if (btn) {
                var orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-success"></i> Copied!';
                setTimeout(function () { btn.innerHTML = orig; }, 1800);
            }
        }).catch(function () {
            var temp = document.createElement('input');
            temp.value = fullUrl;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            adminToast('URL copied to clipboard!');
        });
    }

    /* Universal Preview Modal */
    function openUniversalPreview(url, name, size, dims, folder) {
        if (!url) return;
        $('#uniPreviewTitle').text(name || 'Media Preview');
        $('#uniPreviewImg').attr('src', url);
        var meta = [];
        if (dims) meta.push('<i class="fas fa-vector-square"></i> ' + dims);
        if (size) meta.push('<i class="fas fa-hdd"></i> ' + size);
        if (folder) meta.push('<i class="fas fa-folder"></i> ' + folder);
        $('#uniPreviewMeta').html(meta.join(' &nbsp;|&nbsp; '));
        $('#uniPreviewModal').modal('show');
    }

    /* Universal Replace Image Modal */
    function openReplaceModal(id, name, url, type) {
        $('#uniReplaceId').val(id);
        $('#uniReplaceType').val(type || 'media');
        $('#uniReplaceName').text(name || 'item #' + id);
        $('#uniReplaceInput').val('');
        $('#uniReplacePreviewWrap').hide();
        $('#uniReplaceModal').modal('show');
    }

    function previewUniReplace(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#uniReplacePreviewImg').attr('src', e.target.result);
                $('#uniReplacePreviewWrap').show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitUniReplace(e) {
        e.preventDefault();
        var id = $('#uniReplaceId').val();
        var type = $('#uniReplaceType').val();
        var fileInput = document.getElementById('uniReplaceInput');

        if (!fileInput.files || !fileInput.files[0]) {
            adminToast('Please select a file to replace.', 'error');
            return;
        }

        var fd = new FormData();
        fd.append('_token', $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}');
        fd.append('image', fileInput.files[0]);
        fd.append('file', fileInput.files[0]);

        var $btn = $('#uniReplaceSubmitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Replacing…');

        var endpoint = type === 'banner'
            ? '{{ url("admin/banners") }}/' + id
            : '{{ url("admin/appearance/files") }}/' + id + '/replace';

        $.ajax({
            url: endpoint,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
            },
            success: function (res) {
                adminToast(res.message || 'Image replaced successfully!');
                $('#uniReplaceModal').modal('hide');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to replace image.';
                adminToast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Upload &amp; Replace');
            }
        });
    }

    /* Universal Image Assignment (Use As) */
    function executeUseAs(id, action, name) {
        var token = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
        $.ajax({
            url: '{{ route("admin.appearance.files.set-as") }}',
            method: 'POST',
            data: { 
                _token: token,
                file_id: id, 
                action: action 
            },
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function (res) {
                adminToast(res.message || 'Asset applied successfully!');
                if (action === 'logo' && res.url) {
                    $('#active-logo-preview').attr('src', res.url);
                    $('.admin-brand-logo').attr('src', res.url);
                }
                if (action === 'favicon' && res.url) {
                    $('#active-fav-preview').attr('src', res.url);
                    $('link[rel="shortcut icon"], link[rel="icon"], link[rel="apple-touch-icon"]').attr('href', res.url);
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to set asset.';
                adminToast(msg, 'error');
            }
        });
    }

    /* Universal Soft Delete */
    function deleteMediaItem(id, type, name) {
        if (!confirm('Move "' + (name || 'this item') + '" to Trash?')) return;
        var endpoint = type === 'banner'
            ? '{{ url("admin/banners") }}/' + id
            : '{{ url("admin/appearance/files") }}/' + id;

        $.ajax({
            url: endpoint,
            method: 'DELETE',
            success: function (res) {
                adminToast(res.message || 'Moved to Trash.');
                $('#media-card-' + type + '-' + id).fadeOut(300, function () { $(this).remove(); });
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to delete item.';
                adminToast(msg, 'error');
            }
        });
    }

    /* Universal Restore */
    function restoreMediaItem(id, type, name) {
        var endpoint = type === 'banner'
            ? '{{ url("admin/banners") }}/' + id + '/restore'
            : '{{ url("admin/appearance/files") }}/' + id + '/restore';

        $.ajax({
            url: endpoint,
            method: 'POST',
            success: function (res) {
                adminToast(res.message || 'Restored from Trash.');
                setTimeout(function () { location.reload(); }, 500);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to restore item.';
                adminToast(msg, 'error');
            }
        });
    }

    /* Universal Permanent Delete with Reference Protection & Force Detach Option */
    function forceDeleteMediaItem(id, type, name, isForce) {
        if (!isForce) {
            if (!confirm('PERMANENT DELETION: Are you sure you want to permanently delete "' + (name || 'this item') + '"? This action CANNOT be undone!')) return;
        }
        var endpoint = type === 'banner'
            ? '{{ url("admin/banners") }}/' + id + '/force'
            : '{{ url("admin/appearance/files") }}/' + id + '/force' + (isForce ? '?force=1' : '');

        $.ajax({
            url: endpoint,
            method: 'DELETE',
            success: function (res) {
                adminToast(res.message || 'Permanently deleted.');
                $('#media-card-' + type + '-' + id).fadeOut(300, function () { $(this).remove(); });
            },
            error: function (xhr) {
                var data = xhr.responseJSON || {};
                var msg = data.message || 'Failed to permanently delete item.';
                if (data.is_referenced && !isForce) {
                    if (confirm(msg + '\n\nWould you like to FORCE DELETE this file anyway and detach it from all products/categories?')) {
                        forceDeleteMediaItem(id, type, name, true);
                        return;
                    }
                }
                adminToast(msg, 'error');
            }
        });
    }

    /* Viewport Boundary Guard for Dropdowns */
    $(document).on('shown.bs.dropdown', '.dropdown', function () {
        var menu = $(this).children('.dropdown-menu');
        if (menu.length) {
            var rect = menu[0].getBoundingClientRect();
            if (rect.right > window.innerWidth - 10) {
                menu.addClass('dropdown-menu-right');
            }
        }
    });

    /* ── Universal Bulk Action Framework ── */
    function updateBulkBar() {
        var checked = $('.bulk-item-check:checked');
        var count = checked.length;
        var total = $('.bulk-item-check').length;

        $('#bulkSelectedCount').text(count);

        if (count > 0) {
            $('#adminBulkBar').addClass('visible');
        } else {
            $('#adminBulkBar').removeClass('visible');
        }

        // Master checkbox synchronization
        var $master = $('#bulkMasterCheck');
        if ($master.length) {
            if (count === 0) {
                $master.prop('checked', false).prop('indeterminate', false);
            } else if (count === total && total > 0) {
                $master.prop('checked', true).prop('indeterminate', false);
            } else {
                $master.prop('checked', false).prop('indeterminate', true);
            }
        }

        // Toggle card selected class if in card view
        $('.bulk-item-check').each(function () {
            var $card = $(this).closest('.uni-media-card');
            if ($card.length) {
                $card.toggleClass('is-selected', this.checked);
            }
        });
    }

    function getSelectedBulkIds() {
        var ids = [];
        $('.bulk-item-check:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function deselectAllBulk() {
        $('.bulk-item-check').prop('checked', false);
        $('#bulkMasterCheck').prop('checked', false).prop('indeterminate', false);
        updateBulkBar();
    }

    // Master checkbox click handler
    $(document).on('change', '#bulkMasterCheck', function () {
        var isChecked = this.checked;
        $('.bulk-item-check').prop('checked', isChecked);
        updateBulkBar();
    });

    // Single checkbox click handler
    $(document).on('change', '.bulk-item-check', function () {
        updateBulkBar();
    });

    // Universal bulk action executor
    function runBulkAction(url, action, extraData, confirmMsg, onSuccess) {
        var ids = getSelectedBulkIds();
        if (!ids.length) {
            adminToast('Please select at least one item.', 'error');
            return;
        }

        if (confirmMsg) {
            var formattedMsg = confirmMsg.replace('{count}', ids.length);
            if (!confirm(formattedMsg)) {
                return;
            }
        }

        var payload = Object.assign({ ids: ids, action: action }, extraData || {});

        var $bar = $('#adminBulkBar');
        $bar.find('button, select').prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: payload,
            success: function (res) {
                adminToast(res.message || 'Bulk operation completed.');
                if (typeof onSuccess === 'function') {
                    onSuccess(res);
                } else {
                    setTimeout(function () { location.reload(); }, 600);
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Bulk action failed.';
                adminToast(msg, 'error');
            },
            complete: function () {
                $bar.find('button, select').prop('disabled', false);
            }
        });
    }
</script>

@stack('scripts')
</body>
</html>
