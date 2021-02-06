<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Listeners;

use App\Events\StockLocationOut;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ProductStockLog;

class StockLocationOutLogNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  StockOut  $event
     * @return void
     */
    public function handle(StockLocationOut $event)
    {
        $model = $event->stockLocation;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_OUTPUT)
                        ->setStockLocation($model)
                        ->setRemark($option['remark']??'')
                        ->setItemId($option['item_id']??0)
                        ->setNum(abs($qty) * -1)
                        ->setOrderSn($option['order_sn']??'')
                        ->log();
        app("log")->info("出库事件日志");

    }
}
