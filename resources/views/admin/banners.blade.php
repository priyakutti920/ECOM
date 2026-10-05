@extends('layouts.admin')

@section('title', 'Banners & Sliders')

@push('styles')
<style>
/* ── Banners Page Layout & Header ── */
.banner-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}
.banner-page-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 8px;
}
.banner-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ── Tabs (Consistent with Appearance Files) ── */
.banner-tabs-nav {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 18px;
    overflow-x: auto;
}
.banner-tab-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13.5px;
    font-weight: 600;
    color: #64748b;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}
.banner-tab-item:hover {
    color: #1d4ed8;
    text-decoration: none;
}
.banner-tab-item.active {
    color: #1d4ed8;
    border-bottom-color: #1d4ed8;
}
.banner-tab-badge {
    background: #f1f5f9;
    color: #475569;
    padding: 2px 7px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
}
.banner-tab-item.active .banner-tab-badge {
    background: #dbeafe;
    color: #1e40af;
}

/* ── Filter Bar ── */
.banner-filter-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
    background: #fff;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.banner-search-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    max-width: 420px;
}
.banner-search-input {
    width: 100%;
    padding: 7px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.15s;
}
.banner-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59,130,246,0.15);
}
.btn-view-toggle {
    padding: 6px 10px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: #64748b;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-view-toggle.active, .btn-view-toggle:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #94a3b8;
}

