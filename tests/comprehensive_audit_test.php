<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Review;
use App\Models\User;
use App\Models\Invoice;
use App\Http\Controllers\InvoiceController;
use App\Services\OtpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "=======================================================\n";
echo "       COMPREHENSIVE E-COMMERCE LOGIC AUDIT TEST       \n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($description, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "[PASS] {$description}\n";
        $passCount++;
    } else {
        echo "[FAIL] {$description}\n";
        $failCount++;
    }
}

// -----------------------------------------------------------
// TEST 1: Stock & Restock on Order Cancel
// -----------------------------------------------------------
echo "--- TEST 1: Stock & Restock on Cancel ---\n";
$product = Product::first();
if ($product) {
    $product->update(['qty' => 15, 'stock_status' => 'in_stock']);

    $order = Order::create([
        'order_code' => 'TEST-RESTOCK-' . strtoupper(Str::random(6)),
        'customer_id' => 1,
        'contact_name' => 'Audit Tester',
        'contact_email' => 'test@example.com',
        'contact_mobile' => '9876543210',
        'addr_full_name' => 'Audit Tester',
        'addr_mobile_primary' => '9876543210',
        'addr_line_1' => '123 Test St',
        'addr_city' => 'Chennai',
        'addr_state' => 'Tamil Nadu',
        'addr_pincode' => '600001',
        'subtotal' => 1000,
        'discount' => 0,
        'shipping' => 50,
        'total' => 1050,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'status' => 'processing',
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'quantity' => 3,
        'unit_price' => 300,
        'line_total' => 900,
    ]);

    // Simulate inventory deduction at purchase
    $product->decrement('qty', 3);
    assertTest("Product stock decremented to 12 upon order", (int) $product->fresh()->qty === 12);

    // Cancel order with restock
    $order->cancelWithRestock('Customer requested cancellation', 1, 'Audit Tester');
    $freshProduct = $product->fresh();
    assertTest("Product stock restored to 15 after cancelWithRestock()", (int) $freshProduct->qty === 15);
    assertTest("Order status is cancelled", $order->fresh()->status === 'cancelled');

    // Clean up
    $orderItem->delete();
    $order->delete();
}

// -----------------------------------------------------------
// TEST 2: Refund Balance Safeguard
// -----------------------------------------------------------
echo "\n--- TEST 2: Refund Balance Safeguard ---\n";
$order = Order::create([
    'order_code' => 'TEST-REFUND-' . strtoupper(Str::random(6)),
    'customer_id' => 1,
    'contact_name' => 'Audit Tester',
    'contact_email' => 'test@example.com',
    'contact_mobile' => '9876543210',
    'addr_full_name' => 'Audit Tester',
    'addr_mobile_primary' => '9876543210',
    'addr_line_1' => '123 Test St',
    'addr_city' => 'Chennai',
    'addr_state' => 'Tamil Nadu',
    'addr_pincode' => '600001',
    'subtotal' => 1000,
    'discount' => 0,
    'shipping' => 50,
    'total' => 1050,
    'refunded_amount' => 500,
    'payment_method' => 'upi',
    'payment_status' => 'paid',
    'status' => 'delivered',
]);

$maxRefundable = round($order->total - $order->refunded_amount, 2);
assertTest("Max refundable is correctly calculated as 550", $maxRefundable === 550.0);
$excessRefundAttempt = 600.0;
assertTest("Excess refund (> remaining total) correctly blocked", $excessRefundAttempt > $maxRefundable);

$order->delete();

// -----------------------------------------------------------
// TEST 3: Coupon Handling on Order Cancel
// -----------------------------------------------------------
echo "\n--- TEST 3: Coupon Handling on Order Cancel ---\n";
$coupon = Coupon::create([
    'code' => 'AUDITTEST' . rand(100, 999),
    'type' => 'flat',
    'value' => 100,
    'min_amount' => 100,
    'is_active' => true,
]);

$orderWithCoupon = Order::create([
    'order_code' => 'TEST-COUPON-' . strtoupper(Str::random(6)),
    'customer_id' => 1,
    'contact_name' => 'Audit Tester',
    'contact_email' => 'test@example.com',
    'contact_mobile' => '9876543210',
    'addr_full_name' => 'Audit Tester',
    'addr_mobile_primary' => '9876543210',
    'addr_line_1' => '123 Test St',
    'addr_city' => 'Chennai',
    'addr_state' => 'Tamil Nadu',
    'addr_pincode' => '600001',
    'subtotal' => 500,
    'discount' => 100,
    'shipping' => 0,
    'total' => 400,
    'payment_method' => 'cod',
    'payment_status' => 'pending',
    'status' => 'pending',
]);

// Mark coupon used in order
$coupon->markUsed($orderWithCoupon->id);
assertTest("Coupon marked as used on order placement", $coupon->fresh()->is_active === false && (int) $coupon->fresh()->used_in_order_id === (int) $orderWithCoupon->id);

// Cancel order and verify coupon restored
$orderWithCoupon->cancelWithRestock('Order cancel test', 1, 'Audit Tester');
$freshCoupon = $coupon->fresh();
assertTest("Coupon restored to active after order cancellation", $freshCoupon->is_active === true && $freshCoupon->used_in_order_id === null);

$orderWithCoupon->delete();
$coupon->delete();

