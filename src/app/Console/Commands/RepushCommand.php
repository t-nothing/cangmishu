<?php
/**
 * 重新推送物流
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Jobs\NotifyThirdParty;

class RepushCommand extends Command
{
    protected $signature = 'RepushCommand {nums}';

    protected $description = '重新推送物流给商城';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $nums = $this->argument('nums');

        $out_sns = explode(',', $nums);

        if ($out_sns) {
            foreach ($out_sns as $out_sn) {
                if ($order = Order::where('out_sn', $out_sn)->first()) {
                    $this->repush($order);
                }
            }
        }
    }

    // 重新推送
    public function repush($order)
    {
        if ($order->status >= Order::STATUS_PICKING) {
            $this->notifyPick($order);
        }

        if ($order->status >= Order::STATUS_WAITING) {
            $this->notifyPack($order);
        }
    }

    // 开始拣货了
    public function notifyPick($order)
    {
        $history = OrderHistory::where('order_id', $order->id)->where('status', Order::STATUS_PICKING)->first();

        // 通知商城系统
        if ($history) {
            dispatch(new NotifyThirdParty($order, $history));
        }
    }

    // 开始打包了
    public function notifyPack($order)
    {
        $history = OrderHistory::where('order_id', $order->id)->where('status', Order::STATUS_WAITING)->first();

        // 通知物流系统
        if ($history) {
            dispatch(new NotifyThirdParty($order, $history));
        }
    }
}