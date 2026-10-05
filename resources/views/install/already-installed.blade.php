@extends('install.layout', ['step' => 6])

@section('title', 'Application Already Installed')
@section('page_heading', 'Application Already Installed')
@section('page_subheading', 'The setup wizard has already completed and locked for security.')

@section('content')
<div style="text-align: center; padding: 1.5rem 0 1rem;">
    <div style="width: 76px; height: 76px; border-radius: 50%; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>

    <h2 style="font-size: 1.5rem; font-weight: 800; color: #ffffff; margin-bottom: 0.75rem;">Installation Locked</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 520px; margin: 0 auto 2rem; line-height: 1.6;">
        This application is already installed and protected. To prevent unauthorized overwriting of your database and settings, the installer cannot be accessed directly.
    </p>

    <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Go to Admin Panel</span>
        </a>

        <a href="{{ route('shop.home') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>Go to Storefront</span>
        </a>
    </div>

    <div style="text-align: left; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.75rem; padding: 1.25rem;">
        <div style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.6;">
            <strong style="color: #ffffff;">Need to re-install or test the installer again?</strong><br>
            Run the following command in your terminal to unlock the installer:
            <div style="margin-top: 0.5rem; background: #060911; padding: 0.6rem 0.85rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.85rem; color: #a5f3fc; border: 1px solid rgba(255, 255, 255, 0.08);">
                php artisan app:reset-install
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.4rem;">
                Or manually delete the <code>storage/installed</code> file from the root storage folder.
            </div>
        </div>
    </div>
</div>
@endsection
