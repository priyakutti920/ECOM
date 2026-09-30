<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Shipping\CourierServiceInterface;
use App\Services\Shipping\ShiprocketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CourierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::setValue('store_name', 'Nool & Crop Test');

        config([
            'services.shiprocket.email'           => 'test@shiprocket.in',
            'services.shiprocket.password'        => 'password123',
            'services.shiprocket.pickup_location' => 'Primary',
            'services.shiprocket.pickup_pincode'  => '641001',
            'services.shiprocket.webhook_token'   => 'shiprocket_secret_token_123',
        ]);

        $this->customer = User::factory()->create();

        $this->order = Order::create([
            'customer_id'         => $this->customer->id,
            'order_code'          => 'ORD-SHIP-01',
            'contact_name'        => 'Priya Kutti',
            'contact_mobile'      => '9876543210',
            'contact_email'       => 'priya@example.com',
            'addr_full_name'      => 'Priya Kutti',
            'addr_line_1'         => '100 Cross Cut Road',
            'addr_city'           => 'Coimbatore',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '641012',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 1500,
            'total'               => 1500,
            'payment_method'      => 'cod',
            'payment_status'      => 'pending',
            'status'              => 'placed',
        ]);

        $product = Product::create([
            'name'                => 'Linen Saree',
            'slug'                => 'linen-saree',
            'sku'                 => 'SAR-001',
            'price'               => 1500.00,
            'qty'                 => 10,
            'low_stock_threshold' => 2,
            'stock_status'        => 'in_stock',
            'is_active'           => true,
        ]);

        OrderItem::create([
            'order_id'       => $this->order->id,
            'product_id'     => $product->id,
            'product_name'   => $product->name,
            'sku'            => $product->sku,
            'quantity'       => 1,
            'unit_price'     => 1500.00,
            'line_total'     => 1500.00,
        ]);

        $this->order->load('items');
    }

    public function test_shiprocket_creates_shipment_with_mocked_api(): void
    {
        Http::fake([
            'https://apiv2.shiprocket.in/v1/external/auth/login' => Http::response([
                'token' => 'mock_jwt_token_xyz',
            ], 200),

            'https://apiv2.shiprocket.in/v1/external/orders/create/adhoc' => Http::response([
                'order_id'    => 10001,
                'shipment_id' => 99001,
                'status'      => 'NEW',
            ], 200),
        ]);

        $service = app(CourierServiceInterface::class);
        $res = $service->createShipment($this->order);

        $this->assertTrue($res['success']);
        $this->assertEquals('99001', $res['shipment_id']);
        $this->assertEquals('99001', $this->order->fresh()->shipment_id);
    }

    public function test_shiprocket_generates_awb_and_updates_order_state(): void
    {
        $this->order->update(['shipment_id' => '99001']);

        Http::fake([
            'https://apiv2.shiprocket.in/v1/external/auth/login' => Http::response([
                'token' => 'mock_jwt_token_xyz',
            ], 200),

            'https://apiv2.shiprocket.in/v1/external/courier/assign/awb' => Http::response([
                'response' => [
                    'data' => [
                        'awb_code'           => 'AWB987654321',
                        'courier_name'       => 'Delhivery Surface',
                        'courier_company_id' => 12,
                    ]
                ]
            ], 200),
        ]);

        $service = app(CourierServiceInterface::class);
        $res = $service->generateAwb($this->order);

        $this->assertTrue($res['success']);
        $this->assertEquals('AWB987654321', $res['awb_code']);

        $fresh = $this->order->fresh();
        $this->assertEquals('AWB987654321', $fresh->awb_code);
        $this->assertEquals('AWB987654321', $fresh->tracking_number);
        $this->assertEquals('Delhivery Surface', $fresh->courier_name);
        $this->assertEquals('dispatched', $fresh->status);
    }

    public function test_shiprocket_label_download_and_caching(): void
    {
        $this->order->update(['shipment_id' => '99001']);

        Http::fake([
            'https://apiv2.shiprocket.in/v1/external/auth/login' => Http::response([
                'token' => 'mock_jwt_token_xyz',
            ], 200),

            'https://apiv2.shiprocket.in/v1/external/courier/generate/label' => Http::response([
                'label_url' => 'https://shiprocket.co/labels/99001.pdf',
            ], 200),
        ]);

        $service = app(CourierServiceInterface::class);
        $res = $service->getShippingLabel($this->order);

        $this->assertTrue($res['success']);
        $this->assertEquals('https://shiprocket.co/labels/99001.pdf', $res['label_url']);
        $this->assertEquals('https://shiprocket.co/labels/99001.pdf', $this->order->fresh()->shipping_label_url);
    }

    public function test_courier_webhook_updates_order_lifecycle(): void
    {
        $this->order->update([
            'shipment_id' => '99001',
            'awb_code'    => 'AWB987654321',
            'status'      => 'shipped',
        ]);

        $webhookPayload = [
            'awb'            => 'AWB987654321',
            'order_id'       => $this->order->order_code,
            'current_status' => 'DELIVERED',
        ];

        $headers = [
            'x-api-key' => 'shiprocket_secret_token_123',
        ];

        // Call the public webhook route
        $res = $this->postJson('/api/courier/webhook', $webhookPayload, $headers);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $fresh = $this->order->fresh();
        $this->assertEquals('delivered', $fresh->status);
        // COD order auto-marked paid on delivery
        $this->assertEquals('paid', $fresh->payment_status);
        $this->assertNotNull($fresh->paid_at);
    }
}
