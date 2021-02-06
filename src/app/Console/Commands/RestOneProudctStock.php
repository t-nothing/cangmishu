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
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class RestOneProudctStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:product-reset {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置一个商品的所有库存,并清空出入库记录, id=商品ID';

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
        $id = intval($this->argument('id'));
        $product = Product::find($id);
        if(!$product)
        {
            echo "未找到商品ID".PHP_EOL;
            return false;
        }

        DB::update('update product set total_stockin_num = 0, total_shelf_num =0 ,total_floor_num= 0, total_lock_num=0,total_stockout_num=0,total_stockin_times=0,total_stockout_times=0,total_stock_num=0 where id = ?',[$id]);
        DB::update('update product_spec set total_stockin_num = 0, total_shelf_num =0 ,total_floor_num= 0, total_lock_num=0,total_stockout_num=0,total_stockin_times=0,total_stockout_times=0,total_stock_num=0 where product_id = ?',[$id]);
        DB::delete("delete from product_stock where spec_id in (select id from product_spec where product_id = ?)",[$id]);
        // DB::delete("delete from product_stock_location where spec_id in (select id from product_spec where product_id = ?)",[$id]);
        // DB::delete("delete from product_stock_lock where spec_id in (select id from product_spec where product_id = ?)",[$id]);
        DB::delete("delete from product_stock_log where spec_id in (select id from product_spec where product_id = ?)",[$id]);
        // DB::delete("delete from order_item_stock_location where spec_id in (select id from product_spec where product_id = ?)",[$id]);

        echo "清除完成".PHP_EOL;
    }
}
