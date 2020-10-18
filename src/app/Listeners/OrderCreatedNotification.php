<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\ShopUser;
use App\Models\Shop;
use App\Models\User;
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

            $user = User::where("wechat_openid","!=", "")->find($order["owner_id"]);
            if($user) {

                $shop = Shop::find($order["shop_id"]);

                $mpApp = config('wechat.mini_program_cms.default');
                // app('log')->info('开始给用户推送创建订单消息', [$order["out_sn"], $order["shop_user_id"], $shop->name_cn, $user->toArray()]);
                //这里是公众号的
                $app = app('wechat.official_account');
                $result = [];
                try
                {
                    
                    $data = [
                        'template_id' => '9c0AODtEy_PzrusA53EcQpE2B3utlQHO9TJQjsNTT_o', // 所需下发的订阅模板id
                        'touser' => $user->wechat_openid,     // 接收者（用户）的 openid
                        'url' => 'https://my.cangmishu.com',
                        // 'miniprogram' => [
                        //         'appid' => $mpApp["app_id"],
                        //         'pagepath' => '/pages/order/index',
                        // ],
                        'data' => [ 
                            'first' => [
                                'value' => '您有新的订单，请尽快处理！',
                            ],
                            'keyword1' => [
                                'value' => $order['out_sn'],
                            ],
                            'keyword2' => [
                                'value' => $shop->name_cn,
                            ],
                            'keyword3' => [
                                'value' => $order['sub_total'],
                            ],
                            'keyword4' => [
                                'value' => $order['created_at'],
                            ],
                            'keyword5' => [
                                'value' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                            ],
                        ],
                    ];

                    //这里是发给服务号的
                    $result = $app->template_message->send($data);
                    app('log')->info('发送结果参数', $data);
                    app('log')->info('发送结果', $result);
                }
                catch(InvalidArgumentException $ex)
                {
                    app('log')->info('发送结果失败', [$ex->getMessage()]);
                }
            } else {
                    app('log')->info('未发送微信推送消息,未绑定公众号');
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
