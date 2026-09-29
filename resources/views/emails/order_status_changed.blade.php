<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Status Update</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #232f3e; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; color: #ff9900; }
        .content { padding: 28px; line-height: 1.6; }
        .status-badge { display: inline-block; padding: 6px 14px; background: #dbeafe; color: #1e40af; border-radius: 100px; font-weight: 700; font-size: 13px; text-transform: uppercase; }
        .order-info { background: #f1f5f9; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .btn { display: inline-block; background: #ff9900; color: #111827; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 700; margin-top: 16px; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #64748b; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $storeName }}</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $order->contact_name }}</strong>,</p>
            <p>Your order status has been updated to:</p>
            <p><span class="status-badge">{{ ucfirst($newStatus) }}</span></p>

            @if(!empty($note))
                <p><strong>Note from our store:</strong> <em>{{ $note }}</em></p>
            @endif

            <div class="order-info">
                <div><strong>Order Number:</strong> #{{ $order->order_code }}</div>
                <div><strong>Total Amount:</strong> ₹{{ number_format($order->total, 2) }}</div>
                <div><strong>Payment:</strong> {{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</div>
                @if($order->tracking_number)
                    <div style="margin-top:8px;"><strong>Courier / Tracking:</strong> {{ $order->dispatched_via ?: 'Courier' }} - {{ $order->tracking_number }}</div>
                @endif
            </div>

            <p>You can view your order progress and live updates at any time:</p>
            <a href="{{ url('/track-order?code=' . $order->order_code) }}" class="btn">Track Your Order</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
