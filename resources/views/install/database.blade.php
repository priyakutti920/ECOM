@extends('install.layout', ['step' => 3])

@section('title', 'Database & Environment Setup')
@section('page_heading', 'Environment & Database Configuration')
@section('page_subheading', 'Define your application parameters and connect to your database.')

@section('content')
<div class="card-header">
    <div>
        <h2 class="card-title">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
            </svg>
            <span>Connection Parameters</span>
        </h2>
        <p class="card-subtitle">These parameters are securely written to your <code>.env</code> file.</p>
    </div>
</div>

<form action="{{ route('install.database.save') }}" method="POST" id="dbConfigForm">
    @csrf

    <!-- Application Meta Settings -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">
            1. Application Settings
        </h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="app_name">
                    <span>Store / Application Name</span>
                </label>
                <input type="text" name="app_name" id="app_name" class="form-input" 
                       value="{{ old('app_name', $currentConfig['app_name']) }}" required placeholder="e.g. My Online Store">
                <span class="form-hint">Displayed across emails, storefront title, and invoices.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="app_url">
                    <span>Application URL</span>
                </label>
                <input type="url" name="app_url" id="app_url" class="form-input" 
                       value="{{ old('app_url', $currentConfig['app_url']) }}" required placeholder="https://example.com">
                <span class="form-hint">Base URL used for generating canonical assets and links.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="app_env">
                    <span>Environment Mode</span>
                </label>
                <select name="app_env" id="app_env" class="form-select">
                    <option value="production" {{ old('app_env', $currentConfig['app_env']) === 'production' ? 'selected' : '' }}>Production (Recommended for live)</option>
                    <option value="local" {{ old('app_env', $currentConfig['app_env']) === 'local' ? 'selected' : '' }}>Local (Development & Testing)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="app_debug">
                    <span>Debug Mode</span>
                </label>
                <select name="app_debug" id="app_debug" class="form-select">
                    <option value="0" {{ old('app_debug', $currentConfig['app_debug']) ? '' : 'selected' }}>Disabled (Secure for production)</option>
                    <option value="1" {{ old('app_debug', $currentConfig['app_debug']) ? 'selected' : '' }}>Enabled (Show detailed stack traces)</option>
                </select>
                <span class="form-hint">Always keep disabled in production to protect sensitive data.</span>
            </div>
        </div>
    </div>

    <!-- Database Credentials -->
    <div style="margin-bottom: 1.5rem;">
        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">
            2. Database Credentials
        </h3>

        <div class="form-grid">
            <div class="form-group col-span-2">
                <label class="form-label" for="db_connection">
                    <span>Database Driver</span>
                </label>
                <select name="db_connection" id="db_connection" class="form-select" onchange="toggleDbFields()">
                    <option value="mysql" {{ old('db_connection', $currentConfig['db_connection']) === 'mysql' ? 'selected' : '' }}>MySQL / MariaDB (Standard)</option>
                    <option value="pgsql" {{ old('db_connection', $currentConfig['db_connection']) === 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                    <option value="sqlite" {{ old('db_connection', $currentConfig['db_connection']) === 'sqlite' ? 'selected' : '' }}>SQLite (File-based)</option>
                </select>
            </div>

            <div class="form-group host-port-field">
                <label class="form-label" for="db_host">
                    <span>Host / Server</span>
                </label>
                <input type="text" name="db_host" id="db_host" class="form-input" 
                       value="{{ old('db_host', $currentConfig['db_host']) }}" placeholder="127.0.0.1">
            </div>

            <div class="form-group host-port-field">
                <label class="form-label" for="db_port">
                    <span>Port</span>
                </label>
                <input type="text" name="db_port" id="db_port" class="form-input" 
                       value="{{ old('db_port', $currentConfig['db_port']) }}" placeholder="3306">
            </div>

            <div class="form-group col-span-2">
                <label class="form-label" for="db_database">
                    <span id="dbNameLabel">Database Name</span>
                </label>
                <input type="text" name="db_database" id="db_database" class="form-input" 
                       value="{{ old('db_database', $currentConfig['db_database']) }}" required placeholder="e.g. ecommerce_db">
            </div>

            <div class="form-group auth-field">
                <label class="form-label" for="db_username">
                    <span>Database Username</span>
                </label>
                <input type="text" name="db_username" id="db_username" class="form-input" 
                       value="{{ old('db_username', $currentConfig['db_username']) }}" placeholder="root">
            </div>

            <div class="form-group auth-field">
                <label class="form-label" for="db_password">
                    <span>Database Password</span>
                </label>
                <input type="password" name="db_password" id="db_password" class="form-input" 
                       value="{{ old('db_password', $currentConfig['db_password']) }}" placeholder="Leave blank if none">
            </div>
        </div>
    </div>

    <!-- Live Connection Tester Box -->
    <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border-subtle); border-radius: 0.85rem; padding: 1.25rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; gap: 1rem;">
            <div>
                <strong style="color: #ffffff; font-size: 0.92rem; display: block;">Verify Database Connectivity</strong>
                <span style="color: var(--text-muted); font-size: 0.8rem;">Test credentials before writing configuration to disk.</span>
            </div>
            <button type="button" id="btnTestConn" onclick="testConnection()" class="btn btn-outline" style="padding: 0.55rem 1.15rem; font-size: 0.85rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>Test Connection</span>
            </button>
        </div>

        <div id="testResultBox" style="display: none; margin-top: 1rem; padding: 0.85rem 1rem; border-radius: 0.65rem; font-size: 0.85rem;"></div>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.requirements') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back</span>
        </a>

        <button type="submit" id="btnSubmitForm" class="btn btn-primary">
            <span>Save & Proceed to Migrations</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleDbFields() {
    const driver = document.getElementById('db_connection').value;
    const isSqlite = driver === 'sqlite';
    const isPgsql = driver === 'pgsql';

    document.querySelectorAll('.host-port-field').forEach(el => {
        el.style.display = isSqlite ? 'none' : 'flex';
    });
    document.querySelectorAll('.auth-field').forEach(el => {
        el.style.display = isSqlite ? 'none' : 'flex';
    });

    const portInput = document.getElementById('db_port');
    if (isPgsql && (!portInput.value || portInput.value === '3306')) {
        portInput.value = '5432';
    } else if (!isPgsql && !isSqlite && portInput.value === '5432') {
        portInput.value = '3306';
    }

    const label = document.getElementById('dbNameLabel');
    if (isSqlite) {
        label.innerText = 'SQLite Database Path / File';
        const dbInput = document.getElementById('db_database');
        if (!dbInput.value || dbInput.value === 'e-com') {
            dbInput.value = 'database/database.sqlite';
        }
    } else {
        label.innerText = 'Database Name';
    }
}

async function testConnection() {
    const btn = document.getElementById('btnTestConn');
    const box = document.getElementById('testResultBox');
    
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="spin" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="2" x2="12" y2="6"></line>
            <line x1="12" y1="18" x2="12" y2="22"></line>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
            <line x1="2" y1="12" x2="6" y2="12"></line>
            <line x1="18" y1="12" x2="22" y2="12"></line>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
        </svg>
        <span>Testing...</span>
    `;

    box.style.display = 'block';
    box.style.background = 'rgba(255, 255, 255, 0.05)';
    box.style.border = '1px solid var(--border-subtle)';
    box.style.color = '#cbd5e1';
    box.innerHTML = 'Connecting to database server...';

    const payload = {
        driver: document.getElementById('db_connection').value,
        host: document.getElementById('db_host').value,
        port: document.getElementById('db_port').value,
        database: document.getElementById('db_database').value,
        username: document.getElementById('db_username').value,
        password: document.getElementById('db_password').value,
    };

    try {
        const response = await fetch("{{ route('install.database.test') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success) {
            box.style.background = 'var(--success-bg)';
            box.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            box.style.color = '#6ee7b7';
            box.innerHTML = `<strong>✓ Success:</strong> ${data.message}`;
        } else if (data.can_create_database) {
            box.style.background = 'var(--warning-bg)';
            box.style.border = '1px solid rgba(245, 158, 11, 0.3)';
            box.style.color = '#fcd34d';
            box.innerHTML = `
                <div><strong>Notice:</strong> ${data.message}</div>
                <div style="margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                    <span>${data.detail}</span>
                    <button type="button" onclick="createDatabase()" class="btn btn-primary" style="padding: 0.35rem 0.85rem; font-size: 0.8rem;">
                        Create Database Now
                    </button>
                </div>
            `;
        } else {
            box.style.background = 'var(--danger-bg)';
            box.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            box.style.color = '#fca5a5';
            box.innerHTML = `<strong>✗ Connection Failed:</strong> ${data.message}`;
        }
    } catch (err) {
        box.style.background = 'var(--danger-bg)';
        box.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        box.style.color = '#fca5a5';
        box.innerHTML = `<strong>✗ Request Error:</strong> Could not complete verification test.`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>Test Connection</span>
        `;
    }
}

async function createDatabase() {
    const box = document.getElementById('testResultBox');
    box.innerHTML = 'Creating database schema on server...';

    const payload = {
        driver: document.getElementById('db_connection').value,
        host: document.getElementById('db_host').value,
        port: document.getElementById('db_port').value,
        database: document.getElementById('db_database').value,
        username: document.getElementById('db_username').value,
        password: document.getElementById('db_password').value,
    };

    try {
        const response = await fetch("{{ route('install.database.create') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (data.success) {
            box.style.background = 'var(--success-bg)';
            box.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            box.style.color = '#6ee7b7';
            box.innerHTML = `<strong>✓ Success:</strong> ${data.message}. You can now save and proceed.`;
        } else {
            box.style.background = 'var(--danger-bg)';
            box.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            box.style.color = '#fca5a5';
            box.innerHTML = `<strong>✗ Failed:</strong> ${data.message}`;
        }
    } catch (e) {
        box.innerHTML = `<strong>✗ Error:</strong> Failed to trigger database creation.`;
    }
}

document.addEventListener('DOMContentLoaded', toggleDbFields);
</script>
<style>
.spin {
    animation: rotate 1.5s linear infinite;
}
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection
