<?php

namespace App\Listeners;

use App\Events\OrderCancel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Shop;
use App\Models\ShopUser;
use App\Models\ShopWeappFormId;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

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
        app('log')->info('order result', $order);

        if($order && $order["shop_user_id"] > 0) {
            $shop = Shop::with("owner")->find($order["shop_user_id"]);
            if($shop) {

                $user = $shop->owner;

                app('log')->info('开始给用户推送取消订单消息', [$order["out_sn"], $order["shop_user_id"]]);
                $app = app('wechat.mini_program');

                $service = $app->customer_service;
                $formId = ShopWeappFormId::getOne($user->id);
                if(empty($formId)) {
                    app('log')->info('form Id不足');
                    return;
                }

                try
                {
                    $result = $app->template_message->send([
                        'touser' => $user->weapp_openid,
                        'template_id' => 'TMupKMzx9wIVvxtS0j6tVzk3p6Bxniu6uvse0YhSl9U',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => $formId,
                        'data' => [
                            'keyword1' => $order['source'],
                            'keyword2' => $order['out_sn'],
                            'keyword3' => $order["created_at"],
                            'keyword4' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                            'keyword5' => sprintf("%s%s", currency_symbol($order['sale_currency']), $order['sub_total']),
                            'keyword6' => "店铺取消"
                        ],
                    ]);

                }
                catch(InvalidArgumentException $ex)
                {
                    app('log')->info('发送结果失败', $ex->getMessage());
                }
                
                app('log')->info('发送结果', $result);


            }
        } else {
            app('log')->info('不需要通知用户订单取消', [$order["out_sn"], $order["shop_user_id"]]);
        }
    }
    /**
     * 处理失败任务。
     *
     * @param  \App\Events\OrderShipped  $event
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(OrderCancel $event, $exception)
    {
        //
    }
}
