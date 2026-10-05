@props([
    'file' => null,
    'banner' => null,
    'type' => 'media',
    'id' => null,
    'name' => null,
    'url' => null,
    'path' => null,
    'size' => null,
    'date' => null,
    'width' => null,
    'height' => null,
    'folder' => 'general',
    'isActive' => null,
    'isTrashed' => false,
    'showCopy' => true,
    'showUseAs' => true,
    'showReplace' => true,
    'showDelete' => true,
    'showPreview' => true,
    'showToggle' => true,
    'showEdit' => true,
    'extraClasses' => '',
])

@php
    // Normalize data from $file model if passed
    if ($file) {
        $id = $id ?? $file->id;
        $name = $name ?? $file->name;
        $url = $url ?? $file->url;
        $path = $path ?? $file->path;
        $size = $size ?? ($file->formatted_size ?? '');
        $date = $date ?? ($file->created_at ? $file->created_at->format('d M') : '');
        $width = $width ?? $file->width;
        $height = $height ?? $file->height;
        $folder = $folder ?? ($file->folder ?? 'general');
        $isTrashed = $isTrashed || ($file && method_exists($file, 'trashed') && $file->trashed());
        $isActive = $isActive ?? ($file->is_active ?? null);
    }

    // Normalize from $banner model if passed
    if ($banner) {
        $id = $id ?? $banner->id;
        $name = $name ?? ($banner->primary_text ?: 'Banner #' . $banner->id);
        $url = $url ?? $banner->image_url;
        $path = $path ?? $banner->image;
        $date = $date ?? ($banner->created_at ? $banner->created_at->format('d M') : '');
        $folder = 'banner';
        $isActive = $banner->is_active;
        $isTrashed = $banner && method_exists($banner, 'trashed') && $banner->trashed();
        $type = 'banner';
    }

    $fallbackSvg = asset('assets/images/placeholder.svg');
    $dimensionsStr = ($width && $height) ? "{$width}×{$height}" : null;
@endphp

