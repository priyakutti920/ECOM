@extends('install.layout', ['step' => 6])

@section('title', 'Installation Complete!')
@section('page_heading', 'Setup Successfully Completed!')
@section('page_subheading', 'Congratulations! Your Laravel E-Commerce application is fully configured and ready for live operation.')

@section('content')
<div style="text-align: center; padding: 1.5rem 0 1rem;">
    <!-- Celebration Icon -->
    <div style="width: 84px; height: 84px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4); margin-bottom: 1.5rem; animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>

    <h2 style="font-size: 1.75rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">You are all set!</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 540px; margin: 0 auto 2rem; line-height: 1.6;">
        Your database tables have been provisioned, system cache has been optimized, and installer lock has been engaged.
    </p>

    <!-- Quick Status Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: left; margin-bottom: 2.25rem;">
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.75rem; padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Security Lock</div>
            <div style="display: flex; align-items: center; gap: 0.4rem; color: #34d399; font-weight: 700; margin-top: 0.35rem; font-size: 0.92rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>storage/installed active</span>
            </div>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.75rem; padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Super Administrator</div>
            <div style="color: #ffffff; font-weight: 700; margin-top: 0.35rem; font-size: 0.92rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ auth()->user()?->email ?? 'Configured' }}
            </div>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.75rem; padding: 1rem 1.25rem;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">Environment</div>
            <div style="color: #818cf8; font-weight: 700; margin-top: 0.35rem; font-size: 0.92rem;">
                {{ config('app.env', 'production') }}
            </div>
        </div>
    </div>

    <!-- Direct Launch Action Buttons -->
    <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="padding: 0.85rem 1.85rem; font-size: 1rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Open Admin Panel</span>
        </a>

        <a href="{{ route('shop.home') }}" class="btn btn-secondary" style="padding: 0.85rem 1.85rem; font-size: 1rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span>View Public Storefront</span>
        </a>
    </div>

    <!-- Security Recommendation -->
    <div style="margin-top: 2.5rem; text-align: left; background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 0.85rem; padding: 1.25rem;">
        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" style="flex-shrink: 0; margin-top: 0.1rem;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.5;">
                <strong style="color: #ffffff;">Need to re-run or reset the installer later?</strong><br>
                For security reasons, the installer is automatically locked. If you ever need to reset and run this wizard again in development or staging, run <code>php artisan app:reset-install</code> from your terminal.
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}
</style>
@endsection
