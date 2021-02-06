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

use App\Events\StockLocationIn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ProductStockLog;

class StockLocationInLogNotification
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
     * 入库事件日志
     *
     * @param  StockLocationIn  $event
     * @return void
     */
    public function handle(StockLocationIn $event)
    {
        $model = $event->stockLocation;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_IN)
                        ->setStockLocation($model)
                        ->setRemark($option['remark']??'')
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->setOrderSn($option['order_sn']??'')
                        ->log();


        app("log")->info("入库事件日志");
    }
}
