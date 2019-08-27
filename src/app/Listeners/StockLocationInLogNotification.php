<?php

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
                        ->setRemark($option['remark']??0)
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->setOrderSn($option['order_sn']??'')
                        ->log();


        app("log")->info("入库事件日志");
    }
}
