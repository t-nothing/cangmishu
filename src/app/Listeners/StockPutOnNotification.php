<?php

namespace App\Listeners;

use App\Events\StockPutOn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockPutOnNotification
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
     * 上架之后事件
     * 总库存不变, 未上架库存减少，上架库存增加
     * @param  StockPutOn  $event
     * @return void
     */
    public function handle(StockPutOn $event)
    {
        $model = $event->stock->load("spec.product");
        
        $model->increment('shelf_num', $event->qty);
        $model->decrement('floor_num', $event->qty);
        
        $model->spec->increment('total_shelf_num', $event->qty);
        $model->spec->decrement('total_floor_num', $event->qty);
        
        $model->spec->product->increment('total_shelf_num', $event->qty);
        $model->spec->product->decrement('total_floor_num', $event->qty);
    }
}
