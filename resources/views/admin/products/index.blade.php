@extends('layouts.admin')

@section('title', 'Products')

@push('styles')
<style>
#toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
.toast-msg { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:5px; margin-bottom:7px; font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10); }
.toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
.toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
.toast-msg.info    { background:#f0f7ff; border-left:4px solid #2980b9; color:#1a5276; }

.table-prod-img { width:52px; height:52px; object-fit:cover; border:1px solid #eee; border-radius:4px; background:#f9f9f9; }
.row-flash { animation:row-flash 1.2s ease forwards; }
@keyframes row-flash { 0%{background:#fffbe6} 100%{background:transparent} }
.badge-paid    { background:#27ae60; }
.badge-pending { background:#e67e22; }
.badge-cancelled { background:#95a5a6; }

@media (max-width: 767px) {
    .table-responsive { border: none; }
    .product-table th:nth-child(5),
    .product-table td:nth-child(5),
    .product-table th:nth-child(6),
    .product-table td:nth-child(6) { display: none; }
}
</style>
@endpush

@section('content')
<div id="toast-wrap"></div>

<div style="max-width:1200px; margin:0 auto;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <h2 style="margin:0; font-weight:700; font-size:22px;">
            <i class="fas fa-box-open" style="font-size:18px; margin-right:6px;"></i> Products
            <span style="font-size:14px; color:#999; font-weight:400; margin-left:8px;">({{ $products->total() }})</span>
        </h2>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#bulkImageModal" style="font-weight:600; padding:7px 16px; border:1px solid #cbd5e1; background:#fff; color:#334155;">
                <i class="fas fa-images" style="color:#3a7bd5; margin-right:4px;"></i> Bulk Image Import
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary" style="font-weight:600; padding:7px 20px;">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div style="background:#fff; border:1px solid #ddd; border-radius:5px; padding:12px 14px; margin-bottom:12px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <form method="GET" action="{{ route('admin.products.index') }}" style="display:flex; gap:8px; flex:1; align-items:center; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:200px;">
                <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:13px;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code…"
                    style="width:100%; padding:6px 10px 6px 32px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
            </div>
            <select name="status" style="padding:6px 10px; border:1px solid #ddd; border-radius:4px; font-size:13px; min-width:130px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-default" style="padding:6px 16px; background:#f0f0f0; border:1px solid #ccc; font-size:13px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.products.index') }}" class="btn btn-default" style="padding:6px 12px; border:1px solid #ccc; font-size:12px; color:#c0392b;">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="table-responsive" style="background:#fff; border:1px solid #ddd; border-radius:5px;">
        <table class="table product-table" style="margin:0; font-size:13px;">
            <thead style="background:#f8f8f8;">
                <tr style="border-bottom:2px solid #eee;">
                    <th style="width:38px; padding:10px 12px; text-align:center;"><input type="checkbox" id="bulkMasterCheck"></th>
                    <th style="padding:10px 12px; font-weight:600; color:#555;">Image</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555;">Product Name</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555;">Code</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555;">Categories</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555; text-align:center;">Variations</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555; text-align:center;">Stock</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555; text-align:center;">Status</th>
                    <th style="padding:10px 12px; font-weight:600; color:#555; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr id="product-row-{{ $product->id }}" style="border-bottom:1px solid #f0f0f0;" class="row-flash">
                    <td style="padding:8px 12px; text-align:center; vertical-align:middle;">
                        <input type="checkbox" class="bulk-item-check" value="{{ $product->id }}">
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle;">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}"
                                class="table-prod-img" alt="{{ $product->name }}"
                                onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                        @else
                            <div class="table-prod-img" style="display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-image" style="color:#ccc; font-size:16px;"></i>
                            </div>
                        @endif
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle;">
                        <div style="font-weight:600; color:#222; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $product->name }}</div>
                        @if($product->url)
                            <div style="font-size:11px; color:#888; margin-top:2px;">/{{ $product->url }}</div>
                        @endif
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle;">
                        <span style="font-family:monospace; font-size:12px; background:#f5f5f5; padding:2px 6px; border-radius:3px; color:#555;">
                            {{ $product->code ?: '—' }}
                        </span>
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle;">
                        <div style="max-width:150px;">
                            @forelse($product->categories->take(2) as $cat)
                                <span class="badge" style="background:#e8f4fd; color:#2980b9; font-size:11px; margin-bottom:2px;">{{ $cat->name }}</span>
                            @empty
                                <span style="color:#ccc; font-size:12px;">No category</span>
                            @endforelse
                            @if($product->categories->count() > 2)
                                <span class="badge badge-info" style="font-size:11px;">+{{ $product->categories->count() - 2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle; text-align:center;">
                        <span class="badge {{ $product->variations->count() > 0 ? 'badge-info' : 'badge-secondary' }}" style="font-size:12px;">
                            {{ $product->variations->count() }}
                        </span>
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle; text-align:center;">
                        @if($product->manage_inventory)
                            <span class="badge {{ $product->stock_status === 'out_of_stock' ? 'badge-danger' : 'badge-success' }}" style="font-size:11px;">
                                {{ $product->stock_status === 'out_of_stock' ? 'Out' : $product->qty }}
                            </span>
                        @else
                            <span style="color:#aaa; font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle; text-align:center;">
                        @if($product->is_active)
                            <span class="badge badge-success" style="font-size:11px;">Active</span>
                        @else
                            <span class="badge badge-secondary" style="font-size:11px;">Inactive</span>
                        @endif
                    </td>
                    <td style="padding:8px 12px; vertical-align:middle; text-align:right;">
                        <div style="display:flex; gap:5px; justify-content:flex-end;">
                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                class="btn btn-xs btn-default" title="Edit"
                                style="padding:4px 8px; font-size:12px; background:#e8f4fd; border:1px solid #bcd7ea; color:#2980b9;">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <button class="btn btn-xs btn-default btn-delete-product"
                                data-id="{{ $product->id }}"
                                data-name="{{ e($product->name) }}"
                                title="Delete"
                                style="padding:4px 8px; font-size:12px; background:#fdecea; border:1px solid #f5c6cb; color:#c0392b;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:40px; text-align:center; color:#aaa;">
                        <i class="fas fa-box-open" style="font-size:32px; margin-bottom:8px; display:block;"></i>
                        No products found.
                        <a href="{{ route('admin.products.create') }}" style="color:#2980b9; margin-left:6px;">Add your first product →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div style="margin-top:14px; display:flex; justify-content:center;">
        {{ $products->appends(request()->query())->links() }}
    </div>
    @endif

</div>

{{-- ── BULK CATEGORY MODAL ── --}}
<div class="modal fade" id="bulkCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="font-size:14px; font-weight:700;"><i class="fas fa-tags text-primary"></i> Assign Category</h4>
            </div>
            <div class="modal-body" style="padding:16px;">
                <label style="font-weight:600; font-size:12.5px; margin-bottom:6px; display:block;">Select Category:</label>
                <select id="bulkCategorySelect" class="form-control input-sm">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitBulkCategory()"><i class="fas fa-check"></i> Assign</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Image Import Modal -->
<div class="modal fade" id="bulkImageModal" tabindex="-1" role="dialog" aria-labelledby="bulkImageModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="max-width:850px;">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:14px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size:22px; opacity:0.6;">&times;</button>
                <h4 class="modal-title" id="bulkImageModalLabel" style="font-size:16px; font-weight:700; color:#1e293b; margin:0;">
                    <i class="fas fa-images" style="color:#3a7bd5; margin-right:8px;"></i> Bulk Product Image Import
                </h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <!-- Step 1: Upload / Dropzone -->
                <div id="bulkImageUploadSection">
                    <p style="color:#64748b; font-size:13px; margin-bottom:14px;">
                        Upload multiple product images at once. Images will be auto-matched to products based on filename matching Product SKU, Code, ID, or Slug (e.g., <code>TSHIRT-BLK_1.jpg</code>, <code>PRD001.webp</code>, <code>12.png</code>).
                    </p>
                    <div id="bulkImageDropArea" style="border:2px dashed #cbd5e1; border-radius:8px; padding:35px 20px; text-align:center; background:#f8fafc; cursor:pointer; transition:all 0.2s;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:40px; color:#94a3b8; margin-bottom:10px;"></i>
                        <h5 style="margin:0 0 6px 0; font-weight:600; color:#334155;">Drag & drop images here, or click to browse</h5>
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Supported formats: JPG, PNG, WEBP, GIF, AVIF (Max 10MB each)</p>
                        <input type="file" id="bulkImageFileInput" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/avif" style="display:none;">
                    </div>
                    <div id="bulkImageSelectedCount" style="margin-top:10px; font-size:12.5px; color:#475569; display:none;"></div>
                </div>

                <!-- Loading Spinner -->
                <div id="bulkImageLoading" style="display:none; text-align:center; padding:35px 0;">
                    <i class="fas fa-spinner fa-spin" style="font-size:36px; color:#3a7bd5; margin-bottom:12px;"></i>
                    <p id="bulkImageLoadingText" style="color:#475569; font-weight:500; font-size:13.5px; margin:0;">Analyzing images and matching against product catalog...</p>
                </div>

                <!-- Step 2: Preview & Matching Results -->
                <div id="bulkImagePreviewSection" style="display:none;">
                    <!-- Summary Badges -->
                    <div style="display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap;">
                        <span class="badge" style="background:#10b981; padding:6px 10px; font-size:12px; font-weight:600;"><i class="fas fa-check-circle"></i> <span id="cntMatched">0</span> Matched</span>
                        <span class="badge" style="background:#f59e0b; padding:6px 10px; font-size:12px; font-weight:600;"><i class="fas fa-exclamation-triangle"></i> <span id="cntAmbiguous">0</span> Ambiguous</span>
                        <span class="badge" style="background:#64748b; padding:6px 10px; font-size:12px; font-weight:600;"><i class="fas fa-question-circle"></i> <span id="cntUnmatched">0</span> Unmatched</span>
                        <span class="badge" style="background:#ef4444; padding:6px 10px; font-size:12px; font-weight:600;"><i class="fas fa-times-circle"></i> <span id="cntInvalid">0</span> Invalid</span>
                    </div>

                    <!-- Global options -->
                    <div style="display:flex; gap:20px; margin-bottom:14px; background:#f1f5f9; padding:10px 14px; border-radius:6px; font-size:12.5px; align-items:center;">
                        <label style="margin:0; font-weight:500; cursor:pointer;">
                            <input type="checkbox" id="bulkSetPrimaryGlobal" checked style="margin-right:6px;"> Set as primary product image
                        </label>
                        <label style="margin:0; font-weight:500; cursor:pointer;">
                            <input type="checkbox" id="bulkOverwriteGlobal" style="margin-right:6px;"> Overwrite existing gallery images
                        </label>
                    </div>

                    <!-- Preview Table -->
                    <div style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px;">
                        <table class="table table-hover" style="margin:0; font-size:12px;">
                            <thead style="background:#f8fafc; position:sticky; top:0; z-index:2;">
                                <tr>
                                    <th style="width:60px;">Preview</th>
                                    <th>Filename</th>
                                    <th>Matched / Target Product</th>
                                    <th>Match Details</th>
                                    <th style="width:70px; text-align:center;">Include</th>
                                </tr>
                            </thead>
                            <tbody id="bulkImagePreviewTbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnBulkImageReset" class="btn btn-default" style="display:none;" onclick="resetBulkImageModal()">
                    <i class="fas fa-redo"></i> Choose Different Images
                </button>
                <button type="button" id="btnBulkImageAnalyze" class="btn btn-primary" style="display:none;" onclick="startBulkImageAnalyze()">
                    <i class="fas fa-search"></i> Analyze & Match
                </button>
                <button type="button" id="btnBulkImageApply" class="btn btn-success" style="display:none;" onclick="applyBulkImageImports()">
                    <i class="fas fa-check"></i> Import & Attach Images
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function esc(str) { if (str == null) return ''; return $('<span>').text(str).html(); }

    function toast(msg, type) {
        var icon = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
        var el = $('<div class="toast-msg ' + (type || 'success') + '"><i class="fas ' + icon + '"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
    }

    // Register Bulk Actions in Universal Bar
    var html = '';
    html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeProductBulk(\'activate\')"><i class="fas fa-check-circle"></i> Set Active</button>';
    html += '<button type="button" class="bulk-action-btn" onclick="executeProductBulk(\'deactivate\')"><i class="fas fa-pause-circle"></i> Set Inactive</button>';
    html += '<button type="button" class="bulk-action-btn" onclick="executeProductBulk(\'in_stock\')"><i class="fas fa-box"></i> In Stock</button>';
    html += '<button type="button" class="bulk-action-btn" onclick="executeProductBulk(\'out_of_stock\')"><i class="fas fa-box-open"></i> Out of Stock</button>';
    html += '<button type="button" class="bulk-action-btn" onclick="openBulkCategoryModal()"><i class="fas fa-tags"></i> Change Category</button>';
    html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeProductBulk(\'delete\', \'Move {count} selected product(s) to Trash?\')"><i class="fas fa-trash-alt"></i> Delete</button>';
    $('#bulkBarActions').html(html);

    {{-- Delete --}}
    $(document).on('click', '.btn-delete-product', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');
        if (!confirm('Delete product "' + name + '"?\n\nThis action cannot be undone.')) return;

        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: '{{ url("admin/products") }}/' + id,
            method: 'DELETE',
            success: function (res) {
                if (!res.success) return;
                $('#product-row-' + id).fadeOut(350, function () { $(this).remove(); });
                toast(res.message);
            },
            error: function () {
                $btn.prop('disabled', false);
                toast('Could not delete product.', 'error');
            }
        });
    });

});

function openBulkCategoryModal() {
    var ids = getSelectedBulkIds();
    if (!ids.length) {
        adminToast('Please select at least one product.', 'error');
        return;
    }
    $('#bulkCategoryModal').modal('show');
}

function submitBulkCategory() {
    var catId = $('#bulkCategorySelect').val();
    runBulkAction('{{ route("admin.products.bulk-action") }}', 'change_category', { category_id: catId });
    $('#bulkCategoryModal').modal('hide');
}

function executeProductBulk(action, confirmMsg) {
    runBulkAction('{{ route("admin.products.bulk-action") }}', action, {}, confirmMsg);
}

// Bulk Image Import Logic
var currentBulkBatchId = null;
var currentBulkProducts = [];
var selectedBulkFiles = [];

$(function () {
    var $dropArea = $('#bulkImageDropArea');
    var $fileInput = $('#bulkImageFileInput');

    $dropArea.on('click', function () {
        $fileInput.trigger('click');
    });

    $dropArea.on('dragover dragenter', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $dropArea.css({ 'border-color': '#3a7bd5', 'background': '#eff6ff' });
    });

    $dropArea.on('dragleave dragend drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $dropArea.css({ 'border-color': '#cbd5e1', 'background': '#f8fafc' });
    });

    $dropArea.on('drop', function (e) {
        var files = e.originalEvent.dataTransfer.files;
        handleFilesSelected(files);
    });

    $fileInput.on('change', function () {
        handleFilesSelected(this.files);
    });
});

function handleFilesSelected(files) {
    if (!files || !files.length) return;
    selectedBulkFiles = Array.from(files);
    $('#bulkImageSelectedCount').text(selectedBulkFiles.length + ' image file(s) selected.').show();
    $('#btnBulkImageAnalyze').show();
}

function resetBulkImageModal() {
    selectedBulkFiles = [];
    currentBulkBatchId = null;
    currentBulkProducts = [];
    $('#bulkImageFileInput').val('');
    $('#bulkImageSelectedCount').hide();
    $('#bulkImageUploadSection').show();
    $('#bulkImagePreviewSection').hide();
    $('#bulkImageLoading').hide();
    $('#btnBulkImageReset').hide();
    $('#btnBulkImageApply').hide();
    $('#btnBulkImageAnalyze').hide();
    $('#bulkImagePreviewTbody').empty();
}

function startBulkImageAnalyze() {
    if (!selectedBulkFiles.length) {
        adminToast('Please select at least one image file.', 'error');
        return;
    }

    var formData = new FormData();
    selectedBulkFiles.forEach(function (file) {
        formData.append('images[]', file);
    });

    $('#bulkImageUploadSection').hide();
    $('#btnBulkImageAnalyze').hide();
    $('#bulkImageLoading').show();

    $.ajax({
        url: '{{ route("admin.products.bulk-images.preview") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            $('#bulkImageLoading').hide();
            if (!res.success) {
                adminToast(res.message || 'Failed to analyze images.', 'error');
                $('#bulkImageUploadSection').show();
                $('#btnBulkImageAnalyze').show();
                return;
            }

            currentBulkBatchId = res.batch_id;
            currentBulkProducts = res.available_products || [];

            $('#cntMatched').text(res.counts.matched || 0);
            $('#cntAmbiguous').text(res.counts.ambiguous || 0);
            $('#cntUnmatched').text(res.counts.unmatched || 0);
            $('#cntInvalid').text(res.counts.invalid || 0);

            renderBulkPreviewTable(res);

            $('#bulkImagePreviewSection').show();
            $('#btnBulkImageReset').show();
            $('#btnBulkImageApply').show();
        },
        error: function (xhr) {
            $('#bulkImageLoading').hide();
            $('#bulkImageUploadSection').show();
            $('#btnBulkImageAnalyze').show();
            var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error during upload';
            adminToast(err, 'error');
        }
    });
}

function renderBulkPreviewTable(res) {
    var $tbody = $('#bulkImagePreviewTbody').empty();

    function productOptionsHtml(selectedId) {
        var opts = '<option value="">-- Do Not Import / Skip --</option>';
        currentBulkProducts.forEach(function (p) {
            var sel = (selectedId && p.id == selectedId) ? ' selected' : '';
            opts += '<option value="' + p.id + '"' + sel + '>' + $('<span>').text(p.name + ' (' + p.sku + ')').html() + '</option>';
        });
        return opts;
    }

    // Matched items
    (res.matched || []).forEach(function (item, idx) {
        var row = '<tr class="bulk-import-row" data-filename="' + $('<span>').text(item.temp_filename).html() + '">' +
            '<td><img src="' + item.preview_url + '" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #e2e8f0;"></td>' +
            '<td><strong>' + $('<span>').text(item.original_name).html() + '</strong></td>' +
            '<td>' +
                '<select class="form-control input-sm bulk-target-product" style="font-size:12px;">' +
                    productOptionsHtml(item.product_id) +
                '</select>' +
            '</td>' +
            '<td><span class="badge" style="background:#10b981;">Matched via ' + item.matched_by + '</span></td>' +
            '<td style="text-align:center;"><input type="checkbox" class="bulk-row-include" checked></td>' +
        '</tr>';
        $tbody.append(row);
    });

    // Ambiguous items
    (res.ambiguous || []).forEach(function (item, idx) {
        var candOpts = '<option value="">-- Resolve Match (Select Product) --</option>';
        (item.candidates || []).forEach(function (c) {
            candOpts += '<option value="' + c.id + '">' + $('<span>').text(c.name + ' (' + c.sku + ')').html() + '</option>';
        });
        candOpts += '<option disabled>──────────</option>' + productOptionsHtml(null);

        var row = '<tr class="bulk-import-row" data-filename="' + $('<span>').text(item.temp_filename).html() + '" style="background:#fffbeb;">' +
            '<td><img src="' + item.preview_url + '" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #f59e0b;"></td>' +
            '<td><strong>' + $('<span>').text(item.original_name).html() + '</strong></td>' +
            '<td>' +
                '<select class="form-control input-sm bulk-target-product" style="font-size:12px; border-color:#f59e0b;">' +
                    candOpts +
                '</select>' +
            '</td>' +
            '<td><span class="badge" style="background:#f59e0b;">Multiple Matches</span></td>' +
            '<td style="text-align:center;"><input type="checkbox" class="bulk-row-include"></td>' +
        '</tr>';
        $tbody.append(row);
    });

    // Unmatched items
    (res.unmatched || []).forEach(function (item, idx) {
        var row = '<tr class="bulk-import-row" data-filename="' + $('<span>').text(item.temp_filename).html() + '">' +
            '<td><img src="' + item.preview_url + '" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #e2e8f0;"></td>' +
            '<td><strong>' + $('<span>').text(item.original_name).html() + '</strong></td>' +
            '<td>' +
                '<select class="form-control input-sm bulk-target-product" style="font-size:12px;">' +
                    productOptionsHtml(null) +
                '</select>' +
            '</td>' +
            '<td><span class="badge" style="background:#64748b;">Unmatched</span></td>' +
            '<td style="text-align:center;"><input type="checkbox" class="bulk-row-include"></td>' +
        '</tr>';
        $tbody.append(row);
    });

    // Invalid items
    (res.invalid || []).forEach(function (item, idx) {
        var row = '<tr style="background:#fef2f2; opacity:0.8;">' +
            '<td><i class="fas fa-exclamation-triangle" style="color:#ef4444; font-size:18px;"></i></td>' +
            '<td>' + $('<span>').text(item.original_name).html() + '</td>' +
            '<td colspan="2"><span class="text-danger" style="font-size:11.5px;">' + $('<span>').text(item.reason).html() + '</span></td>' +
            '<td style="text-align:center;"><input type="checkbox" disabled></td>' +
        '</tr>';
        $tbody.append(row);
    });

    // Auto-check checkbox when target product is selected
    $tbody.find('.bulk-target-product').on('change', function () {
        var $row = $(this).closest('tr');
        if ($(this).val()) {
            $row.find('.bulk-row-include').prop('checked', true);
        }
    });
}

function applyBulkImageImports() {
    if (!currentBulkBatchId) return;

    var assignments = [];
    var isPrimary = $('#bulkSetPrimaryGlobal').is(':checked');
    var overwrite = $('#bulkOverwriteGlobal').is(':checked');

    $('#bulkImagePreviewTbody .bulk-import-row').each(function () {
        var $row = $(this);
        var isIncluded = $row.find('.bulk-row-include').is(':checked');
        var productId = $row.find('.bulk-target-product').val();
        var tempFilename = $row.data('filename');

        if (isIncluded && productId && tempFilename) {
            assignments.push({
                temp_filename: tempFilename,
                product_id: parseInt(productId, 10),
                as_primary: isPrimary,
                overwrite: overwrite
            });
        }
    });

    if (!assignments.length) {
        adminToast('Please select at least one matched image to import.', 'error');
        return;
    }

    var $btn = $('#btnBulkImageApply').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');

    $.ajax({
        url: '{{ route("admin.products.bulk-images.apply") }}',
        method: 'POST',
        data: {
            batch_id: currentBulkBatchId,
            assignments: assignments
        },
        success: function (res) {
            $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Import & Attach Images');
            if (res.success) {
                adminToast(res.message || 'Images imported successfully!');
                $('#bulkImageModal').modal('hide');
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                adminToast(res.message || 'Import failed.', 'error');
            }
        },
        error: function (xhr) {
            $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Import & Attach Images');
            var err = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error applying images.';
            adminToast(err, 'error');
        }
    });
}
</script>
@endpush