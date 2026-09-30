<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\CustomerAddress;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAuditAndBugFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_cancel_with_restock_cancels_invoice_by_order_id_and_updates_payment_status()
    {
        $customer = User::factory()->create();
        $product = Product::create([
            'name' => 'Cotton Tee',
            'slug' => 'cotton-tee',
            'price' => 500,
            'qty' => 10,
            'manage_inventory' => 1,
            'status' => 1,
        ]);

        $order = Order::create([
            'order_code' => 'NS-TEST01',
            'customer_id' => $customer->id,
            'contact_name' => 'John Doe',
            'contact_mobile' => '9876543210',
            'addr_full_name' => 'John Doe',
            'addr_line_1' => '123 Main St',
            'addr_city' => 'Delhi',
            'addr_state' => 'Delhi',
            'addr_pincode' => '110001',
            'addr_mobile_primary' => '9876543210',
            'subtotal' => 500,
            'total' => 500,
            'payment_status' => 'pending',
            'status' => Order::STATUS_PLACED,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 250,
            'line_total' => 500,
        ]);

        // Invoice linked by order_id
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-2026-0001',
            'customer_name' => 'John Doe',
            'subtotal' => 500,
            'total_amount' => 500,
            'status' => 0, // Pending
        ]);

        $this->assertEquals(0, $invoice->status);
        $this->assertEquals('pending', $order->payment_status);

        // Cancel order
        $order->cancelWithRestock('Customer requested', $customer->id, 'John Doe');

        $order->refresh();
        $invoice->refresh();

        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        $this->assertEquals('cancelled', $order->payment_status);
        $this->assertEquals(2, $invoice->status, 'Invoice status should be set to 2 (cancelled)');
        $this->assertTrue($order->invoices()->exists());
    }

    public function test_customer_can_cancel_placed_or_confirmed_order_before_shipping()
    {
        $customer = User::factory()->create();

        $order = Order::create([
            'order_code' => 'NS-CANCELME',
            'customer_id' => $customer->id,
            'contact_name' => 'Alice',
            'contact_mobile' => '9876543210',
            'addr_full_name' => 'Alice',
            'addr_line_1' => '456 Elm St',
            'addr_city' => 'Mumbai',
            'addr_state' => 'Maharashtra',
            'addr_pincode' => '400001',
            'addr_mobile_primary' => '9876543210',
            'subtotal' => 300,
            'total' => 300,
            'payment_status' => 'pending',
            'status' => Order::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->post(route('shop.orders.cancel', $order->order_code), [
                'reason' => 'Ordered by mistake',
            ]);

        $response->assertRedirect(route('shop.orders.show', $order->order_code));
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_admin_settings_features_save_updates_all_fields_without_corruption()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.settings.features-save'), [
                'titles' => ['Fast Shipping', '24/7 Helpline', 'Quick Returns', '100% Secure'],
                'descs'  => ['Over Rs 499', 'Always available', '7 days policy', 'Cards & UPI'],
                'icons'  => ['las la-shipping-fast', 'las la-headset', 'las la-sync-alt', 'las la-shield-alt'],
                'enabled' => ['1', '1', '1', '1'],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $features = StoreSetting::getFeatures();
        $this->assertCount(4, $features);
        $this->assertEquals('Fast Shipping', $features[0]['title']);
        $this->assertEquals('Over Rs 499', $features[0]['desc']);
    }

    public function test_buy_now_page_renders_with_array_gateways()
    {
        $customer = User::factory()->create(['is_admin' => false]);

        PaymentGateway::create([
            'name' => 'Cash on Delivery',
            'slug' => 'cod',
            'method_name' => 'Cash on Delivery',
            'is_active' => 1,
            'is_available' => 1,
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->get(route('shop.buy-now'));
        $response->assertOk();
        $response->assertSee('Payment Method');
    }
}
