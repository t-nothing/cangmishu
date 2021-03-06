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

use App\Events\StockLocationAdjust;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\InventoryWarningMail as Mailable;
use App\Models\User;
use App\Models\Warehouse;
use Mail;

class StockLocationAdjustWarningNotification implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'cangmishu_emails';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 20;
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
     * Handle the event.
     *
     * @param  StockLocationAdjust  $event
     * @return void
     */
    public function handle(StockLocationAdjust $event)
    {
        $model = $event->stock->load("spec.product.category");
        app('log')->info('库存调整开始检查库存预警条件');
        app('log')->info('当前规格总库存和预警值为:', [
            $model->spec->total_stock_num,
            $model->spec->product->category->warning_stock,
            $model->spec->product->warehouse_id,
        ]);
        if($model->spec->product->category->warning_stock > 0) {

            if($model->spec->total_stock_num <= $model->spec->product->category->warning_stock )
            {

                $user = User::find($model->spec->product->owner_id);

                $warehouseInfo = Warehouse::find($model->spec->product->warehouse_id);
                if(!$warehouseInfo) {
                    app('log')->error('找不到仓库', ['id'=> $model->spec->product->warehouse_id]);
                    return false;
                }

                $warning_email = $warehouseInfo->warning_email;

                if($user) {
                    if($warning_email) {
                        $product_name = $model->spec->product->name_cn.'规格'.$model->spec->name_cn;
                        app('log')->info('准备发送邮件给', ['name'=> $product_name, 'email'=>$warning_email]);
                        $message = new Mailable($warning_email, $user->nick_name, $product_name, $model->spec->total_stock_num);
                        Mail::send($message);
                    }
                }
            }
        }
    }
}
