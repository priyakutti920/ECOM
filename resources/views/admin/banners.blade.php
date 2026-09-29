@extends('layouts.admin')

@section('title', 'Banners')

@push('styles')
<style>
/* ── Toasts (consistent with admin) ── */
#toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
.toast-msg { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:5px; margin-bottom:7px; font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10); }
.toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
.toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
.toast-msg.info    { background:#f0f7ff; border-left:4px solid #2980b9; color:#1a5276; }

/* ── Modal (panel-style to match admin) ── */
.banner-modal-backdrop { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.45); z-index:1040; display:none; }
.banner-modal-backdrop.show { display:block; }
.banner-modal {
    position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; border-radius:8px; box-shadow:0 12px 50px rgba(0,0,0,0.18);
    width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; z-index:1041;
    display:none;
    border: 1px solid #e7e7e7;
}
.banner-modal.show { display:block; }
.banner-modal .modal-header {
    padding:14px 18px; border-bottom:1px solid #e7e7e7;
    display:flex; align-items:center; justify-content:space-between;
    background: #f6f8fa; border-radius: 8px 8px 0 0;
}
.banner-modal .modal-header h3 { margin:0; font-size:15px; font-weight:600; color:#1a1a2e; }
.banner-modal .modal-body { padding:18px; }
.banner-modal .modal-footer { padding:12px 18px; border-top:1px solid #e7e7e7; display:flex; gap:8px; justify-content:flex-end; background:#fafbfc; border-radius: 0 0 8px 8px; }
.banner-modal .close-btn {
    width:28px; height:28px; border-radius:4px; border:1px solid #d5d9d9; background:#fff;
    cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;
    color:#666; transition:background .15s;
}
.banner-modal .close-btn:hover { background:#f0f0f0; }

/* ── Form (admin form-control look) ── */
.banner-form-group { margin-bottom:14px; }
.banner-form-group label { font-weight:600; font-size:12.5px; color:#333; display:block; margin-bottom:5px; }
.banner-form-group input[type=text], .banner-form-group input[type=number], .banner-form-group input[type=file] {
    width:100%; padding:7px 10px; border:1px solid #d5d9d9; border-radius:6px;
    font-size:13px; font-family:inherit; box-sizing:border-box; background:#fff;
    transition:border-color .15s, box-shadow .15s;
}
.banner-form-group input[type=file] { padding:4px; }
.banner-form-group input:focus {
    border-color:#3a7bd5; box-shadow:0 0 0 2px rgba(58,123,213,0.15); outline:none;
}
.banner-form-group input.has-error { border-color:#c0392b; }
.field-error-msg { font-size:11px; color:#c0392b; display:none; margin-top:3px; }

/* ── Toggle switch ── */
.toggle-wrap { display:flex; align-items:center; gap:8px; }
.toggle-label { font-size:12px; color:#666; }
.toggle-switch { position:relative; width:38px; height:22px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#ccc; border-radius:22px; transition:.2s; }
.toggle-slider:before { position:absolute; content:""; height:16px; width:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
.toggle-switch input:checked + .toggle-slider { background:#27ae60; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(16px); }

/* ── Button-settings sub-block ── */
.banner-button-settings { display: none; background: #f6f8fa; padding: 12px; border-radius: 6px; border: 1px solid #e7e7e7; margin-top: 10px; }
.banner-button-settings.show { display: block; }
.img-preview-container { margin-top: 8px; display: none; }
.img-preview-container img { max-width: 100%; height: 90px; object-fit: cover; border-radius: 4px; border: 1px solid #d5d9d9; }

/* ── Table (flat, rounded, blue header — matches coupons/blade) ── */
.banner-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; overflow: hidden; }
.banner-table th { background: #f6f8fa; text-align: left; font-size: 12px; color: #555; padding: 10px 12px; border-bottom: 1px solid #e7e7e7; font-weight: 600; }
.banner-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: middle; }
.banner-table tr:hover td { background: #fafbfc; }
.banner-table tr:last-child td { border-bottom: none; }
.banner-row-img { width: 140px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #d5d9d9; background:#f9f9f9; }

/* ── Pills / Tags (consistent with admin theme) ── */
.button-info-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 100px; background: #e8f4fd; color: #0a4b87; font-size: 11px; font-weight: 600; border: 1px solid #cce0ff; }
.tag { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; }
.tag.active   { background: #e8f5e9; color: #1a7a42; }
.tag.inactive { background: #f0f0f0; color: #777; }

/* ── Toolbar (matches coupons page) ── */
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; gap: 10px; flex-wrap: wrap; }
.toolbar h2 { margin:0; font-size: 20px; color: #1a1a2e; font-weight: 600; }

/* ── Buttons (admin blue) ── */
.btn-add {
    padding: 7px 16px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px;
    font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    transition: background .15s;
}
.btn-add:hover { background: #2f6bc4; }
.btn-save { padding: 7px 16px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-save:hover { background: #2f6bc4; }
.btn-save:disabled { opacity: .6; cursor: not-allowed; }
.btn-cancel { padding: 7px 14px; background: #fff; color: #555; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; }
.btn-cancel:hover { background: #f6f8fa; }

.btn-sm-action { padding: 4px 8px; border-radius: 4px; border: 1px solid; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 3px; transition: background .15s; background: #fff; }
.btn-edit-sm { color: #3a7bd5; border-color: #bcd7ea; }
.btn-edit-sm:hover { background: #e8f4fd; }
.btn-delete-sm { color: #c0392b; border-color: #f5c6cb; }
.btn-delete-sm:hover { background: #fdecea; }

.spinner-sm { display:inline-block; width:12px; height:12px; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

.empty { padding: 30px; text-align: center; color: #999; }
</style>
@endpush

@section('content')
<div id="toast-wrap"></div>

{{-- Add/Edit Modal --}}
<div class="banner-modal-backdrop" id="banner-backdrop" onclick="closeBannerModal()"></div>
<div class="banner-modal" id="banner-modal">
    <div class="modal-header">
        <h3 id="banner-modal-title"><i class="fas fa-image"></i> Add Banner</h3>
        <button class="close-btn" onclick="closeBannerModal()">&times;</button>
    </div>
    <form id="banner-form" onsubmit="return false;" enctype="multipart/form-data">
        <input type="hidden" id="banner-id" value="">
        <div class="modal-body">

            <div class="banner-form-group">
                <label>Banner Image <span style="color:#c0392b;" id="img-required-star">*</span></label>
                <input type="file" id="banner-image" accept="image/*" onchange="previewImage(this)">
                <span class="field-error-msg" id="err-banner-image"></span>
                <div class="img-preview-container" id="banner-img-preview-wrap">
                    <label style="font-size:11px; color:#888; margin-top:5px;">Preview:</label>
                    <img id="banner-img-preview" src="" alt="preview">
                </div>
            </div>

            <div class="banner-form-group">
                <label>Primary Text (Title)</label>
                <input type="text" id="banner-primary-text" placeholder="e.g. Great Indian Festival" maxlength="255">
                <span class="field-error-msg" id="err-banner-primary-text"></span>
            </div>

            <div class="banner-form-group">
                <label>Tagline (Subtitle)</label>
                <input type="text" id="banner-tagline" placeholder="e.g. Up to 70% off on electronics" maxlength="255">
                <span class="field-error-msg" id="err-banner-tagline"></span>
            </div>

            <div class="banner-form-group">
                <div class="toggle-wrap">
                    <input type="checkbox" id="banner-show-button" onchange="toggleButtonSettings(this)">
                    <label for="banner-show-button" style="font-weight:600; cursor:pointer; margin:0;">Show Button or Not</label>
                </div>

                <div class="banner-button-settings" id="button-settings-wrap">
                    <div class="banner-form-group" style="margin-bottom:10px;">
                        <label>Button Name <span style="color:#c0392b;">*</span></label>
                        <input type="text" id="banner-button-name" placeholder="e.g. Shop Now" maxlength="100">
                        <span class="field-error-msg" id="err-banner-button-name"></span>
                    </div>
                    <div class="banner-form-group" style="margin-bottom:0;">
                        <label>Button Link <span style="color:#c0392b;">*</span></label>
                        <input type="text" id="banner-button-link" placeholder="e.g. /products or /shop?category=1" maxlength="255">
                        <span class="field-error-msg" id="err-banner-button-link"></span>
                    </div>
                </div>
            </div>

            <div class="banner-form-group">
                <label>Sort Order</label>
                <input type="number" id="banner-sort-order" placeholder="e.g. 0" value="0" min="0">
                <span class="field-error-msg" id="err-banner-sort-order"></span>
            </div>

            <div class="banner-form-group">
                <div class="toggle-wrap">
                    <label class="toggle-switch">
                        <input type="checkbox" id="banner-is-active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Active — banner will display on homepage</span>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeBannerModal()">Cancel</button>
            <button type="submit" class="btn-save" id="btn-save-banner" onclick="saveBanner()">
                <i class="fas fa-save"></i> Save Banner
            </button>
        </div>
    </form>
</div>

{{-- Main Content --}}
<div style="max-width:1100px; margin:0 auto;">

    <div class="toolbar">
        <h2>
            <i class="fas fa-image" style="color:#3a7bd5;"></i> Banners
        </h2>
        <button class="btn-add" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add Banner
        </button>
    </div>

    {{-- Banners Table --}}
    <table class="banner-table">
        <thead>
            <tr>
                <th style="width:180px;">Image</th>
                <th>Primary Text / Tagline</th>
                <th>Button Settings</th>
                <th style="width:90px;">Order</th>
                <th style="width:100px;">Status</th>
                <th style="width:120px; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody id="banners-table-body">
            @forelse($banners as $banner)
            <tr id="banner-row-{{ $banner->id }}">
                <td>
                    <img src="{{ $banner->image_url }}" alt="banner" class="banner-row-img">
                </td>
                <td>
                    <div style="font-weight:600; color:#1a1a2e;">{{ $banner->primary_text ?: '—' }}</div>
                    <div style="font-size:11.5px; color:#888; margin-top:2px;">{{ $banner->tagline ?: '—' }}</div>
                </td>
                <td>
                    @if($banner->show_button)
                        <span class="button-info-pill" title="Link: {{ $banner->button_link }}">
                            <i class="fas fa-link"></i> {{ $banner->button_name }}
                        </span>
                    @else
                        <span style="color:#aaa; font-style:italic; font-size:12px;">No button</span>
                    @endif
                </td>
                <td>{{ $banner->sort_order }}</td>
                <td>
                    @if($banner->is_active)
                        <span class="tag active">Active</span>
                    @else
                        <span class="tag inactive">Inactive</span>
                    @endif
                </td>
                <td style="text-align:right;">
                    <button class="btn-sm-action btn-edit-sm"
                        onclick='openEditModal({{ $banner->id }}, {{ json_encode($banner->primary_text) }}, {{ json_encode($banner->tagline) }}, {{ $banner->show_button ? "true" : "false" }}, {{ json_encode($banner->button_name) }}, {{ json_encode($banner->button_link) }}, {{ $banner->sort_order }}, {{ $banner->is_active ? "true" : "false" }}, {{ json_encode($banner->image_url) }})'
                        title="Edit">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-sm-action btn-delete-sm"
                        onclick="deleteBanner({{ $banner->id }})"
                        title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="empty">
                    <i class="fas fa-images" style="font-size:42px; color:#ddd; display:block; margin-bottom:10px;"></i>
                    <p style="font-size:14px; margin:0 0 12px;">No banners created yet.</p>
                    <button class="btn-add" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Create First Banner
                    </button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($banners->hasPages())
    <div style="margin-top:20px; display:flex; justify-content:center;">
        {{ $banners->links() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
});

function toast(msg, type) {
    var icon = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
    var el = $('<div class="toast-msg ' + (type || 'success') + '"><i class="fas ' + icon + '"></i> ' + msg + '</div>');
    $('#toast-wrap').append(el);
    setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#banner-img-preview').attr('src', e.target.result);
            $('#banner-img-preview-wrap').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleButtonSettings(checkbox) {
    if ($(checkbox).is(':checked')) {
        $('#button-settings-wrap').addClass('show');
    } else {
        $('#button-settings-wrap').removeClass('show');
    }
}

function openAddModal() {
    $('#banner-id').val('');
    $('#banner-form')[0].reset();
    clearErrors();
    $('#banner-is-active').prop('checked', true);
    $('#banner-show-button').prop('checked', false);
    $('#button-settings-wrap').removeClass('show');
    $('#banner-img-preview-wrap').hide();
    $('#banner-img-preview').attr('src', '');
    $('#img-required-star').show();
    $('#banner-modal-title').html('<i class="fas fa-image"></i> Add Banner');
    $('#banner-modal').addClass('show');
    $('#banner-backdrop').addClass('show');
}

function openEditModal(id, text, tagline, showBtn, btnName, btnLink, sortOrder, isActive, imgUrl) {
    $('#banner-id').val(id);
    clearErrors();
    $('#banner-image').val('');
    $('#banner-primary-text').val(text);
    $('#banner-tagline').val(tagline);
    $('#banner-show-button').prop('checked', showBtn);
    if (showBtn) {
        $('#button-settings-wrap').addClass('show');
        $('#banner-button-name').val(btnName);
        $('#banner-button-link').val(btnLink);
    } else {
        $('#button-settings-wrap').removeClass('show');
        $('#banner-button-name').val('');
        $('#banner-button-link').val('');
    }
    $('#banner-sort-order').val(sortOrder);
    $('#banner-is-active').prop('checked', isActive);

    if (imgUrl) {
        $('#banner-img-preview').attr('src', imgUrl);
        $('#banner-img-preview-wrap').show();
    } else {
        $('#banner-img-preview-wrap').hide();
    }
    $('#img-required-star').hide(); // optional on edit

    $('#banner-modal-title').html('<i class="fas fa-pen"></i> Edit Banner');
    $('#banner-modal').addClass('show');
    $('#banner-backdrop').addClass('show');
}

function closeBannerModal() {
    $('#banner-modal').removeClass('show');
    $('#banner-backdrop').removeClass('show');
}

function clearErrors() {
    $('.has-error').removeClass('has-error');
    $('.field-error-msg').hide().text('');
}

function saveBanner() {
    clearErrors();
    var id = $('#banner-id').val();
    var imageFile = $('#banner-image')[0].files[0];

    if (!id && !imageFile) {
        $('#banner-image').addClass('has-error');
        $('#err-banner-image').text('Banner image file is required.').show();
        return;
    }

    var showBtn = $('#banner-show-button').is(':checked');
    var btnName = $('#banner-button-name').val().trim();
    var btnLink = $('#banner-button-link').val().trim();

    if (showBtn) {
        if (!btnName) {
            $('#banner-button-name').addClass('has-error');
            $('#err-banner-button-name').text('Button name is required when button is enabled.').show();
            $('#banner-button-name').focus();
            return;
        }
        if (!btnLink) {
            $('#banner-button-link').addClass('has-error');
            $('#err-banner-button-link').text('Button link is required when button is enabled.').show();
            $('#banner-button-link').focus();
            return;
        }
    }

    var primaryText = $('#banner-primary-text').val().trim();
    var tagline = $('#banner-tagline').val().trim();
    var sortOrder = parseInt($('#banner-sort-order').val()) || 0;
    var isActive = $('#banner-is-active').is(':checked');

    var url = id
        ? '{{ url('admin/banners') }}/' + id
        : '{{ route('admin.banners.store') }}';

    var fd = new FormData();
    if (imageFile) {
        fd.append('image', imageFile);
    }
    fd.append('primary_text', primaryText);
    fd.append('tagline', tagline);
    fd.append('show_button', showBtn ? '1' : '0');
    fd.append('button_name', btnName);
    fd.append('button_link', btnLink);
    fd.append('sort_order', sortOrder);
    fd.append('is_active', isActive ? '1' : '0');

    // Laravel requires _method=PUT to mock PUT on FormData requests
    if (id) {
        // We use POST with _method=PUT or CategoryController style where update accepts POST
        // But since we mapped it as POST in web.php (Route::post('/banners/{banner}'), we do not need PUT mock!
    }

    var $btn = $('#btn-save-banner');
    $btn.prop('disabled', true).html('<span class="spinner-sm"></span> Saving…');

    $.ajax({
        url: url,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            toast(id ? 'Banner updated successfully.' : 'Banner created successfully.');
            closeBannerModal();
            location.reload();
        },
        error: function (xhr) {
            var errs = xhr.responseJSON && xhr.responseJSON.errors;
            if (errs) {
                $.each(errs, function (field, msgs) {
                    var inputId = 'banner-' + field.replace('_', '-');
                    var $inp = $('#' + inputId);
                    if ($inp.length) {
                        $inp.addClass('has-error');
                        $('#err-' + inputId).text(msgs[0]).show();
                    }
                });
                toast('Please fix the validation errors.', 'error');
            } else {
                toast('Something went wrong. Please check files and fields.', 'error');
            }
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Banner');
        }
    });
}

function deleteBanner(id) {
    if (!confirm('Are you sure you want to delete this banner?')) return;
    $.ajax({
        url: '{{ url('admin/banners') }}/' + id,
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
        success: function () {
            toast('Banner deleted successfully.');
            $('#banner-row-' + id).fadeOut(350, function () { $(this).remove(); });
            if ($('#banners-table-body tr:visible').length === 0) location.reload();
        },
        error: function () {
            toast('Could not delete banner.', 'error');
        }
    });
}
</script>
@endpush
