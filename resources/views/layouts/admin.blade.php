@php
    $seg      = request()->segment(2) ?? '';
    $siteName = \App\Models\StoreSetting::getStoreName();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <title>@yield('title', 'Dashboard') — {{ $siteName }} Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/css/fa-all.min.css') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

    <style>

        body, .navbar .container-fluid {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }

        /* ── Loading overlay ── */
        #loading {
            position: fixed;
            display: flex; justify-content: center; align-items: center;
            width: 100%; height: 100%;
            top: 0; left: 0;
            background-color: #fff;
            z-index: 9999;
        }
        .loading-bars {
            display: flex; gap: 5px;
            align-items: center; justify-content: center;
        }
        .loading-bars span {
            width: 4px; height: 40px;
            background: #555;
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

        /* ── Navbar — black text, no colour on active ── */
        .navbar-default {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(118, 116, 116, 0.06);
        }

        /* All nav links: black */
        .navbar-default .navbar-nav > li > a,
        .navbar-default .navbar-nav > li > a:focus {
            color: #626262ff !important;
            padding: 12px 10px;
            font-size: 13px;
        }
        .navbar-default .navbar-nav > li > a:hover {
            color: #6a6a6aff !important;
            background: #f5f5f5;
        }

        /* Active: simple underline, no colour fill */
        .navbar-default .navbar-nav > .active > a,
        .navbar-default .navbar-nav > .active > a:focus,
        .navbar-default .navbar-nav > .active > a:hover {
            color: #6a6a6aff !important;
            background: #f0f0f0 !important;
            font-weight: 600;
            border-bottom: 2px solid #333;
            box-shadow: none;
        }

        /* Dropdown items: black */
        .dropdown-menu > li > a {
            color: #6a6a6aff !important;
            font-size: 13px;
            padding: 7px 16px;
        }
        .dropdown-menu > li > a:hover,
        .dropdown-menu > li > a:focus {
            color: #6a6a6aff !important;
            background: #f5f5f5 !important;
        }
        .dropdown-menu > .active > a,
        .dropdown-menu > .active > a:focus,
        .dropdown-menu > .active > a:hover {
            color: #6a6a6aff !important;
            background: #ebebeb !important;
            font-weight: 600;
        }

        .nav .open > a,
        .nav .open > a:hover,
        .nav .open > a:focus { background: #f5f5f5 !important; }

        /* Navbar brand */
        .navbar-brand {
            color: #111 !important;
            font-weight: 700;
            font-size: 15px;
            padding: 12px 15px;
        }

        /* ── Buttons ── */
        .btn-primary { background-color: #3a7bd5; border-color: #3a7bd5; }
        .btn-primary:hover,
        .btn-primary:focus  { background-color: #2f6bc4; border-color: #2f6bc4; }
        .btn-danger  { background-color: #c0392b; border-color: #c0392b; }
        .text-primary { color: #3a7bd5; }
        .text-danger  { color: #c0392b; }
        .text-success { color: #27ae60; }
        .btn-success  { background-color: #27ae60; border-color: #27ae60; }

        /* ── Badges ── */
        .badge-success { background-color: #198754 !important; }
        .badge-danger  { background-color: #dc3545 !important; }
        .badge-warning { background-color: #ffc107 !important; }
        .badge-info    { background-color: #0dcaf0 !important; }

        /* ── Form focus ── */
        .form-control:focus {
            border-color: #3a7bd5;
            box-shadow: 0 0 0 2px rgba(58,123,213,0.15);
        }

        /* ── Spinner ── */
        .spinner-border {
            display: inline-block;
            width: 1.4rem; height: 1.4rem;
            vertical-align: text-bottom;
            border: .2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }
        .spinner-border-sm { width: 1rem; height: 1rem; border-width: .18em; }
        @keyframes spinner-border { 100% { transform: rotate(360deg); } }

        /* ── Responsive ── */
        @media screen and (min-width: 906px) { .navbar-header { display: none; } }
        @media (max-width: 767px) { .table-responsive .dropdown-menu { position: relative !important; } }

        /* ── Page content ── */
        body { padding-top: 51px; }
        .admin-content { padding: 16px 14px; }
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

{{-- ── NAVBAR ── --}}
<nav class="navbar navbar-fixed-top navbar-default">
    <div class="container-fluid">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed"
                    data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">{{ $siteName }}</a>
        </div>

        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">

                <li class="{{ $seg === 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>

                <li class="dropdown {{ $seg === 'users' ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-users"></i> Users <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ $seg === 'users' ? 'active' : '' }}">
                            <a href="{{ url('admin/users') }}"><i class="fas fa-user"></i> All Users</a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown {{ in_array($seg, ['orders','tasks','cancellations-returns']) ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-shopping-cart"></i> Orders <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ $seg === 'orders' ? 'active' : '' }}">
                            <a href="{{ url('admin/orders') }}"><i class="fas fa-list"></i> Orders</a>
                        </li>
                        <li class="{{ $seg === 'cancellations-returns' ? 'active' : '' }}">
                            <a href="{{ url('admin/cancellations-returns') }}"><i class="fas fa-undo"></i> Cancellations &amp; Returns</a>
                        </li>
                        <li class="{{ $seg === 'tasks' ? 'active' : '' }}">
                            <a href="{{ url('admin/tasks') }}"><i class="fas fa-tasks"></i> Tasks</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ $seg === 'users' ? 'active' : '' }}">
                    <a href="{{ url('admin/users') }}"><i class="fas fa-users"></i> Customers</a>
                </li>

                <li class="{{ $seg === 'support' ? 'active' : '' }}">
                    <a href="{{ url('admin/support') }}"><i class="fas fa-headset"></i> Support</a>
                </li>

                <li>
                    <a href="{{ route('shop.help') }}" target="_blank"><i class="fas fa-question-circle"></i> Help (preview)</a>
                </li>

                <li class="dropdown {{ in_array($seg, ['categories', 'products', 'providers', 'reviews']) ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-box"></i> Products <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ $seg === 'products' ? 'active' : '' }}">
                            <a href="{{ url('admin/products') }}"><i class="fas fa-box-open"></i> All Products</a>
                        </li>
                        <li class="{{ $seg === 'providers' ? 'active' : '' }}">
                            <a href="{{ url('admin/providers') }}"><i class="fas fa-truck"></i> Providers</a>
                        </li>
                        <li class="{{ $seg === 'categories' ? 'active' : '' }}">
                            <a href="{{ url('admin/categories') }}"><i class="fas fa-folder"></i> Categories</a>
                        </li>
                        <li class="{{ $seg === 'reviews' ? 'active' : '' }}">
                            <a href="{{ route('admin.reviews.index') }}"><i class="fas fa-star"></i> Product Reviews</a>
                        </li>
                    </ul>
                </li>

                

                <li class="{{ $seg === 'payments' ? 'active' : '' }}">
                    <a href="{{ route('admin.payments.index') }}"><i class="fas fa-credit-card"></i> Payments</a>
                </li>

                <li class="dropdown {{ in_array($seg, ['invoices', 'templates']) ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-file-invoice-dollar"></i> Invoices <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ $seg === 'invoices' ? 'active' : '' }}">
                            <a href="{{ route('admin.invoices.index') }}"><i class="fas fa-list-alt"></i> All Invoices</a>
                        </li>
                        <li class="{{ $seg === 'templates' ? 'active' : '' }}">
                            <a href="{{ route('admin.templates.index') }}"><i class="fas fa-palette"></i> Invoice Templates</a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown {{ in_array($seg, ['reports','broadcasts','coupons','bonuses','banners','logs']) ? 'active' : '' }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-bars"></i> More <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="{{ $seg === 'reports'    ? 'active' : '' }}">
                            <a href="{{ url('admin/reports') }}"><i class="fas fa-chart-bar"></i> Reports</a>
                        </li>
                        <li class="{{ $seg === 'broadcasts' ? 'active' : '' }}">
                            <a href="{{ url('admin/broadcasts') }}"><i class="fas fa-bullhorn"></i> Broadcasts</a>
                        </li>
                        <li class="{{ $seg === 'coupons'    ? 'active' : '' }}">
                            <a href="{{ url('admin/coupons') }}"><i class="fas fa-ticket-alt"></i> Coupons</a>
                        </li>
                        <li class="{{ $seg === 'bonuses'    ? 'active' : '' }}">
                            <a href="{{ url('admin/bonuses') }}"><i class="fas fa-gift"></i> Bonuses</a>
                        </li>
                        <li class="{{ $seg === 'banners'    ? 'active' : '' }}">
                            <a href="{{ url('admin/banners') }}"><i class="fas fa-image"></i> Banners</a>
                        </li>
                        <li class="{{ $seg === 'logs'       ? 'active' : '' }}">
                            <a href="{{ url('admin/logs') }}"><i class="fas fa-clipboard-list"></i> User Logs</a>
                        </li>
                    </ul>
                </li>

                <li class="{{ $seg === 'settings' ? 'active' : '' }}">
                    <a href="{{ url('admin/settings') }}"><i class="fas fa-cog"></i> Settings</a>
                </li>

                <li class="{{ $seg === 'account' ? 'active' : '' }}">
                    <a href="{{ url('admin/account') }}">
                        <i class="fas fa-user-circle"></i> {{ auth()->user()->name ?? 'Account' }}
                    </a>
                </li>

                <li>
                    <a href="#" id="nav-logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="nav-logout-form" method="POST" action="{{ route('admin.logout') }}" style="display:none;">
                        @csrf
                    </form>
                </li>

            </ul>
        </div>

    </div>
</nav>

{{-- ── PAGE CONTENT ── --}}
<div class="admin-content">
    @yield('content')
</div>

{{-- ── SCRIPTS ── --}}
<script src="{{ asset('assets/js/jquery-1.12.4.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.js"></script>

<script>
    // Setup CSRF token for all jQuery AJAX requests globally
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* Hide loading overlay — primary handler */
    $(window).on('load', function () { $('#loading').fadeOut(350); });

    /* Fallback: if load already fired before jQuery registered, hide immediately */
    if (document.readyState === 'complete') {
        $('#loading').fadeOut(350);
    }

    /* Safety net: never show loader for more than 5 seconds */
    setTimeout(function () { $('#loading').fadeOut(350); }, 5000);

    document.getElementById('nav-logout-btn').addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('nav-logout-form').submit();
    });
</script>

@stack('scripts')
</body>
</html>
