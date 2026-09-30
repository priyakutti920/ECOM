<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments and transactions.
     */
    public function index(Request $request)
    {
        // ── Overview / Summary Statistics ──
        $stats = [
            'total_collected' => (float) Order::where('payment_status', 'paid')->sum('total'),
            'paid_count'      => Order::where('payment_status', 'paid')->count(),
            'pending_count'   => Order::where('payment_status', 'pending')->count(),
            'failed_count'    => Order::where('payment_status', 'failed')->count(),
            'refunded_count'  => Order::where('payment_status', 'refunded')->count(),
            'refunded_amount' => (float) Order::where('payment_status', 'refunded')->sum('refunded_amount'),
            'today_collected' => (float) Order::where('payment_status', 'paid')
                ->where(function ($w) {
                    $w->whereDate('paid_at', today())
                      ->orWhere(function ($sub) {
                          $sub->whereNull('paid_at')->whereDate('created_at', today());
                      });
                })->sum('total'),
        ];

        // ── Build Query with Filters ──
        $q = Order::query()->with(['customer']);

        // Search keyword
        if ($request->filled('q')) {
            $needle = trim($request->q);
            $q->where(function ($w) use ($needle) {
                $w->where('order_code', 'like', "%{$needle}%")
                  ->orWhere('contact_name', 'like', "%{$needle}%")
                  ->orWhere('contact_mobile', 'like', "%{$needle}%")
                  ->orWhere('contact_email', 'like', "%{$needle}%")
                  ->orWhere('payment_utr', 'like', "%{$needle}%")
                  ->orWhere('payment_order_id', 'like', "%{$needle}%")
                  ->orWhere('refund_reference', 'like', "%{$needle}%");
            });
        }

        // Payment status filter
        if ($request->filled('payment_status') && in_array($request->payment_status, ['paid', 'pending', 'failed', 'refunded'], true)) {
            $q->where('payment_status', $request->payment_status);
        }

        // Payment method/gateway filter
        if ($request->filled('payment_method')) {
            $method = $request->payment_method;
            $q->where(function ($w) use ($method) {
                $w->where('payment_method', $method)
                  ->orWhere('payment_gateway', $method);
            });
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginate results
        $payments = $q->orderByDesc('id')->paginate(20)->withQueryString();

        // Available payment gateways
        $gateways = PaymentGateway::orderBy('id')->get();

        return view('admin.payments.index', [
            'payments' => $payments,
            'stats'    => $stats,
            'gateways' => $gateways,
            'filters'  => $request->only(['q', 'payment_status', 'payment_method', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Export payments list to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $q = Order::query()->with(['customer']);

        if ($request->filled('q')) {
            $needle = trim($request->q);
            $q->where(function ($w) use ($needle) {
                $w->where('order_code', 'like', "%{$needle}%")
                  ->orWhere('contact_name', 'like', "%{$needle}%")
                  ->orWhere('contact_mobile', 'like', "%{$needle}%")
                  ->orWhere('contact_email', 'like', "%{$needle}%")
                  ->orWhere('payment_utr', 'like', "%{$needle}%")
                  ->orWhere('payment_order_id', 'like', "%{$needle}%");
            });
        }

        if ($request->filled('payment_status') && in_array($request->payment_status, ['paid', 'pending', 'failed', 'refunded'], true)) {
            $q->where('payment_status', $request->payment_status);
        }

        if ($request->filled('payment_method')) {
            $method = $request->payment_method;
            $q->where(function ($w) use ($method) {
                $w->where('payment_method', $method)
                  ->orWhere('payment_gateway', $method);
            });
        }

        if ($request->filled('date_from')) {
            $q->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('created_at', '<=', $request->date_to);
        }

        $filename = 'payments-export-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($q) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Headers
            fputcsv($handle, [
                'Order ID',
                'Customer Name',
                'Customer Mobile',
                'Customer Email',
                'Total Amount (INR)',
                'Payment Method',
                'Gateway Slug',
                'Gateway Order ID',
                'UTR / Reference',
                'Payment Status',
                'Order Status',
                'Paid At',
                'Created At',
            ]);

            foreach ($q->orderByDesc('id')->lazy(500) as $order) {
                fputcsv($handle, [
                    $order->order_code,
                    $order->contact_name,
                    $order->contact_mobile,
                    $order->contact_email ?? '',
                    number_format((float) $order->total, 2, '.', ''),
                    strtoupper($order->payment_method ?? 'UPI'),
                    $order->payment_gateway ?? '',
                    $order->payment_order_id ?? '',
                    $order->payment_utr ?? '',
                    strtoupper($order->payment_status ?? 'PENDING'),
                    strtoupper($order->status ?? 'PLACED'),
                    $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : '',
                    $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
