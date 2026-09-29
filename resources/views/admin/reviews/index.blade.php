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
.rv-filter-group { display: flex; gap: 8px; align-items: center; }
.rv-filter-group input, .rv-filter-group select { padding: 7px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; background: #fff; }
.rv-table { width: 100%; border-collapse: collapse; }
.rv-table th, .rv-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: top; }
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
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 20px;">
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer, comment or product…" style="width:260px;" />
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

        <div style="overflow-x:auto;">
            <table class="rv-table">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th style="width:180px;">Product</th>
                        <th style="width:160px;">Customer</th>
                        <th style="width:100px;">Rating</th>
                        <th>Review</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:120px;">Date</th>
                        <th style="width:140px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rv)
                        <tr>
                            <td>#{{ $rv->id }}</td>
                            <td>
                                @if($rv->product)
                                    <a href="{{ url('/product/' . $rv->product->slug) }}" target="_blank" style="font-weight:600; color:#0284c7; text-decoration:none;">
                                        {{ Str::limit($rv->product->name, 28) }}
                                    </a>
                                @else
                                    <span class="text-muted">Deleted Product</span>
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
                                @if($rv->title)
                                    <div style="font-weight:700; margin-bottom:2px;">{{ $rv->title }}</div>
                                @endif
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
                            <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8;">
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
@endsection
