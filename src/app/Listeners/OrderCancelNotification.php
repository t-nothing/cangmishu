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
            $user = ShopUser::find($order["shop_user_id"]);
            if($user) {

                $shop = Shop::find($order["shop_id"]);

                app('log')->info('开始给用户推送取消订单消息', [$order["out_sn"], $order["shop_user_id"]]);
                $app = app('wechat.mini_program');

                $service = $app->customer_service;
          
                
                $result = [];
                try
                {
                    $result = $app->template_message->send([
                        'touser' => $user->weapp_openid,
                        'template_id' => 'Dx0qg4KbZyJTRMB7vRySptulHhPTGtByxqF8yjx6sgw',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'data' => [
                            'character_string1' => $order['out_sn'],
                            'thing2' => $order['receiver_fullname'],
                            'thing4' => "后台取消订单"
                        ],
                    ]);

                }
                catch(InvalidArgumentException $ex)
                {
                    app('log')->info('发送结内容', [
                        'touser' => $user->weapp_openid,
                        'template_id' => 'Dx0qg4KbZyJTRMB7vRySptulHhPTGtByxqF8yjx6sgw',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => $formId,
                        'data' => [
                            'character_string1' => $order['out_sn'],
                            'thing2' => $order['receiver_fullname'],
                            'thing4' => "后台取消订单"
                        ],
                    ]);
                    app('log')->info('发送结果失败', [$ex->getMessage()]);
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
