<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\DB;

class WarningProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:warning {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '库存预警 id=外部编码';

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
        $spec = ProductSpec::with('product.category')->where('relevance_code', $id)->first();
        if(!$spec)
        {
            echo "未找到规格:{$id}".PHP_EOL;
            return false;
        }
        $spec = $spec->toArray();

        print_r($spec);exit;

        switch ($spec["status"]) {
            case Order::STATUS_CANCEL:
                event(new OrderCancel($spec));
                break;
            case Order::STATUS_DEFAULT:

                event(new OrderCreated($spec));
                
                break;
            case Order::STATUS_PICKING:
                

                break;
            case Order::STATUS_PICK_DONE:
                break;
            case Order::STATUS_WAITING:
                event(new OrderOutReady($spec));
                # code...
                break;
            case Order::STATUS_SENDING:
                
                event(new OrderShipped($spec));
                break;
            case Order::STATUS_SUCCESS:
                
                event(new OrderCompleted($spec));

                break;
            
            default:
                # code...
                break;
        }

        echo "通知完成".PHP_EOL;
    }
}