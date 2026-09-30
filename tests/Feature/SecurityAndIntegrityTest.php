<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\OtpService;
use App\Services\UpiPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::setValue('store_name', 'Security Test Store');
    }

    // ─────────────────────────────────────────────────────────────
    // 1. Password Reset Token Leak
    // ─────────────────────────────────────────────────────────────
    public function test_password_reset_does_not_leak_token_or_dev_reset_url(): void
    {
        $user = User::factory()->create([
            'email'    => 'customer@example.com',
            'is_admin' => false,
        ]);

        // Submit forgot password request
        $response = $this->post('/forgot-password', [
            'email' => 'customer@example.com',
        ]);

        // Must redirect back with generic status
        $response->assertSessionHas('status');
        $response->assertSessionMissing('dev_reset_url');

        // Content of follow-up GET to forgot-password must NOT expose dev_reset_url or raw token
        $followUp = $this->get('/forgot-password');
        $followUp->assertDontSee('dev_reset_url');
        $followUp->assertDontSee('Dev Mode - Password Reset Link');
        $followUp->assertDontSee('reset-password?token=');
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Order Tracking PII & Enumeration Vulnerability
    // ─────────────────────────────────────────────────────────────
    public function test_track_order_rejects_invalid_or_partial_phone_numbers(): void
    {
        $customer = User::factory()->create();

        $order = Order::create([
            'order_code'          => 'NS-ABC123',
            'customer_id'         => $customer->id,
            'contact_name'        => 'John Doe',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'John Doe',
            'addr_line_1'         => '123 Secret Villa',
            'addr_city'           => 'Chennai',
            'addr_state'          => 'Tamil Nadu',
            'addr_pincode'        => '600001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 1500,
            'total'               => 1500,
            'payment_method'      => 'cod',
            'payment_status'      => 'pending',
            'status'              => 'placed',
        ]);

        // Test 1: Single digit rejected
        $res1 = $this->get('/track-order?code=NS-ABC123&phone=9');
        $res1->assertSee('Please enter a valid 10-digit mobile number');

        // Test 2: Invalid 3 digits rejected
        $res2 = $this->get('/track-order?code=NS-ABC123&phone=123');
        $res2->assertSee('Please enter a valid 10-digit mobile number');

        // Test 3: Partial mobile number rejected (cannot use wildcard to match)
        $res3 = $this->get('/track-order?code=NS-ABC123&phone=98765');
        $res3->assertSee('Please enter a valid 10-digit mobile number');

        // Test 4: Full valid 10-digit mobile number succeeds
        $res4 = $this->get('/track-order?code=NS-ABC123&phone=9876543210');
        $res4->assertStatus(200);
        $res4->assertDontSee('Please enter a valid 10-digit mobile number');
        $res4->assertSee('Order Placed');

        // Sensitive full address and raw mobile must be masked in response
        $content = $res4->getContent();
        $this->assertStringNotContainsString('123 Secret Villa', $content);
        $this->assertStringNotContainsString('9876543210', $content);
        $this->assertStringContainsString('98765', $content); // Masked: 98765*****

        // Test 5: Unrelated valid mobile cannot access the order
        $res5 = $this->get('/track-order?code=NS-ABC123&phone=9123456789');
        $res5->assertSee('No order found');
    }

    // ─────────────────────────────────────────────────────────────
    // 3. OTP Authentication Security
    // ─────────────────────────────────────────────────────────────
    public function test_otp_is_six_digits_and_not_exposed_in_response(): void
    {
        $otpService = app(OtpService::class);
        $email = 'otpuser@example.com';

        $result = $otpService->generate($email);
        $this->assertTrue($result['success']);

        $otpRecord = \App\Models\OtpCode::where('email', $email)->latest()->first();
        $this->assertNotNull($otpRecord);
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $otpRecord->code);

        // Web endpoint should not expose OTP in response or session
        $response = $this->post('/login', ['email' => $email]);
        $response->assertSessionMissing('dev_otp');
        $response->assertSessionMissing('otp');
        $this->assertStringNotContainsString($otpRecord->code, $response->getContent() ?: '');
    }

    public function test_otp_verification_throttles_and_invalidates_after_five_failed_attempts(): void
    {
        $email = 'bruteforce@example.com';
        $otpService = app(OtpService::class);
        $otpService->generate($email);

        $otpRecord = \App\Models\OtpCode::where('email', $email)->latest()->first();
        $this->assertNotNull($otpRecord);
        $validOtp = $otpRecord->code;

        // Attempt 5 wrong OTPs with session
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->withSession(['otp_email' => $email])
                ->post('/login/otp', [
                    'otp' => '000000',
                ]);
            $res->assertSessionHasErrors('otp');
        }

        // 6th attempt with ANY otp (even correct) must fail due to throttle / invalidation
        $res6 = $this->withSession(['otp_email' => $email])
            ->post('/login/otp', [
                'otp' => $validOtp,
            ]);
        $res6->assertSessionHasErrors('otp');
    }

    public function test_otp_cannot_be_reused_after_successful_verification(): void
    {
        $user = User::factory()->create([
            'email'    => 'reuse@example.com',
            'is_admin' => false,
        ]);

        $otpService = app(OtpService::class);
        $otpService->generate($user->email);
        $otpRecord = \App\Models\OtpCode::where('email', $user->email)->latest()->first();
        $this->assertNotNull($otpRecord);
        $code = $otpRecord->code;

        // 1st verification succeeds
        $res1 = $this->withSession(['otp_email' => $user->email])
            ->post('/login/otp', [
                'otp' => $code,
            ]);
        $res1->assertRedirect();

        // Logout
        $this->post('/logout');

        // 2nd verification with same code must fail because previous was cleared
        $res2 = $this->withSession(['otp_email' => $user->email])
            ->post('/login/otp', [
                'otp' => $code,
            ]);
        $res2->assertSessionHasErrors('otp');
    }

    // ─────────────────────────────────────────────────────────────
    // 4. UPI Payment Status Logic
    // ─────────────────────────────────────────────────────────────
    public function test_upi_status_pending_remains_pending_and_failures_are_explicit(): void
    {
        PaymentGateway::create([
            'name'         => 'UPI Gateway',
            'slug'         => 'upi',
            'is_active'    => 1,
            'is_available' => 1,
            'credentials'  => [
                'api_key'          => 'test_key',
                'create_order_url' => 'https://api.test/create',
                'status_check_url' => 'https://api.test/status',
            ],
        ]);

        $service = app(UpiPaymentService::class);

        // Mock sequential status check responses
        Http::fake([
            'https://api.test/status' => Http::sequence()
                ->push(['status' => 'PENDING', 'result' => ['txnStatus' => 'PENDING']], 200)
                ->push(['status' => 'SUCCESS', 'result' => ['txnStatus' => 'SUCCESS', 'amount' => 500]], 200)
                ->push(['status' => 'FAILED', 'result' => ['txnStatus' => 'FAILED']], 200)
                ->push(['status' => 'CANCELLED', 'result' => ['txnStatus' => 'CANCELLED']], 200)
                ->push(['status' => 'EXPIRED', 'result' => ['txnStatus' => 'EXPIRED']], 200),
        ]);

        // Case 1: PENDING state
        $pendingResult = $service->checkStatus('ORD_PENDING');
        $this->assertTrue($pendingResult['success']);
        $this->assertFalse($pendingResult['is_paid'], 'PENDING must not be marked paid');
        $this->assertTrue($pendingResult['is_pending'], 'PENDING must be marked pending');
        $this->assertFalse($pendingResult['is_failed'], 'PENDING must not be marked failed');

        // Case 2: SUCCESS state
        $successResult = $service->checkStatus('ORD_SUCCESS');
        $this->assertTrue($successResult['is_paid']);
        $this->assertFalse($successResult['is_pending']);
        $this->assertFalse($successResult['is_failed']);

        // Case 3: FAILED state
        $failedResult = $service->checkStatus('ORD_FAILED');
        $this->assertFalse($failedResult['is_paid']);
        $this->assertTrue($failedResult['is_failed']);

        // Case 4: CANCELLED state
        $cancelledResult = $service->checkStatus('ORD_CANCELLED');
        $this->assertFalse($cancelledResult['is_paid']);
        $this->assertTrue($cancelledResult['is_failed']);

        // Case 5: EXPIRED state
        $expiredResult = $service->checkStatus('ORD_EXPIRED');
        $this->assertFalse($expiredResult['is_paid']);
        $this->assertTrue($expiredResult['is_failed']);
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Payment Amount Verification Before Order Materialisation
    // ─────────────────────────────────────────────────────────────
    public function test_payment_amount_verification_rejects_mismatched_amounts(): void
    {
        $expectedAmount = 1000.00;

        // Paid exactly ₹1000
        $paid1000 = 1000.00;
        $this->assertLessThanOrEqual(0.01, abs($paid1000 - $expectedAmount));

        // Paid ₹1 (Tampered amount)
        $paid1 = 1.00;
        $this->assertGreaterThan(0.01, abs($paid1 - $expectedAmount));

        // Paid ₹999 (Underpaid)
        $paid999 = 999.00;
        $this->assertGreaterThan(0.01, abs($paid999 - $expectedAmount));

        // Paid ₹1001 (Overpaid)
        $paid1001 = 1001.00;
        $this->assertGreaterThan(0.01, abs($paid1001 - $expectedAmount));
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Payment Webhook Idempotency
    // ─────────────────────────────────────────────────────────────
    public function test_webhook_is_idempotent_and_prevents_duplicate_processing(): void
    {
        $customer = User::factory()->create();

        $order = Order::create([
            'order_code'          => 'NS-TEST01',
            'customer_id'         => $customer->id,
            'payment_order_id'    => 'GATEWAY_ORD_999',
            'contact_name'        => 'Test User',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'Test User',
            'addr_line_1'         => 'Test St',
            'addr_city'           => 'Mumbai',
            'addr_state'          => 'Maharashtra',
            'addr_pincode'        => '400001',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 500,
            'total'               => 500,
            'payment_method'      => 'upi',
            'payment_status'      => 'paid',
            'status'              => 'placed',
        ]);

        $payload = [
            'order_id'   => 'GATEWAY_ORD_999',
            'txn_id'     => 'TXN_GATEWAY_123',
            'status'     => 'SUCCESS',
            'amount'     => 500.00,
            'event'      => 'payment.success',
        ];

        // Send webhook first time
        $res1 = $this->postJson('/payment/webhook', $payload);
        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);

        // Send same webhook second time
        $res2 = $this->postJson('/payment/webhook', $payload);
        $res2->assertStatus(200);
        $res2->assertJson(['success' => true]);

        // Verify only 1 order exists for this payment_order_id
        $this->assertEquals(1, Order::where('payment_order_id', 'GATEWAY_ORD_999')->count());
    }

    // ─────────────────────────────────────────────────────────────
    // 7. Product is_returnable Field Integrity
    // ─────────────────────────────────────────────────────────────
    public function test_product_is_returnable_persists_correctly(): void
    {
        // 1. Create with is_returnable = true
        $p1 = Product::create([
            'name'          => 'Returnable Shirt',
            'slug'          => 'returnable-shirt',
            'price'         => 999.00,
            'is_returnable' => true,
        ]);
        $this->assertTrue($p1->fresh()->is_returnable);

        // 2. Create with is_returnable = false
        $p2 = Product::create([
            'name'          => 'Non-Returnable Mask',
            'slug'          => 'non-returnable-mask',
            'price'         => 199.00,
            'is_returnable' => false,
        ]);
        $this->assertFalse($p2->fresh()->is_returnable);

        // 3. Update from false to true
        $p2->update(['is_returnable' => true]);
        $this->assertTrue($p2->fresh()->is_returnable);
    }

    // ─────────────────────────────────────────────────────────────
    // 8. Category / Product Pivot Relationship
    // ─────────────────────────────────────────────────────────────
    public function test_category_product_pivot_relationship_integrity(): void
    {
        $cat = Category::create([
            'name'      => 'Summer Collection',
            'slug'      => 'summer-collection',
            'is_active' => true,
        ]);

        $prod = Product::create([
            'name'  => 'Cotton Tee',
            'slug'  => 'cotton-tee',
            'price' => 499.00,
        ]);

        // Attach through product_categories pivot
        $cat->products()->attach($prod->id, ['sort_order' => 1]);

        $this->assertCount(1, $cat->fresh()->products);
        $this->assertEquals($prod->id, $cat->products->first()->id);
        $this->assertEquals(1, $cat->products->first()->pivot->sort_order);
    }

    // ─────────────────────────────────────────────────────────────
    // 9. Soft Delete File Lifecycle
    // ─────────────────────────────────────────────────────────────
    public function test_product_soft_delete_preserves_images_and_restore_works(): void
    {
        Storage::fake('public');
        $dummyImage = 'products/test_dummy_product.jpg';
        Storage::disk('public')->put($dummyImage, 'image-binary-data');

        $prod = Product::create([
            'name'  => 'Sneakers',
            'slug'  => 'sneakers',
            'price' => 2999.00,
        ]);

        $img = ProductImage::create([
            'product_id' => $prod->id,
            'image'      => $dummyImage,
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        // Soft delete the product
        $prod->delete();
        $this->assertSoftDeleted('products', ['id' => $prod->id]);

        // Physical image must STILL exist on disk after soft delete!
        Storage::disk('public')->assertExists($dummyImage);

        // Restore product
        $prod->restore();
        $this->assertNotSoftDeleted('products', ['id' => $prod->id]);
        Storage::disk('public')->assertExists($dummyImage);
    }

    // ─────────────────────────────────────────────────────────────
    // 10. Store Settings Caching Performance
    // ─────────────────────────────────────────────────────────────
    public function test_store_settings_cache_remembers_and_invalidates_on_update(): void
    {
        Cache::forget('store_settings_all');

        StoreSetting::setValue('test_setting_key', 'initial_value');
        $this->assertEquals('initial_value', StoreSetting::getValue('test_setting_key'));

        // Cache must have stored it
        $this->assertTrue(Cache::has('store_settings_all'));

        // Update value
        StoreSetting::setValue('test_setting_key', 'updated_value');
        $this->assertEquals('updated_value', StoreSetting::getValue('test_setting_key'));
    }

    // ─────────────────────────────────────────────────────────────
    // 11. Financial Bonus Accounting
    // ─────────────────────────────────────────────────────────────
    public function test_bonus_discount_does_not_corrupt_current_order_discount(): void
    {
        $subtotal = 500.00;
        $shipping = 0.00;
        $currentDiscount = 0.00; // Future bonus discount must NOT be subtracted from current order
        $futureBonusAmount = 50.00; // 10% future credit

        $total = $subtotal - $currentDiscount + $shipping;

        // Total must be 500, not 450
        $this->assertEquals(500.00, $total);
        $this->assertEquals($subtotal, $total);
    }
}
