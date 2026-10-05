@php
  $storeName = \App\Models\StoreSetting::getStoreName();
  $metaTitleSetting = \App\Models\StoreSetting::getValue('meta_title');
  $metaDescriptionSetting = \App\Models\StoreSetting::getValue('meta_description');
  $metaKeywordsArr = json_decode(\App\Models\StoreSetting::getValue('meta_keywords', '[]'), true);
  $metaKeywordsSetting = is_array($metaKeywordsArr) ? implode(', ', $metaKeywordsArr) : '';
  $canonicalUrlSetting = \App\Models\StoreSetting::getValue('canonical_url');
  $gtmContainerId = \App\Models\StoreSetting::getValue('gtm_container_id');
  $primaryColor = \App\Models\StoreSetting::getPrimaryColor();
  $secondaryColor = \App\Models\StoreSetting::getSecondaryColor();
  $primaryRgb = \App\Models\StoreSetting::getPrimaryColorRgb();
  $secondaryRgb = \App\Models\StoreSetting::getSecondaryColorRgb();
  $primaryHover = \App\Models\StoreSetting::getPrimaryColorHover();
  $secondaryHover = \App\Models\StoreSetting::getSecondaryColorHover();
  $themeFont = \App\Models\StoreSetting::getFontFamily();
  $headerStyle = \App\Models\StoreSetting::getHeaderStyle();
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', $metaTitleSetting ?: ($storeName . ' — Online Store'))</title>
  <meta name="description" content="@yield('description', $metaDescriptionSetting ?: 'Shop high-quality collections at ' . $storeName)">
  @if(!empty($metaKeywordsSetting))
    <meta name="keywords" content="{{ $metaKeywordsSetting }}">
  @endif
  @if(!empty($canonicalUrlSetting))
    <link rel="canonical" href="{{ rtrim($canonicalUrlSetting, '/') . '/' . ltrim(request()->getPathInfo(), '/') }}">
  @endif
  @if(!empty($gtmContainerId))
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmContainerId }}');</script>
    <!-- End Google Tag Manager -->
  @endif

  <!-- Fast CDN Preconnect & DNS-Prefetch -->
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

  <!-- Google Fonts: Load Active Storefront Font Efficiently with font-display swap -->
  @php
    $activeFontParam = \App\Models\StoreSetting::getValue('theme_font', 'Rubik') ?: 'Rubik';
  @endphp
  <link href="https://fonts.googleapis.com/css2?family={{ urlencode($activeFontParam) }}:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- High-Speed Icons via Cloudflare CDN (Line Awesome & FontAwesome) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="shortcut icon" href="{{ \App\Models\StoreSetting::getFaviconUrl() }}">
  <link rel="icon" href="{{ \App\Models\StoreSetting::getFaviconUrl() }}">
  <link rel="apple-touch-icon" href="{{ \App\Models\StoreSetting::getFaviconUrl() }}">

  <style>
  :root {
    --color-primary: {{ $primaryColor }};
    --color-primary-hover: {{ $primaryHover }};
    --color-primary-rgb: {{ $primaryRgb }};
    --color-primary-transparent: rgba({{ $primaryRgb }}, 0.85);
    --color-primary-transparent-lite: rgba({{ $primaryRgb }}, 0.3);
    --color-primary-transparent-lite-2: rgba({{ $primaryRgb }}, 0.12);
    --primary-color: var(--color-primary);
    --brand-accent: var(--color-primary);
    --brand-accent-hover: var(--color-primary-hover);
    --brand-accent-gradient: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $primaryHover }} 100%);
    
    --color-secondary: {{ $secondaryColor }};
    --color-secondary-hover: {{ $secondaryHover }};
    --color-secondary-rgb: {{ $secondaryRgb }};
    --color-accent: var(--color-secondary);

    --color-heading: #0f172a;
    --color-text: #292D32;
    --color-muted: #626F84;
    --color-border: #E6E9EC;
    --color-border-lite: #F0F2F5;
    --color-bg-gray: #F8F9FA;
    --color-white: #FFFFFF;
    --color-star: #FFA800;
    --color-danger: #EF4444;
    --font-base: {!! $themeFont !!};
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-pill: 100px;
    --shadow-card: 0 2px 10px rgba(0, 0, 0, 0.04);
    --shadow-hover: 0 10px 25px rgba({{ $primaryRgb }}, 0.14), 0 4px 10px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s ease-in-out;

    /* Theme & Legacy Aliases for Cross-Page Harmony */
    --border: var(--color-border);
    --border-light: var(--color-border-lite);
    --border-color: var(--color-border);
    --color-bg: #f8fafc;
    --medium-gray: var(--color-muted);
    --text-muted: var(--color-muted);
    --dark-gray: var(--color-heading);
    --amazon-charcoal: #131921;
    --amazon-orange: var(--color-secondary);
    --amazon-dark: #0f172a;
    --font-heading: var(--font-base);
    --font-sans: var(--font-base);
    --primary: var(--color-primary);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }
  html, body { scroll-behavior: smooth; }
  body {
    font-family: var(--font-base);
    background: var(--color-bg-gray);
    color: var(--color-text);
    min-height: 100vh;
    font-size: 14px;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    padding-bottom: 60px; /* Space for mobile bottom bar */
  }
  @media (min-width: 992px) { body { padding-bottom: 0; } }

  a { text-decoration: none; color: inherit; transition: var(--transition); }
  a:hover { color: var(--color-primary); }
  img { max-width: 100%; height: auto; }
  .container { max-width: 1280px; margin: 0 auto; padding: 0 16px; }

  /* ── 1. Top Navigation Bar (FleetCart style) ───────── */
  .top-nav-wrap {
    background: #ffffff;
    border-bottom: 1px solid var(--color-border);
    font-size: 12.5px;
    color: var(--color-muted);
  }
  .top-nav-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
  }
  .top-nav-left { display: flex; align-items: center; gap: 18px; }
  .top-nav-left a { display: inline-flex; align-items: center; gap: 6px; color: var(--color-text); font-weight: 500; }
  .top-nav-left a i { color: var(--color-primary); font-size: 14px; }
  .top-nav-right { display: flex; align-items: center; gap: 18px; margin-left: auto; }
  .top-nav-right a { display: inline-flex; align-items: center; gap: 6px; color: var(--color-muted); font-weight: 400; }
  .top-nav-right a:hover { color: var(--color-primary); }
  .top-nav-right a i { font-size: 14px; }

  /* ── 2. Main Header (Logo, Search, Actions) ────────── */
  .header-wrap {
    background: #ffffff;
    border-bottom: 1px solid var(--color-border);
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  }
  .header-wrap-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    gap: 20px;
  }
  .header-left {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .sidebar-menu-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    cursor: pointer;
    background: #ffffff;
    transition: var(--transition);
  }
  .sidebar-menu-icon:hover { border-color: var(--color-primary); color: var(--color-primary); }
  .sidebar-menu-icon svg { width: 20px; height: 20px; fill: var(--color-text); }
  .header-logo {
    display: flex;
    align-items: center;
    text-decoration: none;
  }
  .header-logo img {
    max-height: 42px;
    max-width: 170px;
    object-fit: contain;
  }
  .header-logo-text {
    font-size: 24px;
    font-weight: 800;
    color: var(--color-heading);
    letter-spacing: -0.5px;
  }
  .header-logo-text span { color: var(--color-primary); }

  /* ── Header Styling Modes (Primary / Dark / Light) ── */
  @if($headerStyle === 'primary')
  .top-nav-wrap {
    background: rgba(var(--color-primary-rgb), 0.08) !important;
    border-bottom: 1px solid rgba(var(--color-primary-rgb), 0.16) !important;
  }
  .top-nav-left a, .top-nav-right a {
    color: #334155 !important;
  }
  .top-nav-left a i, .top-nav-right a:hover {
    color: var(--color-primary) !important;
  }
  .header-wrap {
    background: var(--color-primary) !important;
    border-bottom: 1px solid var(--color-primary-hover) !important;
    box-shadow: 0 4px 20px rgba(var(--color-primary-rgb), 0.28) !important;
  }
  .header-logo-text { color: #ffffff !important; }
  .header-logo-text span { color: #ffffff !important; text-shadow: 0 0 10px rgba(255, 255, 255, 0.7); }
  .header-logo img {
    background: #ffffff !important;
    padding: 3px 8px !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
  }
  .sidebar-menu-icon {
    background: rgba(255, 255, 255, 0.18) !important;
    border-color: rgba(255, 255, 255, 0.35) !important;
    color: #ffffff !important;
  }
  .sidebar-menu-icon svg { fill: #ffffff !important; }
  .sidebar-menu-icon:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    border-color: #ffffff !important;
  }
  .header-action-item {
    color: #ffffff !important;
  }
  .header-action-icon-wrap svg {
    stroke: #ffffff !important;
  }
  .header-action-badge {
    background: var(--color-secondary) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
  }
  .header-cart-label {
    color: rgba(255, 255, 255, 0.85) !important;
  }
  .header-cart-total {
    color: #ffffff !important;
    font-weight: 800 !important;
  }
  .header-search {
    border: 2px solid rgba(255, 255, 255, 0.5) !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08) !important;
  }
  .header-search-btn {
    background: var(--color-secondary) !important;
    color: #ffffff !important;
  }
  .header-search-btn:hover {
    background: var(--color-secondary-hover) !important;
  }
  @elseif($headerStyle === 'dark')
  .top-nav-wrap {
    background: #090e17 !important;
    border-bottom: 1px solid #1e293b !important;
    color: #94a3b8 !important;
  }
  .top-nav-left a, .top-nav-right a {
    color: #cbd5e1 !important;
  }
  .top-nav-left a i {
    color: var(--color-primary) !important;
  }
  .header-wrap {
    background: #0f172a !important;
    border-bottom: 1px solid #1e293b !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
  }
  .header-logo-text { color: #ffffff !important; }
  .header-logo-text span { color: var(--color-primary) !important; }
  .header-logo img {
    background: #ffffff !important;
    padding: 3px 8px !important;
    border-radius: 6px !important;
  }
  .sidebar-menu-icon {
    background: #1e293b !important;
    border-color: #334155 !important;
  }
  .sidebar-menu-icon svg { fill: #ffffff !important; }
  .header-action-item {
    color: #f8fafc !important;
  }
  .header-action-icon-wrap svg {
    stroke: #f8fafc !important;
  }
  .header-action-badge {
    background: var(--color-primary) !important;
    color: #ffffff !important;
  }
  .header-cart-label {
    color: #94a3b8 !important;
  }
  .header-cart-total {
    color: #ffffff !important;
  }
  .header-search {
    border: 2px solid var(--color-primary) !important;
  }
  .header-search-btn {
    background: var(--color-primary) !important;
    color: #ffffff !important;
  }
  @else
  .top-nav-wrap {
    border-top: 3.5px solid var(--color-primary) !important;
    background: #ffffff !important;
  }
  .header-search {
    border: 2px solid var(--color-primary) !important;
  }
  .header-search-btn {
    background: var(--color-primary) !important;
    color: #ffffff !important;
  }
  .header-action-badge {
    background: var(--color-primary) !important;
    color: #ffffff !important;
  }
  .header-logo-text span {
    color: var(--color-primary) !important;
  }
  @endif

  /* Live Search & Backdrop Blur System */
  .live-search-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    z-index: 1030;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.15s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.15s ease;
    pointer-events: none;
    will-change: opacity;
  }
  .live-search-backdrop.is-active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
  }

  .header-wrap {
    position: relative;
    z-index: 1040;
  }

  /* FleetCart Search Bar with Category Dropdown */
  .header-search-container {
    flex: 1;
    max-width: 620px;
    position: relative;
    z-index: 1050;
    transition: transform 0.15s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .header-search-container.is-focused .header-search {
    border-color: var(--color-primary);
    box-shadow: 0 10px 30px -4px rgba(var(--color-primary-rgb), 0.28), 0 0 0 3.5px rgba(var(--color-primary-rgb), 0.16);
    transform: translateY(-1px);
  }
  .header-search {
    width: 100%;
    display: flex;
    align-items: center;
    border: 1.5px solid var(--color-primary);
    border-radius: var(--radius-sm);
    background: #ffffff;
    overflow: hidden;
    height: 44px;
    transition: box-shadow 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
  }
  .header-search-cat-select {
    padding: 0 12px;
    height: 100%;
    border: none;
    border-right: 1px solid var(--color-border);
    background: #f8fafc;
    color: var(--color-muted);
    font-size: 13px;
    font-weight: 500;
    font-family: var(--font-base);
    outline: none;
    cursor: pointer;
    max-width: 150px;
    transition: background 0.15s ease;
  }
  .header-search-cat-select:hover {
    background: #f1f5f9;
  }
  .header-search-input-wrap {
    flex: 1;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
  }
  .header-search-input {
    flex: 1;
    width: 100%;
    height: 100%;
    border: none;
    padding: 0 42px 0 16px;
    font-size: 13.5px;
    font-family: var(--font-base);
    color: var(--color-text);
    outline: none;
    background: transparent;
  }
  .header-search-btn {
    height: 100%;
    padding: 0 20px;
    background: var(--color-primary);
    color: #ffffff;
    border: none;
    font-size: 15px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .header-search-btn:hover { background: var(--color-primary-hover); }

  /* Mobile Live Search */
  .mobile-search-container {
    position: relative;
    z-index: 1050;
    transition: transform 0.15s ease;
  }
  .mobile-search-container.is-focused .mobile-search-form {
    border-color: var(--color-primary);
    box-shadow: 0 8px 25px -4px rgba(0, 104, 225, 0.28), 0 0 0 3px rgba(0, 104, 225, 0.16);
  }

  /* Clear button & Loading spinner */
  .live-search-clear-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: #e2e8f0;
    border: none;
    color: #64748b;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.12s ease;
    z-index: 4;
  }
  .live-search-clear-btn:hover {
    background: #cbd5e1;
    color: #0f172a;
    transform: translateY(-50%) scale(1.12);
  }
  .live-search-spinner {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 4;
  }
  .live-search-spinner-svg {
    animation: liveSearchSpin 0.6s linear infinite;
  }
  @keyframes liveSearchSpin {
    100% { transform: rotate(360deg); }
  }

  /* Live Search Dropdown Panel with Glassmorphism */
  .live-search-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 14px;
    box-shadow: 0 24px 50px -10px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(0, 104, 225, 0.08);
    z-index: 1060;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-6px) scale(0.99);
    transform-origin: top center;
    transition: opacity 0.12s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.12s cubic-bezier(0.16, 1, 0.3, 1),
                visibility 0.12s;
    pointer-events: none;
    max-height: 520px;
    display: flex;
    flex-direction: column;
    will-change: transform, opacity;
  }
  .live-search-dropdown.is-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
    pointer-events: auto;
  }
  .mobile-live-search-dropdown {
    max-height: calc(100vh - 180px);
  }

  .live-search-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--color-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .live-search-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(0, 104, 225, 0.08);
    color: var(--color-primary);
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
  }

  .live-search-items-list {
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 4px 0;
    max-height: 380px;
  }
  .live-search-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 9px 16px;
    text-decoration: none;
    color: inherit;
    transition: background 0.12s ease, transform 0.12s ease;
    position: relative;
    cursor: pointer;
    animation: liveSearchItemIn 0.12s cubic-bezier(0.16, 1, 0.3, 1) both;
  }
  @keyframes liveSearchItemIn {
    from {
      opacity: 0;
      transform: translateY(4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .live-search-item:hover,
  .live-search-item.is-selected {
    background: #f1f5f9;
    transform: translateX(4px);
  }
  .live-search-item.is-selected {
    background: #eef4ff;
    border-left: 3px solid var(--color-primary);
    padding-left: 13px;
  }
  .live-search-item-img {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
  }
  .live-search-item:hover .live-search-item-img,
  .live-search-item.is-selected .live-search-item-img {
    transform: scale(1.06);
    border-color: var(--color-primary);
  }
  .live-search-item-body {
    flex: 1;
    min-width: 0;
  }
  .live-search-item-cat {
    font-size: 11px;
    font-weight: 600;
    color: var(--color-primary);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .live-search-item-title {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--color-heading);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.35;
    transition: color 0.15s ease;
  }
  .live-search-item:hover .live-search-item-title,
  .live-search-item.is-selected .live-search-item-title {
    color: var(--color-primary);
  }
  .live-search-match {
    background: rgba(0, 104, 225, 0.12);
    color: var(--color-primary);
    font-weight: 700;
    padding: 0 2px;
    border-radius: 3px;
  }
  .live-search-item-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
    flex-wrap: wrap;
  }
  .live-search-item-price {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--color-primary);
  }
  .live-search-item-old-price {
    font-size: 12px;
    color: #94a3b8;
    text-decoration: line-through;
  }
  .live-search-item-discount {
    font-size: 10.5px;
    font-weight: 700;
    color: #16a34a;
    background: rgba(22, 163, 74, 0.1);
    padding: 1px 6px;
    border-radius: 4px;
  }
  .live-search-item-stock {
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: auto;
    color: #16a34a;
  }
  .live-search-item-stock.out-of-stock {
    color: #ef4444;
  }
  .live-search-arrow {
    color: #94a3b8;
    font-size: 15px;
    transition: transform 0.2s ease, color 0.2s ease;
    margin-left: 6px;
    flex-shrink: 0;
  }
  .live-search-item:hover .live-search-arrow,
  .live-search-item.is-selected .live-search-arrow {
    transform: translateX(3px);
    color: var(--color-primary);
  }

  /* Skeleton Loading State */
  .live-search-skeleton {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .live-search-skeleton-img {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: liveSearchShimmer 1.4s infinite;
    flex-shrink: 0;
  }
  .live-search-skeleton-lines {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 7px;
  }
  .live-search-skeleton-line {
    height: 12px;
    border-radius: 4px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: liveSearchShimmer 1.4s infinite;
  }
  @keyframes liveSearchShimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }

  /* Empty State */
  .live-search-empty {
    padding: 32px 20px;
    text-align: center;
    animation: liveSearchItemIn 0.3s ease;
  }
  .live-search-empty-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 10px;
    animation: liveSearchPulse 2s infinite ease-in-out;
  }
  @keyframes liveSearchPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
  }
  .live-search-empty-title {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--color-heading);
    margin-bottom: 4px;
  }
  .live-search-empty-sub {
    font-size: 12.5px;
    color: var(--color-muted);
  }

  /* Footer Action Bar */
  .live-search-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f8fafc;
    border-top: 1px solid #edf2f7;
    font-size: 12px;
  }
  .live-search-view-all-link {
    font-weight: 700;
    color: var(--color-primary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: gap 0.2s ease, color 0.15s ease;
  }
  .live-search-view-all-link:hover {
    gap: 10px;
    color: var(--color-primary-hover);
  }
  .live-search-keyboard-hint {
    font-size: 11px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .live-search-key-badge {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    border-radius: 4px;
    padding: 1px 5px;
    font-family: monospace;
    font-size: 10px;
    color: #475569;
  }

  /* Header Right Actions (Wishlist, Compare, Cart) */
  .header-actions {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .header-action-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--color-text);
    cursor: pointer;
    text-decoration: none;
    position: relative;
    padding: 6px 4px;
    transition: var(--transition);
  }
  .header-action-item:hover { color: var(--color-primary); }
  .header-action-icon-wrap {
    position: relative;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .header-action-icon-wrap svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    transition: var(--transition);
  }
  .header-action-item:hover .header-action-icon-wrap svg {
    stroke: currentColor;
  }
  .header-user-menu.open .header-user-dropdown {
    display: block !important;
  }
  .header-action-badge {
    position: absolute;
    top: -4px;
    right: -6px;
    background: var(--color-primary);
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 700;
    min-width: 17px;
    height: 17px;
    border-radius: var(--radius-pill);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
  }
  .header-cart-info {
    display: flex;
    flex-direction: column;
    text-align: left;
    line-height: 1.2;
  }
  .header-cart-label { font-size: 11px; color: var(--color-muted); text-transform: uppercase; font-weight: 500; }
  .header-cart-total { font-size: 13.5px; font-weight: 700; color: var(--color-heading); }

  /* ── 3. Navigation Bar (Category Menu & Main Links) ─ */
  .navigation-wrap {
    background: #ffffff;
    border-bottom: 1px solid var(--color-border);
  }
  .navigation-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 48px;
  }
  .category-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: var(--color-primary);
    color: #ffffff;
    padding: 0 20px;
    height: 48px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    border: none;
    transition: var(--transition);
  }
  .category-nav-btn:hover { background: var(--color-primary-hover); color: #ffffff; }
  .category-nav-btn i { font-size: 16px; }

  .nav-menu-links {
    display: flex;
    align-items: center;
    gap: 24px;
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .nav-menu-link {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--color-heading);
    padding: 13px 0;
    position: relative;
    text-decoration: none;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .nav-menu-link:hover,
  .nav-menu-link.active {
    color: var(--color-primary);
  }
  .nav-menu-link.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--color-primary);
  }
  .nav-promo-text {
    font-size: 13px;
    font-weight: 500;
    color: var(--color-muted);
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .nav-promo-text i { color: var(--color-primary); font-size: 16px; }

  /* ── 4. Trust Features Bar (4-card strip) ─────────── */
  .features-section {
    padding: 24px 0;
  }
  .features-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
  }
  .feature-card {
    background: #ffffff;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: var(--transition);
    box-shadow: var(--shadow-card);
  }
  .feature-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
    border-color: var(--color-primary);
  }
  .feature-icon {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: var(--color-primary-transparent-lite-2);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
  }
  .feature-info h5 {
    font-size: 14px;
    font-weight: 700;
    color: var(--color-heading);
    margin: 0 0 2px;
  }
  .feature-info p {
    font-size: 12px;
    color: var(--color-muted);
    margin: 0;
  }

  /* ── 5. Product Grid & Cards (FleetCart Style) ─────── */
  .product-section {
    padding: 16px 0 32px;
  }
  .section-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--color-border-lite);
    position: relative;
  }
  .section-header-wrap::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 2px;
    background: var(--color-primary);
  }
  .section-header-wrap h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-heading);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .section-header-wrap a.view-all {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-primary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .section-header-wrap a.view-all:hover {
    color: var(--color-primary-hover);
    transform: translateX(2px);
  }

  .products-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
  }
  @media (max-width: 1200px) {
    .products-grid {
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
    }
  }
  @media (max-width: 992px) {
    .products-grid {
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }
  }
  @media (max-width: 640px) {
    .products-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
    }
  }
  /* ── Compact, Low-Cognitive-Load Product Card ─────────── */
  .product-card {
    background: #ffffff;
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: 10px 10px 12px;
    display: flex;
    flex-direction: column;
    position: relative;
    cursor: pointer;
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                box-shadow 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                border-color 0.18s ease;
    overflow: hidden;
  }
  .product-card:hover {
    border-color: rgba(var(--color-primary-rgb), 0.35);
    box-shadow: 0 10px 24px -6px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(var(--color-primary-rgb), 0.08);
    transform: translateY(-3px);
  }

  /* Top Badges & Actions */
  .product-card-top {
    position: relative;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
  }
  .card-pill-badge {
    position: absolute;
    top: 7px;
    left: 7px;
    font-size: 10px;
    font-weight: 700;
    padding: 2.5px 7px;
    border-radius: 6px;
    z-index: 2;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
  }
  .card-pill-badge.badge-sale {
    background: #ef4444;
    color: #ffffff;
  }
  .card-pill-badge.badge-featured {
    background: var(--color-primary);
    color: #ffffff;
  }
  .card-pill-badge.badge-new {
    background: #0f172a;
    color: #ffffff;
  }

  /* Frosted Glass Wishlist Button */
  .card-wishlist-btn {
    position: absolute;
    top: 7px;
    right: 7px;
    z-index: 3;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.16s ease;
    color: #64748b;
    font-size: 14px;
    padding: 0;
  }
  .card-wishlist-btn:hover,
  .card-wishlist-btn.active {
    color: #ef4444;
    border-color: #fecdd3;
    background: #fff1f2;
    transform: scale(1.1);
  }

  .product-image {
    width: 100%;
    height: 165px;
    background: #f8fafc;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
  }
  .product-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 6px;
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .product-card:hover .product-image img {
    transform: scale(1.05);
  }
  .product-image-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 32px;
    background: #f8fafc;
  }
  .card-stock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
  }
  .card-stock-overlay span {
    font-size: 11px;
    font-weight: 700;
    color: #ef4444;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fee2e2;
    padding: 3px 8px;
    border-radius: 4px;
  }

  /* Product Info */
  .product-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    margin-top: 8px;
    gap: 4px;
  }
  .card-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    min-height: 16px;
  }
  .product-brand {
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .card-rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 2.5px;
    font-size: 10.5px;
    font-weight: 700;
    color: #b45309;
    background: #fef3c7;
    padding: 1.5px 5px;
    border-radius: 4px;
    flex-shrink: 0;
  }
  .card-rating-badge i {
    color: #f59e0b;
    font-size: 11px;
  }
  .card-rating-badge .rating-sub {
    color: #78350f;
    font-weight: 500;
    font-size: 9.5px;
  }
  .product-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-heading);
    line-height: 1.32;
    height: 34px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    text-decoration: none;
    transition: color 0.15s ease;
  }
  .product-card:hover .product-name {
    color: var(--color-primary);
  }

  /* Price & Savings */
  .product-price {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
    flex-wrap: wrap;
    line-height: 1;
  }
  .current-price {
    font-size: 15px;
    font-weight: 800;
    color: var(--color-primary);
    letter-spacing: -0.3px;
  }
  .original-price {
    font-size: 11.5px;
    color: #94a3b8;
    text-decoration: line-through;
  }
  .price-discount-tag {
    font-size: 10px;
    font-weight: 700;
    color: #16a34a;
    background: #dcfce7;
    padding: 1.5px 5px;
    border-radius: 4px;
  }

  /* Action Buttons */
  .card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    margin-top: 6px;
  }
  .btn-add-cart,
  .btn-buy-now {
    height: 30px;
    padding: 0 8px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.15s ease;
  }
  .btn-add-cart {
    background: rgba(var(--color-primary-rgb), 0.08);
    color: var(--color-primary);
    border: 1px solid rgba(var(--color-primary-rgb), 0.28);
    font-weight: 700;
  }
  .btn-add-cart:hover {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(var(--color-primary-rgb), 0.32);
  }
  .btn-buy-now {
    background: var(--color-primary);
    color: #ffffff;
    border: 1px solid var(--color-primary);
    box-shadow: 0 2px 8px rgba(var(--color-primary-rgb), 0.22);
  }
  .btn-buy-now:hover {
    background: var(--color-primary-hover);
    border-color: var(--color-primary-hover);
    box-shadow: 0 4px 14px rgba(var(--color-primary-rgb), 0.35);
  }
  .btn-out-of-stock {
    grid-column: span 2;
    background: #f1f5f9;
    color: #94a3b8;
    border: 1px dashed #cbd5e1;
    cursor: not-allowed;
  }

  
  /* ── Attractive Animation Systems ── */
  .badge-pop {
    animation: badgePop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
  }
  @keyframes badgePop {
    0% { transform: scale(1); }
    40% { transform: scale(1.4); }
    75% { transform: scale(0.9); }
    100% { transform: scale(1); }
  }

  .btn-wishlist {
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.15s, background-color 0.15s, border-color 0.15s !important;
  }
  .btn-wishlist:active {
    transform: scale(0.82) !important;
  }
  .btn-wishlist.animating {
    animation: heartBeat 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  @keyframes heartBeat {
    0% { transform: scale(1); }
    30% { transform: scale(1.4); }
    60% { transform: scale(0.88); }
    100% { transform: scale(1); }
  }

  .btn-add-cart, .btn-buy-now {
    position: relative;
    overflow: hidden;
  }
  .btn-add-cart:active, .btn-buy-now:active {
    transform: scale(0.95);
  }
  .btn-add-cart::after, .btn-buy-now::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -75%;
    width: 50%;
    height: 200%;
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
    transform: rotate(25deg);
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
  }
  .btn-add-cart:hover::after, .btn-buy-now:hover::after {
    opacity: 1;
    animation: shineSweep 0.85s ease-in-out;
  }
  @keyframes shineSweep {
    0% { left: -75%; }
    100% { left: 130%; }
  }

  /* Animated Floating Toast Container */
  .store-toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: none;
    max-width: 380px;
    width: calc(100vw - 32px);
  }
  .store-toast {
    pointer-events: auto;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(226, 232, 240, 0.95);
    border-radius: var(--radius-md, 8px);
    padding: 14px 16px;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12), 0 2px 6px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    overflow: hidden;
    animation: toastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transition: opacity 0.25s, transform 0.25s;
  }
  .store-toast.hiding {
    opacity: 0;
    transform: translateX(40px) scale(0.95);
  }
  @keyframes toastSlideIn {
    0% { opacity: 0; transform: translateX(50px) scale(0.92); }
    100% { opacity: 1; transform: translateX(0) scale(1); }
  }
  .store-toast-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #dcfce7;
    color: #15803d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    flex-shrink: 0;
  }
  .store-toast-icon.info {
    background: #e0f2fe;
    color: #0284c7;
  }
  .store-toast-thumb {
    width: 38px;
    height: 38px;
    border-radius: 6px;
    object-fit: cover;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
  }
  .store-toast-body {
    flex: 1;
    min-width: 0;
  }
  .store-toast-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    margin-bottom: 2px;
    line-height: 1.25;
  }
  .store-toast-desc {
    font-size: 12px;
    color: var(--color-muted, #64748b);
    line-height: 1.35;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .store-toast-close {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 14px;
    padding: 4px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s;
  }
  .store-toast-close:hover { color: #0f172a; }
  .store-toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: var(--color-primary, #0068e1);
    width: 100%;
    animation: toastProgress 3.2s linear forwards;
  }
  @keyframes toastProgress {
    0% { width: 100%; }
    100% { width: 0%; }
  }

  /* Scroll Reveal Utilities */
  .reveal-fade-up {
    opacity: 0;
    transform: translateY(22px);
    transition: opacity 0.55s cubic-bezier(0.16, 1, 0.3, 1), transform 0.55s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: opacity, transform;
  }
  .reveal-fade-up.is-revealed {
    opacity: 1;
    transform: translateY(0);
  }

  @media (prefers-reduced-motion: reduce) {
    *, ::before, ::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
    }
  }

  /* ── 6. Mobile Bottom Navigation Bar (d-lg-none) ──── */
  .bottom-navigation-wrap {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid var(--color-border);
    z-index: 1050;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.06);
    display: none;
  }
  @media (max-width: 991px) {
    .bottom-navigation-wrap { display: block; }
  }
  .bottom-navigation-items {
    display: flex;
    justify-content: space-around;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 6px 0;
  }
  .bottom-navigation-items li a {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--color-muted);
    font-size: 11px;
    font-weight: 500;
    position: relative;
    padding: 4px 10px;
  }
  .bottom-navigation-items li a.active,
  .bottom-navigation-items li a:hover {
    color: var(--color-primary);
  }
  .bottom-navigation-items svg {
    width: 22px;
    height: 22px;
    margin-bottom: 2px;
    stroke: var(--color-muted);
    transition: var(--transition);
  }
  .bottom-navigation-items a.active svg,
  .bottom-navigation-items a:hover svg {
    stroke: var(--color-primary);
    fill: var(--color-primary-transparent-lite-2);
  }
  .bottom-navigation-items .count {
    position: absolute;
    top: 2px;
    right: 8px;
    background: var(--color-primary);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    border-radius: var(--radius-pill);
    padding: 0 4px;
    min-width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  /* ── 7. Slide-in Sidebar Menu ─────────────────────── */
  .sidebar-menu-overlay {
    position: fixed;
    inset: 0;
    background: rgba(14, 30, 62, 0.5);
    backdrop-filter: blur(4px);
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
  }
  .sidebar-menu-overlay.open { opacity: 1; visibility: visible; }
  .sidebar-menu-wrap {
    position: fixed;
    top: 0;
    left: -320px;
    width: 300px;
    height: 100vh;
    background: #ffffff;
    z-index: 2001;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
    transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
  }
  .sidebar-menu-wrap.open { left: 0; }
  .sidebar-menu-header {
    background: var(--color-primary);
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .sidebar-menu-header h4 { margin: 0; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
  .sidebar-menu-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #ffffff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .sidebar-menu-nav { padding: 16px 0; }
  .sidebar-menu-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: var(--color-heading);
    font-size: 14px;
    font-weight: 500;
    border-bottom: 1px solid var(--color-border-lite);
    transition: var(--transition);
  }
  .sidebar-menu-nav a:hover {
    background: #f8fafc;
    color: var(--color-primary);
  }
  .sidebar-menu-nav a i { font-size: 18px; color: var(--color-muted); }

  /* ── 8. Slide-in Cart Sidebar ─────────────────────── */
  .cart-sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(14, 30, 62, 0.5);
    backdrop-filter: blur(4px);
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
  }
  .cart-sidebar-overlay.open { opacity: 1; visibility: visible; }
  .cart-sidebar {
    position: fixed;
    top: 0;
    right: -440px;
    width: 400px;
    max-width: 90vw;
    height: 100vh;
    background: #ffffff;
    z-index: 2001;
    box-shadow: -4px 0 25px rgba(0, 0, 0, 0.15);
    transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
  }
  .cart-sidebar.open { right: 0; }
  .cart-sidebar-header {
    background: var(--color-heading);
    color: #ffffff;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .cart-sidebar-header h3 { margin: 0; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
  .cart-sidebar-header h3 i { color: var(--color-primary); }
  .cart-sidebar-close {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: #ffffff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .cart-sidebar-body { flex: 1; overflow-y: auto; padding: 16px 20px; }
  .cart-sidebar-item {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--color-border-lite);
  }
  .cart-sidebar-item-img {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-sm);
    background: #f8fafc;
    border: 1px solid var(--color-border);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .cart-sidebar-item-img img { width: 100%; height: 100%; object-fit: contain; }
  .cart-sidebar-item-name { font-size: 13px; font-weight: 600; color: var(--color-heading); line-height: 1.3; }
  .cart-sidebar-item-price { font-size: 14px; font-weight: 700; color: var(--color-primary); margin-top: 4px; }
  .cart-sidebar-footer { padding: 18px 20px; background: #f8fafc; border-top: 1px solid var(--color-border); }
  .btn-cs-go-cart, .btn-cs-buy-now {
    width: 100%;
    padding: 11px;
    font-size: 13px;
    font-weight: 700;
    border-radius: var(--radius-sm);
    cursor: pointer;
    border: none;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    transition: var(--transition);
  }
  .btn-cs-go-cart { background: #e2e8f0; color: var(--color-heading); margin-bottom: 8px; }
  .btn-cs-go-cart:hover { background: #cbd5e1; }
  .btn-cs-buy-now { background: var(--color-primary); color: #ffffff; }
  .btn-cs-buy-now:hover { background: var(--color-primary-hover); }

  /* ── 9. Nool & Crop Footer (FleetCart) ─────────────── */
  .footer-wrap {
    background: #ffffff;
    border-top: 1px solid var(--color-border);
    margin-top: 40px;
    font-size: 13.5px;
  }
  .footer-top {
    padding: 48px 0 32px;
  }
  .footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 36px;
  }
  .footer-col h4.title {
    font-size: 16px;
    font-weight: 700;
    color: var(--color-heading);
    margin: 0 0 16px;
    position: relative;
    padding-bottom: 8px;
  }
  .footer-col h4.title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: var(--color-primary);
  }
  .footer-col ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
  .footer-col ul li a { color: var(--color-muted); transition: var(--transition); display: inline-flex; align-items: center; gap: 6px; }
  .footer-col ul li a:hover { color: var(--color-primary); transform: translateX(3px); }
  .contact-info li { display: flex; align-items: flex-start; gap: 10px; color: var(--color-muted); line-height: 1.4; margin-bottom: 12px; }
  .contact-info li i { font-size: 18px; color: var(--color-primary); margin-top: 2px; }
  .footer-bottom {
    background: #f8fafc;
    border-top: 1px solid var(--color-border);
    padding: 18px 0;
    font-size: 13px;
    color: var(--color-muted);
  }
  .footer-bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
  }
  .footer-text a { color: var(--color-heading); font-weight: 600; }
  .footer-payment-badges { display: flex; align-items: center; gap: 8px; }

  /* ── 10. Toast Notification ───────────────────────── */
  .toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 99999; }
  .toast-msg {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: var(--radius-sm);
    background: var(--color-heading);
    color: #ffffff;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    border-left: 4px solid var(--color-primary);
    animation: toastIn 0.25s ease-out;
  }
  .toast-msg.error { border-left-color: var(--color-danger); }
  .toast-msg.info { border-left-color: #3b82f6; }
  @keyframes toastIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

  /* ── Responsive Breakpoints ───────────────────────── */
  @media (max-width: 1200px) {
    .products-grid { grid-template-columns: repeat(4, 1fr); }
    .features-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 991px) {
    .top-nav-wrap { display: none; }
    .navigation-wrap { display: none; }
    .products-grid { grid-template-columns: repeat(3, 1fr); }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 24px; }
    .header-search, .header-search-container { display: none !important; }
    .header-actions .header-action-item:not(.header-cart) { display: none; }
  }
  @media (max-width: 576px) {
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .features-grid { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: 1fr; gap: 20px; }
    .footer-bottom-inner { flex-direction: column; text-align: center; }
  }
  </style>
  @stack('styles')
</head>
<body data-theme-color="{{ $primaryColor }}">

<!-- Global Live Search Backdrop with Frosted Blur -->
<div class="live-search-backdrop" id="liveSearchBackdrop"></div>

@if(!empty($gtmContainerId))
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmContainerId }}"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

