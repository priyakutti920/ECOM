<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $currency = StoreSetting::getCurrencySymbol();
        return new Envelope(
            subject: "🚨 New Order Received: #{$this->order->order_code} ({$currency}" . number_format($this->order->total, 2) . ")",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_order_notification',
            with: [
                'order'     => $this->order,
                'storeName' => StoreSetting::getStoreName(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
