@extends('layouts.admin')

@section('content')
<div class="admin-content">

    {{-- Page Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0; font-size: 22px; font-weight: 700; color: #1e293b;">
                <i class="fas fa-file-invoice-dollar" style="color: #0068e1;"></i> Invoice Manager
            </h2>
            <div style="color: #64748b; font-size: 13px; margin-top: 3px;">
                Manage customer bills, tax invoices, and switch between official invoice templates.
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.templates.index') }}" class="btn btn-default" style="font-weight: 600;">
                <i class="fas fa-palette" style="color: #0068e1;"></i> Invoice Templates
            </a>
            <button type="button" class="btn btn-info" onclick="openOrderInvoiceModal()" style="font-weight: 600;">
                <i class="fas fa-magic"></i> Generate from Order
            </button>
            <button type="button" class="btn btn-primary" onclick="openCreateInvoiceModal()" style="font-weight: 600;">
                <i class="fas fa-plus"></i> Create Invoice
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row" style="margin-bottom: 22px;">
        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
            <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Total Invoices</div>
                <div style="font-size: 24px; font-weight: 700; color: #0f172a; margin-top: 4px;">{{ $stats['total_count'] ?? $invoices->total() }}</div>
                <div style="font-size: 12px; color: #0068e1; font-weight: 600; margin-top: 2px;">₹{{ number_format($stats['total_amount'] ?? 0, 2) }} total billed</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
            <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #16a34a; letter-spacing: 0.5px;">Paid Invoices</div>
                <div style="font-size: 24px; font-weight: 700; color: #16a34a; margin-top: 4px;">{{ $stats['paid_count'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #16a34a; font-weight: 600; margin-top: 2px;">₹{{ number_format($stats['paid_amount'] ?? 0, 2) }} collected</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
            <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #ea580c; letter-spacing: 0.5px;">Pending / Unpaid</div>
                <div style="font-size: 24px; font-weight: 700; color: #ea580c; margin-top: 4px;">{{ $stats['pending_count'] ?? 0 }}</div>
                <div style="font-size: 12px; color: #ea580c; font-weight: 600; margin-top: 2px;">₹{{ number_format($stats['pending_amount'] ?? 0, 2) }} pending</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6" style="margin-bottom: 12px;">
            <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #6366f1; letter-spacing: 0.5px;">Available Templates</div>
                <div style="font-size: 24px; font-weight: 700; color: #6366f1; margin-top: 4px;">{{ $templates->count() }} Designs</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Modern, GST, Luxury, POS</div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 16px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="form-inline" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 220px;">
                <input type="text" name="search" class="form-control" style="width: 100%;" placeholder="Search by Invoice #, Customer, Phone..." value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <select name="template_id" class="form-control">
                    <option value="">All Templates</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}" {{ request('template_id') == $t->id ? 'selected' : '' }}>{{ $t->template_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-default" style="font-weight: 600;">
                <i class="fas fa-search"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'template_id']))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-link" style="color: #64748b;">Clear Filters</a>
            @endif
        </form>
    </div>

    {{-- Invoices Table Card --}}
    <div style="background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="table-responsive">
            <table class="table table-hover" style="margin: 0; font-size: 13px;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Invoice #</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Customer</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Date</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Template</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569;">Order Ref</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-align: right;">Total Amount</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-align: center;">Status</th>
                        <th style="padding: 12px 16px; font-weight: 700; color: #475569; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        @php
                            $invJson = json_decode($inv->invoice_json, true);
                            $orderRef = is_array($invJson) && isset($invJson['order_code']) ? $invJson['order_code'] : null;
                            $tName = $inv->template ? $inv->template->template_name : 'Modern Corporate (Default)';
                            $waPhone = preg_replace('/\D+/', '', $inv->customer_phone ?: '');
                            if (strlen($waPhone) === 10) $waPhone = '91' . $waPhone;
                            $waMsg = urlencode("Hello {$inv->customer_name}, here is your invoice #{$inv->invoice_number} from Nool & Crop for ₹" . number_format($inv->total_amount, 2) . ". Thank you!");
                            $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text={$waMsg}" : "https://wa.me/?text={$waMsg}";
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px 16px; font-weight: 700;">
                                <a href="{{ route('admin.invoices.show', $inv->id) }}" style="color: #0068e1;">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: 600; color: #0f172a;">{{ $inv->customer_name }}</div>
                                <div style="font-size: 11px; color: #64748b;">
                                    {{ $inv->customer_phone ?: $inv->customer_email ?: 'No contact' }}
                                </div>
                            </td>
                            <td style="padding: 14px 16px; color: #64748b;">
                                {{ $inv->created_at ? $inv->created_at->format('d M Y') : '—' }}
                            </td>
                            <td style="padding: 14px 16px;">
                                <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 600;">
                                    <i class="fas fa-palette"></i> {{ Str::limit($tName, 20) }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($orderRef)
                                    <a href="{{ url('admin/orders/' . $orderRef) }}" style="color: #0284c7; font-weight: 600;">
                                        #{{ $orderRef }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8;">Manual</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; text-align: right; font-weight: 700; color: #0f172a;">
                                ₹{{ number_format($inv->total_amount, 2) }}
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                @if($inv->status == 1)
                                    <span class="badge badge-success" style="padding: 4px 8px;">Paid</span>
                                @elseif($inv->status == 2)
                                    <span class="badge badge-danger" style="padding: 4px 8px;">Cancelled</span>
                                @else
                                    <span class="badge badge-warning" style="padding: 4px 8px;">Pending</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; text-align: right;">
                                <div class="btn-group">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}" class="btn btn-xs btn-default" title="View / Switch Template">
                                        <i class="fas fa-eye" style="color: #0068e1;"></i> View
                                    </a>
                                    <a href="{{ route('admin.invoices.pdf', $inv->id) }}" class="btn btn-xs btn-default" title="Download PDF">
                                        <i class="fas fa-file-pdf" style="color: #dc2626;"></i>
                                    </a>
                                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-xs btn-default" title="Send WhatsApp">
                                        <i class="fab fa-whatsapp" style="color: #25d366;"></i>
                                    </a>
                                    <button type="button" class="btn btn-xs btn-default" onclick="openEditInvoiceModal({{ $inv->id }})" title="Edit Invoice">
                                        <i class="fas fa-pencil-alt" style="color: #f59e0b;"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-default" onclick="deleteInvoice({{ $inv->id }})" title="Delete">
                                        <i class="fas fa-trash" style="color: #ef4444;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px 20px; color: #64748b;">
                                <i class="fas fa-file-invoice" style="font-size: 36px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                                <div style="font-size: 15px; font-weight: 600;">No invoices found.</div>
                                <div style="font-size: 12px; margin-top: 4px;">Click <strong>"Generate from Order"</strong> or <strong>"Create Invoice"</strong> to issue an invoice.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div style="padding: 12px 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 12px; color: #64748b;">
                    Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices
                </div>
                <div>
                    {{ $invoices->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL 1: Generate Invoice from Order --}}
<div class="modal fade" id="orderInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('admin.invoices.from-order') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background: #f8fafc;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="font-weight: 700; color: #0f172a;">
                        <i class="fas fa-magic" style="color: #0068e1;"></i> Generate Invoice From Order
                    </h4>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <div class="form-group">
                        <label>Select Customer Order:</label>
                        <select name="order_code" class="form-control" required>
                            <option value="">-- Choose an Order --</option>
                            @foreach($orders as $o)
                                <option value="{{ $o->order_code }}">
                                    #{{ $o->order_code }} — {{ $o->contact_name ?: $o->addr_full_name }} (₹{{ number_format($o->total, 2) }}) [{{ strtoupper($o->payment_status) }}]
                                </option>
                            @endforeach
                        </select>
                        <span class="help-block" style="font-size: 11px;">Customer information, item lines, GST, and shipping will be automatically populated.</span>
                    </div>

                    <div class="form-group">
                        <label>Choose Invoice Template:</label>
                        <select name="template_id" class="form-control" required>
                            @foreach($templates as $t)
                                @php
                                    $tData = json_decode($t->template_json, true);
                                    $badge = $tData['badge'] ?? '';
                                @endphp
                                <option value="{{ $t->id }}">
                                    {{ $t->template_name }} {{ $badge ? '('.$badge.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Initial Invoice Status:</label>
                        <select name="status" class="form-control">
                            <option value="1">Paid (Settled)</option>
                            <option value="0">Pending (Awaiting Payment)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                        <i class="fas fa-check"></i> Generate Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Create / Edit Manual Invoice --}}
<div class="modal fade" id="manualInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <form id="manualInvoiceForm" method="POST" action="{{ route('admin.invoices.save') }}">
            @csrf
            <input type="hidden" name="id" id="inv_id" value="">
            <input type="hidden" name="invoice_json" id="inv_json_field" value="">

            <div class="modal-content">
                <div class="modal-header" style="background: #f8fafc;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="manualInvoiceModalTitle" style="font-weight: 700; color: #0f172a;">
                        <i class="fas fa-plus-circle" style="color: #0068e1;"></i> Create Custom Invoice
                    </h4>
                </div>
                <div class="modal-body" style="padding: 20px;">

                    {{-- Customer details row --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" id="inv_customer_name" class="form-control" required placeholder="Full Name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" name="invoice_number" id="inv_number" class="form-control" required value="INV-{{ date('Y') }}-{{ strtoupper(substr(uniqid(), 8)) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Mobile Phone</label>
                                <input type="text" name="customer_phone" id="inv_customer_phone" class="form-control" placeholder="10-digit mobile">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Email</label>
                                <input type="email" name="customer_email" id="inv_customer_email" class="form-control" placeholder="email@domain.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Address</label>
                                <textarea name="customer_address" id="inv_customer_address" class="form-control" rows="2" placeholder="Full street address, city, state, pincode"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer GSTIN (Optional)</label>
                                <input type="text" name="customer_gst" id="inv_customer_gst" class="form-control" placeholder="e.g. 33AAAAA0000A1Z5">
                            </div>
                            <div class="form-group">
                                <label>Choose Invoice Template <span class="text-danger">*</span></label>
                                <select name="template_id" id="inv_template_id" class="form-control" required>
                                    @foreach($templates as $t)
                                        @php
                                            $tData = json_decode($t->template_json, true);
                                            $badge = $tData['badge'] ?? '';
                                        @endphp
                                        <option value="{{ $t->id }}">{{ $t->template_name }} {{ $badge ? '('.$badge.')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Itemization table --}}
                    <div style="margin-top: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <label style="margin: 0; font-weight: 700;">Invoice Items:</label>
                        <button type="button" class="btn btn-xs btn-primary" onclick="addInvoiceItemRow()">
                            <i class="fas fa-plus"></i> Add Item Row
                        </button>
                    </div>

                    <table class="table table-bordered" id="invoiceItemsTable" style="font-size: 12px;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th>Item Description</th>
                                <th style="width: 100px;">HSN/SAC</th>
                                <th style="width: 80px;">Qty</th>
                                <th style="width: 110px;">Unit Price (₹)</th>
                                <th style="width: 110px;">Line Total (₹)</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItemsTbody">
                            <tr>
                                <td><input type="text" class="form-control input-sm item-desc" placeholder="Product name" required value="Men's Cotton T-Shirt"></td>
                                <td><input type="text" class="form-control input-sm item-hsn" placeholder="610910" value="610910"></td>
                                <td><input type="number" class="form-control input-sm item-qty" value="1" min="1" onchange="calcInvoiceTotals()"></td>
                                <td><input type="number" class="form-control input-sm item-price" value="179.00" step="0.01" min="0" onchange="calcInvoiceTotals()"></td>
                                <td><input type="number" class="form-control input-sm item-total" value="179.00" readonly></td>
                                <td style="text-align: center;"><button type="button" class="btn btn-xs btn-danger" onclick="removeInvoiceItemRow(this)">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Totals calculation --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Status:</label>
                                <select name="status" id="inv_status" class="form-control">
                                    <option value="1">Paid (Received)</option>
                                    <option value="0">Pending (Unpaid)</option>
                                    <option value="2">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table style="width: 100%; font-size: 13px;">
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b;">Subtotal (₹):</td>
                                    <td style="padding: 4px 0; text-align: right;"><input type="number" name="subtotal" id="inv_subtotal" class="form-control input-sm" style="text-align: right;" value="179.00" readonly></td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b;">Shipping / Delivery (₹):</td>
                                    <td style="padding: 4px 0; text-align: right;"><input type="number" name="other_charges" id="inv_other_charges" class="form-control input-sm" style="text-align: right;" value="0.00" step="0.01" onchange="calcInvoiceTotals()"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b;">Tax / GST (₹):</td>
                                    <td style="padding: 4px 0; text-align: right;"><input type="number" name="tax_amount" id="inv_tax_amount" class="form-control input-sm" style="text-align: right;" value="0.00" step="0.01" onchange="calcInvoiceTotals()"></td>
                                </tr>
                                <tr style="border-top: 2px solid #0068e1;">
                                    <td style="padding: 8px 0; font-weight: bold; font-size: 14px; color: #0068e1;">Grand Total (₹):</td>
                                    <td style="padding: 8px 0; text-align: right;"><input type="number" name="total_amount" id="inv_total_amount" class="form-control input-sm" style="text-align: right; font-weight: bold; font-size: 14px;" value="179.00" readonly required></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="background: #f8fafc;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                        <i class="fas fa-save"></i> Save Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openOrderInvoiceModal() {
        $('#orderInvoiceModal').modal('show');
    }

    function openCreateInvoiceModal() {
        $('#manualInvoiceForm')[0].reset();
        $('#inv_id').val('');
        $('#manualInvoiceModalTitle').html('<i class="fas fa-plus-circle" style="color: #0068e1;"></i> Create Custom Invoice');
        $('#inv_number').val('INV-' + new Date().getFullYear() + '-' + Math.random().toString(36).substring(2, 7).toUpperCase());
        resetItemsTable();
        calcInvoiceTotals();
        $('#manualInvoiceModal').modal('show');
    }

    function resetItemsTable() {
        $('#invoiceItemsTbody').html(`
            <tr>
                <td><input type="text" class="form-control input-sm item-desc" placeholder="Product name" required value="Men's Cotton T-Shirt"></td>
                <td><input type="text" class="form-control input-sm item-hsn" placeholder="610910" value="610910"></td>
                <td><input type="number" class="form-control input-sm item-qty" value="1" min="1" onchange="calcInvoiceTotals()"></td>
                <td><input type="number" class="form-control input-sm item-price" value="179.00" step="0.01" min="0" onchange="calcInvoiceTotals()"></td>
                <td><input type="number" class="form-control input-sm item-total" value="179.00" readonly></td>
                <td style="text-align: center;"><button type="button" class="btn btn-xs btn-danger" onclick="removeInvoiceItemRow(this)">&times;</button></td>
            </tr>
        `);
    }

    function addInvoiceItemRow() {
        $('#invoiceItemsTbody').append(`
            <tr>
                <td><input type="text" class="form-control input-sm item-desc" placeholder="Product name" required></td>
                <td><input type="text" class="form-control input-sm item-hsn" placeholder="610910" value="610910"></td>
                <td><input type="number" class="form-control input-sm item-qty" value="1" min="1" onchange="calcInvoiceTotals()"></td>
                <td><input type="number" class="form-control input-sm item-price" value="0.00" step="0.01" min="0" onchange="calcInvoiceTotals()"></td>
                <td><input type="number" class="form-control input-sm item-total" value="0.00" readonly></td>
                <td style="text-align: center;"><button type="button" class="btn btn-xs btn-danger" onclick="removeInvoiceItemRow(this)">&times;</button></td>
            </tr>
        `);
    }

    function removeInvoiceItemRow(btn) {
        if ($('#invoiceItemsTbody tr').length > 1) {
            $(btn).closest('tr').remove();
            calcInvoiceTotals();
        } else {
            alert('At least one item is required in the invoice.');
        }
    }

    function calcInvoiceTotals() {
        let subtotal = 0;
        const items = [];

        $('#invoiceItemsTbody tr').each(function() {
            const desc = $(this).find('.item-desc').val();
            const hsn = $(this).find('.item-hsn').val();
            const qty = parseFloat($(this).find('.item-qty').val()) || 1;
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            const lineTotal = qty * price;

            $(this).find('.item-total').val(lineTotal.toFixed(2));
            subtotal += lineTotal;

            if (desc) {
                items.push({
                    name: desc,
                    hsn: hsn,
                    quantity: qty,
                    price: price,
                    total: lineTotal
                });
            }
        });

        const shipping = parseFloat($('#inv_other_charges').val()) || 0;
        const tax = parseFloat($('#inv_tax_amount').val()) || 0;
        const grandTotal = subtotal + shipping + tax;

        $('#inv_subtotal').val(subtotal.toFixed(2));
        $('#inv_total_amount').val(grandTotal.toFixed(2));
        $('#inv_json_field').val(JSON.stringify({ items: items }));
    }

    // Prepare JSON items on form submit
    $('#manualInvoiceForm').on('submit', function() {
        calcInvoiceTotals();
    });

    function openEditInvoiceModal(id) {
        $.get('{{ url("admin/invoices") }}/' + id, function(resp) {
            if (resp.success && resp.data) {
                const inv = resp.data;
                $('#inv_id').val(inv.id);
                $('#manualInvoiceModalTitle').html('<i class="fas fa-edit" style="color: #f59e0b;"></i> Edit Invoice #' + inv.invoice_number);
                $('#inv_customer_name').val(inv.customer_name);
                $('#inv_number').val(inv.invoice_number);
                $('#inv_customer_phone').val(inv.customer_phone);
                $('#inv_customer_email').val(inv.customer_email);
                $('#inv_customer_address').val(inv.customer_address);
                $('#inv_customer_gst').val(inv.customer_gst);
                $('#inv_template_id').val(inv.template_id || 1);
                $('#inv_status').val(inv.status);
                $('#inv_other_charges').val(parseFloat(inv.other_charges || 0).toFixed(2));
                $('#inv_tax_amount').val(parseFloat(inv.tax_amount || 0).toFixed(2));

                // Populate items
                let itemsList = [];
                try {
                    const parsed = JSON.parse(inv.invoice_json);
                    itemsList = parsed.items || (Array.isArray(parsed) ? parsed : []);
                } catch(e) {}

                if (itemsList && itemsList.length > 0) {
                    $('#invoiceItemsTbody').empty();
                    itemsList.forEach(it => {
                        const qty = it.quantity || it.qty || 1;
                        const price = it.price || 0;
                        const total = it.total || (qty * price);
                        $('#invoiceItemsTbody').append(`
                            <tr>
                                <td><input type="text" class="form-control input-sm item-desc" required value="${it.name || it.description || ''}"></td>
                                <td><input type="text" class="form-control input-sm item-hsn" value="${it.hsn || '610910'}"></td>
                                <td><input type="number" class="form-control input-sm item-qty" value="${qty}" min="1" onchange="calcInvoiceTotals()"></td>
                                <td><input type="number" class="form-control input-sm item-price" value="${parseFloat(price).toFixed(2)}" step="0.01" min="0" onchange="calcInvoiceTotals()"></td>
                                <td><input type="number" class="form-control input-sm item-total" value="${parseFloat(total).toFixed(2)}" readonly></td>
                                <td style="text-align: center;"><button type="button" class="btn btn-xs btn-danger" onclick="removeInvoiceItemRow(this)">&times;</button></td>
                            </tr>
                        `);
                    });
                } else {
                    resetItemsTable();
                }

                calcInvoiceTotals();
                $('#manualInvoiceModal').modal('show');
            }
        });
    }

    function deleteInvoice(id) {
        if (!confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
            return;
        }

        $.post('{{ route("admin.invoices.delete") }}', {
            _token: '{{ csrf_token() }}',
            id: id
        }, function(resp) {
            if (resp.success) {
                location.reload();
            } else {
                alert(resp.message || 'Error deleting invoice.');
            }
        }).fail(function() {
            alert('Failed to delete invoice.');
        });
    }
</script>
@endpush
@endsection
