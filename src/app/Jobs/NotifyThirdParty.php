<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderHistory;

/**
 * 物流通知
 */
class NotifyThirdParty extends Job
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'wms2';

    protected $order;

    protected $history;

    /**
     * Create a new job insxtance.
     *
     * @return void
     */
    public function __construct(Order $order, OrderHistory $history)
    {
        $this->order = $order;
        $this->history = $history;

        app('log')->info('推送物流开始', ['order' => $this->order->toArray(), 'history' => $this->history->toArray()]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            switch ($this->history->status) {
                // 开始拣货了
                case Order::STATUS_PICKING:
                    $this->notifyPicking();
                    break;

                // 开始打包了
                case Order::STATUS_WAITING:
                    $this->notifyPacking();
                    break;

                default:
                    app('log')->info('NotifyThirdParty - 当前状态暂时不支持推送', [
                        'order' => $this->order->toArray(),
                        'history' => $this->history->toArray(),
                    ]);
                    break;
            }
        } catch (ClientException $e) {
            app('log')->info('NotifyThirdParty', [
                'order' => $this->order->toArray(), 
                'history' => $this->history->toArray(),
                'exception' => $e->getMessage(),
                'response' => (string) $e->getResponse()->getBody(),
            ]);
        }
    }

    /**
     * 拣货
     */
    private function notifyPicking()
    {
        app('MallService')->notifyPicking($this->order->out_sn);
    }

    /**
     * 打包
     */
    private function notifyPacking()
    {
        app('MallService')->notifyPacking($this->order->out_sn);
    }
}
