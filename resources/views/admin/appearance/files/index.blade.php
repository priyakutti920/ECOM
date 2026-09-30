@extends('layouts.admin')

@section('title', 'Appearance — Files & Media Manager')

@section('content')
<div class="admin-content-wrap">

    {{-- Breadcrumb & Header --}}
    <div class="media-page-header">
        <div class="media-header-left">
            <h1 class="media-title"><i class="fas fa-photo-video text-primary"></i> Files &amp; Media Manager</h1>
            <p class="media-subtitle">Upload, manage, and use media assets for Store Logo, Favicon, Products, and Banners.</p>
        </div>
        <div class="media-header-actions">
            <form action="{{ route('admin.appearance.files.sync') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-default" title="Scan disk and import untracked files">
                    <i class="fas fa-sync-alt"></i> Sync Disk
                </button>
            </form>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('upload-zone-collapse').scrollIntoView({behavior: 'smooth'}); document.getElementById('files-input').click();">
                <i class="fas fa-cloud-upload-alt"></i> Upload Files
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
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stat Cards / Active Branding Widget --}}
    <div class="media-stats-grid">
        <div class="media-stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-folder-open"></i></div>
            <div class="stat-info">
                <span class="stat-value">{{ number_format($totalFiles) }}</span>
                <span class="stat-label">Total Files</span>
            </div>
        </div>

        <div class="media-stat-card">
            <div class="stat-icon stat-icon-purple"><i class="fas fa-hdd"></i></div>
            <div class="stat-info">
                <span class="stat-value">{{ $totalStorage }}</span>
                <span class="stat-label">Storage Used</span>
            </div>
        </div>

        <div class="media-stat-card media-brand-card">
            <div class="brand-preview-wrap">
                @if($currentLogoUrl)
                    <img src="{{ $currentLogoUrl }}" alt="Active Logo" class="brand-thumb-logo" id="active-logo-preview">
                @else
                    <span class="brand-none-badge">No Logo</span>
                @endif
            </div>
            <div class="stat-info">
                <span class="stat-value" style="font-size:14px; font-weight:700;">Store Logo</span>
                <span class="stat-label text-success"><i class="fas fa-check-circle"></i> Active on storefront</span>
            </div>
        </div>

        <div class="media-stat-card media-brand-card">
            <div class="brand-preview-wrap">
                @if($currentFaviconUrl)
                    <img src="{{ $currentFaviconUrl }}" alt="Active Favicon" class="brand-thumb-fav" id="active-fav-preview">
                @else
                    <span class="brand-none-badge">Default</span>
                @endif
            </div>
            <div class="stat-info">
                <span class="stat-value" style="font-size:14px; font-weight:700;">Store Favicon</span>
                <span class="stat-label text-success"><i class="fas fa-check-circle"></i> Active in browser tab</span>
            </div>
        </div>
    </div>

    {{-- Drag & Drop Upload Zone --}}
    <div class="upload-zone-panel" id="upload-zone-collapse">
        <form action="{{ route('admin.appearance.files.upload') }}" method="POST" enctype="multipart/form-data" id="media-upload-form">
            @csrf
            <div class="dropzone-box" id="dropzone-area" onclick="document.getElementById('files-input').click();">
                <i class="fas fa-cloud-upload-alt dropzone-icon"></i>
                <div class="dropzone-text">
                    <h4>Drop files here or click to browse</h4>
                    <p>Supports PNG, JPG, JPEG, WEBP, GIF, SVG, ICO (up to 20MB per file)</p>
                </div>
                <input type="file" name="files[]" id="files-input" multiple accept="image/*,.ico,.svg,.pdf" style="display:none;" onchange="handleFileSelect(this);">
            </div>

            <div class="upload-options-bar">
                <div class="folder-select-wrap">
                    <label><i class="fas fa-folder"></i> Destination Folder:</label>
                    <select name="folder" class="form-control input-sm" style="display:inline-block; width:auto;">
                        <option value="appearance" selected>Appearance / Branding</option>
                        <option value="settings">Settings / Logos</option>
                        <option value="products">Products</option>
                        <option value="banners">Banners</option>
                        <option value="general">General Media</option>
                    </select>
                </div>
                <div id="selected-files-summary" style="font-size:13px; font-weight:600; color:#3a7bd5;"></div>
                <button type="submit" class="btn btn-success btn-sm" id="btn-submit-upload" style="display:none;">
                    <i class="fas fa-upload"></i> Start Upload
                </button>
            </div>
        </form>
    </div>

    {{-- Filters & Search Toolbar --}}
    <div class="media-toolbar">
        <form action="{{ route('admin.appearance.files.index') }}" method="GET" class="media-filter-form">
            <div class="search-input-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search files by name..." class="form-control" autocomplete="off">
            </div>

            <div class="filter-group">
                <select name="folder" class="form-control" onchange="this.form.submit();">
                    <option value="all" {{ request('folder') === 'all' || !request('folder') ? 'selected' : '' }}>All Folders ({{ $totalFiles }})</option>
                    @foreach($folders as $fName => $fCount)
                        <option value="{{ $fName }}" {{ request('folder') === $fName ? 'selected' : '' }}>
                            {{ ucfirst($fName) }} ({{ $fCount }})
                        </option>
                    @endforeach
                </select>

                <select name="sort" class="form-control" onchange="this.form.submit();">
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="size_desc" {{ request('sort') === 'size_desc' ? 'selected' : '' }}>Size (Largest)</option>
                    <option value="size_asc" {{ request('sort') === 'size_asc' ? 'selected' : '' }}>Size (Smallest)</option>
                </select>

                @if(request('q') || request('folder') || request('sort'))
                    <a href="{{ route('admin.appearance.files.index') }}" class="btn btn-default" title="Reset Filters">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Media Grid --}}
    @if($files->count() > 0)
        <div class="media-grid">
            @foreach($files as $file)
                <div class="media-card" data-id="{{ $file->id }}" data-url="{{ $file->url }}" data-name="{{ $file->name }}">
                    {{-- Thumbnail Container --}}
                    <div class="media-card-thumb" onclick="openPreviewModal('{{ $file->url }}', '{{ addslashes($file->name) }}', '{{ $file->formatted_size }}', '{{ $file->width && $file->height ? ($file->width.'x'.$file->height) : '' }}', '{{ $file->folder }}');">
                        @if($file->is_image)
                            <img src="{{ $file->url }}" alt="{{ $file->name }}" loading="lazy">
                        @else
                            <div class="file-icon-placeholder">
                                <i class="fas fa-file-alt"></i>
                                <span>{{ strtoupper(pathinfo($file->filename, PATHINFO_EXTENSION)) }}</span>
                            </div>
                        @endif

                        {{-- Hover overlay preview badge --}}
                        <div class="thumb-overlay">
                            <i class="fas fa-search-plus"></i> View Preview
                        </div>

                        {{-- Top Badge: Folder & Dimensions --}}
                        <div class="thumb-badge-top">
                            <span class="badge-folder">{{ ucfirst($file->folder) }}</span>
                            @if($file->width && $file->height)
                                <span class="badge-dim">{{ $file->width }}&times;{{ $file->height }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Info --}}
                    <div class="media-card-body">
                        <div class="file-name" title="{{ $file->name }}">{{ Str::limit($file->name, 22) }}</div>
                        <div class="file-meta">
                            <span><i class="fas fa-weight-hanging"></i> {{ $file->formatted_size }}</span>
                            <span><i class="far fa-clock"></i> {{ $file->created_at ? $file->created_at->format('d M') : '' }}</span>
                        </div>
                    </div>

                    {{-- Quick Actions Toolbar --}}
                    <div class="media-card-actions">
                        {{-- Copy URL --}}
                        <button type="button" class="action-btn action-copy" onclick="copyToClipboard('{{ $file->url }}', this);" title="Copy Public URL for use anywhere">
                            <i class="fas fa-link"></i> <span class="btn-text">Copy URL</span>
                        </button>

                        {{-- Dropdown for Set As Logo, Favicon, Product --}}
                        <div class="dropdown" style="display:inline-block;">
                            <button type="button" class="action-btn action-menu dropdown-toggle" data-toggle="dropdown" title="Use as Logo / Favicon / Product">
                                <i class="fas fa-magic"></i> Use As <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a href="javascript:void(0)" onclick="setAsStoreBranding({{ $file->id }}, 'logo');">
                                        <i class="fas fa-crown text-warning"></i> Set as Store Logo
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="setAsStoreBranding({{ $file->id }}, 'favicon');">
                                        <i class="fas fa-star text-info"></i> Set as Store Favicon
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="javascript:void(0)" onclick="openProductAttachModal({{ $file->id }}, '{{ addslashes($file->name) }}', '{{ $file->url }}');">
                                        <i class="fas fa-tshirt text-primary"></i> Attach to Product...
                                    </a>
                                </li>
                            </ul>
                        </div>

                        {{-- Delete Button --}}
                        <form action="{{ route('admin.appearance.files.destroy', $file->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this file from storage?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn action-delete" title="Delete File">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="media-pagination">
            {{ $files->links() }}
        </div>
    @else
        <div class="media-empty-state">
            <i class="fas fa-images"></i>
            <h3>No Media Files Found</h3>
            <p>Upload new files above or click "Sync Disk" to detect existing images in storage.</p>
        </div>
    @endif

