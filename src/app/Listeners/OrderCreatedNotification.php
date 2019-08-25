<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ShopUser;
use EasyWeChat\Kernel\Messages\Text;
use App\Models\ShopWeappFormId;


class OrderCreatedNotification  implements ShouldQueue
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
    public function handle(OrderCreated $event)
    {
        $order = $event->order;

        if($order["shop_user_id"] > 0) {
            $user = ShopUser::find($order["shop_user_id"]);
            if($user) {

                app('log')->info('开始给用户推送创建订单消息', [$order["out_sn"], $order["shop_user_id"]]);
                $app = app('wechat.mini_program');

                $service = $app->customer_service;

                $result = $app->template_message->send([
                    'touser' => $user->weapp_openid,
                    'template_id' => 'PuDzHjSss8ID5KgDzuQPo-LE90yJwv99czWNtnkiUKY',
                    'page' => '/pages/center/center?shop='.$order['shop_id'],
                    'form_id' => ShopWeappFormId::getOne($user->id),
                    'data' => [
                        'keyword1' => $order['sub_total'],
                        'keyword2' => $order['out_sn'],
                        'keyword3' => $order['items'][0]['name_cn']??'仓小铺商品',
                        'keyword4' => $order['status_name']??'已支付',
                    ],
                ]);

                // $text = new Text(sprintf("%s 您好, 您的订单下单成功, 订单号为:%s", $order["receiver_fullname"], $order["out_sn"]));

                // $result = $app->customer_service->message($text)->to($user->weapp_openid)->send();

                
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
    public function failed(OrderCreated $event, $exception)
    {
        //
    }
}
