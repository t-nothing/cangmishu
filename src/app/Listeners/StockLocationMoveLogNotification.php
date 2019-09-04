<?php

namespace App\Listeners;

use App\Events\StockLocationMove;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ProductStockLog;

class StockLocationMoveLogNotification
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
     * @param  StockMove  $event
     * @return void
     */
    public function handle(StockLocationMove $event)
    {
        $model = $event->stockLocation;
        $option = $event->option;
        $qty = $event->qty;

        app("stockLog")->setTypeId(ProductStockLog::TYPE_MOVE)
                        ->setStockLocation($model)
                        ->setRemark($option['remark']??'')
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->log();
    }
}
