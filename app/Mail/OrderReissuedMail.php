<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderReissuedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $customer_name;
    public $item;
    public $quantity;
    public function __construct($newOrder, $item)
    {
        $this->customer_name = $newOrder->customer->first_name . ' ' . $newOrder->customer->last_name;
        $this->item =  $item->product->product_name;
        $this->quantity =  $item->quantity .' '. $item->product->unit?->unit_name ?? "";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Order Has Been Reissued',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'Mail.OrderReissuedMail',
            with: [
                'customer_name' => $this->customer_name,
                'item' => $this->item,
                'quantity' => $this->quantity
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
