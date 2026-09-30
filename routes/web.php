<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\Admin\BonusController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Shop\AddressController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\LoginController;
use App\Http\Controllers\Shop\OrdersController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ReviewController as ShopReviewController;
use App\Http\Controllers\Shop\ShopController;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\StoreSetting;

// ================================================================
// PUBLIC FRONTEND (Amazon-style shop)
// ================================================================
Route::get('/', [ShopController::class, 'home'])->name('shop.home');
Route::get('/products', [ShopController::class, 'home'])->name('shop.products');
Route::get('/shop', [ShopController::class, 'shop'])->name('shop.shop');
Route::get('/wishlist', [ShopController::class, 'wishlist'])->name('shop.wishlist');
Route::get('/category/{id}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('shop.product');
Route::get('/search', [ShopController::class, 'search'])->name('shop.search');
Route::get('/track-order', [OrdersController::class, 'trackOrder'])->name('shop.track-order');

// Cart (localStorage-backed; server only returns product details for IDs)
Route::get('/cart', [CartController::class, 'show'])->name('shop.cart');
Route::post('/api/cart/items', [CartController::class, 'items'])->name('api.cart.items');

// Wishlist APIs
Route::post('/api/wishlist/toggle', [LoginController::class, 'toggleWishlist'])->name('api.wishlist.toggle');
Route::get('/api/wishlist/items', [LoginController::class, 'getWishlistItems'])->name('api.wishlist.items');

// Customer Reviews submission
Route::post('/product/{product}/reviews', [ShopReviewController::class, 'store'])->name('shop.reviews.store');

// Customer registration & login
Route::get('/register', [LoginController::class, 'showRegister'])->name('shop.register');
Route::post('/register', [LoginController::class, 'register'])->middleware('throttle:10,1')->name('shop.register.post');
Route::post('/login/password', [LoginController::class, 'loginWithPassword'])->middleware('throttle:10,1')->name('shop.login.password');

// Customer login — email + OTP (two-step)
Route::prefix('login')->name('shop.login.')->group(function () {
    Route::get('/',  [LoginController::class, 'showEmail'])->name('email');
    Route::post('/', [LoginController::class, 'sendOtp'])->middleware('throttle:6,1')->name('send');
    Route::get('/otp',  [LoginController::class, 'showOtp'])->name('otp');
    Route::post('/otp', [LoginController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('verify');
    Route::get('/resend', [LoginController::class, 'resendOtp'])->middleware('throttle:3,1')->name('resend');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('shop.logout');

// Customer Password Reset
Route::get('/forgot-password', [LoginController::class, 'showForgotPassword'])->name('shop.password.forgot');
Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('shop.password.email');
Route::get('/reset-password/{token}', [LoginController::class, 'showResetPassword'])->name('shop.password.reset.form');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->middleware('throttle:5,1')->name('shop.password.reset.post');

// API: check if customer is authenticated (used by JS)
Route::get('/api/customer/auth-check', function () {
    return response()->json([
        'authenticated' => \Illuminate\Support\Facades\Auth::guard('customer')->check(),
    ]);
});

// Buy Now / Checkout — requires customer login
Route::middleware('customer')->group(function () {
    Route::get('/buy-now', [CartController::class, 'buyNow'])->name('shop.buy-now');
    Route::post('/buy-now', [CartController::class, 'placeOrder'])->name('shop.place-order');
    Route::get('/account', [LoginController::class, 'showAccount'])->name('shop.account');
    Route::post('/account/profile', [LoginController::class, 'updateProfile'])->name('shop.account.profile');
    Route::post('/account/password', [LoginController::class, 'updatePassword'])->name('shop.account.password');

 Route::post  ('/account/addresses',                  [AddressController::class, 'store'])     ->name('shop.addresses.store');
 Route::put   ('/account/addresses/{address}',        [AddressController::class, 'update'])    ->name('shop.addresses.update');
 Route::delete('/account/addresses/{address}',        [AddressController::class, 'destroy'])   ->name('shop.addresses.destroy');
 Route::post  ('/account/addresses/{address}/default',[AddressController::class, 'setDefault'])->name('shop.addresses.default');

 // Payment — token is the temp pending order id (TMP...)
 Route::get ('/pay/{token}',          [PaymentController::class, 'show'])    ->name('shop.payment.show');
 Route::post('/pay/{token}/initiate', [PaymentController::class, 'initiate'])->name('shop.payment.initiate');
 Route::get ('/pay/{token}/status',   [PaymentController::class, 'status'])  ->name('shop.payment.status');
 Route::get ('/pay/{token}/return',   [PaymentController::class, 'return'])  ->name('shop.payment.return');

 // Order success page (looked up by friendly order_code, e.g. NS0001)
 Route::get('/order/{order}', [PaymentController::class, 'success'])->name('shop.order.success');

 // My Orders list + detail
 Route::get('/account/orders', [OrdersController::class, 'index'])->name('shop.orders.index');
 Route::get('/account/orders/{order}', [OrdersController::class, 'show'])->name('shop.orders.show');
 Route::post('/account/orders/{order}/cancel', [OrdersController::class, 'cancel'])->name('shop.orders.cancel');
 Route::post('/account/orders/{order}/return', [OrdersController::class, 'requestReturn'])->name('shop.orders.return');
 Route::get('/account/orders/{order}/invoice', [OrdersController::class, 'invoice'])->name('shop.orders.invoice');

 // Help & Support
 Route::get('/help',                                          [\App\Http\Controllers\Shop\SupportController::class, 'help'])->name('shop.help');
 Route::get('/account/support',                               [\App\Http\Controllers\Shop\SupportController::class, 'index'])->name('shop.support.index');
 Route::post('/account/support',                              [\App\Http\Controllers\Shop\SupportController::class, 'store'])->name('shop.support.store');
 Route::get('/account/support/{ticket}',                      [\App\Http\Controllers\Shop\SupportController::class, 'show'])->name('shop.support.show');
 Route::post('/account/support/{ticket}/reply',               [\App\Http\Controllers\Shop\SupportController::class, 'reply'])->name('shop.support.reply');
 Route::post('/account/support/{ticket}/close',               [\App\Http\Controllers\Shop\SupportController::class, 'close'])->name('shop.support.close');

 // My Coupons (JSON)
 Route::get('/api/coupons/mine', [\App\Http\Controllers\Shop\CouponController::class, 'myCoupons'])->name('api.coupons.mine');
});

// UPI webhook — public POST endpoint, CSRF-exempt (configured in bootstrap/app.php)
Route::post('/api/upi/webhook', [PaymentController::class, 'webhook'])->name('shop.payment.webhook');
Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

// ================================================================
// PUBLIC SEO/UTILITY ROUTES
// ================================================================
Route::get('/robots.txt', function () {
 $robotsTxt = \App\Models\StoreSetting::getValue('robots_txt', '');
 if ($robotsTxt) {
 return response($robotsTxt, 200)->header('Content-Type', 'text/plain');
 }
 return response("User-agent: *\nDisallow:", 200)->header('Content-Type', 'text/plain');
});

Route::get('/sitemap.xml', function () {
 $baseUrl = config('app.url', url('/'));
 $canonicalUrl = StoreSetting::getValue('canonical_url', $baseUrl);

    $categories = Category::active()->get(['id', 'slug', 'updated_at']);
    $products = Product::active()->get(['id', 'slug', 'updated_at']);

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>' . $baseUrl . '/</loc>' . "\n";
    $xml .= '        <changefreq>daily</changefreq>' . "\n";
    $xml .= '        <priority>1.0</priority>' . "\n";
    $xml .= '    </url>' . "\n";

    foreach ($categories as $cat) {
        $catIdentifier = $cat->slug ?: $cat->id;
        $xml .= '    <url>' . "\n";
        $xml .= '        <loc>' . $baseUrl . '/category/' . $catIdentifier . '</loc>' . "\n";
        $xml .= '        <lastmod>' . ($cat->updated_at ? $cat->updated_at->toDateString() : date('Y-m-d')) . '</lastmod>' . "\n";
        $xml .= '        <changefreq>weekly</changefreq>' . "\n";
        $xml .= '        <priority>0.8</priority>' . "\n";
        $xml .= '    </url>' . "\n";
    }

    foreach ($products as $prod) {
        $slug = $prod->slug ?: ('product-' . $prod->id);
        $xml .= '    <url>' . "\n";
        $xml .= '        <loc>' . $baseUrl . '/product/' . $slug . '</loc>' . "\n";
        $xml .= '        <lastmod>' . ($prod->updated_at ? $prod->updated_at->toDateString() : date('Y-m-d')) . '</lastmod>' . "\n";
        $xml .= '        <changefreq>weekly</changefreq>' . "\n";
        $xml .= '        <priority>0.6</priority>' . "\n";
        $xml .= '    </url>' . "\n";
    }

 $xml .= '</urlset>';

 return response($xml, 200)->header('Content-Type', 'application/xml');
});

Route::get('/api/payment-methods', function () {
 $methods = \App\Models\PaymentGateway::where('is_available', 1)
 ->where('is_active', 1)
 ->orderBy('id')
 ->get(['id', 'slug', 'name', 'method_name', 'description', 'icon']);

 return response()->json([
 'success' => true,
 'data' => $methods->map(function ($m) {
 return [
 'id' => $m->id,
 'slug' => $m->slug,
 'name' => $m->name,
 'method_name' => $m->method_name ?: $m->name,
 'description' => $m->description,
 'icon' => $m->icon,
 ];
 })
 ]);
});

// ================================================================
// ADMIN PANEL
// ================================================================
Route::prefix('admin')->name('admin.')->group(function () {

 Route::get('/', fn () =>
 \Illuminate\Support\Facades\Auth::check()
 ? redirect()->route('admin.dashboard')
 : redirect()->route('admin.login')
 );

 Route::middleware('guest')->group(function () {
 Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
 Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');
 Route::post('/setup', [AdminAuthController::class, 'setup'])->middleware('throttle:5,1')->name('setup');
 Route::get('/setup', [AdminAuthController::class, 'showSetupForm'])->name('setup.form');
 });

 Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

 Route::middleware('admin')->group(function () {
 Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

  Route::get('/bonuses', [BonusController::class, 'index'])->name('bonuses.index');
  Route::post('/bonuses', [BonusController::class, 'store'])->name('bonuses.store');
  Route::put('/bonuses/{bonus}', [BonusController::class, 'update'])->name('bonuses.update');
  Route::delete('/bonuses/{bonus}', [BonusController::class, 'destroy'])->name('bonuses.destroy');

  Route::get('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'index'])->name('banners.index');
  Route::post('/banners', [\App\Http\Controllers\Admin\BannerController::class, 'store'])->name('banners.store');
  Route::post('/banners/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'update'])->name('banners.update');
  Route::delete('/banners/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'destroy'])->name('banners.destroy');

 Route::get('/coupons', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('coupons.index');

 Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
 Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
 Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
 Route::post('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
 Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggleActive'])->name('categories.toggle');
 Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

 Route::get('/settings', [AdminDashboardController::class, 'settingsStore'])->name('settings');
 Route::get('/settings/store', [AdminDashboardController::class, 'settingsStore'])->name('settings.store');
 Route::get('/settings/seo', [AdminDashboardController::class, 'settingsSeo'])->name('settings.seo');
 Route::get('/settings/store-data', [AdminDashboardController::class, 'settingsStoreData'])->name('settings.store-data');
 Route::post('/settings/store-save', [AdminDashboardController::class, 'settingsStoreSave'])->name('settings.store-save');
 Route::get('/settings/seo-data', [AdminDashboardController::class, 'settingsSeoData'])->name('settings.seo-data');
 Route::post('/settings/seo-save', [AdminDashboardController::class, 'settingsSeoSave'])->name('settings.seo-save');
 Route::post('/settings/seo/generate-sitemap', [AdminDashboardController::class, 'generateSitemap'])->name('settings.generate-sitemap');
 Route::post('/settings/seo/generate-robots', [AdminDashboardController::class, 'generateRobots'])->name('settings.generate-robots');
 Route::get('/settings/social', [SocialLinkController::class, 'index'])->name('settings.social');
 Route::get('/settings/social-data', [SocialLinkController::class, 'getData'])->name('settings.social-data');
 Route::post('/settings/social-save', [SocialLinkController::class, 'save'])->name('settings.social-save');
 Route::get('/settings/plugin', [AdminDashboardController::class, 'settingsPlugin'])->name('settings.plugin');
 Route::get('/settings/plugin-data', [AdminDashboardController::class, 'settingsPluginData'])->name('settings.plugin-data');
 Route::post('/settings/plugin-save', [AdminDashboardController::class, 'settingsPluginSave'])->name('settings.plugin-save');
 Route::get('/settings/payment', [AdminDashboardController::class, 'settingsPayment'])->name('settings.payment');
 Route::get('/settings/payment-data', [AdminDashboardController::class, 'settingsPaymentData'])->name('settings.payment-data');
 Route::post('/settings/payment-toggle', [AdminDashboardController::class, 'settingsPaymentToggle'])->name('settings.payment-toggle');
 Route::post('/settings/payment-save', [AdminDashboardController::class, 'settingsPaymentSave'])->name('settings.payment-save');
 Route::get('/settings/smtp', [AdminDashboardController::class, 'settingsSmtp'])->name('settings.smtp');
 Route::get('/settings/smtp-data', [AdminDashboardController::class, 'settingsSmtpData'])->name('settings.smtp-data');
 Route::post('/settings/smtp-save', [AdminDashboardController::class, 'settingsSmtpSave'])->name('settings.smtp-save');
 Route::post('/settings/smtp-test', [AdminDashboardController::class, 'settingsSmtpTest'])->name('settings.smtp-test');
 Route::get('/settings/tax', [AdminDashboardController::class, 'settingsTax'])->name('settings.tax');
 Route::get('/settings/tax-data', [AdminDashboardController::class, 'settingsTaxData'])->name('settings.tax-data');
 Route::post('/settings/tax-gst-save', [AdminDashboardController::class, 'settingsTaxGstSave'])->name('settings.tax-gst-save');
 Route::post('/settings/tax-charge-save', [AdminDashboardController::class, 'settingsTaxChargeSave'])->name('settings.tax-charge-save');
 Route::post('/settings/tax-charge-delete', [AdminDashboardController::class, 'settingsTaxChargeDelete'])->name('settings.tax-charge-delete');

 Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
 Route::post('/templates/set-default', [TemplateController::class, 'setDefault'])->name('templates.set-default');
 Route::get('/templates/{id}', [TemplateController::class, 'show'])->name('templates.show');
 Route::post('/templates/save', [TemplateController::class, 'save'])->name('templates.save');
 Route::post('/templates/delete', [TemplateController::class, 'destroy'])->name('templates.delete');
 Route::post('/templates/upload-image', [TemplateController::class, 'uploadImage'])->name('templates.upload-image');
 Route::post('/templates/export-pdf', [TemplateController::class, 'exportPdf'])->name('templates.export-pdf');
 Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
 Route::match(['get', 'post'], '/invoices/from-order/{order_code?}', [InvoiceController::class, 'fromOrder'])->name('invoices.from-order');
 Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
 Route::post('/invoices/save', [InvoiceController::class, 'save'])->name('invoices.save');
 Route::post('/invoices/delete', [InvoiceController::class, 'destroy'])->name('invoices.delete');
 Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');

 // Payments & Transactions
 Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
 Route::get('/payments/export', [\App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('payments.export');

 // Users
 Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');

 // Product Reviews Moderation
 Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
 Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
 Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
 Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
 Route::post('/reviews/toggle-auto-approve', [AdminReviewController::class, 'toggleAutoApprove'])->name('reviews.toggle-auto-approve');

 // Support
 Route::get  ('/support',                      [\App\Http\Controllers\Admin\SupportController::class, 'index'])        ->name('support.index');
 Route::get  ('/support/{ticket}',             [\App\Http\Controllers\Admin\SupportController::class, 'show'])         ->name('support.show');
 Route::post ('/support/{ticket}/reply',      [\App\Http\Controllers\Admin\SupportController::class, 'reply'])        ->name('support.reply');
 Route::post ('/support/{ticket}/status',     [\App\Http\Controllers\Admin\SupportController::class, 'updateStatus']) ->name('support.status');
 Route::get  ('/tickets',                      fn () => redirect()->route('admin.support.index'))                       ->name('tickets.index');

 Route::get('/products', [ProductController::class, 'index'])->name('products.index');
 Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
 Route::post('/products', [ProductController::class, 'store'])->name('products.store');
 Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
 Route::post('/products/upload-image', [ProductController::class, 'uploadImage'])->name('products.upload-image');
 Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
 Route::match(['put', 'patch', 'post'], '/products/{product}', [ProductController::class, 'update'])->name('products.update');
 Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

 Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
 Route::post('/providers', [ProviderController::class, 'store'])->name('providers.store');
 Route::put('/providers/{provider}', [ProviderController::class, 'update'])->name('providers.update');
 Route::delete('/providers/{provider}', [ProviderController::class, 'destroy'])->name('providers.destroy');
 Route::get('/providers/search-products', [ProviderController::class, 'searchProducts'])->name('providers.search-products');

 // Orders
 Route::get  ('/orders',                                 [\App\Http\Controllers\Admin\OrderController::class, 'index'])         ->name('orders.index');
 Route::get  ('/orders/{order}',                         [\App\Http\Controllers\Admin\OrderController::class, 'show'])          ->name('orders.show');
 Route::post ('/orders/{order}/status',                 [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])   ->name('orders.status');
 Route::post ('/orders/{order}/custom-status',          [\App\Http\Controllers\Admin\OrderController::class, 'addCustomStatus']) ->name('orders.custom-status');
 Route::post ('/orders/{order}/custom-status/remove',   [\App\Http\Controllers\Admin\OrderController::class, 'removeCustomStatus'])->name('orders.custom-status.remove');
 Route::post ('/orders/{order}/dispatch',               [\App\Http\Controllers\Admin\OrderController::class, 'dispatchOrder'])   ->name('orders.dispatch');
 Route::post ('/orders/{order}/mark-paid',              [\App\Http\Controllers\Admin\OrderController::class, 'markPaid'])        ->name('orders.mark-paid');
 Route::post ('/orders/{order}/cancel',                 [\App\Http\Controllers\Admin\OrderController::class, 'cancel'])          ->name('orders.cancel');
 Route::post ('/orders/{order}/refund',                 [\App\Http\Controllers\Admin\OrderController::class, 'refund'])          ->name('orders.refund');
 Route::get  ('/orders/{order}/invoice',                [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])         ->name('orders.invoice');

 // Cancellations & Returns
 Route::get   ('/cancellations-returns',                              [\App\Http\Controllers\Admin\CancellationReturnController::class, 'index'])         ->name('cancellations-returns.index');
 Route::post  ('/returns/{return}/status',                            [\App\Http\Controllers\Admin\CancellationReturnController::class, 'updateReturnStatus'])->name('returns.status');
 Route::post  ('/returns/{return}/note',                              [\App\Http\Controllers\Admin\CancellationReturnController::class, 'updateReturnNote'])->name('returns.note');

 // Account & Profile
 Route::get  ('/account',    [AdminDashboardController::class, 'account'])      ->name('account');
 Route::post ('/account',    [AdminDashboardController::class, 'updateAccount'])->name('account.update');

 // Operational Tasks, Reports, Broadcasts & Logs
 Route::get  ('/tasks',      [AdminDashboardController::class, 'tasks'])        ->name('tasks.index');
 Route::get  ('/reports',    [AdminDashboardController::class, 'reports'])      ->name('reports.index');
 Route::get  ('/broadcasts', [AdminDashboardController::class, 'broadcasts'])   ->name('broadcasts.index');
 Route::post ('/broadcasts', [AdminDashboardController::class, 'saveBroadcast'])->name('broadcasts.save');
 Route::get  ('/logs',       [AdminDashboardController::class, 'logs'])         ->name('logs.index');
 });
});
