<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAndButtonIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;
    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test-pages.com',
            'is_admin' => true,
        ]);

        // Create Customer
        $this->customerUser = User::factory()->create([
            'email' => 'customer@test-pages.com',
            'is_admin' => false,
        ]);

        // Create Test Category
        $this->category = Category::create([
            'name' => 'Fashion & Apparel',
            'slug' => 'fashion-apparel',
            'is_active' => true,
        ]);

        // Create Test Product
        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Classic Crewneck T-Shirt',
            'slug' => 'classic-crewneck-t-shirt',
            'price' => 799,
            'special_price' => 599,
            'stock' => 50,
            'sku' => 'TEST-CREW-01',
            'status' => 'active',
            'is_featured' => true,
            'description' => 'A premium cotton t-shirt built for daily comfort.',
        ]);
    }

    /**
     * Helper to inspect DOM, count buttons, forms, and check CSRF.
     */
    protected function validateDom(string $html, string $url): array
    {
        $this->assertStringNotContainsString('@extends', $html, "Blade @extends leaked in {$url}");
        $this->assertStringNotContainsString('@section', $html, "Blade @section leaked in {$url}");
        $this->assertStringNotContainsString('@endsection', $html, "Blade @endsection leaked in {$url}");

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Buttons
        $buttons = $xpath->query('//button');
        $this->assertGreaterThan(0, $buttons->length, "Page {$url} should have interactive buttons");

        // Forms and CSRF
        $forms = $xpath->query('//form');
        foreach ($forms as $form) {
            $method = strtoupper($form->getAttribute('method') ?: 'GET');
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $csrfInputs = $xpath->query('.//input[@name="_token"]', $form);
                $this->assertGreaterThan(
                    0, 
                    $csrfInputs->length, 
                    "Form with method {$method} in {$url} must have a valid CSRF _token input"
                );
            }
        }

        return [
            'buttons' => $buttons->length,
            'forms' => $forms->length,
        ];
    }

    public function test_storefront_homepage_and_buttons(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/');
        $this->assertGreaterThanOrEqual(2, $metrics['buttons']);
    }

    public function test_storefront_shop_page_and_buttons(): void
    {
        $response = $this->get('/shop');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/shop');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_storefront_category_page_and_buttons(): void
    {
        $response = $this->get('/category/' . $this->category->id);
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/category/' . $this->category->id);
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_storefront_product_detail_page_and_buttons(): void
    {
        $response = $this->get('/product/' . $this->product->slug);
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/product/' . $this->product->slug);
        $this->assertGreaterThanOrEqual(2, $metrics['buttons']);
        $response->assertSee('Add to Cart');
    }

    public function test_storefront_cart_page_and_buttons(): void
    {
        $response = $this->get('/cart');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/cart');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_storefront_buy_now_page_and_buttons(): void
    {
        // Unauthenticated guest is redirected to login
        $guestResp = $this->get('/buy-now');
        $guestResp->assertRedirect('/login');

        // Authenticated customer gets 200 with checkout buttons
        $response = $this->actingAs($this->customerUser, 'customer')->get('/buy-now');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/buy-now');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_storefront_deals_page_and_buttons(): void
    {
        $response = $this->get('/deals');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/deals');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_storefront_track_order_page_and_buttons(): void
    {
        $response = $this->get('/track-order');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/track-order');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
        $response->assertSee('Track Order');
    }

    public function test_storefront_help_page_and_buttons(): void
    {
        $response = $this->get('/help');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/help');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_storefront_auth_pages_and_buttons(): void
    {
        // Login page
        $login = $this->get('/login');
        $login->assertStatus(200);
        $this->validateDom($login->getContent(), '/login');

        // Register page
        $reg = $this->get('/register');
        $reg->assertStatus(200);
        $this->validateDom($reg->getContent(), '/register');

        // Forgot password page
        $forgot = $this->get('/forgot-password');
        $forgot->assertStatus(200);
        $this->validateDom($forgot->getContent(), '/forgot-password');
    }

    public function test_admin_dashboard_page_and_buttons(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/dashboard');
        $response->assertStatus(200);
        $metrics = $this->validateDom($response->getContent(), '/admin/dashboard');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_admin_product_management_pages_and_buttons(): void
    {
        // Products index
        $index = $this->actingAs($this->adminUser)->get('/admin/products');
        $index->assertStatus(200);
        $this->validateDom($index->getContent(), '/admin/products');

        // Products create
        $create = $this->actingAs($this->adminUser)->get('/admin/products/create');
        $create->assertStatus(200);
        $this->validateDom($create->getContent(), '/admin/products/create');

        // Products edit
        $edit = $this->actingAs($this->adminUser)->get('/admin/products/' . $this->product->id . '/edit');
        $edit->assertStatus(200);
        $this->validateDom($edit->getContent(), '/admin/products/' . $this->product->id . '/edit');
    }

    public function test_admin_category_management_pages_and_buttons(): void
    {
        // Categories index with add modal and action buttons
        $index = $this->actingAs($this->adminUser)->get('/admin/categories');
        $index->assertStatus(200);
        $metrics = $this->validateDom($index->getContent(), '/admin/categories');
        $this->assertGreaterThanOrEqual(1, $metrics['buttons']);
    }

    public function test_admin_orders_inventory_and_returns_pages_and_buttons(): void
    {
        // Orders index
        $orders = $this->actingAs($this->adminUser)->get('/admin/orders');
        $orders->assertStatus(200);
        $this->validateDom($orders->getContent(), '/admin/orders');

        // Inventory index
        $inventory = $this->actingAs($this->adminUser)->get('/admin/inventory');
        $inventory->assertStatus(200);
        $this->validateDom($inventory->getContent(), '/admin/inventory');

        // Cancellations & Returns index
        $returns = $this->actingAs($this->adminUser)->get('/admin/cancellations-returns');
        $returns->assertStatus(200);
        $this->validateDom($returns->getContent(), '/admin/cancellations-returns');
    }

    public function test_admin_coupons_and_bonuses_pages_and_buttons(): void
    {
        // Coupons index
        $coupons = $this->actingAs($this->adminUser)->get('/admin/coupons');
        $coupons->assertStatus(200);
        $this->validateDom($coupons->getContent(), '/admin/coupons');

        // Bonuses index
        $bonuses = $this->actingAs($this->adminUser)->get('/admin/bonuses');
        $bonuses->assertStatus(200);
        $this->validateDom($bonuses->getContent(), '/admin/bonuses');
    }

    public function test_admin_media_banners_and_appearance_pages_and_buttons(): void
    {
        // Media manager
        $media = $this->actingAs($this->adminUser)->get('/admin/appearance/files');
        $media->assertStatus(200);
        $this->validateDom($media->getContent(), '/admin/appearance/files');

        // Banners index
        $banners = $this->actingAs($this->adminUser)->get('/admin/banners');
        $banners->assertStatus(200);
        $this->validateDom($banners->getContent(), '/admin/banners');

        // Theme customizer
        $theme = $this->actingAs($this->adminUser)->get('/admin/appearance/theme');
        $theme->assertStatus(200);
        $this->validateDom($theme->getContent(), '/admin/appearance/theme');

        // Product sections
        $sections = $this->actingAs($this->adminUser)->get('/admin/appearance/sections');
        $sections->assertStatus(200);
        $this->validateDom($sections->getContent(), '/admin/appearance/sections');

        // Footer builder
        $footer = $this->actingAs($this->adminUser)->get('/admin/appearance/footer');
        $footer->assertStatus(200);
        $this->validateDom($footer->getContent(), '/admin/appearance/footer');
    }

    public function test_admin_settings_and_system_pages_and_buttons(): void
    {
        // Store settings
        $settings = $this->actingAs($this->adminUser)->get('/admin/settings');
        $settings->assertStatus(200);
        $this->validateDom($settings->getContent(), '/admin/settings');

        // SEO settings
        $seo = $this->actingAs($this->adminUser)->get('/admin/settings/seo');
        $seo->assertStatus(200);
        $this->validateDom($seo->getContent(), '/admin/settings/seo');

        // Social settings
        $social = $this->actingAs($this->adminUser)->get('/admin/settings/social');
        $social->assertStatus(200);
        $this->validateDom($social->getContent(), '/admin/settings/social');

        // Payment settings
        $payment = $this->actingAs($this->adminUser)->get('/admin/settings/payment');
        $payment->assertStatus(200);
        $this->validateDom($payment->getContent(), '/admin/settings/payment');

        // SMTP settings
        $smtp = $this->actingAs($this->adminUser)->get('/admin/settings/smtp');
        $smtp->assertStatus(200);
        $this->validateDom($smtp->getContent(), '/admin/settings/smtp');

        // Tax settings
        $tax = $this->actingAs($this->adminUser)->get('/admin/settings/tax');
        $tax->assertStatus(200);
        $this->validateDom($tax->getContent(), '/admin/settings/tax');

        // Features settings
        $features = $this->actingAs($this->adminUser)->get('/admin/settings/features');
        $features->assertStatus(200);
        $this->validateDom($features->getContent(), '/admin/settings/features');

        // Invoices
        $invoices = $this->actingAs($this->adminUser)->get('/admin/invoices');
        $invoices->assertStatus(200);
        $this->validateDom($invoices->getContent(), '/admin/invoices');

        // Templates
        $templates = $this->actingAs($this->adminUser)->get('/admin/templates');
        $templates->assertStatus(200);
        $this->validateDom($templates->getContent(), '/admin/templates');

        // Payments
        $payments = $this->actingAs($this->adminUser)->get('/admin/payments');
        $payments->assertStatus(200);
        $this->validateDom($payments->getContent(), '/admin/payments');

        // User management
        $users = $this->actingAs($this->adminUser)->get('/admin/users');
        $users->assertStatus(200);
        $this->validateDom($users->getContent(), '/admin/users');

        // Reviews
        $reviews = $this->actingAs($this->adminUser)->get('/admin/reviews');
        $reviews->assertStatus(200);
        $this->validateDom($reviews->getContent(), '/admin/reviews');

        // Support
        $support = $this->actingAs($this->adminUser)->get('/admin/support');
        $support->assertStatus(200);
        $this->validateDom($support->getContent(), '/admin/support');

        // System logs
        $logs = $this->actingAs($this->adminUser)->get('/admin/logs');
        $logs->assertStatus(200);
        $this->validateDom($logs->getContent(), '/admin/logs');
    }
}
