<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
 /**
 * Cart page — display all items
 * Note: Cart items are stored in browser localStorage to keep things simple.
 * Server returns product details for each item id passed in via cookie/query.
 */
 public function show(Request $request)
 {
 $storeName = StoreSetting::getStoreName();
 $bonuses = Bonus::active()->get();
 $categories = Category::active()->orderBy('sort_order')->limit(20)->get();

 // Customer-owned coupons they can apply on this order.
 $customerId = Auth::guard('customer')->id();
 $coupons = $customerId
 ? Coupon::where('customer_id', $customerId)
 ->whereNull('used_at')
 ->where(function ($q) {
 $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
 })
 ->orderByDesc('created_at')
 ->get()
 : collect();

 return view('shop.cart', compact('storeName', 'bonuses', 'categories', 'coupons'));
 }

 /**
 * Get product details for items currently in cart
 * Called by frontend JavaScript to refresh prices/stock.
 */
    public function items(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $products = Product::active()
            ->with(['primaryImage', 'variations'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($ids as $id) {
            if (!isset($products[$id])) continue;
            $p = $products[$id];
            $price = (float) $p->effective_price;
            $items[] = [
                'id'             => $p->id,
                'name'           => $p->name,
                'slug'           => $p->slug,
                'image'          => $p->image_url,
                'price'          => $price,
                'original_price' => (float) $p->price,
                'in_stock'       => $p->stock_status === 'in_stock' && ($p->qty === null || $p->qty > 0),
                'qty_available'  => (int) ($p->qty ?? 10),
            ];
        }

        return response()->json(['success' => true, 'data' => $items]);
    }

    /**
     * Buy Now page — simple checkout form.
     * Customer info (name, mobile) and address (full name, address lines, 10-digit mobile,
     * alternate number, type home/work) are collected here.
     * Cart contents are still read from the browser's localStorage on the client.
     */
    public function buyNow(Request $request)
    {
        $storeName = StoreSetting::getStoreName();
        $bonuses = Bonus::active()->get();
        $categories = Category::active()->orderBy('sort_order')->limit(20)->get();

        $customer = Auth::guard('customer')->user();
        $addresses = $customer
            ? $customer->addresses()->orderByDesc('is_default')->orderByDesc('id')->get()
            : collect();

        return view('shop.buy-now', compact('storeName', 'bonuses', 'categories', 'addresses', 'customer'));
    }

    /**
     * Place order — server-side order creation from cart + form.
     */
    public function placeOrder(Request $request)
    {
        $customerId = Auth::guard('customer')->id();

        // Step 1: validate contact details + items (always required)
        $base = $request->validate([
            'name'           => 'required|string|max:120',
            'mobile'         => 'required|string|regex:/^[0-9]{10}$/',
            'email'          => 'nullable|email|max:120',
            'items'          => 'required',
            'address_id'     => 'nullable|integer|exists:customer_addresses,id',
            'payment_method' => 'nullable|string|in:upi,cod',
        ]);

        // Step 2: resolve the delivery address — either an existing saved one
        // or a brand-new one captured inline.
        if (!empty($base['address_id'])) {
            $address = CustomerAddress::find($base['address_id']);
            abort_if(!$address || $address->customer_id !== $customerId, 403);
        } else {
            // Validate full new-address payload
            $new = $request->validate([
                'full_name'        => 'required|string|max:120',
                'address_line_1'   => 'required|string|max:255',
                'address_line_2'   => 'nullable|string|max:255',
                'city'             => 'required|string|max:120',
                'state'            => 'required|string|max:120',
                'pincode'          => 'required|string|regex:/^[0-9]{6}$/',
                'mobile_primary'   => 'required|string|regex:/^[0-9]{10}$/',
                'mobile_alternate' => 'nullable|string|regex:/^[0-9]{10}$/',
                'address_type'     => 'required|in:home,work',
                'save_address'     => 'sometimes|boolean',
            ], [
                'pincode.regex'        => 'Enter a valid 6-digit PIN code.',
                'mobile_primary.regex' => 'Enter a valid 10-digit mobile number.',
            ]);

            // Persist the new address (auto-default if it's the first one)
            $address = DB::transaction(function () use ($new, $customerId) {
                $isFirst = !CustomerAddress::forCustomer($customerId)->exists();
                return CustomerAddress::create([
                    'customer_id'      => $customerId,
                    'full_name'        => $new['full_name'],
                    'mobile_primary'   => $new['mobile_primary'],
                    'mobile_alternate' => $new['mobile_alternate'] ?? null,
                    'address_line_1'   => $new['address_line_1'],
                    'address_line_2'   => $new['address_line_2'] ?? null,
                    'city'             => $new['city'],
                    'state'            => $new['state'],
                    'pincode'          => $new['pincode'],
                    'type'             => $new['address_type'],
                    'is_default'       => $isFirst,
                ]);
            });
        }

        // Step 3: rebuild pricing server-side from the products (never trust client prices)
        $cartItems = json_decode($base['items'], true) ?: [];
        if (!is_array($cartItems) || empty($cartItems)) {
            return back()->withErrors(['items' => 'Your cart is empty.'])->withInput();
        }

        $itemIds = collect($cartItems)->pluck('id')->filter()->all();
        $products = Product::active()->whereIn('id', $itemIds)->with(['variations', 'options'])->get()->keyBy('id');

        $subtotal = 0;
        $itemsPayload = [];
        foreach ($cartItems as $row) {
            $p = $products->get($row['id'] ?? null);
            if (!$p) continue;
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $varId = !empty($row['variation_id']) ? (int) $row['variation_id'] : null;
            $optionIds = collect($row['options'] ?? [])->pluck('id')->filter()->all();

            // Stock & Inventory Check
            if ($p->stock_status === 'out_of_stock') {
                return back()->withErrors(['items' => "Sorry, '{$p->name}' is currently out of stock."])->withInput();
            }

            if ($p->manage_inventory && $p->qty < $qty) {
                $available = max(0, (int)$p->qty);
                $msg = $available > 0
                    ? "Sorry, '{$p->name}' only has {$available} unit(s) left in stock (requested {$qty})."
                    : "Sorry, '{$p->name}' is currently out of stock.";
                return back()->withErrors(['items' => $msg])->withInput();
            }

            $price = $p->calculateUnitPrice($varId, $optionIds);
            $varName = $row['variation_name'] ?? null;
            if ($varId) {
                $v = $p->variations->firstWhere('id', $varId);
                if ($v) {
                    $varName = $v->name;
                    if ($v->stock_status === 'out_of_stock') {
                        return back()->withErrors(['items' => "Sorry, '{$p->name} - {$varName}' is out of stock."])->withInput();
                    }
                    if ($v->manage_inventory && $v->qty < $qty) {
                        $vAvail = max(0, (int)$v->qty);
                        $msg = $vAvail > 0
                            ? "Sorry, '{$p->name} - {$varName}' only has {$vAvail} unit(s) left in stock."
                            : "Sorry, '{$p->name} - {$varName}' is out of stock.";
                        return back()->withErrors(['items' => $msg])->withInput();
                    }
                }
            }

            $subtotal += $price * $qty;
            $itemsPayload[] = [
                'id'             => $p->id,
                'qty'            => $qty,
                'price'          => $price,
                'name'           => $p->name,
                'image'          => $p->image_url,
                'variation_id'   => $varId,
                'variation_name' => $varName,
                'color'          => $row['color'] ?? null,
                'options'        => $row['options'] ?? null,
            ];
        }

        if (empty($itemsPayload)) {
            return back()->withErrors(['items' => 'None of the cart items are available.'])->withInput();
        }

        // Discounts
        $couponCode = strtoupper(trim((string) $request->input('coupon_code', '')));
        $coupon     = null;
        $couponErr  = null;

        if ($couponCode !== '') {
            $coupon = Coupon::where('code', $couponCode)
                ->redeemableFor($customerId)
                ->first();

            if (!$coupon) {
                $couponErr = 'Invalid or expired coupon code.';
            } elseif ($coupon->min_amount > 0 && $subtotal < (float) $coupon->min_amount) {
                $couponErr = 'Add items worth at least ₹' . number_format($coupon->min_amount, 0) . ' to use this coupon.';
            }
        }

        if ($coupon && !$couponErr) {
            $discount       = $coupon->discountFor($subtotal);
            $discountSource = 'coupon:' . $coupon->code;
        } else {
            $bonuses = Bonus::active()->get();
            $bestPct = 0.0;
            foreach ($bonuses as $b) {
                if ($subtotal >= (float) $b->min_amount && (float) $b->bonus_percent > $bestPct) {
                    $bestPct = (float) $b->bonus_percent;
                }
            }
            $discount       = round($subtotal * ($bestPct / 100), 2);
            $discountSource = 'bonus:' . $bestPct;
        }

        if (!empty($couponErr)) {
            return back()->withErrors(['coupon_code' => $couponErr])->withInput();
        }

        $total = ($coupon && !$couponErr)
            ? round($subtotal - $discount, 2)
            : round($subtotal, 2);

        $pendingPayload = [
            'customer_id' => $customerId,
            'contact' => [
                'name'   => $base['name'],
                'mobile' => $base['mobile'],
                'email'  => $base['email'] ?? null,
            ],
            'address' => [
                'id'               => $address->id,
                'full_name'        => $address->full_name,
                'line1'            => $address->address_line_1,
                'line2'            => $address->address_line_2,
                'city'             => $address->city,
                'state'            => $address->state,
                'pincode'          => $address->pincode,
                'mobile_primary'   => $address->mobile_primary,
                'mobile_alternate' => $address->mobile_alternate,
                'type'             => $address->type,
            ],
            'items'           => $itemsPayload,
            'subtotal'        => $subtotal,
            'discount'        => $discount,
            'shipping'        => 0,
            'total'           => $total,
            'coupon_code'     => $coupon?->code,
            'discount_source' => $discountSource,
        ];

        $paymentMethod = $request->input('payment_method', 'upi');
        if ($paymentMethod === 'cod') {
            $order = PaymentController::createCodOrder($pendingPayload);
            return redirect()->route('shop.order.success', ['order' => $order->order_code]);
        }

        $token = PaymentController::stashPending($pendingPayload);
        return redirect()->route('shop.payment.show', ['token' => $token]);
    }
}
