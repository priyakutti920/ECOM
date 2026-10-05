@extends('layouts.admin')

@section('title', 'Files & Media Manager')

@section('content')
<div class="admin-content-wrap">

    {{-- Breadcrumb & Header --}}
    <div class="media-page-header">
        <div class="media-header-left">
            <h1 class="media-title"><i class="fas fa-photo-video text-primary"></i> Files &amp; Media Manager</h1>
            <p class="media-subtitle">Upload, manage, and assign media assets for Store Logo, Favicon, Products, and Banners.</p>
        </div>
        <div class="media-header-actions">
            <form action="{{ route('admin.appearance.files.sync') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-default" title="Scan disk and import untracked files">
                    <i class="fas fa-sync-alt"></i> Sync Disk
                </button>
            </form>
            <button type="button" class="btn btn-primary" onclick="toggleBulkUploadPanel()">
                <i class="fas fa-cloud-upload-alt"></i> Bulk Image Import
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
                    <img src="{{ $currentLogoUrl }}" alt="Active Logo" class="brand-thumb-logo" id="active-logo-preview" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
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
                    <img src="{{ $currentFaviconUrl }}" alt="Active Favicon" class="brand-thumb-fav" id="active-fav-preview" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
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

    {{-- Section 7: Bulk Image Import Dropzone Panel --}}
    <div class="bulk-upload-card" id="bulkUploadPanel" style="display:none;">
        <div class="bulk-card-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-cloud-upload-alt text-primary" style="font-size:18px;"></i>
                <h3 style="margin:0; font-size:15px; font-weight:700; color:#0f172a;">Bulk Image Import</h3>
            </div>
            <button type="button" class="close" onclick="toggleBulkUploadPanel()">&times;</button>
        </div>

        <div class="bulk-card-body">
            <div class="bulk-dropzone" id="bulkDropzone" onclick="document.getElementById('bulkFileInput').click()">
                <input type="file" id="bulkFileInput" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" style="display:none;" onchange="handleBulkFilesSelected(this.files)">
                <div class="dropzone-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h4 style="margin:0 0 6px; font-weight:700; font-size:16px; color:#0f172a;">Drag &amp; Drop Images Here</h4>
                <p style="margin:0 0 14px; font-size:13px; color:#64748b;">or click to browse from your device</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="event.stopPropagation(); document.getElementById('bulkFileInput').click();">
                    <i class="fas fa-folder-open"></i> Choose Images
                </button>
                <div class="dropzone-hint">
                    Supported: JPG &bull; PNG &bull; WebP &bull; GIF &bull; SVG (Max 20MB per file)
                </div>
            </div>

            {{-- Target Folder Selector --}}
            <div class="bulk-folder-select-row" style="margin-top:14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <label style="font-weight:600; font-size:12.5px; margin:0;"><i class="fas fa-folder"></i> Destination Folder:</label>
                <select id="bulkFolderSelect" class="form-control" style="width:180px; display:inline-block; height:34px;">
                    <option value="media">media (General)</option>
                    <option value="banner">banner (Banners)</option>
                    <option value="product">product (Products)</option>
                    <option value="appearance">appearance (Theme)</option>
                    <option value="settings">settings (Brand)</option>
                </select>
                <span class="text-muted" style="font-size:12px;">Images will be safely indexed and optimized.</span>
            </div>

            {{-- Staged Files Queue --}}
            <div id="bulkQueueWrap" style="display:none; margin-top:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h5 style="margin:0; font-weight:700; font-size:13px;">Selected Files Queue (<span id="bulkQueueCount">0</span>)</h5>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="btn btn-default btn-xs" id="clearBulkQueueBtn" onclick="clearBulkQueue()"><i class="fas fa-trash-alt"></i> Clear All</button>
                        <button type="button" class="btn btn-success btn-xs" id="startUploadBtn" onclick="startBulkUpload()"><i class="fas fa-upload"></i> Upload All</button>
                    </div>
                </div>

                {{-- Overall Progress Bar Card with Percentage Animation --}}
                <div class="bulk-progress-panel" id="bulkProgressBarWrap" style="display:none;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span id="bulkProgressSpinner" class="upload-pulse-icon">
                                <i class="fas fa-cloud-upload-alt text-primary" style="font-size:16px;"></i>
                            </span>
                            <strong id="bulkProgressTitle" style="font-size:13px; color:#0f172a;">Uploading files...</strong>
                            <span id="bulkProgressRatio" class="text-muted" style="font-size:12px;">(0 / 0)</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span id="bulkProgressPercent" style="font-size:15px; font-weight:800; color:#2563eb; min-width:48px; text-align:right;">0%</span>
                        </div>
                    </div>

                    <div class="progress" style="height:20px; margin-bottom:6px; border-radius:10px; background:#e2e8f0; overflow:hidden; box-shadow:inset 0 1px 3px rgba(0,0,0,0.08);">
                        <div class="progress-bar progress-bar-striped active bulk-animated-bar" id="bulkProgressBar" style="width: 0%; line-height:20px; font-size:11.5px; font-weight:700; background-color:#2563eb;">
                            <span id="bulkProgressBarLabel" style="display:inline-block;">0%</span>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:11.5px; color:#64748b;">
                        <span id="bulkProgressDetail">Processing upload queue...</span>
                        <span id="bulkProgressStats"></span>
                    </div>
                </div>

                {{-- File Item List --}}
                <div class="bulk-file-queue" id="bulkFileQueue"></div>
            </div>
        </div>
    </div>

    {{-- Filter, Search, and Status Tabs Bar --}}
    <div class="media-filter-bar">
        {{-- Tabs: Active vs Trashed --}}
        <div class="media-tabs">
            <a href="{{ route('admin.appearance.files.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
               class="media-tab {{ $status !== 'trashed' ? 'active' : '' }}">
                <i class="fas fa-images"></i> Active Files <span class="badge">{{ number_format($totalFiles) }}</span>
            </a>
            <a href="{{ route('admin.appearance.files.index', array_merge(request()->except('status', 'page'), ['status' => 'trashed'])) }}"
               class="media-tab {{ $status === 'trashed' ? 'active' : '' }}">
                <i class="fas fa-trash-alt"></i> Trash <span class="badge">{{ number_format($trashedCount) }}</span>
            </a>
        </div>

        <form action="{{ route('admin.appearance.files.index') }}" method="GET" class="media-filter-form">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="filter-group">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, filename..." class="form-control input-sm media-search-input">
            </div>

            <div class="filter-group">
                <select name="folder" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="all">All Folders</option>
                    @foreach($folders as $fName => $fCount)
                        <option value="{{ $fName }}" {{ request('folder') === $fName ? 'selected' : '' }}>
                            {{ ucfirst($fName) }} ({{ $fCount }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="sort" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort') === 'latest' || !request('sort') ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="size_desc" {{ request('sort') === 'size_desc' ? 'selected' : '' }}>Size (Largest)</option>
                    <option value="size_asc" {{ request('sort') === 'size_asc' ? 'selected' : '' }}>Size (Smallest)</option>
                </select>

                @if(request('q') || request('folder') || request('sort') || request('per_page'))
                    <a href="{{ route('admin.appearance.files.index', ['status' => $status]) }}" class="btn btn-default btn-sm" title="Reset Filters">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </div>

            <div class="filter-group">
                <select name="per_page" class="form-control input-sm" onchange="this.form.submit()" title="Images Per Page">
                    <option value="all" {{ ($perPageParam ?? 'all') === 'all' ? 'selected' : '' }}>All (Single Page)</option>
                    <option value="24" {{ ($perPageParam ?? '') == '24' ? 'selected' : '' }}>24 / page</option>
                    <option value="48" {{ ($perPageParam ?? '') == '48' ? 'selected' : '' }}>48 / page</option>
                    <option value="100" {{ ($perPageParam ?? '') == '100' ? 'selected' : '' }}>100 / page</option>
                </select>
            </div>

            <div class="btn-group btn-group-sm view-mode-btn-group" role="group" aria-label="View Mode">
                <button type="button" class="btn btn-default active" id="viewGridBtn" onclick="setMediaViewMode('grid')" title="Grid View">
                    <i class="fas fa-th-large"></i> <span class="hidden-xs">Grid</span>
                </button>
                <button type="button" class="btn btn-default" id="viewListBtn" onclick="setMediaViewMode('list')" title="List View">
                    <i class="fas fa-list"></i> <span class="hidden-xs">List</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Section 9: Universal Media Display (Grid & List View) --}}
    @if($files->count() > 0)
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; padding:10px 14px; background:#fff; border:1px solid #e2e8f0; border-radius:6px;">
            <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:13px; color:#334155; margin:0;">
                <input type="checkbox" id="bulkMasterCheck"> Select All (<span id="bulkMasterCheckCount">{{ $files->count() }}</span>)
            </label>
            <span class="text-muted" style="font-size:12px;">
                @if(($perPageParam ?? 'all') === 'all')
                    Showing all <strong>{{ $files->total() }}</strong> files on a single page
                @else
                    Showing {{ $files->firstItem() }}-{{ $files->lastItem() }} of {{ $files->total() }}
                @endif
            </span>
        </div>

        {{-- 1. Grid View --}}
        <div class="uni-media-grid" id="mainMediaGrid">
            @foreach($files as $file)
                <x-media-card :file="$file" type="media" />
            @endforeach
        </div>

        {{-- 2. List View --}}
        <div class="table-responsive media-list-table-wrap" id="mainMediaListTable" style="display:none;">
            <table class="table media-list-table">
                <thead>
                    <tr>
                        <th style="width:36px; text-align:center;">
                            <input type="checkbox" onchange="$('#bulkMasterCheck').prop('checked', this.checked).trigger('change');">
                        </th>
                        <th style="width:68px; text-align:center;">Thumb</th>
                        <th>File Name &amp; Key</th>
                        <th>Folder</th>
                        <th>Dimensions</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th style="width:160px; text-align:right; padding-right:16px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                        @php
                            $isLogo = ($currentLogoUrl && str_contains($currentLogoUrl, $file->filename));
                            $isFavicon = ($currentFaviconUrl && str_contains($currentFaviconUrl, $file->filename));
                            $ext = strtoupper(pathinfo($file->filename, PATHINFO_EXTENSION));
                        @endphp
                        <tr class="uni-media-row {{ $file->trashed() ? 'is-trashed' : '' }}" id="media-row-media-{{ $file->id }}" data-id="{{ $file->id }}">
                            <td style="text-align:center;">
                                <input type="checkbox" class="bulk-item-check" value="{{ $file->id }}" data-type="media">
                            </td>
                            <td style="text-align:center;">
                                <div class="list-thumb-wrap" onclick="openUniversalPreview('{{ $file->url }}', '{{ addslashes($file->name) }}', '{{ $file->formatted_size }}', '{{ $file->width }}×{{ $file->height }}', '{{ $file->folder }}');" title="Click to preview">
                                    <img src="{{ $file->url }}" alt="{{ $file->name }}" loading="lazy" decoding="async" class="list-thumb-img" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                    <div class="list-thumb-overlay"><i class="fas fa-search-plus"></i></div>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="list-file-name" onclick="openUniversalPreview('{{ $file->url }}', '{{ addslashes($file->name) }}', '{{ $file->formatted_size }}', '{{ $file->width }}×{{ $file->height }}', '{{ $file->folder }}');" title="{{ $file->name }}">
                                        {{ Str::limit($file->name, 40) }}
                                    </span>
                                    @if($ext)
                                        <span class="badge" style="background:#e2e8f0; color:#475569; font-size:10px; font-weight:700;">{{ $ext }}</span>
                                    @endif
                                    @if($isLogo)
                                        <span class="badge" style="background:#fef3c7; color:#b45309; font-size:10px;"><i class="fas fa-crown"></i> Logo</span>
                                    @endif
                                    @if($isFavicon)
                                        <span class="badge" style="background:#e0e7ff; color:#4338ca; font-size:10px;"><i class="fas fa-star"></i> Favicon</span>
                                    @endif
                                </div>
                                <div class="text-muted" style="font-size:11.5px; margin-top:2px; font-family:monospace; color:#64748b;">
                                    {{ $file->filename }}
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background:#f1f5f9; color:#334155; font-size:11px; font-weight:600; padding:4px 8px;">
                                    <i class="fas fa-folder text-warning"></i> {{ ucfirst($file->folder ?? 'media') }}
                                </span>
                            </td>
                            <td style="color:#475569; font-size:12px;">
                                @if($file->width && $file->height)
                                    <span>{{ $file->width }} × {{ $file->height }}</span>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td style="font-weight:600; color:#334155; font-size:12px;">
                                {{ $file->formatted_size ?: '—' }}
                            </td>
                            <td style="color:#64748b; font-size:12px;">
                                {{ $file->created_at ? $file->created_at->format('d M Y, h:i A') : '—' }}
                            </td>
                            <td style="text-align:right; padding-right:16px;">
                                @if(!$file->trashed())
                                    <div style="display:inline-flex; align-items:center; gap:4px;">
                                        <button type="button" class="btn btn-default btn-xs" onclick="copyMediaUrl('{{ $file->url }}', this);" title="Copy Public URL">
                                            <i class="fas fa-link text-primary"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-xs" onclick="openUniversalPreview('{{ $file->url }}', '{{ addslashes($file->name) }}', '{{ $file->formatted_size }}', '{{ $file->width }}×{{ $file->height }}', '{{ $file->folder }}');" title="Preview image">
                                            <i class="fas fa-eye text-info"></i>
                                        </button>
                                        <div class="dropdown" style="display:inline-block;">
                                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" title="Use as / Attach">
                                                <i class="fas fa-magic text-warning"></i> <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right uni-dropdown-menu">
                                                <li class="dropdown-header">Brand Identity</li>
                                                <li>
                                                    <a href="javascript:void(0)" onclick="executeUseAs({{ $file->id }}, 'logo', '{{ addslashes($file->name) }}');">
                                                        <i class="fas fa-crown text-warning"></i> Set as Store Logo
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)" onclick="executeUseAs({{ $file->id }}, 'favicon', '{{ addslashes($file->name) }}');">
                                                        <i class="fas fa-star text-info"></i> Set as Store Favicon
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                <li class="dropdown-header">Marketing & Catalog</li>
                                                <li>
                                                    <a href="javascript:void(0)" onclick="executeUseAs({{ $file->id }}, 'banner', '{{ addslashes($file->name) }}');">
                                                        <i class="fas fa-image text-success"></i> Create Homepage Banner
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)" onclick="openCategoryAttachModal({{ $file->id }}, '{{ addslashes($file->name) }}', '{{ $file->url }}');">
                                                        <i class="fas fa-folder text-warning"></i> Assign to Category...
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)" onclick="openProductAttachModal({{ $file->id }}, '{{ addslashes($file->name) }}', '{{ $file->url }}');">
                                                        <i class="fas fa-tshirt text-primary"></i> Assign to Product...
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-default btn-xs" onclick="openReplaceModal({{ $file->id }}, '{{ addslashes($file->name) }}', '{{ $file->url }}', 'media');" title="Replace File">
                                            <i class="fas fa-exchange-alt text-muted"></i>
                                        </button>
                                        <button type="button" class="btn btn-default btn-xs text-danger" onclick="deleteMediaItem({{ $file->id }}, 'media', '{{ addslashes($file->name) }}');" title="Move to Trash">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                @else
                                    <div style="display:inline-flex; align-items:center; gap:4px;">
                                        <button type="button" class="btn btn-success btn-xs" onclick="restoreMediaItem({{ $file->id }}, 'media', '{{ addslashes($file->name) }}');" title="Restore file">
                                            <i class="fas fa-trash-restore"></i> Restore
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" onclick="forceDeleteMediaItem({{ $file->id }}, 'media', '{{ addslashes($file->name) }}');" title="Permanently Delete">
                                            <i class="fas fa-ban"></i> Delete
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination (only shown if not 'all') --}}
        @if(($perPageParam ?? 'all') !== 'all')
            <div style="margin-top:24px; text-align:center;">
                {{ $files->links() }}
            </div>
        @endif
    @else
        <div class="media-empty-state">
            <i class="fas fa-photo-video" style="font-size:48px; color:#cbd5e1; margin-bottom:12px;"></i>
            <h4 style="margin:0 0 6px; font-weight:700; color:#0f172a;">No files found</h4>
            <p style="margin:0 0 16px; color:#64748b; font-size:13px;">
                {{ $status === 'trashed' ? 'Trash is currently empty.' : 'No media files matched your query. Import images using the bulk uploader above.' }}
            </p>
            @if($status !== 'trashed')
                <button type="button" class="btn btn-primary btn-sm" onclick="toggleBulkUploadPanel()">
                    <i class="fas fa-cloud-upload-alt"></i> Import Images Now
                </button>
            @endif
        </div>
    @endif

