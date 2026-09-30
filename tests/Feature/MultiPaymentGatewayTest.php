<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Payment\Drivers\CashfreeGateway;
use App\Services\Payment\Drivers\CodGateway;
use App\Services\Payment\Drivers\RazorpayGateway;
use App\Services\Payment\Drivers\UpiGateway;
use App\Services\Payment\PaymentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::setValue('store_name', 'Nool & Crop Test');

        // Seed basic gateways
        PaymentGateway::create([
            'name'         => 'Razorpay',
            'slug'         => 'razorpay',
            'is_active'    => true,
            'is_available' => true,
            'credentials'  => [
                'key_id'         => 'rzp_test_key123',
                'key_secret'     => 'rzp_test_secret456',
                'webhook_secret' => 'whsec_test_789',
            ],
        ]);

        PaymentGateway::create([
            'name'         => 'Cashfree',
            'slug'         => 'cashfree',
            'is_active'    => true,
            'is_available' => true,
            'credentials'  => [
                'app_id'     => 'cf_test_app123',
                'secret_key' => 'cf_test_secret456',
                'mode'       => 'sandbox',
            ],
        ]);

        PaymentGateway::create([
            'name'         => 'UPI',
            'slug'         => 'upi',
            'is_active'    => true,
            'is_available' => true,
            'credentials'  => [],
        ]);

        PaymentGateway::create([
            'name'         => 'Cash on Delivery',
            'slug'         => 'cod',
            'is_active'    => true,
            'is_available' => true,
            'credentials'  => [],
        ]);
    }

    public function test_payment_manager_resolves_all_four_drivers(): void
    {
        $manager = app(PaymentManager::class);

        $this->assertInstanceOf(RazorpayGateway::class, $manager->driver('razorpay'));
        $this->assertInstanceOf(CashfreeGateway::class, $manager->driver('cashfree'));
        $this->assertInstanceOf(UpiGateway::class, $manager->driver('upi'));
        $this->assertInstanceOf(CodGateway::class, $manager->driver('cod'));

        $active = $manager->getActiveGateways();
        $this->assertCount(4, $active);
    }

    public function test_razorpay_signature_verification(): void
    {
        $gateway = new RazorpayGateway();

        $data = [
            'razorpay_order_id'   => 'order_9A33XWu170gUtm',
            'razorpay_payment_id' => 'pay_29MoAMoGyEG0WD',
        ];

        // Known HMAC-SHA256 signature for key "rzp_test_secret456"
        $expectedSignature = hash_hmac('sha256', 'order_9A33XWu170gUtm|pay_29MoAMoGyEG0WD', 'rzp_test_secret456');

        $this->assertTrue($gateway->verifySignature($data, $expectedSignature));
        $this->assertFalse($gateway->verifySignature($data, 'invalid_tampered_signature'));
    }

    public function test_cashfree_signature_verification(): void
    {
        $gateway = new CashfreeGateway();

        $timestamp = '1690000000';
        $rawPayload = '{"data":{"order":{"order_id":"CF_ORD_1"}}}';
        $expectedSignature = base64_encode(hash_hmac('sha256', $timestamp . $rawPayload, 'cf_test_secret456', true));

        $this->assertTrue($gateway->verifySignature(
            ['raw_content' => $rawPayload, 'timestamp' => $timestamp],
            $expectedSignature
        ));

        $this->assertFalse($gateway->verifySignature(
            ['raw_content' => $rawPayload, 'timestamp' => $timestamp],
            'bogus_signature'
        ));
    }

    public function test_cod_gateway_initiates_immediately(): void
    {
        $cod = new CodGateway();

        $res = $cod->createOrder([
            'order_code' => 'ORD_COD_01',
            'amount'     => 450.00,
        ]);

        $this->assertTrue($res['success']);
        $this->assertNotNull($res['gateway_order_id']);
        $this->assertNull($res['payment_url']);
    }

    public function test_multi_gateway_webhook_routes_and_processes_idempotently(): void
    {
        $user = User::factory()->create();

        $order = Order::create([
            'customer_id'         => $user->id,
            'contact_name'        => 'Test Customer',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'Test Customer',
            'addr_line_1'         => '123 Main St',
            'addr_city'           => 'Chennai',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '600001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'order_code'          => 'ORD_WEBHOOK_01',
            'payment_order_id'    => 'RZP_ORDER_999',
            'payment_utr'         => 'pay_test_12345',
            'subtotal'            => 1200,
            'total'               => 1200,
            'payment_method'      => 'razorpay',
            'payment_status'      => 'paid',
            'status'              => 'placed',
        ]);

        $payload = [
            'order_id'   => 'RZP_ORDER_999',
            'payment_id' => 'pay_test_12345',
            'status'     => 'SUCCESS',
            'amount'     => 1200.00,
        ];

        // First call returns 200 Order already processed
        $res1 = $this->postJson('/api/payment/webhook/razorpay', $payload);
        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);

        // Duplicate call returns 200 without creating new order
        $res2 = $this->postJson('/api/payment/webhook/razorpay', $payload);
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true]);

        $this->assertEquals(1, Order::where('payment_order_id', 'RZP_ORDER_999')->count());
    }
}
