<?php

namespace App\Listeners;

use App\Events\orderShippedEvent;
use App\Mail\sentOrderDespatchedMail;
use Illuminate\Support\Facades\Mail;

class orderShippedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(orderShippedEvent $event): void
    {
        $saved = \Illuminate\Support\Facades\Schema::hasTable('storefront_checkouts') ? \Illuminate\Support\Facades\DB::table('storefront_checkouts')->where('order_id', $event->order->order_id)->first() : null;
        $snapshot = $saved ? json_decode($saved->breakdown, true) : null;
        $email = $saved ? ($snapshot['customer_snapshot']['email'] ?? null) : $event->order->customer->email;
        if (! $email) {
            return;
        }
        Mail::to($email)->send(new sentOrderDespatchedMail($event->order, $event->invoicePath));
    }
}
