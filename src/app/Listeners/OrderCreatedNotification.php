<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ShopUser;
use App\Models\Shop;
use EasyWeChat\Kernel\Messages\Text;
use App\Models\ShopWeappFormId;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;


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

                $shop = Shop::find($order["shop_id"]);

                app('log')->info('开始给用户推送创建订单消息', [$order["out_sn"], $order["shop_user_id"], $shop->name_cn, $user->toArray()]);
                $app = app('wechat.mini_program');
                $result = [];
                try
                {
                    $result = $app->template_message->send([
                        'touser' => $user->weapp_openid,
                        'template_id' => '9c0AODtEy_PzrusA53EcQpE2B3utlQHO9TJQjsNTT_o',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => ShopWeappFormId::getOne($user->id),
                        'data' => [
                            'keyword1' => $order['sub_total'],
                            'keyword2' => $order['out_sn'],
                            'keyword3' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                            'keyword4' => $order['status_name']??'已支付',
                        ],
                    ]);
                }
                catch(InvalidArgumentException $ex)
                {
                    app('log')->info('发送结内容', [
                        'touser' => $user->weapp_openid,
                        'template_id' => '9c0AODtEy_PzrusA53EcQpE2B3utlQHO9TJQjsNTT_o',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => ShopWeappFormId::getOne($user->id),
                        'data' => [
                            'keyword1' => $order['sub_total'],
                            'keyword2' => $order['out_sn'],
                            'keyword3' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                            'keyword4' => $order['status_name']??'已支付',
                        ],
                    ]);
                    app('log')->info('发送结果失败', [$ex->getMessage()]);
                }

                

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