</div>

{{-- ── CATEGORY ATTACH MODAL ── --}}
<div class="modal fade" id="categoryAttachModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="font-size:14px; font-weight:700;"><i class="fas fa-folder text-warning"></i> Assign Image to Category</h4>
            </div>
            <form id="categoryAttachForm" onsubmit="submitCategoryAttach(event);">
                <div class="modal-body" style="padding:18px;">
                    <input type="hidden" id="cat_attach_file_id">
                    <div style="display:flex; align-items:center; gap:14px; background:#f8fafc; padding:12px; border-radius:6px; margin-bottom:16px; border:1px solid #e2e8f0;">
                        <img id="cat_attach_preview" src="" alt="preview" style="width:54px; height:54px; object-fit:cover; border-radius:6px;">
                        <div>
                            <strong id="cat_attach_name" style="font-size:13px; color:#0f172a; display:block;"></strong>
                            <small class="text-muted">This image will appear as the category thumbnail across storefront carousels and lists.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category_select" style="font-weight:600; font-size:12px;">Select Category <span class="text-danger">*</span></label>
                        <select id="category_select" class="form-control" required>
                            <option value="">-- Choose Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-do-cat-attach"><i class="fas fa-check"></i> Assign to Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── PRODUCT ATTACH MODAL ── --}}
