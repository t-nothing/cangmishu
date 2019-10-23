<?php

namespace App\Listeners;

use App\Events\StockLocationIn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationInNotification
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
    public function handle(StockLocationIn $event)
    {
        $stockLocation = $event->stockLocation;
        $model = $event->stock->load("spec.product");

        // $model->increment('stockin_num', $event->qty); //总入库数量
        // $model->increment('stock_num', $event->qty); //库存数量
        // $model->increment('floor_num', $event->qty);

        //库存可用数量
        $model->spec->increment('total_stock_num', $event->qty);
        //入库数量
        $model->spec->increment('total_stockin_num', $event->qty);

        $model->spec->increment('total_floor_num', $event->qty);
        $model->spec->increment('total_stockin_times', 1);


        $model->spec->product->increment('total_stock_num', $event->qty); #可用库存
        $model->spec->product->increment('total_stockin_num', $event->qty); //总入库数量

        $model->spec->product->increment('total_floor_num', $event->qty);
        $model->spec->product->increment('total_stockin_times', 1);



        app("log")->info("入库事件");
        
    }
}
