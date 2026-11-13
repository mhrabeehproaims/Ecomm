<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $name;
    public $order_number;
    public $delivered_date;
    public $delivery_address;
    public $cartItems;
    public $total_amount;
    public function __construct($order)
    {
        $this->name = $order->customer?->full_name ?? "";
        $this->order_number = $order->order_id ?? "";
        $this->delivery_address = $order->customer?->defaultDeliveryAddress ?? "";
        $this->cartItems = $order->items ?? collect();
        $this->total_amount = $order->total_amount ?? "";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Delivered',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'Mail.DeliveredMail',
            with: ([
                'name' => $this->name,
                'order_number' => $this->order_number,
                'cartItems' => $this->cartItems,
                'order' => $this->cartItems,
                'delivery_address' => $this->delivery_address,
                'total_amount' => $this->total_amount
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
        return [];
    }
}
