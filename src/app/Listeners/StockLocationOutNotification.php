<?php

namespace App\Listeners;

use App\Events\StockOut;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationOutNotification
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
     * 出库之后
     * 总库存减少，锁定库存减少
     * @param  StockOut  $event
     * @return void
     */
    public function handle(StockOut $event)
    {
        $model = $event->stock->load("spec.product");

        //库存减少
        $model->decrement('stock_num', $event->qty);
        //出库库存增加
        $model->increment('stockout_num', $event->qty);
        $model->decrement('lock_num', $event->qty);

        //规格减总库存
        $model->spec->decrement('total_stock_num', $event->qty);
        $model->spec->decrement('total_lock_num', $event->qty);
        $model->spec->increment('total_stockout_num', $event->qty);
        $model->spec->increment('total_stockout_times', 1);
        
        $model->spec->product->decrement('total_stock_num', $event->qty);
        $model->spec->product->decrement('total_lock_num', $event->qty);
        $model->spec->product->increment('total_stockout_num', $event->qty);
        $model->spec->product->increment('total_stockout_times', 1);
    }
}
