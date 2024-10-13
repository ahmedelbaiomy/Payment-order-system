<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $status;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($order, $status,$message='')
    {
        $this->order = $order;
        $this->status = $status;
        $this->message = $message;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('payment-status-event'),
        ];
    }
    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->status,
            'message' => $this->message
        ];
    }
}
