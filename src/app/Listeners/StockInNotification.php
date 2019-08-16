<?php

namespace App\Listeners;

use App\Events\StockIn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockInNotification
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
     * 入库事件
     * 总库存增加, 未上架库存增加
     * @param  StockIn  $event
     * @return void
     */
    public function handle(StockIn $event)
    {
        
        $model = $event->stock->load("spec.product");
        
        $model->increment('stockin_num', $event->qty);
        $model->increment('floor_num', $event->qty);

        $model->spec->increment('total_stockin_num', $event->qty);
        $model->spec->increment('total_floor_num', $event->qty);
        $model->spec->increment('total_stockin_times', 1);

        $model->spec->product->increment('total_stockin_num', $event->qty);
        $model->spec->product->increment('total_floor_num', $event->qty);
        $model->spec->product->increment('total_stockin_times', 1);



        app("log")->info("入库事件");
        
    }
}
