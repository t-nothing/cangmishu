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

use App\Events\StockLocationAdjust;
use App\Models\ProductStockLog;

class StockLocationAdjustLogNotification
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
     * @param  StockLocationAdjust  $event
     * @return void
     */
    public function handle(StockLocationAdjust $event)
    {
        $model = $event->stockLocation;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_COUNT)
                        ->setStockLocation($model)
                        ->setRemark($option['remark']??'')
                        ->setItemId($option['item_id']??0)
                        ->setNum($option['diff']) // - ($option['origin_stock_location_shelf_num']??0)
                        ->setOrderSn($option['order_sn']??'')
                        ->log();
    }
}
