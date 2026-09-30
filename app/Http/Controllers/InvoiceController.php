<?php

namespace App\Http\Controllers;

use App\Models\BillingTemplate;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display Invoice Manager dashboard with filters & statistics.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('template')->orderBy('id', 'desc');

        // Filter by Status
        if ($request->filled('status')) {
            $statusVal = match ($request->status) {
                'paid' => 1,
                'cancelled' => 2,
                'pending' => 0,
                default => null,
            };
            if ($statusVal !== null) {
                $query->where('status', $statusVal);
            }
        }

        // Filter by Template
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        // Search Keyword
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('customer_email', 'like', "%{$s}%");
            });
        }

        // Statistics — database aggregates (prevents memory exhaustion on large datasets)
        $stats = [
            'total_count'    => Invoice::count(),
            'total_amount'   => (float) Invoice::sum('total_amount'),
            'paid_count'     => Invoice::where('status', 1)->count(),
            'paid_amount'    => (float) Invoice::where('status', 1)->sum('total_amount'),
            'pending_count'  => Invoice::where('status', 0)->count(),
            'pending_amount' => (float) Invoice::where('status', 0)->sum('total_amount'),
        ];

        $invoices = $query->paginate(15)->withQueryString();
        $templates = BillingTemplate::where('status', 1)->orderBy('id', 'asc')->get();
        $orders = Order::orderBy('id', 'desc')->take(30)->get();

        return view('admin.invoices.index', compact('invoices', 'stats', 'templates', 'orders'));
    }

    /**
     * Show / Preview invoice with interactive template chooser.
     */
    public function show($id, Request $request)
    {
        $invoice = Invoice::with('template')->findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'data' => $invoice]);
        }

        return view('admin.invoices.preview', compact('invoice'));
    }

    /**
     * Auto-generate an invoice directly from an existing Order.
     */
    public function fromOrder(Request $request, $order_code = null)
    {
        $code = $order_code ?: $request->input('order_code');

        if (!$code) {
            return redirect()->route('admin.invoices.index')->with('error', 'Please provide an order code.');
        }

        $order = Order::where('order_code', $code)->with('items')->first();

        if (!$order) {
            return redirect()->route('admin.invoices.index')->with('error', "Order with code {$code} was not found.");
        }

        // Check if invoice already exists for this order
        if (!$request->has('force_new')) {
            $existing = Invoice::where('invoice_json', 'like', '%"order_code":"' . $order->order_code . '"%')->latest()->first();
            if ($existing) {
                return redirect()->route('admin.invoices.show', $existing->id)
                    ->with('info', "An invoice for Order #{$order->order_code} already exists (#{$existing->invoice_number}). You can switch templates or print below.");
            }
        }

        $invoice = self::createFromOrder($order, $request->template_id);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', "Invoice #{$invoice->invoice_number} successfully generated for Order #{$order->order_code}!");
    }

    /**
     * Reusable generator to build an invoice record directly from an Order model.
     */
    public static function createFromOrder(Order $order, ?int $templateId = null): Invoice
    {
        $existing = Invoice::where('invoice_json', 'like', '%"order_code":"' . $order->order_code . '"%')->latest()->first();
        if ($existing) {
            return $existing;
        }

        $defaultTemplateId = StoreSetting::getValue('default_invoice_template', 1);
        $tplId = $templateId ?: $defaultTemplateId;

        // Check interstate (Tamil Nadu code 33 is store home state)
        $customerState = strtolower((string)$order->addr_state);
        $isInterstate = !empty($customerState) && !str_contains($customerState, 'tamil') && !str_contains($customerState, 'tn');

        $subtotal = (float)$order->subtotal;
        $taxAmount = round($subtotal * 0.05, 2);

        $itemsList = $order->items->map(function ($it) {
            $desc = $it->product_name;
            if ($it->variation_name) {
                $desc .= ' (' . $it->variation_name . ')';
            }
            if ($it->color) {
                $desc .= ' - ' . $it->color;
            }
            return [
                'name'        => $desc,
                'hsn'         => '61091000',
                'quantity'    => $it->quantity,
                'price'       => (float) $it->unit_price,
                'tax_percent' => 5,
                'total'       => (float) $it->line_total,
            ];
        })->toArray();

        $invoiceNumber = 'INV-' . date('Y') . '-' . strtoupper(substr(uniqid(), 8));

        return Invoice::create([
            'user_id'          => $order->customer_id ?: (auth()->id() ?: 1),
            'template_id'      => $tplId,
            'customer_name'    => $order->contact_name ?: $order->addr_full_name,
            'customer_email'   => $order->contact_email,
            'customer_phone'   => $order->contact_mobile ?: $order->addr_mobile_primary,
            'customer_address' => trim($order->addr_line_1 . ($order->addr_line_2 ? ', ' . $order->addr_line_2 : '') . ', ' . $order->addr_city . ', ' . $order->addr_state . ' - ' . $order->addr_pincode, ' ,'),
            'customer_gst'     => null,
            'invoice_number'   => $invoiceNumber,
            'invoice_json'     => json_encode([
                'order_code'     => $order->order_code,
                'order_date'     => $order->created_at->format('Y-m-d H:i:s'),
                'payment_method' => $order->payment_method,
                'is_interstate'  => $isInterstate,
                'items'          => $itemsList,
            ]),
            'subtotal'         => $subtotal,
            'tax_amount'       => $taxAmount,
            'other_charges'    => (float) ($order->shipping ?? 0),
            'total_amount'     => (float) $order->total,
            'status'           => $order->payment_status === 'paid' ? 1 : 0,
        ]);
    }

    /**
     * Create or update invoice.
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'id'               => 'nullable|integer|exists:invoices,id',
            'template_id'      => 'nullable|integer|exists:billing_templates,id',
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_gst'     => 'nullable|string|max:50',
            'invoice_number'   => 'required|string|max:100',
            'invoice_json'     => 'nullable|string',
            'subtotal'         => 'nullable|numeric|min:0',
            'tax_amount'       => 'nullable|numeric|min:0',
            'other_charges'    => 'nullable|numeric|min:0',
            'total_amount'     => 'required|numeric|min:0',
            'status'           => 'nullable|integer|in:0,1,2',
        ]);

        $defaultTemplateId = StoreSetting::getValue('default_invoice_template', 1);

        $data = [
            'user_id'          => auth()->id() ?: 1,
            'template_id'      => $validated['template_id'] ?? $defaultTemplateId,
            'customer_name'    => $validated['customer_name'],
            'customer_email'   => $validated['customer_email'] ?? null,
            'customer_phone'   => $validated['customer_phone'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'customer_gst'     => $validated['customer_gst'] ?? null,
            'invoice_number'   => $validated['invoice_number'],
            'invoice_json'     => $validated['invoice_json'] ?? json_encode(['items' => []]),
            'subtotal'         => $validated['subtotal'] ?? 0,
            'tax_amount'       => $validated['tax_amount'] ?? 0,
            'other_charges'    => $validated['other_charges'] ?? 0,
            'total_amount'     => $validated['total_amount'],
            'status'           => $validated['status'] ?? 1,
        ];

        if (!empty($validated['id'])) {
            $invoice = Invoice::findOrFail($validated['id']);
            $invoice->update($data);
            $msg = "Invoice #{$invoice->invoice_number} updated.";
        } else {
            $invoice = Invoice::create($data);
            $msg = "Invoice #{$invoice->invoice_number} created.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'invoice' => $invoice]);
        }

        return redirect()->route('admin.invoices.index')->with('success', $msg);
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Request $request)
    {
        $invoice = Invoice::find($request->id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }
        $invoice->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Invoice deleted successfully.']);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Generate downloadable PDF using the chosen template.
     */
    public function generatePdf($id, Request $request)
    {
        $invoice = Invoice::with('template')->findOrFail($id);
        $templateId = $request->query('template') ?: ($invoice->template_id ?: 1);

        $template = BillingTemplate::find($templateId);
        $templateJson = $template ? json_decode($template->template_json, true) : null;
        $templateKey = $templateJson['key'] ?? 'modern_blue';

        $paper = ($templateKey === 'thermal_pos') ? [0, 0, 226.77, 600] : 'a4'; // 80mm width in points
        $orientation = 'portrait';

        $pdf = Pdf::loadView('admin.invoices.pdf', [
            'invoice'    => $invoice,
            'templateId' => $templateId,
        ])
        ->setPaper($paper, $orientation)
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => ($templateKey === 'thermal_pos') ? 'monospace' : 'sans-serif',
        ]);

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }
}
