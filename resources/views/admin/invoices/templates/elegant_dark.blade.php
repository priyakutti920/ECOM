@php
    $storeName = \App\Models\StoreSetting::getStoreName() ?: 'Nool & Crop';
    $storeAddress = \App\Models\StoreSetting::getValue('address', 'Tirupur, Tamil Nadu, India - 641602');
    $storeMobile = \App\Models\StoreSetting::getValue('mobile', '80560 81594');
    $storeEmail = \App\Models\StoreSetting::getValue('email', 'noolcrop@gmail.com');
    $currency = \App\Models\StoreSetting::getCurrencySymbol() ?: '₹';
    $items = json_decode($invoice->invoice_json, true);
    if (!is_array($items) || !isset($items['items'])) {
        $itemsList = is_array($items) ? $items : [];
    } else {
        $itemsList = $items['items'] ?? [];
    }
    $orderCode = is_array($items) && isset($items['order_code']) ? $items['order_code'] : null;
@endphp
<div class="template-wrapper template-elegant-dark" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #18181b; background: #ffffff; padding: 32px; max-width: 820px; margin: 0 auto; border: 1px solid #d4d4d8;">

    <!-- Top Dark Minimalist Header -->
    <div style="background: #18181b; color: #ffffff; padding: 22px 28px; border-radius: 4px; margin-bottom: 28px;">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 65%; vertical-align: middle;">
                <div style="font-size: 26px; font-weight: 300; letter-spacing: 3px; text-transform: uppercase;">{{ $storeName }}</div>
                <div style="font-size: 11px; color: #a1a1aa; letter-spacing: 1px; margin-top: 4px;">COTTON APPAREL &amp; ESSENTIALS</div>
            </div>
            <div style="display: table-cell; width: 35%; text-align: right; vertical-align: middle;">
                <div style="font-size: 18px; font-weight: 300; letter-spacing: 2px;">INVOICE</div>
                <div style="font-size: 12px; color: #e4e4e7; margin-top: 4px;">#{{ $invoice->invoice_number }}</div>
            </div>
        </div>
    </div>

    <!-- Info Columns -->
    <div style="display: table; width: 100%; margin-bottom: 28px; font-size: 11px; line-height: 1.6;">
        <div style="display: table-cell; width: 33%; vertical-align: top;">
            <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; color: #71717a; letter-spacing: 1px; margin-bottom: 6px;">DISPATCHED FROM</div>
            <div style="font-weight: 600;">{{ $storeName }}</div>
            <div style="color: #52525b;">{{ $storeAddress }}<br>{{ $storeMobile }}<br>{{ $storeEmail }}</div>
        </div>
        <div style="display: table-cell; width: 34%; vertical-align: top; padding: 0 10px;">
            <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; color: #71717a; letter-spacing: 1px; margin-bottom: 6px;">DELIVERY TO</div>
            <div style="font-weight: 600;">{{ $invoice->customer_name }}</div>
            <div style="color: #52525b;">
                {{ $invoice->customer_address ?: 'Standard Customer Address' }}<br>
                {{ $invoice->customer_phone ?: '' }}<br>
                {{ $invoice->customer_email ?: '' }}
            </div>
        </div>
        <div style="display: table-cell; width: 33%; vertical-align: top; text-align: right;">
            <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; color: #71717a; letter-spacing: 1px; margin-bottom: 6px;">INVOICE DETAILS</div>
            <div><strong>Date:</strong> {{ $invoice->created_at ? $invoice->created_at->format('M d, Y') : date('M d, Y') }}</div>
            @if($orderCode)<div><strong>Order ID:</strong> #{{ $orderCode }}</div>@endif
            <div><strong>Payment:</strong> {{ $invoice->status == 1 ? 'PAID IN FULL' : 'PENDING' }}</div>
        </div>
    </div>

    <!-- Items Table with Minimalist Hairlines -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 11px;">
        <thead>
            <tr style="border-bottom: 2px solid #18181b;">
                <th style="padding: 10px 4px; text-align: left; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #71717a; width: 30px;">#</th>
                <th style="padding: 10px 10px; text-align: left; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #71717a;">Item</th>
                <th style="padding: 10px 10px; text-align: center; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #71717a; width: 60px;">Qty</th>
                <th style="padding: 10px 10px; text-align: right; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #71717a; width: 90px;">Rate</th>
                <th style="padding: 10px 10px; text-align: right; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #71717a; width: 100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsList as $idx => $it)
                @php
                    $qty = $it['quantity'] ?? ($it['qty'] ?? 1);
                    $price = (float) ($it['price'] ?? 0);
                    $total = (float) ($it['total'] ?? ($price * $qty));
                    $name = $it['name'] ?? ($it['description'] ?? 'Curated Essential');
                @endphp
                <tr style="border-bottom: 1px solid #f4f4f5;">
                    <td style="padding: 12px 4px; color: #a1a1aa;">{{ sprintf('%02d', $idx + 1) }}</td>
                    <td style="padding: 12px 10px; font-weight: 500;">{{ $name }}</td>
                    <td style="padding: 12px 10px; text-align: center;">{{ $qty }}</td>
                    <td style="padding: 12px 10px; text-align: right; color: #52525b;">{{ $currency }}{{ number_format($price, 2) }}</td>
                    <td style="padding: 12px 10px; text-align: right; font-weight: 600;">{{ $currency }}{{ number_format($total, 2) }}</td>
                </tr>
            @empty
                <tr style="border-bottom: 1px solid #f4f4f5;">
                    <td style="padding: 12px 4px;">01</td>
                    <td style="padding: 12px 10px; font-weight: 500;">Bespoke Merchandise Order</td>
                    <td style="padding: 12px 10px; text-align: center;">1</td>
                    <td style="padding: 12px 10px; text-align: right;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
                    <td style="padding: 12px 10px; text-align: right; font-weight: 600;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals Area -->
    <div style="display: table; width: 100%; border-top: 1px solid #18181b; padding-top: 16px;">
        <div style="display: table-cell; width: 60%; vertical-align: top; padding-right: 20px;">
            <div style="font-size: 10px; letter-spacing: 1px; text-transform: uppercase; color: #71717a; margin-bottom: 4px;">NOTE FROM STORE</div>
            <div style="font-size: 11px; color: #52525b; line-height: 1.6;">
                Each garment is carefully inspected before dispatch. If you need any adjustments or size assistance, please reach out to our team within 7 days.
            </div>
        </div>
        <div style="display: table-cell; width: 40%; vertical-align: top;">
            <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px 0; color: #71717a;">Subtotal</td>
                    <td style="padding: 4px 0; text-align: right;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #71717a;">Shipping</td>
                    <td style="padding: 4px 0; text-align: right;">{{ $invoice->other_charges > 0 ? $currency . number_format($invoice->other_charges, 2) : 'COMPLIMENTARY' }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td style="padding: 4px 0; color: #71717a;">Tax Included</td>
                    <td style="padding: 4px 0; text-align: right;">{{ $currency }}{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #18181b;">
                    <td style="padding: 10px 0; font-size: 13px; font-weight: bold; letter-spacing: 0.5px;">TOTAL AMOUNT</td>
                    <td style="padding: 10px 0; font-size: 15px; font-weight: bold; text-align: right;">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Bottom Signature -->
    <div style="margin-top: 35px; text-align: center; border-top: 1px dashed #e4e4e7; padding-top: 16px; font-size: 10px; color: #a1a1aa; letter-spacing: 1px;">
        THANK YOU FOR SHOPPING WITH NOOL &amp; CROP • TIRUPUR, INDIA
    </div>

</div>
