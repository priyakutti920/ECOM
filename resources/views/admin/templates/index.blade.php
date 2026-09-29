@extends('layouts.admin')

@section('content')
<div class="admin-content">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #1e293b;">
                <i class="fas fa-palette" style="color: #0068e1;"></i> Invoice Template Chooser &amp; Manager
            </h2>
            <div style="color: #64748b; font-size: 13px; margin-top: 3px;">
                Select, preview, and configure your default invoice templates for online bills and printable PDFs.
            </div>
        </div>
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-default" style="font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Back to Invoices
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Templates Grid --}}
    <div class="row">
        @php
            $defaultTemplateId = \App\Models\StoreSetting::getValue('default_invoice_template', 1);
            $sampleInvoice = \App\Models\Invoice::first();
        @endphp

        @foreach($templates as $tpl)
            @php
                $tData = json_decode($tpl->template_json, true) ?: [];
                $key = $tData['key'] ?? 'modern_blue';
                $badge = $tData['badge'] ?? 'Standard';
                $desc = $tData['description'] ?? 'Professional invoice layout.';
                $pColor = $tData['primary_color'] ?? '#0068e1';
                $isDefault = ($defaultTemplateId == $tpl->id);
            @endphp
            <div class="col-md-6 col-lg-3" style="margin-bottom: 24px;">
                <div style="background: #ffffff; border-radius: 10px; border: 2px solid {{ $isDefault ? '#0068e1' : '#e2e8f0' }}; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); height: 100%; display: flex; flex-direction: column; position: relative;">
                    
                    @if($isDefault)
                        <div style="position: absolute; top: 12px; right: 12px; background: #0068e1; color: #ffffff; font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 100px; letter-spacing: 0.5px; z-index: 10;">
                            ★ Default Active
                        </div>
                    @endif

                    <!-- Template Visual Header Card -->
                    <div style="height: 140px; background: {{ $pColor }}; color: #ffffff; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -15px; bottom: -15px; font-size: 90px; opacity: 0.15; color: #ffffff;">
                            @if($key === 'gst_tax')
                                <i class="fas fa-landmark"></i>
                            @elseif($key === 'elegant_dark')
                                <i class="fas fa-crown"></i>
                            @elseif($key === 'thermal_pos')
                                <i class="fas fa-receipt"></i>
                            @else
                                <i class="fas fa-file-invoice"></i>
                            @endif
                        </div>
                        <div>
                            <span style="display: inline-block; background: rgba(255,255,255,0.2); font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; letter-spacing: 0.5px;">
                                {{ $badge }}
                            </span>
                        </div>
                        <div>
                            <div style="font-size: 17px; font-weight: 700; line-height: 1.3;">{{ $tpl->template_name }}</div>
                            <div style="font-size: 11px; opacity: 0.85; margin-top: 2px;">Key: <code>{{ $key }}</code></div>
                        </div>
                    </div>

                    <!-- Template Details Body -->
                    <div style="padding: 18px; flex: 1; display: flex; flex-direction: column;">
                        <p style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 16px; flex: 1;">
                            {{ $desc }}
                        </p>

                        <!-- Features highlights -->
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 16px; line-height: 1.6;">
                            @if($key === 'gst_tax')
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> CGST + SGST + IGST Rate Tables</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> HSN/SAC Code &amp; Place of Supply</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Bank Details for NEFT/RTGS</div>
                            @elseif($key === 'elegant_dark')
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Luxury High-Contrast Dark Bar</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Minimalist Hairline Borders</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Designer Apparel Aesthetic</div>
                            @elseif($key === 'thermal_pos')
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> 80mm Receipt Roll Sizing</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> High-speed Counter Printing</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Monospaced Font &amp; Barcode</div>
                            @else
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Crisp Electric Blue Brand Accent</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Standard Corporate Grid Table</div>
                                <div><i class="fas fa-check-circle" style="color: #16a34a;"></i> Print &amp; PDF A4 Optimized</div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @if($sampleInvoice)
                                <a href="{{ route('admin.invoices.show', ['id' => $sampleInvoice->id, 'template' => $tpl->id]) }}" class="btn btn-default btn-sm" style="font-weight: 600; text-align: center;">
                                    <i class="fas fa-eye"></i> Preview Sample Invoice
                                </a>
                            @endif

                            @if(!$isDefault)
                                <form method="POST" action="{{ route('admin.templates.set-default') }}">
                                    @csrf
                                    <input type="hidden" name="template_id" value="{{ $tpl->id }}">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block" style="font-weight: 600;">
                                        <i class="fas fa-check"></i> Set as Default Template
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-success btn-sm btn-block" disabled style="font-weight: 600;">
                                    <i class="fas fa-check-double"></i> Current Default
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

    {{-- Store Billing & GST Settings Card --}}
    <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 22px; margin-top: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div>
                <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">
                    <i class="fas fa-cogs" style="color: #0068e1;"></i> Store Invoice &amp; GST Details
                </h4>
                <div style="font-size: 13px; color: #64748b; margin-top: 3px;">
                    These business details are automatically rendered on all invoice templates, printouts, and downloaded PDFs.
                </div>
            </div>
            <div>
                <a href="{{ route('admin.settings.store') }}" class="btn btn-primary" style="font-weight: 600;">
                    <i class="fas fa-sliders-h"></i> Manage Store Settings
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3" style="margin-bottom: 10px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;">Store Brand Name</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 4px;">{{ \App\Models\StoreSetting::getStoreName() ?: 'Nool & Crop' }}</div>
                </div>
            </div>
            <div class="col-md-3" style="margin-bottom: 10px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;">Store GSTIN</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0068e1; margin-top: 4px;">{{ \App\Models\StoreSetting::getValue('gst_number', '33AAAAA0000A1Z5') }}</div>
                </div>
            </div>
            <div class="col-md-3" style="margin-bottom: 10px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;">Place of Supply / State</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 4px;">Tamil Nadu (Code 33)</div>
                </div>
            </div>
            <div class="col-md-3" style="margin-bottom: 10px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b;">Active Default Template</div>
                    <div style="font-size: 14px; font-weight: 700; color: #16a34a; margin-top: 4px;">
                        {{ $templates->firstWhere('id', $defaultTemplateId)?->template_name ?? 'Modern Corporate' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
