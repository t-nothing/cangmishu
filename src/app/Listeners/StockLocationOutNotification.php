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

use App\Events\StockLocationOut;
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
     * @param  StockLocationOut  $event
     * @return void
     */
    public function handle(StockLocationOut $event)
    {
        $model = $event->stock->load("spec.product");
        $stockLocation = $event->stockLocation;
        

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
