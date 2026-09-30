<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    /**
     * My Orders — newest first, paginated, ownership-scoped.
     */
    public function index(Request $request)
    {
        $orders = Order::forCustomer(Auth::guard('customer')->id())
            ->with(['items', 'returns'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $storeName = \App\Models\StoreSetting::getStoreName();

        return view('shop.orders.index', [
            'storeName' => $storeName,
            'orders'    => $orders,
        ]);
    }

    /**
     * Order detail with tracking timeline.
     */
    public function show(string $order)
    {
        $orderModel = Order::where('order_code', $order)
            ->with(['items', 'address', 'returns'])
            ->firstOrFail();

        abort_if($orderModel->customer_id !== Auth::guard('customer')->id(), 403);

        // Show Cancel button only when not yet dispatched.
        $canCancel = in_array($orderModel->status, ['placed', 'accepted'], true);

        // Show Return button for delivered orders when products are returnable and
        // a return hasn't already been requested for the entire order.
        $existingReturns = $orderModel->returns;
        $returnItems = $orderModel->items->map(function (OrderItem $it) use ($existingReturns, $orderModel) {
            $ret = $existingReturns->firstWhere('order_item_id', $it->id);
            $isReturnable = $it->product && $it->product->is_returnable;
            return [
                'item'          => $it,
                'is_returnable' => (bool) $isReturnable,
                'return'        => $ret,
                'can_request'   => $isReturnable && $orderModel->status === 'delivered' && !$ret,
            ];
        });

        $storeName = \App\Models\StoreSetting::getStoreName();

        return view('shop.orders.show', [
            'storeName'    => $storeName,
            'order'        => $orderModel,
            'canCancel'    => $canCancel,
            'returnItems'  => $returnItems,
        ]);
    }

    /**
     * POST /account/orders/{order}/cancel
     * Customer-driven cancellation — only allowed before dispatch.
     */
    public function cancel(Request $request, string $order)
    {
        $orderModel = Order::where('order_code', $order)->firstOrFail();
        abort_if($orderModel->customer_id !== Auth::guard('customer')->id(), 403);

        if (!in_array($orderModel->status, ['placed', 'accepted'], true)) {
            return back()->withErrors(['order' => 'This order can no longer be cancelled.']);
        }

        $data = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $user = Auth::guard('customer')->user();
        $orderModel->cancelWithRestock($data['reason'], $user?->id, $user?->name);

        return redirect()->route('shop.orders.show', ['order' => $orderModel->order_code])
            ->with('success', 'Order cancelled and stock restored successfully.');
    }

    /**
     * POST /account/orders/{order}/return
     * Customer asks for a return for a specific item.
     */
    public function requestReturn(Request $request, string $order)
    {
        $orderModel = Order::where('order_code', $order)->firstOrFail();
        abort_if($orderModel->customer_id !== Auth::guard('customer')->id(), 403);

        if ($orderModel->status !== 'delivered') {
            return back()->withErrors(['order' => 'You can only request a return for delivered orders.']);
        }

        $data = $request->validate([
            'order_item_id' => 'required|integer|exists:order_items,id',
            'reason'        => 'required|string|min:3|max:1000',
            'description'   => 'nullable|string|max:2000',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $item = OrderItem::where('id', $data['order_item_id'])
            ->where('order_id', $orderModel->id)
            ->firstOrFail();

        // Ensure the product is returnable
        $product = $item->product;
        if (!$product || !$product->is_returnable) {
            return back()->withErrors(['order' => 'This product is not eligible for return.']);
        }

        // Block duplicate returns on the same item
        $exists = OrderReturn::where('order_id', $orderModel->id)
            ->where('order_item_id', $item->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['order' => 'A return request already exists for this item.']);
        }

        $imgPath = null;
        if ($request->hasFile('image')) {
            $imgPath = $request->file('image')->store('returns', 'public');
        }

        OrderReturn::create([
            'order_id'      => $orderModel->id,
            'order_item_id' => $item->id,
            'product_id'    => $item->product_id,
            'customer_id'   => $orderModel->customer_id,
            'reason'        => $data['reason'],
            'description'   => $data['description'] ?? null,
            'image_path'    => $imgPath,
            'status'        => 'requested',
            'requested_at'  => now(),
        ]);

        return redirect()->route('shop.orders.show', ['order' => $orderModel->order_code])
            ->with('success', 'Return request submitted. Our team will review it shortly.');
    }

    /**
     * GET /account/orders/{order}/invoice
     * Stream a PDF invoice (DomPDF) for the customer.
     */
    public function invoice(string $order)
    {
        $orderModel = Order::where('order_code', $order)
            ->with(['items', 'customer', 'address'])
            ->firstOrFail();
        abort_if($orderModel->customer_id !== Auth::guard('customer')->id(), 403);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shop.orders.invoice_pdf', [
            'order' => $orderModel,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        return $pdf->download('Invoice-' . $orderModel->order_code . '.pdf');
    }

    /**
     * Public / Customer Track Order page.
     * Accessible by order_code and strictly verified 10-digit mobile or email.
     */
    public function trackOrder(Request $request)
    {
        $storeName = \App\Models\StoreSetting::getStoreName();
        $code = trim(strtoupper((string) $request->input('code', '')));
        $phone = trim((string) $request->input('phone', ''));

        $order = null;
        $error = null;

        if ($code !== '') {
            $customerId = Auth::guard('customer')->id();

            if ($customerId) {
                // Logged-in customers track orders belonging to their account
                $order = Order::where('order_code', $code)
                    ->where('customer_id', $customerId)
                    ->with(['items'])
                    ->first();

                if (!$order && $phone !== '') {
                    // Fall back to phone/email verification if customer has older guest orders
                    $cleanPhone = preg_replace('/\D+/', '', $phone);
                    $isValidMobile = (bool) preg_match('/^[6-9][0-9]{9}$/', $cleanPhone);
                    $isValidEmail = (bool) filter_var($phone, FILTER_VALIDATE_EMAIL);

                    if ($isValidMobile || $isValidEmail) {
                        $order = Order::where('order_code', $code)
                            ->where(function ($q) use ($phone, $cleanPhone, $isValidMobile, $isValidEmail) {
                                if ($isValidMobile) {
                                    $q->where('contact_mobile', $cleanPhone)
                                      ->orWhere('addr_mobile_primary', $cleanPhone);
                                } elseif ($isValidEmail) {
                                    $q->where('contact_email', strtolower($phone));
                                }
                            })
                            ->with(['items'])
                            ->first();
                    }
                }
            } else {
                // Guests MUST provide their full registered 10-digit Indian mobile number or valid email
                $cleanPhone = preg_replace('/\D+/', '', $phone);
                $isValidMobile = (bool) preg_match('/^[6-9][0-9]{9}$/', $cleanPhone);
                $isValidEmail = (bool) filter_var($phone, FILTER_VALIDATE_EMAIL);

                if (empty($phone)) {
                    $error = 'Please enter the 10-digit mobile number or email associated with this order.';
                } elseif (!$isValidMobile && !$isValidEmail) {
                    $error = 'Please enter a valid 10-digit mobile number (starting with 6-9) or email address.';
                } else {
                    // Strict exact matching — NEVER use LIKE wildcard queries on phone numbers
                    $order = Order::where('order_code', $code)
                        ->where(function ($q) use ($phone, $cleanPhone, $isValidMobile, $isValidEmail) {
                            if ($isValidMobile) {
                                $q->where('contact_mobile', $cleanPhone)
                                  ->orWhere('addr_mobile_primary', $cleanPhone);
                            } elseif ($isValidEmail) {
                                $q->where('contact_email', strtolower($phone));
                            }
                        })
                        ->with(['items'])
                        ->first();
                }
            }

            if (!$order && !$error) {
                $error = 'No order found matching the provided order ID and contact details.';
            }
        }

        return view('shop.orders.track', compact('storeName', 'order', 'code', 'phone', 'error'));
    }
}
