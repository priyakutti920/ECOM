@component('mail::message')
# Order confirmed — {{ $order->order_code }}

Hi {{ $order->contact_name ?: ($order->customer->name ?? 'there') }},

Thank you for shopping with {{ $storeName }}. Your payment of **₹{{ number_format($order->total, 2) }}** has been received and your order is now being processed.

A copy of your invoice is attached to this email as a PDF.

@component('mail::panel')
**Order:** {{ $order->order_code }}
**Total paid:** ₹{{ number_format($order->total, 2) }}
**Payment method:** {{ ucfirst($order->payment_method) }}@if($order->payment_utr) (UTR: {{ $order->payment_utr }})@endif
**Placed on:** {{ $order->created_at->format('d M Y, h:i A') }}
@endcomponent

You can also view and track your order anytime from your account:
@php
    $url = route('shop.orders.show', ['order' => $order->order_code]);
@endphp
@component('mail::button', ['url' => $url, 'color' => 'primary'])
View order
@endcomponent

If you have any questions, just reply to this email or contact us on WhatsApp.

Thanks,
**{{ $storeName }}**
@endcomponent