</div>

{{-- Modal 1: Image Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="previewModalTitle">File Preview</h4>
            </div>
            <div class="modal-body text-center" style="background:#f8fafc; padding:24px;">
                <div style="max-height:65vh; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <img id="previewModalImg" src="" alt="" style="max-width:100%; max-height:60vh; object-fit:contain; border-radius:6px; box-shadow:0 4px 16px rgba(0,0,0,0.1);">
                </div>
                <div class="preview-meta-details" style="margin-top:16px; display:flex; justify-content:center; gap:20px; font-size:13px; color:#64748b;">
                    <span id="previewModalDim"></span>
                    <span id="previewModalSize"></span>
                    <span id="previewModalFolder"></span>
                </div>
                <div class="input-group" style="margin-top:16px; max-width:600px; margin-left:auto; margin-right:auto;">
                    <input type="text" id="previewModalUrl" class="form-control" readonly>
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="button" onclick="copyToClipboard(document.getElementById('previewModalUrl').value, this);">
                            <i class="fas fa-copy"></i> Copy Link
                        </button>
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: Attach to Product Modal --}}
<div class="modal fade" id="productAttachModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fas fa-tshirt text-primary"></i> Attach Image to Product</h4>
            </div>
            <form id="productAttachForm" onsubmit="submitProductAttach(event);">
                <div class="modal-body">
                    <input type="hidden" id="attach_file_id" name="file_id">
                    <input type="hidden" name="action" value="product">

                    <div style="display:flex; align-items:center; gap:16px; background:#f1f5f9; padding:12px; border-radius:8px; margin-bottom:16px;">
                        <img id="attach_file_preview" src="" alt="" style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;">
                        <div>
                            <strong id="attach_file_name" style="display:block; font-size:14px; color:#0f172a;"></strong>
                            <small class="text-muted">This image will be linked to the selected product.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_select">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="product_select" class="form-control" required style="width:100%;">
                            <option value="">-- Choose a Product --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} (Code: {{ $prod->code ?: $prod->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_primary" value="1" checked> <strong>Make Primary Product Image</strong> (shown as main photo on shop &amp; home)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-do-attach">
                        <i class="fas fa-check"></i> Attach to Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast Notification container --}}
<div id="media-toast" style="display:none; position:fixed; bottom:24px; right:24px; background:#0f172a; color:#fff; padding:12px 20px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.2); font-size:14px; z-index:99999; display:flex; align-items:center; gap:10px; transform:translateY(100px); opacity:0; transition:all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
    <i class="fas fa-check-circle text-success" id="toast-icon"></i>
    <span id="toast-text">Message</span>
</div>

@push('styles')
<style>
/* ── Appearance Files & Media Styles ── */
.admin-content-wrap {
    padding: 10px 5px 40px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.media-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}
.media-title {
    margin: 0 0 4px;
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.media-subtitle {
    margin: 0;
    color: #64748b;
    font-size: 13.5px;
}
.media-header-actions {
    display: flex;
    gap: 10px;
}

/* Stat Cards Grid */
.media-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.media-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.media-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-icon-blue { background: #eff6ff; color: #3b82f6; }
.stat-icon-purple { background: #faf5ff; color: #a855f7; }
.stat-value {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.stat-label {
    display: block;
    font-size: 12.5px;
    color: #64748b;
    margin-top: 2px;
}

/* Brand Cards */
.media-brand-card {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.brand-preview-wrap {
    width: 48px;
    height: 48px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    flex-shrink: 0;
}
.brand-thumb-logo {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.brand-thumb-fav {
    width: 24px;
    height: 24px;
    object-fit: contain;
}
.brand-none-badge {
    font-size: 10px;
    color: #94a3b8;
    font-weight: 600;
}

/* Dropzone Panel */
.upload-zone-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.dropzone-box {
    border: 2px dashed #3b82f6;
    border-radius: 10px;
    background: #f8fafc;
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.dropzone-box:hover, .dropzone-box.dragover {
    background: #eff6ff;
    border-color: #2563eb;
    transform: scale(0.998);
}
.dropzone-icon {
    font-size: 40px;
    color: #3b82f6;
    margin-bottom: 10px;
}
.dropzone-text h4 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}
.dropzone-text p {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
}
.upload-options-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.folder-select-wrap {
    font-size: 13px;
    color: #334155;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Toolbar */
.media-toolbar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
}
.media-filter-form {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.search-input-wrap {
    position: relative;
    flex: 1;
    min-width: 240px;
    max-width: 400px;
}
.search-input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}
.search-input-wrap input {
    padding-left: 36px;
    border-radius: 8px;
    border-color: #cbd5e1;
}
.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.filter-group select {
    border-radius: 8px;
    border-color: #cbd5e1;
}

/* Media Cards Grid */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}
.media-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}
.media-card-thumb {
    position: relative;
    height: 160px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
}
.media-card-thumb img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.media-card:hover .media-card-thumb img {
    transform: scale(1.04);
}
.file-icon-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
}
.file-icon-placeholder i { font-size: 40px; }
.file-icon-placeholder span { font-size: 11px; font-weight: 700; }

.thumb-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.media-card-thumb:hover .thumb-overlay {
    opacity: 1;
}

.thumb-badge-top {
    position: absolute;
    top: 8px;
    left: 8px;
    right: 8px;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
}
.badge-folder {
    background: rgba(15, 23, 42, 0.75);
    color: #fff;
    font-size: 10.5px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
    backdrop-filter: blur(2px);
}
.badge-dim {
    background: rgba(15, 23, 42, 0.75);
    color: #cbd5e1;
    font-size: 10.5px;
    font-weight: 500;
    padding: 2px 7px;
    border-radius: 4px;
}

.media-card-body {
    padding: 12px 14px 8px;
    flex: 1;
}
.file-name {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.file-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    color: #64748b;
}

.media-card-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}
.action-btn {
    border: none;
    background: transparent;
    padding: 5px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.action-copy {
    color: #3b82f6;
}
.action-copy:hover {
    background: #eff6ff;
    color: #1d4ed8;
}
.action-menu {
    color: #475569;
}
.action-menu:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.action-delete {
    color: #ef4444;
}
.action-delete:hover {
    background: #fee2e2;
    color: #b91c1c;
}

/* Empty State */
.media-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    color: #64748b;
}
.media-empty-state i {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 12px;
}
.media-empty-state h3 {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
}
.media-empty-state p { margin: 0; font-size: 14px; }
</style>
@endpush

@push('scripts')
<script>
// File Dropzone Handling
const dropzone = document.getElementById('dropzone-area');
const filesInput = document.getElementById('files-input');
const summaryDiv = document.getElementById('selected-files-summary');
const submitBtn = document.getElementById('btn-submit-upload');

if (dropzone) {
    ['dragenter', 'dragover'].forEach(name => {
        dropzone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(name => {
        dropzone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            filesInput.files = e.dataTransfer.files;
            handleFileSelect(filesInput);
        }
    });
}

function handleFileSelect(input) {
    if (input.files && input.files.length > 0) {
        summaryDiv.textContent = `${input.files.length} file(s) selected: ` + Array.from(input.files).map(f => f.name).slice(0, 3).join(', ') + (input.files.length > 3 ? '...' : '');
        submitBtn.style.display = 'inline-block';
    } else {
        summaryDiv.textContent = '';
        submitBtn.style.display = 'none';
    }
}

// Copy URL to clipboard
function copyToClipboard(text, btnElement) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Link copied to clipboard!');
        if (btnElement) {
            const originalHtml = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fas fa-check text-success"></i> Copied!';
            setTimeout(() => { btnElement.innerHTML = originalHtml; }, 2000);
        }
    }).catch(err => {
        // Fallback for non-https
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        showToast('Link copied to clipboard!');
    });
}

// Set as Store Logo or Favicon
function setAsStoreBranding(fileId, action) {
    const actionLabel = action === 'logo' ? 'Store Logo' : 'Store Favicon';
    if (!confirm(`Set this image as active ${actionLabel}?`)) return;

    fetch("{{ route('admin.appearance.files.set-as') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ file_id: fileId, action: action })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            if (action === 'logo' && data.url) {
                const el = document.getElementById('active-logo-preview');
                if (el) el.src = data.url;
            } else if (action === 'favicon' && data.url) {
                const el = document.getElementById('active-fav-preview');
                if (el) el.src = data.url;
            }
        } else {
            alert(data.message || 'Action failed.');
        }
    })
    .catch(err => {
        alert('Request error. Please try again.');
    });
}

