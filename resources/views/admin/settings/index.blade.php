@extends('layouts.admin')

@section('title', 'Settings')

@php
    $currentSettingName = 'Store Settings';
    if (request()->routeIs('admin.settings.store')) $currentSettingName = 'Store Settings';
    elseif (request()->routeIs('admin.settings.features')) $currentSettingName = 'Trust Features';
    elseif (request()->routeIs('admin.navigation.*')) $currentSettingName = 'Navigation Bar';
    elseif (request()->routeIs('admin.settings.seo')) $currentSettingName = 'SEO Settings';
    elseif (request()->routeIs('admin.settings.social')) $currentSettingName = 'Social Links';
    elseif (request()->routeIs('admin.settings.whatsapp')) $currentSettingName = 'WhatsApp Notifications';
    elseif (request()->routeIs('admin.settings.smtp')) $currentSettingName = 'SMTP Settings';
    elseif (request()->routeIs('admin.settings.plugin')) $currentSettingName = 'Plugin Settings';
    elseif (request()->routeIs('admin.settings.payment')) $currentSettingName = 'Payment Settings';
    elseif (request()->routeIs('admin.settings.tax')) $currentSettingName = 'Tax & Class';
    elseif (request()->routeIs('admin.templates.*')) $currentSettingName = 'Billing & Invoices';
    elseif (request()->routeIs('admin.account')) $currentSettingName = 'Account Settings';
@endphp

