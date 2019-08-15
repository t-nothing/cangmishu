<?php
namespace  App\Services\Service;

use App\Models\ProductStock;
use App\Models\ProductStockLock;

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
        $stocks = ProductStock::with('location')
            ->has('location')
            ->whose($owner_id)
            ->ofWarehouse($this->warehouse->id)
            ->enabled()
            ->where('shelf_num', '>=' , $amount)
            ->where('relevance_code', $relevance_code)
            ->orderByRaw('ISNULL(`expiration_date`) ASC')
            ->oldest('expiration_date')
            ->orderByRaw('ISNULL(`best_before_date`) ASC')
            ->oldest('best_before_date')
            ->orderByRaw('ISNULL(`created_at`) ASC')
            ->oldest('created_at')
            ->when($ids, function($query) use ($ids){
                return $query->whereNotIn('id', $ids);
            })
            ->first();
        return $stocks;
    }
}