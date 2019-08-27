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

        $diff_num = $event->qty - $stockLocation->shelf_num;

        $model->increment('recount_times',1);

        $stockLocation->shelf_num = $event->qty;
        $stockLocation->save();

        app('log')->info('库存盘点事件后,修改库存为', [
            'qty'       =>  $event->qty,
            'diff_num'  =>  $diff_num
        ]);

        //现在的值，比之前的大,库存都要增加
        if($diff_num > 0) {

            $model->increment('stock_num', $diff_num);
            $model->increment('shelf_num', $diff_num);

            $model->spec->increment('total_shelf_num',$diff_num);
            $model->spec->increment('total_stock_num',$diff_num);

            app('log')->info('商品原库存为+', [
                'total_shelf_num'       =>  $model->spec->product->total_shelf_num,
                'total_stock_num'       =>  $model->spec->product->total_stock_num,
            ]);

            $model->spec->product->increment('total_shelf_num',$diff_num);
            $model->spec->product->increment('total_stock_num',$diff_num);
        } elseif($diff_num < 0) {

            $model->decrement('stock_num', abs($diff_num));
            $model->decrement('shelf_num', abs($diff_num));

            $model->spec->decrement('total_shelf_num', abs($diff_num));
            $model->spec->decrement('total_stock_num', abs($diff_num));

            app('log')->info('商品原库存为-', [
                'total_shelf_num'       =>  $model->spec->product->total_shelf_num,
                'total_stock_num'       =>  $model->spec->product->total_stock_num,
            ]);

            $model->spec->product->decrement('total_shelf_num', abs($diff_num));
            $model->spec->product->decrement('total_stock_num', abs($diff_num));
        }

    }
}