@push('styles')
<style>
    body { background: #f0f2f5 !important; }
    .admin-content { padding: 0 !important; }

    .settings-page {
        background: #fff;
        max-width: 1140px;
        width: 100%;
        margin: 16px auto;
        padding: 24px 28px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        box-sizing: border-box;
    }

    .settings-layout {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        width: 100%;
        box-sizing: border-box;
    }

    .settings-sidebar {
        width: 230px;
        flex-shrink: 0;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 0;
        position: sticky;
        top: 68px; /* Sticks cleanly below fixed 52px navbar with 16px space */
        z-index: 10;
    }

    .settings-sidebar-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #888;
        padding: 6px 16px 4px;
    }

    .settings-nav-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 500;
        color: #555;
        cursor: pointer;
        transition: all .15s;
        border-left: 3px solid transparent;
        text-decoration: none;
    }

    .settings-nav-item:hover {
        background: #f0f0f0;
        color: #333;
    }

    .settings-nav-item.active {
        background: #f0ebff;
        color: #7b1fa2;
        border-left-color: #7b1fa2;
        font-weight: 600;
    }

    .settings-nav-item i { width: 16px; text-align: center; font-size: 13px; }

    .settings-nav-item .badge-soon {
        margin-left: auto;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        background: #e0e0e0;
        color: #888;
        padding: 1px 5px;
        border-radius: 3px;
    }

    .settings-nav-item.active .badge-soon {
        background: #d1b8f0;
        color: #6a1b9a;
    }

    .settings-content {
        flex: 1;
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
    }

    .settings-card {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 20px 24px;
    }

    .settings-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #222;
        margin: 0 0 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }

    .settings-card-title i {
        margin-right: 6px;
        color: #7b1fa2;
    }

    .settings-empty {
        text-align: center;
        padding: 48px 24px;
        color: #aaa;
    }

    .settings-empty i { font-size: 40px; margin-bottom: 12px; display: block; }
    .settings-empty p { font-size: 14px; margin: 0; }

    /* Mobile settings menu bar */
    .settings-mobile-menu-btn {
        display: none;
        width: 100%;
        padding: 12px 16px;
        background: #f8f9fa;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        font-size: 13.5px;
        color: #1e293b;
        transition: background 0.15s, border-color 0.15s;
        box-sizing: border-box;
        text-align: left;
    }
    .settings-mobile-menu-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }
    .settings-mobile-menu-btn .sm-btn-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .settings-mobile-menu-btn .sm-btn-left i {
        color: #7b1fa2;
        font-size: 15px;
    }
    .settings-mobile-menu-btn .sm-btn-badge {
        background: #f0ebff;
        color: #7b1fa2;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 4px;
        font-size: 12.5px;
        margin-left: 4px;
    }
    .settings-mobile-menu-btn .sm-btn-arrow {
        color: #64748b;
        font-size: 13px;
        transition: transform 0.25s ease;
    }

    @media (max-width: 991px) {
        .settings-page {
            margin: 8px auto;
            padding: 14px 12px;
            border-radius: 0;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow-x: hidden;
        }
        .settings-layout {
            flex-direction: column;
            gap: 16px;
            align-items: stretch !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .settings-sidebar {
            width: 100% !important;
            max-width: 100% !important;
            position: static !important; /* CRITICAL: Never sticky in mobile column flow */
            top: auto !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
        }
        .settings-mobile-menu-btn {
            display: flex;
            width: 100%;
            box-sizing: border-box;
        }
        .settings-sidebar-body {
            display: none;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 0;
            margin-top: 8px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            width: 100%;
            box-sizing: border-box;
        }
        .settings-sidebar-body.open {
            display: block;
            animation: settingsFadeIn 0.2s ease;
        }
        @keyframes settingsFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .settings-content {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
    }

    @media (max-width: 576px) {
        .settings-page {
            margin: 4px auto;
            padding: 10px 8px;
        }
    }

    @media (max-width: 360px) {
        .settings-page {
            margin: 2px auto;
            padding: 6px 4px;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-page">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <h2 style="margin:0; font-weight:700; font-size:22px; color:#222;">
            <i class="fas fa-cog" style="font-size:18px; margin-right:6px; color:#7b1fa2;"></i> Settings
        </h2>
    </div>

    <div class="settings-layout">

        {{-- Sidebar --}}
        <div class="settings-sidebar">
            {{-- Mobile Toggle Button --}}
            <button type="button" class="settings-mobile-menu-btn" onclick="toggleSettingsSidebarMenu()" aria-expanded="false">
                <span class="sm-btn-left">
                    <i class="fas fa-sliders-h"></i>
                    <span>Setting: <span class="sm-btn-badge">{{ $currentSettingName }}</span></span>
                </span>
                <span class="sm-btn-arrow" id="settingsMenuChevron"><i class="fas fa-chevron-down"></i></span>
            </button>

            <div class="settings-sidebar-body" id="settingsSidebarBody">
                <div class="settings-sidebar-title">Store</div>
                <a href="{{ route('admin.settings.store') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.store') ? 'active' : '' }}">
                    <i class="fas fa-store"></i> Store Settings
                </a>
                <a href="{{ route('admin.settings.features') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.features') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> Trust Features
                </a>
                <a href="{{ route('admin.navigation.index') }}" class="settings-nav-item {{ request()->routeIs('admin.navigation.*') ? 'active' : '' }}">
                    <i class="fas fa-compass"></i> Navigation Bar
                </a>

                <div class="settings-sidebar-title" style="margin-top:8px;">Marketing</div>
                <a href="{{ route('admin.settings.seo') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.seo') ? 'active' : '' }}">
                    <i class="fas fa-search"></i> SEO Settings
                </a>
                <a href="{{ route('admin.settings.social') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.social') ? 'active' : '' }}">
                    <i class="fas fa-share-alt"></i> Social Links
                </a>

                <div class="settings-sidebar-title" style="margin-top:8px;">Notifications & Chat</div>
                <a href="{{ route('admin.settings.whatsapp') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.whatsapp') ? 'active' : '' }}">
                    <i class="fab fa-whatsapp" style="color:#25D366; font-size:14px;"></i> WhatsApp Notifications
                </a>
                <a href="{{ route('admin.settings.smtp') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.smtp') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i> SMTP Settings
                </a>

                <div class="settings-sidebar-title" style="margin-top:8px;">Integrations & Plugins</div>
                <a href="{{ route('admin.settings.plugin') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.plugin') ? 'active' : '' }}">
                    <i class="fas fa-puzzle-piece" style="color:#7b1fa2;"></i> Plugin Settings
                </a>

                <div class="settings-sidebar-title" style="margin-top:8px;">Finance & System</div>
                <a href="{{ route('admin.settings.payment') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.payment') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Payment Settings
                </a>
                <a href="{{ route('admin.settings.tax') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.tax') ? 'active' : '' }}">
                    <i class="fas fa-percentage"></i> Tax &amp; Class
                </a>

            </div>
        </div>

        {{-- Content --}}
        <div class="settings-content">
            @yield('settings_content')
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleSettingsSidebarMenu() {
        var body = document.getElementById('settingsSidebarBody');
        var chevron = document.getElementById('settingsMenuChevron');
        var btn = document.querySelector('.settings-mobile-menu-btn');
        if (!body) return;
        var isOpen = body.classList.toggle('open');
        if (chevron) {
            chevron.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
        }
        if (btn) {
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    }

    // Dismiss settings mobile menu on outside click or Escape
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.settings-sidebar').length) {
            var body = document.getElementById('settingsSidebarBody');
            var chevron = document.getElementById('settingsMenuChevron');
            var btn = document.querySelector('.settings-mobile-menu-btn');
            if (body && body.classList.contains('open')) {
                body.classList.remove('open');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            var body = document.getElementById('settingsSidebarBody');
            var chevron = document.getElementById('settingsMenuChevron');
            var btn = document.querySelector('.settings-mobile-menu-btn');
            if (body && body.classList.contains('open')) {
                body.classList.remove('open');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        }
    });
</script>
@endpush