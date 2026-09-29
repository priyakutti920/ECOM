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
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary" style="font-weight:600; padding:7px 20px;">
            <i class="fas fa-plus"></i> Add Product
        </a>
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
                    <td style="padding:8px 12px; vertical-align:middle;">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}"
                                class="table-prod-img" alt="{{ $product->name }}">
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
</script>
@endpush