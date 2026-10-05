@extends('install.layout', ['step' => 1])

@section('title', 'Welcome to Installation')
@section('page_heading', 'Clean Laravel Store Installer')
@section('page_subheading', 'Welcome! This installer guides you through system readiness, database configuration, database migrations, and super administrator setup.')

@section('content')
<div style="text-align: center; padding: 1rem 0 1.5rem;">
    <div style="width: 76px; height: 76px; border-radius: 20px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); margin-bottom: 1.5rem;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
    </div>

    <h2 style="font-size: 1.6rem; font-weight: 800; color: #ffffff; margin-bottom: 0.75rem;">Ready to launch your online store?</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 580px; margin: 0 auto 2rem; line-height: 1.6;">
        The wizard will automatically check your server environment, test your database credentials, generate required application keys, run schema migrations, and provision your super admin account.
    </p>

    <!-- Highlight feature cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; text-align: left; margin-bottom: 2.5rem;">
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.85rem; padding: 1.25rem;">
            <div style="color: #818cf8; font-size: 1.25rem; margin-bottom: 0.5rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.35rem;">System Diagnostic</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">Validates PHP version, extensions, and directory writability before deployment.</p>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.85rem; padding: 1.25rem;">
            <div style="color: #34d399; font-size: 1.25rem; margin-bottom: 0.5rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
            </div>
            <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.35rem;">Live DB Verification</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">Real-time connection tester with one-click automatic database creation.</p>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.85rem; padding: 1.25rem;">
            <div style="color: #f472b6; font-size: 1.25rem; margin-bottom: 0.5rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.35rem;">Admin & Security</h3>
            <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">Initializes cryptographic keys, locks installer, and sets up your super admin credentials.</p>
        </div>
    </div>

    <!-- Action button -->
    <a href="{{ route('install.requirements') }}" class="btn btn-primary" style="padding: 0.9rem 2.25rem; font-size: 1.05rem;">
        <span>Check Server Requirements</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
    </a>
</div>
@endsection
