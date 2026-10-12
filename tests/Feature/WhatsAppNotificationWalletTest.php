<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\UpiPaymentService;
use App\Services\WaWalletService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNotificationWalletTest extends TestCase
{
    use RefreshDatabase;

    private function createTestOrder(array $attributes = []): Order
    {
        $customer = User::factory()->create();

        return Order::create(array_merge([
            'order_code'          => 'ORD' . strtoupper(substr(uniqid(), -6)),
            'customer_id'         => $customer->id,
            'contact_name'        => 'Test Customer',
            'contact_mobile'      => '9876543210',
            'contact_email'       => 'customer@example.com',
            'addr_full_name'      => 'Test Customer',
            'addr_line_1'         => '100 Main Street',
            'addr_city'           => 'Sivakasi',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '626123',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 1200.00,
            'discount'            => 0.00,
            'shipping'            => 0.00,
            'tax_amount'          => 0.00,
            'total'               => 1200.00,
            'payment_method'      => 'cod',
            'payment_status'      => 'pending',
            'status'              => 'placed',
        ], $attributes));
    }

    public function test_upi_payment_service_uses_separate_key_for_whatsapp_from_env_or_config(): void
    {
        // 1. When services.whatsapp.upi_key is configured
        config(['services.whatsapp.upi_key' => 'wa_custom_upi_secret_999']);
        config(['services.whatsapp.upi_create_order_url' => 'https://api.upigateway.test/wa/create']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.upigateway.test/wa/status']);

        $service = UpiPaymentService::forWhatsApp();

        $this->assertEquals('wa_custom_upi_secret_999', $service->getApiKey());
        $this->assertEquals('https://api.upigateway.test/wa/create', $service->getCreateOrderUrl());
        $this->assertEquals('https://api.upigateway.test/wa/status', $service->getStatusCheckUrl());
        $this->assertTrue($service->isConfigured());
    }

    public function test_admin_can_add_amount_directly_to_whatsapp_wallet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        $initialBalance = WaWalletService::getBalance();

        $res = $this->postJson(route('admin.settings.whatsapp-add-amount'), [
            'amount' => 150,
            'mode'   => 'direct',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'      => true,
            'requires_upi' => false,
        ]);

        $this->assertEquals($initialBalance + 150.00, WaWalletService::getBalance());
    }

    public function test_admin_can_initiate_upi_order_for_whatsapp_wallet_add_amount(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        // Configure separate WA UPI credentials
        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_create_order_url' => 'https://api.test/upi/create']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        Http::fake([
            'https://api.test/upi/create' => Http::response([
                'status'  => true,
                'message' => 'UPI Order Created',
                'result'  => [
                    'orderId'     => 'WA_TOPUP_TEST_001',
                    'payment_url' => 'https://merchant.upigateway.com/pay/WA_TOPUP_TEST_001',
                ],
            ], 200),
        ]);

        $res = $this->postJson(route('admin.settings.whatsapp-add-amount'), [
            'amount' => 200,
            'mode'   => 'upi',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'      => true,
            'requires_upi' => true,
            'payment_url'  => 'https://merchant.upigateway.com/pay/WA_TOPUP_TEST_001',
        ]);
    }

    public function test_admin_can_verify_upi_payment_and_credit_whatsapp_wallet(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        Http::fake([
            'https://api.test/upi/status' => Http::response([
                'status' => 'COMPLETED',
                'result' => [
                    'txnStatus' => 'COMPLETED',
                    'amount'    => 250,
                    'utr'       => 'UTR123456789',
                ],
            ], 200),
        ]);

        $initialBalance = WaWalletService::getBalance();

        $res = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => 'WA_TOPUP_TEST_001',
            'amount'   => 250,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'is_paid' => true,
        ]);

        $this->assertEquals($initialBalance + 250.00, WaWalletService::getBalance());
    }

    public function test_whatsapp_notification_message_includes_and_formats_amount(): void
    {
        $order = $this->createTestOrder([
            'order_code' => 'ORD-WA-999',
            'subtotal'   => 1200.00,
            'discount'   => 100.00,
            'shipping'   => 50.00,
            'tax_amount' => 50.00,
            'total'      => 1200.00,
        ]);

        $message = app(WhatsAppService::class)->buildOrderMessage($order);

        $this->assertStringContainsString('Total Rs:* ₹1,200.00', $message);
        $this->assertStringContainsString('Final Value: ₹1,200.00', $message);
    }

    public function test_order_placement_deducts_wallet_balance_two_messages_one_rupee(): void
    {
        // 1. Give wallet initial balance of 10.00
        WaWalletService::addFunds(10.00, 'Seed balance');
        $this->assertEquals(10.00, WaWalletService::getBalance());

        // 2. Enable customer notification
        config(['services.whatsapp.customer_notify_enabled' => true]);
        config(['services.whatsapp.fast2sms_auth_key' => 'test_auth_key']);
        config(['services.whatsapp.phone_number_id' => 'test_phone_id']);
        config(['services.whatsapp.admin_phone' => '919876543210']);

        $order = $this->createTestOrder([
            'order_code'            => 'ORD-WA-2MSG',
            'total'                 => 500.00,
            'contact_mobile'        => '9876543210',
            'addr_mobile_primary'   => '9876543210',
            'contact_name'          => 'Test Customer',
        ]);

        // Dispatch automated notifications (1 admin + 1 customer)
        $res = app(WhatsAppService::class)->sendAutomatedNotification($order);

        // 2 messages = 1 Rupee deduction (2 x 0.50 = 1.00)
        $this->assertEquals(9.00, WaWalletService::getBalance(), 'Two WhatsApp messages must deduct exactly ₹1.00 from wallet');
    }

    public function test_single_whatsapp_message_deducts_fifty_paise(): void
    {
        WaWalletService::addFunds(5.00, 'Seed balance');
        $startBalance = WaWalletService::getBalance();

        config(['services.whatsapp.fast2sms_auth_key' => 'test_auth_key']);
        config(['services.whatsapp.phone_number_id' => 'test_phone_id']);

        WhatsAppService::sendToCustomer(
            '9876543210',
            'Test Customer',
            'ORD-TEST-01',
            'Rs. 100.00',
            'Test Store'
        );

        // Deducts 0.50 (50 paise)
        $this->assertEquals($startBalance - 0.50, WaWalletService::getBalance(), 'Per message cost must be ₹0.50 (50 paise)');
    }
}
