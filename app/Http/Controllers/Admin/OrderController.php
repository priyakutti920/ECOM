<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * The lifecycle states an admin can flip the order to (in display order).
     */
    public const LIFECYCLE_STATES = [
        'placed',
        'confirmed',
        'processing',
        'packed',
        'dispatched',
        'shipped',
        'out_for_delivery',
        'delivered',
        'cancelled',
        'returned',
    ];

    /**
     * GET /admin/orders
     */
    public function index(Request $request)
    {
        $q = Order::query()->with(['items', 'customer']);

        // Filters
        if ($request->filled('q')) {
            $needle = $request->q;
            $q->where(function ($w) use ($needle) {
                $w->where('order_code', 'like', "%{$needle}%")
                  ->orWhere('contact_name', 'like', "%{$needle}%")
                  ->orWhere('contact_mobile', 'like', "%{$needle}%")
                  ->orWhere('addr_full_name', 'like', "%{$needle}%")
                  ->orWhere('addr_mobile_primary', 'like', "%{$needle}%")
                  ->orWhere('payment_utr', 'like', "%{$needle}%")
                  ->orWhereHas('items', function ($iq) use ($needle) {
                      $iq->where('product_name', 'like', "%{$needle}%");
                  });
            });
        }
        if ($request->filled('status') && in_array($request->status, self::LIFECYCLE_STATES, true)) {
            $q->where('status', $request->status);
        }
        if ($request->filled('payment_status') && in_array($request->payment_status, ['paid', 'failed', 'pending', 'refunded'], true)) {
            $q->where('payment_status', $request->payment_status);
        }

        $orders = $q->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $request->only(['q', 'status', 'payment_status']),
        ]);
    }

    /**
     * GET /admin/orders/{order}
     */
    public function show(string $order)
    {
        $o = Order::where('order_code', $order)
            ->with(['items', 'customer', 'markedPaidBy'])
            ->firstOrFail();

        return view('admin.orders.show', [
            'order' => $o,
            'lifecycleStates' => self::LIFECYCLE_STATES,
        ]);
    }

    /**
     * POST /admin/orders/{order}/status
     * Body: { status: "accepted"|"dispatched"|"delivered", note?: "" }
     */
    public function updateStatus(Request $request, string $order)
    {
        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::LIFECYCLE_STATES),
            'note'   => 'nullable|string|max:500',
        ]);

        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $from = $o->status;
        $o->status = $data['status'];
        $o->pushHistory('status_change', "{$from} → {$data['status']}" . ($data['note'] ? " — {$data['note']}" : ''), $user?->id, $user?->name);
        $o->save();

        // Send order status changed email to customer
        try {
            $email = $o->contact_email ?: optional($o->customer)->email;
            if ($email) {
                Mail::to($email)->send(new OrderStatusChangedMail($o, $from, $data['status'], $data['note'] ?? null));
            }
        } catch (\Throwable $e) {
            Log::warning('OrderStatusChangedMail failed: ' . $e->getMessage());
        }

        return back()->with('success', "Order status updated to “{$data['status']}”.");
    }

    /**
     * POST /admin/orders/{order}/custom-status
     * Body: { message: "Out for delivery — ETA 30 mins" }
     */
    public function addCustomStatus(Request $request, string $order)
    {
        $data = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $o->appendCustomStatus($data['message'], $user?->id, $user?->name);
        $o->save();

        return back()->with('success', 'Custom status added.');
    }

    /**
     * POST /admin/orders/{order}/custom-status/remove
     * Body: { index: 0 }
     */
    public function removeCustomStatus(Request $request, string $order)
    {
        $data = $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $list = $o->custom_statuses ?? [];
        if (array_key_exists($data['index'], $list)) {
            array_splice($list, $data['index'], 1);
            $o->custom_statuses = $list;
            $o->pushHistory('custom_status_removed', "Removed custom status #{$data['index']}", $user?->id, $user?->name);
            $o->save();
        }

        return back()->with('success', 'Custom status removed.');
    }

    /**
     * POST /admin/orders/{order}/dispatch
     * Body: { via?: "DTDC", tracking?: "DTDC12345" }
     */
    public function dispatchOrder(Request $request, string $order)
    {
        $data = $request->validate([
            'via'      => 'nullable|string|max:100',
            'tracking' => 'nullable|string|max:200',
        ]);

        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $from = $o->status;
        $o->status          = 'dispatched';
        $o->dispatched_via  = $data['via']      ?: null;
        $o->tracking_number = $data['tracking'] ?: null;
        $o->dispatched_at   = now();
        $dispatchNote = "via " . ($data['via'] ?: 'Courier') . " — tracking " . ($data['tracking'] ?: '—');
        $o->pushHistory('dispatched', $dispatchNote, $user?->id, $user?->name);
        $o->save();

        // Send dispatched email to customer
        try {
            $email = $o->contact_email ?: optional($o->customer)->email;
            if ($email) {
                Mail::to($email)->send(new OrderStatusChangedMail($o, $from, 'dispatched', $dispatchNote));
            }
        } catch (\Throwable $e) {
            Log::warning('OrderStatusChangedMail on dispatch failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Order marked dispatched.');
    }

    /**
     * POST /admin/orders/{order}/mark-paid
     * Used for cash on delivery, manual UPI confirmation, or gateway-recovery flows.
     * Body: { utr?: "1234", method?: "cash"|"upi"|"bank" }
     */
    public function markPaid(Request $request, string $order)
    {
        $data = $request->validate([
            'utr'    => 'nullable|string|max:100',
            'method' => 'nullable|in:cash,upi,bank,other',
        ]);

        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $o->payment_status   = 'paid';
        $o->paid_at          = $o->paid_at ?: now();
        $o->marked_paid_at   = now();
        $o->marked_paid_by   = $user?->id;
        if (!empty($data['utr']))    $o->payment_utr = $data['utr'];
        if (!empty($data['method'])) $o->payment_method = $data['method'];
        $o->pushHistory('mark_paid', "Method: " . ($data['method'] ?? 'manual') . " — UTR: " . ($data['utr'] ?? '—'), $user?->id, $user?->name);
        $o->save();

        return back()->with('success', 'Order marked as paid.');
    }

    /**
     * POST /admin/orders/{order}/cancel
     * Body: { reason: "Out of stock" }
     */
    public function cancel(Request $request, string $order)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $o->cancelWithRestock($data['reason'], $user?->id, $user?->name);

        return back()->with('success', 'Order cancelled and stock restored to inventory.');
    }

    /**
     * POST /admin/orders/{order}/refund
     * Body: { amount: 500.00, reference: "REF123" }
     */
    public function refund(Request $request, string $order)
    {
        $o = Order::where('order_code', $order)->firstOrFail();
        $user = Auth::user();

        $maxRefundable = max(0, round((float)$o->total - (float)$o->refunded_amount, 2));

        if ($maxRefundable <= 0) {
            return back()->withErrors(['amount' => 'This order has already been fully refunded.']);
        }

        $data = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01', 'max:' . $maxRefundable],
            'reference' => 'nullable|string|max:100',
        ], [
            'amount.max' => "Refund amount cannot exceed the remaining balance of ₹{$maxRefundable}.",
        ]);

        $amount = round((float) $data['amount'], 2);
        $newRefundedTotal = round((float)$o->refunded_amount + $amount, 2);

        $o->refunded_amount  = $newRefundedTotal;
        $o->refunded_at      = now();
        $o->refund_reference = $data['reference'] ?: null;

        if ($newRefundedTotal >= (float)$o->total) {
            $o->status         = 'refunded';
            $o->payment_status = 'refunded';
        } else {
            $o->payment_status = 'partially_refunded';
        }

        $o->pushHistory('refunded', "Refunded ₹{$amount} (Total refunded: ₹{$newRefundedTotal})" . ($data['reference'] ? " — ref {$data['reference']}" : ''), $user?->id, $user?->name);
        $o->save();

        return back()->with('success', "Refund of ₹{$amount} recorded successfully.");
    }

    /**
     * GET /admin/orders/{order}/invoice
     *  - default: open the bill in the browser (with admin chrome)
     *  - ?download=1: stream a real A4 PDF using barryvdh/laravel-dompdf
     */
    public function invoice(Request $request, string $order)
    {
        $o = Order::where('order_code', $order)->with(['items', 'customer'])->firstOrFail();

        if ($request->boolean('download')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.orders.invoice_pdf', ['order' => $o])
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,
                    'defaultFont'          => 'sans-serif',
                ]);

            return $pdf->download('Bill-' . $o->order_code . '.pdf');
        }

        return view('admin.orders.invoice', [
            'order'      => $o,
            'hideChrome' => false,
        ]);
    }
}
