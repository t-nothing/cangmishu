<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
        //原始货位库存
        $this->option['origin_stock_location_shelf_num'] = $stockLocation->shelf_num;
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
