<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Events\OrderCancel;
use App\Events\OrderCreated;
use App\Events\OrderOutReady;
use App\Events\OrderShipped;
use App\Events\OrderPaid;
use App\Events\OrderCompleted;

class RePushOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:push {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新推送订单消息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = trim($this->argument('id'));
        $order = Order::where('out_sn', $id)->first();
        if(!$order)
        {
            echo "未找到订单号:{$id}".PHP_EOL;
            return false;
        }
        $order = $order->toArray();

        switch ($order["status"]) {
            case Order::STATUS_CANCEL:
                event(new OrderCancel($order));
                break;
            case Order::STATUS_DEFAULT:

                event(new OrderCreated($order));
                
                break;
            case Order::STATUS_PICKING:
                

                break;
            case Order::STATUS_PICK_DONE:
                break;
            case Order::STATUS_WAITING:
                event(new OrderOutReady($order));
                # code...
                break;
            case Order::STATUS_SENDING:
                
                event(new OrderShipped($order));
                break;
            case Order::STATUS_SUCCESS:
                
                event(new OrderCompleted($order));

                break;
            
            default:
                # code...
                break;
        }

        echo "通知完成".PHP_EOL;
    }
}