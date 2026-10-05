@extends('install.layout', ['step' => 2])

@section('title', 'Server Requirements & Permissions')
@section('page_heading', 'System Diagnostic & Permissions')
@section('page_subheading', 'Verifying that your hosting environment and directory permissions satisfy all framework requirements.')

@section('content')
<div class="card-header">
    <div>
        <h2 class="card-title">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            <span>Environment Checklist</span>
        </h2>
        <p class="card-subtitle">Ensure all required components display green checkmarks before proceeding.</p>
    </div>
    @if($allMet)
        <span class="badge badge-success">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            System Ready
        </span>
    @else
        <span class="badge badge-danger">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Action Required
        </span>
    @endif
</div>

<!-- PHP Version -->
<div style="margin-bottom: 2rem;">
    <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">1. PHP Runtime</h3>
    <table class="status-table">
        <tbody>
            <tr>
                <td style="font-weight: 600; color: #ffffff; width: 40%;">PHP Version</td>
                <td style="color: var(--text-muted);">Current: <strong>{{ $php['current'] }}</strong> (Required: &gt;= {{ $php['minimum'] }})</td>
                <td style="text-align: right; width: 120px;">
                    @if($php['passed'])
                        <span class="badge badge-success">Passed</span>
                    @else
                        <span class="badge badge-danger">Failed</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Required Extensions -->
<div style="margin-bottom: 2rem;">
    <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">2. Required PHP Extensions</h3>
    <table class="status-table">
        <tbody>
            @foreach($extensions as $ext)
                @if($ext['required'])
                    <tr>
                        <td style="font-weight: 600; color: #ffffff; width: 30%;">
                            <code>{{ $ext['name'] }}</code>
                        </td>
                        <td style="color: var(--text-muted);">{{ $ext['description'] }}</td>
                        <td style="text-align: right; width: 120px;">
                            @if($ext['passed'])
                                <span class="badge badge-success">Loaded</span>
                            @else
                                <span class="badge badge-danger">Missing</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<!-- Recommended Extensions & PDO Drivers -->
<div style="margin-bottom: 2rem;">
    <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">3. Database Drivers & Recommended Modules</h3>
    <table class="status-table">
        <tbody>
            @foreach($pdo['drivers'] as $drv)
                <tr>
                    <td style="font-weight: 600; color: #ffffff; width: 30%;">
                        <code>{{ $drv['name'] }}</code>
                    </td>
                    <td style="color: var(--text-muted);">{{ $drv['label'] }}</td>
                    <td style="text-align: right; width: 120px;">
                        @if($drv['passed'])
                            <span class="badge badge-success">Available</span>
                        @else
                            <span class="badge badge-warning">Optional</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            @foreach($extensions as $ext)
                @if(!$ext['required'])
                    <tr>
                        <td style="font-weight: 600; color: #ffffff; width: 30%;">
                            <code>{{ $ext['name'] }}</code>
                        </td>
                        <td style="color: var(--text-muted);">{{ $ext['description'] }}</td>
                        <td style="text-align: right; width: 120px;">
                            @if($ext['passed'])
                                <span class="badge badge-success">Loaded</span>
                            @else
                                <span class="badge badge-warning">Optional</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<!-- Directory Permissions -->
<div style="margin-bottom: 1.5rem;">
    <h3 style="font-size: 0.95rem; font-weight: 700; color: #ffffff; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8;">4. Directory & File Permissions</h3>
    <table class="status-table">
        <tbody>
            @foreach($permissions as $perm)
                <tr>
                    <td style="font-weight: 600; color: #ffffff; width: 35%;">
                        <code>{{ $perm['name'] }}</code>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem; font-family: monospace;">
                        {{ $perm['path'] }}
                    </td>
                    <td style="text-align: right; width: 120px;">
                        @if($perm['writable'])
                            <span class="badge badge-success">Writable</span>
                        @else
                            <span class="badge badge-danger">Not Writable</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if(!$allMet)
    <div class="alert alert-warning">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <div>
            Some critical server requirements are missing. Please ensure required extensions are enabled in your <code>php.ini</code> and target folders have write permissions (e.g. <code>chmod -R 775 storage bootstrap/cache</code>), then click <strong>Re-check Requirements</strong>.
        </div>
    </div>
@endif

<div class="btn-group">
    <a href="{{ route('install.index') }}" class="btn btn-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Back</span>
    </a>

    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('install.requirements') }}" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"></polyline>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
            </svg>
            <span>Re-check</span>
        </a>

        @if($allMet)
            <a href="{{ route('install.database') }}" class="btn btn-primary">
                <span>Continue to Database Setup</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        @else
            <button class="btn btn-primary" disabled title="Please fix critical requirements first">
                <span>Fix Requirements to Proceed</span>
            </button>
        @endif
    </div>
</div>
@endsection
