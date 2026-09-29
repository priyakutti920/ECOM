@php
    $storeName = \App\Models\StoreSetting::getStoreName() ?: 'Nool & Crop';
    $storeAddress = \App\Models\StoreSetting::getValue('address', 'Tirupur, Tamil Nadu - 641602');
    $storeMobile = \App\Models\StoreSetting::getValue('mobile', '80560 81594');
    $storeGst = \App\Models\StoreSetting::getValue('gst_number', '33AAAAA0000A1Z5');
    $currency = \App\Models\StoreSetting::getCurrencySymbol() ?: '₹';
    $items = json_decode($invoice->invoice_json, true);
    if (!is_array($items) || !isset($items['items'])) {
        $itemsList = is_array($items) ? $items : [];
    } else {
        $itemsList = $items['items'] ?? [];
    }
    $orderCode = is_array($items) && isset($items['order_code']) ? $items['order_code'] : null;
@endphp
<div class="template-wrapper template-thermal-pos" style="font-family: 'Courier New', Courier, monospace; color: #000000; background: #ffffff; padding: 18px 12px; max-width: 340px; margin: 0 auto; border: 1px dashed #71717a; font-size: 11px; line-height: 1.35;">

    <!-- Center Store Name -->
    <div style="text-align: center; margin-bottom: 8px;">
        <div style="font-size: 16px; font-weight: bold; letter-spacing: 1px;">*** {{ strtoupper($storeName) }} ***</div>
        <div style="font-size: 10px;">{{ $storeAddress }}</div>
        <div style="font-size: 10px;">Ph: {{ $storeMobile }}</div>
        @if($storeGst)<div style="font-size: 10px;">GSTIN: {{ $storeGst }}</div>@endif
    </div>

    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

    <!-- Bill Details -->
    <div style="font-size: 10px; margin-bottom: 6px;">
        <div><strong>INV NO:</strong> {{ $invoice->invoice_number }}</div>
        @if($orderCode)<div><strong>ORD NO:</strong> #{{ $orderCode }}</div>@endif
        <div><strong>DATE  :</strong> {{ $invoice->created_at ? $invoice->created_at->format('d/m/Y H:i') : date('d/m/Y H:i') }}</div>
        <div><strong>CUST  :</strong> {{ strtoupper($invoice->customer_name) }}</div>
        @if($invoice->customer_phone)<div><strong>PHONE :</strong> {{ $invoice->customer_phone }}</div>@endif
    </div>

    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

    <!-- Items List -->
    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left; padding: 4px 0;">ITEM</th>
                <th style="text-align: center; width: 30px; padding: 4px 0;">QTY</th>
                <th style="text-align: right; width: 50px; padding: 4px 0;">RATE</th>
                <th style="text-align: right; width: 55px; padding: 4px 0;">AMT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsList as $idx => $it)
                @php
                    $qty = $it['quantity'] ?? ($it['qty'] ?? 1);
                    $price = (float) ($it['price'] ?? 0);
                    $total = (float) ($it['total'] ?? ($price * $qty));
                    $name = $it['name'] ?? ($it['description'] ?? 'Item #' . ($idx + 1));
                @endphp
                <tr>
                    <td colspan="4" style="padding-top: 4px; font-weight: bold;">{{ $name }}</td>
                </tr>
                <tr style="border-bottom: 1px dotted #ccc;">
                    <td style="color: #666; font-size: 9px;">&nbsp;&nbsp;HSN: {{ $it['hsn'] ?? '610910' }}</td>
                    <td style="text-align: center;">{{ $qty }}</td>
                    <td style="text-align: right;">{{ number_format($price, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding: 4px 0;">Standard Apparel Items</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

    <!-- Summary -->
    <table style="width: 100%; font-size: 11px;">
        <tr>
            <td style="padding: 2px 0;">Sub Total:</td>
            <td style="text-align: right; font-weight: bold;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 2px 0;">Shipping:</td>
            <td style="text-align: right;">{{ $invoice->other_charges > 0 ? $currency . number_format($invoice->other_charges, 2) : '0.00' }}</td>
        </tr>
        @if($invoice->tax_amount > 0)
        <tr>
            <td style="padding: 2px 0;">GST (Incl.):</td>
            <td style="text-align: right;">{{ $currency }}{{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        @endif
        <tr style="border-top: 1px solid #000; font-size: 13px;">
            <td style="padding: 6px 0; font-weight: bold;">NET PAYABLE:</td>
            <td style="padding: 6px 0; text-align: right; font-weight: bold;">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

    <!-- Barcode simulation & footer -->
    <div style="text-align: center; margin-top: 10px;">
        <div style="font-family: monospace; font-size: 20px; letter-spacing: 4px; font-weight: bold;">||| | ||||| | |||| ||</div>
        <div style="font-size: 9px; margin-top: 4px;">{{ $invoice->invoice_number }}</div>
        <div style="font-size: 10px; margin-top: 8px; font-weight: bold;">THANK YOU FOR YOUR VISIT!</div>
        <div style="font-size: 9px; color: #555;">Exchange within 7 days with bill.</div>
    </div>

</div>
