<?php

namespace App\Listeners;

use App\Events\StockIn;
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
     * @param  StockIn  $event
     * @return void
     */
    public function handle(StockIn $event)
    {
        $model = $event->stock;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_IN)
                        ->setStock($model)
                        ->setRemark($option['remark']??0)
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->log();


        app("log")->info("入库事件日志");
    }
}
