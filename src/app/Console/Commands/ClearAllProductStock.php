<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearAllProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除商品库所有库存记录';

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
        DB::update('update product set total_stockin_num = 0, total_shelf_num =0 ,total_floor_num= 0, total_lock_num=0,total_stockout_num=0,total_stockin_times=0,total_stockout_times=0,total_stock_num=0');
        DB::update('update product_spec set total_stockin_num = 0, total_shelf_num =0 ,total_floor_num= 0, total_lock_num=0,total_stockout_num=0,total_stockin_times=0,total_stockout_times=0,total_stock_num=0');
        DB::delete("delete from product_stock");
        DB::delete("delete from product_stock_location");
        DB::delete("delete from product_stock_lock");
        DB::delete("delete from product_stock_log");
        DB::delete("delete from order_item_stock_location");
        DB::delete("delete from `order`");
        DB::delete("delete from order_history");
        DB::delete("delete from order_item");
        DB::delete("delete from merge_pick");

        echo "清除完成".PHP_EOL;


    }
}
