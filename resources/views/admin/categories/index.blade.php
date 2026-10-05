@extends('layouts.admin')

@section('title', 'Categories')

@push('styles')
<style>
/* ── Toast ── */
#toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
.toast-msg {
    display:flex; align-items:center; gap:8px;
    padding:10px 14px; border-radius:5px; margin-bottom:7px;
    font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10);
}
.toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
.toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }

/* ── Tree wrapper ── */
.cat-tree-box {
    background: #fff;
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    overflow: hidden;
    padding: 12px 8px 12px 4px;
}

/* ── Tree node ── */
.tree-node {
    position: relative;
}
.tree-node-content {
    display: flex;
    align-items: center;
    padding: 5px 8px;
    border-radius: 4px;
    gap: 6px;
    cursor: pointer;
    transition: background .1s;
    position: relative;
}
.tree-node-content:hover { background: #f5f5f5; }

/* ── Tree lines ── */
.tree-lines {
    display: flex;
    align-items: flex-start;
    flex-shrink: 0;
}
.tree-vline {
    width: 20px;
    height: 100%;
    position: relative;
    flex-shrink: 0;
}
.tree-vline::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: -2px;
    width: 1px;
    background: #ccc;
    transform: translateX(-50%);
}
.tree-hline {
    width: 14px;
    height: 20px;
    position: relative;
    flex-shrink: 0;
}
.tree-hline::before {
    content: '';
    position: absolute;
    top: 10px;
    left: 0;
    right: 0;
    height: 1px;
    background: #ccc;
}
.tree-vline-last::before {
    height: 10px;
    bottom: auto;
    top: 0;
}

