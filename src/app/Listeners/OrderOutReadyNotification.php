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

use App\Events\OrderOutReady;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ShopUser;
use App\Models\Shop;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

class OrderOutReadyNotification implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'cangmishu_push';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;

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
     * @param  OrderOutReady  $event
     * @return void
     */
    public function handle(OrderOutReady $event)
    {
        $order = $event->order;
        return false;//先不用处理
        if($order["shop_user_id"] > 0) {

            $user = ShopUser::find($order["shop_user_id"]);
            if($user) {

                app('log')->info('开始给用户推送准备出库的订单消息', [$order["out_sn"], $order["shop_user_id"]]);

                $app = app('wechat.mini_program');

                $service = $app->customer_service;

                $text = new Text(sprintf("%s 您好, 您的订单 %s已经准备完毕, 下一步我们将为您准备发货, 请您耐心等待",$order["receiver_fullname"], $order["out_sn"]));

                

                $result = $app->customer_service->message($text)->to($user->weapp_openid)->send();

                app('log')->info('发送结果', $result);


            }
        }
    }

    /**
     * 处理失败任务。
     *
     * @param  \App\Events\OrderShipped  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(OrderOutReady $event, $exception)
    {
        //
    }
}
