<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
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
            new PrivateChannel('sales-return'),
        ];
    }

    public function broadcastAs()
    {
        return 'sales-return-event'; // <-- event name
    }

    public function broadcastWith(): array
    {
        return ['cus_name' => $this->cus_name, 'order_id' => $this->order_id, 'subtotal' => $this->subtotal, 'item_id' => $this->item_id,
            'return' => ['return_id' => $this->return->return_id, 'total_amount' => $this->return->total_amount]];
    }
}
