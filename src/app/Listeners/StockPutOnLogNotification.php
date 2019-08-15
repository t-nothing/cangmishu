<?php

namespace App\Listeners;

use App\Events\StockPutOn;
use App\Models\ProductStockLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockPutOnLogNotification
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
    public function handle(StockPutOn $event)
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
