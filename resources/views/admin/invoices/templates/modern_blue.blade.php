@php
    $storeName = \App\Models\StoreSetting::getStoreName() ?: 'Nool & Crop';
    $storeAddress = \App\Models\StoreSetting::getValue('address', 'Tirupur, Tamil Nadu, India - 641602');
    $storeMobile = \App\Models\StoreSetting::getValue('mobile', '80560 81594');
    $storeEmail = \App\Models\StoreSetting::getValue('email', 'noolcrop@gmail.com');
    $storeGst = \App\Models\StoreSetting::getValue('gst_number', '33AAAAA0000A1Z5');
    $currency = \App\Models\StoreSetting::getCurrencySymbol() ?: '₹';
    $items = json_decode($invoice->invoice_json, true);
    if (!is_array($items) || !isset($items['items'])) {
        $itemsList = is_array($items) ? $items : [];
    } else {
        $itemsList = $items['items'] ?? [];
    }
    $orderCode = is_array($items) && isset($items['order_code']) ? $items['order_code'] : null;
    $payMethod = is_array($items) && isset($items['payment_method']) ? strtoupper($items['payment_method']) : 'ONLINE';
@endphp
<div class="template-wrapper template-modern-blue" style="font-family: Arial, Helvetica, sans-serif; color: #1e293b; background: #ffffff; padding: 25px; max-width: 820px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px;">

    <!-- Header Section -->
    <div style="display: table; width: 100%; border-bottom: 2px solid #0068e1; padding-bottom: 18px; margin-bottom: 20px;">
        <div style="display: table-cell; width: 60%; vertical-align: top;">
            <div style="font-size: 24px; font-weight: bold; color: #0068e1; text-transform: uppercase; letter-spacing: 0.5px;">{{ $storeName }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 5px; line-height: 1.5;">
                {{ $storeAddress }}<br>
                @if($storeMobile)<strong>Phone:</strong> {{ $storeMobile }} &nbsp;|&nbsp; @endif
                @if($storeEmail)<strong>Email:</strong> {{ $storeEmail }}<br> @endif
                @if($storeGst)<strong>GSTIN:</strong> {{ $storeGst }} @endif
            </div>
        </div>
        <div style="display: table-cell; width: 40%; text-align: right; vertical-align: top;">
            <div style="display: inline-block; background: #0068e1; color: #ffffff; font-size: 16px; font-weight: bold; padding: 6px 16px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px;">
                TAX INVOICE
            </div>
            <div style="margin-top: 10px; font-size: 12px; line-height: 1.6; color: #334155;">
                <strong>Invoice #:</strong> <span style="color: #0068e1; font-weight: bold;">{{ $invoice->invoice_number }}</span><br>
                @if($orderCode)<strong>Order Ref:</strong> #{{ $orderCode }}<br>@endif
                <strong>Date:</strong> {{ $invoice->created_at ? $invoice->created_at->format('d M Y') : date('d M Y') }}<br>
                <strong>Status:</strong> 
                @if($invoice->status == 1)
                    <span style="color: #16a34a; font-weight: bold;">● PAID</span>
                @elseif($invoice->status == 2)
                    <span style="color: #dc2626; font-weight: bold;">● CANCELLED</span>
                @else
                    <span style="color: #ea580c; font-weight: bold;">● PENDING</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Bill To / Customer Information Box -->
    <div style="display: table; width: 100%; margin-bottom: 22px;">
        <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 12px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px;">
                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #0068e1; margin-bottom: 6px;">Billed To (Customer):</div>
                <div style="font-size: 13px; font-weight: bold; color: #0f172a;">{{ $invoice->customer_name }}</div>
                <div style="font-size: 11px; color: #475569; line-height: 1.5; margin-top: 4px;">
                    @if($invoice->customer_address){{ $invoice->customer_address }}<br>@endif
                    @if($invoice->customer_phone)<strong>Phone:</strong> {{ $invoice->customer_phone }}<br>@endif
                    @if($invoice->customer_email)<strong>Email:</strong> {{ $invoice->customer_email }}<br>@endif
                    @if($invoice->customer_gst)<strong>Buyer GSTIN:</strong> {{ $invoice->customer_gst }}@endif
                </div>
            </div>
        </div>
        <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 12px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px;">
                <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #0068e1; margin-bottom: 6px;">Payment & Delivery Details:</div>
                <div style="font-size: 11px; color: #475569; line-height: 1.6;">
                    <strong>Payment Mode:</strong> {{ $payMethod }}<br>
                    <strong>Currency:</strong> INR ({{ $currency }})<br>
                    <strong>Dispatch Hub:</strong> Tirupur Central Dispatch Hub<br>
                    <strong>Terms:</strong> Standard 7 Days Return Policy
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px;">
        <thead>
            <tr style="background: #0068e1; color: #ffffff;">
                <th style="padding: 10px 12px; text-align: center; width: 35px; border-top-left-radius: 4px;">#</th>
                <th style="padding: 10px 12px; text-align: left;">Item Description</th>
                <th style="padding: 10px 12px; text-align: center; width: 80px;">HSN/SAC</th>
                <th style="padding: 10px 12px; text-align: right; width: 80px;">Price</th>
                <th style="padding: 10px 12px; text-align: center; width: 50px;">Qty</th>
                <th style="padding: 10px 12px; text-align: right; width: 95px; border-top-right-radius: 4px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsList as $idx => $it)
                @php
                    $itQty = $it['quantity'] ?? ($it['qty'] ?? 1);
                    $itPrice = (float) ($it['price'] ?? 0);
                    $itTotal = (float) ($it['total'] ?? ($itPrice * $itQty));
                    $itHsn = $it['hsn'] ?? '610910';
                    $itName = $it['name'] ?? ($it['description'] ?? 'Product Item');
                @endphp
                <tr style="border-bottom: 1px solid #e2e8f0; background: {{ $idx % 2 === 0 ? '#ffffff' : '#f8fafc' }};">
                    <td style="padding: 10px 12px; text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="padding: 10px 12px; font-weight: 500; color: #0f172a;">{{ $itName }}</td>
                    <td style="padding: 10px 12px; text-align: center; color: #64748b;">{{ $itHsn }}</td>
                    <td style="padding: 10px 12px; text-align: right;">{{ $currency }}{{ number_format($itPrice, 2) }}</td>
                    <td style="padding: 10px 12px; text-align: center; font-weight: bold;">{{ $itQty }}</td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: bold; color: #0f172a;">{{ $currency }}{{ number_format($itTotal, 2) }}</td>
                </tr>
            @empty
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 12px; text-align: center;">1</td>
                    <td style="padding: 10px 12px; font-weight: 500;">General Store Order Items</td>
                    <td style="padding: 10px 12px; text-align: center;">610910</td>
                    <td style="padding: 10px 12px; text-align: right;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
                    <td style="padding: 10px 12px; text-align: center;">1</td>
                    <td style="padding: 10px 12px; text-align: right; font-weight: bold;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals Section -->
    <div style="display: table; width: 100%; margin-top: 10px;">
        <div style="display: table-cell; width: 55%; vertical-align: top; padding-right: 20px;">
            <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 11px; line-height: 1.5; color: #475569;">
                <strong>Terms &amp; Conditions:</strong><br>
                1. Goods once sold are covered under 7-day hassle-free exchange policy.<br>
                2. Please quote Invoice #<strong>{{ $invoice->invoice_number }}</strong> for any order assistance.<br>
                3. Inquiries? Call <strong>{{ $storeMobile }}</strong> or email <strong>{{ $storeEmail }}</strong>.
            </div>
        </div>
        <div style="display: table-cell; width: 45%; vertical-align: top;">
            <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; color: #64748b; text-align: right;">Subtotal:</td>
                    <td style="padding: 5px 0; font-weight: bold; text-align: right; width: 100px;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->other_charges > 0)
                <tr>
                    <td style="padding: 5px 0; color: #64748b; text-align: right;">Shipping &amp; Handling:</td>
                    <td style="padding: 5px 0; font-weight: bold; text-align: right;">{{ $currency }}{{ number_format($invoice->other_charges, 2) }}</td>
                </tr>
                @else
                <tr>
                    <td style="padding: 5px 0; color: #64748b; text-align: right;">Shipping:</td>
                    <td style="padding: 5px 0; font-weight: bold; color: #16a34a; text-align: right;">FREE</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td style="padding: 5px 0; color: #64748b; text-align: right;">Taxes (GST Incl.):</td>
                    <td style="padding: 5px 0; font-weight: bold; text-align: right;">{{ $currency }}{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #0068e1;">
                    <td style="padding: 10px 0; font-size: 15px; font-weight: bold; color: #0068e1; text-align: right;">Grand Total:</td>
                    <td style="padding: 10px 0; font-size: 16px; font-weight: bold; color: #0068e1; text-align: right;">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Signatory / Footer -->
    <div style="margin-top: 28px; border-top: 1px solid #e2e8f0; padding-top: 16px; display: table; width: 100%;">
        <div style="display: table-cell; width: 60%; vertical-align: middle; font-size: 10px; color: #94a3b8;">
            Generated on {{ date('d M Y, h:i A') }} • Computer-generated invoice, no signature required.
        </div>
        <div style="display: table-cell; width: 40%; text-align: right; vertical-align: middle;">
            <div style="font-size: 11px; font-weight: bold; color: #0f172a;">For {{ $storeName }}</div>
            <div style="font-size: 10px; color: #64748b; margin-top: 25px;">Authorized Signatory</div>
        </div>
    </div>

</div>
