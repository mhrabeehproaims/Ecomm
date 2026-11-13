<?php

namespace App\Listeners;

use App\Events\orderShippedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\sentOrderDespatchedMail;

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
        // $pdfPath = storage_path('app/public/' . $event->invoicePath);
        // Mail::to($event->order->customer->email)->send(new sentOrderDespatchedMail($event->order, $pdfPath)); //old
        Mail::to($event->order->customer->email)->send(new sentOrderDespatchedMail($event->order, $event->invoicePath));
    }
}
