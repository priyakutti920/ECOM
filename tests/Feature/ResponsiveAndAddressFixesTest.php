<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Models\WaMessageLog;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResponsiveAndAddressFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::clearCache();
    }

    /**
     * PHASE 1: Admin settings and WhatsApp page render responsive CSS rules.
     */
    public function test_admin_whatsapp_settings_renders_responsive_css_and_no_overflow(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $res = $this->actingAs($admin)->get(route('admin.settings.whatsapp'));
        $res->assertStatus(200);

        // Assert mobile-friendly CSS breakpoints exist
        $res->assertSee('wallet-stat-card', false);
        $res->assertSee('admin-phone-input-row', false);
        $res->assertSee('@media (max-width: 576px)', false);
        $res->assertSee('@media (max-width: 360px)', false);
        $res->assertSee('log-table-wrap', false);
    }

    /**
     * PHASE 2: WhatsApp recipient routing — Admin order notifications route exclusively
     * to the configured admin WhatsApp mobile number, NEVER store number.
     */
    public function test_admin_order_notification_recipient_is_exclusively_admin_phone(): void
    {
        // Configure store number vs admin number
        StoreSetting::setValue('wa_number', '8056081594');
        StoreSetting::setValue('whatsapp_number', '8056081594');
        StoreSetting::setValue('wa_admin_phone', '919876543210');
        StoreSetting::clearCache();

        $this->assertEquals('919876543210', WhatsAppService::getAdminPhone());

        // Fast2SMS fake API call
        $customer = User::factory()->create(['is_admin' => false]);
        $product = Product::create([
            'name' => 'Test Saree',
            'slug' => 'test-saree-' . uniqid(),
            'price' => 500,
            'stock_status' => 'in_stock',
            'qty' => 10,
            'status' => 1,
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_code' => 'NC-TEST-001',
            'contact_name' => 'Alice Customer',
            'contact_mobile' => '9123456789',
            'addr_full_name' => 'Alice Customer',
            'addr_line_1' => 'Flat 12B, Green Woods',
            'addr_city' => 'Chennai',
            'addr_state' => 'Tamil Nadu',
            'addr_pincode' => '600001',
            'addr_mobile_primary' => '9123456789',
            'total' => 500,
            'payment_method' => 'cod',
            'status' => 'placed',
        ]);

        // Attempt sending with missing Fast2SMS auth key (simulated failure)
        config(['services.whatsapp.fast2sms_auth_key' => '']);
        $sent = WhatsAppService::sendOrderNotificationToAdmin($order);

        // Assert it does NOT route to store number (8056081594)
        $this->assertNotEquals('918056081594', WhatsAppService::getAdminPhone());
        $this->assertEquals('919876543210', WhatsAppService::getAdminPhone());
    }

    /**
     * PHASE 2: If admin number is missing, reports clear error and does not silently fall back.
     */
    public function test_admin_notification_reports_clear_error_when_admin_phone_missing(): void
    {
        StoreSetting::setValue('wa_number', '8056081594');
        StoreSetting::setValue('wa_admin_phone', '');
        StoreSetting::setValue('wa_admin_mobile', '');
        config(['services.whatsapp.admin_phone' => '']);
        putenv('WA_ADMIN_PHONE=');
        putenv('WA_ADMIN_MOBILE=');
        StoreSetting::clearCache();

        $this->assertEmpty(WhatsAppService::getAdminPhone());

        $customer = User::factory()->create(['is_admin' => false]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'order_code' => 'NC-NO-ADMIN',
            'contact_name' => 'Bob',
            'contact_mobile' => '9123456789',
            'addr_full_name' => 'Bob',
            'addr_line_1' => 'Sample Street',
            'addr_city' => 'Madurai',
            'addr_state' => 'Tamil Nadu',
            'addr_pincode' => '625001',
            'addr_mobile_primary' => '9123456789',
            'total' => 300,
            'payment_method' => 'cod',
            'status' => 'placed',
        ]);

        $sent = WhatsAppService::sendOrderNotificationToAdmin($order);
        $this->assertFalse($sent);

        // Verify a failure log was recorded
        $log = WaMessageLog::where('order_id', $order->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertStringContainsString('missing_config', $log->source);
    }

    /**
     * PHASE 2: Test notification uses explicitly provided phone and logs as test_admin.
     */
    public function test_test_notification_uses_explicit_recipient_and_logs_as_test_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        config(['services.whatsapp.fast2sms_auth_key' => '']);
        
        $res = $this->actingAs($admin)->postJson(route('admin.settings.plugin-test-wa'), [
            'admin_phone' => '9876543210',
        ]);

        // Phone is formatted and verified
        $this->assertEquals('919876543210', StoreSetting::normalizePhoneNumber('9876543210'));

        $log = WaMessageLog::where('source', 'test_admin')->latest()->first();
        if ($log) {
            $this->assertEquals('919876543210', $log->telephone);
            $this->assertEquals('test_admin', $log->source);
        }
    }

    /**
     * PHASE 3: Admin menu and drawer responsive markup is present without JS syntax errors.
     */
    public function test_admin_navigation_drawer_markup_and_scripts_present(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $res = $this->actingAs($admin)->get(route('admin.dashboard'));
        $res->assertStatus(200);

        $res->assertSee('adminDrawerToggle', false);
        $res->assertSee('adminMobileDrawer', false);
        $res->assertSee('adminDrawerBackdrop', false);
        $res->assertSee('window.toggleAdminDrawer', false);
        $res->assertSee('window.closeAdminDrawer', false);
    }

    /**
     * PHASE 4: Buy Now checkout displays address form when customer has no saved addresses.
     */
    public function test_buy_now_page_displays_address_form_when_customer_has_no_saved_addresses(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $res = $this->actingAs($customer, 'customer')->get(route('shop.buy-now', ['single' => 1]));
        $res->assertStatus(200);

        // Since customer has 0 addresses, new-address-block must be displayed (not display:none)
        $res->assertSee('id="new-address-block"', false);
        $res->assertSee('display:block;', false);
        $res->assertSee('Add Delivery Address', false);
    }

    /**
     * PHASE 4: Buy Now checkout lists saved addresses as selectable options with "Add a new address" button.
     */
    public function test_buy_now_page_lists_saved_addresses_when_customer_has_addresses(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $addr = CustomerAddress::create([
            'customer_id' => $customer->id,
            'full_name' => 'John Doe',
            'mobile_primary' => '9876543210',
            'address_line_1' => '123 Main St',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'pincode' => '641001',
            'type' => 'home',
            'is_default' => true,
        ]);

        $res = $this->actingAs($customer, 'customer')->get(route('shop.buy-now', ['single' => 1]));
        $res->assertStatus(200);

        $res->assertSee('saved-address-list', false);
        $res->assertSee('value="' . $addr->id . '"', false);
        $res->assertSee('btn-toggle-new', false);
        $res->assertSee('Add a new address', false);
    }

    /**
     * PHASE 5: Customer-facing address form contains NO State input field.
     */
    public function test_address_form_has_no_state_input_field(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $res = $this->actingAs($customer, 'customer')->get(route('shop.buy-now'));
        $res->assertStatus(200);

        // Must NOT have State input
        $res->assertDontSee('name="state"', false);
        $res->assertDontSee('id="bn-addr-state"', false);
    }

    /**
     * PHASE 5: State is safely derived from PIN code without requiring user input.
     */
    public function test_state_derived_from_pincode_in_customer_address_and_order_placement(): void
    {
        $this->assertEquals('Tamil Nadu', CustomerAddress::getStateFromPincode('600001'));
        $this->assertEquals('Karnataka', CustomerAddress::getStateFromPincode('560001'));
        $this->assertEquals('Maharashtra', CustomerAddress::getStateFromPincode('400001'));
        $this->assertEquals('Delhi', CustomerAddress::getStateFromPincode('110001'));
        $this->assertEquals('Kerala', CustomerAddress::getStateFromPincode('695001'));
        $this->assertEquals('Lakshadweep', CustomerAddress::getStateFromPincode('682001'));

        $customer = User::factory()->create(['is_admin' => false]);
        $product = Product::create([
            'name' => 'Cotton Saree',
            'slug' => 'cotton-saree-' . uniqid(),
            'price' => 1200,
            'stock_status' => 'in_stock',
            'qty' => 5,
            'status' => 1,
        ]);

        $payload = [
            'name' => 'Priya Raman',
            'mobile' => '9876543210',
            'email' => 'priya@example.com',
            'items' => json_encode([[
                'id' => $product->id,
                'name' => $product->name,
                'price' => 1200,
                'qty' => 1,
            ]]),
            'full_name' => 'Priya Raman',
            'mobile_primary' => '9876543210',
            'pincode' => '600028', // Chennai, Tamil Nadu
            'city' => 'Chennai',
            // Notice: NO state field submitted by the customer!
            'address_line_1' => 'No 45, Gandhi Road',
            'address_line_2' => 'Mylapore',
            'address_type' => 'home',
            'payment_method' => 'cod',
        ];

        $res = $this->actingAs($customer, 'customer')->post(route('shop.place-order'), $payload);
        $res->assertSessionHasNoErrors();

        // Verify CustomerAddress was created with derived state
        $createdAddr = CustomerAddress::where('customer_id', $customer->id)->first();
        $this->assertNotNull($createdAddr);
        $this->assertEquals('Tamil Nadu', $createdAddr->state);
        $this->assertEquals('600028', $createdAddr->pincode);
    }

    /**
     * PHASE 6: Site name is removed from the admin navbar header while preserving brand logo and Admin badge.
     */
    public function test_site_name_removed_from_admin_navbar_header(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        StoreSetting::setValue('store_name', 'Nool & Crop');
        StoreSetting::clearCache();

        $res = $this->actingAs($admin)->get(route('admin.dashboard'));
        $res->assertStatus(200);

        // Header brand container
        $content = $res->getContent();
        
        // Assert header brand has Admin badge
        $res->assertSee('admin-brand-badge', false);
        $res->assertSee('Admin</span>', false);

        // Assert <span class="admin-brand-text"> is NOT present in admin-brand-left
        $res->assertDontSee('<span class="admin-brand-text">', false);

        // Title should still contain the site name for browser tab accuracy
        $res->assertSee('Admin</title>', false);
        $this->assertTrue(str_contains($content, 'Nool & Crop Admin</title>') || str_contains($content, 'Nool &amp; Crop Admin</title>'));
    }
}
