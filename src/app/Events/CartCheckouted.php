<?php

namespace App\Events;

use Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CartCheckouted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 有关配送状态更新的信息。
     *
     * @var Order
     */
    public $order;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * 获取事件应该广播的频道。
     *
     * @return array
     */
    public function broadcastOn()
    {
        return new Channel('rss');//PrivateChannel('order.'.$this->order);
    }

    /**
     * 指定广播数据。
     *
     * @return array
     */
    public function broadcastWith()
    {
        // 返回当前时间
        return ['name' => Carbon::now()->toDateTimeString()];
    }
}