/* ── Expand/collapse toggle ── */
.tree-toggle {
    width: 18px;
    height: 18px;
    border: 1.5px solid #aaa;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    background: #fff;
    font-size: 13px;
    font-weight: 700;
    line-height: 1;
    color: #888;
    user-select: none;
    transition: border-color .15s, background .15s;
}
.tree-toggle:hover { border-color: #555; background: #f0f0f0; }
.tree-toggle.empty {
    background: transparent;
    border-color: transparent;
    cursor: default;
}
.tree-toggle.empty::before,
.tree-toggle.empty::after { display: none; }

/* ── Folder icon ── */
.tree-folder {
    font-size: 15px;
    color: #f0c040;
    flex-shrink: 0;
    text-shadow: 0 1px 0 rgba(0,0,0,0.1);
}

/* ── Category name ── */
.tree-name {
    flex: 1;
    font-weight: 600;
    font-size: 13px;
    color: #222;
    letter-spacing: .03em;
    white-space: nowrap;
}
.tree-count {
    font-size: 11px;
    color: #999;
    font-weight: 400;
    margin-left: 6px;
}

/* ── Actions (hidden until hover) ── */
.tree-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity .15s;
}
.tree-node-content:hover .tree-actions { opacity: 1; }
.tree-actions .btn-icon {
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 4px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: filter .15s;
}
.tree-actions .btn-icon:hover  { filter: brightness(.88); }
.tree-actions .btn-icon.edit   { background: #3a9fbf; color: #fff; }
.tree-actions .btn-icon.delete { background: #c0392b; color: #fff; }
.tree-actions .btn-icon.addsub {
    background: #27ae60;
    color: #fff;
    font-size: 11px;
}

/* ── Children container ── */
.tree-children {
    padding-left: 20px;
}

/* ── Drag & drop ── */
.tree-node-content.sortable-ghost  { opacity: .35; background: #f0f0ff !important; }
.tree-node-content.sortable-chosen { background: #f8f8f8; }

/* ── Empty state ── */
.empty-state { text-align:center; padding:52px 20px; color:#bbb; }
.empty-state i { font-size:34px; margin-bottom:10px; display:block; }
.empty-state p { font-size:13px; margin:0; }

/* ── Row flash ── */
.row-flash { animation: row-flash 1s ease forwards; }
@keyframes row-flash { 0%{background:#fffbe6} 100%{background:transparent} }

/* ── Modal ── */
.modal-content  { border-radius:6px; border:none; box-shadow:0 4px 28px rgba(0,0,0,0.14); }
.modal-header   { padding:15px 18px; border-bottom:1px solid #eee; }
.modal-title    { font-weight:700; font-size:15px; }
.modal-body     { padding:18px; }
.modal-footer   { padding:11px 18px; border-top:1px solid #eee; }

/* ── Tree controls ── */
.tree-controls {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 10px;
    padding: 0 2px;
}
.tree-controls a {
    font-size: 12px;
    color: #2980b9;
    text-decoration: none;
    padding: 3px 8px;
    border-radius: 3px;
    transition: background .1s;
}
.tree-controls a:hover { background: #eef6fb; }
.tree-controls .sep {
    color: #ccc;
    font-size: 12px;
    padding: 0 2px;
}

/* ── Modal parent name hint ── */
#cat-parent-hint {
    font-size: 12px;
    color: #888;
    margin-top: 4px;
    display: none;
}

</style>
@endpush

@section('content')
@php 
$catJson = $categories->map(function($c){ 
    return [
        'id' => $c->id,
        'name' => $c->name,
        'slug' => $c->slug,
        'parent_id' => $c->parent_id,
        'image' => $c->image,
        'image_url' => $c->image_url,
        'is_active' => (bool)$c->is_active,
        'meta_title' => $c->meta_title,
        'meta_description' => $c->meta_description,
        'meta_keywords' => $c->meta_keywords,
    ]; 
}); 
@endphp
<div id="toast-wrap"></div>

<div style="max-width:960px; margin:0 auto;">

    {{-- Page header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <h2 style="margin:0; font-weight:700; font-size:22px;">
            <i class="fas fa-sitemap" style="font-size:18px; margin-right:6px;"></i> Categories
        </h2>
        <div style="display:flex; gap:8px;">
            <button id="btn-new-subcategory" class="btn btn-default" style="font-weight:600; padding:7px 18px; background:#f0f0f0; border:none;">
                + Add Subcategory
            </button>
            <button id="btn-new-category" class="btn btn-primary" style="font-weight:600; padding:7px 18px;">
                + Add Root Category
            </button>
        </div>
    </div>

    {{-- Tree controls --}}
    <div class="tree-controls" id="tree-controls" style="{{ $categories->isEmpty() ? 'display:none;' : '' }}">
        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; margin:0 8px 0 0; color:#334155; font-weight:600;">
            <input type="checkbox" id="bulkMasterCheck" style="margin:0;"> Select All
        </label>
        <span class="sep">|</span>
        <a href="#" id="btn-collapse">Collapse All</a>
        <span class="sep">|</span>
        <a href="#" id="btn-expand">Expand All</a>
    </div>

    {{-- Tree box --}}
    <div class="cat-tree-box" id="tree-box">

        {{-- Empty state --}}
        <div id="empty-state" style="{{ $categories->isEmpty() ? '' : 'display:none;' }}">
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>No categories yet. Click <strong>Add Root Category</strong> to create one.</p>
            </div>
        </div>

        {{-- Tree container --}}
        <div id="category-tree" style="{{ $categories->isEmpty() ? 'display:none;' : '' }}"></div>

    </div>

</div>

{{-- ═══════════════════════════════
     MODAL — Add / Edit Category
═══════════════════════════════ --}}
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width:500px; margin-top:60px;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-title-label">+ Add Category</h4>
            </div>

            <div class="modal-body">

                {{-- Name --}}
                <div class="form-group">
                    <label style="font-weight:600; font-size:13px;">Category Name</label>
                    <input type="text" id="cat-name" class="form-control" placeholder="Enter category name">
                    <span class="text-danger" id="err-name" style="font-size:12px; display:none;"></span>
                </div>

                {{-- Slug & Status --}}
                <div class="row">
                    <div class="col-sm-8">
                        <div class="form-group">
                            <label style="font-weight:600; font-size:13px;">Slug <span class="text-muted" style="font-weight:400; font-size:11px;">(Auto-generated if empty)</span></label>
                            <input type="text" id="cat-slug" class="form-control" placeholder="category-slug">
                            <span class="text-danger" id="err-slug" style="font-size:12px; display:none;"></span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group" style="padding-top:26px;">
                            <label style="font-weight:600; font-size:13px; cursor:pointer;">
                                <input type="checkbox" id="cat-is-active" checked value="1"> Active
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Parent --}}
                <div class="form-group">
                    <label style="font-weight:600; font-size:13px;">Parent Category</label>
                    <select id="cat-parent" class="form-control">
                        <option value="">-- None --</option>
                    </select>
                </div>

                {{-- Image --}}
                <div class="form-group">
                    <label style="font-weight:600; font-size:13px;">
                        Category Image
                        <span class="text-muted" style="font-weight:400; font-size:12px;">(optional)</span>
                    </label>
                    <input type="file" id="img-input" class="form-control" accept="image/*">
                    <span class="text-danger" id="err-image" style="font-size:12px; display:none;"></span>
                    <div id="img-preview-wrap" style="margin-top:10px; display:none;">
                        <img id="img-preview" src="" alt="Preview"
                            style="width:72px; height:72px; object-fit:cover; border:1px solid #ddd; border-radius:4px; display:block;">
                        <button type="button" id="btn-remove-img"
                            style="margin-top:5px; background:none; border:none; color:#c0392b; font-size:12px; cursor:pointer; padding:0;">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                </div>

                {{-- SEO Settings --}}
                <div style="margin-top:14px; padding-top:12px; border-top:1px solid #eee;">
                    <a href="javascript:void(0)" onclick="$('#cat-seo-fields').slideToggle(200);" style="font-size:13px; font-weight:600; color:#007185; text-decoration:none;">
                        <i class="fas fa-search" style="margin-right:4px;"></i> Category SEO Settings <i class="fas fa-angle-down"></i>
                    </a>
                    <div id="cat-seo-fields" style="display:none; margin-top:10px;">
                        <div class="form-group">
                            <label style="font-size:12px; font-weight:600;">Meta Title</label>
                            <input type="text" id="cat-meta-title" class="form-control input-sm" placeholder="SEO Page Title">
                        </div>
                        <div class="form-group">
                            <label style="font-size:12px; font-weight:600;">Meta Description</label>
                            <textarea id="cat-meta-desc" class="form-control input-sm" rows="2" placeholder="SEO Description"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:12px; font-weight:600;">Meta Keywords</label>
                            <input type="text" id="cat-meta-keywords" class="form-control input-sm" placeholder="e.g. clothing, fashion, shirts">
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="btn-save" class="btn btn-primary" style="min-width:80px; font-weight:600;">
                    <span id="btn-save-text">
                        <i class="fas fa-save"></i> <span id="btn-save-label">Add</span>
                    </span>
                    <span id="btn-save-spin" style="display:none;">
                        <span class="spinner-border spinner-border-sm"></span> Saving…
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
var ALL_CATS = <?php echo json_encode($catJson); ?>;
var editingId = null;
var activeParentId = null;
var expandedNodes = {};

$(function () {

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    /* ── Toast ── */
    function toast(msg, type) {
        var icon = (type === 'error') ? 'fa-exclamation-circle' : 'fa-check-circle';
        var el = $('<div class="toast-msg ' + (type || 'success') + '">' +
                   '<i class="fas ' + icon + '"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3000);
    }

    /* ── Helpers ── */
    function esc(str) {
        if (str == null) return '';
        return $('<span>').text(str).html();
    }

    /* ── Build parent <select> options ── */
    function buildParentOptions(excludeId, selectedId) {
        $('#cat-parent').html('<option value="">-- None (Root) --</option>');
        ALL_CATS.forEach(function (c) {
            if (c.id == excludeId) return;
            var sel = (selectedId && c.id == selectedId) ? ' selected' : '';
            $('#cat-parent').append(
                '<option value="' + c.id + '"' + sel + '>' + esc(c.name) + '</option>'
            );
        });
    }

    /* ── Reset modal ── */
    function resetModal() {
        editingId = null;
        activeParentId = null;
        $('#cat-name').val('');
        $('#cat-slug').val('');
        $('#cat-is-active').prop('checked', true);
        $('#cat-meta-title').val('');
        $('#cat-meta-desc').val('');
        $('#cat-meta-keywords').val('');
        $('#cat-seo-fields').hide();
        $('#img-input').val('');
        $('#img-preview').attr('src', '');
        $('#img-preview-wrap').hide();
        $('#err-name, #err-slug, #err-image').hide().text('');
        $('#modal-title-label').text('+ Add Category');
        $('#btn-save-label').text('Add');
        $('#cat-parent-hint').hide().text('');
        buildParentOptions(null, null);
    }

    /* ── Open: New Root Category ── */
    $('#btn-new-category').on('click', function () {
        resetModal();
        $('#categoryModal').modal('show');
        setTimeout(function () { $('#cat-name').focus(); }, 350);
    });

    /* ── Open: New Subcategory ── */
    $('#btn-new-subcategory').on('click', function () {
        resetModal();
        // If a node is selected, pre-select its parent
        var $selected = $('.tree-node-content.selected');
        if ($selected.length) {
            var pid = $selected.data('parent-id') || '';
            buildParentOptions(null, pid);
            if (pid) {
                $('#cat-parent').val(pid);
            }
        }
        $('#categoryModal').modal('show');
        setTimeout(function () { $('#cat-name').focus(); }, 350);
    });

    /* ── Open: Edit ── */
    $(document).on('click', '.btn-edit', function (e) {
        e.stopPropagation();
        resetModal();
        editingId    = $(this).data('id');
        var cat      = ALL_CATS.find(function (c) { return c.id == editingId; }) || {};
        var name     = cat.name || $(this).data('name');
        var parentId = (cat.parent_id !== undefined) ? cat.parent_id : $(this).data('parent');
        var imgUrl   = cat.image_url || $(this).data('image');

        $('#modal-title-label').html('<i class="fas fa-pen" style="font-size:13px;"></i> Edit Category');
        $('#btn-save-label').text('Save');
        $('#cat-name').val(name);
        $('#cat-slug').val(cat.slug || '');
        $('#cat-is-active').prop('checked', cat.is_active !== false);
        $('#cat-meta-title').val(cat.meta_title || '');
        $('#cat-meta-desc').val(cat.meta_description || '');
        $('#cat-meta-keywords').val(cat.meta_keywords || '');

        buildParentOptions(editingId, parentId || '');

        if (imgUrl) {
            $('#img-preview').attr('src', imgUrl);
            $('#img-preview-wrap').show();
        }

        $('#categoryModal').modal('show');
        setTimeout(function () { $('#cat-name').focus(); }, 350);
    });

    /* ── Open: Add Sub to specific node ── */
    $(document).on('click', '.btn-addsub', function (e) {
        e.stopPropagation();
        resetModal();
        var parentId = $(this).data('parent-id');
        buildParentOptions(null, parentId);
        if (parentId) {
            $('#cat-parent').val(parentId);
        }
        $('#modal-title-label').html('<i class="fas fa-plus" style="font-size:13px;"></i> Add Subcategory');
        $('#btn-save-label').text('Add');
        $('#categoryModal').modal('show');
        setTimeout(function () { $('#cat-name').focus(); }, 350);
    });

    /* ── Image selected → preview ── */
    $('#img-input').on('change', function () {
        var f = this.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function (e) {
            $('#img-preview').attr('src', e.target.result);
            $('#img-preview-wrap').show();
        };
        r.readAsDataURL(f);
    });

    /* ── Remove image preview ── */
    $('#btn-remove-img').on('click', function () {
        $('#img-input').val('');
        $('#img-preview').attr('src', '');
        $('#img-preview-wrap').hide();
    });

    /* ── Save ── */
    $('#btn-save').on('click', function () {
        $('#err-name, #err-slug, #err-image').hide().text('');

        var name = $('#cat-name').val().trim();
        if (!name) {
            $('#err-name').text('Category name is required.').show();
            $('#cat-name').focus();
            return;
        }

        var fd = new FormData();
        fd.append('name', name);
        fd.append('slug', $('#cat-slug').val().trim());
        fd.append('is_active', $('#cat-is-active').is(':checked') ? '1' : '0');
        fd.append('parent_id', $('#cat-parent').val() || '');
        fd.append('meta_title', $('#cat-meta-title').val().trim());
        fd.append('meta_description', $('#cat-meta-desc').val().trim());
        fd.append('meta_keywords', $('#cat-meta-keywords').val().trim());
        var f = $('#img-input')[0].files[0];
        if (f) fd.append('image', f);

        var url = editingId
            ? '{{ url("admin/categories") }}/' + editingId
            : '{{ route("admin.categories.store") }}';

        $('#btn-save-text').hide();
        $('#btn-save-spin').show();
        $('#btn-save').prop('disabled', true);

        $.ajax({
            url: url, method: 'POST', data: fd, processData: false, contentType: false,
            success: function (res) {
                if (!res.success) return;
                $('#categoryModal').modal('hide');

                // Update ALL_CATS
                ALL_CATS = ALL_CATS.filter(function (c) { return c.id != res.category.id; });
                ALL_CATS.push(res.category);

                // Expand parent if adding child
                if (res.category.parent_id && !expandedNodes[res.category.parent_id]) {
                    expandedNodes[res.category.parent_id] = true;
                }

                rebuildTree();
                syncEmpty();
                toast(res.message);
            },
            error: function (xhr) {
                var errs = xhr.responseJSON && xhr.responseJSON.errors;
                if (errs) {
                    if (errs.name)      $('#err-name').text(errs.name[0]).show();
                    if (errs.slug)      $('#err-slug').text(errs.slug[0]).show();
                    if (errs.image)     $('#err-image').text(errs.image[0]).show();
                    if (errs.parent_id) toast(errs.parent_id[0], 'error');
                } else {
                    toast('Something went wrong. Please try again.', 'error');
                }
            },
            complete: function () {
                $('#btn-save-text').show();
                $('#btn-save-spin').hide();
                $('#btn-save').prop('disabled', false);
            }
        });
    });

    /* ── Delete ── */
    $(document).on('click', '.btn-delete', function (e) {
        e.stopPropagation();
        var id   = $(this).data('id');
        var name = $(this).closest('.tree-node').find('.tree-name').clone()
                   .children().remove().end().text().trim();

        if (!confirm('Delete "' + name + '"?\n\nAll subcategories will also be removed.')) return;

        var $btn = $(this).prop('disabled', true);

        $.ajax({
            url: '{{ url("admin/categories") }}/' + id, method: 'DELETE',
            success: function (res) {
                if (!res.success) return;
                ALL_CATS = ALL_CATS.filter(function (c) { return c.id != id; });
                delete expandedNodes[id];
                rebuildTree();
                syncEmpty();
                toast(res.message);
            },
            error: function () {
                $btn.prop('disabled', false);
                toast('Could not delete category.', 'error');
            }
        });
    });

    /* ── Toggle Active ── */
    $(document).on('click', '.btn-toggle-active', function (e) {
        e.stopPropagation();
        var id = $(this).data('id');
        var $btn = $(this);
        $.ajax({
            url: '{{ url("admin/categories") }}/' + id + '/toggle',
            method: 'POST',
            success: function (res) {
                if (!res.success) return;
                var c = ALL_CATS.find(function (item) { return item.id == id; });
                if (c) c.is_active = res.is_active;
                rebuildTree();
                toast(res.message);
            },
            error: function () {
                toast('Could not toggle status.', 'error');
            }
        });
    });

    /* ── Empty state sync ── */
    function syncEmpty() {
        var n = ALL_CATS.length;
        if (n === 0) {
            $('#category-tree').hide();
            $('#tree-controls').hide();
            $('#empty-state').show();
        } else {
            $('#category-tree').show();
            $('#tree-controls').show();
            $('#empty-state').hide();
        }
    }

    /* ═══════════════════════════════════════════
       TREE BUILDER
    ═══════════════════════════════════════════ */

    function buildTreeNode(cat, children, isLast) {
        var hasChildren = children && children.length > 0;
        var isExpanded  = !!expandedNodes[cat.id];

        // Lines wrapper
        var linesHtml = '<div class="tree-lines">';

        // Toggle button
        if (hasChildren) {
            linesHtml += '<div class="tree-toggle"' +
                (isExpanded ? ' data-expanded="1"' : '') + '>' +
                (isExpanded ? '−' : '+') + '</div>';
        } else {
            linesHtml += '<div class="tree-toggle empty"></div>';
        }

        linesHtml += '</div>';

        // Count badge
        var countHtml = hasChildren
            ? '<span class="tree-count">(' + children.length + ')</span>'
            : '';

        var isAct = (cat.is_active !== false && cat.is_active !== 0 && cat.is_active !== '0');
        var toggleIcon = isAct ? 'fa-eye' : 'fa-eye-slash';
        var toggleTitle = isAct ? 'Category is Active (click to disable)' : 'Category is Disabled (click to enable)';
        var toggleBg = isAct ? '#27ae60' : '#7f8c8d';
        var inactiveBadge = !isAct ? ' <span style="font-size:10px; color:#e74c3c; font-weight:700;">(Disabled)</span>' : '';

        // Actions
        var actionsHtml =
            '<div class="tree-actions">' +
                '<button class="btn-icon btn-toggle-active" title="' + toggleTitle + '" data-id="' + cat.id + '" style="background:' + toggleBg + '; color:#fff;">' +
                    '<i class="fas ' + toggleIcon + '"></i></button>' +
                '<button class="btn-icon addsub btn-addsub" title="Add Subcategory" data-parent-id="' + cat.id + '">' +
                    '<i class="fas fa-plus"></i></button>' +
                '<button class="btn-icon edit btn-edit" title="Edit"' +
                    ' data-id="' + cat.id + '"' +
                    ' data-name="' + esc(cat.name) + '"' +
                    ' data-parent="' + (cat.parent_id || '') + '"' +
                    ' data-image="' + esc(cat.image_url || '') + '">' +
                    '<i class="fas fa-pencil-alt"></i></button>' +
                '<button class="btn-icon delete btn-delete" title="Delete" data-id="' + cat.id + '">' +
                    '<i class="fas fa-trash"></i></button>' +
            '</div>';

        // Children container (hidden by default)
        var childrenHtml = '';
        if (hasChildren) {
            childrenHtml = '<div class="tree-children"' +
                (isExpanded ? '' : ' style="display:none;"') + '>' +
                buildTree(children, 1) +
            '</div>';
        }

        // Last-child line class for connector
        var lastClass = isLast ? ' tree-vline-last' : '';

        return '' +
            '<div class="tree-node" data-id="' + cat.id + '">' +
                '<div class="tree-node-content"' +
                    ' data-id="' + cat.id + '"' +
                    ' data-parent-id="' + (cat.parent_id || '') + '">' +
                    '<div class="tree-vline' + lastClass + '"></div>' +
                    linesHtml +
                    '<input type="checkbox" class="bulk-item-check" value="' + cat.id + '" style="margin:0 6px 0 2px; cursor:pointer;" onclick="event.stopPropagation();" onchange="updateBulkBar();">' +
                    '<i class="fas fa-folder tree-folder"></i>' +
                    '<span class="tree-name">' + esc(cat.name) + countHtml + inactiveBadge + '</span>' +
                    actionsHtml +
                '</div>' +
                childrenHtml +
            '</div>';
    }

    function buildTree(cats, depth) {
        var result = '';
        cats.forEach(function (cat, idx) {
            var isLast = (idx === cats.length - 1);
            var children = getChildren(cat.id);
            result += buildTreeNode(cat, children, isLast);
        });
        return result;
    }

    function getChildren(parentId) {
        var children = [];
        ALL_CATS.forEach(function (c) {
            if ((c.parent_id || null) == (parentId || null)) {
                children.push(c);
            }
        });
        return children;
    }

    function getRootCats() {
        var idMap = {};
        ALL_CATS.forEach(function (c) { idMap[c.id] = true; });

        return ALL_CATS.filter(function (c) {
            return !c.parent_id || !idMap[c.parent_id];
        });
    }

    function rebuildTree() {
        var roots = getRootCats();
        $('#category-tree').html(buildTree(roots, 0));
        attachSortable();
    }

    /* ── Toggle expand/collapse ── */
    $(document).on('click', '.tree-toggle:not(.empty)', function () {
        var $node   = $(this).closest('.tree-node');
        var id      = $node.data('id');
        var $toggle = $node.find('> .tree-node-content .tree-toggle');
        var isExpanded = $toggle.data('expanded');

        if (isExpanded) {
            // Collapse
            $toggle.text('+').data('expanded', '');
            $node.find('> .tree-children').hide();
            expandedNodes[id] = false;
        } else {
            // Expand
            $toggle.text('−').data('expanded', '1');
            $node.find('> .tree-children').show();
            expandedNodes[id] = true;
        }
    });

    /* ── Expand All / Collapse All ── */
    $('#btn-expand').on('click', function (e) {
        e.preventDefault();
        ALL_CATS.forEach(function (c) { expandedNodes[c.id] = true; });
        $('.tree-children').show();
        $('.tree-toggle:not(.empty)').text('−').data('expanded', '1');
    });

    $('#btn-collapse').on('click', function (e) {
        e.preventDefault();
        ALL_CATS.forEach(function (c) { expandedNodes[c.id] = false; });
        $('.tree-children').hide();
        $('.tree-toggle:not(.empty)').text('+').data('expanded', '');
    });

    /* ── Expand parents up the chain ── */
    function expandParents(catId) {
        var cat = ALL_CATS.find(function (c) { return c.id == catId; });
        while (cat && cat.parent_id) {
            expandedNodes[cat.parent_id] = true;
            cat = ALL_CATS.find(function (c) { return c.id == cat.parent_id; });
        }
    }

    /* ── Drag & drop within same level ── */
    var sortableInstances = [];

    function attachSortable() {
        // Destroy old instances
        sortableInstances.forEach(function (inst) { inst.destroy(); });
        sortableInstances = [];

        // Make each level sortable (roots and children)
        $('#category-tree, .tree-children').each(function () {
            var inst = new Sortable(this, {
                group       : 'tree',
                animation   : 150,
                ghostClass  : 'sortable-ghost',
                chosenClass : 'sortable-chosen',
                handle      : '.tree-folder',
                draggable   : '.tree-node',
                onEnd: function (evt) {
                    var nodeId    = $(evt.item).data('id');
                    var newParent = null;

                    // Determine new parent
                    var $parentChildren = $(evt.to);
                    if ($parentChildren.hasClass('tree-children')) {
                        var $parentNode = $parentChildren.closest('.tree-node');
                        if ($parentNode.length) {
                            newParent = $parentNode.data('id');
                        }
                    }

                    // Check if parent changed
                    var cat = ALL_CATS.find(function (c) { return c.id == nodeId; });
                    if (!cat) return;

                    if (cat.parent_id !== newParent) {
                        // Update parent locally
                        cat.parent_id = newParent;
                        if (newParent) expandedNodes[newParent] = true;

                        $.ajax({
                            url        : '{{ url("admin/categories") }}/' + nodeId,
                            method     : 'POST',
                            data       : { _method: 'PUT', parent_id: newParent || '' },
                            success    : function (res) {
                                expandParents(nodeId);
                                rebuildTree();
                                toast(res.message || 'Category moved.');
                            },
                            error: function () {
                                toast('Could not move category.', 'error');
                                rebuildTree();
                            }
                        });
                    }
                }
            });
            sortableInstances.push(inst);
        });
    }

    /* ── Initial build ── */
    // Expand root categories by default
    getRootCats().forEach(function (root) {
        if (getChildren(root.id).length > 0) {
            expandedNodes[root.id] = true;
        }
    });

    rebuildTree();
    syncEmpty();

    /* ── Modal events ── */
    $('#categoryModal').on('hidden.bs.modal', resetModal);
    $('#cat-name').on('keydown', function (e) { if (e.key === 'Enter') $('#btn-save').trigger('click'); });

    // Register Bulk Actions in Universal Bar
    var html = '';
    html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeCategoryBulk(\'activate\')"><i class="fas fa-check-circle"></i> Activate</button>';
    html += '<button type="button" class="bulk-action-btn" onclick="executeCategoryBulk(\'deactivate\')"><i class="fas fa-pause-circle"></i> Deactivate</button>';
    html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeCategoryBulk(\'delete\', \'Delete {count} selected category/categories and their subcategories?\')"><i class="fas fa-trash"></i> Delete</button>';
    $('#bulkBarActions').html(html);

});

function executeCategoryBulk(action, confirmMsg) {
    runBulkAction('{{ route("admin.categories.bulk-action") }}', action, {}, confirmMsg);
}
</script>
@endpush
