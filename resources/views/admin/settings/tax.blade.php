@extends('admin.settings.index')

@section('title', 'Tax & Class Settings')

@push('styles')
<style>
    #toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
    .toast-msg {
        display:flex; align-items:center; gap:8px;
        padding:10px 14px; border-radius:5px; margin-bottom:7px;
        font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10);
    }
    .toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
    .toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
    .toast-msg.info    { background:#eff6ff; border-left:4px solid #2563eb; color:#1d4ed8; }

    .tax-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px 24px; margin-bottom: 16px; }
    .tax-card-title {
        font-size: 15px; font-weight: 700; color: #222;
        margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #eee;
        display: flex; align-items: center; gap: 8px;
    }
    .tax-card-title i { color: #7b1fa2; }

    .tax-section-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #888; margin: 0 0 12px;
    }

    .tax-form-row { display: flex; gap: 16px; flex-wrap: wrap; }
    .tax-form-group { flex: 1; min-width: 180px; margin-bottom: 14px; }
    .tax-form-group.full { min-width: 100%; }
    .tax-form-label { display: block; font-size: 12px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .tax-form-input {
        width: 100%; padding: 8px 12px; border: 1px solid #ccc;
        border-radius: 5px; font-size: 13px; box-sizing: border-box;
    }
    .tax-form-input:focus { border-color: #7b1fa2; outline: none; box-shadow: 0 0 0 3px rgba(123,31,162,0.08); }
    .tax-form-select {
        width: 100%; padding: 8px 12px; border: 1px solid #ccc;
        border-radius: 5px; font-size: 13px; box-sizing: border-box; background: #fff;
    }
    .tax-form-select:focus { border-color: #7b1fa2; outline: none; box-shadow: 0 0 0 3px rgba(123,31,162,0.08); }
    .tax-form-hint { display: block; font-size: 11px; color: #999; margin-top: 4px; }

    .tax-form-actions { display: flex; gap: 10px; margin-top: 8px; }

    .tax-save-btn {
        padding: 8px 20px; background: #2e7d32; color: #fff;
        border: none; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .tax-save-btn:hover { background: #1b5e20; }
    .tax-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    /* Charges list */
    .tax-charges-list { margin-top: 12px; }
    .tax-charge-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 14px; border: 1px solid #e0e0e0; border-radius: 5px;
        margin-bottom: 8px; background: #fafafa;
    }
    .tax-charge-info { flex: 1; }
    .tax-charge-name { font-size: 13px; font-weight: 600; color: #222; }
    .tax-charge-value { font-size: 12px; color: #666; margin-top: 2px; }
    .tax-charge-type-badge {
        display: inline-block; font-size: 10px; font-weight: 700;
        padding: 2px 6px; border-radius: 3px; text-transform: uppercase;
    }
    .tax-charge-type-badge.percentage { background: #e3f2fd; color: #1565c0; }
    .tax-charge-type-badge.amount { background: #f3e5f5; color: #7b1fa2; }

    .tax-charge-actions { display: flex; gap: 6px; }
    .tax-charge-edit-btn, .tax-charge-delete-btn {
        padding: 5px 10px; border: none; border-radius: 4px;
        font-size: 12px; font-weight: 600; cursor: pointer;
    }
    .tax-charge-edit-btn { background: #fff3e0; color: #e65100; }
    .tax-charge-edit-btn:hover { background: #ffe0b2; }
    .tax-charge-delete-btn { background: #ffebee; color: #c62828; }
    .tax-charge-delete-btn:hover { background: #ffcdd2; }

    /* Empty state */
    .tax-empty-charges {
        text-align: center; padding: 24px; color: #aaa;
        font-size: 13px; border: 1px dashed #ddd; border-radius: 5px;
        margin-top: 8px;
    }
    .tax-empty-charges i { font-size: 28px; margin-bottom: 8px; display: block; }

    /* Charge form inline edit */
    .charge-form-row { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; }
    .charge-form-row .tax-form-group { margin-bottom: 0; }

    /* Add charge btn */
    .tax-add-charge-btn {
        padding: 7px 16px; background: #fff; color: #7b1fa2;
        border: 1px dashed #7b1fa2; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
        margin-top: 8px;
    }
    .tax-add-charge-btn:hover { background: #f3e5f5; }

    .charge-inline-form {
        background: #f3e5f5; border: 1px solid #ce93d8; border-radius: 5px;
        padding: 16px; margin-bottom: 12px;
    }
    .charge-inline-form .charge-form-row { margin-bottom: 12px; }
    .charge-inline-actions { display: flex; gap: 8px; }
    .charge-save-btn {
        padding: 6px 16px; background: #7b1fa2; color: #fff; border: none;
        border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;
    }
    .charge-save-btn:hover { background: #6a1b9a; }
    .charge-cancel-btn {
        padding: 6px 16px; background: #fff; color: #666; border: 1px solid #ccc;
        border-radius: 4px; font-size: 12px; font-weight: 600; cursor: pointer;
    }
    .charge-cancel-btn:hover { background: #f5f5f5; }

    @media (max-width: 768px) {
        .tax-form-group { min-width: 100%; }
        .tax-form-row { flex-direction: column; }
        .charge-form-row { flex-direction: column; }
        .tax-charge-item { flex-wrap: wrap; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

{{-- GST Configuration --}}
<div class="tax-card">
    <h3 class="tax-card-title">
        <i class="fas fa-receipt"></i> Tax Configuration
    </h3>

    <div class="tax-form-row">
        <div class="tax-form-group">
            <label class="tax-form-label" for="gst-percentage">GST Percentage (%)</label>
            <input type="number" id="gst-percentage" class="tax-form-input" placeholder="e.g. 18" min="0" max="100" step="0.01">
            <span class="tax-form-hint">Applied to all orders automatically</span>
        </div>
    </div>

    <div class="tax-form-actions">
        <button type="button" id="tax-gst-save-btn" class="tax-save-btn">
            <i class="fas fa-save"></i> Save GST
        </button>
    </div>
</div>

{{-- Other Charges --}}
<div class="tax-card">
    <h3 class="tax-card-title">
        <i class="fas fa-plus-circle"></i> Additional Charges
    </h3>

    <div class="tax-section-label">Configured Charges</div>
    <div id="charges-list" class="tax-charges-list">
        <div class="tax-empty-charges">
            <i class="fas fa-inbox"></i>
            No additional charges configured yet.
        </div>
    </div>

    <div id="charge-inline-form" class="charge-inline-form" style="display:none;">
        <div class="charge-form-row">
            <div class="tax-form-group">
                <label class="tax-form-label">Charge Name</label>
                <input type="text" id="charge-name" class="tax-form-input" placeholder="e.g. Delivery Fee">
            </div>
            <div class="tax-form-group">
                <label class="tax-form-label">Type</label>
                <select id="charge-type" class="tax-form-select">
                    <option value="percentage">Percentage (%)</option>
                    <option value="amount">Fixed Amount (₹)</option>
                </select>
            </div>
            <div class="tax-form-group">
                <label class="tax-form-label" id="charge-value-label">Percentage (%)</label>
                <input type="number" id="charge-value" class="tax-form-input" placeholder="e.g. 5" min="0" step="0.01">
            </div>
        </div>
        <div class="charge-inline-actions">
            <button type="button" id="charge-save-btn" class="charge-save-btn">
                <i class="fas fa-check"></i> Save Charge
            </button>
            <button type="button" id="charge-cancel-btn" class="charge-cancel-btn">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </div>

    <button type="button" id="add-charge-btn" class="tax-add-charge-btn">
        <i class="fas fa-plus"></i> Add New Charge
    </button>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var editingChargeId = null;

    // Load saved settings
    function loadTaxSettings() {
        $.ajax({
            url: '{{ route("admin.settings.tax-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.data) return;

                // GST
                $('#gst-percentage').val(res.data.gst_percentage || '');

                // Other charges
                renderCharges(res.data.charges || []);
            }
        });
    }

    loadTaxSettings();

    // Save GST
    $(document).on('click', '#tax-gst-save-btn', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.settings.tax-gst-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                gst_percentage: $('#gst-percentage').val().trim(),
            },
            success: function (res) {
                toast(res.message || 'GST saved successfully.', 'success');
            },
            error: function (xhr) {
                toast(xhr.responseJSON?.message || 'Failed to save GST.', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save GST');
            }
        });
    });

    // Show add charge form
    $(document).on('click', '#add-charge-btn', function () {
        editingChargeId = null;
        $('#charge-inline-form').show();
        $('#charge-name').val('').prop('disabled', false);
        $('#charge-type').val('percentage').change();
        $('#charge-value').val('');
        $('#charge-inline-form').find('input, select').not('#charge-type').prop('disabled', false);
    });

    // Charge type change
    $(document).on('change', '#charge-type', function () {
        var type = $(this).val();
        $('#charge-value-label').text(type === 'percentage' ? 'Percentage (%)' : 'Amount (₹)');
        $('#charge-value').attr('placeholder', type === 'percentage' ? 'e.g. 5' : 'e.g. 50');
    });

    // Cancel charge form
    $(document).on('click', '#charge-cancel-btn', function () {
        $('#charge-inline-form').hide();
        editingChargeId = null;
    });

    // Save charge
    $(document).on('click', '#charge-save-btn', function () {
        var name = $('#charge-name').val().trim();
        var type = $('#charge-type').val();
        var value = $('#charge-value').val().trim();

        if (!name) {
            toast('Please enter a charge name.', 'error');
            return;
        }
        if (!value || isNaN(parseFloat(value))) {
            toast('Please enter a valid value.', 'error');
            return;
        }

        $.ajax({
            url: '{{ route("admin.settings.tax-charge-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: editingChargeId || '',
                name: name,
                type: type,
                value: value,
            },
            success: function (res) {
                toast(res.message || 'Charge saved successfully.', 'success');
                $('#charge-inline-form').hide();
                editingChargeId = null;
                loadTaxSettings();
            },
            error: function (xhr) {
                toast(xhr.responseJSON?.message || 'Failed to save charge.', 'error');
            }
        });
    });

    // Edit charge
    $(document).on('click', '.tax-charge-edit-btn', function () {
        var id = $(this).data('id');
        var $item = $(this).closest('.tax-charge-item');

        editingChargeId = id;
        $('#charge-name').val($item.data('name')).prop('disabled', false);
        $('#charge-type').val($item.data('type')).change();
        $('#charge-value').val($item.data('value'));
        $('#charge-inline-form').show();
        window.scrollTo({ top: $('#charge-inline-form').offset().top - 20, behavior: 'smooth' });
    });

    // Delete charge
    $(document).on('click', '.tax-charge-delete-btn', function () {
        if (!confirm('Delete this charge?')) return;
        var id = $(this).data('id');

        $.ajax({
            url: '{{ route("admin.settings.tax-charge-delete") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: id,
            },
            success: function (res) {
                toast(res.message || 'Charge deleted.', 'success');
                loadTaxSettings();
            },
            error: function (xhr) {
                toast(xhr.responseJSON?.message || 'Failed to delete charge.', 'error');
            }
        });
    });

    // Render charges list
    function renderCharges(charges) {
        var $list = $('#charges-list');

        if (!charges || charges.length === 0) {
            $list.html('<div class="tax-empty-charges"><i class="fas fa-inbox"></i> No additional charges configured yet.</div>');
            return;
        }

        var html = '';
        charges.forEach(function (c) {
            var displayValue = c.type === 'percentage' ? c.value + '%' : '₹' + parseFloat(c.value).toFixed(2);
            html += '<div class="tax-charge-item" data-id="' + c.id + '" data-name="' + escHtml(c.name) + '" data-type="' + c.type + '" data-value="' + c.value + '">' +
                '<div class="tax-charge-info">' +
                '<div class="tax-charge-name">' + escHtml(c.name) + '</div>' +
                '<div class="tax-charge-value">' + displayValue + '</div>' +
                '<span class="tax-charge-type-badge ' + c.type + '">' + (c.type === 'percentage' ? '%' : '₹') + '</span>' +
                '</div>' +
                '<div class="tax-charge-actions">' +
                '<button type="button" class="tax-charge-edit-btn" data-id="' + c.id + '"><i class="fas fa-pen"></i> Edit</button>' +
                '<button type="button" class="tax-charge-delete-btn" data-id="' + c.id + '"><i class="fas fa-trash"></i> Delete</button>' +
                '</div>' +
                '</div>';
        });

        $list.html(html);
    }

    function escHtml(str) {
        return $('<span>').text(str || '').html();
    }

    function toast(msg, type) {
        var icon = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
        var el = $('<div class="toast-msg ' + (type || 'success') + '">' +
            '<i class="fas ' + icon + '"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 4000);
    }
});
</script>
@endpush