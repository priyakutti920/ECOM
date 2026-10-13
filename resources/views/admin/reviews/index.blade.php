@extends('layouts.admin')

@section('title', 'Product Reviews & Ratings')

@push('styles')
<style>
.rv-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; }
.rv-stats { display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.rv-stat-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 20px; flex: 1; min-width: 140px; }
.rv-stat-box .num { font-size: 24px; font-weight: 800; color: #1e293b; }
.rv-stat-box .lbl { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-top: 2px; }
.rv-filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.rv-filter-group { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.rv-filter-group input, .rv-filter-group select { padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; }
.table-responsive {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
    -webkit-overflow-scrolling: touch !important;
    touch-action: pan-x pan-y !important;
    overscroll-behavior-x: contain;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 16px;
    background: #ffffff;
}
.rv-table { width: 100%; min-width: 900px; border-collapse: collapse; }
.rv-table th, .rv-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: top; touch-action: pan-x pan-y !important; }
.rv-table th { background: #f8fafc; font-weight: 700; color: #475569; }
.rv-table tr:hover td { background: #fafafa; }
.stars { color: #f59e0b; font-size: 13px; }
.badge-appr { background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge-pend { background: #fef9c3; color: #854d0e; padding: 3px 8px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.badge-verified { background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 10.5px; font-weight: 600; margin-left: 4px; }
.btn-sm-action { padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.btn-appr { background: #22c55e; color: #fff; }
.btn-appr:hover { background: #16a34a; }
.btn-rejt { background: #f59e0b; color: #fff; }
.btn-rejt:hover { background: #d97706; }
.btn-del { background: #ef4444; color: #fff; }
.btn-del:hover { background: #dc2626; }
.btn-edit { background: #0284c7; color: #fff; }
.btn-edit:hover { background: #0369a1; }
.badge-cat-quality { background: #e0e7ff; color: #4338ca; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
.badge-cat-service { background: #ccfbf1; color: #0f766e; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
.badge-cat-cart { background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
.mobile-scroll-hint {
    display: none;
    font-size: 11.5px;
    color: #64748b;
    padding: 8px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    align-items: center;
    gap: 6px;
}
.review-modal-box {
    background: #fff;
    border-radius: 12px;
    max-width: 520px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    box-sizing: border-box;
}

@media (max-width: 900px) {
    .mobile-scroll-hint { display: flex; }
}
@media (max-width: 768px) {
    .rv-filters { flex-direction: column; align-items: stretch; gap: 10px; }
    .rv-filter-group { flex-direction: column; align-items: stretch; width: 100%; gap: 8px; }
    .rv-filter-group input, .rv-filter-group select { width: 100% !important; max-width: 100%; box-sizing: border-box; }
    .rv-filters button[type="submit"] { width: 100%; }
}
@media (max-width: 480px) {
    .rv-card { padding: 14px 10px; }
    .rv-stats { gap: 10px; }
    .rv-stat-box { min-width: 100%; }
    .review-modal-box { padding: 16px; border-radius: 8px; }
}
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0; font-size:22px; font-weight:700; color:#1e293b;">Product Reviews & Ratings</h2>
            <p style="margin:4px 0 0; color:#64748b; font-size:13px;">Review customer feedback, approve ratings, and moderate store comments.</p>
        </div>
        <form method="POST" action="{{ route('admin.reviews.toggle-auto-approve') }}">
            @csrf
            <button type="submit" class="btn btn-default" style="font-size:13px; font-weight:600; background:#fff; border:1px solid #cbd5e1; padding:8px 14px; border-radius:6px; cursor:pointer;">
                <i class="fas fa-magic" style="color:{{ $autoApprove ? '#22c55e' : '#94a3b8' }}; margin-right:6px;"></i>
                Auto-Approve Reviews: <strong>{{ $autoApprove ? 'ON' : 'OFF' }}</strong>
            </button>
        </form>
    </div>

    {{-- Stats row --}}
    <div class="rv-stats">
        <div class="rv-stat-box">
            <div class="num">{{ number_format($counts['total']) }}</div>
            <div class="lbl">Total Reviews</div>
        </div>
        <div class="rv-stat-box" style="border-left:4px solid #f59e0b;">
            <div class="num" style="color:#d97706;">{{ number_format($counts['pending']) }}</div>
            <div class="lbl">Pending Moderation</div>
        </div>
        <div class="rv-stat-box" style="border-left:4px solid #22c55e;">
            <div class="num" style="color:#16a34a;">{{ number_format($counts['approved']) }}</div>
            <div class="lbl">Approved & Live</div>
        </div>
    </div>

    <div class="rv-card">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="rv-filters">
            <div class="rv-filter-group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, comment or product…" style="width:240px;" />
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="quality" @selected(request('category') === 'quality')>Quality Review ({{ $counts['quality'] ?? 0 }})</option>
                    <option value="customer_service" @selected(request('category') === 'customer_service')>Customer Service Review ({{ $counts['customer_service'] ?? 0 }})</option>
                    <option value="cart" @selected(request('category') === 'cart')>Cart Review ({{ $counts['cart'] ?? 0 }})</option>
                </select>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending Moderation</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                </select>
                <select name="rating" onchange="this.form.submit()">
                    <option value="">All Ratings</option>
                    <option value="5" @selected(request('rating') === '5')>5 Stars ★★★★★</option>
                    <option value="4" @selected(request('rating') === '4')>4 Stars ★★★★☆</option>
                    <option value="3" @selected(request('rating') === '3')>3 Stars ★★★☆☆</option>
                    <option value="2" @selected(request('rating') === '2')>2 Stars ★★☆☆☆</option>
                    <option value="1" @selected(request('rating') === '1')>1 Star ★☆☆☆☆</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding:6px 16px; font-size:13px; font-weight:600; border-radius:6px;">Filter</button>
        </form>

        <div class="table-responsive">
            <div class="mobile-scroll-hint">
                <i class="fas fa-arrows-alt-h"></i> Scroll right to view ratings, customer details, and action buttons &rarr;
            </div>
            <table class="rv-table">
                <thead>
                    <tr>
                        <th style="width:36px; text-align:center;"><input type="checkbox" id="bulkMasterCheck"></th>
                        <th style="width:60px;">ID</th>
                        <th style="width:160px;">Product</th>
                        <th style="width:140px;">Category</th>
                        <th style="width:150px;">Customer</th>
                        <th style="width:100px;">Rating</th>
                        <th>Review</th>
                        <th style="width:90px;">Status</th>
                        <th style="width:110px;">Date</th>
                        <th style="width:150px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rv)
                        <tr>
                            <td style="vertical-align:middle; text-align:center;">
                                <input type="checkbox" class="bulk-item-check" value="{{ $rv->id }}">
                            </td>
                            <td>#{{ $rv->id }}</td>
                            <td>
                                @if($rv->product)
                                    <a href="{{ url('/product/' . $rv->product->slug) }}" target="_blank" style="font-weight:600; color:#0284c7; text-decoration:none;">
                                        {{ Str::limit($rv->product->name, 26) }}
                                    </a>
                                @else
                                    <span class="text-muted">Deleted Product</span>
                                @endif
                            </td>
                            <td>
                                @if($rv->category === 'customer_service')
                                    <span class="badge-cat-service"><i class="fas fa-headset"></i> Customer Service</span>
                                @elseif($rv->category === 'cart')
                                    <span class="badge-cat-cart"><i class="fas fa-shopping-cart"></i> Cart Review</span>
                                @else
                                    <span class="badge-cat-quality"><i class="fas fa-gem"></i> Quality Review</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $rv->customer_name }}</strong>
                                @if($rv->is_verified_purchase)
                                    <span class="badge-verified" title="Verified Purchaser"><i class="fas fa-check-circle"></i> Verified</span>
                                @endif
                                <div style="font-size:11px; color:#64748b;">{{ $rv->customer_email }}</div>
                            </td>
                            <td>
                                <div class="stars">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="{{ $i <= $rv->rating ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                            </td>
                            <td>
                                <div style="color:#334155; line-height:1.5;">{{ $rv->comment }}</div>
                            </td>
                            <td>
                                @if($rv->is_approved)
                                    <span class="badge-appr">Approved</span>
                                @else
                                    <span class="badge-pend">Pending</span>
                                @endif
                            </td>
                            <td style="color:#64748b; font-size:12px;">{{ $rv->created_at->format('d M Y') }}</td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button type="button" class="btn-sm-action btn-edit" title="Edit Review" onclick="openReviewEditModal({{ $rv->id }}, '{{ $rv->category }}', {{ $rv->rating }}, '{{ addslashes($rv->display_title) }}', '{{ addslashes($rv->comment) }}', {{ $rv->is_approved ? 1 : 0 }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if(!$rv->is_approved)
                                        <form method="POST" action="{{ route('admin.reviews.approve', $rv) }}">
                                            @csrf
                                            <button type="submit" class="btn-sm-action btn-appr" title="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.reviews.reject', $rv) }}">
                                            @csrf
                                            <button type="submit" class="btn-sm-action btn-rejt" title="Unapprove"><i class="fas fa-pause"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.reviews.destroy', $rv) }}" onsubmit="return confirm('Delete this review permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm-action btn-del" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; padding:40px; color:#94a3b8;">
                                <i class="fas fa-star" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                                No reviews found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div style="margin-top:16px;">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Edit Review Modal --}}
<div id="editReviewModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="review-modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
            <h3 style="margin:0; font-size:17px; font-weight:700; color:#1e293b;">Edit Review #<span id="modalReviewId"></span></h3>
            <button type="button" onclick="closeReviewEditModal()" style="background:none; border:none; font-size:22px; color:#64748b; cursor:pointer;">&times;</button>
        </div>
        <form id="editReviewForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12.5px; font-weight:700; color:#475569; margin-bottom:4px;">Review Category</label>
                <select name="category" id="modalCategory" class="form-control" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;" required>
                    <option value="quality">Quality Review</option>
                    <option value="customer_service">Customer Service Review</option>
                    <option value="cart">Cart Review</option>
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12.5px; font-weight:700; color:#475569; margin-bottom:4px;">Rating</label>
                <select name="rating" id="modalRating" class="form-control" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;" required>
                    <option value="5">5 Stars ★★★★★</option>
                    <option value="4">4 Stars ★★★★☆</option>
                    <option value="3">3 Stars ★★★☆☆</option>
                    <option value="2">2 Stars ★★☆☆☆</option>
                    <option value="1">1 Star ★☆☆☆☆</option>
                </select>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:12.5px; font-weight:700; color:#475569; margin-bottom:4px;">Comment</label>
                <textarea name="comment" id="modalComment" rows="4" class="form-control" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;" required></textarea>
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#334155; cursor:pointer;">
                    <input type="checkbox" name="is_approved" id="modalApproved" value="1">
                    <span>Approved & Published</span>
                </label>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeReviewEditModal()" class="btn btn-default" style="padding:7px 16px; border:1px solid #cbd5e1; border-radius:6px; background:#fff; font-size:13px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:7px 18px; border-radius:6px; font-size:13px; font-weight:600;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    var html = '';
    html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeReviewBulk(\'approve\')"><i class="fas fa-check-circle"></i> Approve</button>';
    html += '<button type="button" class="bulk-action-btn" onclick="executeReviewBulk(\'reject\')"><i class="fas fa-ban"></i> Reject / Unapprove</button>';
    html += '<button type="button" class="bulk-action-btn btn-bulk-danger" onclick="executeReviewBulk(\'delete\', \'Delete {count} selected review(s)?\')"><i class="fas fa-trash"></i> Delete</button>';
    $('#bulkBarActions').html(html);
});

function executeReviewBulk(action, confirmMsg) {
    runBulkAction('{{ route("admin.reviews.bulk-action") }}', action, {}, confirmMsg);
}

function openReviewEditModal(id, category, rating, title, comment, isApproved) {
    var modal = document.getElementById('editReviewModal');
    if (!modal) return;
    document.getElementById('modalReviewId').textContent = id;
    document.getElementById('editReviewForm').action = '{{ url("/admin/reviews") }}/' + id;
    document.getElementById('modalCategory').value = category || 'quality';
    document.getElementById('modalRating').value = rating || 5;
    document.getElementById('modalComment').value = comment || '';
    document.getElementById('modalApproved').checked = !!isApproved;
    modal.style.display = 'flex';
}

function closeReviewEditModal() {
    var modal = document.getElementById('editReviewModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('click', function(e) {
    var modal = document.getElementById('editReviewModal');
    if (modal && e.target === modal) {
        closeReviewEditModal();
    }
});
</script>
@endpush
