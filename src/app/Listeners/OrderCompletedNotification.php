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

use App\Events\OrderCompleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Shop;
use App\Models\ShopUser;
use App\Models\ShopWeappFormId;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;

class OrderCompletedNotification
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
     * @param  OrderCompleted  $event
     * @return void
     */
    public function handle(OrderCompleted $event)
    {
        $order = $event->order;

        if($order["shop_user_id"] > 0) {
            $user = ShopUser::find($order["shop_user_id"]);
            if($user) {

                $shop = Shop::find($order["shop_id"]);

                app('log')->info('开始给用户推送签收订单消息', [$order["out_sn"], $order["shop_user_id"]]);
                $app = app('wechat.mini_program');

                $formId = ShopWeappFormId::getOne($user->id);
                if(empty($formId)) return ;
                
                $result = [];

                try
                {
                    $result = $app->template_message->send([
                        'touser' => $user->weapp_openid,
                        'template_id' => 'zz1dcq7ybDrsj2hZgGY6YSuXLzJh4Q51d5eczxN3078',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => $formId,
                        'data' => [
                            'keyword1' => $order['out_sn'],
                            'keyword2' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                            'keyword3' => $order['receiver_fullname'],
                            'keyword4' => app('ship')->getExpressName($order['express_code']),
                            'keyword5' => $order['express_num'],
                            'keyword6' => '您好，你购买的宝贝已经签收, 感谢您使用订货老司机小程序!',
                        ],
                    ]);

                }
                catch(InvalidArgumentException $ex)
                {
                    app('log')->info('发送结内容', [
                        'touser' => $user->weapp_openid,
                        'template_id' => 'zz1dcq7ybDrsj2hZgGY6YSuXLzJh4Q51d5eczxN3078',
                        'page' => '/pages/center/center?shop='.$order['shop_id'],
                        'form_id' => $formId,
                        'data' => [
                            'keyword1' => $order['out_sn'],
                            'keyword2' => $order['order_items'][0]['name_cn']??$shop->name_cn,
                            'keyword3' => $order['receiver_fullname'],
                            'keyword4' => app('ship')->getExpressName($order['express_code']),
                            'keyword5' => $order['express_num'],
                            'keyword6' => '您好，你购买的宝贝已经签收, 感谢您使用订货老司机小程序!',
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
