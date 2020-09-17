<?php

namespace App\Listeners;

use App\Events\StockLocationOut;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\InventoryWarningMail as Mailable;
use App\Models\User;
use App\Models\Warehouse;
use Mail;

class StockLocationOutWarningNotification implements ShouldQueue
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
     * @param  StockLocationOut  $event
     * @return void
     */
    public function handle(StockLocationOut $event)
    {

        $model = $event->stock->load("spec.product.category");
        app('log')->info('出库开始检查库存预警条件');
        if($model->spec->product->category->warning_stock > 0) {

            app('log')->info('当前规格总库存和预警值为:', [
                $model->spec->total_stock_num,
                $model->spec->product->category->warning_stock
            ]);
            app('log')->info('开始检查库存预警条件');
            if($model->spec->total_stock_num <= $model->spec->product->category->warning_stock )
            {

                $user = User::find($model->spec->product->owner_id);

                $warehouseInfo = Warehouse::find($model->spec->product->warehouse_id);
                if(!$warehouseInfo) {
                    app('log')->error('找不到仓库', ['id'=> $model->spec->product->warehouse_id]);
                    return false;
                }

                $warning_email = $warehouseInfo->warning_email;

                app('log')->info('准备发送邮件给', ['email'=>$warning_email]);
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
