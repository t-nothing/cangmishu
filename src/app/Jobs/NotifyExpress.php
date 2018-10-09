<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderHistory;
use GuzzleHttp\Exception\ClientException;

/**
 * 物流通知
 */
class NotifyExpress extends Job
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
                    app('log')->info('NotifyExpress - 当前状态暂时不支持推送', [
                        'order' => $this->order->toArray(),
                        'history' => $this->history->toArray(),
                    ]);
                    break;
            }
        } catch (ClientException $e) {
            app('log')->info('NotifyExpress', [
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
        app('TrackingService')->notifyPicking($this->order, $this->history);
    }

    /**
     * 打包
     */
    private function notifyPacking()
    {
        app('TrackingService')->notifyPacking($this->order, $this->history);
    }
}
