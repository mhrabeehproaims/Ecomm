<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
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

    public $delivery_charge;

    public $discount;

    public function __construct($order, $pdfPath)
    {
        $saved = \Illuminate\Support\Facades\Schema::hasTable('storefront_checkouts') ? \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('order_id', $order->order_id)->first() : null;
        $snapshot = $saved ? json_decode($saved->breakdown, true) : null;
        $this->name = $saved ? ($snapshot['customer_snapshot']['first_name'] ?? 'Customer') : $order->customer->first_name;
        $this->orderItems = $saved ? collect($snapshot['items'])->map(fn ($line) => (object) [
            'product' => (object) ['product_name' => $line['name']], 'quantity' => $line['quantity'], 'price' => $line['unit_minor'] / 100,
        ]) : $order->items;
        $this->del_date = $order->delivery_date ? date('d-M-Y', strtotime($order->delivery_date)) : null;
        $this->total_amount = $saved ? $snapshot['total_minor'] / 100 : $order->total_amount;
        $this->delivery_charge = $saved ? $snapshot['shipping_minor'] / 100 : $order->delivery_charge;
        $this->discount = $saved ? $snapshot['discount_minor'] / 100 : $order->discount;
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
                'delivery_charge' => $this->delivery_charge,
                'discount' => $this->discount,
                'invoice' => $this->invoice,
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
        $invoice = \App\Models\Invoice::where('invoice_number', pathinfo(basename(parse_url($this->invoice, PHP_URL_PATH)), PATHINFO_FILENAME))->first();
        if ($invoice) {
            try {
                $filePath = app(\App\Services\Storefront\OrderDocuments::class)->file($invoice, 'invoice');

                return [\Illuminate\Mail\Mailables\Attachment::fromPath($filePath)->as($invoice->invoice_number.'.pdf')->withMime('application/pdf')];
            } catch (\Throwable $error) {
                \Illuminate\Support\Facades\Log::warning('Shipment invoice attachment unavailable', ['order_id' => $invoice->order_id]);
            }
        }

        return []; // return empty if file not found
    }
}
