@extends('layouts.shop')

@section('title', 'My Orders — ' . $storeName)

@push('styles')
<style>
.orders-wrap { max-width: 1100px; margin: 0 auto; padding: 24px 16px 60px; }
.orders-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 8px; }
.orders-header h1 { margin: 0; font-size: 22px; color: var(--amazon-charcoal); }
.orders-header .count { font-size: 13px; color: var(--medium-gray); }

.orders-list { display: flex; flex-direction: column; gap: 14px; }
.order-card {
 background: #fff; border: 1px solid #e7e7e7; border-radius: 8px;
 padding: 16px 20px; transition: box-shadow 0.15s, border-color 0.15s;
 display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: center;
}
.order-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-color: #d5d9d9; }

.order-head { display: flex; flex-wrap: wrap; gap: 16px 24px; align-items: baseline; margin-bottom: 6px; }
.order-head .id { font-size: 18px; font-weight: 700; color: var(--amazon-charcoal); letter-spacing: 0.5px; }
.order-head .meta { font-size: 12px; color: var(--medium-gray); }
.order-head .meta strong { color: #0F1111; font-weight: 600; }

.order-items-preview { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
.order-items-preview .thumb {
 width: 50px; height: 50px; border-radius: 4px; background: #f3f3f3; overflow: hidden;
 display: flex; align-items: center; justify-content: center; color: #ccc; border: 1px solid #eee;
}
.order-items-preview .thumb img { width: 100%; height: 100%; object-fit: cover; }
.order-items-preview .more {
 width: 50px; height: 50px; border-radius: 4px; background: #fafafa; border: 1px solid #eee;
 display: flex; align-items: center; justify-content: center; font-size: 12px; color: var(--medium-gray); font-weight: 600;
}

.order-right { text-align: right; }
.order-right .total { font-size: 18px; font-weight: 700; color: #c7511f; }
.order-right .view-btn {
 display: inline-block; margin-top: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600;
 background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; color: #0F1111; text-decoration: none;
}
.order-right .view-btn:hover { background: #f7ca00; }

.status-badge { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
.status-placed   { background: #fff8e1; color: #946a00; }
.status-packed   { background: #e3f2fd; color: #0a4b6e; }
.status-shipped  { background: #e0f2f1; color: #00695c; }
.status-delivered{ background: #d4edda; color: #155724; }
.status-cancelled{ background: #f8d7da; color: #721c24; }
.status-paid     { background: #d4edda; color: #155724; }
.status-failed   { background: #f8d7da; color: #721c24; }
.status-refunded { background: #e2e3e5; color: #383d41; }

.orders-empty {
 text-align: center; padding: 60px 20px; background: #fff; border: 1px dashed #d5d9d9; border-radius: 8px;
}
.orders-empty i { font-size: 48px; color: #d5d9d9; margin-bottom: 12px; }
.orders-empty h2 { font-size: 18px; color: var(--amazon-charcoal); margin: 0 0 6px; }
.orders-empty p { color: var(--medium-gray); font-size: 14px; margin: 0 0 18px; }
.orders-empty a { background: #ffd814; border: 1px solid #fcd200; padding: 10px 24px; border-radius: 100px; text-decoration: none; color: #0F1111; font-weight: 600; font-size: 14px; }

.pagination-wrap { margin-top: 22px; }
.pagination-wrap nav { display: flex; justify-content: center; }

@media (max-width: 700px) {
 .order-card { grid-template-columns: 1fr; }
 .order-right { text-align: left; }
}
</style>
@endpush

@section('content')
<div class="orders-wrap">
 <div class="orders-header">
 <h1><i class="fas fa-box" style="color: var(--amazon-orange);"></i> My Orders</h1>
 <span class="count">{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}</span>
 </div>

 @if($orders->isEmpty())
 <div class="orders-empty">
 <i class="fas fa-shopping-bag"></i>
 <h2>No orders yet</h2>
 <p>You haven't placed any orders with us. Let's change that!</p>
 <a href="{{ url('/') }}">Start shopping</a>
 </div>
 @else
 <div class="orders-list">
 @foreach($orders as $o)
 <div class="order-card">
 <div>
 <div class="order-head">
 <span class="id">{{ $o->order_code }}</span>
 <span class="status-badge status-{{ $o->status }}">{{ ucfirst($o->status) }}</span>
 <span class="status-badge status-{{ $o->payment_status }}">{{ ucfirst($o->payment_status) }}</span>
 <span class="meta">Placed <strong>{{ $o->created_at->format('d M Y, h:i A') }}</strong></span>
 <span class="meta"><strong>{{ $o->items->count() }}</strong> {{ \Illuminate\Support\Str::plural('item', $o->items->count()) }}</span>
 </div>
 <div class="order-items-preview">
 @foreach($o->items->take(5) as $it)
 <div class="thumb">
 @if($it->product_image)
 <img src="{{ $it->product_image }}" alt="">
 @else
 <i class="fas fa-image"></i>
 @endif
 </div>
 @endforeach
 @if($o->items->count() > 5)
 <div class="more">+{{ $o->items->count() - 5 }}</div>
 @endif
 </div>
 </div>
 <div class="order-right">
 <div class="total">₹{{ number_format($o->total, 2) }}</div>
 <a href="{{ route('shop.orders.show', ['order' => $o->order_code]) }}" class="view-btn">View details</a>
 <a href="{{ route('shop.orders.invoice', ['order' => $o->order_code]) }}" style="display:inline-block; margin-top:6px; font-size:12px; color:#007185; text-decoration:none;"><i class="fas fa-file-pdf"></i> Download Invoice</a>
 </div>
 </div>
 @endforeach
 </div>

 <div class="pagination-wrap">
 {{ $orders->links() }}
 </div>
 @endif
</div>
@endsection