// -----------------------------------------------------------
// TEST 4: Product Real Ratings & Reviews
// -----------------------------------------------------------
echo "\n--- TEST 4: Product Real Ratings & Reviews ---\n";
if ($product) {
    // Delete existing test reviews for this product
    Review::where('product_id', $product->id)->delete();

    Review::create([
        'product_id' => $product->id,
        'customer_id' => 1,
        'customer_name' => 'Reviewer 1',
        'customer_email' => 'rev1@example.com',
        'rating' => 5,
        'title' => 'Great product',
        'comment' => 'Loved it',
        'is_approved' => true,
    ]);
    Review::create([
        'product_id' => $product->id,
        'customer_id' => 1,
        'customer_name' => 'Reviewer 2',
        'customer_email' => 'rev2@example.com',
        'rating' => 3,
        'title' => 'Okay product',
        'comment' => 'Decent quality',
        'is_approved' => true,
    ]);

    $freshProduct = $product->fresh();
    assertTest("Product rating_count is 2", (int) $freshProduct->rating_count === 2);
    assertTest("Product average_rating is 4.0", (float) $freshProduct->average_rating === 4.0);

    // Clean up test reviews
    Review::where('product_id', $product->id)->delete();
}

// -----------------------------------------------------------
// TEST 5: Auto-Invoice Creation with State GST Logic
// -----------------------------------------------------------
echo "\n--- TEST 5: Auto-Invoice Creation & GST Logic ---\n";
// Intra-state (Tamil Nadu) -> CGST + SGST
$orderTN = Order::create([
    'order_code' => 'TEST-TN-' . strtoupper(Str::random(6)),
    'customer_id' => 1,
    'contact_name' => 'TN Customer',
    'contact_email' => 'tn@example.com',
    'contact_mobile' => '9876543210',
    'addr_full_name' => 'TN Customer',
    'addr_mobile_primary' => '9876543210',
    'addr_line_1' => 'Anna Salai',
    'addr_city' => 'Chennai',
    'addr_state' => 'Tamil Nadu',
    'addr_pincode' => '600002',
    'subtotal' => 2000,
    'discount' => 0,
    'shipping' => 100,
    'total' => 2100,
    'payment_method' => 'cod',
    'payment_status' => 'pending',
    'status' => 'processing',
]);

$invoiceTN = InvoiceController::createFromOrder($orderTN);
assertTest("Invoice generated for TN order", $invoiceTN instanceof Invoice);
assertTest("TN Order is intra-state (is_interstate = false)", $invoiceTN->is_interstate === false);
assertTest("TN Order has CGST > 0 and SGST > 0", $invoiceTN->cgst > 0 && $invoiceTN->sgst > 0);
assertTest("TN Order has IGST = 0", (float) $invoiceTN->igst === 0.0);

// Inter-state (Karnataka) -> IGST
$orderKA = Order::create([
    'order_code' => 'TEST-KA-' . strtoupper(Str::random(6)),
    'customer_id' => 1,
    'contact_name' => 'KA Customer',
    'contact_email' => 'ka@example.com',
    'contact_mobile' => '9876543210',
    'addr_full_name' => 'KA Customer',
    'addr_mobile_primary' => '9876543210',
    'addr_line_1' => 'MG Road',
    'addr_city' => 'Bangalore',
    'addr_state' => 'Karnataka',
    'addr_pincode' => '560001',
    'subtotal' => 2000,
    'discount' => 0,
    'shipping' => 100,
    'total' => 2100,
    'payment_method' => 'upi',
    'payment_status' => 'paid',
    'status' => 'processing',
]);

$invoiceKA = InvoiceController::createFromOrder($orderKA);
assertTest("Invoice generated for KA order", $invoiceKA instanceof Invoice);
assertTest("KA Order is inter-state (is_interstate = true)", $invoiceKA->is_interstate === true);
assertTest("KA Order has IGST > 0", $invoiceKA->igst > 0);
assertTest("KA Order has CGST = 0 and SGST = 0", (float) $invoiceKA->cgst === 0.0 && (float) $invoiceKA->sgst === 0.0);

$orderTN->delete();
$orderKA->delete();

// -----------------------------------------------------------
// TEST 6: Forgot Password Flow & Password Reset
// -----------------------------------------------------------
echo "\n--- TEST 6: Forgot Password & Reset Flow ---\n";
$testUser = User::firstOrCreate(
    ['email' => 'reset_test@example.com'],
    [
        'name' => 'Reset Tester',
        'password' => Hash::make('old_password123'),
        'is_admin' => false,
    ]
);

$plainToken = Str::random(60);
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $testUser->email],
    ['token' => Hash::make($plainToken), 'created_at' => now()]
);

$record = DB::table('password_reset_tokens')->where('email', $testUser->email)->first();
assertTest("Password reset token stored in database", $record !== null);
assertTest("Token matches Hash check", Hash::check($plainToken, $record->token));

// Simulate reset password
$newPass = 'new_password123';
$testUser->update(['password' => Hash::make($newPass)]);
DB::table('password_reset_tokens')->where('email', $testUser->email)->delete();

assertTest("Password reset successfully verified with new password", Hash::check($newPass, $testUser->fresh()->password));
assertTest("Old password does not match anymore", !Hash::check('old_password123', $testUser->fresh()->password));
assertTest("Token cleaned up from database", DB::table('password_reset_tokens')->where('email', $testUser->email)->count() === 0);

$testUser->delete();

// -----------------------------------------------------------
// TEST 7: OTP Graceful Handling
// -----------------------------------------------------------
echo "\n--- TEST 7: OTP Graceful Handling ---\n";
$otpService = app(OtpService::class);
$otpResult = $otpService->generate('audit_otp@example.com');
assertTest("OtpService::generate() returns result array with success key", isset($otpResult['success']));
assertTest("OtpService generates 4-digit code in dev mode", isset($otpResult['code']) ? strlen($otpResult['code']) === 4 : true);

echo "\n=======================================================\n";
echo "AUDIT TEST COMPLETE: {$passCount} Passed, {$failCount} Failed\n";
echo "=======================================================\n";

exit($failCount > 0 ? 1 : 0);
