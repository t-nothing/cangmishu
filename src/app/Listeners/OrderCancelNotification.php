<?php

namespace App\Listeners;

use App\Events\OrderCancel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ShopUser;
use App\Models\ShopWeappFormId;

class OrderCancelNotification  implements ShouldQueue
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
     * @param  OrderCancel  $event
     * @return void
     */
    public function handle(OrderCancel $event)
    {
        $order = $event->order;

        if($order["shop_user_id"] > 0) {
            $user = ShopUser::find($order["shop_user_id"]);
            if($user) {

                app('log')->info('开始给用户推送取消订单消息', [$order["out_sn"], $order["shop_user_id"]]);
                $app = app('wechat.mini_program');

                $service = $app->customer_service;

                $result = $app->template_message->send([
                    'touser' => $user->weapp_openid,
                    'template_id' => 'TMupKMzx9wIVvxtS0j6tVzk3p6Bxniu6uvse0YhSl9U',
                    'page' => '/pages/center/center?shop='.$order['shop_id'],
                    'form_id' => ShopWeappFormId::getOne($user->id),
                    'data' => [
                        'keyword1' => $order['source'],
                        'keyword2' => $order['out_sn'],
                        'keyword3' => date("Y-m-d H:i:s", $order["created_at"]),
                        'keyword4' => $order['items'][0]['name_cn']??'仓小铺商品',
                        'keyword5' => sprintf("%s%s", $order['sale_currency'], $order['subtotal']),
                        'keyword6' => "后台取消"
                    ],
                ]);

                // $text = new Text(sprintf("%s 您好, 您的订单下单成功, 订单号为:%s", $order["receiver_fullname"], $order["out_sn"]));

                // $result = $app->customer_service->message($text)->to($user->weapp_openid)->send();

                
                app('log')->info('发送结果', $result);


            }
        } else {
            app('log')->info('不需要通知用户订单取消', [$order["out_sn"], $order["shop_user_id"]]);
        }
    }
}
