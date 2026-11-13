<?php

namespace App\Listeners;

use App\Events\OrderPlaceEvent;
use App\Mail\OrderPlacedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class sendOrderPlacedMail
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
    public function handle(OrderPlaceEvent $event): void
    {
        Mail::to($event->email)->send(new OrderPlacedMail($event->fullName, $event->newOrder, $event->cartItems, $event->discountedTotal));
    }
}
