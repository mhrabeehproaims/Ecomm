<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sentOrderDespatchedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $name;
    public $orderItems;
    public $del_date;
    public $total_amount;
    public $invoice;
    public function __construct($order, $pdfPath)
    {
        $this->name = $order->customer->first_name;
        $this->orderItems = $order->items;
        $this->del_date = date('d-M-Y', strtotime($order->delivery_date));
        $this->total_amount = $order->total_amount;
        $this->invoice = $pdfPath;

        // info($order->first_name);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Order Has Despatched',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'Mail.order_despatchedMail',
            with: ([
                'name' => $this->name,
                'cartItems' => $this->orderItems,
                'delivery_date' => $this->del_date,
                'total_amount' => $this->total_amount,
                // 'invoice' => $this->invoice //old
                'invoice' => asset('storage/' . $this->invoice)
            ])
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $filePath = storage_path('app/public/Invoices/' . $this->invoice);
        // $filePath = asset('storage/Invoices/' . $this->invoice);

        if (file_exists($filePath)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromPath($filePath)
                    ->as($this->invoice)
                    ->withMime('application/pdf'),
            ];
        }

        return []; // return empty if file not found
    }
}
