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

        print_r($spec['total_stock_num']);exit;

        echo "通知完成".PHP_EOL;
    }
}