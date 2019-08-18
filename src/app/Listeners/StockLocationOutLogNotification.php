<?php

namespace App\Listeners;

use App\Events\StockOut;
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
    public function handle(StockOut $event)
    {
        $model = $event->stock;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_OUTPUT)
                        ->setStock($model)
                        ->setRemark($option['remark']??0)
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->log();
        app("log")->info("出库事件日志");

    }
}