/* ── Table View ── */
.banner-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.banner-table th {
    background: #f8fafc;
    text-align: left;
    font-size: 12px;
    color: #475569;
    padding: 11px 14px;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
}
.banner-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    vertical-align: middle;
}
.banner-table tr:hover td { background: #f8fafc; }
.banner-row-img {
    width: 140px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.button-info-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 100px;
    background: #e8f4fd;
    color: #0a4b87;
    font-size: 11px;
    font-weight: 600;
    border: 1px solid #cce0ff;
}

/* ── Modal styling ── */
.banner-modal-backdrop { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1040; display:none; }
.banner-modal-backdrop.show { display:block; }
.banner-modal {
    position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; border-radius:10px; box-shadow:0 20px 60px rgba(0,0,0,0.22);
    width:560px; max-width:95vw; max-height:92vh; overflow-y:auto; z-index:1041;
    display:none; border:1px solid #e2e8f0;
}
.banner-modal.show { display:block; }
.banner-modal .modal-header {
    padding:16px 20px; border-bottom:1px solid #e2e8f0;
    display:flex; align-items:center; justify-content:space-between;
    background:#f8fafc; border-radius:10px 10px 0 0;
}
.banner-modal .modal-header h3 { margin:0; font-size:16px; font-weight:700; color:#0f172a; }
.banner-modal .modal-body { padding:20px; }
.banner-modal .modal-footer {
    padding:14px 20px; border-top:1px solid #e2e8f0;
    display:flex; gap:10px; justify-content:flex-end;
    background:#f8fafc; border-radius:0 0 10px 10px;
}
.banner-modal .close-btn {
    width:30px; height:30px; border-radius:6px; border:1px solid #cbd5e1; background:#fff;
    cursor:pointer; font-size:15px; display:flex; align-items:center; justify-content:center;
    color:#64748b; transition:all .15s;
}
.banner-modal .close-btn:hover { background:#f1f5f9; color:#0f172a; }

.banner-form-group { margin-bottom:15px; }
.banner-form-group label { font-weight:600; font-size:13px; color:#1e293b; display:block; margin-bottom:6px; }
.banner-form-group input[type=text], .banner-form-group input[type=number], .banner-form-group input[type=file] {
    width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;
    font-size:13px; background:#fff; transition:border-color .15s, box-shadow .15s;
}
.banner-form-group input:focus {
    border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,0.15); outline:none;
}
.banner-form-group input.has-error { border-color:#ef4444; }
.field-error-msg { font-size:11.5px; color:#ef4444; display:none; margin-top:4px; }

/* ── Toggle Switch ── */
.toggle-wrap { display:flex; align-items:center; gap:10px; }
.toggle-label { font-size:12.5px; color:#475569; }
.toggle-switch { position:relative; width:40px; height:22px; display:inline-block; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#cbd5e1; border-radius:22px; transition:.2s; }
.toggle-slider:before { position:absolute; content:""; height:16px; width:16px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.2s; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.toggle-switch input:checked + .toggle-slider { background:#16a34a; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(18px); }

/* ── Button-settings sub-block ── */
.banner-button-settings { display:none; background:#f8fafc; padding:14px; border-radius:8px; border:1px solid #e2e8f0; margin-top:10px; }
.banner-button-settings.show { display:block; }
.img-preview-container { margin-top:10px; display:none; }
.img-preview-container img { max-width:100%; height:110px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1; }

.empty-state-box {
    padding: 50px 20px;
    text-align: center;
    background: #fff;
    border-radius: 8px;
    border: 1px dashed #cbd5e1;
    color: #64748b;
}
.empty-state-box i { font-size: 46px; color: #94a3b8; margin-bottom: 12px; display: block; }
</style>
@endpush

@section('content')
<div style="max-width:1300px; margin:0 auto; padding:10px 0 30px;">

    {{-- 1. Header Toolbar --}}
    <div class="banner-page-header">
        <div>
            <h2><i class="fas fa-image text-primary"></i> Storefront Banners & Sliders</h2>
            <p style="margin:4px 0 0; font-size:13px; color:#64748b;">
                Manage homepage carousel banners, promotional graphics, and active campaign sliders.
            </p>
        </div>
        <div class="banner-header-actions">
            <a href="{{ route('admin.appearance.files.index', ['folder' => 'banners']) }}" class="btn btn-default" style="font-size:13px;">
                <i class="fas fa-photo-video"></i> Media Library
            </a>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Banner
            </button>
        </div>
    </div>

    {{-- 2. Status Filter Tabs --}}
    <nav class="banner-tabs-nav" aria-label="Banner status filters">
        <a href="{{ route('admin.banners.index', ['status' => 'all', 'q' => request('q')]) }}"
           class="banner-tab-item {{ $status === 'all' ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i> All Banners
            <span class="banner-tab-badge">{{ $totalCount }}</span>
        </a>
        <a href="{{ route('admin.banners.index', ['status' => 'active', 'q' => request('q')]) }}"
           class="banner-tab-item {{ $status === 'active' ? 'active' : '' }}">
            <i class="fas fa-check-circle text-success"></i> Active
            <span class="banner-tab-badge">{{ $activeCount }}</span>
        </a>
        <a href="{{ route('admin.banners.index', ['status' => 'inactive', 'q' => request('q')]) }}"
           class="banner-tab-item {{ $status === 'inactive' ? 'active' : '' }}">
            <i class="fas fa-pause-circle text-muted"></i> Inactive
            <span class="banner-tab-badge">{{ $inactiveCount }}</span>
        </a>
        <a href="{{ route('admin.banners.index', ['status' => 'trashed', 'q' => request('q')]) }}"
           class="banner-tab-item {{ $status === 'trashed' ? 'active' : '' }}">
            <i class="fas fa-trash-alt text-danger"></i> Trash
            <span class="banner-tab-badge">{{ $trashedCount }}</span>
        </a>
    </nav>

    {{-- 3. Filter & View Controls --}}
    <div class="banner-filter-bar">
        <form method="GET" action="{{ route('admin.banners.index') }}" class="banner-search-form">
            <input type="hidden" name="status" value="{{ $status }}">
            <div style="position:relative; width:100%;">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search by title, tagline, or button..."
                       class="banner-search-input" />
            </div>
            <button type="submit" class="btn btn-default" style="padding:7px 12px; font-size:13px;">
                <i class="fas fa-search"></i>
            </button>
            @if(request('q'))
                <a href="{{ route('admin.banners.index', ['status' => $status]) }}" class="btn btn-default text-danger" title="Clear Search">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>

        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" class="btn-view-toggle" id="btn-view-grid" onclick="switchBannerView('grid')" title="Card Grid View">
                <i class="fas fa-th-large"></i> Grid
            </button>
            <button type="button" class="btn-view-toggle" id="btn-view-table" onclick="switchBannerView('table')" title="Table View">
                <i class="fas fa-list"></i> Table
            </button>
        </div>
    </div>

    {{-- Notice when viewing Trash --}}
    @if($status === 'trashed')
        <div class="alert alert-warning" style="display:flex; align-items:center; gap:10px; font-size:13px;">
            <i class="fas fa-info-circle fa-lg"></i>
            <span>
                <strong>Trash View:</strong> Deleted banners do not appear on the storefront. You can restore them to Active status or permanently delete them from storage and database.
            </span>
        </div>
    @endif

    {{-- Select All Toolbar --}}
    @if($banners->count() > 0)
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; padding:8px 14px; background:#fff; border:1px solid #e2e8f0; border-radius:6px;">
            <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:13px; color:#334155; margin:0;">
                <input type="checkbox" id="bulkMasterCheck"> Select All on Page
            </label>
            <span class="text-muted" style="font-size:12px;">Showing {{ $banners->firstItem() }}-{{ $banners->lastItem() }} of {{ $banners->total() }}</span>
        </div>
    @endif

    {{-- 4. Content Area: Unified Media Card Grid View --}}
    <div id="banners-grid-view">
        @if($banners->count() > 0)
            <div class="uni-media-grid">
                @foreach($banners as $banner)
                    <x-media-card :banner="$banner" type="banner" />
                @endforeach
            </div>
        @else
            <div class="empty-state-box">
                <i class="fas fa-images"></i>
                <h4 style="margin:0 0 6px; font-weight:600; color:#1e293b;">No banners found</h4>
                <p style="margin:0 0 16px; font-size:13px;">
                    @if(request('q'))
                        No banners matched your search query "{{ request('q') }}".
                    @elseif($status === 'trashed')
                        The trash is currently empty.
                    @else
                        No banners created yet in this view.
                    @endif
                </p>
                @if($status !== 'trashed')
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Create First Banner
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- 5. Content Area: Table View --}}
    <div id="banners-table-view" style="display:none;">
        @if($banners->count() > 0)
            <div class="table-responsive">
                <table class="banner-table">
                    <thead>
                        <tr>
                            <th style="width:40px; text-align:center;">
                                <input type="checkbox" id="bulkMasterCheckTable" onchange="$('.bulk-item-check').prop('checked', this.checked); updateBulkBar();">
                            </th>
                            <th style="width:160px;">Image Preview</th>
                            <th>Primary Text & Subtitle</th>
                            <th>Call to Action</th>
                            <th style="width:80px; text-align:center;">Sort</th>
                            <th style="width:110px; text-align:center;">Status</th>
                            <th style="width:150px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="banners-table-body">
                        @foreach($banners as $banner)
                            <tr id="banner-table-row-{{ $banner->id }}">
                                <td style="text-align:center; vertical-align:middle;">
                                    <input type="checkbox" class="bulk-item-check" value="{{ $banner->id }}">
                                </td>
                                <td>
                                    <img src="{{ $banner->image_url }}"
                                         alt="{{ $banner->primary_text }}"
                                         class="banner-row-img"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';" />
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#1e293b; font-size:13.5px;">
                                        {{ $banner->primary_text ?: '—' }}
                                    </div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                        {{ $banner->tagline ?: '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if($banner->show_button && $banner->button_name)
                                        <span class="button-info-pill" title="Link: {{ $banner->button_link }}">
                                            <i class="fas fa-link"></i> {{ $banner->button_name }}
                                        </span>
                                    @else
                                        <span style="color:#94a3b8; font-style:italic; font-size:12px;">No button</span>
                                    @endif
                                </td>
                                <td style="text-align:center; font-weight:600; color:#475569;">
                                    {{ $banner->sort_order }}
                                </td>
                                <td style="text-align:center;">
                                    @if($banner->trashed())
                                        <span class="badge" style="background:#ef4444; color:#fff;">Trashed</span>
                                    @elseif($banner->is_active)
                                        <span class="badge" style="background:#16a34a; color:#fff;">Active</span>
                                    @else
                                        <span class="badge" style="background:#94a3b8; color:#fff;">Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    @if(!$banner->trashed())
                                        <button type="button"
                                                class="btn btn-xs {{ $banner->is_active ? 'btn-success' : 'btn-default' }}"
                                                onclick="toggleBannerActive({{ $banner->id }}, this)"
                                                title="Toggle Active/Inactive">
                                            <i class="fas {{ $banner->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-xs btn-primary"
                                                onclick="openEditModal({{ $banner->id }}, {{ json_encode($banner->primary_text) }}, {{ json_encode($banner->tagline) }}, {{ $banner->show_button ? 'true' : 'false' }}, {{ json_encode($banner->button_name) }}, {{ json_encode($banner->button_link) }}, {{ $banner->sort_order }}, {{ $banner->is_active ? 'true' : 'false' }}, {{ json_encode($banner->image_url) }})"
                                                title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-xs btn-default"
                                                onclick="openReplaceModal({{ $banner->id }}, '{{ addslashes($banner->primary_text) }}', '{{ $banner->image_url }}', 'banner')"
                                                title="Replace Image">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-xs btn-danger"
                                                onclick="deleteBannerItem({{ $banner->id }}, '{{ addslashes($banner->primary_text) }}')"
                                                title="Move to Trash">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-xs btn-success"
                                                onclick="restoreBannerItem({{ $banner->id }}, '{{ addslashes($banner->primary_text) }}')"
                                                title="Restore">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                        <button type="button"
                                                class="btn btn-xs btn-danger"
                                                onclick="forceDeleteBannerItem({{ $banner->id }}, '{{ addslashes($banner->primary_text) }}')"
                                                title="Delete Forever">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 6. Pagination --}}
    @if($banners->hasPages())
        <div style="margin-top:24px; display:flex; justify-content:center;">
            {{ $banners->links() }}
        </div>
    @endif

</div>

{{-- Add / Edit Banner Modal --}}
<div class="banner-modal-backdrop" id="banner-backdrop" onclick="closeBannerModal()"></div>
<div class="banner-modal" id="banner-modal" role="dialog" aria-labelledby="banner-modal-title" aria-modal="true">
    <div class="modal-header">
        <h3 id="banner-modal-title"><i class="fas fa-image text-primary"></i> Add Banner</h3>
        <button type="button" class="close-btn" onclick="closeBannerModal()" aria-label="Close dialog">&times;</button>
    </div>
    <form id="banner-form" onsubmit="return false;" enctype="multipart/form-data">
        <input type="hidden" id="banner-id" value="">
        <div class="modal-body">

            <div class="banner-form-group">
                <label for="banner-image">Banner Graphic <span style="color:#ef4444;" id="img-required-star">*</span></label>
                <input type="file" id="banner-image" accept="image/jpeg,image/png,image/webp,image/svg+xml" onchange="previewBannerFileInput(this)">
                <span class="field-error-msg" id="err-banner-image"></span>

                <div class="img-preview-container" id="banner-img-preview-wrap">
                    <label style="font-size:11.5px; color:#64748b; margin-top:6px; display:block;">Current Preview:</label>
                    <img id="banner-img-preview" src="" alt="preview" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                </div>
            </div>

            <div class="banner-form-group">
                <label for="banner-primary-text">Primary Text / Title</label>
                <input type="text" id="banner-primary-text" placeholder="e.g. Festival Season Sale" maxlength="255">
                <span class="field-error-msg" id="err-banner-primary-text"></span>
            </div>

            <div class="banner-form-group">
                <label for="banner-tagline">Tagline / Subtitle</label>
                <input type="text" id="banner-tagline" placeholder="e.g. Flat 40% Off Handcrafted Cotton Wear" maxlength="255">
                <span class="field-error-msg" id="err-banner-tagline"></span>
            </div>

            <div class="banner-form-group">
                <div class="toggle-wrap">
                    <input type="checkbox" id="banner-show-button" onchange="toggleButtonSettings(this)" style="width:16px; height:16px; cursor:pointer;">
                    <label for="banner-show-button" style="font-weight:600; cursor:pointer; margin:0;">Include Call-to-Action Button</label>
                </div>

                <div class="banner-button-settings" id="button-settings-wrap">
                    <div class="banner-form-group" style="margin-bottom:10px;">
                        <label for="banner-button-name">Button Label <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="banner-button-name" placeholder="e.g. Shop Collection" maxlength="100">
                        <span class="field-error-msg" id="err-banner-button-name"></span>
                    </div>
                    <div class="banner-form-group" style="margin-bottom:0;">
                        <label for="banner-button-link">Button Link URL <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="banner-button-link" placeholder="e.g. /shop or /category/sarees" maxlength="255">
                        <span class="field-error-msg" id="err-banner-button-link"></span>
                    </div>
                </div>
            </div>

            <div class="banner-form-group">
                <label for="banner-sort-order">Sort Order (Lower numbers display first)</label>
                <input type="number" id="banner-sort-order" placeholder="0" value="0" min="0">
                <span class="field-error-msg" id="err-banner-sort-order"></span>
            </div>

            <div class="banner-form-group">
                <div class="toggle-wrap">
                    <label class="toggle-switch">
                        <input type="checkbox" id="banner-is-active" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label"><strong>Active</strong> — Immediately visible on homepage carousel</span>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" onclick="closeBannerModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="btn-save-banner" onclick="saveBanner()">
                <i class="fas fa-save"></i> Save Banner
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Restore preferred view mode
    var savedView = localStorage.getItem('banner_view_mode') || 'grid';
    switchBannerView(savedView);
});

function switchBannerView(mode) {
    localStorage.setItem('banner_view_mode', mode);
    if (mode === 'table') {
        $('#banners-grid-view').hide();
        $('#banners-table-view').show();
        $('#btn-view-table').addClass('active');
        $('#btn-view-grid').removeClass('active');
    } else {
        $('#banners-table-view').hide();
        $('#banners-grid-view').show();
        $('#btn-view-grid').addClass('active');
        $('#btn-view-table').removeClass('active');
    }
}

function previewBannerFileInput(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#banner-img-preview').attr('src', e.target.result);
            $('#banner-img-preview-wrap').show();
        };
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
    clearBannerErrors();
    $('#banner-is-active').prop('checked', true);
    $('#banner-show-button').prop('checked', false);
    $('#button-settings-wrap').removeClass('show');
    $('#banner-img-preview-wrap').hide();
    $('#banner-img-preview').attr('src', '');
    $('#img-required-star').show();
    $('#banner-modal-title').html('<i class="fas fa-image text-primary"></i> Add Banner');
    $('#banner-modal').addClass('show');
    $('#banner-backdrop').addClass('show');
}

function openEditModalFromCard(id) {
    var $card = $('#media-card-banner-' + id);
    if (!$card.length) return;

    var primaryText = $card.attr('data-primary-text') || '';
    var tagline = $card.attr('data-tagline') || '';
    var showBtn = $card.attr('data-show-button') === '1';
    var btnName = $card.attr('data-button-name') || '';
    var btnLink = $card.attr('data-button-link') || '';
    var sortOrder = parseInt($card.attr('data-sort-order')) || 0;
    var isActive = $card.attr('data-is-active') === '1';
    var imgUrl = $card.attr('data-image-url') || '';

    openEditModal(id, primaryText, tagline, showBtn, btnName, btnLink, sortOrder, isActive, imgUrl);
}

function openEditModal(id, text, tagline, showBtn, btnName, btnLink, sortOrder, isActive, imgUrl) {
    $('#banner-id').val(id);
    clearBannerErrors();
    $('#banner-image').val('');
    $('#banner-primary-text').val(text || '');
    $('#banner-tagline').val(tagline || '');
    $('#banner-show-button').prop('checked', !!showBtn);
    if (showBtn) {
        $('#button-settings-wrap').addClass('show');
        $('#banner-button-name').val(btnName || '');
        $('#banner-button-link').val(btnLink || '');
    } else {
        $('#button-settings-wrap').removeClass('show');
        $('#banner-button-name').val('');
        $('#banner-button-link').val('');
    }
    $('#banner-sort-order').val(sortOrder || 0);
    $('#banner-is-active').prop('checked', !!isActive);

    if (imgUrl) {
        $('#banner-img-preview').attr('src', imgUrl);
        $('#banner-img-preview-wrap').show();
    } else {
        $('#banner-img-preview-wrap').hide();
    }
    $('#img-required-star').hide();

    $('#banner-modal-title').html('<i class="fas fa-pen text-primary"></i> Edit Banner #' + id);
    $('#banner-modal').addClass('show');
    $('#banner-backdrop').addClass('show');
}

function closeBannerModal() {
    $('#banner-modal').removeClass('show');
    $('#banner-backdrop').removeClass('show');
}

function clearBannerErrors() {
    $('.has-error').removeClass('has-error');
    $('.field-error-msg').hide().text('');
}

function saveBanner() {
    clearBannerErrors();
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
            $('#err-banner-button-name').text('Button label is required when button is enabled.').show();
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

    var $btn = $('#btn-save-banner');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: url,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            if (typeof showAdminToast === 'function') {
                showAdminToast(id ? 'Banner updated successfully.' : 'Banner created successfully.', 'success');
            }
            closeBannerModal();
            setTimeout(function () { location.reload(); }, 600);
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
                if (typeof showAdminToast === 'function') {
                    showAdminToast('Please fix the validation errors.', 'error');
                }
            } else {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Error saving banner.';
                if (typeof showAdminToast === 'function') {
                    showAdminToast(msg, 'error');
                }
            }
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Banner');
        }
    });
}

function toggleBannerActive(id, btn) {
    var $btn = $(btn);
    $btn.prop('disabled', true);

    $.ajax({
        url: '{{ url('admin/banners') }}/' + id + '/toggle',
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            if (res.success) {
                var active = res.is_active;
                var $card = $('#media-card-banner-' + id);
                $card.attr('data-is-active', active ? '1' : '0');

                // Update badge in card
                var $badge = $card.find('.uni-status-badge-' + id);
                if (active) {
                    $badge.removeClass('uni-badge-inactive').addClass('uni-badge-active')
                          .html('<i class="fas fa-check-circle"></i> Active');
                    $btn.removeClass('text-muted').addClass('text-success')
                        .html('<i class="fas fa-toggle-on"></i> <span class="uni-btn-label">Active</span>');
                } else {
                    $badge.removeClass('uni-badge-active').addClass('uni-badge-inactive')
                          .html('<i class="fas fa-times-circle"></i> Inactive');
                    $btn.removeClass('text-success').addClass('text-muted')
                        .html('<i class="fas fa-toggle-off"></i> <span class="uni-btn-label">Inactive</span>');
                }

                if (typeof showAdminToast === 'function') {
                    showAdminToast(res.message || 'Status updated.', 'success');
                }
            }
        },
        error: function () {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Failed to toggle status.', 'error');
            }
        },
        complete: function () {
            $btn.prop('disabled', false);
        }
    });
}

