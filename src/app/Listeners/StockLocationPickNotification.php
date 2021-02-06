<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Listeners;

use App\Events\StockLocationPick;
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
    public function handle(StockLocationPick $event)
    {

        app('log')->info('拣货事件', [
            'stock-id'=> $event->stock->id
        ]);


        $stockLocation = $event->stockLocation;
        $stockLocation->decrement('shelf_num', $event->qty); 
        
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