// Product Attach Modal
function openProductAttachModal(fileId, fileName, fileUrl) {
    document.getElementById('attach_file_id').value = fileId;
    document.getElementById('attach_file_name').textContent = fileName;
    document.getElementById('attach_file_preview').src = fileUrl;
    $('#productAttachModal').modal('show');
}

function submitProductAttach(event) {
    event.preventDefault();
    const form = document.getElementById('productAttachForm');
    const fileId = document.getElementById('attach_file_id').value;
    const productId = document.getElementById('product_select').value;
    const isPrimary = form.querySelector('input[name="is_primary"]').checked ? 1 : 0;

    if (!productId) {
        alert('Please choose a product.');
        return;
    }

    const btn = document.getElementById('btn-do-attach');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Attaching...';

    fetch("{{ route('admin.appearance.files.set-as') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            file_id: fileId,
            action: 'product',
            product_id: productId,
            is_primary: isPrimary
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Attach to Product';
        if (data.success) {
            $('#productAttachModal').modal('hide');
            showToast(data.message);
        } else {
            alert(data.message || 'Attach failed.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Attach to Product';
        alert('Request failed. Please try again.');
    });
}

// Preview Modal
function openPreviewModal(url, name, size, dim, folder) {
    document.getElementById('previewModalTitle').textContent = name;
    document.getElementById('previewModalImg').src = url;
    document.getElementById('previewModalUrl').value = url;
    document.getElementById('previewModalDim').textContent = dim ? `Dimensions: ${dim}` : '';
    document.getElementById('previewModalSize').textContent = `Size: ${size}`;
    document.getElementById('previewModalFolder').textContent = `Folder: ${folder}`;
    $('#previewModal').modal('show');
}

// Toast notification helper
function showToast(msg) {
    const toast = document.getElementById('media-toast');
    const toastText = document.getElementById('toast-text');
    if (!toast) return;
    toastText.textContent = msg;
    toast.style.display = 'flex';
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });
    setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 3500);
}
</script>
@endpush
@endsection
