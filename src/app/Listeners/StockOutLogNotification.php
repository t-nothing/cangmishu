<?php

namespace App\Listeners;

use App\Events\StockOut;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockOutLogNotification
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

        app("stockLog")->setTypeId(ProductStockLog::TYPE_PUTON)
                        ->setStock($model)
                        ->setRemark($option['remark']??0)
                        ->setItemId($option['item_id']??0)
                        ->setNum($qty)
                        ->log();
    }
}
