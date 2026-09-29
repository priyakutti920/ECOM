@php
  $storeName = \App\Models\StoreSetting::getStoreName();
  $metaTitleSetting = \App\Models\StoreSetting::getValue('meta_title');
  $metaDescriptionSetting = \App\Models\StoreSetting::getValue('meta_description');
  $metaKeywordsArr = json_decode(\App\Models\StoreSetting::getValue('meta_keywords', '[]'), true);
  $metaKeywordsSetting = is_array($metaKeywordsArr) ? implode(', ', $metaKeywordsArr) : '';
  $canonicalUrlSetting = \App\Models\StoreSetting::getValue('canonical_url');
  $gtmContainerId = \App\Models\StoreSetting::getValue('gtm_container_id');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', $metaTitleSetting ?: $storeName)</title>
  <meta name="description" content="@yield('description', $metaDescriptionSetting ?: 'Shop online at ' . $storeName)">
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
 <link href="https://fonts.googleapis.com/css2?family=Amazon+Ember:wght@300;400;500;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
 <link rel="icon" href="{{ \App\Models\StoreSetting::getFaviconUrl() }}" type="image/x-icon">
 <style>
 :root {
 --amazon-orange: {{ \App\Models\StoreSetting::getSecondaryColor() }};
 --amazon-orange-dark: {{ \App\Models\StoreSetting::getSecondaryColor() }};
 --amazon-dark: {{ \App\Models\StoreSetting::getPrimaryColor() }};
 --amazon-charcoal: #232F3E;
 --amazon-blue: #007185;
 --white: #ffffff;
 --light-gray: #f3f3f3;
 --medium-gray: #565959;
 --border: #ddd;
 --red: #c7511f;
 }

 * { margin: 0; padding: 0; box-sizing: border-box; }
 html, body { scroll-behavior: smooth; }
 body {
 font-family: 'Amazon Ember', Arial, sans-serif;
 background: var(--white);
 color: #0F1111;
 min-height: 100vh;
 overflow-x: hidden;
 }
 a { text-decoration: none; color: inherit; }
 img { max-width: 100%; }

 /* Header (reused) */
 .header { background: var(--amazon-charcoal); color: var(--white); position: sticky; top: 0; z-index: 1000; }
 .header-top { background: var(--amazon-dark); padding: 4px 16px; display: none; }
 .header-top-content { max-width: 1400px; margin: 0 auto; display: flex; align-items: center; gap: 16px; font-size: 12px; }
 .header-top a { color: var(--white); display: flex; align-items: center; gap: 4px; }
 .header-top a:hover { text-decoration: underline; }
 .header-top .location-icon { color: var(--amazon-orange); }
 .header-top-right { display: flex; gap: 16px; margin-left: auto; }

 .header-main { display: flex; align-items: center; padding: 8px 16px; gap: 8px; max-width: 1400px; margin: 0 auto; }
 .logo { display: flex; align-items: center; padding: 8px 10px; border-radius: 4px; transition: background 0.2s; }
 .logo:hover { background: #37475a; }
 .logo-text { font-size: 22px; font-weight: 700; font-style: italic; color: var(--white); padding-bottom: 2px; }
 .logo-text .in { color: var(--amazon-orange); margin-left: 2px; }

 .search-bar { flex: 1; max-width: 680px; display: flex; height: 38px; border-radius: 4px; overflow: hidden; }
 .search-bar select { background: #e6e6e6; border: none; padding: 0 8px; font-size: 12px; cursor: pointer; min-width: 50px; border-right: 1px solid #ccc; }
 .search-bar input { flex: 1; padding: 8px 12px; border: none; font-size: 14px; outline: none; }
 .search-bar button { padding: 0 14px; border: none; background: var(--amazon-orange); color: var(--amazon-dark); cursor: pointer; font-size: 16px; transition: background 0.2s; }
 .search-bar button:hover { background: #f3a847; }

 .header-nav { display: flex; align-items: center; gap: 16px; }
 .header-nav a { color: var(--white); text-decoration: none; display: flex; flex-direction: column; padding: 4px 8px; border-radius: 4px; transition: background 0.2s; }
 .header-nav a:hover { background: #37475a; text-decoration: none; }
 .nav-label { font-size: 11px; color: #ccc; }
 .nav-main-text { font-size: 14px; font-weight: 600; }

 .cart-link { display: flex; align-items: center; gap: 6px; position: relative; padding: 4px 8px; border-radius: 4px; }
 .cart-link:hover { background: #37475a; }
 .cart-icon { font-size: 26px; color: var(--white); }
 .cart-count { position: absolute; top: -2px; left: 18px; background: var(--amazon-orange); color: var(--amazon-dark); font-size: 11px; font-weight: 700; padding: 1px 5px; border-radius: 50%; }
 .cart-text { display: flex; flex-direction: column; }
 .cart-text span:first-child { font-size: 11px; color: #ccc; }
 .cart-text span:last-child { font-size: 14px; font-weight: 700; }

 .account-link { display: flex; align-items: center; padding: 4px 8px; border-radius: 4px; color: var(--white); text-decoration: none; }
 .account-link:hover { background: #37475a; }
 .account-icon { font-size: 22px; color: var(--white); }

 /* Nav bar */
 .nav-bar { background: var(--amazon-charcoal); padding: 0 16px; border-top: 1px solid #3a4553; }
 .nav-bar ul { list-style: none; display: flex; align-items: center; max-width: 1400px; margin: 0 auto; padding: 6px 0; gap: 4px; font-size: 13px; overflow-x: auto; white-space: nowrap; -ms-overflow-style: none; scrollbar-width: none; }
 .nav-bar ul::-webkit-scrollbar { display: none; }
 .nav-bar a { color: var(--white); text-decoration: none; font-weight: 500; padding: 4px 10px; border-radius: 4px; transition: background 0.2s; }
 .nav-bar a:hover { text-decoration: none; background: #37475a; }
 .nav-bar li:first-child a { font-weight: 600; display: flex; align-items: center; gap: 6px; }

 .mobile-menu-btn { display: flex; background: none; border: none; color: var(--white); font-size: 22px; padding: 8px; cursor: pointer; align-items: center; justify-content: center; }
 .mobile-menu-btn:hover { color: var(--amazon-orange); }

 /* Banner */
 .banner { position: relative; background: var(--white); margin-bottom: 16px; overflow: hidden; }
 .banner-slider { display: flex; transition: transform 0.5s ease; width: 100%; }
 .banner-slide { min-width: 100%; min-height: 200px; display: flex; align-items: center; justify-content: center; position: relative; background-size: cover; background-position: center center; }
 .banner-slide::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.05) 50%, transparent 100%); }
 .banner-content { position: relative; z-index: 1; padding: 20px 24px; text-align: center; width: 100%; max-width: 100%; }
 .banner-content h2 { font-size: 24px; font-weight: 700; margin-bottom: 8px; color: var(--white); text-shadow: 0 2px 4px rgba(0,0,0,0.3); line-height: 1.2; }
 .banner-content p { font-size: 13px; color: rgba(255,255,255,0.95); margin-bottom: 12px; line-height: 1.4; }
 .banner-content .btn { display: inline-block; padding: 8px 20px; background: var(--white); color: #333; text-decoration: none; border-radius: 18px; font-weight: 600; font-size: 12px; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
 .banner-content .btn:hover { background: #f5f5f5; transform: scale(1.02); }
 .banner-nav { display: none; } /* Arrows removed */
 .banner-dots { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 10; padding: 4px 8px; background: rgba(0,0,0,0.2); border-radius: 12px; }
 .banner-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.2s; border: none; }
 .banner-dot:hover { background: rgba(255,255,255,0.8); }
 .banner-dot.active { background: var(--white); transform: scale(1.2); }

 /* Product sections */
 .product-section { max-width: 1400px; margin: 0 auto 16px; background: var(--white); padding: 0 12px; }
 .section-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid #e7e7e7; }
 .section-header h3 { font-size: 20px; font-weight: 600; color: var(--amazon-charcoal); }
 .section-header h3 a { color: var(--amazon-charcoal); text-decoration: none; }
 .section-header h3 a:hover { color: var(--amazon-orange); text-decoration: underline; }
 .section-header .view-all { color: var(--amazon-blue); text-decoration: none; font-size: 13px; font-weight: 500; }
 .section-header .view-all:hover { text-decoration: underline; color: #c45500; }

 .products-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; padding: 16px 0; }
 .product-card { background: var(--white); padding: 14px 10px; cursor: pointer; transition: all 0.2s; border: 1px solid transparent; border-radius: 4px; position: relative; display: flex; flex-direction: column; }
 .product-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); border-color: #e7e7e7; }
 .product-image { width: 100%; height: 170px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: var(--light-gray); border-radius: 4px; overflow: hidden; position: relative; flex-shrink: 0; box-sizing: border-box; }
 .product-image img { max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; transition: transform 0.3s; display:block; }
 .product-card:hover .product-image img { transform: scale(1.03); }
 .product-info { padding-top: 6px; display: flex; flex-direction: column; flex: 1; }
 .product-brand { font-size: 11px; color: var(--medium-gray); margin-bottom: 2px; }
 .product-name { font-size: 13px; color: var(--amazon-charcoal); margin-bottom: 6px; font-weight: 500; line-height: 1.35; height: 36px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
 .product-name:hover { color: #c45500; }
 .product-price { display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px; }
 .current-price { font-size: 16px; font-weight: 700; color: #0F1111; }
 .original-price { font-size: 12px; color: var(--medium-gray); text-decoration: line-through; }
 .discount { font-size: 11px; color: #c45500; font-weight: 500; }
 .product-rating { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
 .rating-badge { background: var(--amazon-orange); color: var(--white); padding: 2px 6px; border-radius: 2px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 2px; }
 .rating-badge i { font-size: 10px; }
 .rating-count { font-size: 11px; color: var(--amazon-blue); }
 .prime-badge { display: inline-flex; align-items: center; gap: 4px; color: #00a8e1; font-size: 11px; font-weight: 600; margin-top: 4px; }
 .prime-badge i { font-size: 14px; }
 .card-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: auto; padding-top: 8px; }
 .card-add-cart, .card-buy-now { padding: 7px 6px; font-size: 11px; font-weight: 700; border-radius: 100px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px; transition: all 0.2s; border: 1px solid; }
 .card-add-cart { background: #ffd814; border-color: #fcd200; color: #0F1111; }
 .card-add-cart:hover { background: #f7ca00; }
 .card-buy-now { background: #ffa41c; border-color: #ff8f00; color: #0F1111; }
 .card-buy-now:hover { background: #fa8900; }

 /* Wishlist */
 .wishlist-btn { position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; background: var(--white); border: 1px solid #d5d9d9; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; z-index: 10; }
 .wishlist-btn:hover { border-color: #007185; }
 .wishlist-btn i { color: #565959; font-size: 11px; transition: all 0.2s; }
 .wishlist-btn:hover i { color: #e91e63; }
 .wishlist-btn.active i { color: #e91e63; }
 .quick-badge { position: absolute; top: 6px; left: 6px; background: var(--amazon-orange); color: var(--white); padding: 2px 6px; border-radius: 2px; font-size: 10px; font-weight: 700; z-index: 10; }

 /* Deal banner */
 .deal-banner { background: linear-gradient(90deg, #e67e22, #f39c12); color: var(--white); padding: 14px 20px; max-width: 1400px; margin: 16px auto 0; display: flex; align-items: center; justify-content: space-between; border-radius: 4px; }
 .deal-banner h3 { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
 .deal-banner .timer { display: flex; gap: 4px; align-items: center; }
 .deal-banner .timer span { background: var(--white); color: #333; padding: 3px 8px; border-radius: 3px; font-weight: 700; font-size: 14px; }

 /* Offer banner */
 .offer-banner { max-width: 1400px; margin: 16px auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; padding: 0 12px; }
 .offer-card { background: var(--white); border-radius: 4px; padding: 20px 16px; cursor: pointer; transition: all 0.2s; border: 1px solid #e7e7e7; text-align: center; }
 .offer-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
 .offer-card i { font-size: 32px; color: var(--amazon-charcoal); margin-bottom: 10px; }
 .offer-card h4 { font-size: 14px; font-weight: 600; margin-bottom: 4px; color: var(--amazon-charcoal); }
 .offer-card p { font-size: 11px; color: var(--medium-gray); }

 /* Categories grid */
 .categories-grid { max-width: 1400px; margin: 16px auto; display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; padding: 0 12px; }

 /* Reusable category tile (round icon + name below) */
 .cat-tile {
     flex: 0 0 auto;
     width: 90px;
     display: flex;
     flex-direction: column;
     align-items: center;
     gap: 8px;
     padding: 6px 4px;
     text-align: center;
     text-decoration: none;
     color: var(--amazon-charcoal);
     border-radius: 10px;
     transition: background 0.18s ease, transform 0.18s ease;
     scroll-snap-align: start;
 }
 .cat-tile:hover { background: #f7f7f7; }
 .cat-tile:hover .cat-tile-circle { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.10); }
 .cat-tile.is-active { background: #fff5e0; }
 .cat-tile.is-active .cat-tile-name { color: var(--amazon-orange); font-weight: 700; }

 .cat-tile-circle {
     width: 64px;
     height: 64px;
     border-radius: 50%;
     background: #f3f3f3;
     border: 2px solid #e7e7e7;
     display: flex;
     align-items: center;
     justify-content: center;
     overflow: hidden;
     flex-shrink: 0;
     transition: all 0.18s ease;
     position: relative;
 }
 .cat-tile-circle img {
     width: 100%;
     height: 100%;
     object-fit: cover;
     border-radius: 50%;
     display: block;
 }
 .cat-tile-circle i {
     font-size: 26px;
     line-height: 1;
 }
 .cat-tile.is-active .cat-tile-circle {
     border-color: var(--amazon-orange);
     box-shadow: 0 0 0 3px rgba(255,153,0,0.20);
 }

 .cat-tile-name {
     font-size: 12px;
     font-weight: 600;
     line-height: 1.25;
     max-width: 88px;
     overflow: hidden;
     text-overflow: ellipsis;
     white-space: nowrap;
     color: inherit;
 }

 .cat-tiles-row {
     display: flex;
     align-items: flex-start;
     gap: 8px;
     overflow-x: auto;
     padding: 6px 4px 10px;
     -webkit-overflow-scrolling: touch;
     scroll-snap-type: x proximity;
     scrollbar-width: thin;
 }
 .cat-tiles-row::-webkit-scrollbar { height: 4px; }
 .cat-tiles-row::-webkit-scrollbar-thumb { background: #d5d9d9; border-radius: 2px; }
 .category-card { background: var(--white); border-radius: 4px; padding: 18px 14px; text-align: center; cursor: pointer; transition: all 0.2s; border: 1px solid #e7e7e7; }
 .category-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
 .category-card i { font-size: 32px; color: var(--amazon-charcoal); margin-bottom: 10px; }
 .category-card h4 { font-size: 12px; font-weight: 600; margin-bottom: 4px; color: var(--amazon-charcoal); }
 .category-card p { font-size: 10px; color: var(--medium-gray); }
 .category-card img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; margin-bottom: 8px; }

 /* Footer */
 .footer { background: var(--white); margin-top: 24px; }
 .back-to-top { background: var(--amazon-charcoal); color: var(--white); text-align: center; padding: 12px; cursor: pointer; font-size: 13px; font-weight: 600; transition: background 0.2s; }
 .back-to-top:hover { background: #37475a; }
 .footer-main { background: var(--amazon-dark); color: var(--white); padding: 40px 20px 30px; }
 .footer-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; }
 .footer-section h4 { font-size: 13px; font-weight: 700; margin-bottom: 12px; color: var(--white); }
 .footer-section ul { list-style: none; }
 .footer-section ul li { margin-bottom: 8px; }
 .footer-section ul li a { color: #ddd; text-decoration: none; font-size: 12px; transition: color 0.2s; }
 .footer-section ul li a:hover { color: var(--white); text-decoration: underline; }
 .footer-divider { max-width: 1400px; margin: 0 auto; border-top: 1px solid #3a4553; padding: 20px 20px 16px; display: flex; align-items: center; justify-content: center; gap: 12px; }
 .footer-logo { font-size: 20px; font-weight: 700; font-style: italic; color: var(--white); }
 .footer-lang { display: flex; align-items: center; gap: 8px; }
 .footer-lang select { background: var(--amazon-dark); color: var(--white); border: 1px solid #595959; padding: 6px 10px; font-size: 12px; cursor: pointer; border-radius: 4px; }
 .footer-bottom { background: var(--amazon-dark); border-top: 1px solid #3a4553; padding: 16px; }
 .footer-bottom-content { max-width: 1400px; margin: 0 auto; text-align: center; }
 .footer-links { display: flex; justify-content: center; gap: 4px; flex-wrap: wrap; margin-bottom: 8px; }
 .footer-links a { color: var(--white); text-decoration: none; font-size: 11px; transition: all 0.2s; }
 .footer-links a:hover { text-decoration: underline; }
 .footer-links span { color: #595959; font-size: 11px; }
 .footer-copyright { font-size: 11px; color: #ccc; }
 .footer-copyright span { display: block; margin-top: 4px; }

 .back-to-top-btn { position: fixed; bottom: 20px; right: 20px; width: 44px; height: 44px; background: var(--amazon-charcoal); color: var(--white); border: none; cursor: pointer; font-size: 16px; display: none; transition: all 0.3s; z-index: 999; border-radius: 50%; }
 .back-to-top-btn:hover { background: #37475a; }
 .back-to-top-btn.visible { display: block; }

 /* Animations */
 @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
 .product-card { animation: fadeIn 0.4s ease forwards; }
 .product-card:nth-child(2) { animation-delay: 0.04s; }
 .product-card:nth-child(3) { animation-delay: 0.08s; }
 .product-card:nth-child(4) { animation-delay: 0.12s; }
 .product-card:nth-child(5) { animation-delay: 0.16s; }

 /* Toast */
 .toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 9999; }
 .toast-msg { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 5px; margin-bottom: 7px; font-size: 13px; font-weight: 500; box-shadow: 0 2px 10px rgba(0,0,0,0.10); min-width: 240px; background: #fff; border-left: 4px solid #27ae60; }
 .toast-msg.error { border-color: #c0392b; }
 .toast-msg.info { border-color: #2980b9; }
 .toast-msg.warning { border-color: #f39c12; }

 /* Sidebar cart (slide-in from right) */
 .cart-sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1999; opacity: 0; visibility: hidden; transition: opacity 0.25s, visibility 0.25s; }
 .cart-sidebar-overlay.open { opacity: 1; visibility: visible; }
 .cart-sidebar { position: fixed; top: 0; right: -440px; width: 420px; max-width: 95vw; height: 100vh; background: #fff; z-index: 2000; box-shadow: -4px 0 20px rgba(0,0,0,0.15); transition: right 0.3s ease; display: flex; flex-direction: column; }
 .cart-sidebar.open { right: 0; }
 .cart-sidebar-header { background: var(--amazon-charcoal); color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
 .cart-sidebar-header h3 { margin: 0; font-size: 16px; font-weight: 700; }
 .cart-sidebar-header h3 span { color: var(--amazon-orange); }
 .cart-sidebar-close { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; line-height: 1; }
 .cart-sidebar-close:hover { color: var(--amazon-orange); }
 .cart-sidebar-body { flex: 1; overflow-y: auto; padding: 12px 16px; }
 .cart-sidebar-empty { text-align: center; padding: 40px 16px; color: var(--medium-gray); }
 .cart-sidebar-empty i { font-size: 60px; color: #cbd5e1; display: block; margin-bottom: 12px; }
 .cart-sidebar-empty h4 { font-size: 15px; color: var(--amazon-charcoal); margin: 0 0 4px; }
 .cart-sidebar-item { display: grid; grid-template-columns: 64px 1fr; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; align-items: start; }
 .cart-sidebar-item-img { width: 64px; height: 64px; border-radius: 4px; background: #f3f3f3; overflow: hidden; display: flex; align-items: center; justify-content: center; }
 .cart-sidebar-item-img img { width: 100%; height: 100%; object-fit: cover; }
 .cart-sidebar-item-info { min-width: 0; }
 .cart-sidebar-item-name { font-size: 13px; font-weight: 600; color: #0F1111; text-decoration: none; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
 .cart-sidebar-item-name:hover { color: #c45500; }
 .cart-sidebar-item-price { font-size: 14px; font-weight: 700; color: #c7511f; margin-top: 4px; }
 .cart-sidebar-item-actions { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
 .cs-qty { display: flex; align-items: stretch; border: 1px solid #d5d9d9; border-radius: 20px; overflow: hidden; height: 26px; }
 .cs-qty button { width: 26px; height: 100%; background: #f0f2f2; border: none; cursor: pointer; font-size: 13px; font-weight: 700; padding: 0; }
 .cs-qty button:hover { background: #e7e7e7; }
 .cs-qty input { width: 32px; text-align: center; border: none; border-left: 1px solid #d5d9d9; border-right: 1px solid #d5d9d9; font-size: 12px; font-weight: 600; -moz-appearance: textfield; }
 .cs-qty input::-webkit-outer-spin-button, .cs-qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
 .cs-remove { background: none; border: none; color: #c7511f; font-size: 12px; cursor: pointer; padding: 0; }
 .cs-remove:hover { text-decoration: underline; }
 .cs-line-total { font-size: 12px; color: var(--medium-gray); margin-left: auto; align-self: center; }
 .cart-sidebar-footer { border-top: 1px solid #e7e7e7; padding: 14px 18px; background: #fafafa; }
 .cart-sidebar-subtotal { display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; color: #0F1111; margin-bottom: 10px; }
 .cart-sidebar-subtotal strong { color: #c7511f; }
 .cart-sidebar-btns { display: grid; gap: 8px; }
 .btn-cs-go-cart, .btn-cs-buy-now { width: 100%; padding: 10px; font-size: 13px; font-weight: 700; border-radius: 100px; cursor: pointer; border: 1px solid; text-align: center; display: block; text-decoration: none; }
 .btn-cs-go-cart { background: #fff; border-color: #d5d9d9; color: #0F1111; }
 .btn-cs-go-cart:hover { background: #f7f7f7; }
 .btn-cs-buy-now { background: #ffa41c; border-color: #ff8f00; color: #0F1111; }
 .btn-cs-buy-now:hover { background: #fa8900; }

 @media (max-width: 1200px) { .products-grid { grid-template-columns: repeat(4, 1fr); } .header-top { display: block; } }
 @media (min-width: 1201px) and (max-width: 1366px) { .products-grid { grid-template-columns: repeat(5, 1fr); gap: 10px; } .product-section { padding: 0 14px; } }
 @media (min-width: 1367px) { .products-grid { grid-template-columns: repeat(5, 1fr); gap: 12px; } }

 /* Category tile responsive */
 @media (max-width: 768px) {
  .cat-tile { width: 72px; }
  .cat-tile-circle { width: 54px; height: 54px; }
  .cat-tile-circle i { font-size: 22px; }
  .cat-tile-name { font-size: 11px; max-width: 72px; }
 }
 @media (max-width: 480px) {
  .cat-tile { width: 64px; gap: 6px; }
  .cat-tile-circle { width: 48px; height: 48px; }
  .cat-tile-circle i { font-size: 19px; }
  .cat-tile-name { font-size: 10px; max-width: 64px; }
 }
 @media (max-width: 992px) {
 .header-main { flex-wrap: nowrap; gap: 8px; padding: 8px 16px; }
 .search-bar { flex: 1; min-width: 0; }
 .products-grid { grid-template-columns: repeat(3, 1fr); }
 .offer-banner { grid-template-columns: repeat(4, 1fr); }
 .footer-grid { grid-template-columns: repeat(2, 1fr); }
 .categories-grid { grid-template-columns: repeat(3, 1fr); }
 .deal-banner { flex-direction: column; gap: 10px; text-align: center; padding: 12px 16px; }
 .banner-content h2 { font-size: 26px; } .banner-content p { font-size: 14px; } .banner-slide { min-height: 240px; }
 }
 @media (max-width: 768px) {
 .header-top { display: none; }
 .header-main { padding: 8px 12px; gap: 6px; flex-wrap: nowrap; }
 .logo { padding: 4px 6px; flex-shrink: 0; }
 .logo-text { font-size: 16px; }
 .search-bar { flex: 1; min-width: 0; height: 34px; }
 .search-bar input { font-size: 12px; padding: 6px 10px; }
 .search-bar button { padding: 0 10px; font-size: 14px; }
 .cart-link { padding: 2px 4px; flex-shrink: 0; }
 .account-link { padding: 2px 4px; flex-shrink: 0; }
 .account-icon { font-size: 20px; }
 .cart-text { display: none; }
 .cart-count { top: 0; left: 14px; }
 .nav-bar { display: none; }
 .mobile-menu-btn { padding: 6px; flex-shrink: 0; }
 .banner { margin-bottom: 12px; }
 .banner-slide { min-height: 180px; }
 .banner-content { padding: 16px 40px; }
 .banner-nav { display: none; }
 .banner-nav.prev { left: 4px; } .banner-nav.next { right: 4px; }
 .banner-content h2 { font-size: 18px; } .banner-content p { font-size: 11px; margin-bottom: 10px; }
 .banner-content .btn { padding: 6px 16px; font-size: 11px; }
 .banner-dots { bottom: 8px; }
 .product-section { padding: 0 8px; margin-bottom: 12px; }
 .section-header { padding: 12px 0; }
 .section-header h3 { font-size: 15px; }
 .section-header .view-all { font-size: 11px; }
 .products-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; padding: 12px 0; }
 .product-card { padding: 10px 6px; }
 .product-image { height: 120px; }
 .product-info { padding-top: 4px; }
 .product-brand { font-size: 10px; }
 .product-name { font-size: 11px; height: 30px; margin-bottom: 4px; }
 .current-price { font-size: 13px; } .original-price { font-size: 10px; } .discount { font-size: 10px; }
 .rating-badge { font-size: 10px; padding: 1px 4px; } .rating-count { font-size: 10px; }
 .prime-badge { font-size: 9px; } .prime-badge i { font-size: 10px; }
 .quick-badge { font-size: 8px; padding: 1px 4px; top: 4px; left: 4px; }
 .wishlist-btn { width: 22px; height: 22px; top: 4px; right: 4px; }
 .wishlist-btn i { font-size: 9px; }
 .offer-banner { grid-template-columns: repeat(4, 1fr); gap: 6px; padding: 0 8px; margin: 12px auto; }
 .offer-card { padding: 12px 6px; }
 .offer-card i { font-size: 20px; margin-bottom: 6px; } .offer-card h4 { font-size: 10px; } .offer-card p { font-size: 9px; }
 .categories-grid { grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 0 8px; margin: 12px auto; }
 .category-card { padding: 12px 8px; } .category-card i { font-size: 22px; margin-bottom: 6px; } .category-card h4 { font-size: 10px; } .category-card p { font-size: 9px; }
 .deal-banner { margin: 12px 8px 0; padding: 10px 14px; flex-direction: column; gap: 8px; text-align: center; }
 .deal-banner h3 { font-size: 13px; }
 .deal-banner .timer { gap: 3px; } .deal-banner .timer span { font-size: 12px; padding: 2px 6px; }
 .deal-banner .timer span:last-child { display: none; }
 .footer-grid { grid-template-columns: 1fr; text-align: center; gap: 20px; }
 .footer-divider { flex-direction: column; gap: 8px; }
 }
 @media (max-width: 480px) {
 .header-main { padding: 6px 10px; gap: 4px; flex-wrap: nowrap; }
 .logo-text { font-size: 14px; } .search-bar { height: 32px; min-width: 0; }
 .search-bar input { font-size: 11px; padding: 5px 8px; }
 .search-bar button { padding: 0 8px; font-size: 12px; }
 .mobile-menu-btn { font-size: 18px; padding: 4px; }
 .banner-slide { min-height: 150px; } .banner-content { padding: 12px 36px; }
 .banner-content h2 { font-size: 16px; } .banner-content p { font-size: 10px; }
 .banner-content .btn { padding: 5px 14px; font-size: 10px; }
 .banner-nav { display: none; }
 .banner-nav.prev { left: 2px; } .banner-nav.next { right: 2px; }
 .products-grid { padding: 10px 6px; }
 .product-image { height: 100px; }
 .product-name { font-size: 10px; height: 26px; }
 .current-price { font-size: 12px; }
 .offer-banner { grid-template-columns: repeat(2, 1fr); gap: 6px; }
 .offer-card i { font-size: 18px; } .offer-card h4 { font-size: 9px; }
 .categories-grid { grid-template-columns: repeat(2, 1fr); }
 .category-card { padding: 10px 6px; } .category-card i { font-size: 20px; } .category-card h4 { font-size: 9px; }
 .deal-banner h3 { font-size: 12px; } .deal-banner .timer span { font-size: 10px; padding: 2px 5px; }
 .back-to-top-btn { bottom: 12px; right: 12px; width: 36px; height: 36px; font-size: 14px; }
 }

 /* Mobile menu drawer styles */
 .mobile-menu-overlay {
     position: fixed;
     top: 0;
     left: 0;
     right: 0;
     bottom: 0;
     background: rgba(0, 0, 0, 0.55);
     z-index: 2999;
     opacity: 0;
     visibility: hidden;
     transition: opacity 0.3s ease, visibility 0.3s ease;
 }
 .mobile-menu-overlay.active {
     opacity: 1;
     visibility: visible;
 }
 .mobile-menu {
     position: fixed;
     top: 0;
     left: -320px;
     width: 80%;
     max-width: 320px;
     height: 100vh;
     background: #fff;
     z-index: 3000;
     transition: left 0.3s ease-out;
     box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
     overflow-y: auto;
 }
 .mobile-menu.active {
     left: 0 !important;
 }
 </style>
 @stack('styles')
</head>
<body>

@if(!empty($gtmContainerId))
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmContainerId }}"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
@endif

@include('shop.partials.shop-header')

@yield('content')

@include('shop.partials.shop-footer')

<button class="back-to-top-btn" id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
 <i class="fas fa-chevron-up"></i>
</button>

<div class="toast-wrap" id="toast-wrap"></div>

<script>
 // ── Banner slider with auto-scroll & pause-on-hover ─
 let currentSlide = 0;
 const slidesEl = document.getElementById('banner-slider');
 const dotsEl = document.getElementById('banner-dots');
 const totalSlides = slidesEl ? slidesEl.children.length : 0;
 let sliderTimer = null;

 function buildDots() {
 if (!dotsEl) return;
 dotsEl.innerHTML = '';
 for (let i = 0; i < totalSlides; i++) {
 const b = document.createElement('button');
 b.className = 'banner-dot' + (i === 0 ? ' active' : '');
 b.onclick = () => goToSlide(i);
 dotsEl.appendChild(b);
 }
 }
 function goToSlide(i) {
 if (!slidesEl) return;
 currentSlide = (i + totalSlides) % totalSlides;
 slidesEl.style.transform = `translateX(-${currentSlide * 100}%)`;
 if (dotsEl) {
 [...dotsEl.children].forEach((d, idx) => d.classList.toggle('active', idx === currentSlide));
 }
 }
 function moveSlide(dir) { goToSlide(currentSlide + dir); }

 function startSlider() {
 stopSlider();
 if (totalSlides > 1) {
 sliderTimer = setInterval(() => moveSlide(1), 5000);
 }
 }
 function stopSlider() {
 if (sliderTimer) { clearInterval(sliderTimer); sliderTimer = null; }
 }

 if (totalSlides > 0) {
 buildDots();
 startSlider();

 const bannerWrap = slidesEl.closest('.banner');
 if (bannerWrap) {
 bannerWrap.addEventListener('mouseenter', stopSlider);
 bannerWrap.addEventListener('mouseleave', startSlider);

 let startX = 0;
 bannerWrap.addEventListener('touchstart', e => { startX = e.touches[0].clientX; stopSlider(); }, { passive: true });
 bannerWrap.addEventListener('touchend', e => {
 let diff = startX - e.changedTouches[0].clientX;
 if (Math.abs(diff) > 40) moveSlide(diff > 0 ? 1 : -1);
 startSlider();
 }, { passive: true });
 }
 }

 // ── Mobile menu ─────────────────────────────────────
 function toggleMobileMenu() {
 document.querySelector('.mobile-menu')?.classList.toggle('active');
 document.querySelector('.mobile-menu-overlay')?.classList.toggle('active');
 }

 // ── Back to top button ─────────────────────────────
 const btt = document.getElementById('backToTop');
 window.addEventListener('scroll', () => {
 if (btt) btt.classList.toggle('visible', window.scrollY > 300);
 });

 // ── Toasts ─────────────────────────────────────────
 window.showToast = function(msg, type = 'success') {
 const wrap = document.getElementById('toast-wrap');
 if (!wrap) return;
 const div = document.createElement('div');
 div.className = 'toast-msg ' + (type === 'error' ? 'error' : type === 'info' ? 'info' : type === 'warning' ? 'warning' : '');
 div.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${msg}`;
 wrap.appendChild(div);
 setTimeout(() => { div.style.opacity = '0'; setTimeout(() => div.remove(), 300); }, 2500);
 };

 // ── Customer Auth check ─────────────────────────────
 // Redirects to login if not authenticated, returns true if logged in.
 // Pass a returnUrl so user comes back after login.
 window.ensureCustomerLogin = function(returnUrl = null) {
 const returnTo = returnUrl || window.location.href;
 window.location.href = '/login?redirect=' + encodeURIComponent(returnTo);
 return false;
 };

 // Check auth status via API call
 window.checkCustomerAuth = async function() {
 try {
 const res = await fetch('/api/customer/auth-check', {
 headers: {
 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
 'Accept': 'application/json',
 }
 });
 const data = await res.json();
 return data.authenticated || false;
 } catch(e) {
 return false;
 }
 };

 // ── Cart (localStorage) ────────────────────────────
 const CART_KEY = 'store_cart';
 window.getCart = function() {
 try { 
   let raw = localStorage.getItem(CART_KEY);
   if (!raw) {
     raw = localStorage.getItem('nellai_cart');
     if (raw) localStorage.setItem(CART_KEY, raw);
   }
   return JSON.parse(raw || '[]'); 
 }
 catch(e) { return []; }
 };
 window.saveCart = function(cart) {
 localStorage.setItem(CART_KEY, JSON.stringify(cart));
 localStorage.setItem('nellai_cart', JSON.stringify(cart));
 updateCartCount();
 };
 window.updateCartCount = function() {
 const cart = getCart();
 const count = cart.reduce((sum, item) => sum + (item.qty || 0), 0);
 const el = document.getElementById('cart-count');
 if (el) {
 el.textContent = count;
 el.style.display = count > 0 ? 'block' : 'none';
 }
 };
 window.addToCartDetailed = function(p) {
 const cart = getCart();
 const optKey = (p.options && p.options.length) ? p.options.map(o => o.id).sort().join('-') : '';
 const itemKey = p.id + '_' + (p.variation_id || '0') + '_' + (p.color || '') + '_' + optKey;

 const existing = cart.find(c => (c.itemKey === itemKey) || (!c.itemKey && !p.variation_id && c.id === p.id));
 if (existing) {
 existing.qty = (existing.qty || 0) + Math.max(1, parseInt(p.qty) || 1);
 existing.price = p.price || existing.price;
 existing.variation_id = p.variation_id || existing.variation_id;
 existing.variation_name = p.variation_name || existing.variation_name;
 existing.color = p.color || existing.color;
 existing.options = p.options || existing.options;
 } else {
 cart.push({
 itemKey: itemKey,
 id: p.id,
 qty: Math.max(1, parseInt(p.qty) || 1),
 name: p.name,
 price: p.price,
 image: p.image,
 slug: p.slug,
 variation_id: p.variation_id || null,
 variation_name: p.variation_name || null,
 color: p.color || null,
 options: p.options || []
 });
 }
 saveCart(cart);
 showToast('Added to cart!');
 };

 window.addToCart = function(productId, qty = 1, name = '', price = 0, image = '', slug = '', variation_id = null, variation_name = null, color = null, options = []) {
 window.addToCartDetailed({
 id: productId,
 qty: qty,
 name: name,
 price: price,
 image: image,
 slug: slug,
 variation_id: variation_id,
 variation_name: variation_name,
 color: color,
 options: options
 });
 };

 // Buy Now: checks auth, then adds to cart and navigates to buy-now.
 // If not logged in, redirects to login page with return URL.
 window.buyNow = async function(productId, qty = 1, name = '', price = 0, image = '', slug = '') {
 try {
 const res = await fetch('/api/customer/auth-check', {
 headers: {
 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
 'Accept': 'application/json',
 }
 });
 const data = await res.json();

 if (!data.authenticated) {
 const returnTo = window.location.href;
 window.location.href = '/login?redirect=' + encodeURIComponent('/buy-now');
 return;
 }

 // Logged in — add to cart and go to checkout
 addToCart(productId, qty, name, price, image, slug);
 window.location.href = '/buy-now';
 } catch(e) {
 // On error, still allow (auth check failed but don't block user)
 addToCart(productId, qty, name, price, image, slug);
 window.location.href = '/buy-now';
 }
 };
 window.addToCartIncrement = function(productId, qty = 1, name = '', price = 0, image = '', slug = '') {
 // Adds to existing cart quantity (used by quick-add / "Add 1 more")
 const cart = getCart();
 const existing = cart.find(c => c.id === productId);
 if (existing) {
 existing.qty = (existing.qty || 1) + Math.max(1, parseInt(qty) || 1);
 } else {
 cart.push({ id: productId, qty: Math.max(1, parseInt(qty) || 1), name, price, image, slug });
 }
 saveCart(cart);
 showToast('Added to cart!');
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

 // ── Wishlist (localStorage) ────────────────────────
 const WISH_KEY = 'store_wishlist';
 window.getWishlist = function() {
 try { 
   let raw = localStorage.getItem(WISH_KEY);
   if (!raw) {
     raw = localStorage.getItem('nellai_wishlist');
     if (raw) localStorage.setItem(WISH_KEY, raw);
   }
   return JSON.parse(raw || '[]'); 
 }
 catch(e) { return []; }
 };
 window.toggleWishlist = function(productId, btn) {
 const list = getWishlist();
 const idx = list.indexOf(productId);
 if (idx >= 0) {
 list.splice(idx, 1);
 if (btn) { btn.classList.remove('active'); btn.querySelector('i')?.classList.replace('fas','far'); }
 showToast('Removed from wishlist', 'info');
 } else {
 list.push(productId);
 if (btn) { btn.classList.add('active'); btn.querySelector('i')?.classList.replace('far','fas'); }
 showToast('Added to wishlist!');
 }
 localStorage.setItem(WISH_KEY, JSON.stringify(list));
 localStorage.setItem('nellai_wishlist', JSON.stringify(list));
 };

 // Init: apply active wishlist state
 document.addEventListener('DOMContentLoaded', () => {
 updateCartCount();
 const wishlist = getWishlist();
 document.querySelectorAll('.wishlist-btn').forEach(btn => {
 const id = parseInt(btn.dataset.productId);
 if (id && wishlist.includes(id)) {
 btn.classList.add('active');
 btn.querySelector('i')?.classList.replace('far','fas');
 }
 });
 });

 // ── PDP qty selector sync ──────────────────────────
 window.updateQtyBtns = function() {
 const v = document.getElementById('pdp-qty');
 const d = document.getElementById('pdp-qty-display');
 if (v && d) d.textContent = v.value;
 };

 // ── Sidebar cart (slide-in from right) ────────────
 window.openCartSidebar = function() {
 document.getElementById('cart-sidebar').classList.add('open');
 document.getElementById('cart-sidebar-overlay').classList.add('open');
 document.body.style.overflow = 'hidden';
 sbLoad();
 };
 window.closeCartSidebar = function() {
 document.getElementById('cart-sidebar').classList.remove('open');
 document.getElementById('cart-sidebar-overlay').classList.remove('open');
 document.body.style.overflow = '';
 };

 let sbItems = [];
 async function sbLoad() {
 const cart = getCart();
 const list = document.getElementById('cs-items-list');
 const empty = document.getElementById('cs-empty');
 const subtotalEl = document.getElementById('cs-subtotal');
 const countEl = document.getElementById('cs-items-count');
 const footer = document.getElementById('cs-footer');
 if (!cart.length) {
 sbItems = [];
 list.innerHTML = '';
 empty.style.display = 'block';
 subtotalEl.textContent = '0';
 countEl.textContent = '0';
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
 } catch (e) { console.error(e); }
 }

 function sbRender() {
 const list = document.getElementById('cs-items-list');
 const empty = document.getElementById('cs-empty');
 const subtotalEl = document.getElementById('cs-subtotal');
 const countEl = document.getElementById('cs-items-count');
 const footer = document.getElementById('cs-footer');
 if (!sbItems.length) {
 empty.style.display = 'block';
 list.innerHTML = '';
 subtotalEl.textContent = '0';
 countEl.textContent = '0';
 if (footer) footer.style.display = 'none';
 return;
 }
 empty.style.display = 'none';
 if (footer) footer.style.display = 'block';
 list.innerHTML = sbItems.map(item => `
 <div class="cart-sidebar-item" data-id="${item.id}">
 <a href="/product/${item.slug || item.id}" class="cart-sidebar-item-img">
 ${item.image ? `<img src="${item.image}" alt="">` : '<i class="fas fa-image" style="color:#cbd5e1;"></i>'}
 </a>
 <div>
 <a href="/product/${item.slug || item.id}" class="cart-sidebar-item-name">${item.name || 'Product #' + item.id}</a>
 <div class="cart-sidebar-item-price">₹${Number(item.price || 0).toLocaleString('en-IN')}</div>
 <div class="cart-sidebar-item-actions">
 <div class="cs-qty">
 <button onclick="sbChangeQty(${item.id}, ${item.qty - 1})">−</button>
 <input type="number" value="${item.qty}" min="1" onchange="sbChangeQty(${item.id}, parseInt(this.value))">
 <button onclick="sbChangeQty(${item.id}, ${item.qty + 1})">+</button>
 </div>
 <button class="cs-remove" onclick="sbRemove(${item.id})"><i class="fas fa-trash"></i></button>
 </div>
 </div>
 </div>
 `).join('');
 const subtotal = sbItems.reduce((s, i) => s + Number(i.price || 0) * i.qty, 0);
 const count = sbItems.reduce((s, i) => s + i.qty, 0);
 subtotalEl.textContent = subtotal.toLocaleString('en-IN');
 countEl.textContent = count;
 }

 window.sbChangeQty = function(id, qty) {
 if (qty < 1) { sbRemove(id); return; }
 updateCartQty(id, qty);
 updateCartCount();
 const local = sbItems.find(i => i.id === id);
 if (local) local.qty = qty;
 sbRender();
 };
 window.sbRemove = function(id) {
 removeFromCart(id);
 updateCartCount();
 sbItems = sbItems.filter(i => i.id !== id);
 sbRender();
 if (typeof renderCart === 'function') renderCart();
 };
</script>

<!-- Slide-in cart sidebar (right side) -->
<div class="cart-sidebar-overlay" id="cart-sidebar-overlay" onclick="closeCartSidebar()"></div>
<aside class="cart-sidebar" id="cart-sidebar" aria-label="Shopping cart">
 <div class="cart-sidebar-header">
 <h3>Your Cart <span id="cs-items-count" style="font-size:13px; font-weight:400;">(0)</span></h3>
 <button class="cart-sidebar-close" onclick="closeCartSidebar()" aria-label="Close">&times;</button>
 </div>
 <div class="cart-sidebar-body">
 <div id="cs-empty" class="cart-sidebar-empty" style="display:none;">
 <i class="fas fa-shopping-cart"></i>
 <h4>Your cart is empty</h4>
 <p>Add items to get started</p>
 <a href="{{ url('/') }}" onclick="closeCartSidebar()" style="display:inline-block; margin-top:14px; padding: 8px 20px; background: var(--amazon-orange); color: #fff; border-radius: 100px; font-weight: 600; text-decoration: none;">Continue Shopping</a>
 </div>
 <div id="cs-items-list"></div>
 </div>
 <div class="cart-sidebar-footer" id="cs-footer" style="display:none;">
 <div class="cart-sidebar-subtotal">
 <span>Subtotal:</span>
 <strong>₹<span id="cs-subtotal">0</span></strong>
 </div>
 <div class="cart-sidebar-btns">
 <a href="{{ url('/cart') }}" onclick="closeCartSidebar()" class="btn-cs-go-cart"><i class="fas fa-shopping-bag"></i> Go to Cart</a>
 <a href="{{ url('/buy-now') }}" onclick="closeCartSidebar()" class="btn-cs-buy-now"><i class="fas fa-bolt"></i> Buy Now</a>
 </div>
 </div>
</aside>
@stack('scripts')
</body>
</html>
