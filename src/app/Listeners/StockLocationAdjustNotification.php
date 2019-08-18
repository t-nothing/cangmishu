<?php

namespace App\Listeners;

use App\Events\StockAdjust;
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
     * @param  StockAdjust  $event
     * @return void
     */
    public function handle(StockAdjust $event)
    {
        /**
         * $event->qty;
         * 等于最终数量
         */
        $model = $event->stock->load("spec.product");

        //如果是最终数量 大于 上架库存
        //如货位上面有10个，盘成8个，货位就减2
        //如货位上面有8个，盘成10个，货位就减2
        $diff_num = $event->qty - $model->shelf_num;
        $model->increment('recount_times',1);
        if($diff_num > 0 ) {
            $model->decrement('stock_num', $diff_num);
            $model->decrement('shelf_num', $diff_num);
            $model->spec->decrement('total_shelf_num', $diff_num);
            $model->spec->decrement('total_stock_num', $diff_num);
            $model->spec->product->decrement('total_shelf_num', $diff_num);
            $model->spec->product->decrement('total_stock_num', $diff_num);
        } elseif($diff_num < 0 ) {
            $model->increment('stock_num', $diff_num);
            $model->increment('shelf_num', $diff_num);
            $model->spec->increment('total_shelf_num', $diff_num);
            $model->spec->increment('total_stock_num', $diff_num);
            $model->spec->product->increment('total_shelf_num', $diff_num);
            $model->spec->product->increment('total_stock_num', $diff_num);
        } 

    }
}
