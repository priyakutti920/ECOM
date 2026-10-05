<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Installation Wizard') — {{ config('app.name', 'Laravel Store') }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #090d16;
            --bg-card: rgba(17, 24, 39, 0.75);
            --bg-card-hover: rgba(31, 41, 55, 0.85);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(99, 102, 241, 0.4);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-glow: rgba(99, 102, 241, 0.25);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.12);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.12);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.12);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-faint: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(168, 85, 247, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.05) 0%, transparent 60%);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        .ambient-glow {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 350px;
            background: radial-gradient(ellipse at top, rgba(99, 102, 241, 0.2), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header & Brand */
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .brand-badge .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #818cf8;
            box-shadow: 0 0 10px #818cf8;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }

        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 50%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .brand-subtitle {
            font-size: 0.95rem;
            color: var(--text-muted);
            max-width: 520px;
            margin: 0 auto;
        }

        /* Stepper Navigation */
        .stepper-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2.25rem;
            position: relative;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-subtle);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            backdrop-filter: blur(12px);
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-faint);
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
            z-index: 2;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-faint);
            transition: all 0.2s ease;
        }

        .step-item.active {
            color: var(--text-main);
        }

        .step-item.active .step-circle {
            background: var(--primary);
            border-color: #818cf8;
            color: #ffffff;
            box-shadow: 0 0 15px var(--primary-glow);
        }

        .step-item.completed {
            color: #cbd5e1;
        }

        .step-item.completed .step-circle {
            background: var(--success-bg);
            border-color: var(--success);
            color: var(--success);
        }

        .step-divider {
            flex: 1;
            height: 2px;
            background: rgba(255, 255, 255, 0.06);
            margin: 0 0.75rem;
        }

        .step-divider.filled {
            background: linear-gradient(90deg, var(--success), var(--primary));
        }

        /* Glass Card */
        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 1.25rem;
            backdrop-filter: blur(16px);
            padding: 2.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            margin-bottom: 2rem;
            position: relative;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        }

        .card-header {
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Alerts & Badges */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            line-height: 1.5;
        }

        .alert-success {
            background: var(--success-bg);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .alert-danger {
            background: var(--danger-bg);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-warning {
            background: var(--warning-bg);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fcd34d;
        }

        /* Form elements */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }

        .col-span-2 {
            grid-column: span 2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .form-label {
            font-size: 0.84rem;
            font-weight: 600;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .form-input, .form-select {
            width: 100%;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border-subtle);
            border-radius: 0.65rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: #ffffff;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            background: rgba(15, 23, 42, 0.95);
        }

        .form-input::placeholder {
            color: #475569;
        }

        /* Buttons */
        .btn-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-subtle);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: inherit;
            font-size: 0.92rem;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.65rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px var(--primary-glow);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            filter: brightness(1.08);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border-subtle);
            color: #cbd5e1;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(99, 102, 241, 0.4);
            color: #a5b4fc;
        }

        .btn-outline:hover {
            background: rgba(99, 102, 241, 0.15);
            color: #ffffff;
        }

        /* Tables & Lists */
        .status-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1.5rem;
        }

        .status-table tr:not(:last-child) td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .status-table td {
            padding: 0.85rem 0.5rem;
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-success {
            background: var(--success-bg);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-danger {
            background: var(--danger-bg);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-warning {
            background: var(--warning-bg);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        /* Footer */
        .installer-footer {
            text-align: center;
            font-size: 0.8125rem;
            color: var(--text-faint);
            margin-top: auto;
            padding-top: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
        }

        .installer-footer a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .installer-footer a:hover {
            color: #ffffff;
        }

        /* Terminal Console */
        .terminal-box {
            background: #060911;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            padding: 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.825rem;
            color: #a5f3fc;
            max-height: 280px;
            overflow-y: auto;
            white-space: pre-wrap;
            line-height: 1.6;
            margin: 1.5rem 0;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stepper-nav {
                display: none;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .col-span-2 {
                grid-column: span 1;
            }
            .glass-card {
                padding: 1.5rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="ambient-glow"></div>

    <div class="container">
        <!-- Brand Header -->
        <header class="brand-header">
            <div class="brand-badge">
                <span class="pulse-dot"></span>
                <span>Laravel Installer v12.0</span>
            </div>
            <h1 class="brand-title">@yield('page_heading', 'E-Commerce Setup Wizard')</h1>
            <p class="brand-subtitle">@yield('page_subheading', 'Deploy, configure, and initialize your production-ready store in a few clicks.')</p>
        </header>

        <!-- Stepper Navigation -->
        @php
            $currentStep = $step ?? 1;
            $steps = [
                1 => ['label' => 'Welcome', 'icon' => '1'],
                2 => ['label' => 'Requirements', 'icon' => '2'],
                3 => ['label' => 'Database', 'icon' => '3'],
                4 => ['label' => 'Migrations', 'icon' => '4'],
                5 => ['label' => 'Admin Account', 'icon' => '5'],
                6 => ['label' => 'Ready', 'icon' => '6'],
            ];
        @endphp

        <nav class="stepper-nav" aria-label="Installation Progress">
            @foreach($steps as $sNum => $sInfo)
                @php
                    $isCompleted = $sNum < $currentStep;
                    $isActive = $sNum === $currentStep;
                    $class = $isActive ? 'active' : ($isCompleted ? 'completed' : '');
                @endphp
                <div class="step-item {{ $class }}">
                    <div class="step-circle">
                        @if($isCompleted)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        @else
                            {{ $sNum }}
                        @endif
                    </div>
                    <span>{{ $sInfo['label'] }}</span>
                </div>
                @if(!$loop->last)
                    <div class="step-divider {{ $sNum < $currentStep ? 'filled' : '' }}"></div>
                @endif
            @endforeach
        </nav>

        <!-- Flash alerts -->
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <strong>Configuration issues detected:</strong>
                    <ul style="margin-top: 0.35rem; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Card Content -->
        <main class="glass-card">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="installer-footer">
            <span>PHP {{ PHP_VERSION }}</span>
            <span>•</span>
            <span>Laravel {{ app()->version() }}</span>
            <span>•</span>
            <span>CLI Companion: <code>php artisan app:install</code></span>
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
