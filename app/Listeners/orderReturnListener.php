<?php

namespace App\Listeners;

use App\Events\sendReturnNotification;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class orderReturnListener
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
    public function handle(sendReturnNotification $event): void
    {      
        Notification::create([
            'type' => 'return',
            'title' => 'New Sales Return',
            'message' => 'You have a new sales return from ' . $event->cus_name,
            'notifiable_type' => get_class($event->return),
            'notifiable_id' => $event->order_id,
            'data' => ['item_id' => $event->item_id, 'amount' => $event->subtotal],
        ]);
    }
}
