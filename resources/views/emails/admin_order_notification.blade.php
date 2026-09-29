<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Order Alert</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background: #0f172a; color: #ffffff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 18px; color: #38bdf8; }
        .content { padding: 24px; line-height: 1.6; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { background: #f1f5f9; font-weight: 600; }
        .btn { display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Order Received — {{ $storeName }}</h1>
        </div>
        <div class="content">
            <p>A new order <strong>#{{ $order->order_code }}</strong> has been placed.</p>

            <div class="info-box">
                <p style="margin:4px 0;"><strong>Customer:</strong> {{ $order->contact_name }} ({{ $order->contact_mobile }})</p>
                @if($order->contact_email)
                    <p style="margin:4px 0;"><strong>Email:</strong> {{ $order->contact_email }}</p>
                @endif
                <p style="margin:4px 0;"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                <p style="margin:4px 0;"><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
                <p style="margin:4px 0;"><strong>Total Amount:</strong> ₹{{ number_format($order->total, 2) }}</p>
            </div>

            <h3>Ordered Products:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->full_description }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <a href="{{ url('/admin/orders/' . $order->order_code) }}" class="btn">View in Admin Panel</a>
        </div>
    </div>
</body>
</html>
