<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\ProductStock;

/**
 * 库存事件
 */
class BaseStockLocation
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stockLocation;
    public $stock;
    public $qty;
    public $option;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($stockLocation, int $qty, $option = NULL)
    {
        $this->stockLocation    = $stockLocation;
        // $this->stock            = ProductStock::with('spec.product')->find($stockLocation->stock->id);
        $this->stock            = $stockLocation->stock;

        $this->qty              = $qty;
        $this->option           = $option;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
