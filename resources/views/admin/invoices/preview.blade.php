@php
    $siteName = \App\Models\StoreSetting::getStoreName() ?: 'Nool & Crop';
    $currentTemplateId = request('template') ?: ($invoice->template_id ?: 1);
    $template = \App\Models\BillingTemplate::find($currentTemplateId);
    $templateJson = $template ? json_decode($template->template_json, true) : null;
    $templateKey = $templateJson['key'] ?? ($currentTemplateId == 2 ? 'gst_tax' : ($currentTemplateId == 3 ? 'elegant_dark' : ($currentTemplateId == 4 ? 'thermal_pos' : 'modern_blue')));
    $templates = \App\Models\BillingTemplate::where('status', 1)->get();
    $waPhone = preg_replace('/\D+/', '', $invoice->customer_phone ?: '');
    if (strlen($waPhone) === 10) $waPhone = '91' . $waPhone;
    $waMsg = urlencode("Hello {$invoice->customer_name}, here is your invoice #{$invoice->invoice_number} from {$siteName} for ₹" . number_format($invoice->total_amount, 2) . ". Thank you for your business!");
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text={$waMsg}" : "https://wa.me/?text={$waMsg}";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} — {{ $siteName }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background: #f1f5f9; color: #1e293b; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        
        /* Top Action Bar */
        .preview-topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .template-chooser-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
        .template-chooser-select {
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #0068e1;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            cursor: pointer;
            outline: none;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 15px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .btn-back { background: #e2e8f0; color: #334155; }
        .btn-back:hover { background: #cbd5e1; }
        .btn-print { background: #0068e1; color: #ffffff; }
        .btn-print:hover { background: #0056b3; }
        .btn-pdf { background: #0f172a; color: #ffffff; }
        .btn-pdf:hover { background: #1e293b; }
        .btn-wa { background: #25d366; color: #ffffff; }
        .btn-wa:hover { background: #20ba5a; }

        .preview-canvas {
            padding: 30px 15px 60px;
        }

        @media print {
            .preview-topbar { display: none !important; }
            .preview-canvas { padding: 0 !important; }
            body { background: #ffffff !important; }
            .template-wrapper { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

    <!-- Top Sticky Action Bar -->
    <div class="preview-topbar">
        <div class="topbar-left">
            <a href="{{ route('admin.invoices.index') }}" class="btn-action btn-back">
                <i class="fas fa-arrow-left"></i> Invoices
            </a>
            <div class="topbar-title">Invoice #{{ $invoice->invoice_number }}</div>

            <!-- Template Chooser Dropdown -->
            <div class="template-chooser-wrap">
                <i class="fas fa-palette" style="color: #0068e1;"></i>
                <label for="template-select" style="font-size: 12px; font-weight: 600; color: #475569;">Choose Template:</label>
                <select id="template-select" class="template-chooser-select" onchange="changeTemplate(this.value)">
                    @foreach($templates as $t)
                        @php
                            $tData = json_decode($t->template_json, true);
                            $badge = $tData['badge'] ?? '';
                        @endphp
                        <option value="{{ $t->id }}" {{ $currentTemplateId == $t->id ? 'selected' : '' }}>
                            {{ $t->template_name }} {{ $badge ? '('.$badge.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="topbar-right">
            <a href="{{ $waUrl }}" target="_blank" class="btn-action btn-wa" title="Send invoice to customer via WhatsApp">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a href="{{ route('admin.invoices.pdf', ['id' => $invoice->id, 'template' => $currentTemplateId]) }}" class="btn-action btn-pdf" title="Download PDF invoice">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn-action btn-print" title="Print this invoice">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- Active Template Canvas Container -->
    <div class="preview-canvas">
        @if($templateKey === 'gst_tax')
            @include('admin.invoices.templates.gst_tax')
        @elseif($templateKey === 'elegant_dark')
            @include('admin.invoices.templates.elegant_dark')
        @elseif($templateKey === 'thermal_pos')
            @include('admin.invoices.templates.thermal_pos')
        @else
            @include('admin.invoices.templates.modern_blue')
        @endif
    </div>

    <script>
        function changeTemplate(templateId) {
            const url = new URL(window.location.href);
            url.searchParams.set('template', templateId);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
