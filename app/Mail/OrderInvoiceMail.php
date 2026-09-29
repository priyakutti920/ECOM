<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your invoice for order ' . $this->order->order_code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_invoice',
            with: [
                'order'   => $this->order,
                'storeName' => \App\Models\StoreSetting::getStoreName(),
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shop.orders.invoice_pdf', ['order' => $this->order])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'sans-serif',
            ])
            ->output();

        return [
            Attachment::fromData(fn () => $pdf, 'Invoice-' . $this->order->order_code . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
