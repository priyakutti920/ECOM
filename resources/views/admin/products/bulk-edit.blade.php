@extends('layouts.admin')

@section('title', 'Bulk Edit Products')

@push('styles')
<style>
    body { background: #f0f2f5 !important; }
    .admin-content { padding: 0 !important; }

    .be-page {
        background: #fff;
        max-width: 1400px;
        margin: 16px auto 90px;
        padding: 24px 32px;
    }

    #toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
    .toast-msg {
        display:flex; align-items:center; gap:8px;
        padding:10px 14px; border-radius:5px; margin-bottom:7px;
        font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10);
    }
    .toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
    .toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }

    .be-table { width:100%; border-collapse:collapse; font-size:13px; table-layout:fixed; }
    .be-table th {
        padding:8px 12px; border-bottom:2px solid #dee2e6;
        text-align:left; font-weight:600; color:#444;
        background:#f8f9fa;
    }
    .be-table td { padding:7px 10px; border-bottom:1px solid #ebebeb; vertical-align:middle; }
    .be-table tr:hover td { background:#fafafa; }
    .be-table tbody tr:last-child td { border-bottom:none; }

    .be-thumb {
        width:52px; height:52px; object-fit:cover;
        border:1px solid #ddd; border-radius:4px; display:block;
    }
    .be-thumb-ph {
        width:52px; height:52px; border:1px solid #ddd; border-radius:4px;
        background:#f5f5f5; display:flex; align-items:center; justify-content:center;
        color:#ccc; font-size:15px;
    }
    .be-thumb-deleted {
        width:52px; height:52px; border:1px dashed #ccc; border-radius:4px;
        background:#fafafa; display:flex; align-items:center; justify-content:center;
        color:#ccc; font-size:15px;
    }

    .be-img-cell {
        display:flex; flex-direction:column; align-items:center; gap:5px;
    }
    .be-img-cell label, .be-img-cell button { font-size:11px; }

    .be-name-col { width:auto; }
    .be-input, .be-select {
        width:100%; padding:6px 8px; border:1px solid #ccc;
        border-radius:4px; font-size:13px; box-sizing:border-box;
    }
    .be-name-input { padding:6px 8px; border:1px solid #ccc; border-radius:4px; font-size:13px; }
    .be-price-input, .be-discount-input {
        width:100%; padding:6px 8px; border:1px solid #ccc;
        border-radius:4px; font-size:13px; box-sizing:border-box;
    }

    .btn-remove-row {
        background:#ffebee; color:#c62828; border:none;
        width:24px; height:24px; border-radius:4px;
        cursor:pointer; font-size:12px;
        display:inline-flex; align-items:center; justify-content:center;
    }
    .btn-remove-row:hover { background:#ffd5d5; }

    .be-section { margin-bottom:24px; }
    .be-section-header {
        background:#f8f9fa; padding:8px 14px; font-size:12px; font-weight:700;
        color:#555; border:1px solid #ddd; border-bottom:none; border-radius:4px 4px 0 0;
    }

    .sticky-footer {
        position:fixed; bottom:0; left:0; right:0;
        background:#fff; border-top:1px solid #eee;
        padding:14px 24px;
        display:flex; justify-content:flex-end; gap:10px;
        box-shadow:0 -2px 12px rgba(0,0,0,0.08);
        z-index:1000;
    }
</style>
@endpush

@section('content')
<div id="toast-wrap"></div>

@php
    $grouped = $products->groupBy(fn($p) => $p->category_id ?? '0');
@endphp

<div class="be-page">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <h2 style="margin:0; font-weight:700; font-size:22px;">
            <i class="fas fa-edit" style="font-size:18px; margin-right:6px; color:#7b1fa2;"></i> Bulk Edit Products
            <span style="font-size:14px; font-weight:400; color:#888; margin-left:8px;">({{ $products->count() }} product(s))</span>
        </h2>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.products.index') }}" style="padding:7px 16px; border:1px solid #ccc; background:#fff; border-radius:5px; cursor:pointer; font-size:13px; color:#555; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- Products grouped by category --}}
    @php
        $allCategories = $categories->keyBy('id');
        $uncatProducts = $grouped->get('0', collect());
        $catGroups = $grouped->forget('0');
    @endphp

    @foreach ($catGroups as $catId => $catProducts)
    @php $cat = $allCategories->get($catId); @endphp
    <div class="be-section">
        <div class="be-section-header">
            {{ $cat ? $cat->name : 'Unknown' }} <span style="font-weight:400; color:#888;">({{ $catProducts->count() }} item(s))</span>
        </div>
        <table class="be-table">
            <thead>
                <tr>
                    <th style="width:100px;">Image</th>
                    <th>Name</th>
                    <th style="width:140px;">Category</th>
                    <th style="width:120px;">Price (₹)</th>
                    <th style="width:120px;">Discount (₹)</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($catProducts as $product)
                <tr id="be-row-{{ $product->id }}" data-id="{{ $product->id }}" class="be-product-row">
                    <td>
                        <div class="be-img-cell">
                            <div id="be-img-preview-{{ $product->id }}">
                                @if ($product->image_url)
                                <img src="{{ $product->image_url }}" class="be-thumb" alt="">
                                @else
                                <div class="be-thumb-ph"><i class="fas fa-image"></i></div>
                                @endif
                            </div>
                            <label style="color:#3a7bd5; cursor:pointer; text-decoration:underline;">
                                <input type="file" id="be-img-file-{{ $product->id }}" class="be-img-input" data-id="{{ $product->id }}" accept="image/*" style="display:none;">
                                Choose File
                            </label>
                            <button type="button" class="be-img-delete-btn"
                                data-id="{{ $product->id }}"
                                data-has-img="{{ $product->image ? '1' : '0' }}"
                                style="color:#c0392b; background:none; border:none; cursor:pointer; padding:0;">
                                Delete
                            </button>
                        </div>
                    </td>
                    <td>
                        <input type="hidden" class="be-id" value="{{ $product->id }}">
                        <input type="text" class="be-name-input" value="{{ e($product->name) }}">
                    </td>
                    <td>
                        <select class="be-cat be-select">
                            <option value="">-- None --</option>
                            @foreach ($categories as $catOpt)
                            <option value="{{ $catOpt->id }}" {{ $product->category_id == $catOpt->id ? 'selected' : '' }}>
                                {{ $catOpt->name }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="be-price-input"
                            value="{{ $product->price > 0 ? $product->price : '' }}"
                            min="0" step="0.01" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="be-discount-input"
                            value="{{ $product->discount_price > 0 ? $product->discount_price : '' }}"
                            min="0" step="0.01" placeholder="">
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn-remove-row be-remove" data-id="{{ $product->id }}" title="Remove from list">&times;</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    {{-- Uncategorized --}}
    @if ($uncatProducts->count())
    <div class="be-section">
        <div class="be-section-header">
            Uncategorized <span style="font-weight:400; color:#888;">({{ $uncatProducts->count() }} item(s))</span>
        </div>
        <table class="be-table">
            <thead>
                <tr>
                    <th style="width:100px;">Image</th>
                    <th>Name</th>
                    <th style="width:140px;">Category</th>
                    <th style="width:120px;">Price (₹)</th>
                    <th style="width:120px;">Discount (₹)</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($uncatProducts as $product)
                <tr id="be-row-{{ $product->id }}" data-id="{{ $product->id }}" class="be-product-row">
                    <td>
                        <div class="be-img-cell">
                            <div id="be-img-preview-{{ $product->id }}">
                                @if ($product->image_url)
                                <img src="{{ $product->image_url }}" class="be-thumb" alt="">
                                @else
                                <div class="be-thumb-ph"><i class="fas fa-image"></i></div>
                                @endif
                            </div>
                            <label style="color:#3a7bd5; cursor:pointer; text-decoration:underline;">
                                <input type="file" id="be-img-file-{{ $product->id }}" class="be-img-input" data-id="{{ $product->id }}" accept="image/*" style="display:none;">
                                Choose File
                            </label>
                            <button type="button" class="be-img-delete-btn"
                                data-id="{{ $product->id }}"
                                data-has-img="{{ $product->image ? '1' : '0' }}"
                                style="color:#c0392b; background:none; border:none; cursor:pointer; padding:0;">
                                Delete
                            </button>
                        </div>
                    </td>
                    <td>
                        <input type="hidden" class="be-id" value="{{ $product->id }}">
                        <input type="text" class="be-name-input" value="{{ e($product->name) }}">
                    </td>
                    <td>
                        <select class="be-cat be-select">
                            <option value="">-- None --</option>
                            @foreach ($categories as $catOpt)
                            <option value="{{ $catOpt->id }}" {{ $product->category_id == $catOpt->id ? 'selected' : '' }}>
                                {{ $catOpt->name }}
                            </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="be-price-input"
                            value="{{ $product->price > 0 ? $product->price : '' }}"
                            min="0" step="0.01" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="be-discount-input"
                            value="{{ $product->discount_price > 0 ? $product->discount_price : '' }}"
                            min="0" step="0.01" placeholder="">
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn-remove-row be-remove" data-id="{{ $product->id }}" title="Remove from list">&times;</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>{{-- /.be-page --}}

{{-- Sticky footer --}}
<div class="sticky-footer">
    <a href="{{ route('admin.products.index') }}" style="padding:8px 18px; border:1px solid #ccc; background:#fff; border-radius:5px; font-size:13px; color:#555; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <button id="btn-save-all-bottom" style="padding:8px 20px; border:none; background:#7b1fa2; color:#fff; border-radius:5px; cursor:pointer; font-size:13px; font-weight:600;">
        <i class="fas fa-save"></i> Save All Changes
    </button>
</div>

@endsection

@push('scripts')
<script>
$(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function toast(msg, type) {
        var icon = (type === 'error') ? 'fa-exclamation-circle' : 'fa-check-circle';
        var el = $('<div class="toast-msg ' + (type || 'success') + '">' +
            '<i class="fas ' + icon + '"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
    }

    // Image preview
    $(document).on('change', '.be-img-input', function () {
        var id = $(this).data('id');
        var f = this.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function (e) {
            $('#be-img-preview-' + id).html('<img src="' + e.target.result + '" class="be-thumb" alt="">');
        };
        r.readAsDataURL(f);
    });

    // Image delete
    $(document).on('click', '.be-img-delete-btn', function () {
        var id = $(this).data('id');
        $(this).attr('data-pending-delete', '1');
        $('#be-img-preview-' + id).html('<div class="be-thumb-deleted"><i class="fas fa-image"></i></div>');
        $('#be-img-file-' + id).val('');
    });

    // Remove row
    $(document).on('click', '.be-remove', function () {
        var id = $(this).data('id');
        $('#be-row-' + id).fadeOut(200, function () { $(this).remove(); });
    });

    function saveAll() {
        var fd = new FormData();
        var rowCount = 0;

        $('.be-product-row').each(function () {
            var $row = $(this);
            var id = $row.find('.be-id').val();
            if (!id) return;

            fd.append('ids[]', id);
            fd.append('names[' + id + ']', $row.find('.be-name-input').val().trim());
            fd.append('categories[' + id + ']', $row.find('.be-cat').val());
            fd.append('prices[' + id + ']', $row.find('.be-price-input').val());
            fd.append('discounts[' + id + ']', $row.find('.be-discount-input').val());

            var $imgInput = $('#be-img-file-' + id);
            var $delBtn = $row.find('.be-img-delete-btn');

            if ($delBtn.attr('data-pending-delete') === '1') {
                fd.append('delete_images[]', id);
            } else if ($imgInput[0] && $imgInput[0].files[0]) {
                fd.append('images[' + id + ']', $imgInput[0].files[0]);
            }
            rowCount++;
        });

        if (rowCount === 0) {
            toast('No products to save.', 'error');
            return;
        }

        $('#btn-save-all-bottom').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.products.bulk-update") }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                if (!res.success) return;
                toast(res.message);
                setTimeout(function () { window.location.href = '{{ route("admin.products.index") }}'; }, 800);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not save changes.';
                toast(msg, 'error');
                $('#btn-save-all-bottom').prop('disabled', false).html('<i class="fas fa-save"></i> Save All Changes');
            }
        });
    }

    $('#btn-save-all, #btn-save-all-bottom').on('click', saveAll);

});
</script>
@endpush