@extends('layouts.admin')

@section('title', 'Providers')

@push('styles')
<style>
#toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
.toast-msg { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:5px; margin-bottom:7px; font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10); }
.toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
.toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
.toast-msg.info    { background:#f0f7ff; border-left:4px solid #2980b9; color:#1a5276; }

/* Modal backdrop */
.modal-backdrop-custom { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.45); z-index:1040; display:none; }
.modal-backdrop-custom.show { display:block; }

/* Modal box */
.provider-modal {
    position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; border-radius:8px; box-shadow:0 8px 40px rgba(0,0,0,0.18);
    width:560px; max-width:95vw; max-height:90vh; overflow-y:auto; z-index:1041;
    display:none;
}
.provider-modal.show { display:block; }
.provider-modal .modal-header {
    padding:16px 20px; border-bottom:1px solid #eee;
    display:flex; align-items:center; justify-content:space-between;
}
.provider-modal .modal-header h3 { margin:0; font-size:17px; font-weight:700; color:#222; }
.provider-modal .modal-header .close-btn {
    width:30px; height:30px; border-radius:50%; border:none; background:#f0f0f0;
    cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center;
    color:#666; transition:background .15s;
}
.provider-modal .modal-header .close-btn:hover { background:#e0e0e0; }
.provider-modal .modal-body { padding:20px; }
.provider-modal .modal-footer { padding:12px 20px; border-top:1px solid #eee; display:flex; gap:10px; justify-content:flex-end; }

/* Form rows */
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
.form-row.full { grid-template-columns:1fr; }
.form-group { margin-bottom:14px; }
.form-group label { font-weight:600; font-size:13px; color:#444; display:block; margin-bottom:4px; }
.form-group input, .form-group select, .form-group textarea {
    width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:5px;
    font-size:13px; font-family:inherit; transition:border-color .15s, box-shadow .15s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color:#3a7bd5; box-shadow:0 0 0 2px rgba(58,123,213,0.15); outline:none;
}
.form-group textarea { resize:vertical; min-height:70px; }
.field-error-msg { font-size:11px; color:#c0392b; display:none; margin-top:3px; }
input.has-error, select.has-error, textarea.has-error { border-color:#c0392b !important; }

/* Product tag select */
.prod-select-wrap { border:1px solid #ddd; border-radius:5px; overflow:hidden; }
.prod-select-header {
    padding:8px 10px; display:flex; flex-wrap:wrap; gap:6px; align-items:center;
    min-height:42px; cursor:text; background:#fff;
}
.prod-tag {
    display:flex; align-items:center; gap:4px; padding:3px 8px;
    background:#e8f4fd; border:1px solid #bcd7ea; border-radius:4px;
    font-size:12px; color:#2980b9;
}
.prod-tag .prod-tag-remove { cursor:pointer; font-weight:700; color:#2980b9; padding:0 1px; }
.prod-tag .prod-tag-remove:hover { color:#c0392b; }
.prod-search-input {
    flex:1; min-width:180px; border:none; outline:none; font-size:13px; padding:4px;
}
.prod-dropdown {
    position:relative;
}
.prod-search-box {
    padding:8px 10px; border-top:1px solid #eee; display:none; background:#fff;
}
.prod-search-box.show { display:block; }
.prod-search-input-wrap { position:relative; }
.prod-search-input-wrap input {
    width:100%; padding:7px 10px 7px 32px; border:1px solid #ddd; border-radius:4px;
    font-size:13px;
}
.prod-search-input-wrap i {
    position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:12px;
}
.prod-results { max-height:220px; overflow-y:auto; margin-top:4px; }
.prod-result-item {
    padding:8px 12px; cursor:pointer; font-size:13px; display:flex; justify-content:space-between; align-items:center;
}
.prod-result-item:hover { background:#f0f7ff; }
.prod-result-item.selected { background:#e8f4fd; color:#2980b9; pointer-events:none; opacity:.6; }
.prod-result-item .already-tag { font-size:11px; color:#27ae60; }
.no-prod-results { padding:10px 12px; color:#aaa; font-size:12px; text-align:center; }

/* Products readmore */
.prod-tags-container { display:flex; flex-wrap:wrap; gap:4px; align-items:center; }
.prod-tags-container .inline-tag {
    display:inline-flex; align-items:center; gap:3px; padding:2px 8px;
    background:#f0f7ff; border:1px solid #d0e8f8; border-radius:4px;
    font-size:11px; color:#2980b9; white-space:nowrap;
}
.more-products-btn {
    font-size:11px; color:#3a7bd5; cursor:pointer; font-weight:600;
    background:none; border:none; padding:2px 4px; text-decoration:underline;
}
.more-products-btn:hover { color:#2f6bc4; }
.prod-expand-panel {
    display:none; margin-top:8px; padding:10px 12px; background:#f8fbff;
    border:1px solid #d0e8f8; border-radius:5px;
}
.prod-expand-panel.show { display:block; }
.prod-expand-panel .expand-close {
    font-size:11px; color:#c0392b; cursor:pointer; margin-bottom:6px;
    background:none; border:none; text-decoration:underline;
}

/* Provider table */
.provider-table { width:100%; border-collapse:collapse; font-size:13px; }
.provider-table thead tr { border-bottom:2px solid #eee; }
.provider-table thead th { padding:10px 12px; font-weight:600; color:#555; text-align:left; background:#f8f8f8; }
.provider-table tbody tr { border-bottom:1px solid #f0f0f0; }
.provider-table tbody tr:hover { background:#fafcff; }
.provider-table tbody td { padding:12px; vertical-align:top; }
.provider-table .col-sn { width:40px; text-align:center; color:#999; font-size:12px; }
.provider-table .col-actions { width:90px; text-align:right; white-space:nowrap; }
.provider-name-cell { font-weight:600; color:#222; font-size:14px; }
.provider-meta { font-size:11px; color:#888; margin-top:2px; }
.provider-meta i { width:14px; }
.action-btns { display:flex; gap:5px; justify-content:flex-end; }
.btn-action {
    padding:4px 10px; border-radius:4px; border:none; cursor:pointer; font-size:12px;
    display:inline-flex; align-items:center; gap:4px; transition:background .15s;
}
.btn-edit { background:#e8f4fd; border:1px solid #bcd7ea; color:#2980b9; }
.btn-edit:hover { background:#d4eaf8; }
.btn-delete { background:#fdecea; border:1px solid #f5c6cb; color:#c0392b; }
.btn-delete:hover { background:#fbd8d5; }
.btn-save-modal {
    padding:9px 22px; background:#3a7bd5; color:#fff; border:none; border-radius:5px;
    font-size:14px; font-weight:600; cursor:pointer;
}
.btn-save-modal:hover { background:#2f6bc4; }
.btn-save-modal:disabled { opacity:.6; cursor:not-allowed; }
.btn-cancel-modal {
    padding:9px 18px; background:#f0f0f0; border:1px solid #ccc; color:#555;
    border-radius:5px; font-size:13px; cursor:pointer;
}
.btn-cancel-modal:hover { background:#e8e8e8; }

/* Spinner */
.spinner-sm { display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

@media (max-width: 767px) {
    .form-row { grid-template-columns:1fr; }
    .provider-table .col-mobile, .provider-table .col-address { display:none; }
}
</style>
@endpush

@section('content')
<div id="toast-wrap"></div>

{{-- Modal backdrop --}}
<div class="modal-backdrop-custom" id="modal-backdrop" onclick="closeModal()"></div>

{{-- Add/Edit Provider Modal --}}
<div class="provider-modal" id="provider-modal">
    <div class="modal-header">
        <h3 id="modal-title"><i class="fas fa-plus"></i> Add Provider</h3>
        <button class="close-btn" onclick="closeModal()">&times;</button>
    </div>
    <form id="provider-form" onsubmit="return false;">
        <input type="hidden" id="provider-id" value="">
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Provider Name <span style="color:#c0392b;">*</span></label>
                    <input type="text" id="prov-name" placeholder="Enter provider name" maxlength="255">
                    <span class="field-error-msg" id="err-prov-name"></span>
                </div>
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" id="prov-mobile" placeholder="e.g. 9876543210" maxlength="20">
                    <span class="field-error-msg" id="err-prov-mobile"></span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Address</label>
                    <textarea id="prov-address" placeholder="Enter full address" maxlength="500"></textarea>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" id="prov-city" placeholder="e.g. Chennai" maxlength="100">
                    <span class="field-error-msg" id="err-prov-city"></span>
                </div>
            </div>
            <div class="form-row full">
                <div class="form-group">
                    <label>Products <span style="font-weight:400; color:#888;">(search and select — multiple)</span></label>
                    <div class="prod-select-wrap" id="prod-select-wrap">
                        <div class="prod-select-header" id="prod-select-header" onclick="$('#prod-search-box').toggleClass('show'); $('#prod-search-input').focus();">
                            <div class="prod-search-input-wrap" style="flex:1; min-width:180px; position:relative;">
                                <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:12px;"></i>
                                <input type="text" id="prod-search-input" class="prod-search-input" placeholder="Search products to assign…" autocomplete="off">
                            </div>
                        </div>
                        <div class="prod-search-box" id="prod-search-box">
                            <div class="prod-results" id="prod-results">
                                <div class="no-prod-results">Type to search products…</div>
                            </div>
                        </div>
                    </div>
                    <p style="font-size:11px; color:#888; margin-top:4px;">
                        <i class="fas fa-info-circle"></i> Selected products will appear as tags above. Click &times; to remove.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel-modal" onclick="closeModal()">Cancel</button>
            <button type="submit" class="btn-save-modal" id="btn-save-provider" onclick="saveProvider()">
                <i class="fas fa-save"></i> Save Provider
            </button>
        </div>
    </form>
</div>

{{-- Main Content --}}
<div style="max-width:1100px; margin:0 auto;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <h2 style="margin:0; font-weight:700; font-size:22px;">
            <i class="fas fa-truck" style="font-size:18px; margin-right:6px;"></i> Providers
            <span style="font-size:14px; color:#999; font-weight:400; margin-left:8px;">({{ $providers->total() }})</span>
        </h2>
        <button onclick="openAddModal()" class="btn btn-primary" style="font-weight:600; padding:7px 20px;">
            <i class="fas fa-plus"></i> Add Provider
        </button>
    </div>

    {{-- Search --}}
    <div style="background:#fff; border:1px solid #ddd; border-radius:5px; padding:12px 14px; margin-bottom:12px;">
        <form method="GET" action="{{ route('admin.providers.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <div style="position:relative; flex:1; min-width:220px;">
                <i class="fas fa-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#aaa; font-size:13px;"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by provider name, city, mobile, or product…"
                    style="width:100%; padding:6px 10px 6px 32px; border:1px solid #ddd; border-radius:4px; font-size:13px;">
            </div>
            <button type="submit" class="btn btn-default" style="padding:6px 16px; background:#f0f0f0; border:1px solid #ccc; font-size:13px;">
                <i class="fas fa-filter"></i> Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.providers.index') }}" class="btn btn-default" style="padding:6px 12px; border:1px solid #ccc; font-size:12px; color:#c0392b;">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div style="background:#fff; border:1px solid #ddd; border-radius:5px; overflow:hidden;">
        <table class="provider-table">
            <thead>
                <tr>
                    <th class="col-sn">S.No</th>
                    <th>Provider Name</th>
                    <th class="col-mobile">Mobile</th>
                    <th>City</th>
                    <th class="col-address">Address</th>
                    <th>Products</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $index => $provider)
                <tr id="provider-row-{{ $provider->id }}" style="border-bottom:1px solid #f0f0f0;">
                    <td class="col-sn">{{ $providers->firstItem() + $index }}</td>
                    <td>
                        <div class="provider-name-cell">{{ $provider->name }}</div>
                    </td>
                    <td class="col-mobile" style="font-size:12px; color:#555;">
                        @if($provider->mobile)
                            <i class="fas fa-mobile-alt" style="color:#888; margin-right:4px;"></i>{{ $provider->mobile }}
                        @else
                            <span style="color:#ccc;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px; color:#666;">
                        @if($provider->city)
                            <i class="fas fa-map-marker-alt" style="color:#888; margin-right:4px;"></i>{{ $provider->city }}
                        @else
                            <span style="color:#ccc;">—</span>
                        @endif
                    </td>
                    <td class="col-address" style="font-size:12px; color:#888; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $provider->address ?: '—' }}
                    </td>
                    <td style="min-width:200px;">
                        @if($provider->products->count())
                            @php $allProds = $provider->products; @endphp
                            <div class="prod-tags-container" id="prod-tags-{{ $provider->id }}">
                                @foreach($allProds->take(3) as $prod)
                                    <span class="inline-tag">
                                        <i class="fas fa-box" style="font-size:9px;"></i> {{ $prod->name }}
                                    </span>
                                @endforeach
                                @if($allProds->count() > 3)
                                    <button class="more-products-btn" onclick="toggleProdExpand({{ $provider->id }}, {{ $allProds->count() }})"
                                        id="more-btn-{{ $provider->id }}">
                                        +{{ $allProds->count() - 3 }} more
                                    </button>
                                @endif
                            </div>
                            @if($allProds->count() > 3)
                            <div class="prod-expand-panel" id="prod-expand-{{ $provider->id }}">
                                <button class="expand-close" onclick="toggleProdExpand({{ $provider->id }}, {{ $allProds->count() }})">
                                    &times; Close
                                </button>
                                <div style="display:flex; flex-wrap:wrap; gap:5px;">
                                    @foreach($allProds as $prod)
                                        <span class="inline-tag">
                                            <i class="fas fa-box" style="font-size:9px;"></i> {{ $prod->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @else
                            <span style="font-size:12px; color:#ccc;">No products assigned</span>
                        @endif
                    </td>
                    <td class="col-actions">
                        <div class="action-btns">
                            <button class="btn-action btn-edit"
                                onclick='openEditModal({{ $provider->id }}, {{ json_encode($provider->name) }}, {{ json_encode($provider->mobile ?? '') }}, {{ json_encode($provider->address ?? '') }}, {{ json_encode($provider->city ?? '') }}, {{ $provider->products->pluck('id') }})'
                                title="Edit">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                            <button class="btn-action btn-delete"
                                onclick="deleteProvider({{ $provider->id }}, {{ json_encode($provider->name) }})"
                                title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:40px; text-align:center; color:#aaa;">
                        <i class="fas fa-truck" style="font-size:32px; margin-bottom:8px; display:block;"></i>
                        No providers found.
                        <a href="javascript:void(0)" onclick="openAddModal()" style="color:#2980b9; margin-left:6px;">Add your first provider →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($providers->hasPages())
    <div style="margin-top:14px; display:flex; justify-content:center;">
        {{ $providers->appends(request()->query())->links() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
var selectedProducts = [];
var allProducts = {!! json_encode($allProducts) !!};
var searchTimer = null;

$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Close dropdown on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.prod-select-wrap').length) {
            $('#prod-search-box').removeClass('show');
        }
    });

    // Product search
    $('#prod-search-input').on('input', function () {
        clearTimeout(searchTimer);
        var q = $(this).val().toLowerCase();

        if (q.length < 1) {
            $('#prod-results').html('<div class="no-prod-results">Type to search products…</div>');
            return;
        }

        searchTimer = setTimeout(function () {
            var filtered = allProducts.filter(function (p) {
                return p.name.toLowerCase().includes(q) && !selectedProducts.includes(p.id);
            });

            if (!filtered.length) {
                $('#prod-results').html('<div class="no-prod-results">No products found</div>');
                return;
            }

            var html = '';
            filtered.forEach(function (p) {
                html += '<div class="prod-result-item" data-id="' + p.id + '" onclick="addProductTag(' + p.id + ', \'' + esc(p.name) + '\')">' +
                    '<span>' + esc(p.name) + '</span>' +
                    '</div>';
            });
            $('#prod-results').html(html);
        }, 250);
    });
});

function esc(str) {
    if (str == null) return '';
    return $('<span>').text(str).html();
}

function toast(msg, type) {
    var icon = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
    var el = $('<div class="toast-msg ' + (type || 'success') + '"><i class="fas ' + icon + '"></i> ' + msg + '</div>');
    $('#toast-wrap').append(el);
    setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
}

function openAddModal() {
    $('#provider-id').val('');
    $('#modal-title').html('<i class="fas fa-plus"></i> Add Provider');
    $('#provider-form')[0].reset();
    clearErrors();
    selectedProducts = [];
    renderProductTags();
    $('#prod-search-input').val('');
    $('#prod-results').html('<div class="no-prod-results">Type to search products…</div>');
    $('#provider-modal').addClass('show');
    $('#modal-backdrop').addClass('show');
    $('#prov-name').focus();
}

function openEditModal(id, name, mobile, address, city, productIds) {
    $('#provider-id').val(id);
    $('#modal-title').html('<i class="fas fa-pen"></i> Edit Provider');
    clearErrors();

    $('#prov-name').val(name);
    $('#prov-mobile').val(mobile);
    $('#prov-address').val(address);
    $('#prov-city').val(city);

    selectedProducts = productIds.slice();
    renderProductTags();
    $('#prod-search-input').val('');
    $('#prod-results').html('<div class="no-prod-results">Type to search products…</div>');

    $('#provider-modal').addClass('show');
    $('#modal-backdrop').addClass('show');
    $('#prov-name').focus();
}

function closeModal() {
    $('#provider-modal').removeClass('show');
    $('#modal-backdrop').removeClass('show');
}

function renderProductTags() {
    var $header = $('#prod-select-header');
    // Remove existing tags (keep the search input wrapper)
    $header.find('.prod-tag').remove();

    selectedProducts.forEach(function (id) {
        var prod = allProducts.find(function (p) { return p.id == id; });
        if (!prod) return;
        var tag = '<span class="prod-tag" data-id="' + id + '">' +
            '<i class="fas fa-box" style="font-size:10px;"></i> ' + esc(prod.name) +
            ' <span class="prod-tag-remove" onclick="removeProductTag(' + id + ', event)">&times;</span>' +
            '</span>';
        $header.find('.prod-search-input-wrap').before(tag);
    });
}

function addProductTag(id, name) {
    if (selectedProducts.includes(id)) return;
    selectedProducts.push(id);
    renderProductTags();

    // Remove from search results
    $('.prod-result-item[data-id="' + id + '"]').addClass('selected');
    $('#prod-search-input').val('').focus();
}

function removeProductTag(id, e) {
    if (e) e.stopPropagation();
    selectedProducts = selectedProducts.filter(function (p) { return p !== id; });
    renderProductTags();
    $('.prod-result-item[data-id="' + id + '"]').removeClass('selected');
}

function clearErrors() {
    $('.has-error').removeClass('has-error');
    $('.field-error-msg').hide().text('');
}

function saveProvider() {
    clearErrors();
    var id = $('#provider-id').val();
    var name = $('#prov-name').val().trim();

    if (!name) {
        $('#prov-name').addClass('has-error');
        $('#err-prov-name').text('Provider name is required.').show();
        $('#prov-name').focus();
        return;
    }

    var url = id
        ? '{{ url('admin/providers') }}/' + id
        : '{{ route('admin.providers.store') }}';

    var fd = new FormData();
    fd.append('name', name);
    fd.append('mobile', $('#prov-mobile').val().trim());
    fd.append('address', $('#prov-address').val().trim());
    fd.append('city', $('#prov-city').val().trim());
    selectedProducts.forEach(function (pid) { fd.append('products[]', pid); });
    if (id) fd.append('_method', 'PUT');

    var $btn = $('#btn-save-provider');
    $btn.prop('disabled', true).html('<span class="spinner-sm"></span> Saving…');

    $.ajax({
        url: url,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            toast(res.message || (id ? 'Provider updated.' : 'Provider added.'));
            closeModal();
            location.reload();
        },
        error: function (xhr) {
            var errs = xhr.responseJSON && xhr.responseJSON.errors;
            if (errs) {
                $.each(errs, function (field, msgs) {
                    var $inp = $('#prov-' + field);
                    if ($inp.length) {
                        $inp.addClass('has-error');
                        $('#err-prov-' + field).text(msgs[0]).show();
                    }
                });
                toast('Please fix the errors above.', 'error');
            } else {
                toast('Something went wrong. Please try again.', 'error');
            }
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Provider');
        }
    });
}

function deleteProvider(id, name) {
    if (!confirm('Delete provider "' + name + '"?\n\nThis will remove all product associations. This action cannot be undone.')) return;

    $.ajax({
        url: '{{ url('admin/providers') }}/' + id,
        method: 'DELETE',
        success: function (res) {
            toast(res.message || 'Provider deleted.');
            $('#provider-row-' + id).fadeOut(350, function () { $(this).remove(); });
        },
        error: function () {
            toast('Could not delete provider.', 'error');
        }
    });
}

function toggleProdExpand(providerId, total) {
    var $panel = $('#prod-expand-' + providerId);
    var $btn = $('#more-btn-' + providerId);
    if ($panel.is(':visible')) {
        $panel.removeClass('show').hide();
        $btn.text('+' + (total - 3) + ' more');
    } else {
        $panel.addClass('show').show();
        $btn.text('− Less');
    }
}
</script>
@endpush