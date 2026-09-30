@extends('layouts.admin')

@section('title', 'Settings')

@push('styles')
<style>
    body { background: #f0f2f5 !important; }
    .admin-content { padding: 0 !important; }

    .settings-page {
        background: #fff;
        max-width: 1140px;
        margin: 16px auto;
        padding: 24px 28px;
    }

    .settings-layout {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    .settings-sidebar {
        width: 220px;
        flex-shrink: 0;
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 8px 0;
        position: sticky;
        top: 16px;
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

    @media (max-width: 768px) {
        .settings-layout { flex-direction: column; }
        .settings-sidebar { width: 100%; }
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

            <div class="settings-sidebar-title" style="margin-top:8px;">System</div>
            <a href="{{ route('admin.settings.plugin') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.plugin') ? 'active' : '' }}">
                <i class="fas fa-puzzle-piece"></i> Plugin Settings
            </a>
            <a href="{{ route('admin.settings.payment') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.payment') ? 'active' : '' }}">
                <i class="fas fa-credit-card"></i> Payment Settings
            </a>
            <a href="{{ route('admin.settings.smtp') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.smtp') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i> SMTP Settings
            </a>
            <a href="{{ route('admin.settings.tax') }}" class="settings-nav-item {{ request()->routeIs('admin.settings.tax') ? 'active' : '' }}">
                <i class="fas fa-percentage"></i> Tax &amp; Class
            </a>
        </div>

        {{-- Content --}}
        <div class="settings-content">
            @yield('settings_content')
        </div>

    </div>
</div>
@endsection