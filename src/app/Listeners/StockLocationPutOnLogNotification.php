<?php

namespace App\Listeners;

use App\Events\StockLocationPutOn;
use App\Models\ProductStockLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationPutOnLogNotification
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
     * @param  StockPutOn  $event
     * @return void
     */
    public function handle(StockLocationPutOn $event)
    {
        $model = $event->stockLocation;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_PUTON)
                        ->setStockLocation($model)
                        ->setRemark($option['remark']??'')
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->setOrderSn($option['order_sn']??'')
                        ->log();
        app("log")->info("上架事件日志");

    }
}
