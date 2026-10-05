@extends('install.layout', ['step' => 4])

@section('title', 'Database Migrations & Schema')
@section('page_heading', 'Database Schema & Initialization')
@section('page_subheading', 'Create the database tables, default catalog structures, and payment gateways.')

@section('content')
<div class="card-header">
    <div>
        <h2 class="card-title">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                <line x1="6" y1="6" x2="6.01" y2="6"></line>
                <line x1="6" y1="18" x2="6.01" y2="18"></line>
            </svg>
            <span>Execute Schema Migrations</span>
        </h2>
        <p class="card-subtitle">Synchronize all database tables, columns, indexes, and initial configurations.</p>
    </div>
</div>

<form id="migrationForm" onsubmit="event.preventDefault(); startMigrations();">
    @csrf

    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-subtle); border-radius: 0.85rem; padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 1rem;">Setup Options</h3>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <label style="display: flex; align-items: flex-start; gap: 0.85rem; cursor: pointer;">
                <input type="checkbox" name="seed_demo" id="seed_demo" checked style="margin-top: 0.25rem; accent-color: var(--primary); width: 18px; height: 18px;">
                <div>
                    <strong style="color: #ffffff; font-size: 0.9rem; display: block;">Seed Essential Gateway & Store Data</strong>
                    <span style="color: var(--text-muted); font-size: 0.82rem;">Seeds payment gateway records (Razorpay, Cashfree, UPI, COD) and default invoice templates.</span>
                </div>
            </label>

            <label style="display: flex; align-items: flex-start; gap: 0.85rem; cursor: pointer;">
                <input type="checkbox" name="fresh" id="fresh" style="margin-top: 0.25rem; accent-color: #ef4444; width: 18px; height: 18px;">
                <div>
                    <strong style="color: #ffffff; font-size: 0.9rem; display: block;">Fresh Migration (Drop existing tables first)</strong>
                    <span style="color: #f87171; font-size: 0.82rem;">Warning: If checked, any existing tables in this database will be dropped.</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Live Execution Status & Terminal -->
    <div id="executionPanel" style="display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span id="migrationStatusDot" class="pulse-dot" style="background: #38bdf8; box-shadow: 0 0 10px #38bdf8;"></span>
                <span id="migrationStatusText" style="font-weight: 600; font-size: 0.875rem; color: #e2e8f0;">Running migrations...</span>
            </div>
            <span id="migrationTimer" style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">0s</span>
        </div>

        <div class="terminal-box" id="migrationLog">Waiting for Artisan output...</div>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.database') }}" class="btn btn-secondary" id="btnBack">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back</span>
        </a>

        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" id="btnRunMigrate" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                <span>Execute Migrations</span>
            </button>

            <a href="{{ route('install.admin') }}" id="btnNext" class="btn btn-success" style="display: none;">
                <span>Continue to Admin Setup</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
let seconds = 0;
let timerInterval = null;

async function startMigrations() {
    const btnRun = document.getElementById('btnRunMigrate');
    const btnBack = document.getElementById('btnBack');
    const btnNext = document.getElementById('btnNext');
    const panel = document.getElementById('executionPanel');
    const log = document.getElementById('migrationLog');
    const statusText = document.getElementById('migrationStatusText');
    const statusDot = document.getElementById('migrationStatusDot');
    const timer = document.getElementById('migrationTimer');

    btnRun.disabled = true;
    btnBack.style.pointerEvents = 'none';
    btnBack.style.opacity = '0.5';
    panel.style.display = 'block';

    log.textContent = "> php artisan migrate --force\nInitializing migration process...\n";
    statusText.innerText = "Executing database schema migrations...";

    seconds = 0;
    timerInterval = setInterval(() => {
        seconds++;
        timer.innerText = `${seconds}s`;
    }, 1000);

    const payload = {
        fresh: document.getElementById('fresh').checked,
        seed_demo: document.getElementById('seed_demo').checked,
    };

    try {
        const response = await fetch("{{ route('install.migrations.run') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        clearInterval(timerInterval);
        const data = await response.json();

        if (data.success) {
            statusDot.style.background = '#10b981';
            statusDot.style.boxShadow = '0 0 10px #10b981';
            statusText.innerHTML = '<span style="color: #6ee7b7;">✓ Migrations and essential seeds completed successfully!</span>';
            log.textContent = (data.output || '') + "\n\n✓ Database synchronization complete. Ready for Admin Setup.";
            
            btnRun.style.display = 'none';
            btnNext.style.display = 'inline-flex';
        } else {
            statusDot.style.background = '#ef4444';
            statusDot.style.boxShadow = '0 0 10px #ef4444';
            statusText.innerHTML = '<span style="color: #fca5a5;">✗ Migration failed</span>';
            log.textContent += "\n\n[ERROR]: " + (data.message || 'Unknown migration error.');
            btnRun.disabled = false;
            btnRun.innerHTML = '<span>Retry Migrations</span>';
        }
    } catch (err) {
        clearInterval(timerInterval);
        statusDot.style.background = '#ef4444';
        statusText.innerHTML = '<span style="color: #fca5a5;">✗ Network or server error</span>';
        log.textContent += "\n\n[ERROR]: Failed to receive response from server. Check server logs.";
        btnRun.disabled = false;
        btnRun.innerHTML = '<span>Retry Migrations</span>';
    } finally {
        btnBack.style.pointerEvents = 'auto';
        btnBack.style.opacity = '1';
    }
}
</script>
@endsection
