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

use App\Events\StockLocationAdjust;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Concerns\ThirdPartyPush;

class StockLocationAdjustWarningPushThirdParty implements ShouldQueue
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
     * @param  StockLocationAdjust  $event
     * @return void
     */
    public function handle(StockLocationAdjust $event)
    {
        $model = $event->stock->load("spec");
        app('log')->info('库存调整-通知第三方');
        app('log')->info('当前规格总库存和预警值为:', [
            $model->spec->total_stock_num,
        ]);

        $this->askPost(
            [
                'sku'   => $model->spec->sku,
                'qty'   => $model->spec->total_stock_num
            ],
            $model->spec->warehouse_id,
            'stockChange'
        );
    }
}
