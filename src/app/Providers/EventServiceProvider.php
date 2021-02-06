<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\WechatScanLogined' => [
                'App\Listeners\WechatScanLoginedNotification',
            ],
        'App\Events\StockLocationIn' => [
                'App\Listeners\StockLocationInNotification',
                'App\Listeners\StockLocationInLogNotification',
            ],
        'App\Events\StockLocationPutOn' => [
                'App\Listeners\StockLocationPutOnNotification',
                'App\Listeners\StockLocationPutOnLogNotification',
            ],
        'App\Events\StockLocationPick' => [
                'App\Listeners\StockLocationPickNotification',
                'App\Listeners\StockLocationPickLogNotification',
            ],
        'App\Events\StockLocationOut' => [
                'App\Listeners\StockLocationOutNotification',
                'App\Listeners\StockLocationOutLogNotification',
                'App\Listeners\StockLocationOutWarningNotification',
                'App\Listeners\StockLocationOutWarningPushThirdParty',
            ],
        'App\Events\StockLocationAdjust' => [
                'App\Listeners\StockLocationAdjustNotification',
                'App\Listeners\StockLocationAdjustLogNotification',
                'App\Listeners\StockLocationAdjustWarningNotification',
                'App\Listeners\StockLocationAdjustWarningPushThirdParty',
            ],
        'App\Events\StockLocationMove' => [
                'App\Listeners\StockLocationMoveNotification',
                'App\Listeners\StockLocationMoveLogNotification',
            ],
        'App\Events\OrderCancel' => [ //订单取消
                'App\Listeners\OrderCancelNotification',
                'App\Listeners\OrderCancelPushThirdParty',
            ],
        'App\Events\OrderCreated' => [ //更新了快递单号
                'App\Listeners\OrderCreatedNotification',
            ],
        'App\Events\OrderPaid' => [ //更新了快递单号
                'App\Listeners\OrderPaidNotification',
            ],
        'App\Events\OrderShipped' => [ //更新了快递单号
                'App\Listeners\OrderShippedNotification',
                'App\Listeners\OrderShippedPushThirdParty',
            ],
        'App\Events\OrderCompleted' => [ //订单完成
                'App\Listeners\OrderCompletedNotification',
            ],
        'App\Events\OrderOutReady' => [ //拣货完成，准备出库
                'App\Listeners\OrderOutReadyNotification',
            ],
        'App\Events\CartAdded' => [ 
                'App\Listeners\CartAddedNotification',
            ],
        'App\Events\CartAdding' => [ 
                'App\Listeners\CartAddingNotification',
            ],
        'App\Events\CartUpdating' => [ 
                'App\Listeners\CartUpdatingNotification',
            ],
        'App\Events\CartUpdated' => [ 
                'App\Listeners\CartUpdatedNotification',
            ],
        'App\Events\CartRemoving' => [ 
                'App\Listeners\CartRemovingNotification',
            ],
        'App\Events\CartRemoved' => [ 
                'App\Listeners\CartRemovedNotification',
            ],
        'App\Events\CartCheckouting' => [ 
                'App\Listeners\CartCheckoutingNotification',
            ],
        'App\Events\CartCheckouted' => [ 
                'App\Listeners\CartCheckoutedNotification',
            ],
        'App\Events\CartDestroying' => [ 
                'App\Listeners\CartDestroyingNotification',
            ],
        'App\Events\CartDestroyed' => [ 
                'App\Listeners\CartDestroyedNotification',
            ],
        'App\Events\AppAccountCreated' => [ 
                'App\Listeners\AppAccountCreatedNotification',
            ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

    }
}
