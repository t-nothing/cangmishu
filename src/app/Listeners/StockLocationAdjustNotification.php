<?php

namespace App\Listeners;

use App\Events\StockLocationAdjust;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationAdjustNotification
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
     * 库存盘点
     * 上架的货位库存发生变化，总库存发生变化
     * @param  StockLocationAdjust  $event
     * @return void
     */
    public function handle(StockLocationAdjust $event)
    {
        /**
         * $event->qty;
         * 等于最终数量
         */
        // $model = $event->stock->load("spec.product");
        $model = $event->stock;


        $stockLocation = $event->stockLocation;

        //$event->qty 这个是最终库存！！！
        //如果是最终数量 大于 上架库存
        //如货位上面有10个，盘成8个，货位就减2
        //如货位上面有8个，盘成10个，货位就减2
        // $diff_num = $event->qty - $model->shelf_num;
        $model->increment('recount_times',1);

        $stockLocation->shelf_num = $event->qty;
        $stockLocation->save();

        $model->stock_num = $event->qty;
        $model->shelf_num = $event->qty;
        $model->save();

        $model->spec->total_shelf_num = $event->qty;
        $model->spec->total_stock_num = $event->qty;
        $model->spec->save();

        $model->spec->product->total_shelf_num = $event->qty;
        $model->spec->product->total_stock_num = $event->qty;
        $model->spec->product->save();
    }
}
