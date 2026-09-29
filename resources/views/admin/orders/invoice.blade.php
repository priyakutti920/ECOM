<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_code }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            size: A4;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 15mm;
        }

        /* Main Container */
        .invoice-box {
            width: 100%;
            min-height: 100vh;
            position: relative;
            background: #fff;
            padding-bottom: 110px;
        }

        /* Header Section */
        .header-section {
            display: table;
            width: 100%;
            padding: 25px 30px;
            border-bottom: 2px solid #dc2626;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .store-name {
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .store-address {
            font-size: 10px;
            color: #666;
            line-height: 1.5;
        }

        .store-contact {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #000;
            margin-bottom: 8px;
        }

        .invoice-meta {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-meta strong {
            color: #000;
        }

        /* Info Section */
        .info-section {
            padding: 20px 30px;
            border-bottom: 1px solid #ddd;
        }

        .info-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #dc2626;
            margin-bottom: 8px;
        }

        .info-content {
            font-size: 11px;
            line-height: 1.6;
        }

        .info-content strong {
            display: inline-block;
            min-width: 70px;
        }

        /* Products Table */
        .products-section {
            padding: 0 30px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .products-table thead th {
            background: transparent;
            padding: 12px 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
            color: #dc2626;
            border-bottom: 2px solid #dc2626;
        }

        .products-table thead th.text-center {
            text-align: center;
        }

        .products-table thead th.text-right {
            text-align: right;
        }

        .products-table tbody td {
            padding: 10px;
            font-size: 10px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }

        .products-table tbody td.text-center {
            text-align: center;
        }

        .products-table tbody td.text-right {
            text-align: right;
        }

        .products-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Totals Section */
        .totals-section {
            padding: 15px 30px;
            margin-top: 15px;
            border-top: 1px solid #eee;
            width: 100%;
        }

        .totals-right {
            float: right;
            width: 50%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .totals-table td:first-child {
            font-weight: 600;
            color: #333;
        }

        .totals-table td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .totals-table tr.total td {
            background: #dc2626;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            padding: 14px 14px;
        }

        .totals-table tr.refund td:last-child {
            color: #dc2626;
        }

        .totals-table tr.paid td:last-child {
            color: #28a745;
        }

        .totals-table tr.pending td:last-child {
            color: #dc2626;
        }

        /* Footer Bar */
        .footer-bar {
            background: #dc2626;
            padding: 18px 35px;
            margin-top: 35px;

            width: calc(100% + 30mm);
            margin-left: -15mm;
            margin-right: -15mm;

            display: table;
        }

        .footer-left {
            display: table-cell;
            width: 50%;
        }

        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }

        .footer-bar-text {
            font-size: 11px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Signature Section */
        .signature-section {
            padding: 35px 30px;
            display: table;
            width: 100%;
        }

        .signature-left {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: bottom;
        }

        .sign-line {
            font-size: 11px;
            border-top: 1px solid #000;
            padding-top: 6px;
            display: inline-block;
            min-width: 180px;
            font-weight: 600;
        }

        .footer-note {
            font-size: 9px;
            color: #999;
            margin-top: 8px;
        }

        /* Bottom Red Bar */
        .bottom-red-bar {
            position: fixed;

            left: 0;
            right: 0;
            bottom: 0;

            width: 100%;
            height: 70px;

            background: #b90f16;

            z-index: -1;
        }

        /* Chrome (admin button bar) — hidden on print */
        .bill-chrome {
            max-width: 210mm;
            margin: 0 auto 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bill-chrome a, .bill-chrome button {
            font-family: inherit;
            font-size: 12px;
            padding: 7px 14px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            cursor: pointer;
            text-decoration: none;
        }
        .bill-chrome a:hover, .bill-chrome button:hover { background: #f5f5f5; }
        .bill-chrome .print-btn { background: #3a7bd5; color: #fff; border-color: #3a7bd5; }
        .bill-chrome .print-btn:hover { background: #2f6bc4; }

        @media print {
            body { padding: 0; }
            .bill-chrome { display: none; }
        }

        @if(!empty($hideChrome))
            .bill-chrome { display: none !important; }
        @endif
    </style>
</head>

<body>

    @php
        // Pull store info from store_settings. Fall back to brand defaults.
        $storeName    = \App\Models\StoreSetting::getStoreName();
        $storeAddress = \App\Models\StoreSetting::getValue('address', '');
        $storeMobile  = \App\Models\StoreSetting::getValue('mobile', '');
        $storeEmail   = \App\Models\StoreSetting::getValue('email', '');

        // "Bill No" — reuses order_code; if you want a separate sequence later,
        // store it in a `bill_number` column on orders.
        $billNo = $order->order_code;

        // Order line items. (We never use $order->services since orders are
        // always products in this system; the legacy services branch is kept
        // out of the PDF for cleanliness.)
        $items = $order->items;

        // Net payable after any refund.
        $netTotal = (float) $order->total - (float) ($order->refunded_amount ?? 0);
    @endphp

    {{-- Admin chrome (only visible in browser, not on print) --}}
    <div class="bill-chrome">
        <a href="{{ route('admin.orders.show', ['order' => $order->order_code]) }}">
            <i class="fas fa-arrow-left"></i> Back to order
        </a>
        <button type="button" class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print / Save PDF
        </button>
    </div>

    <div class="invoice-box">

        <!-- Header Section -->
        <div class="header-section">

            <div class="header-left">

                <div class="store-name">
                    {{ $storeName }}
                </div>

                @if($storeAddress)
                    <div class="store-address">
                        {{ $storeAddress }}
                    </div>
                @endif

                <div class="store-contact">
                    @if($storeMobile)
                        Ph: {{ $storeMobile }}
                    @endif
                    @if($storeEmail)
                        @if($storeMobile) | @endif
                        {{ $storeEmail }}
                    @endif
                </div>

            </div>

            <div class="header-right">

                <div class="invoice-title">
                    Invoice
                </div>

                <div class="invoice-meta">
                    <strong>Bill No:</strong>
                    {{ $billNo }}
                    <br>

                    <strong>Date:</strong>
                    {{ $order->created_at->format('d/m/Y') }}
                    <br>

                    <strong>Order ID:</strong>
                    {{ $order->order_code }}
                </div>

            </div>

        </div>

        <!-- Bill To Section -->
        <div class="info-section">

            <div class="info-label">
                Bill To & Payment Details
            </div>

            <div class="info-content">

                <strong>Name:</strong>
                {{ $order->contact_name ?: ($order->addr_full_name ?: '—') }}
                <br>

                <strong>Phone:</strong>
                {{ $order->contact_mobile ?: ($order->addr_mobile_primary ?: '—') }}

                @if($order->addr_mobile_alternate)
                <br>
                <strong>Alt:</strong>
                {{ $order->addr_mobile_alternate }}
                @endif

                @if($order->contact_email)
                <br>
                <strong>Email:</strong>
                {{ $order->contact_email }}
                @endif

                @if($order->addr_line_1)
                <br>
                <strong>Address:</strong>
                {{ $order->addr_line_1 }}{{ $order->addr_line_2 ? ', '.$order->addr_line_2 : '' }},
                {{ $order->addr_city }}, {{ $order->addr_state }} — {{ $order->addr_pincode }}
                @endif

                <br><br>
                <strong>Payment:</strong>
                @if($order->payment_status === 'paid')
                    <span style="color: #28a745; font-weight: 700;">PAID</span>
                    @if($order->payment_utr)
                        &nbsp;<span style="font-size: 10px; color:#666;">(UTR: {{ $order->payment_utr }})</span>
                    @endif
                @elseif($order->payment_status === 'refunded')
                    <span style="color: #6c757d; font-weight: 700;">REFUNDED</span>
                @elseif($order->payment_status === 'failed')
                    <span style="color: #dc2626; font-weight: 700;">FAILED</span>
                @else
                    <span style="color: #dc2626; font-weight: 700;">PENDING</span>
                @endif

            </div>

        </div>

        <!-- Products Table -->
        <div class="products-section">

            <table class="products-table">

                <thead>
                    <tr>
                        <th style="width:35px;">No</th>
                        <th>Description</th>
                        <th style="width:80px;" class="text-right">Price</th>
                        <th style="width:50px;" class="text-center">Qty</th>
                        <th style="width:90px;" class="text-right">Total</th>
                    </tr>
                </thead>

                <tbody>

                    @php $sno = 1; @endphp

                    @forelse($items as $item)

                    <tr>
                        <td>{{ $sno++ }}</td>

                        <td>
                            {{ $item->product_name }}
                            {{-- Note: providers intentionally omitted from the bill per requirements --}}
                        </td>

                        <td class="text-right">
                            {{ number_format($item->unit_price, 2) }}
                        </td>

                        <td class="text-center">
                            {{ $item->quantity }}
                        </td>

                        <td class="text-right">
                            {{ number_format($item->line_total, 2) }}
                        </td>

                    </tr>

                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding:20px;">
                            No items in this order.
                        </td>
                    </tr>
                    @endforelse

                    @php
                    $totalRows = $items->count();
                    $emptyRows = max(0, 6 - $totalRows);
                    @endphp

                    @for($i = 0; $i < $emptyRows; $i++)

                        <tr style="height:32px;">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        </tr>

                    @endfor

                </tbody>

            </table>

        </div>

        <!-- Totals -->
        <div class="totals-section">

            <!-- Right Side - Totals Table -->
            <div class="totals-right">

                <table class="totals-table">

                    <tr>
                        <td>Price</td>
                        <td>Rs. {{ number_format($order->subtotal, 2) }}</td>
                    </tr>

                    @if((float) $order->discount > 0)
                    <tr>
                        <td>Discount</td>
                        <td style="color: #28a745;">− Rs. {{ number_format($order->discount, 2) }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td>Shipping</td>
                        <td>Rs. {{ number_format($order->shipping, 2) }}</td>
                    </tr>

                    <tr class="paid {{ $order->payment_status === 'paid' ? '' : 'pending' }}">
                        <td>Payment Status</td>
                        <td>
                            @if($order->payment_status === 'paid')
                                PAID
                            @elseif($order->payment_status === 'refunded')
                                REFUNDED
                            @elseif($order->payment_status === 'failed')
                                FAILED
                            @else
                                PENDING
                            @endif
                        </td>
                    </tr>

                    @if((float) $order->refunded_amount > 0)
                    <tr class="refund">
                        <td>Less: Refund</td>
                        <td>- Rs. {{ number_format($order->refunded_amount, 2) }}</td>
                    </tr>
                    @endif

                    <tr class="total">
                        <td>Total Rs.</td>
                        <td>
                            Rs. {{ number_format($netTotal, 2) }}
                        </td>
                    </tr>

                </table>

            </div>

            <div style="clear: both;"></div>

        </div>

        <!-- Signature -->
        <div class="signature-section">

            <div class="signature-left">

                <div class="sign-line">
                    Receiver's Signature
                </div>

                <div class="footer-note">
                    Received the above services/items in good condition
                </div>

            </div>

            <div class="signature-right">

                <div class="sign-line">
                    For {{ $storeName }}
                </div>

                <div class="footer-note">
                    {{ now()->format('d/m/Y h:i A') }}
                </div>

            </div>

        </div>

        <!-- Fixed Bottom Bar -->
        <div class="bottom-red-bar"></div>

    </div>

</body>

</html>
