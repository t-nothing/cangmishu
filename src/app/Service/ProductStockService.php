<?php
namespace  App\Services\Service;

use App\Models\ProductStock;
use App\Models\ProductStockLock;
use App\Models\ProductStockLocation;
use DB;

class ProductStockService
{
    protected  $warehouse;

    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /*
    *根据入库单编号获取sku详情,并分页返回
    */
    public function getSkuInfoByBatchId($batch_id, $page_size){
        $info = ProductStock::ofWarehouse(app('auth')->warehouse()->id)
            ->when(app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER, function ($query) {
                $query->whose(app('auth')->id());
            })
            ->where('batch_id', $batch_id)
            ->with(['spec:id,name_cn,product_id', 'spec.product:id,name_cn'])
            ->paginate($page_size, [
                'id',
                'relevance_code',
                'distributor_code',
                'pieces_num',
                'relevance_code',
                'remark',
                'spec_id',
                'sku',
                'need_num',
                'total_stockin_num'
            ])
            ->toArray();
        return $info;
    }


    /*
     * 根据库存需求查出符合要求的货品
     */
    public function getStockByAmount($amount,$owner_id,$relevance_code,$ids = null){

         //第一步先找出所有总库存够不够

        $totalShelfNumRecord = ProductStockLocation::where('product_stock_location.owner_id', $owner_id)
            ->where('product_stock_location.warehouse_id', $this->warehouse->id)
            ->where('product_stock_location.shelf_num', '>' , 0) //库存即架子上面的数量 $amount
            ->where('product_stock_location.relevance_code', $relevance_code)
            ->select(DB::raw('sum(product_stock_location.shelf_num) as total_shelf_num, min(product_stock_location.stock_id) as min_id, count(product_stock_location.id) as  count'))
            ->when($ids, function($query) use ($ids){
                return $query->whereNotIn('id', $ids);
            })
            ->limit(1)
            ->first();

        if(!$totalShelfNumRecord || $totalShelfNumRecord["total_shelf_num"] < $amount)
        {
            // echo "库存不足";
            return NULL;
        }


        // DB::connection()->enableQueryLog();  // 开启QueryLog
        $stocks = ProductStockLocation::leftjoin('product_stock', 'product_stock.id', '=', 'product_stock_location.stock_id')
            ->where('product_stock_location.owner_id', $owner_id)
            ->where('product_stock_location.warehouse_id', $this->warehouse->id)
            ->where('product_stock.status', ProductStock::GOODS_STATUS_ONLINE)
            ->where('product_stock.id', '>=', $totalShelfNumRecord["min_id"])
            ->where('product_stock.shelf_num', '>' , 0) //库存即架子上面的数量 $amount
            ->where('product_stock_location.shelf_num', '>' , 0) //库存即架子上面的数量 $amount
            ->where('product_stock_location.relevance_code', $relevance_code)
            ->where('product_stock.relevance_code', $relevance_code)
            ->whereRaw("product_stock_location.id not in (
                select product_stock_location_id from product_stock_lock where relevance_code = '{$relevance_code}'  and product_stock_location_id = product_stock_location.id  and over_time>=UNIX_TIMESTAMP(now())
                group by product_stock_location_id having sum(lock_amount) >= product_stock_location.shelf_num)
                ")
            ->orderByRaw('if(product_stock_location.sort_num >= 8,8,0) desc')
            ->orderByRaw('ISNULL(`expiration_date`) ASC')
            ->oldest('expiration_date')
            ->orderByRaw('ISNULL(`best_before_date`) ASC')
            ->oldest('best_before_date')
            // ->orderByRaw('ISNULL(`created_at`) ASC')
            ->oldest('product_stock_location.created_at')
            ->oldest('product_stock_location.id')
            ->when($ids, function($query) use ($ids){
                return $query->whereNotIn('id', $ids);
            })
            ->select(DB::raw('product_stock_location.*'))
            ->limit(min($amount, $totalShelfNumRecord['count']+1)) //先限制数量，这个性能不一定最佳，基本上传进来的数量都是10个以下,+1安全数量
            ->get();
// dd(DB::getQueryLog());      
 

        $stocks->load("location", "spec.product");
        return $stocks;
    }

    /**
     * 扣掉已经锁掉的库存
     * @param amout 拣货数量
     **/
    public function getStockOverAmount(int $amount, $stockInLocations){

        //@todo 这个地方存在，10个苹果去3个记录上面拿
        //原来是判断一个位置就行了
        //over_time
        $restNum = $amount;
        foreach ($stockInLocations as $k=>&$stock){

            $stock->pick_num = $stock->shelf_num;
            //如果找完了
            if($restNum <= 0)
            {
                unset($stockInLocations[$k]);
                continue;
            }

            $lock_num = ProductStockLock::where('stock_id',$stock->stock_id)->where('product_stock_location_id', $stock->id)->where('over_time','>=',time())->sum('lock_amount');

            //架上的库存减锁定库存
            //use_num =实际可用库存
            $availableNum = $stock->pick_num - $lock_num;

            if($availableNum <= 0)
            {
                unset($stockInLocations[$k]);
                continue;
            }


            app('log')->info('开始拣货', [
                'stock-id'=> $stock->stock_id
            ]);


            $stock->pick_num = min($availableNum, $restNum);
            $restNum -= min($availableNum, $restNum);
        }



        return $stockInLocations;
    }
}