@include('shop.partials.shop-header')

@yield('content')

@include('shop.partials.shop-footer')

<div class="toast-wrap" id="toast-wrap"></div>

<script>
  function showToast(msg, type = 'success') {
    const wrap = document.getElementById('toast-wrap');
    if (!wrap) return;
    const toast = document.createElement('div');
    toast.className = 'toast-msg' + (type === 'error' ? ' error' : (type === 'info' ? ' info' : ''));
    toast.innerHTML = `<i class="las ${type === 'error' ? 'la-exclamation-circle' : 'la-check-circle'}"></i> <span>${msg}</span>`;
    wrap.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 2800);
  }

  // ── Cart Storage & Sync ────────────────────────────
  const CART_KEY = 'store_cart';
  window.getCart = function() {
    try {
      let raw = localStorage.getItem(CART_KEY);
      if (!raw) {
        raw = localStorage.getItem('nellai_cart');
        if (raw) localStorage.setItem(CART_KEY, raw);
      }
      return JSON.parse(raw || '[]');
    } catch(e) { return []; }
  };

  
  // Floating Toast Notification System
  window.showToast = function(title, desc = '', type = 'success', thumb = '') {
    let container = document.getElementById('storeToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'storeToastContainer';
      container.className = 'store-toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'store-toast';

    let iconHtml = '<div class="store-toast-icon ' + (type === 'info' ? 'info' : '') + '"><i class="las ' + (type === 'info' ? 'la-info' : 'la-check') + '"></i></div>';
    if (thumb) {
      iconHtml = '<img src="' + thumb + '" class="store-toast-thumb" alt="Product">';
    }

    toast.innerHTML = `
      ${iconHtml}
      <div class="store-toast-body">
        <div class="store-toast-title">${title}</div>
        ${desc ? `<div class="store-toast-desc">${desc}</div>` : ''}
      </div>
      <button class="store-toast-close" onclick="this.closest('.store-toast').remove()" aria-label="Close"><i class="las la-times"></i></button>
      <div class="store-toast-progress"></div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('hiding');
      setTimeout(() => toast.remove(), 260);
    }, 3200);
  };

  window.saveCart = function(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    localStorage.setItem('nellai_cart', JSON.stringify(cart));
    updateCartCount();
  };

  window.updateCartCount = function() {
    const cart = getCart();
    const count = cart.reduce((sum, item) => sum + (item.qty || 0), 0);
    const subtotal = cart.reduce((sum, item) => sum + (Number(item.price || 0) * (item.qty || 1)), 0);

    document.querySelectorAll('.cart-count-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'inline-flex' : 'none';
      el.classList.remove('badge-pop');
      void el.offsetWidth;
      el.classList.add('badge-pop');
    });
    document.querySelectorAll('.header-cart-total').forEach(el => {
      el.textContent = '₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });
  };

  window.updateWishlistCount = function() {
    const list = getWishlist();
    const count = list.length;
    document.querySelectorAll('.wishlist-count-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'inline-flex' : 'none';
      el.classList.remove('badge-pop');
      void el.offsetWidth;
      el.classList.add('badge-pop');
    });
  };

  window.addToCartDetailed = function(p) {
    const cart = getCart();
    const existing = cart.find(c => c.id === p.id);
    if (existing) {
      existing.qty = (existing.qty || 0) + Math.max(1, parseInt(p.qty) || 1);
    } else {
      cart.push({
        id: p.id,
        qty: Math.max(1, parseInt(p.qty) || 1),
        name: p.name,
        price: p.price,
        image: p.image,
        slug: p.slug
      });
    }
    saveCart(cart);
    showToast('Added to Cart!', p.name || 'Item added to your shopping cart', 'success', p.image || '');
  };

  window.addToCart = function(productId, qty = 1, name = '', price = 0, image = '', slug = '') {
    window.addToCartDetailed({ id: productId, qty, name, price, image, slug });
  };

  window.buyNow = async function(productId, qty = 1, name = '', price = 0, image = '', slug = '') {
    addToCart(productId, qty, name, price, image, slug);
    window.location.href = '/buy-now';
  };

  window.removeFromCart = function(productId) {
    const cart = getCart().filter(c => c.id !== productId);
    saveCart(cart);
  };

  window.updateCartQty = function(productId, qty) {
    const cart = getCart();
    const item = cart.find(c => c.id === productId);
    if (item) {
      item.qty = Math.max(1, qty);
      saveCart(cart);
    }
  };

  // ── Wishlist (localStorage + Database Sync) ────────
  const WISH_KEY = 'store_wishlist';
  window.getWishlist = function() {
    try {
      let raw = localStorage.getItem(WISH_KEY);
      if (!raw) {
        raw = localStorage.getItem('nellai_wishlist');
        if (raw) localStorage.setItem(WISH_KEY, raw);
      }
      return JSON.parse(raw || '[]');
    } catch(e) { return []; }
  };

  window.toggleWishlist = function(productId, btn) {
    const list = getWishlist();
    const idx = list.indexOf(productId);
    if (idx >= 0) {
      list.splice(idx, 1);
      if (btn) { btn.classList.remove('active'); btn.querySelector('i')?.classList.replace('las','lar'); }
      showToast('Removed from wishlist', 'info');
    } else {
      list.push(productId);
      if (btn) {
        btn.classList.add('active', 'animating');
        btn.querySelector('i')?.classList.replace('lar','las');
        setTimeout(() => btn.classList.remove('animating'), 500);
      }
      showToast('Saved to Wishlist!', 'You can view all saved items in your wishlist', 'success');
    }
    localStorage.setItem(WISH_KEY, JSON.stringify(list));
    localStorage.setItem('nellai_wishlist', JSON.stringify(list));
    updateWishlistCount();

    // Sync with server if customer is logged in
    try {
      fetch('/api/wishlist/toggle', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
      }).catch(() => {});
    } catch(e) {}
  };

  // ── Sidebar Menu Drawer ────────────────────────────
  window.toggleSidebarMenu = function() {
    const menu = document.getElementById('sidebar-menu-drawer');
    const overlay = document.getElementById('sidebar-menu-overlay');
    if (menu && overlay) {
      menu.classList.toggle('open');
      overlay.classList.toggle('open');
      document.body.style.overflow = menu.classList.contains('open') ? 'hidden' : '';
    }
  };

  // ── Cart Sidebar Drawer ────────────────────────────
  window.openCartSidebar = function() {
    const sb = document.getElementById('cart-sidebar');
    const overlay = document.getElementById('cart-sidebar-overlay');
    if (sb && overlay) {
      sb.classList.add('open');
      overlay.classList.add('open');
      document.body.style.overflow = 'hidden';
      sbLoad();
    }
  };
  window.closeCartSidebar = function() {
    const sb = document.getElementById('cart-sidebar');
    const overlay = document.getElementById('cart-sidebar-overlay');
    if (sb && overlay) {
      sb.classList.remove('open');
      overlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  };

  let sbItems = [];
  async function sbLoad() {
    const cart = getCart();
    const list = document.getElementById('cs-items-list');
    const empty = document.getElementById('cs-empty');
    const subtotalEl = document.getElementById('cs-subtotal');
    const footer = document.getElementById('cs-footer');

    if (!cart.length) {
      sbItems = [];
      if (list) list.innerHTML = '';
      if (empty) empty.style.display = 'block';
      if (subtotalEl) subtotalEl.textContent = '0.00';
      if (footer) footer.style.display = 'none';
      return;
    }
    try {
      const res = await fetch('/api/cart/items', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''},
        body: JSON.stringify({ ids: cart.map(c => c.id) })
      });
      const data = await res.json();
      if (data.success && data.data) {
        sbItems = data.data.map(p => {
          const local = cart.find(c => c.id === p.id);
          return { id: p.id, name: p.name, slug: p.slug, image: p.image, price: p.price, qty: local ? local.qty : 1 };
        });
        sbRender();
      }
    } catch(e) { console.error(e); }
  }

  function sbRender() {
    const list = document.getElementById('cs-items-list');
    const empty = document.getElementById('cs-empty');
    const subtotalEl = document.getElementById('cs-subtotal');
    const footer = document.getElementById('cs-footer');

    if (!sbItems.length) {
      if (empty) empty.style.display = 'block';
      if (list) list.innerHTML = '';
      if (subtotalEl) subtotalEl.textContent = '0.00';
      if (footer) footer.style.display = 'none';
      return;
    }
    if (empty) empty.style.display = 'none';
    if (footer) footer.style.display = 'block';

    list.innerHTML = sbItems.map(item => `
      <div class="cart-sidebar-item" data-id="${item.id}">
        <a href="/product/${item.slug || item.id}" class="cart-sidebar-item-img">
          ${item.image ? `<img src="${item.image}" alt="">` : '<i class="las la-image" style="color:#cbd5e1; font-size:24px;"></i>'}
        </a>
        <div style="flex:1;">
          <a href="/product/${item.slug || item.id}" class="cart-sidebar-item-name">${item.name || 'Product #' + item.id}</a>
          <div class="cart-sidebar-item-price">₹${Number(item.price || 0).toLocaleString('en-IN', {minimumFractionDigits: 2})} × ${item.qty}</div>
          <button onclick="sbRemove(${item.id})" style="background:none; border:none; color:#ef4444; font-size:12px; cursor:pointer; padding:4px 0; display:flex; align-items:center; gap:4px; margin-top:4px;">
            <i class="las la-trash"></i> Remove
          </button>
        </div>
      </div>
    `).join('');

    const subtotal = sbItems.reduce((s, i) => s + Number(i.price || 0) * i.qty, 0);
    if (subtotalEl) subtotalEl.textContent = subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
  }

  window.sbRemove = function(id) {
    removeFromCart(id);
    updateCartCount();
    sbItems = sbItems.filter(i => i.id !== id);
    sbRender();
    if (typeof renderCart === 'function') renderCart();
  };

  // Init
  document.addEventListener('DOMContentLoaded', () => {
    // Scroll Reveal Observer
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.06, rootMargin: '0px 0px -40px 0px' });

      document.querySelectorAll('.reveal-fade-up, .product-section, .category-strip, .features-wrap, .shop-products-grid, .cat-products-grid').forEach(el => {
        el.classList.add('reveal-fade-up');
        observer.observe(el);
      });
    }

    updateCartCount();
    updateWishlistCount();

    const wishlist = getWishlist();
    document.querySelectorAll('.btn-wishlist').forEach(btn => {
      const id = parseInt(btn.dataset.productId);
      if (id && wishlist.includes(id)) {
        btn.classList.add('active');
        btn.querySelector('i')?.classList.replace('lar','las');
      }
    });
  });

  // ================================================================
  // LIVE PRODUCT SEARCH CONTROLLER WITH BACKDROP BLUR & ANIMATIONS
  // ================================================================
  (function() {
    const backdrop = document.getElementById('liveSearchBackdrop');

    function highlightMatch(text, query) {
      if (!query) return escapeHtml(text);
      const escapedText = escapeHtml(text);
      const escapedQuery = escapeHtml(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const regex = new RegExp(`(${escapedQuery})`, 'gi');
      return escapedText.replace(regex, '<mark class="live-search-match">$1</mark>');
    }

    function escapeHtml(str) {
      return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function initLiveSearch(config) {
      const container = document.getElementById(config.containerId);
      const input = document.getElementById(config.inputId);
      const dropdown = document.getElementById(config.dropdownId);
      const clearBtn = document.getElementById(config.clearBtnId);
      const spinner = document.getElementById(config.spinnerId);
      const catSelect = config.catSelectId ? document.getElementById(config.catSelectId) : null;

      if (!container || !input || !dropdown) return null;

      let debounceTimer = null;
      let abortController = null;
      let selectedIndex = -1;
      let currentItems = [];
      const searchCache = new Map();

      function showSpinner() {
        if (spinner) spinner.style.display = 'flex';
        if (clearBtn) clearBtn.style.display = 'none';
      }

      function hideSpinner() {
        if (spinner) spinner.style.display = 'none';
        if (clearBtn) clearBtn.style.display = input.value.trim() ? 'inline-flex' : 'none';
      }

      function openSearch() {
        container.classList.add('is-focused');
        if (backdrop) backdrop.classList.add('is-active');
      }

      function closeSearch() {
        container.classList.remove('is-focused');
        dropdown.classList.remove('is-open');
        dropdown.innerHTML = '';
        selectedIndex = -1;
        currentItems = [];
        hideSpinner();
        if (backdrop) {
          const anyOpen = document.querySelectorAll('.header-search-container.is-focused, .mobile-search-container.is-focused');
          if (anyOpen.length <= 1) {
            backdrop.classList.remove('is-active');
          }
        }
      }

      function renderSkeleton() {
        dropdown.innerHTML = `
          <div class="live-search-header">
            <span>Searching products…</span>
          </div>
          <div class="live-search-items-list">
            ${[1, 2, 3].map(() => `
              <div class="live-search-skeleton">
                <div class="live-search-skeleton-img"></div>
                <div class="live-search-skeleton-lines">
                  <div class="live-search-skeleton-line" style="width: 35%;"></div>
                  <div class="live-search-skeleton-line" style="width: 85%;"></div>
                  <div class="live-search-skeleton-line" style="width: 50%;"></div>
                </div>
              </div>
            `).join('')}
          </div>
        `;
        dropdown.classList.add('is-open');
      }

      function renderResults(data) {
        const q = data.query || input.value.trim();
        currentItems = data.products || [];
        selectedIndex = -1;

        if (!currentItems.length) {
          dropdown.innerHTML = `
            <div class="live-search-empty">
              <div class="live-search-empty-icon">
                <i class="las la-search"></i>
              </div>
              <div class="live-search-empty-title">No matching products found</div>
              <div class="live-search-empty-sub">We couldn't find anything for "<strong>${escapeHtml(q)}</strong>". Try checking for typos or searching another keyword.</div>
            </div>
          `;
          dropdown.classList.add('is-open');
          return;
        }

        const totalCount = data.total || currentItems.length;
        const html = `
          <div class="live-search-header">
            <span>Products</span>
            <span class="live-search-header-badge">${totalCount} ${totalCount === 1 ? 'match' : 'matches'}</span>
          </div>
          <div class="live-search-items-list">
            ${currentItems.map((p, idx) => `
              <a href="${p.url}" class="live-search-item" data-index="${idx}" style="animation-delay: ${idx * 0.015}s;">
                <img src="${p.image}" alt="${escapeHtml(p.name)}" class="live-search-item-img" onerror="this.src='/assets/images/placeholder.png';">
                <div class="live-search-item-body">
                  ${p.category_name ? `<div class="live-search-item-cat">${escapeHtml(p.category_name)}</div>` : ''}
                  <div class="live-search-item-title">${highlightMatch(p.name, q)}</div>
                  <div class="live-search-item-meta">
                    <span class="live-search-item-price">${p.formatted_effective_price}</span>
                    ${p.is_on_sale ? `<span class="live-search-item-old-price">${p.formatted_price}</span>` : ''}
                    ${p.discount_percent > 0 ? `<span class="live-search-item-discount">${p.discount_percent}% OFF</span>` : ''}
                    <span class="live-search-item-stock ${p.in_stock ? '' : 'out-of-stock'}">
                      <i class="las ${p.in_stock ? 'la-check-circle' : 'la-times-circle'}"></i>
                      ${p.in_stock ? 'In Stock' : 'Out of Stock'}
                    </span>
                  </div>
                </div>
                <i class="las la-angle-right live-search-arrow"></i>
              </a>
            `).join('')}
          </div>
          <div class="live-search-footer">
            <a href="${data.view_all_url}" class="live-search-view-all-link">
              <span>View all ${totalCount} results for "${escapeHtml(q)}"</span>
              <i class="las la-arrow-right"></i>
            </a>
            <span class="live-search-keyboard-hint d-none d-sm-flex">
              <span><span class="live-search-key-badge">↑</span> <span class="live-search-key-badge">↓</span> Navigate</span>
              <span><span class="live-search-key-badge">↵</span> Select</span>
              <span><span class="live-search-key-badge">esc</span> Close</span>
            </span>
          </div>
        `;

        dropdown.innerHTML = html;
        dropdown.classList.add('is-open');

        // Mouse hover selection
        dropdown.querySelectorAll('.live-search-item').forEach((el, idx) => {
          el.addEventListener('mouseenter', () => {
            setSelectedItem(idx, false);
          });
        });
      }

      function setSelectedItem(index, scrollIntoView = true) {
        const items = dropdown.querySelectorAll('.live-search-item');
        if (!items.length) return;

        items.forEach(el => el.classList.remove('is-selected'));
        selectedIndex = Math.max(-1, Math.min(index, items.length - 1));

        if (selectedIndex >= 0 && items[selectedIndex]) {
          items[selectedIndex].classList.add('is-selected');
          if (scrollIntoView) {
            items[selectedIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
          }
        }
      }

      function fetchResults() {
        const query = input.value.trim();
        const cat = catSelect ? catSelect.value : '';

        if (query.length < 1) {
          dropdown.classList.remove('is-open');
          dropdown.innerHTML = '';
          hideSpinner();
          return;
        }

        const cacheKey = (cat || 'all') + ':' + query.toLowerCase();
        if (searchCache.has(cacheKey)) {
          hideSpinner();
          renderResults(searchCache.get(cacheKey));
          return;
        }

        if (abortController) {
          abortController.abort();
        }
        abortController = new AbortController();

        showSpinner();
        if (!dropdown.classList.contains('is-open')) {
          renderSkeleton();
        }

        const params = new URLSearchParams({ q: query });
        if (cat) params.append('category', cat);

        fetch(`/api/search/live?${params.toString()}`, {
          signal: abortController.signal,
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
          if (!res.ok) throw new Error('Network response error');
          return res.json();
        })
        .then(data => {
          searchCache.set(cacheKey, data);
          hideSpinner();
          renderResults(data);
        })
        .catch(err => {
          if (err.name === 'AbortError') return;
          hideSpinner();
          console.error('Live search error:', err);
        });
      }

      function scheduleFetch() {
        clearTimeout(debounceTimer);
        const query = input.value.trim();
        const cat = catSelect ? catSelect.value : '';
        if (clearBtn) {
          clearBtn.style.display = query ? 'inline-flex' : 'none';
        }
        if (query.length < 1) {
          dropdown.classList.remove('is-open');
          dropdown.innerHTML = '';
          hideSpinner();
          return;
        }

        const cacheKey = (cat || 'all') + ':' + query.toLowerCase();
        if (searchCache.has(cacheKey)) {
          hideSpinner();
          renderResults(searchCache.get(cacheKey));
          return;
        }

        showSpinner();
        debounceTimer = setTimeout(fetchResults, 75);
      }

      // Event Listeners
      input.addEventListener('input', () => {
        openSearch();
        scheduleFetch();
      });

      input.addEventListener('focus', () => {
        openSearch();
        if (input.value.trim().length >= 1) {
          if (!dropdown.classList.contains('is-open') || !dropdown.children.length) {
            fetchResults();
          } else {
            dropdown.classList.add('is-open');
          }
        }
      });

      if (catSelect) {
        catSelect.addEventListener('change', () => {
          if (input.value.trim().length >= 1) {
            fetchResults();
          }
        });
      }

      if (clearBtn) {
        clearBtn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          input.value = '';
          clearBtn.style.display = 'none';
          dropdown.classList.remove('is-open');
          dropdown.innerHTML = '';
          input.focus();
        });
      }

      // Keyboard navigation (ArrowDown, ArrowUp, Enter, ESC)
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          e.preventDefault();
          closeSearch();
          input.blur();
          return;
        }

        if (!dropdown.classList.contains('is-open')) return;

        const items = dropdown.querySelectorAll('.live-search-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const nextIdx = selectedIndex < items.length - 1 ? selectedIndex + 1 : 0;
          setSelectedItem(nextIdx, true);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prevIdx = selectedIndex > 0 ? selectedIndex - 1 : items.length - 1;
          setSelectedItem(prevIdx, true);
        } else if (e.key === 'Enter') {
          if (selectedIndex >= 0 && items[selectedIndex]) {
            e.preventDefault();
            window.location.href = items[selectedIndex].getAttribute('href');
          }
        }
      });

      return {
        close: closeSearch
      };
    }

    // Initialize both Desktop & Mobile controllers
    const desktopCtrl = initLiveSearch({
      containerId: 'desktopSearchContainer',
      inputId: 'desktopSearchInput',
      dropdownId: 'desktopSearchDropdown',
      clearBtnId: 'desktopSearchClear',
      spinnerId: 'desktopSearchSpinner',
      catSelectId: 'desktopSearchCat',
    });

    const mobileCtrl = initLiveSearch({
      containerId: 'mobileSearchContainer',
      inputId: 'mobileSearchInput',
      dropdownId: 'mobileSearchDropdown',
      clearBtnId: 'mobileSearchClear',
      spinnerId: 'mobileSearchSpinner',
      catSelectId: null,
    });

    window.LiveSearch = {
      closeAll: function() {
        if (desktopCtrl) desktopCtrl.close();
        if (mobileCtrl) mobileCtrl.close();
        if (backdrop) backdrop.classList.remove('is-active');
      }
    };

    // Click outside to close
    document.addEventListener('click', (e) => {
      if (!e.target.closest('#desktopSearchContainer') && !e.target.closest('#mobileSearchContainer')) {
        window.LiveSearch.closeAll();
      }
    });

    // Global ESC key to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        window.LiveSearch.closeAll();
      }
    });
  })();
</script>

<!-- Sidebar Menu (drawer) -->
<div class="sidebar-menu-overlay" id="sidebar-menu-overlay" onclick="toggleSidebarMenu()"></div>
<aside class="sidebar-menu-wrap" id="sidebar-menu-drawer">
  <div class="sidebar-menu-header">
    <h4>Menu</h4>
    <button class="sidebar-menu-close" onclick="toggleSidebarMenu()"><i class="las la-times"></i></button>
  </div>
  <nav class="sidebar-menu-nav">
    <a href="{{ url('/') }}"><i class="las la-home"></i> Home</a>
    <a href="{{ url('/products') }}"><i class="las la-tshirt"></i> All Products</a>
    @php
      $menuCats = \App\Models\Category::active()->limit(8)->get();
    @endphp
    @foreach($menuCats as $mCat)
      <a href="{{ url('/shop?category=' . $mCat->id) }}"><i class="las la-angle-right"></i> {{ $mCat->name }}</a>
    @endforeach
    <a href="{{ route('shop.wishlist') }}"><i class="las la-heart"></i> Wishlist</a>
    <a href="{{ url('/cart') }}"><i class="las la-shopping-bag"></i> Cart</a>
    @auth('customer')
      <a href="{{ route('shop.orders.index') }}"><i class="las la-box"></i> My Orders</a>
      <a href="{{ route('shop.account') }}"><i class="las la-user"></i> My Account</a>
      <a href="{{ route('shop.logout') }}" onclick="event.preventDefault(); document.getElementById('side-logout-form').submit();" style="color: #ef4444;"><i class="las la-sign-out-alt"></i> Logout</a>
      <form id="side-logout-form" action="{{ route('shop.logout') }}" method="POST" style="display:none;">@csrf</form>
    @else
      <a href="{{ route('shop.login.email') }}"><i class="las la-sign-in-alt"></i> Login / Register</a>
    @endauth
  </nav>
</aside>

<!-- Slide-in Cart Sidebar -->
<div class="cart-sidebar-overlay" id="cart-sidebar-overlay" onclick="closeCartSidebar()"></div>
<aside class="cart-sidebar" id="cart-sidebar">
  <div class="cart-sidebar-header">
    <h3><i class="las la-shopping-bag"></i> Shopping Cart</h3>
    <button class="cart-sidebar-close" onclick="closeCartSidebar()"><i class="las la-times"></i></button>
  </div>
  <div class="cart-sidebar-body">
    <div id="cs-empty" style="display:none; text-align:center; padding:50px 20px;">
      <i class="las la-shopping-bag" style="font-size:50px; color:#cbd5e1; margin-bottom:12px; display:block;"></i>
      <h4 style="font-size:16px; font-weight:700; color:var(--color-heading); margin-bottom:6px;">Your cart is empty</h4>
      <p style="font-size:13px; color:var(--color-muted); margin-bottom:16px;">Add items to your cart to checkout</p>
      <a href="{{ url('/products') }}" onclick="closeCartSidebar()" style="display:inline-block; padding:9px 24px; background:var(--color-primary); color:#fff; border-radius:var(--radius-sm); font-weight:600; text-decoration:none;">Shop Now</a>
    </div>
    <div id="cs-items-list"></div>
  </div>
  <div class="cart-sidebar-footer" id="cs-footer" style="display:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; font-size:15px; font-weight:700; color:var(--color-heading);">
      <span>Subtotal:</span>
      <span style="color:var(--color-primary);">₹<span id="cs-subtotal">0.00</span></span>
    </div>
    <a href="{{ url('/cart') }}" onclick="closeCartSidebar()" class="btn-cs-go-cart">View Cart</a>
    <a href="{{ url('/buy-now') }}" onclick="closeCartSidebar()" class="btn-cs-buy-now">Proceed to Checkout</a>
  </div>
</aside>

@stack('scripts')
</body>
</html>
