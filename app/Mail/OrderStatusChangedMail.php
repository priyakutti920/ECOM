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

class OrderStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
        public ?string $note = null
    ) {}

    public function envelope(): Envelope
    {
        $storeName = StoreSetting::getStoreName();
        return new Envelope(
            subject: "Update on your order #{$this->order->order_code} — " . ucfirst($this->newStatus),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_status_changed',
            with: [
                'order'     => $this->order,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'note'      => $this->note,
                'storeName' => StoreSetting::getStoreName(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
