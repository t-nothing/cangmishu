<?php

namespace App\Listeners;

use App\Events\StockLocationAdjust;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
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
                        ->setRemark($option['remark']??0)
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->log();
    }
}