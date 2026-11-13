<?php

namespace App\Listeners;

use App\Events\NotifyOrderPlaced;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SaveOrderNotification
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
    public function handle(NotifyOrderPlaced $event): void
    {
        $order = $event->order_details;
        
        Notification::create([
            'type' => 'order',
            'title' => 'New Order Received',
            'message' => 'You have a new order from ' . $order->customer->full_name,
            'notifiable_type' => get_class($order),
            'notifiable_id' => $order->order_id,
            'data' => ['amount' => $order->total_amount],
        ]);
    }
}
