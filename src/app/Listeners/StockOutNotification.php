<?php

namespace App\Listeners;

use App\Events\StockOut;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockOutNotification
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

        
        $model->decrement('stockin_num', $event->qty);
        $model->decrement('lock_num', $event->qty);
        
        $model->spec->decrement('total_stockin_num', $event->qty);
        $model->spec->decrement('total_lock_num', $event->qty);
        $model->spec->increment('total_stockout_num', $event->qty);
        
        $model->spec->product->decrement('total_stockin_num', $event->qty);
        $model->spec->product->decrement('total_lock_num', $event->qty);
        $model->spec->product->increment('total_stockout_num', $event->qty);
    }
}
