<?php

namespace App\Listeners;

use App\Events\OrderOutReady;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ShopUser;
use EasyWeChat\Kernel\Messages\Text;


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
    public $delay = 60;

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

        if($order->shop_user_id > 0) {
            $user = ShopUser::find($order->shop_user_id);
            if($user) {

                $app = app('wechat.mini_program');

                $service = $app->customer_service;

                $text = new Text(sprintf("%s 您好, 您的订单 %s已经准备完毕, 下一步我们将为您准备发货, 请您耐心等待", $order->receiver_fullname, $order->out_sn));

                $result = $app->customer_service->message($text)->to($user->weapp_openid)->send();


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
    public function failed(OrderShipped $event, $exception)
    {
        //
    }
}
