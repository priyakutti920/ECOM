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

    // GST calculations (5% standard apparel)
    $taxRate = 5;
    $cgstRate = 2.5;
    $sgstRate = 2.5;
    $taxableTotal = $invoice->subtotal / (1 + ($taxRate / 100));
    $totalTax = $invoice->subtotal - $taxableTotal;
    $cgstTotal = $totalTax / 2;
    $sgstTotal = $totalTax / 2;
@endphp
<div class="template-wrapper template-gst-tax" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #111827; background: #ffffff; padding: 25px; max-width: 820px; margin: 0 auto; border: 1px solid #111827;">

    <!-- Top GST Banner -->
    <div style="text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px;">
        <div style="font-size: 16px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;">TAX INVOICE</div>
        <div style="font-size: 10px; color: #4b5563;">(Issued under Section 31 of Central Goods and Services Tax Act, 2017)</div>
    </div>

    <!-- Seller and Invoice Meta Grid -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11px; border: 1px solid #111827;">
        <tr>
            <td style="width: 55%; padding: 10px; border-right: 1px solid #111827; vertical-align: top;">
                <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px;">{{ $storeName }}</div>
                <div style="line-height: 1.45; color: #374151;">
                    {{ $storeAddress }}<br>
                    <strong>GSTIN:</strong> {{ $storeGst }}<br>
                    <strong>State:</strong> Tamil Nadu (State Code: 33)<br>
                    <strong>Contact:</strong> {{ $storeMobile }} | {{ $storeEmail }}
                </div>
            </td>
            <td style="width: 45%; padding: 10px; vertical-align: top; line-height: 1.6;">
                <strong>Invoice Number:</strong> {{ $invoice->invoice_number }}<br>
                <strong>Invoice Date:</strong> {{ $invoice->created_at ? $invoice->created_at->format('d/m/Y') : date('d/m/Y') }}<br>
                @if($orderCode)<strong>Order Number:</strong> #{{ $orderCode }}<br>@endif
                <strong>Reverse Charge:</strong> No<br>
                <strong>Place of Supply:</strong> Tamil Nadu (33)
            </td>
        </tr>
    </table>

    <!-- Buyer Details Box -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11px; border: 1px solid #111827;">
        <tr style="background: #f3f4f6;">
            <th style="padding: 6px 10px; text-align: left; font-size: 11px; font-weight: bold; border-bottom: 1px solid #111827;">
                DETAILS OF RECEIVER / BILLED TO:
            </th>
        </tr>
        <tr>
            <td style="padding: 10px; line-height: 1.5;">
                <div style="font-size: 13px; font-weight: bold;">{{ $invoice->customer_name }}</div>
                <div>{{ $invoice->customer_address ?: 'Same as delivery address' }}</div>
                <div><strong>Mobile:</strong> {{ $invoice->customer_phone ?: '—' }} &nbsp;|&nbsp; <strong>Email:</strong> {{ $invoice->customer_email ?: '—' }}</div>
                <div><strong>Buyer GSTIN:</strong> {{ $invoice->customer_gst ?: 'Unregistered Consumer' }}</div>
            </td>
        </tr>
    </table>

    <!-- Items & GST Breakdown Table -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; border: 1px solid #111827;">
        <thead>
            <tr style="background: #e5e7eb; border-bottom: 1px solid #111827;">
                <th style="padding: 8px 6px; text-align: center; border-right: 1px solid #111827; width: 25px;">S.N</th>
                <th style="padding: 8px 8px; text-align: left; border-right: 1px solid #111827;">Description of Goods</th>
                <th style="padding: 8px 6px; text-align: center; border-right: 1px solid #111827; width: 60px;">HSN</th>
                <th style="padding: 8px 6px; text-align: center; border-right: 1px solid #111827; width: 40px;">Qty</th>
                <th style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827; width: 70px;">Rate</th>
                <th style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827; width: 75px;">Taxable</th>
                <th style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827; width: 65px;">CGST (2.5%)</th>
                <th style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827; width: 65px;">SGST (2.5%)</th>
                <th style="padding: 8px 8px; text-align: right; width: 80px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsList as $idx => $it)
                @php
                    $qty = $it['quantity'] ?? ($it['qty'] ?? 1);
                    $price = (float) ($it['price'] ?? 0);
                    $lineTotal = (float) ($it['total'] ?? ($price * $qty));
                    $taxableLine = $lineTotal / 1.05;
                    $taxLine = $lineTotal - $taxableLine;
                    $cgstLine = $taxLine / 2;
                    $sgstLine = $taxLine / 2;
                    $hsn = $it['hsn'] ?? '61091000';
                    $name = $it['name'] ?? ($it['description'] ?? 'Cotton Apparel Item');
                @endphp
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 6px; text-align: center; border-right: 1px solid #111827;">{{ $idx + 1 }}</td>
                    <td style="padding: 6px 8px; border-right: 1px solid #111827; font-weight: 500;">{{ $name }}</td>
                    <td style="padding: 6px; text-align: center; border-right: 1px solid #111827;">{{ $hsn }}</td>
                    <td style="padding: 6px; text-align: center; border-right: 1px solid #111827;">{{ $qty }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($price, 2) }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($taxableLine, 2) }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($cgstLine, 2) }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($sgstLine, 2) }}</td>
                    <td style="padding: 6px 8px; text-align: right; font-weight: bold;">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 6px; text-align: center; border-right: 1px solid #111827;">1</td>
                    <td style="padding: 6px 8px; border-right: 1px solid #111827;">Standard Store Items</td>
                    <td style="padding: 6px; text-align: center; border-right: 1px solid #111827;">61091000</td>
                    <td style="padding: 6px; text-align: center; border-right: 1px solid #111827;">1</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($invoice->subtotal, 2) }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($taxableTotal, 2) }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($cgstTotal, 2) }}</td>
                    <td style="padding: 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($sgstTotal, 2) }}</td>
                    <td style="padding: 6px 8px; text-align: right; font-weight: bold;">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f9fafb; font-weight: bold; border-top: 1px solid #111827;">
                <td colspan="5" style="padding: 8px; text-align: right; border-right: 1px solid #111827;">Total:</td>
                <td style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($taxableTotal, 2) }}</td>
                <td style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($cgstTotal, 2) }}</td>
                <td style="padding: 8px 6px; text-align: right; border-right: 1px solid #111827;">{{ number_format($sgstTotal, 2) }}</td>
                <td style="padding: 8px 8px; text-align: right;">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Final Summary & Bank Details -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11px; border: 1px solid #111827;">
        <tr>
            <td style="width: 55%; padding: 10px; border-right: 1px solid #111827; vertical-align: top;">
                <strong>Bank Account Details for NEFT/RTGS:</strong><br>
                <div style="margin-top: 4px; line-height: 1.5; color: #374151;">
                    Bank Name: HDFC Bank Ltd.<br>
                    Account Name: {{ $storeName }}<br>
                    Account No.: 50200088992211<br>
                    IFSC Code: HDFC0001234 (Branch: Tirupur Main)
                </div>
            </td>
            <td style="width: 45%; padding: 10px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <tr>
                        <td style="padding: 3px 0;">Taxable Amount:</td>
                        <td style="padding: 3px 0; text-align: right; font-weight: bold;">{{ $currency }}{{ number_format($taxableTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0;">Total Tax (GST 5%):</td>
                        <td style="padding: 3px 0; text-align: right; font-weight: bold;">{{ $currency }}{{ number_format($totalTax, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 3px 0;">Shipping Charges:</td>
                        <td style="padding: 3px 0; text-align: right; font-weight: bold;">
                            {{ $invoice->other_charges > 0 ? $currency . number_format($invoice->other_charges, 2) : 'FREE' }}
                        </td>
                    </tr>
                    <tr style="border-top: 1px solid #111827; font-size: 13px;">
                        <td style="padding: 6px 0; font-weight: bold;">Invoice Total:</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold; color: #111827;">{{ $currency }}{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Legal Declarations & Signature -->
    <table style="width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #111827;">
        <tr>
            <td style="width: 60%; padding: 10px; border-right: 1px solid #111827; vertical-align: top; color: #4b5563; line-height: 1.45;">
                <strong>Declaration:</strong><br>
                We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
            </td>
            <td style="width: 40%; padding: 10px; text-align: center; vertical-align: middle;">
                <div style="font-weight: bold; margin-bottom: 25px;">For {{ $storeName }}</div>
                <div style="border-top: 1px dashed #6b7280; width: 140px; margin: 0 auto; padding-top: 4px;">Authorised Signatory</div>
            </td>
        </tr>
    </table>

</div>
