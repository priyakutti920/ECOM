@extends('layouts.admin')

@section('title', 'Bonus')

@push('styles')
<style>
#toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
.toast-msg { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:5px; margin-bottom:7px; font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10); }
.toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
.toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
.toast-msg.info    { background:#f0f7ff; border-left:4px solid #2980b9; color:#1a5276; }

/* Modal */
.bonus-modal-backdrop { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.45); z-index:1040; display:none; }
.bonus-modal-backdrop.show { display:block; }
.bonus-modal {
    position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; border-radius:12px; box-shadow:0 12px 50px rgba(0,0,0,0.18);
    width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; z-index:1041;
    display:none;
}
.bonus-modal.show { display:block; }
.bonus-modal .modal-header {
    padding:18px 24px; border-bottom:1px solid #eee;
    display:flex; align-items:center; justify-content:space-between;
}
.bonus-modal .modal-header h3 { margin:0; font-size:18px; font-weight:700; color:#1a1a2e; }
.bonus-modal .modal-body { padding:24px; }
.bonus-modal .modal-footer { padding:14px 24px; border-top:1px solid #eee; display:flex; gap:10px; justify-content:flex-end; }
.bonus-modal .close-btn {
    width:32px; height:32px; border-radius:50%; border:none; background:#f0f0f0;
    cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center;
    color:#666; transition:background .15s;
}
.bonus-modal .close-btn:hover { background:#e0e0e0; }

/* Form */
.bonus-form-group { margin-bottom:18px; }
.bonus-form-group label { font-weight:600; font-size:13px; color:#333; display:block; margin-bottom:6px; }
.bonus-form-group input, .bonus-form-group select {
    width:100%; padding:9px 12px; border:1.5px solid #ddd; border-radius:8px;
    font-size:13px; font-family:inherit; box-sizing:border-box;
    transition:border-color .2s, box-shadow .2s;
}
.bonus-form-group input:focus, .bonus-form-group select:focus {
    border-color:#3a7bd5; box-shadow:0 0 0 3px rgba(58,123,213,0.12); outline:none;
}
.bonus-form-group input.has-error, .bonus-form-group select.has-error { border-color:#c0392b; }
.field-error-msg { font-size:11px; color:#c0392b; display:none; margin-top:4px; }

/* Toggle */
.toggle-wrap { display:flex; align-items:center; gap:10px; margin-top:4px; }
.toggle-label { font-size:12px; color:#888; }
.toggle-switch { position:relative; width:42px; height:24px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#ccc; border-radius:24px; transition:.2s; }
.toggle-slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; }
.toggle-switch input:checked + .toggle-slider { background:#27ae60; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(18px); }

/* Cards */
.bonus-cards { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px; margin-top:16px; }
.bonus-card {
    border:1.5px solid #e8e8e8; border-radius:12px; padding:22px;
    background:#fff; transition:box-shadow .2s, border-color .2s;
    position:relative; overflow:hidden;
}
.bonus-card:hover { box-shadow:0 4px 20px rgba(0,0,0,0.08); border-color:#bcd7ea; }
.bonus-card.inactive { opacity:.5; }
.bonus-card.inactive .bonus-percent-val { color:#aaa; }

.bonus-card .top-bar {
    position:absolute; top:0; left:0; right:0; height:4px;
    background:linear-gradient(90deg, #3a7bd5, #5a9fd4);
    border-radius:12px 12px 0 0;
}
.bonus-card.inactive .top-bar { background:#ccc; }

.bonus-card-name { font-size:15px; font-weight:700; color:#1a1a2e; margin-bottom:14px; line-height:1.3; }

.bonus-percent-wrap { display:flex; align-items:baseline; gap:8px; margin-bottom:14px; }
.bonus-percent-val { font-size:36px; font-weight:800; color:#3a7bd5; line-height:1; }
.bonus-percent-label { font-size:13px; color:#888; font-weight:500; }

.bonus-meta { display:flex; flex-direction:column; gap:8px; }
.bonus-meta-row { display:flex; align-items:center; gap:8px; font-size:13px; color:#555; }
.bonus-meta-row i { color:#aaa; width:14px; text-align:center; }
.bonus-meta-row span { font-weight:500; }

.bonus-card-footer { display:flex; align-items:center; justify-content:space-between; margin-top:16px; padding-top:14px; border-top:1px solid #f0f0f0; }
.bonus-active-badge {
    display:inline-flex; align-items:center; gap:5px; padding:3px 10px;
    border-radius:20px; font-size:11px; font-weight:700;
}
.bonus-active-badge.active { background:#e8f5e9; color:#2e7d32; }
.bonus-active-badge.inactive { background:#f5f5f5; color:#999; }
.bonus-card-actions { display:flex; gap:6px; }

/* Page */
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
.page-header h2 { margin:0; font-weight:700; font-size:22px; color:#1a1a2e; }

.btn-add {
    padding:9px 22px; background:#3a7bd5; color:#fff; border:none; border-radius:8px;
    font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px;
    transition:background .15s;
}
.btn-add:hover { background:#2f6bc4; }

/* Actions */
.btn-sm-action { padding:5px 10px; border-radius:6px; border:none; cursor:pointer; font-size:12px; display:inline-flex; align-items:center; gap:4px; transition:background .15s; }
.btn-edit-sm { background:#e8f4fd; color:#2980b9; border:1px solid #bcd7ea; }
.btn-edit-sm:hover { background:#d4eaf8; }
.btn-delete-sm { background:#fdecea; color:#c0392b; border:1px solid #f5c6cb; }
.btn-delete-sm:hover { background:#fbd8d5; }

/* Example */
.bonus-example { background:#f8fbff; border:1px solid #d0e8f8; border-radius:8px; padding:10px 14px; font-size:12px; color:#2980b9; margin-top:4px; }
.bonus-example i { margin-right:5px; }

/* Empty state */
.empty-state { text-align:center; padding:60px 20px; color:#aaa; grid-column:1 / -1; }
.empty-state i { font-size:48px; margin-bottom:14px; display:block; color:#ddd; }
.empty-state p { font-size:15px; margin:0 0 16px; }

.btn-save { padding:10px 24px; background:#3a7bd5; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-save:hover { background:#2f6bc4; }
.btn-save:disabled { opacity:.6; cursor:not-allowed; }
.btn-cancel { padding:10px 18px; background:#f0f0f0; color:#555; border:1px solid #ccc; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-cancel:hover { background:#e8e8e8; }
.spinner-sm { display:inline-block; width:14px; height:14px; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

@media (max-width: 600px) {
    .bonus-cards { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
<div id="toast-wrap"></div>

{{-- Add/Edit Modal --}}
<div class="bonus-modal-backdrop" id="bonus-backdrop" onclick="closeBonusModal()"></div>
<div class="bonus-modal" id="bonus-modal">
    <div class="modal-header">
        <h3 id="bonus-modal-title"><i class="fas fa-gift"></i> Add Bonus</h3>
        <button class="close-btn" onclick="closeBonusModal()">&times;</button>
    </div>
    <form id="bonus-form" onsubmit="return false;">
        <input type="hidden" id="bonus-id" value="">
        <div class="modal-body">

            <div class="bonus-form-group">
                <label>Bonus Name <span style="color:#c0392b;">*</span></label>
                <input type="text" id="bonus-name" placeholder="e.g. Festival Offer" maxlength="200">
                <span class="field-error-msg" id="err-bonus-name"></span>
            </div>

            <div class="bonus-form-group">
                <label>Bonus Percentage (%) <span style="color:#c0392b;">*</span></label>
                <input type="number" id="bonus-percent" placeholder="e.g. 5" step="0.01" min="0.01" max="100">
                <span class="field-error-msg" id="err-bonus-percent"></span>
                <div class="bonus-example">
                    <i class="fas fa-calculator"></i>
                    e.g. 5% — when a customer orders above the minimum, a coupon worth <b>5% of the order total</b> is auto-issued to their account for use on a <b>future order</b>. It does <b>not</b> reduce the current order.
                </div>
            </div>

            <div class="bonus-form-group">
                <label>Minimum Purchase Amount (₹) <span style="color:#c0392b;">*</span></label>
                <input type="number" id="bonus-min-amount" placeholder="e.g. 500" step="0.01" min="100">
                <span class="field-error-msg" id="err-bonus-min-amount"></span>
                <div class="bonus-example">
                    <i class="fas fa-info-circle"></i>
                    Must be at least ₹100. When an order meets this minimum, a coupon worth <b>% of the order total</b> is automatically credited to the customer's account — they can use it on their next order.
                </div>
            </div>

            <div class="bonus-form-group">
                <div class="toggle-wrap">
                    <label class="toggle-switch">
                        <input type="checkbox" id="bonus-is-active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label">Active — bonus will be applied when eligible</span>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeBonusModal()">Cancel</button>
            <button type="submit" class="btn-save" id="btn-save-bonus" onclick="saveBonus()">
                <i class="fas fa-save"></i> Save Bonus
            </button>
        </div>
    </form>
</div>

{{-- Main Content --}}
<div style="max-width:1100px; margin:0 auto;">

    <div class="page-header">
        <h2>
            <i class="fas fa-gift" style="font-size:20px; margin-right:8px; color:#3a7bd5;"></i>
            Bonus
        </h2>
        <button class="btn-add" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add Bonus
        </button>
    </div>

    {{-- Cards --}}
    <div class="bonus-cards" id="bonus-cards-list">
        @forelse($bonuses as $bonus)
        <div class="bonus-card {{ $bonus->is_active ? '' : 'inactive' }}" id="bonus-card-{{ $bonus->id }}">
            <div class="top-bar"></div>
            <div class="bonus-card-name">{{ $bonus->name }}</div>

            <div class="bonus-percent-wrap">
                <div class="bonus-percent-val">{{ $bonus->display_percent }}</div>
                <div class="bonus-percent-label">off</div>
            </div>

            <div class="bonus-meta">
                <div class="bonus-meta-row">
                    <i class="fas fa-indian-rupee-sign"></i>
                    <span>Min purchase: <b>₹{{ number_format($bonus->min_amount, 0) }}</b></span>
                </div>
                <div class="bonus-meta-row">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Issues coupon worth <b>{{ $bonus->display_percent }}</b> of order for next purchase</span>
                </div>
            </div>

            <div class="bonus-card-footer">
                <span class="bonus-active-badge {{ $bonus->is_active ? 'active' : 'inactive' }}">
                    <i class="fas fa-circle" style="font-size:7px;"></i>
                    {{ $bonus->is_active ? 'Active' : 'Inactive' }}
                </span>
                <div class="bonus-card-actions">
                    <button class="btn-sm-action btn-edit-sm"
                        onclick='openEditModal({{ $bonus->id }}, {{ json_encode($bonus->name) }}, {{ $bonus->bonus_percent }}, {{ $bonus->min_amount }}, {{ $bonus->is_active ? "true" : "false" }})'
                        title="Edit">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-sm-action btn-delete-sm"
                        onclick="deleteBonus({{ $bonus->id }}, {{ json_encode($bonus->name) }})"
                        title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-gift"></i>
            <p>No bonuses created yet.</p>
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Create First Bonus
            </button>
        </div>
        @endforelse
    </div>

    @if($bonuses->hasPages())
    <div style="margin-top:20px; display:flex; justify-content:center;">
        {{ $bonuses->links() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
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
    $('#bonus-id').val('');
    $('#bonus-form')[0].reset();
    clearErrors();
    $('#bonus-is-active').prop('checked', true);
    $('#bonus-modal-title').html('<i class="fas fa-gift"></i> Add Bonus');
    $('#bonus-modal').addClass('show');
    $('#bonus-backdrop').addClass('show');
    $('#bonus-name').focus();
}

function openEditModal(id, name, bonusPercent, minAmount, isActive) {
    $('#bonus-id').val(id);
    clearErrors();
    $('#bonus-name').val(name);
    $('#bonus-percent').val(bonusPercent);
    $('#bonus-min-amount').val(minAmount);
    $('#bonus-is-active').prop('checked', isActive);
    $('#bonus-modal-title').html('<i class="fas fa-pen"></i> Edit Bonus');
    $('#bonus-modal').addClass('show');
    $('#bonus-backdrop').addClass('show');
    $('#bonus-name').focus();
}

function closeBonusModal() {
    $('#bonus-modal').removeClass('show');
    $('#bonus-backdrop').removeClass('show');
}

function clearErrors() {
    $('.has-error').removeClass('has-error');
    $('.field-error-msg').hide().text('');
}

function saveBonus() {
    clearErrors();
    var id = $('#bonus-id').val();
    var name = $('#bonus-name').val().trim();

    if (!name) {
        $('#bonus-name').addClass('has-error');
        $('#err-bonus-name').text('Bonus name is required.').show();
        $('#bonus-name').focus();
        return;
    }

    var percent = parseFloat($('#bonus-percent').val());
    if (!percent || percent <= 0) {
        $('#bonus-percent').addClass('has-error');
        $('#err-bonus-percent').text('Bonus percentage must be greater than 0.').show();
        $('#bonus-percent').focus();
        return;
    }

    var minAmount = parseFloat($('#bonus-min-amount').val());
    if (isNaN(minAmount) || minAmount < 100) {
        $('#bonus-min-amount').addClass('has-error');
        $('#err-bonus-min-amount').text('Minimum amount must be at least ₹100.').show();
        $('#bonus-min-amount').focus();
        return;
    }

    var isActive = $('#bonus-is-active').is(':checked');

    var url = id
        ? '{{ url('admin/bonuses') }}/' + id
        : '{{ route('admin.bonuses.store') }}';

    var fd = new FormData();
    fd.append('name', name);
    fd.append('bonus_percent', percent);
    fd.append('min_amount', minAmount);
    fd.append('is_active', isActive ? '1' : '0');
    if (id) fd.append('_method', 'PUT');

    var $btn = $('#btn-save-bonus');
    $btn.prop('disabled', true).html('<span class="spinner-sm"></span> Saving…');

    $.ajax({
        url: url,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function () {
            toast(id ? 'Bonus updated.' : 'Bonus created.');
            closeBonusModal();
            location.reload();
        },
        error: function (xhr) {
            var errs = xhr.responseJSON && xhr.responseJSON.errors;
            if (errs) {
                $.each(errs, function (field, msgs) {
                    var $inp = $('#bonus-' + field.replace('_', '-'));
                    if ($inp.length) {
                        $inp.addClass('has-error');
                        $('#err-bonus-' + field.replace('_', '-')).text(msgs[0]).show();
                    }
                });
                toast('Please fix the errors above.', 'error');
            } else {
                toast('Something went wrong.', 'error');
            }
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Bonus');
        }
    });
}

function deleteBonus(id, name) {
    if (!confirm('Delete bonus "' + name + '"?\n\nThis action cannot be undone.')) return;
    $.ajax({
        url: '{{ url('admin/bonuses') }}/' + id,
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'DELETE' },
        success: function () {
            toast('Bonus deleted.');
            $('#bonus-card-' + id).fadeOut(350, function () { $(this).remove(); });
            if ($('.bonus-card').length === 0) location.reload();
        },
        error: function () {
            toast('Could not delete bonus.', 'error');
        }
    });
}
</script>
@endpush