<div class="uni-media-card {{ $isTrashed ? 'is-trashed' : '' }} {{ $extraClasses }}"
     id="media-card-{{ $type }}-{{ $id }}"
     data-id="{{ $id }}"
     data-url="{{ $url }}"
     data-name="{{ $name }}"
     data-path="{{ $path }}"
     data-type="{{ $type }}"
     @if($banner)
     data-primary-text="{{ $banner->primary_text }}"
     data-tagline="{{ $banner->tagline }}"
     data-show-button="{{ $banner->show_button ? '1' : '0' }}"
     data-button-name="{{ $banner->button_name }}"
     data-button-link="{{ $banner->button_link }}"
     data-sort-order="{{ $banner->sort_order }}"
     data-is-active="{{ $banner->is_active ? '1' : '0' }}"
     data-image-url="{{ $banner->image_url }}"
     @endif
     >

    {{-- Universal Checkbox for Bulk Actions --}}
    <label class="uni-card-select" onclick="event.stopPropagation();" title="Select for bulk actions">
        <input type="checkbox" class="bulk-item-check" value="{{ $id }}" data-type="{{ $type }}">
    </label>

    {{-- 1. Thumbnail Header with Badges & Hover Overlay --}}
    <div class="uni-card-thumb" onclick="if(typeof openUniversalPreview === 'function'){ openUniversalPreview('{{ $url }}', '{{ addslashes($name) }}', '{{ $size }}', '{{ $dimensionsStr }}', '{{ $folder }}'); }">
        <img src="{{ $url }}"
             alt="{{ $name }}"
             loading="lazy"
             class="uni-card-img"
             onerror="this.onerror=null; this.src='{{ $fallbackSvg }}'; this.classList.add('is-broken-img');" />

        {{-- Top Badges: Category/Folder & Dimensions / Status --}}
        <div class="uni-thumb-badges">
            <span class="uni-badge-pill uni-badge-folder"><i class="fas fa-folder"></i> {{ ucfirst($folder) }}</span>
            @if($dimensionsStr)
                <span class="uni-badge-pill uni-badge-dim">{{ $dimensionsStr }}</span>
            @endif
            @if($isActive !== null)
                <span class="uni-badge-pill {{ $isActive ? 'uni-badge-active' : 'uni-badge-inactive' }} uni-status-badge-{{ $id }}">
                    <i class="fas {{ $isActive ? 'fa-check-circle' : 'fa-times-circle' }}"></i> {{ $isActive ? 'Active' : 'Inactive' }}
                </span>
            @endif
            @if($banner && $banner->sort_order !== null)
                <span class="uni-badge-pill" style="background:rgba(0,0,0,0.55); color:#fff;" title="Sort Order">
                    <i class="fas fa-sort-numeric-down"></i> {{ $banner->sort_order }}
                </span>
            @endif
        </div>

        {{-- Hover Overlay with Zoom Icon --}}
        <div class="uni-thumb-overlay">
            <i class="fas fa-search-plus"></i> <span>Preview</span>
        </div>
    </div>

    {{-- 2. Card Body: Title, File Size, Upload Date --}}
    <div class="uni-card-body">
        <div class="uni-card-title" title="{{ $name }}">
            {{ Str::limit($name, 26) }}
        </div>
        <div class="uni-card-meta">
            @if($banner && $banner->tagline)
                <div style="font-size:11px; color:#777; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $banner->tagline }}">
                    {{ $banner->tagline }}
                </div>
            @endif
            @if($size)
                <span class="meta-item"><i class="fas fa-hdd"></i> {{ $size }}</span>
            @endif
            @if($date)
                <span class="meta-item"><i class="far fa-clock"></i> {{ $date }}</span>
            @endif
            @if($banner && $banner->show_button && $banner->button_name)
                <span class="meta-item text-primary" title="{{ $banner->button_link }}"><i class="fas fa-link"></i> {{ $banner->button_name }}</span>
            @endif
        </div>
    </div>

    {{-- 3. Card Footer / Actions Toolbar --}}
    <div class="uni-card-footer">
        @if(!$isTrashed)
            @if($type === 'banner')
                {{-- 1-Click Toggle Active/Inactive --}}
                <button type="button"
                        class="uni-action-btn uni-btn-toggle {{ $isActive ? 'text-success' : 'text-muted' }}"
                        onclick="toggleBannerActive({{ $id }}, this);"
                        title="{{ $isActive ? 'Click to Deactivate' : 'Click to Activate' }}"
                        aria-label="Toggle active status">
                    <i class="fas {{ $isActive ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                    <span class="uni-btn-label">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                </button>

                {{-- Edit Banner --}}
                <button type="button"
                        class="uni-action-btn uni-btn-edit text-primary"
                        onclick="openEditModalFromCard({{ $id }});"
                        title="Edit Banner Details"
                        aria-label="Edit Banner">
                    <i class="fas fa-pen"></i> <span class="uni-btn-label">Edit</span>
                </button>

                {{-- Replace Banner Image --}}
                <button type="button"
                        class="uni-action-btn uni-btn-replace"
                        onclick="openReplaceModal({{ $id }}, '{{ addslashes($name) }}', '{{ $url }}', 'banner');"
                        title="Replace Graphic"
                        aria-label="Replace Graphic">
                    <i class="fas fa-exchange-alt"></i>
                </button>

                {{-- Soft Delete Banner --}}
                <button type="button"
                        class="uni-action-btn uni-btn-delete"
                        onclick="deleteBannerItem({{ $id }}, '{{ addslashes($name) }}');"
                        title="Move to Trash"
                        aria-label="Delete">
                    <i class="fas fa-trash-alt"></i>
                </button>
            @else
                {{-- Copy URL Button --}}
                @if($showCopy)
                    <button type="button"
                            class="uni-action-btn uni-btn-copy"
                            onclick="copyMediaUrl('{{ $url }}', this);"
                            title="Copy Public URL"
                            aria-label="Copy Public URL">
                        <i class="fas fa-link"></i> <span class="uni-btn-label">Copy URL</span>
                    </button>
                @endif

                {{-- Use As Dropdown --}}
                @if($showUseAs)
                    <div class="dropdown" style="display:inline-block;">
                        <button type="button"
                                class="uni-action-btn uni-btn-use dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                title="Assign image"
                                aria-label="Assign image to resource">
                            <i class="fas fa-magic"></i> <span class="uni-btn-label">Use As</span> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right uni-dropdown-menu">
                            <li class="dropdown-header">Brand Identity</li>
                            <li>
                                <a href="javascript:void(0)" onclick="executeUseAs({{ $id }}, 'logo', '{{ addslashes($name) }}');">
                                    <i class="fas fa-crown text-warning"></i> Set as Store Logo
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="executeUseAs({{ $id }}, 'favicon', '{{ addslashes($name) }}');">
                                    <i class="fas fa-star text-info"></i> Set as Store Favicon
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li class="dropdown-header">Marketing & Catalog</li>
                            <li>
                                <a href="javascript:void(0)" onclick="executeUseAs({{ $id }}, 'banner', '{{ addslashes($name) }}');">
                                    <i class="fas fa-image text-success"></i> Create Homepage Banner
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="openCategoryAttachModal({{ $id }}, '{{ addslashes($name) }}', '{{ $url }}');">
                                    <i class="fas fa-folder text-warning"></i> Assign to Category...
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="openProductAttachModal({{ $id }}, '{{ addslashes($name) }}', '{{ $url }}');">
                                    <i class="fas fa-tshirt text-primary"></i> Assign to Product...
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="javascript:void(0)" onclick="executeUseAs({{ $id }}, 'avatar', '{{ addslashes($name) }}');">
                                    <i class="fas fa-user-circle text-muted"></i> Set as My Admin Avatar
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif

                {{-- Replace Image Button --}}
                @if($showReplace)
                    <button type="button"
                            class="uni-action-btn uni-btn-replace"
                            onclick="openReplaceModal({{ $id }}, '{{ addslashes($name) }}', '{{ $url }}', '{{ $type }}');"
                            title="Replace Image File"
                            aria-label="Replace Image">
                            <i class="fas fa-exchange-alt"></i>
                    </button>
                @endif

                {{-- Delete Button (Soft-Delete) --}}
                @if($showDelete)
                    <button type="button"
                            class="uni-action-btn uni-btn-delete"
                            onclick="deleteMediaItem({{ $id }}, '{{ $type }}', '{{ addslashes($name) }}');"
                            title="Move to Trash"
                            aria-label="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                @endif
            @endif
        @else
            {{-- Trashed Actions: Restore & Permanent Delete --}}
            @if($type === 'banner')
                <button type="button"
                        class="uni-action-btn uni-btn-restore text-success"
                        onclick="restoreBannerItem({{ $id }}, '{{ addslashes($name) }}');"
                        title="Restore Banner"
                        aria-label="Restore Banner">
                    <i class="fas fa-trash-restore"></i> <span class="uni-btn-label">Restore</span>
                </button>
                <button type="button"
                        class="uni-action-btn uni-btn-force-delete text-danger"
                        onclick="forceDeleteBannerItem({{ $id }}, '{{ addslashes($name) }}');"
                        title="Permanently Delete Banner"
                        aria-label="Permanently Delete Banner">
                    <i class="fas fa-ban"></i> <span class="uni-btn-label">Delete Forever</span>
                </button>
            @else
                <button type="button"
                        class="uni-action-btn uni-btn-restore text-success"
                        onclick="restoreMediaItem({{ $id }}, '{{ $type }}', '{{ addslashes($name) }}');"
                        title="Restore file"
                        aria-label="Restore">
                    <i class="fas fa-trash-restore"></i> <span class="uni-btn-label">Restore</span>
                </button>
                <button type="button"
                        class="uni-action-btn uni-btn-force-delete text-danger"
                        onclick="forceDeleteMediaItem({{ $id }}, '{{ $type }}', '{{ addslashes($name) }}');"
                        title="Permanently Delete"
                        aria-label="Permanently Delete">
                    <i class="fas fa-ban"></i> <span class="uni-btn-label">Delete Forever</span>
                </button>
            @endif
        @endif
    </div>
</div>
