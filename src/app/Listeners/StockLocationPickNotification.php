<?php

namespace App\Listeners;

use App\Events\StockPick;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationPickNotification
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
     * 拣货
     * 总库存不变, 上架库存减少，锁定库存增加
     * @param  StockPick  $event
     * @return void
     */
    public function handle(StockPick $event)
    {
        $model = $event->stock->load("spec.product");
        //架子上面减少
        $model->decrement('shelf_num', $event->qty);
        $model->increment('lock_num', $event->qty);//锁定库存增加
        
        $model->spec->decrement('total_shelf_num', $event->qty);
        $model->spec->increment('total_lock_num', $event->qty);//锁定库存
        
        $model->spec->product->decrement('total_shelf_num', $event->qty);
        $model->spec->product->increment('total_lock_num', $event->qty);//锁定库存
    }
}
