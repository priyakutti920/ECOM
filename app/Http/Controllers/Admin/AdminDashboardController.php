<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OtherCharge;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\SupportTicket;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = \App\Models\User::count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalRevenue = (float) Order::where(function($q) {
            $q->where('payment_status', 'paid')
              ->orWhere('status', 'delivered');
        })->sum('total');

        $pendingOrdersCount = Order::whereIn('status', ['placed', 'confirmed', 'processing', 'packed'])->count();
        $deliveredOrdersCount = Order::where('status', 'delivered')->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->count();
        $pendingReviewsCount = \App\Models\Review::where('is_approved', false)->count();

        $recentOrders = Order::with(['items', 'customer'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $currency = StoreSetting::getCurrencySymbol();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalOrders',
            'totalProducts',
            'totalRevenue',
            'pendingOrdersCount',
            'deliveredOrdersCount',
            'cancelledOrdersCount',
            'pendingReviewsCount',
            'recentOrders',
            'currency'
        ));
    }

    public function settings()
    {
        return view('admin.settings.index');
    }

    public function settingsStore()
    {
        return view('admin.settings.store');
    }

    public function settingsSeo()
    {
        return view('admin.settings.seo');
    }

    public function settingsSocial()
    {
        return app(\App\Http\Controllers\Admin\SocialLinkController::class)->index();
    }

    public function settingsSocialData()
    {
        return app(\App\Http\Controllers\Admin\SocialLinkController::class)->getData();
    }

    public function settingsSocialSave(Request $request)
    {
        return app(\App\Http\Controllers\Admin\SocialLinkController::class)->save($request);
    }

    public function settingsPlugin()
    {
        return view('admin.settings.plugin');
    }

    public function settingsPayment()
    {
        return view('admin.settings.payment');
    }

    public function settingsPaymentData()
    {
        $gateways = PaymentGateway::orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $gateways]);
    }

    public function settingsPaymentToggle(Request $request)
    {
        $gateway = PaymentGateway::where('slug', $request->slug)->first();
        if (!$gateway) {
            return response()->json(['success' => false, 'message' => 'Gateway not found.'], 404);
        }

        // Cannot toggle if not available
        if (!$gateway->is_available) {
            return response()->json(['success' => false, 'message' => 'This payment gateway is not available. Please configure it in the database.'], 403);
        }

        $gateway->is_active = $gateway->is_active ? 0 : 1;
        $gateway->save();

        return response()->json([
            'success' => true,
            'message' => $gateway->is_active ? $gateway->name . ' activated.' : $gateway->name . ' deactivated.',
            'is_active' => $gateway->is_active,
            'gateway' => $gateway,
        ]);
    }

    public function settingsPaymentSave(Request $request)
    {
        $gateway = PaymentGateway::where('slug', $request->slug)->first();
        if (!$gateway) {
            return response()->json(['success' => false, 'message' => 'Gateway not found.'], 404);
        }

        $gateway->method_name = $request->method_name ?? '';
        $gateway->description = $request->description ?? '';
        $gateway->save();

        // Handle gateway-specific credentials (JSON field)
        if ($request->has('credentials') && is_array($request->credentials)) {
            $gateway->credentials = $request->credentials;
            $gateway->save();
        }

        return response()->json(['success' => true, 'message' => $gateway->name . ' settings saved.']);
    }

    public function settingsSmtp()
    {
        return view('admin.settings.smtp');
    }

    public function settingsSmtpData()
    {
        $data = [
            'smtp_host'       => StoreSetting::getValue('smtp_host', ''),
            'smtp_port'      => StoreSetting::getValue('smtp_port', ''),
            'smtp_encryption'=> StoreSetting::getValue('smtp_encryption', ''),
            'smtp_username'  => StoreSetting::getValue('smtp_username', ''),
            'smtp_password'  => StoreSetting::getValue('smtp_password', ''),
            'smtp_from_email'=> StoreSetting::getValue('smtp_from_email', ''),
            'smtp_from_name' => StoreSetting::getValue('smtp_from_name', ''),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function settingsSmtpSave(Request $request)
    {
        $fields = [
            'smtp_host', 'smtp_port', 'smtp_encryption',
            'smtp_username', 'smtp_password', 'smtp_from_email', 'smtp_from_name',
        ];

        foreach ($fields as $field) {
            StoreSetting::setValue($field, $request->input($field, ''));
        }

        return response()->json(['success' => true, 'message' => 'SMTP settings saved successfully.']);
    }

    public function settingsSmtpTest(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $host       = StoreSetting::getValue('smtp_host');
        $port       = StoreSetting::getValue('smtp_port');
        $encryption = StoreSetting::getValue('smtp_encryption');
        $username   = StoreSetting::getValue('smtp_username');
        $password   = StoreSetting::getValue('smtp_password');
        $fromEmail  = StoreSetting::getValue('smtp_from_email');
        $fromName   = StoreSetting::getValue('smtp_from_name');

        if (!$host || !$username || !$fromEmail) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP configuration is incomplete. Please save your settings first.',
            ], 422);
        }

        // Temporarily override mail config
        config([
            'mail.default'               => 'smtp',
            'mail.mailers.smtp.host'     => $host,
            'mail.mailers.smtp.port'     => $port ?: 587,
            'mail.mailers.smtp.encryption' => $encryption ?: null,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.from.address'          => $fromEmail,
            'mail.from.name'             => $fromName ?: 'Store',
        ]);

        try {
            Mail::raw('This is a test email from your store. If you received this, your SMTP configuration is working correctly!', function ($message) use ($request, $fromEmail, $fromName) {
                $message->to($request->email)
                    ->from($fromEmail, $fromName ?: 'Store')
                    ->subject('Test Email - SMTP Configuration Working!');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully! Check your inbox.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function settingsTax()
    {
        return view('admin.settings.tax');
    }

    public function settingsTaxData()
    {
        $data = [
            'gst_percentage' => StoreSetting::getValue('gst_percentage', ''),
            'charges'        => OtherCharge::where('is_active', 1)->orderBy('sort_order')->get(['id', 'name', 'type', 'value'])->toArray(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function settingsTaxGstSave(Request $request)
    {
        $request->validate([
            'gst_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        StoreSetting::setValue('gst_percentage', $request->gst_percentage ?? '');

        return response()->json(['success' => true, 'message' => 'GST percentage saved.']);
    }

    public function settingsTaxChargeSave(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:percentage,amount',
            'value' => 'required|numeric|min:0',
        ]);

        if ($request->id) {
            $charge = OtherCharge::find($request->id);
            if (!$charge) {
                return response()->json(['success' => false, 'message' => 'Charge not found.'], 404);
            }
            $charge->name  = $request->name;
            $charge->type  = $request->type;
            $charge->value = $request->value;
            $charge->save();
            $msg = 'Charge updated.';
        } else {
            $maxOrder = OtherCharge::max('sort_order') ?? 0;
            OtherCharge::create([
                'name'       => $request->name,
                'type'       => $request->type,
                'value'      => $request->value,
                'is_active'  => 1,
                'sort_order' => $maxOrder + 1,
            ]);
            $msg = 'Charge added.';
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function settingsTaxChargeDelete(Request $request)
    {
        $charge = OtherCharge::find($request->id);
        if (!$charge) {
            return response()->json(['success' => false, 'message' => 'Charge not found.'], 404);
        }

        $charge->delete();

        return response()->json(['success' => true, 'message' => 'Charge deleted.']);
    }

    // Feature Settings (Trust strip on home page — 4 icons)
    public function settingsFeatures()
    {
        $features = [];
        for ($i = 1; $i <= 4; $i++) {
            $features[] = [
                'title'   => StoreSetting::getValue("feature_{$i}_title", ''),
                'desc'    => StoreSetting::getValue("feature_{$i}_desc", ''),
                'icon'    => StoreSetting::getValue("feature_{$i}_icon", 'las la-check-circle'),
                'enabled' => StoreSetting::getValue("feature_{$i}_enabled", '1') === '1',
            ];
        }
        return view('admin.settings.features', compact('features'));
    }

    public function settingsFeaturesData()
    {
        $features = [];
        for ($i = 1; $i <= 4; $i++) {
            $features[] = [
                'title' => StoreSetting::getValue("feature_{$i}_title", ''),
                'desc'  => StoreSetting::getValue("feature_{$i}_desc", ''),
                'icon'  => StoreSetting::getValue("feature_{$i}_icon", 'las la-check-circle'),
                'enabled' => StoreSetting::getValue("feature_{$i}_enabled", '1') === '1',
            ];
        }
        return response()->json(['success' => true, 'data' => $features]);
    }

    public function settingsFeaturesSave(Request $request)
    {
        $titles = $request->input('titles', []);
        $descs  = $request->input('descs', []);
        $icons  = $request->input('icons', []);
        $enabled = $request->input('enabled', []);

        for ($i = 1; $i <= 4; $i++) {
            $idx = $i - 1;
            if ($request->has('enabled')) {
                $val = $enabled[$idx] ?? null;
                $isEnabled = ($val === '1' || $val === 1 || $val === true || $val === 'true' || $val === 'on');
                StoreSetting::setValue("feature_{$i}_enabled", $isEnabled ? '1' : '0');
            }

            if (isset($titles[$idx])) {
                StoreSetting::setValue("feature_{$i}_title", trim($titles[$idx]));
            }
            if (isset($descs[$idx])) {
                StoreSetting::setValue("feature_{$i}_desc",  trim($descs[$idx]));
            }
            if (isset($icons[$idx])) {
                $icon = trim($icons[$idx]);
                StoreSetting::setValue("feature_{$i}_icon", $icon !== '' ? $icon : 'las la-check-circle');
            }
        }

        return response()->json(['success' => true, 'message' => 'Features saved successfully.']);
    }

    public function settingsFeaturesToggle(Request $request)
    {
        $idx = (int) $request->input('index', 0);
        if ($idx < 1 || $idx > 4) {
            return response()->json(['success' => false, 'message' => 'Invalid feature index.']);
        }
        $current = StoreSetting::getValue("feature_{$idx}_enabled", '1') === '1';
        $new = !$current;
        StoreSetting::setValue("feature_{$idx}_enabled", $new ? '1' : '0');

        return response()->json([
            'success' => true,
            'enabled' => $new,
            'message' => $new ? 'Feature enabled.' : 'Feature disabled.',
        ]);
    }

    public function settingsStoreData()
    {
        $data = [
            'store_name'      => StoreSetting::getValue('store_name', config('app.name', '')),
            'store_tagline'   => StoreSetting::getValue('store_tagline', ''),
            'address'         => StoreSetting::getValue('address', ''),
            'order_close'     => StoreSetting::getValue('order_close', '0'),
            'phones'          => json_decode(StoreSetting::getValue('phones', '[]'), true),
            'emails'          => json_decode(StoreSetting::getValue('emails', '[]'), true),
            'logo_url'        => $this->getSettingImageUrl('logo'),
            'favicon_url'     => $this->getSettingImageUrl('favicon'),
            'primary_color'   => StoreSetting::getPrimaryColor(),
            'secondary_color' => StoreSetting::getSecondaryColor(),
            'header_style'    => StoreSetting::getHeaderStyle(),
            'theme_font'      => StoreSetting::getValue('theme_font', 'Rubik'),
            'currency_symbol' => StoreSetting::getValue('currency_symbol', '₹'),
            'show_map'        => StoreSetting::getValue('show_map', '1'),
            'map_location'    => StoreSetting::getValue('map_location', ''),
            'map_zoom'        => StoreSetting::getValue('map_zoom', '14'),
            'map_iframe'      => StoreSetting::getValue('map_iframe', ''),
            'map_embed_url'   => StoreSetting::getMapEmbedUrl(),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function settingsStoreSave(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'logo'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:2048',
            'favicon'    => 'nullable|mimes:ico,png,jpg,jpeg,webp,svg|max:1024',
        ]);

        StoreSetting::setValue('store_name', $request->store_name);
        StoreSetting::setValue('store_tagline', $request->store_tagline ?? '');
        StoreSetting::setValue('address', $request->address ?? '');
        StoreSetting::setValue('order_close', $request->order_close ?? '0');
        StoreSetting::setValue('phones', $request->phones ?? '[]');
        StoreSetting::setValue('emails', $request->emails ?? '[]');

        if ($request->filled('primary_color')) {
            $p = trim($request->primary_color);
            StoreSetting::setValue('primary_color', str_starts_with($p, '#') ? $p : ('#' . $p));
        }
        if ($request->filled('secondary_color')) {
            $s = trim($request->secondary_color);
            StoreSetting::setValue('secondary_color', str_starts_with($s, '#') ? $s : ('#' . $s));
        }
        if ($request->filled('header_style')) {
            StoreSetting::setValue('header_style', $request->header_style);
        }
        if ($request->filled('theme_font')) {
            StoreSetting::setValue('theme_font', $request->theme_font);
        }
        \Illuminate\Support\Facades\Cache::forget('store_settings_all');
        \Illuminate\Support\Facades\Cache::forget('store_primary_color');
        \Illuminate\Support\Facades\Cache::forget('store_secondary_color');
        \Illuminate\Support\Facades\Cache::forget('store_header_style');
        \Illuminate\Support\Facades\Cache::forget('store_theme_font');
        if ($request->filled('currency_symbol')) {
            StoreSetting::setValue('currency_symbol', $request->currency_symbol);
        }

        // Store Map Settings
        StoreSetting::setValue('show_map', $request->boolean('show_map') ? '1' : '0');
        StoreSetting::setValue('map_location', $request->map_location ?? '');
        StoreSetting::setValue('map_zoom', $request->map_zoom ?? '14');
        StoreSetting::setValue('map_iframe', $request->map_iframe ?? '');

        // Logo
        if ($request->boolean('delete_logo')) {
            $this->deleteSettingImage('logo');
        } elseif ($request->hasFile('logo')) {
            $this->deleteSettingImage('logo');
            $path = $request->file('logo')->store('settings', 'public');
            StoreSetting::setValue('logo', $path);
            try {
                \App\Models\MediaFile::create([
                    'name' => 'Store Logo ' . date('Y-m-d'),
                    'filename' => basename($path),
                    'path' => $path,
                    'disk' => 'public',
                    'mime_type' => $request->file('logo')->getClientMimeType() ?: 'image/png',
                    'size' => $request->file('logo')->getSize() ?: 0,
                    'folder' => 'settings',
                ]);
            } catch (\Throwable $e) {}
        }

        // Favicon
        if ($request->boolean('delete_favicon')) {
            $this->deleteSettingImage('favicon');
        } elseif ($request->hasFile('favicon')) {
            $this->deleteSettingImage('favicon');
            $path = $request->file('favicon')->store('settings', 'public');
            StoreSetting::setValue('favicon', $path);
            try {
                \App\Models\MediaFile::create([
                    'name' => 'Store Favicon ' . date('Y-m-d'),
                    'filename' => basename($path),
                    'path' => $path,
                    'disk' => 'public',
                    'mime_type' => $request->file('favicon')->getClientMimeType() ?: 'image/png',
                    'size' => $request->file('favicon')->getSize() ?: 0,
                    'folder' => 'settings',
                ]);
            } catch (\Throwable $e) {}
        }

        \Illuminate\Support\Facades\Cache::forget('store_settings_all');

        return response()->json(['success' => true, 'message' => 'Settings saved successfully.']);
    }

    private function getSettingImageUrl(string $key): ?string
    {
        $path = StoreSetting::getValue($key);
        if (!$path) return null;
        return StoreSetting::resolveMediaUrl($path);
    }

    private function deleteSettingImage(string $key): void
    {
        $path = StoreSetting::getValue($key);
        if ($path) {
            Storage::disk('public')->delete($path);
            StoreSetting::setValue($key, null);
            \Illuminate\Support\Facades\Cache::forget('store_settings_all');
        }
    }

    // SEO Settings
    public function settingsSeoData()
    {
        $data = [
            'meta_title'       => StoreSetting::getValue('meta_title', ''),
            'meta_description' => StoreSetting::getValue('meta_description', ''),
            'meta_keywords'    => json_decode(StoreSetting::getValue('meta_keywords', '[]'), true),
            'canonical_url'    => StoreSetting::getValue('canonical_url', ''),
            'robots_txt'       => StoreSetting::getValue('robots_txt', "User-agent: *\nDisallow:"),
            'gtm_container_id' => StoreSetting::getValue('gtm_container_id', ''),
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function settingsSeoSave(Request $request)
    {
        StoreSetting::setValue('meta_title', $request->meta_title ?? '');
        StoreSetting::setValue('meta_description', $request->meta_description ?? '');
        StoreSetting::setValue('meta_keywords', $request->meta_keywords ?? '[]');
        StoreSetting::setValue('canonical_url', $request->canonical_url ?? '');
        StoreSetting::setValue('robots_txt', $request->robots_txt ?? '');
        StoreSetting::setValue('gtm_container_id', $request->gtm_container_id ?? '');

        return response()->json(['success' => true, 'message' => 'SEO settings saved successfully.']);
    }

    public function settingsPluginData()
    {
        $waNumber = StoreSetting::getValue('wa_number') ?: StoreSetting::getValue('whatsapp_number', '');
        $waMsg    = StoreSetting::getValue('wa_message') ?: StoreSetting::getValue('whatsapp_default_message', '');

        $data = [
            'wa_enabled'  => StoreSetting::getValue('wa_enabled', !empty($waNumber) ? '1' : '0'),
            'wa_number'   => $waNumber,
            'wa_message'  => $waMsg,
        ];
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function settingsPluginSave(Request $request)
    {
        $enabled = ($request->wa_enabled == '1' || $request->wa_enabled === true || $request->wa_enabled === 'true') ? '1' : '0';
        $number  = trim($request->wa_number ?? '');
        $message = trim($request->wa_message ?? '');

        StoreSetting::setValue('wa_enabled', $enabled);
        StoreSetting::setValue('wa_number', $number);
        StoreSetting::setValue('wa_message', $message);

        // Keep both keys in sync across the application
        StoreSetting::setValue('whatsapp_number', $enabled === '1' ? $number : '');
        StoreSetting::setValue('whatsapp_default_message', $message);

        return response()->json(['success' => true, 'message' => 'Plugin settings saved successfully.']);
    }

    public function generateSitemap(Request $request)
    {
        $baseUrl = config('app.url', url('/'));
        $canonicalUrl = StoreSetting::getValue('canonical_url', $baseUrl);

        $categories = Category::where('status', 0)->get(['id', 'updated_at']);
        $products = Product::where('status', 1)->get(['id', 'updated_at']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . rtrim($canonicalUrl, '/') . '/</loc>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";

        foreach ($categories as $cat) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . rtrim($canonicalUrl, '/') . '/category/' . $cat->id . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($cat->updated_at ? $cat->updated_at->toDateString() : date('Y-m-d')) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        foreach ($products as $prod) {
            $slug = $prod->slug ?: 'product-' . $prod->id;
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . rtrim($canonicalUrl, '/') . '/product/' . $slug . '</loc>' . "\n";
            $xml .= '    <lastmod>' . ($prod->updated_at ? $prod->updated_at->toDateString() : date('Y-m-d')) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function generateRobots(Request $request)
    {
        $canonicalUrl = StoreSetting::getValue('canonical_url', config('app.url', url('/')));

        $default = "User-agent: *\n";
        $default .= "Allow: /\n";
        $default .= "Sitemap: " . rtrim($canonicalUrl, '/') . "/sitemap.xml\n";

        return response()->json(['success' => true, 'content' => $default]);
    }

    // ===== ACCOUNT / PROFILE =====
    public function account()
    {
        $admin = Auth::user();
        return view('admin.account', compact('admin'));
    }

    public function updateAccount(Request $request)
    {
        $admin = Auth::user();
        $request->validate([
            'name'             => 'required|string|max:120',
            'email'            => 'required|email|max:120|unique:users,email,' . $admin->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password'     => ['nullable', Password::min(8)->letters()->numbers(), 'confirmed'],
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->with('error', 'Current password does not match.');
            }
            $admin->password = Hash::make($request->new_password);
        }

        $admin->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    // ===== OPERATIONAL TASKS =====
    public function tasks()
    {
        $pendingOrders = Order::whereIn('status', ['placed', 'accepted'])->with(['items'])->orderBy('id')->get();
        $openReturns   = OrderReturn::where('status', 'requested')->with(['order', 'product'])->orderBy('id')->get();
        $openTickets   = SupportTicket::where('status', 'open')->orderByDesc('updated_at')->take(10)->get();
        $unpaidOrders  = Order::where('payment_status', 'pending')->orderByDesc('id')->take(10)->get();

        return view('admin.tasks', compact('pendingOrders', 'openReturns', 'openTickets', 'unpaidOrders'));
    }

    // ===== SALES & PERFORMANCE REPORTS =====
    public function reports(Request $request)
    {
        $period = $request->get('period', 'month');

        $dateQuery = function ($q) use ($period) {
            if ($period === 'today') {
                $q->whereDate('created_at', today());
            } elseif ($period === 'week') {
                $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($period === 'month') {
                $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            } elseif ($period === 'year') {
                $q->whereYear('created_at', now()->year);
            }
        };

        $ordersQuery = Order::query();
        $dateQuery($ordersQuery);

        $totalOrders     = (clone $ordersQuery)->count();
        $totalRevenue    = (float) (clone $ordersQuery)->where('payment_status', 'paid')->sum('total');
        $deliveredOrders = (clone $ordersQuery)->where('status', 'delivered')->count();
        $avgOrderValue   = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $ordersByStatus = (clone $ordersQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $topItemsQuery = OrderItem::query();
        $dateQuery($topItemsQuery);

        $topItems = $topItemsQuery
            ->select('product_id', 'product_name', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(line_total) as total_amount'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return view('admin.reports', compact('totalOrders', 'totalRevenue', 'deliveredOrders', 'avgOrderValue', 'ordersByStatus', 'topItems', 'period'));
    }

    // ===== BROADCASTS / ANNOUNCEMENTS =====
    public function broadcasts()
    {
        $broadcastMessage = StoreSetting::getValue('store_broadcast_message', '');
        $broadcastEnabled = (bool) StoreSetting::getValue('store_broadcast_enabled', false);
        $broadcastType    = StoreSetting::getValue('store_broadcast_type', 'info');

        return view('admin.broadcasts', compact('broadcastMessage', 'broadcastEnabled', 'broadcastType'));
    }

    public function saveBroadcast(Request $request)
    {
        StoreSetting::setValue('store_broadcast_message', $request->input('broadcast_message', ''));
        StoreSetting::setValue('store_broadcast_enabled', $request->has('broadcast_enabled') ? '1' : '0');
        StoreSetting::setValue('store_broadcast_type', $request->input('broadcast_type', 'info'));

        return back()->with('success', 'Broadcast banner settings saved successfully.');
    }

    // ===== USER & SYSTEM AUDIT LOGS =====
    public function logs()
    {
        $ordersWithHistory = Order::whereNotNull('status_history')
            ->orderByDesc('updated_at')
            ->take(30)
            ->get();

        $events = [];
        foreach ($ordersWithHistory as $order) {
            $history = $order->status_history ?? [];
            if (is_array($history)) {
                foreach ($history as $h) {
                    $events[] = [
                        'order_code' => $order->order_code,
                        'event'      => $h['event'] ?? 'status_update',
                        'detail'     => $h['detail'] ?? '',
                        'by'         => $h['by'] ?? 'System',
                        'at'         => $h['at'] ?? ($order->updated_at ? $order->updated_at->toDateTimeString() : ''),
                    ];
                }
            }
        }

        usort($events, fn($a, $b) => strcmp($b['at'], $a['at']));
        $events = array_slice($events, 0, 50);

        return view('admin.logs', compact('events'));
    }
}
