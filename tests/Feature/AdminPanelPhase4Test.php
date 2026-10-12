<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\MediaFile;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelPhase4Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /**
     * 1 & 2: Banner lifecycle - activate, deactivate, update, and storefront visibility.
     */
    public function test_banner_lifecycle_activation_and_storefront_exclusion(): void
    {
        $banner = Banner::create([
            'image' => 'banners/summer-banner.jpg',
            'primary_text' => 'Phase 4 Banner Test',
            'tagline' => 'Fresh Arrivals',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Verify active banner shows up on public storefront
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Phase 4 Banner Test');

        // Deactivate banner via admin update
        $updateResponse = $this->actingAs($this->admin)->post(route('admin.banners.update', $banner->id), [
            'primary_text' => 'Phase 4 Banner Test - Deactivated',
            'sort_order' => 1,
            'is_active' => '0',
        ]);
        $updateResponse->assertRedirect(route('admin.banners.index'));

        $banner->refresh();
        $this->assertFalse((bool) $banner->is_active);

        // Verify deactivated banner NEVER appears on public storefront
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Phase 4 Banner Test - Deactivated');
    }

    /**
     * 3: Deleted banners do not reappear through cached storefront responses.
     */
    public function test_deleted_banners_cache_invalidation_and_exclusion(): void
    {
        $banner = Banner::create([
            'image' => 'banners/spring-hero.jpg',
            'primary_text' => 'Spring Clearance 2026',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Prime public cache
        $this->get('/')->assertSee('Spring Clearance 2026');

        // Soft delete the banner via DELETE route
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/banners/{$banner->id}");
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertSoftDeleted('banners', ['id' => $banner->id]);

        // Verify public storefront does NOT display soft-deleted banner
        $storefront = $this->get('/');
        $storefront->assertStatus(200);
        $storefront->assertDontSee('Spring Clearance 2026');
    }

    /**
     * 4 & 5: Bulk image import accepts valid images and rejects invalid/oversized/scripts.
     */
    public function test_bulk_image_upload_accepts_valid_images_and_rejects_scripts(): void
    {
        $validFile1 = UploadedFile::fake()->create('product_1.jpg', 300, 'image/jpeg');
        $validFile2 = UploadedFile::fake()->create('product_2.png', 300, 'image/png');

        // Upload valid images
        $response = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$validFile1, $validFile2],
            'folder' => 'catalog',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonCount(2, 'data.uploaded');
        $this->assertEquals(2, MediaFile::where('folder', 'catalog')->count());

        // Attempt uploading malicious or executable script
        $scriptFile = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');
        $failResponse = $this->actingAs($this->admin)->postJson(route('admin.appearance.files.bulk-upload'), [
            'files' => [$scriptFile],
            'folder' => 'catalog',
        ]);
        $failResponse->assertStatus(422);
    }

    /**
     * 6, 7 & 8: Bulk image SKU matching, conflict detection, and existing image preservation.
     */
    public function test_bulk_image_matching_detects_conflicts_and_preserves_existing_images(): void
    {
        $category = Category::create([
            'name' => 'Shirts',
            'slug' => 'shirts-phase4',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Classic Oxford Shirt',
            'slug' => 'classic-oxford-shirt',
            'sku' => 'OXF-100',
            'code' => 'OXF100',
            'price' => 1299,
            'is_active' => true,
            'image' => 'products/oxford-main.jpg',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image' => 'products/oxford-main.jpg',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $file1 = UploadedFile::fake()->create('OXF-100_1.jpg', 50, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('OXF100_back.png', 50, 'image/png');
        $unmatchedFile = UploadedFile::fake()->create('UNKNOWN_999.jpg', 50, 'image/jpeg');

        // Preview images
        $previewResponse = $this->actingAs($this->admin)->post(route('admin.products.bulk-images.preview'), [
            'images' => [$file1, $file2, $unmatchedFile],
        ]);

        $previewResponse->assertStatus(200);
        $previewData = $previewResponse->json();
        $this->assertTrue($previewData['success']);
        $this->assertEquals(3, $previewData['counts']['total']);
        $this->assertEquals(2, $previewData['counts']['matched']);
        $this->assertEquals(1, $previewData['counts']['unmatched']);

        // Check matched contains our product
        $matchedKeys = collect($previewData['matched'])->pluck('product_id')->all();
        $this->assertContains($product->id, $matchedKeys);

        $batchId = $previewData['batch_id'];
        $tempFilename = $previewData['matched'][0]['temp_filename'];

        // Apply bulk import with overwrite = false
        $applyResponse = $this->actingAs($this->admin)->post(route('admin.products.bulk-images.apply'), [
            'batch_id' => $batchId,
            'assignments' => [
                [
                    'temp_filename' => $tempFilename,
                    'product_id' => $product->id,
                    'as_primary' => false,
                    'overwrite' => false,
                ],
            ],
        ]);

        $applyResponse->assertStatus(200);
        $applyData = $applyResponse->json();
        $this->assertTrue($applyData['success']);
        $this->assertEquals(1, $applyData['imported_count']);

        // Ensure original primary image was NOT overwritten
        $product->refresh();
        $this->assertEquals('products/oxford-main.jpg', $product->image);
        $this->assertCount(2, $product->images);
    }

    /**
     * 9 & 12: Admin header responsiveness, mobile drawer, and configured site name.
     */
    public function test_admin_header_displays_configured_site_name_and_responsive_elements(): void
    {
        StoreSetting::setValue('store_name', 'Noolandcrop Handlooms');
        Cache::flush();

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Confirmed configured site name in header
        $response->assertSee('Noolandcrop Handlooms');

        // Responsive drawer toggle and classes
        $response->assertSee('id="adminDrawerToggle"', false);
        $response->assertSee('class="admin-brand-left"', false);
        $response->assertSee('class="admin-desktop-nav"', false);
        $response->assertSee('class="admin-mobile-drawer"', false);
    }

    /**
     * 10 & 11: Admin dashboard cards and navigation for Orders, Returns, and Support.
     */
    public function test_admin_dashboard_metrics_and_navigation_links(): void
    {
        // Seed an order
        $order = Order::create([
            'order_code' => 'NC' . strtoupper(substr(uniqid(), -6)),
            'customer_id' => $this->admin->id,
            'contact_name' => $this->admin->name,
            'contact_mobile' => '9876543210',
            'contact_email' => $this->admin->email,
            'addr_full_name' => $this->admin->name,
            'addr_line_1' => '12 Gandhi Road',
            'addr_city' => 'Erode',
            'addr_state' => 'Tamil Nadu',
            'addr_pincode' => '638001',
            'addr_mobile_primary' => '9876543210',
            'addr_type' => 'home',
            'subtotal' => 1500.00,
            'discount' => 0.00,
            'tax_amount' => 75.00,
            'shipping' => 0.00,
            'total' => 1575.00,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'placed',
        ]);

        // Seed an open return
        OrderReturn::create([
            'order_id' => $order->id,
            'customer_id' => $this->admin->id,
            'status' => 'requested',
            'reason' => 'Defective item',
        ]);

        // Seed an open support ticket
        SupportTicket::create([
            'customer_id' => $this->admin->id,
            'order_code' => $order->order_code,
            'subject' => 'Order delivery status',
            'status' => 'open',
            'category' => 'order',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Verify links to orders, cancellations/returns, and support
        $response->assertSee(route('admin.orders.index'));
        $response->assertSee(route('admin.cancellations-returns.index'));
        $response->assertSee(route('admin.support.index'));

        // Verify operational attention alert for returns and open tickets
        $response->assertSee('Action Required:');
        $response->assertSee('Return request');
        $response->assertSee('Open support ticket');
    }

    /**
     * 13: Unwanted Invoice and Account Settings menu entries are removed from visible navigation.
     */
    public function test_unwanted_invoice_and_account_settings_menu_entries_removed_from_visible_navigation(): void
    {
        // 1. In admin settings sidebar
        $settingsResponse = $this->actingAs($this->admin)->get(route('admin.settings.store'));
        $settingsResponse->assertStatus(200);

        // Assert "Billing & Invoices" and "Account Settings" are NOT present in settings sidebar
        $settingsResponse->assertDontSee('Billing &amp; Invoices', false);
        $settingsResponse->assertDontSee('Billing & Invoices', false);
        $settingsResponse->assertDontSee('Account Settings', false);

        // 2. In global admin navigation
        $dashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);

        // Top nav / mobile drawer should NOT contain the Invoices dropdown or "All Invoices" / "Invoice Templates"
        $dashboardResponse->assertDontSee('All Invoices');
        $dashboardResponse->assertDontSee('Invoice Templates');

        // Payments link remains visible
        $dashboardResponse->assertSee(route('admin.payments.index'));

        // 3. Direct routes for invoices and account remain accessible to authorized admins
        $this->actingAs($this->admin)->get(route('admin.invoices.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.templates.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.account'))->assertStatus(200);
    }

    /**
     * 14: Role-based access control restricts unauthorized users from admin routes.
     */
    public function test_unauthorized_guests_cannot_access_admin_panel(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.banners.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.settings.store'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.invoices.index'))->assertRedirect(route('admin.login'));
    }

    /**
     * 16: Regression tests for storefront, shop, and cart.
     */
    public function test_storefront_and_cart_regression_integrity(): void
    {
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);

        $shopResponse = $this->get('/shop');
        $shopResponse->assertStatus(200);

        $cartResponse = $this->get('/cart');
        $cartResponse->assertStatus(200);
    }
}
