<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyOrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $cus_name;

    public $total_amount;

    public $order_details;

    public function __construct($cus_name, $total_amount, $order)
    {
        $this->cus_name = $cus_name;
        $this->total_amount = $total_amount;
        $this->order_details = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('my-channel'),
        ];
    }

    public function broadcastAs()
    {
        return 'my-event'; // <-- event name
    }

    public function broadcastWith(): array
    {
        // The existing admin toast needs names and totals, never a serialized customer record.
        return ['cus_name' => $this->cus_name, 'total_amount' => $this->total_amount,
            'order_details' => ['order_id' => $this->order_details->order_id, 'total_amount' => $this->total_amount,
                'status' => $this->order_details->status, 'payment_status' => $this->order_details->payment_status]];
    }
}