<div class="modal fade" id="productAttachModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="font-size:14px; font-weight:700;"><i class="fas fa-tshirt text-primary"></i> Assign Image to Product</h4>
            </div>
            <form id="productAttachForm" onsubmit="submitProductAttach(event);">
                <div class="modal-body" style="padding:18px;">
                    <input type="hidden" id="prod_attach_file_id">
                    <div style="display:flex; align-items:center; gap:14px; background:#f8fafc; padding:12px; border-radius:6px; margin-bottom:16px; border:1px solid #e2e8f0;">
                        <img id="prod_attach_preview" src="" alt="preview" style="width:54px; height:54px; object-fit:cover; border-radius:6px;">
                        <div>
                            <strong id="prod_attach_name" style="font-size:13px; color:#0f172a; display:block;"></strong>
                            <small class="text-muted">Link this image as primary photo or add to product gallery.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_select" style="font-weight:600; font-size:12px;">Select Product <span class="text-danger">*</span></label>
                        <select id="product_select" class="form-control" required>
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} (Code: {{ $prod->code ?: $prod->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="checkbox" style="margin:0;">
                        <label style="font-size:12.5px;">
                            <input type="checkbox" id="prod_is_primary" value="1" checked> <strong>Set as Primary Product Image</strong> (shown on catalog &amp; home)
                        </label>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-do-prod-attach"><i class="fas fa-check"></i> Assign to Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Page Layout */
.media-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--admin-card-border);
}
.media-title {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.media-subtitle {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}
.media-header-actions {
    display: flex;
    gap: 8px;
}

/* Stat Cards */
.media-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 20px;
}
.media-stat-card {
    background: #ffffff;
    border: 1px solid var(--admin-card-border);
    border-radius: 8px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-icon-blue { background: #e0f2fe; color: #0284c7; }
.stat-icon-purple { background: #f3e8ff; color: #9333ea; }
.stat-info .stat-value {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    display: block;
}
.stat-info .stat-label {
    font-size: 12px;
    color: #64748b;
}

.media-brand-card {
    background: #fafbfc;
}
.brand-preview-wrap {
    width: 50px;
    height: 44px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.brand-thumb-logo, .brand-thumb-fav {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.brand-none-badge {
    font-size: 9px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
}

/* Bulk Upload Panel */
.bulk-upload-card {
    background: #ffffff;
    border: 1px solid var(--admin-card-border);
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    margin-bottom: 22px;
    overflow: hidden;
    animation: fadeIn 0.2s ease;
}
.bulk-card-header {
    padding: 12px 18px;
    background: #f8fafc;
    border-bottom: 1px solid var(--admin-card-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.bulk-card-body {
    padding: 20px;
}
.bulk-dropzone {
    border: 2px dashed #93c5fd;
    background: #f0f7ff;
    border-radius: 8px;
    padding: 34px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.bulk-dropzone:hover, .bulk-dropzone.dragover {
    background: #e0f2fe;
    border-color: #3b82f6;
    transform: scale(1.005);
}
.dropzone-icon {
    font-size: 38px;
    color: #3b82f6;
    margin-bottom: 8px;
}
.dropzone-hint {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 10px;
}

/* Bulk File Queue */
.bulk-file-queue {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 320px;
    overflow-y: auto;
}
.bulk-queue-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 12px;
}
.bulk-queue-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.bulk-queue-thumb {
    width: 36px;
    height: 36px;
    border-radius: 4px;
    object-fit: cover;
    background: #e2e8f0;
    flex-shrink: 0;
}
.bulk-queue-title {
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 280px;
}

/* Bulk Progress & Percentage Animation */
@keyframes progressStripesMove {
    0% { background-position: 40px 0; }
    100% { background-position: 0 0; }
}
@keyframes pulseUploadGlow {
    0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
    100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
}
.bulk-progress-panel {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 16px;
    animation: fadeIn 0.25s ease;
}
.bulk-animated-bar {
    background-image: linear-gradient(
        45deg,
        rgba(255, 255, 255, 0.25) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.25) 50%,
        rgba(255, 255, 255, 0.25) 75%,
        transparent 75%,
        transparent
    ) !important;
    background-size: 32px 32px !important;
    animation: progressStripesMove 0.8s linear infinite !important;
    transition: width 0.25s ease-out !important;
}
.bulk-queue-status-badge {
    padding: 4px 9px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
}
.status-badge-ready {
    background: #e2e8f0;
    color: #475569;
}
.status-badge-uploading {
    background: #dbeafe;
    color: #1d4ed8;
    animation: pulseUploadGlow 1.5s infinite;
}
.status-badge-done {
    background: #dcfce7;
    color: #15803d;
}
.status-badge-error {
    background: #fee2e2;
    color: #b91c1c;
}

/* List View Styles */
.media-list-table-wrap {
    margin-top: 14px;
    background: #ffffff;
    border: 1px solid var(--admin-card-border);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    overflow: hidden;
}
.media-list-table {
    margin-bottom: 0;
    width: 100%;
}
.media-list-table thead th {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 14px;
    vertical-align: middle;
}
.media-list-table tbody tr {
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #f1f5f9;
}
.media-list-table tbody tr:hover {
    background-color: #f8fafc;
}
.media-list-table tbody tr.active-selected-row {
    background-color: #eff6ff !important;
}
.media-list-table tbody tr.is-trashed {
    opacity: 0.65;
    background-color: #fff1f2;
}
.media-list-table tbody td {
    padding: 10px 14px;
    vertical-align: middle;
}
.list-thumb-wrap {
    width: 46px;
    height: 46px;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.list-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.2s ease;
}
.list-thumb-wrap:hover .list-thumb-img {
    transform: scale(1.1);
}
.list-thumb-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 13px;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.list-thumb-wrap:hover .list-thumb-overlay {
    opacity: 1;
}
.list-file-name {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    transition: color 0.15s;
    cursor: pointer;
}
.list-file-name:hover {
    color: #2563eb;
    text-decoration: underline;
}
.view-mode-btn-group .btn.active {
    background-color: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

/* Filter Bar & Tabs */
.media-filter-bar {
    background: #ffffff;
    border: 1px solid var(--admin-card-border);
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.media-tabs {
    display: flex;
    gap: 6px;
}
.media-tab {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 500;
    color: #475569;
    text-decoration: none !important;
    background: #f1f5f9;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
}
.media-tab:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.media-tab.active {
    background: #0f172a;
    color: #ffffff;
}
.media-tab .badge {
    background: rgba(255, 255, 255, 0.2);
    color: inherit;
    font-size: 10.5px;
}
.media-tab.active .badge {
    background: #334155;
    color: #ffffff;
}

.media-filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.media-search-input {
    width: 220px;
}

.media-empty-state {
    text-align: center;
    padding: 50px 20px;
    background: #ffffff;
    border: 1px dashed var(--admin-card-border);
    border-radius: 8px;
    margin-top: 16px;
}
</style>
@endpush

@push('scripts')
<script>
var stagedFiles = [];
var isBulkUploading = false;

function toggleBulkUploadPanel() {
    var p = document.getElementById('bulkUploadPanel');
    p.style.display = (p.style.display === 'none' || p.style.display === '') ? 'block' : 'none';
    if (p.style.display === 'block') {
        p.scrollIntoView({ behavior: 'smooth' });
    }
}

// Drag & drop handlers
var dz = document.getElementById('bulkDropzone');
['dragenter', 'dragover'].forEach(function (evt) {
    dz.addEventListener(evt, function (e) {
        e.preventDefault();
        e.stopPropagation();
        dz.classList.add('dragover');
    }, false);
});
['dragleave', 'drop'].forEach(function (evt) {
    dz.addEventListener(evt, function (e) {
        e.preventDefault();
        e.stopPropagation();
        dz.classList.remove('dragover');
    }, false);
});
dz.addEventListener('drop', function (e) {
    var dt = e.dataTransfer;
    if (dt && dt.files && dt.files.length) {
        handleBulkFilesSelected(dt.files);
    }
});

var fileSeq = 0;
function handleBulkFilesSelected(fileList) {
    if (!fileList || !fileList.length) return;
    for (var i = 0; i < fileList.length; i++) {
        var f = fileList[i];
        fileSeq++;
        stagedFiles.push({
            id: 'f_' + Date.now() + '_' + fileSeq,
            file: f,
            name: f.name,
            size: f.size,
            type: f.type || 'image/jpeg',
            status: 'ready',
            errorMsg: ''
        });
    }
    var inputEl = document.getElementById('bulkFileInput');
    if (inputEl) inputEl.value = '';
    renderBulkQueue();
}

function renderBulkQueue() {
    var qWrap = document.getElementById('bulkQueueWrap');
    var qList = document.getElementById('bulkFileQueue');
    var countEl = document.getElementById('bulkQueueCount');

    if (!stagedFiles.length) {
        qWrap.style.display = 'none';
        return;
    }

    qWrap.style.display = 'block';
    countEl.innerText = stagedFiles.length;
    qList.innerHTML = '';

    stagedFiles.forEach(function (item, idx) {
        var el = document.createElement('div');
        el.className = 'bulk-queue-item';
        el.id = 'queue-item-' + item.id;

        var sizeKb = (item.size / 1024).toFixed(0) + ' KB';
        var objectUrl = '';
        try {
            objectUrl = URL.createObjectURL(item.file);
        } catch (e) {}

        var badgeHtml = '';
        if (item.status === 'uploading') {
            badgeHtml = '<span class="bulk-queue-status-badge status-badge-uploading" id="queue-status-' + item.id + '"><i class="fas fa-spinner fa-spin"></i> Uploading</span>';
        } else if (item.status === 'done') {
            badgeHtml = '<span class="bulk-queue-status-badge status-badge-done" id="queue-status-' + item.id + '"><i class="fas fa-check-circle"></i> Uploaded</span>';
        } else if (item.status === 'error') {
            badgeHtml = '<span class="bulk-queue-status-badge status-badge-error" id="queue-status-' + item.id + '" title="' + (item.errorMsg || 'Upload failed') + '"><i class="fas fa-exclamation-triangle"></i> Failed</span>';
        } else {
            badgeHtml = '<span class="bulk-queue-status-badge status-badge-ready" id="queue-status-' + item.id + '">Ready</span>';
        }

        var removeBtn = isBulkUploading
            ? ''
            : '<button type="button" class="btn btn-default btn-xs text-danger" onclick="removeFromQueue(' + idx + ')"><i class="fas fa-times"></i></button>';

        el.innerHTML =
            '<div class="bulk-queue-left">' +
                '<img src="' + (objectUrl || '{{ asset("assets/images/placeholder.svg") }}') + '" class="bulk-queue-thumb" alt="thumb" onerror="this.onerror=null; this.src=\'{{ asset("assets/images/placeholder.svg") }}\';">' +
                '<div>' +
                    '<div class="bulk-queue-title" title="' + item.name + '">' + item.name + '</div>' +
                    '<span class="text-muted" style="font-size:11px;">' + sizeKb + ' &bull; ' + item.type + '</span>' +
                '</div>' +
            '</div>' +
            '<div style="display:flex; align-items:center; gap:8px;">' +
                badgeHtml +
                removeBtn +
            '</div>';

        qList.appendChild(el);
    });
}

function removeFromQueue(idx) {
    if (isBulkUploading) return;
    stagedFiles.splice(idx, 1);
    renderBulkQueue();
}

function clearBulkQueue() {
    if (isBulkUploading) return;
    stagedFiles = [];
    var pWrap = document.getElementById('bulkProgressBarWrap');
    if (pWrap) pWrap.style.display = 'none';
    renderBulkQueue();
}

function updateProgressDisplay(percent, completed, total, detailText) {
    var pBar = document.getElementById('bulkProgressBar');
    var pLabel = document.getElementById('bulkProgressBarLabel');
    var pPercent = document.getElementById('bulkProgressPercent');
    var pRatio = document.getElementById('bulkProgressRatio');
    var pDetail = document.getElementById('bulkProgressDetail');

    percent = Math.max(0, Math.min(100, Math.round(percent)));

    if (pBar) {
        pBar.style.width = percent + '%';
        if (percent >= 100) {
            pBar.style.backgroundColor = '#10b981';
        } else {
            pBar.style.backgroundColor = '#2563eb';
        }
    }
    if (pLabel) pLabel.innerText = percent + '%';
    if (pPercent) pPercent.innerText = percent + '%';
    if (pRatio) pRatio.innerText = '(' + completed + ' / ' + total + ' completed)';
    if (pDetail && detailText) pDetail.innerText = detailText;
}

function startBulkUpload() {
    if (isBulkUploading) return;
    if (!stagedFiles.length) return;

    var pendingItems = stagedFiles.filter(function (item) {
        return item.status !== 'done';
    });

    if (!pendingItems.length) {
        adminToast('All staged files have already been uploaded.', 'info');
        return;
    }

    isBulkUploading = true;

    var btn = document.getElementById('startUploadBtn');
    var clearBtn = document.getElementById('clearBulkQueueBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading…';
    }
    if (clearBtn) clearBtn.disabled = true;

    var pWrap = document.getElementById('bulkProgressBarWrap');
    if (pWrap) pWrap.style.display = 'block';

    var totalFiles = pendingItems.length;
    var completedFiles = 0;
    var failedFiles = 0;

    // Calculate total bytes across all pending files
    var totalBytes = pendingItems.reduce(function (acc, item) {
        return acc + (item.size || 0);
    }, 0);
    var uploadedBytesCompletedBatches = 0;

    updateProgressDisplay(0, 0, totalFiles, 'Starting upload queue for ' + totalFiles + ' file(s)...');

    // Group into safe batches: Max 2 files per batch OR 1 file if > 3.5MB, batch max 8MB
    var batches = [];
    var currentBatch = [];
    var currentBatchSize = 0;

    pendingItems.forEach(function (item) {
        var itemSize = item.size || 0;
        if (currentBatch.length >= 2 || (currentBatchSize + itemSize > 8388608 && currentBatch.length > 0)) {
            batches.push(currentBatch);
            currentBatch = [];
            currentBatchSize = 0;
        }
        currentBatch.push(item);
        currentBatchSize += itemSize;
    });
    if (currentBatch.length > 0) {
        batches.push(currentBatch);
    }

    var folder = document.getElementById('bulkFolderSelect').value || 'media';
    var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
    var batchIndex = 0;

    function processNextBatch() {
        if (batchIndex >= batches.length) {
            // All batches processed!
            isBulkUploading = false;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Upload Complete';
            }
            if (clearBtn) clearBtn.disabled = false;

            updateProgressDisplay(100, completedFiles, totalFiles, 'Completed! ' + completedFiles + ' uploaded' + (failedFiles > 0 ? ', ' + failedFiles + ' failed' : '') + '.');

            var msg = completedFiles + ' of ' + totalFiles + ' files imported successfully!';
            if (failedFiles > 0) {
                msg += ' (' + failedFiles + ' failed)';
                adminToast(msg, 'warning');
            } else {
                adminToast(msg, 'success');
            }

            if (completedFiles > 0) {
                setTimeout(function () {
                    window.location.href = '{{ route("admin.appearance.files.index", ["status" => "active", "sort" => "latest"]) }}';
                }, 1200);
            }
            return;
        }

        var batch = batches[batchIndex];
        var batchBytesTotal = batch.reduce(function (acc, item) {
            return acc + (item.size || 0);
        }, 0);

        // Mark current batch items as uploading in UI
        batch.forEach(function (item) {
            item.status = 'uploading';
            var badge = document.getElementById('queue-status-' + item.id);
            if (badge) {
                badge.className = 'bulk-queue-status-badge status-badge-uploading';
                badge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading';
            }
            var row = document.getElementById('queue-item-' + item.id);
            if (row) {
                row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        var fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('folder', folder);
        batch.forEach(function (item) {
            fd.append('files[]', item.file);
        });

        $.ajax({
            url: '{{ route("admin.appearance.files.bulk-upload") }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable && totalBytes > 0) {
                        var currentBatchLoaded = Math.min(e.loaded, batchBytesTotal);
                        var totalLoadedNow = uploadedBytesCompletedBatches + currentBatchLoaded;
                        var pct = Math.min(99, Math.round((totalLoadedNow / totalBytes) * 100));
                        updateProgressDisplay(pct, completedFiles, totalFiles, 'Uploading: batch ' + (batchIndex + 1) + ' of ' + batches.length + ' (' + pct + '%)');
                    }
                }, false);
                return xhr;
            },
            success: function (res) {
                uploadedBytesCompletedBatches += batchBytesTotal;
                batch.forEach(function (item) {
                    item.status = 'done';
                    completedFiles++;
                    var badge = document.getElementById('queue-status-' + item.id);
                    if (badge) {
                        badge.className = 'bulk-queue-status-badge status-badge-done';
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> Uploaded';
                    }
                });

                var pct = Math.round((completedFiles / totalFiles) * 100);
                updateProgressDisplay(pct, completedFiles, totalFiles, 'Uploaded ' + completedFiles + ' of ' + totalFiles + ' files...');

                batchIndex++;
                processNextBatch();
            },
            error: function (xhr) {
                uploadedBytesCompletedBatches += batchBytesTotal;
                var res = xhr.responseJSON || {};
                var errDetail = res.message || 'Upload failed.';

                batch.forEach(function (item) {
                    item.status = 'error';
                    item.errorMsg = errDetail;
                    failedFiles++;
                    var badge = document.getElementById('queue-status-' + item.id);
                    if (badge) {
                        badge.className = 'bulk-queue-status-badge status-badge-error';
                        badge.title = errDetail;
                        badge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed';
                    }
                });

                batchIndex++;
                processNextBatch();
            }
        });
    }

    processNextBatch();
}

// Category Attach Modal
function openCategoryAttachModal(fileId, name, url) {
    $('#cat_attach_file_id').val(fileId);
    $('#cat_attach_name').text(name);
    $('#cat_attach_preview').attr('src', url);
    $('#categoryAttachModal').modal('show');
}

function submitCategoryAttach(e) {
    e.preventDefault();
    var fileId = $('#cat_attach_file_id').val();
    var catId = $('#category_select').val();
    if (!catId) {
        adminToast('Please select a category.', 'error');
        return;
    }

    var btn = $('#btn-do-cat-attach');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning…');

    $.ajax({
        url: '{{ route("admin.appearance.files.set-as") }}',
        method: 'POST',
        data: { file_id: fileId, action: 'category', category_id: catId },
        success: function (res) {
            adminToast(res.message || 'Category image updated!');
            $('#categoryAttachModal').modal('hide');
        },
        error: function (xhr) {
            adminToast('Failed to assign image to category.', 'error');
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fas fa-check"></i> Assign to Category');
        }
    });
}

// Product Attach Modal
function openProductAttachModal(fileId, name, url) {
    $('#prod_attach_file_id').val(fileId);
    $('#prod_attach_name').text(name);
    $('#prod_attach_preview').attr('src', url);
    $('#productAttachModal').modal('show');
}

function submitProductAttach(e) {
    e.preventDefault();
    var fileId = $('#prod_attach_file_id').val();
    var prodId = $('#product_select').val();
    var isPrimary = $('#prod_is_primary').is(':checked') ? 1 : 0;

    if (!prodId) {
        adminToast('Please select a product.', 'error');
        return;
    }

    var btn = $('#btn-do-prod-attach');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning…');

    $.ajax({
        url: '{{ route("admin.appearance.files.set-as") }}',
        method: 'POST',
        data: { file_id: fileId, action: 'product', product_id: prodId, is_primary: isPrimary },
        success: function (res) {
            adminToast(res.message || 'Product image assigned!');
            $('#productAttachModal').modal('hide');
        },
        error: function (xhr) {
            adminToast('Failed to assign image to product.', 'error');
        },
        complete: function () {
            btn.prop('disabled', false).html('<i class="fas fa-check"></i> Assign to Product');
        }
    });
}

// Register Bulk Actions in Universal Bar
$(function () {
    var isTrash = '{{ $status }}' === 'trashed';
    var html = '';
    if (!isTrash) {
        html += '<button type="button" class="bulk-action-btn" onclick="openBulkMoveFolderModal()"><i class="fas fa-folder"></i> Move to Folder</button>';
        html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeFilesBulkAction(\'delete\', \'Move {count} selected file(s) to Trash?\')"><i class="fas fa-trash-alt"></i> Move to Trash</button>';
    } else {
        html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeFilesBulkAction(\'restore\')"><i class="fas fa-undo"></i> Restore</button>';
        html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeFilesBulkAction(\'force_delete\', \'PERMANENT DELETION: Are you sure you want to permanently delete {count} selected file(s)? This cannot be undone!\')"><i class="fas fa-trash"></i> Permanently Delete</button>';
    }
    $('#bulkBarActions').html(html);
});

function openBulkMoveFolderModal() {
    var ids = getSelectedBulkIds();
    if (!ids.length) {
        adminToast('Please select at least one file.', 'error');
        return;
    }
    $('#bulkMoveFolderModal').modal('show');
}

function submitBulkMoveFolder() {
    var folder = $('#bulkMoveFolderSelect').val();
    runBulkAction('{{ route("admin.appearance.files.bulk-action") }}', 'move_folder', { folder: folder });
    $('#bulkMoveFolderModal').modal('hide');
}

function executeFilesBulkAction(action, confirmMsg) {
    var extra = {};
    if (action === 'force_delete') {
        extra.force = 1;
    }
    runBulkAction('{{ route("admin.appearance.files.bulk-action") }}', action, extra, confirmMsg);
}

/* View Mode Management (Grid vs List) */
function setMediaViewMode(mode) {
    var gridEl = document.getElementById('mainMediaGrid');
    var listEl = document.getElementById('mainMediaListTable');
    var gridBtn = document.getElementById('viewGridBtn');
    var listBtn = document.getElementById('viewListBtn');

    if (mode === 'list') {
        if (gridEl) gridEl.style.display = 'none';
        if (listEl) listEl.style.display = 'block';
        if (gridBtn) gridBtn.classList.remove('active');
        if (listBtn) listBtn.classList.add('active');
        try { localStorage.setItem('nool_media_view_mode', 'list'); } catch (e) {}
    } else {
        if (gridEl) gridEl.style.display = 'grid';
        if (listEl) listEl.style.display = 'none';
        if (gridBtn) gridBtn.classList.add('active');
        if (listBtn) listBtn.classList.remove('active');
        try { localStorage.setItem('nool_media_view_mode', 'grid'); } catch (e) {}
    }
}

$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var viewParam = urlParams.get('view');
    var savedView = 'grid';
    try {
        savedView = viewParam || localStorage.getItem('nool_media_view_mode') || 'grid';
    } catch (e) {
        savedView = viewParam || 'grid';
    }
    setMediaViewMode(savedView);
});
</script>
@endpush

{{-- ── BULK MOVE FOLDER MODAL ── --}}
<div class="modal fade" id="bulkMoveFolderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="font-size:14px; font-weight:700;"><i class="fas fa-folder text-primary"></i> Move to Folder</h4>
            </div>
            <div class="modal-body" style="padding:16px;">
                <label style="font-weight:600; font-size:12.5px; margin-bottom:6px; display:block;">Target Folder:</label>
                <select id="bulkMoveFolderSelect" class="form-control input-sm">
                    <option value="media">media (General)</option>
                    <option value="banner">banner (Banners)</option>
                    <option value="product">product (Products)</option>
                    <option value="appearance">appearance (Theme)</option>
                    <option value="settings">settings (Brand)</option>
                </select>
            </div>
            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="submitBulkMoveFolder()"><i class="fas fa-check"></i> Move</button>
            </div>
        </div>
    </div>
</div>
@endsection
