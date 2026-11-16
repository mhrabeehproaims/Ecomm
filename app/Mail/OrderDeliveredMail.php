<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
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

    public $delivery_charge;

    public $discount;

    public function __construct($order)
    {
        $this->name = $order->customer?->full_name ?? '';
        $this->order_number = $order->order_id ?? '';
        $this->delivery_address = $order->customer?->defaultDeliveryAddress ?? '';
        $this->cartItems = $order->items ?? collect();
        $this->total_amount = $order->total_amount ?? '';
        $this->delivery_charge = $order->delivery_charge ?? 0;
        $this->discount = $order->discount ?? 0;
        $saved = \Illuminate\Support\Facades\Schema::hasTable('storefront_checkouts') ? \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('order_id', $order->order_id)->first() : null;
        if ($saved) {
            $snapshot = json_decode($saved->breakdown, true);
            $this->name = trim(($snapshot['customer_snapshot']['first_name'] ?? 'Customer').' '.($snapshot['customer_snapshot']['last_name'] ?? ''));
            $this->delivery_address = (object) json_decode($saved->address_snapshot, true);
            $this->cartItems = collect($snapshot['items'])->map(fn ($line) => (object) ['product' => (object) ['product_name' => $line['name']], 'quantity' => $line['quantity'], 'price' => $line['unit_minor'] / 100]);
            $this->total_amount = $snapshot['total_minor'] / 100;
            $this->delivery_charge = $snapshot['shipping_minor'] / 100;
            $this->discount = $snapshot['discount_minor'] / 100;
        }
        $this->delivered_date = \Illuminate\Support\Facades\DB::table('order_status_logs')->where('order_id', $order->order_id)->where('status', 'delivered')->orderBy('changed_at')->value('changed_at');
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
                'total_amount' => $this->total_amount,
                'delivery_charge' => $this->delivery_charge,
                'discount' => $this->discount,
                'delivered_date' => $this->delivered_date,
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
