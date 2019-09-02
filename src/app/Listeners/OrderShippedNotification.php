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

                $formId = ShopWeappFormId::getOne($user->id);
                if(empty($formId)) return ;
                
                $result = [];

                try
                {
                    $result = $app->template_message->send([
                        'touser' => $user->weapp_openid,
                        'template_id' => 'eRoqrc6HHi8PR8eZxFfvAjEv4T1Jo5xTih4nviuAUUI',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => $formId,
                        'data' => [
                            'keyword1' => app('ship')->getExpressName($order['express_code']),
                            'keyword2' => date("Y年m月d日"),
                            'keyword3' => date("Y年m月d日", $order['create_at']),
                            'keyword4' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                        ],
                    ]);

                }
                catch(InvalidArgumentException $ex)
                {
                    app('log')->info('发送结内容', [
                        'touser' => $user->weapp_openid,
                        'template_id' => 'eRoqrc6HHi8PR8eZxFfvAjEv4T1Jo5xTih4nviuAUUI',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => $formId,
                        'data' => [
                            'keyword1' => app('ship')->getExpressName($order['express_code']),
                            'keyword2' => date("Y年m月d日"),
                            'keyword3' => date("Y年m月d日", strtotime($order['create_at'])),
                            'keyword4' => $order['order_items'][0]['name_cn']??$shop->name_cn,
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
}
