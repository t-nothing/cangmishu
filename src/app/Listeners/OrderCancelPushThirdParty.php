<?php

namespace App\Listeners;

use App\Events\OrderCancel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Concerns\ThirdPartyPush;

class OrderCancelPushThirdParty implements ShouldQueue
{
    use ThirdPartyPush;
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'cangmishu_push_third_party';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 20;
    /**
     * Create the event listener.
     *
     * @return void
     */

    /**
     * 任务可以尝试的最大次数。
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 任务可以执行的最大秒数 (超时时间)。
     *
     * @var int
     */
    public $timeout = 30;
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
        app('log')->info('取消-通知第三方', [
                'out_sn'            => $order["out_sn"],
            ]);

        $this->askPost(
            [
                'out_sn'            => $order["out_sn"],
            ],
            $order["warehouse_id"],
            'orderCancel'
        );
    }
}
