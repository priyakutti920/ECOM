<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreSetting;
use App\Models\User;
use App\Models\WaTransaction;
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
        config(['services.whatsapp.upi_key' => 'wa_custom_upi_secret_999']);
        config(['services.whatsapp.upi_create_order_url' => 'https://api.upigateway.test/wa/create']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.upigateway.test/wa/status']);

        $service = UpiPaymentService::forWhatsApp();

        $this->assertEquals('wa_custom_upi_secret_999', $service->getApiKey());
        $this->assertEquals('https://api.upigateway.test/wa/create', $service->getCreateOrderUrl());
        $this->assertEquals('https://api.upigateway.test/wa/status', $service->getStatusCheckUrl());
        $this->assertTrue($service->isConfigured());
    }

    /**
     * Requirement 1 & 10: Unauthorized direct-credit requests are strictly rejected.
     */
    public function test_direct_credit_actions_and_manual_topup_are_strictly_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        $initialBalance = WaWalletService::getBalance();

        // 1. Direct mode in add-amount is rejected with 403
        $res = $this->postJson(route('admin.settings.whatsapp-add-amount'), [
            'amount' => 150,
            'mode'   => 'direct',
        ]);
        $res->assertStatus(403);
        $res->assertJson([
            'success' => false,
        ]);
        $this->assertEquals($initialBalance, WaWalletService::getBalance());

        // 2. Manual credit topup via plugin wallet-topup is rejected with 403
        $res2 = $this->postJson(route('admin.settings.plugin-wallet-topup'), [
            'action' => 'credit',
            'amount' => 500,
        ]);
        $res2->assertStatus(403);
        $res2->assertJson([
            'success' => false,
        ]);
        $this->assertEquals($initialBalance, WaWalletService::getBalance());

        // 3. Unauthenticated guest cannot initiate recharge
        auth()->logout();
        $resGuest = $this->postJson(route('admin.settings.whatsapp-add-amount'), [
            'amount' => 100,
            'mode'   => 'upi',
        ]);
        $this->assertTrue(in_array($resGuest->status(), [401, 302]));
        $this->assertEquals($initialBalance, WaWalletService::getBalance());
    }

    /**
     * Requirement 2 & 5: Initiating UPI creates a pending transaction without adding credits.
     */
    public function test_admin_can_initiate_upi_order_creates_pending_transaction_without_crediting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_create_order_url' => 'https://api.test/upi/create']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        Http::fake([
            'https://api.test/upi/create' => Http::response([
                'status'  => true,
                'message' => 'UPI Order Created',
                'result'  => [
                    'orderId'     => 'WA_TOPUP_INIT_001',
                    'payment_url' => 'https://merchant.upigateway.com/pay/WA_TOPUP_INIT_001',
                ],
            ], 200),
        ]);

        $initialBalance = WaWalletService::getBalance();

        $res = $this->postJson(route('admin.settings.whatsapp-add-amount'), [
            'amount' => 200,
            'mode'   => 'upi',
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'          => true,
            'requires_upi'     => true,
            'payment_url'      => 'https://merchant.upigateway.com/pay/WA_TOPUP_INIT_001',
            'expected_credits' => 400, // 200 / 0.50 = 400 messages
        ]);

        // Available wallet balance must NOT be increased for pending order!
        $this->assertEquals($initialBalance, WaWalletService::getBalance());

        // Pending transaction exists in DB
        $pendingTxn = WaTransaction::where('payment_reference', $res->json('order_id'))->first();
        $this->assertNotNull($pendingTxn);
        $this->assertEquals('pending', $pendingTxn->status);
        $this->assertEquals(200.00, $pendingTxn->amount);
    }

    /**
     * Requirement 1, 8 & 11: Successful verified UPI payment credits wallet idempotently.
     */
    public function test_admin_can_verify_upi_payment_and_credit_whatsapp_wallet_idempotently(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        $orderId = 'WA_TOPUP_TEST_001';

        // Pre-create pending order created by backend
        WaTransaction::create([
            'type'              => 'credit',
            'amount'            => 250.00,
            'description'       => "WhatsApp wallet top-up via UPI (#{$orderId})",
            'payment_gateway'   => 'upi',
            'payment_reference' => $orderId,
            'status'            => 'pending',
            'related_type'      => 'topup',
            'related_invoice'   => $orderId,
        ]);

        Http::fake([
            'https://api.test/upi/status' => Http::response([
                'status' => 'COMPLETED',
                'result' => [
                    'txnStatus' => 'COMPLETED',
                    'amount'    => 250.00,
                    'utr'       => 'UTR123456789',
                ],
            ], 200),
        ]);

        $initialBalance = WaWalletService::getBalance();

        // First verification call
        $res = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => $orderId,
            'amount'   => 250,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => true,
            'is_paid' => true,
        ]);

        $this->assertEquals($initialBalance + 250.00, WaWalletService::getBalance());

        // Second verification call (duplicate / retry) -> MUST NOT duplicate credits
        $res2 = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => $orderId,
            'amount'   => 250,
        ]);

        $res2->assertStatus(200);
        $res2->assertJson([
            'success'          => true,
            'is_paid'          => true,
            'already_credited' => true,
        ]);

        $this->assertEquals($initialBalance + 250.00, WaWalletService::getBalance(), 'Duplicate verify calls must never increase balance twice');
    }

    /**
     * Requirement 2 & 4: Failed or cancelled UPI payment adds no credits.
     */
    public function test_failed_or_cancelled_payment_adds_no_credits(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        $orderId = 'WA_TOPUP_FAILED_001';

        WaTransaction::create([
            'type'              => 'credit',
            'amount'            => 100.00,
            'description'       => "WhatsApp wallet top-up via UPI (#{$orderId})",
            'payment_gateway'   => 'upi',
            'payment_reference' => $orderId,
            'status'            => 'pending',
            'related_type'      => 'topup',
            'related_invoice'   => $orderId,
        ]);

        Http::fake([
            'https://api.test/upi/status' => Http::response([
                'status' => 'FAILED',
                'result' => [
                    'txnStatus' => 'FAILED',
                    'amount'    => 100.00,
                ],
            ], 200),
        ]);

        $initialBalance = WaWalletService::getBalance();

        $res = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => $orderId,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'   => false,
            'is_paid'   => false,
            'is_failed' => true,
        ]);

        $this->assertEquals($initialBalance, WaWalletService::getBalance());

        $txn = WaTransaction::where('payment_reference', $orderId)->first();
        $this->assertEquals('failed', $txn->status);
    }

    /**
     * Requirement 3 & 4: Pending payment adds no credits.
     */
    public function test_pending_payment_adds_no_credits(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        $orderId = 'WA_TOPUP_PENDING_001';

        WaTransaction::create([
            'type'              => 'credit',
            'amount'            => 100.00,
            'description'       => "WhatsApp wallet top-up via UPI (#{$orderId})",
            'payment_gateway'   => 'upi',
            'payment_reference' => $orderId,
            'status'            => 'pending',
            'related_type'      => 'topup',
            'related_invoice'   => $orderId,
        ]);

        Http::fake([
            'https://api.test/upi/status' => Http::response([
                'status' => 'PENDING',
                'result' => [
                    'txnStatus' => 'PENDING',
                    'amount'    => 100.00,
                ],
            ], 200),
        ]);

        $initialBalance = WaWalletService::getBalance();

        $res = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => $orderId,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'    => false,
            'is_paid'    => false,
            'is_pending' => true,
        ]);

        $this->assertEquals($initialBalance, WaWalletService::getBalance());
    }

    /**
     * Requirement 5 & 6: Fake callback or unregistered order ID adds no credits.
     */
    public function test_unregistered_order_id_verification_adds_no_credits(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        $initialBalance = WaWalletService::getBalance();

        // Call verify on an order ID that never originated in our DB
        $res = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => 'FAKE_ORDER_ID_999999',
            'amount'   => 500,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => false,
            'is_paid' => false,
        ]);

        $this->assertEquals($initialBalance, WaWalletService::getBalance());
    }

    /**
     * Requirement 9: Amount mismatch between expected order and verified payment adds no credits.
     */
    public function test_payment_amount_mismatch_rejects_credit_addition(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        $orderId = 'WA_TOPUP_MISMATCH_001';

        // Order was for ₹500
        WaTransaction::create([
            'type'              => 'credit',
            'amount'            => 500.00,
            'description'       => "WhatsApp wallet top-up via UPI (#{$orderId})",
            'payment_gateway'   => 'upi',
            'payment_reference' => $orderId,
            'status'            => 'pending',
            'related_type'      => 'topup',
            'related_invoice'   => $orderId,
        ]);

        // Gateway reports user only paid ₹50
        Http::fake([
            'https://api.test/upi/status' => Http::response([
                'status' => 'COMPLETED',
                'result' => [
                    'txnStatus' => 'COMPLETED',
                    'amount'    => 50.00,
                ],
            ], 200),
        ]);

        $initialBalance = WaWalletService::getBalance();

        $res = $this->postJson(route('admin.settings.whatsapp-verify-upi'), [
            'order_id' => $orderId,
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success'   => false,
            'is_paid'   => false,
            'is_failed' => true,
        ]);

        $this->assertEquals($initialBalance, WaWalletService::getBalance(), 'Amount mismatch must never credit wallet');

        $txn = WaTransaction::where('payment_reference', $orderId)->first();
        $this->assertEquals('failed', $txn->status);
    }

    /**
     * Requirement 12: Rate calculation: ₹0.50 per notification (1 Rupee = 2 notifications).
     */
    public function test_custom_recharge_amounts_and_rates_calculate_correctly(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        config(['services.whatsapp.upi_key' => 'wa_merchant_key_123']);
        config(['services.whatsapp.upi_create_order_url' => 'https://api.test/upi/create']);
        config(['services.whatsapp.upi_status_check_url' => 'https://api.test/upi/status']);

        Http::fake([
            'https://api.test/upi/create' => Http::response([
                'status'  => true,
                'message' => 'UPI Order Created',
                'result'  => [
                    'orderId'     => 'WA_TOPUP_RATE_TEST',
                    'payment_url' => 'https://merchant.upigateway.com/pay/test',
                ],
            ], 200),
        ]);

        // Check rates for standard presets
        $presets = [
            50   => 100,
            100  => 200,
            200  => 400,
            500  => 1000,
            1000 => 2000,
        ];

        foreach ($presets as $amount => $expectedMessages) {
            $res = $this->postJson(route('admin.settings.whatsapp-add-amount'), [
                'amount' => $amount,
                'mode'   => 'upi',
            ]);

            $res->assertStatus(200);
            $this->assertEquals($expectedMessages, $res->json('expected_credits'));
        }
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
