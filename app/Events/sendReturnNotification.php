<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class sendReturnNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $cus_name;
    public $order_id;
    public $subtotal;
    public $item_id;
    public $return;

    public function __construct($data)
    {
        $this->return = $data['return'];
        $this->cus_name = $data['customer'];
        $this->order_id = $data['order_id'];
        $this->subtotal = $data['subtotal'];
        $this->item_id = $data['item_id'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sales-return'),
        ];
    }
    public function broadcastAs()
    {
        return 'sales-return-event'; // <-- event name
    }
}
