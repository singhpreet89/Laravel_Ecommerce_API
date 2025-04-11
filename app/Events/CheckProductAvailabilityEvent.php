<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CheckProductAvailabilityEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $productObject;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->productObject = $product;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     */
    public function broadcastOn() : Channel|array
    {
        return new PrivateChannel('channel-name');
    }
}
