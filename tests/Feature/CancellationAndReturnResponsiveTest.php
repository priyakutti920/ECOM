<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationAndReturnResponsiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::clearCache();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->customer = User::factory()->create(['is_admin' => false]);
    }

    /**
     * Test 1: Admin cancellations & returns page renders responsive scroll containers and CSS.
     */
    public function test_admin_cancellations_returns_page_renders_responsive_css_and_containers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index'));
        $response->assertStatus(200);

        // Responsive scroll container classes and scroll hint
        $response->assertSee('table-responsive', false);
        $response->assertSee('data-cancellations', false);
        $response->assertSee('data-returns', false);
        $response->assertSee('mobile-scroll-hint', false);

        // Responsive CSS media queries and overflow rules
        $response->assertSee('overflow-x: auto', false);
        $response->assertSee('-webkit-overflow-scrolling: touch', false);
        $response->assertSee('@media (max-width: 860px)', false);
        $response->assertSee('@media (max-width: 576px)', false);
        $response->assertSee('@media (max-width: 520px)', false);

        // Column class tags for minimum widths and alignment
        $response->assertSee('col-order', false);
        $response->assertSee('col-customer', false);
        $response->assertSee('col-date', false);
        $response->assertSee('col-reason', false);
        $response->assertSee('col-total', false);
        $response->assertSee('col-payment', false);
        $response->assertSee('col-action', false);
        $response->assertSee('col-product', false);
        $response->assertSee('col-status', false);
        $response->assertSee('col-manage', false);
    }

    /**
     * Test 2: Cancellations tab renders cancelled orders inside table-responsive.
     */
    public function test_cancellations_tab_renders_cancelled_orders_in_scroll_container(): void
    {
        $order = Order::create([
            'order_code' => 'NC-CANC-99',
            'customer_id' => $this->customer->id,
            'contact_name' => 'Jane Doe',
            'contact_mobile' => '9876543210',
            'contact_email' => 'jane@example.com',
            'addr_full_name' => 'Jane Doe',
            'addr_line_1' => '45 Beach Road',
            'addr_city' => 'Chennai',
            'addr_state' => 'Tamil Nadu',
            'addr_pincode' => '600002',
            'addr_mobile_primary' => '9876543210',
            'subtotal' => 1200.00,
            'discount' => 0.00,
            'total' => 1200.00,
            'payment_method' => 'upi',
            'payment_status' => 'paid',
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => 'Ordered wrong size and color',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index', ['tab' => 'cancellations']));
        $response->assertStatus(200);

        $response->assertSee('NC-CANC-99');
        $response->assertSee('Jane Doe');
        $response->assertSee('Ordered wrong size and color');
        $response->assertSee('table-responsive', false);
        $response->assertSee('data-cancellations', false);
        $response->assertSee(route('admin.orders.show', 'NC-CANC-99'));
    }

    /**
     * Test 3: Returns tab renders return requests with status and note forms inside table-responsive.
     */
    public function test_returns_tab_renders_return_requests_and_forms_in_scroll_container(): void
    {
        $product = Product::create([
            'name' => 'Silk Kanchipuram Saree',
            'slug' => 'silk-kanchipuram-saree-' . uniqid(),
            'price' => 2500,
            'stock_status' => 'in_stock',
            'qty' => 5,
            'status' => 1,
        ]);

        $order = Order::create([
            'order_code' => 'NC-RET-101',
            'customer_id' => $this->customer->id,
            'contact_name' => 'Priya Kumar',
            'contact_mobile' => '9876543211',
            'contact_email' => 'priya@example.com',
            'addr_full_name' => 'Priya Kumar',
            'addr_line_1' => '10 Temple Street',
            'addr_city' => 'Coimbatore',
            'addr_state' => 'Tamil Nadu',
            'addr_pincode' => '641001',
            'addr_mobile_primary' => '9876543211',
            'subtotal' => 2500.00,
            'discount' => 0.00,
            'total' => 2500.00,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'delivered',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 2500,
            'line_total' => 2500,
        ]);

        $orderReturn = OrderReturn::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'customer_id' => $this->customer->id,
            'status' => 'requested',
            'reason' => 'Quality not as expected',
            'description' => 'Color differs slightly from photo',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index', ['tab' => 'returns']));
        $response->assertStatus(200);

        $response->assertSee('Silk Kanchipuram Saree');
        $response->assertSee('NC-RET-101');
        $response->assertSee('Quality not as expected');
        $response->assertSee('status-form', false);
        $response->assertSee(route('admin.returns.status', $orderReturn->id));
        $response->assertSee(route('admin.returns.note', $orderReturn->id));
    }

    /**
     * Test 4: Customer order show page renders responsive cancel and return modal styles.
     */
    public function test_customer_order_show_page_renders_responsive_modal_and_return_styles(): void
    {
        $order = Order::create([
            'order_code' => 'NC-CUST-202',
            'customer_id' => $this->customer->id,
            'contact_name' => 'Priya Kumar',
            'contact_mobile' => '9876543211',
            'contact_email' => 'priya@example.com',
            'addr_full_name' => 'Priya Kumar',
            'addr_line_1' => '10 Temple Street',
            'addr_city' => 'Coimbatore',
            'addr_state' => 'Tamil Nadu',
            'addr_pincode' => '641001',
            'addr_mobile_primary' => '9876543211',
            'subtotal' => 999.00,
            'discount' => 0.00,
            'total' => 999.00,
            'payment_method' => 'upi',
            'payment_status' => 'paid',
            'status' => 'placed',
        ]);

        $response = $this->actingAs($this->customer, 'customer')->get(route('shop.orders.show', $order->order_code));
        $response->assertStatus(200);

        // Modal responsive scroll constraints
        $response->assertSee('max-height: calc(100vh - 32px)', false);
        $response->assertSee('overflow-y: auto', false);
        $response->assertSee('@media (max-width: 600px)', false);
        $response->assertSee('cancelModal', false);
        $response->assertSee('returnModal', false);
    }

    /**
     * Test 5: Validate responsive CSS rules coverage across all required viewport widths:
     * 320px, 360px, 375px, 390px, 414px, 768px, and Desktop.
     */
    public function test_viewport_rules_coverage_for_all_target_widths(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cancellations-returns.index'));
        $content = $response->getContent();

        // Target widths: 320px, 360px, 375px, 390px, 414px fall under mobile breakpoint <= 520px and <= 576px
        // 1. KPI grid single column rule:
        $this->assertStringContainsString('@media (max-width: 520px)', $content);
        $this->assertStringContainsString('grid-template-columns: 1fr', $content);

        // 2. Toolbar flex direction column and 100% width rule for mobile:
        $this->assertStringContainsString('@media (max-width: 576px)', $content);
        $this->assertStringContainsString('flex-direction: column', $content);

        // 3. Tablet breakpoint 768px covered by <= 860px (2 column KPIs) and <= 900px (scroll hint):
        $this->assertStringContainsString('@media (max-width: 860px)', $content);
        $this->assertStringContainsString('grid-template-columns: repeat(2, 1fr)', $content);
        $this->assertStringContainsString('@media (max-width: 900px)', $content);
        $this->assertStringContainsString('display: flex;', $content);

        // 4. Desktop (> 860px) default:
        $this->assertStringContainsString('grid-template-columns: repeat(3, 1fr)', $content);

        // 5. Table scroll container is unconditionally active for any viewport narrower than table min-width:
        $this->assertStringContainsString('min-width: 760px', $content);
        $this->assertStringContainsString('min-width: 920px', $content);
        $this->assertStringContainsString('overflow-x: auto', $content);
    }

    /**
     * Test 6: Strict WhatsApp protection verification.
     * Ensure WhatsApp routes, service class, and configuration remain completely untouched.
     */
    public function test_whatsapp_service_and_configuration_remain_protected(): void
    {
        // WhatsApp Service class and methods intact
        $this->assertTrue(class_exists(\App\Services\WhatsAppService::class));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'sendOrderNotificationToAdmin'));
        $this->assertTrue(method_exists(\App\Services\WhatsAppService::class, 'getAdminPhone'));

        // WhatsApp routes intact
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.settings.whatsapp'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.orders.send-whatsapp'));
    }
}