function deleteBannerItem(id, name) {
    if (!confirm('Are you sure you want to move "' + name + '" to trash? It will immediately stop appearing on the storefront.')) {
        return;
    }

    $.ajax({
        url: '{{ url('admin/banners') }}/' + id,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'DELETE'
        },
        success: function (res) {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Banner moved to trash.', 'success');
            }
            $('#media-card-banner-' + id).fadeOut(300, function () { $(this).remove(); });
            $('#banner-table-row-' + id).fadeOut(300, function () { $(this).remove(); });
        },
        error: function () {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Could not delete banner.', 'error');
            }
        }
    });
}

function restoreBannerItem(id, name) {
    $.ajax({
        url: '{{ url('admin/banners') }}/' + id + '/restore',
        method: 'POST',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Banner restored from trash.', 'success');
            }
            $('#media-card-banner-' + id).fadeOut(300, function () { $(this).remove(); });
            $('#banner-table-row-' + id).fadeOut(300, function () { $(this).remove(); });
        },
        error: function () {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Could not restore banner.', 'error');
            }
        }
    });
}

function forceDeleteBannerItem(id, name) {
    if (!confirm('PERMANENT DELETION: Are you sure you want to permanently delete "' + name + '"? This action cannot be undone.')) {
        return;
    }

    $.ajax({
        url: '{{ url('admin/banners') }}/' + id + '/force',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'DELETE'
        },
        success: function (res) {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Banner permanently deleted.', 'success');
            }
            $('#media-card-banner-' + id).fadeOut(300, function () { $(this).remove(); });
            $('#banner-table-row-' + id).fadeOut(300, function () { $(this).remove(); });
        },
        error: function () {
            if (typeof showAdminToast === 'function') {
                showAdminToast('Could not delete banner.', 'error');
            }
        }
    });
}

// Register Bulk Actions in Universal Bar
$(function () {
    var isTrash = '{{ $status }}' === 'trashed';
    var html = '';
    if (!isTrash) {
        html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeBannerBulk(\'activate\')"><i class="fas fa-check-circle"></i> Activate</button>';
        html += '<button type="button" class="bulk-action-btn" onclick="executeBannerBulk(\'deactivate\')"><i class="fas fa-pause-circle"></i> Deactivate</button>';
        html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeBannerBulk(\'delete\', \'Move {count} selected banner(s) to Trash?\')"><i class="fas fa-trash-alt"></i> Move to Trash</button>';
    } else {
        html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeBannerBulk(\'restore\')"><i class="fas fa-undo"></i> Restore</button>';
        html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeBannerBulk(\'force_delete\', \'PERMANENT DELETION: Are you sure you want to permanently delete {count} selected banner(s)? This cannot be undone!\')"><i class="fas fa-trash"></i> Permanently Delete</button>';
    }
    $('#bulkBarActions').html(html);
});

function executeBannerBulk(action, confirmMsg) {
    runBulkAction('{{ route("admin.banners.bulk-action") }}', action, {}, confirmMsg);
}
</script>
@endpush
