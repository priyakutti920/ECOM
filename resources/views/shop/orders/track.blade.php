@extends('layouts.shop')

@section('title', 'Track Your Order — ' . $storeName)
@section('description', 'Track your package and delivery progress on ' . $storeName)

@push('styles')
<style>
.track-wrap { max-width: 900px; margin: 30px auto 60px; padding: 0 16px; }
.track-card { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 28px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px; }
.track-title { font-size: 24px; font-weight: 700; color: #111; margin: 0 0 8px; }
.track-sub { font-size: 14px; color: var(--medium-gray); margin: 0 0 20px; }
.track-form-grid { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; }
.track-field label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; }
.track-field input { width: 100%; padding: 10px 14px; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.15s; }
.track-field input:focus { border-color: var(--amazon-orange); box-shadow: 0 0 0 3px rgba(255,153,0,0.2); }
.btn-track { padding: 10px 24px; background: #ffd814; border: 1px solid #fcd200; border-radius: 6px; font-weight: 700; font-size: 14px; color: #111; cursor: pointer; height: 42px; display: inline-flex; align-items: center; gap: 6px; }
.btn-track:hover { background: #f7ca00; }

.track-result-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 16px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.track-code { font-size: 20px; font-weight: 700; color: #111; }
.status-pill { display: inline-block; padding: 4px 14px; border-radius: 100px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.status-placed    { background: #fef9c3; color: #854d0e; }
.status-confirmed { background: #e0f2fe; color: #0369a1; }
.status-processing{ background: #ede9fe; color: #5b21b6; }
.status-packed    { background: #e0e7ff; color: #3730a3; }
.status-shipped   { background: #dbeafe; color: #1e40af; }
.status-out_for_delivery { background: #ffedd5; color: #9a3412; }
.status-delivered { background: #dcfce7; color: #15803d; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-returned  { background: #f1f5f9; color: #475569; }

/* Visual Order Progress Pipeline */
.pipeline { display: grid; grid-template-columns: repeat(6, 1fr); margin: 30px 0 20px; position: relative; }
.pipeline::before { content: ''; position: absolute; top: 16px; left: 8%; right: 8%; height: 4px; background: #e2e8f0; z-index: 1; }
.pipe-step { text-align: center; position: relative; z-index: 2; }
.pipe-circle { width: 34px; height: 34px; border-radius: 50%; background: #fff; border: 3px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; color: #64748b; margin-bottom: 8px; transition: all 0.2s; }
.pipe-step.done .pipe-circle { background: #16a34a; border-color: #16a34a; color: #fff; }
.pipe-step.current .pipe-circle { background: #ff9900; border-color: #ff9900; color: #fff; box-shadow: 0 0 0 4px rgba(255,153,0,0.25); }
.pipe-label { font-size: 11.5px; font-weight: 600; color: #64748b; line-height: 1.2; }
.pipe-step.done .pipe-label { color: #16a34a; }
.pipe-step.current .pipe-label { color: #111; font-weight: 700; }

.track-details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px; }
.track-panel { background: #f8fafc; border-radius: 6px; padding: 16px; border: 1px solid #e2e8f0; font-size: 13px; line-height: 1.6; }
.track-panel h3 { font-size: 13px; text-transform: uppercase; font-weight: 700; color: #475569; margin: 0 0 10px; display: flex; align-items: center; gap: 6px; }

@media (max-width: 768px) {
    .track-form-grid { grid-template-columns: 1fr; }
    .track-details-grid { grid-template-columns: 1fr; }
    .pipeline { grid-template-columns: repeat(3, 1fr); row-gap: 20px; }
    .pipeline::before { display: none; }
}
</style>
@endpush

@section('content')
<div class="track-wrap">
    <div class="track-card">
        <h1 class="track-title"><i class="fas fa-truck" style="color:var(--amazon-orange); margin-right:8px;"></i> Track Your Order</h1>
        <p class="track-sub">Check real-time delivery status, tracking number and timeline for your package.</p>

        <form method="GET" action="{{ route('shop.track-order') }}" class="track-form-grid">
            <div class="track-field">
                <label for="track-code">Order Number / ID <span style="color:#c7511f;">*</span></label>
                <input type="text" id="track-code" name="code" value="{{ $code }}" placeholder="e.g. NS0001" required />
            </div>
            <div class="track-field">
                <label for="track-phone">Mobile / Email <span style="font-weight:normal; color:#666;">(for verification)</span></label>
                <input type="text" id="track-phone" name="phone" value="{{ $phone }}" placeholder="10-digit mobile or email" />
            </div>
            <button type="submit" class="btn-track">
                <i class="fas fa-search"></i> Track
            </button>
        </form>

        @if(!empty($error))
            <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:6px; margin-top:16px; font-size:13.5px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exclamation-circle"></i> {{ $error }}
            </div>
        @endif
    </div>

    @if($order)
        @php
            $statuses = ['placed', 'confirmed', 'packed', 'shipped', 'out_for_delivery', 'delivered'];
            $statusLabels = [
                'placed'           => 'Order Placed',
                'confirmed'        => 'Confirmed',
                'packed'           => 'Packed',
                'shipped'          => 'Shipped',
                'out_for_delivery' => 'Out for Delivery',
                'delivered'        => 'Delivered',
            ];
            $currentStatus = $order->status;
            $currentIndex = array_search($currentStatus, $statuses);
            if ($currentIndex === false) {
                if (in_array($currentStatus, ['processing', 'accepted'])) $currentIndex = 1;
                elseif ($currentStatus === 'dispatched') $currentIndex = 3;
                else $currentIndex = 0;
            }
            $isCancelled = in_array($currentStatus, ['cancelled', 'returned']);
        @endphp

        <div class="track-card">
            <div class="track-result-head">
                <div>
                    <span class="track-code">Order #{{ $order->order_code }}</span>
                    <span style="color:var(--medium-gray); font-size:13px; margin-left:8px;">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div>
                    <span class="status-pill status-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                </div>
            </div>

            @if(!$isCancelled)
                {{-- Visual Timeline Pipeline --}}
                <div class="pipeline">
                    @foreach($statuses as $idx => $st)
                        @php
                            $stepClass = '';
                            if ($idx < $currentIndex) {
                                $stepClass = 'done';
                            } elseif ($idx === $currentIndex) {
                                $stepClass = 'current';
                            }
                        @endphp
                        <div class="pipe-step {{ $stepClass }}">
                            <div class="pipe-circle">
                                @if($idx < $currentIndex)
                                    <i class="fas fa-check"></i>
                                @elseif($idx === $currentIndex)
                                    <i class="fas fa-box"></i>
                                @else
                                    {{ $idx + 1 }}
                                @endif
                            </div>
                            <div class="pipe-label">{{ $statusLabels[$st] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:6px; margin:16px 0; font-size:14px;">
                    <strong>Order Status:</strong> This order has been {{ $currentStatus }}.
                    @if($order->cancelled_reason)
                        <div style="margin-top:4px;">Reason: {{ $order->cancelled_reason }}</div>
                    @endif
                </div>
            @endif

            {{-- Tracking info if dispatched --}}
            @if($order->tracking_number || $order->dispatched_via)
                <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:6px; padding:14px 18px; margin:20px 0; color:#0369a1; font-size:13.5px;">
                    <i class="fas fa-shipping-fast" style="font-size:16px; margin-right:6px;"></i>
                    <strong>Courier:</strong> {{ $order->dispatched_via ?: 'Courier Express' }}
                    @if($order->tracking_number)
                        &nbsp;|&nbsp; <strong>Tracking Number:</strong> <code>{{ $order->tracking_number }}</code>
                    @endif
                    @if($order->dispatched_at)
                        &nbsp;|&nbsp; <span>Dispatched on {{ $order->dispatched_at->format('d M Y') }}</span>
                    @endif
                </div>
            @endif

            <div class="track-details-grid">
                {{-- Items --}}
                <div class="track-panel">
                    <h3><i class="fas fa-boxes"></i> Ordered Items</h3>
                    @foreach($order->items as $item)
                        <div style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #e2e8f0;">
                            <div>
                                <strong>{{ $item->full_description }}</strong> × {{ $item->quantity }}
                            </div>
                            <div>
                                ₹{{ number_format($item->line_total, 2) }}
                            </div>
                        </div>
                    @endforeach
                    <div style="display:flex; justify-content:space-between; margin-top:10px; font-weight:700; font-size:14px; color:#111;">
                        <span>Grand Total</span>
                        <span style="color:#c7511f;">₹{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                {{-- Delivery Destination --}}
                <div class="track-panel">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Destination</h3>
                    <p style="margin:0 0 6px;"><strong>{{ $order->addr_full_name }}</strong> ({{ ucfirst($order->addr_type) }})</p>
                    <p style="margin:0 0 6px; color:#555;">{{ $order->addr_line_1 }}@if($order->addr_line_2), {{ $order->addr_line_2 }}@endif</p>
                    <p style="margin:0 0 6px; color:#555;">{{ $order->addr_city }}, {{ $order->addr_state }} — {{ $order->addr_pincode }}</p>
                    <p style="margin:0; color:#555;"><i class="fas fa-phone"></i> {{ $order->addr_mobile_primary }}</p>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
                @php
                    $waService = app(\App\Services\WhatsAppService::class);
                    $waUrl = $waService->getClickableShareUrl($order);
                @endphp
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" style="padding:9px 18px; background:#25d366; color:#fff; border-radius:100px; font-size:13px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fab fa-whatsapp"></i> Share on WhatsApp
                </a>
                @if(Auth::guard('customer')->check() && Auth::guard('customer')->id() === $order->customer_id)
                    <a href="{{ route('shop.orders.invoice', ['order' => $order->order_code]) }}" style="padding:9px 18px; background:#fff; border:1px solid #d5d9d9; border-radius:100px; font-size:13px; font-weight:600; text-decoration:none; color:#111; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fas fa-file-pdf" style="color:#c7511f;"></i> Download Invoice
                    </a>
                    <a href="{{ route('shop.orders.show', ['order' => $order->order_code]) }}" style="padding:9px 18px; background:#fff; border:1px solid #d5d9d9; border-radius:100px; font-size:13px; font-weight:600; text-decoration:none; color:#111;">
                        View Full Order Details
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
