@php
    $templateId = $templateId ?? ($invoice->template_id ?: 1);
    $template = \App\Models\BillingTemplate::find($templateId);
    $templateJson = $template ? json_decode($template->template_json, true) : null;
    $templateKey = $templateJson['key'] ?? ($templateId == 2 ? 'gst_tax' : ($templateId == 3 ? 'elegant_dark' : ($templateId == 4 ? 'thermal_pos' : 'modern_blue')));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        @if($templateKey === 'thermal_pos')
            @page { size: 80mm 200mm; margin: 4mm; }
            body { font-family: 'Courier New', monospace; font-size: 10px; color: #000; background: #fff; }
        @else
            @page { size: A4 portrait; margin: 10mm; }
            body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }
        @endif
        .template-wrapper { width: 100% !important; max-width: 100% !important; border: none !important; padding: 0 !important; }
    </style>
</head>
<body>
    @if($templateKey === 'gst_tax')
        @include('admin.invoices.templates.gst_tax')
    @elseif($templateKey === 'elegant_dark')
        @include('admin.invoices.templates.elegant_dark')
    @elseif($templateKey === 'thermal_pos')
        @include('admin.invoices.templates.thermal_pos')
    @else
        @include('admin.invoices.templates.modern_blue')
    @endif
</body>
</html>
