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

use App\Events\StockLocationPutOn;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLocationPutOnNotification
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
    public function handle(StockLocationPutOn $event)
    {

        //这里不需要给货位加库存了！！！
        // $stockLocation = $event->stockLocation;
        // $stockLocation->increment('shelf_num', $event->qty); 
        
        $model = $event->stock->load("spec.product");
        $model->increment('shelf_num', $event->qty);
        $model->decrement('floor_num', $event->qty);
        
        $model->spec->increment('total_shelf_num', $event->qty);
        $model->spec->decrement('total_floor_num', $event->qty);
        
        $model->spec->product->increment('total_shelf_num', $event->qty);
        $model->spec->product->decrement('total_floor_num', $event->qty);

        app('log')->info('上架之后事件 handle');
    }
}
