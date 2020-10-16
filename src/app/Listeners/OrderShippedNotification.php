<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Shop;
use App\Models\ShopUser;
use App\Models\ShopWeappFormId;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

class OrderShippedNotification
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
    public function handle(OrderShipped $event)
    {
        $order = $event->order;

        if($order["shop_user_id"] > 0) {
            $user = ShopUser::find($order["shop_user_id"]);
            if($user) {

                $shop = Shop::find($order["shop_id"]);

                app('log')->info('开始给用户推送发货订单消息', [$order["out_sn"], $order["shop_user_id"]]);
                $app = app('wechat.mini_program');

                
                $result = [];

                try
                {
                    $result = $app->subscribe_message->send([
                        'touser' => $user->weapp_openid,
                        'template_id' => '-93hSJ-C5U7bh10LLD9Gl885G4n9nNYC3O_5UluRwsM',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'data' => [
                            'first' => [
                                'value' =>$order['order_items'][0]['name_cn']??$shop->name_cn
                            ],
                            'character_string2' => [
                                'value' => $order['out_sn']
                            ],
                            'date3' => [
                                'value' => date("Y年m月d日")
                            ],
                            'thing4' => [
                                'value' => app('ship')->getExpressName($order['express_code'])
                            ],
                        ],
                    ]);

                }
                catch(InvalidArgumentException $ex)
                {
                   
                }

                // $text = new Text(sprintf("%s 您好, 您的订单下单成功, 订单号为:%s", $order["receiver_fullname"], $order["out_sn"]));

                // $result = $app->customer_service->message($text)->to($user->weapp_openid)->send();

                
                app('log')->info('发送结果', $result);


            }
        }
    }
}
