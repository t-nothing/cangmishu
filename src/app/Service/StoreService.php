<?php
namespace  App\Services\Service;

use App\Models\Batch;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\WarehouseLocation;

class StoreService
{

    //入库
    public  function  InAndPutOn($warehouse_id,$data,$batch_id)
    {
        $stocks =[];
        if (count($data) ==count($data, 1)) {
            $stocks[] = $data;
        }else{
            $stocks = $data;
        }
        $stocks = collect($stocks)->map(function ($v) use ($warehouse_id){
            //入库
            $stock = $this->In($warehouse_id,$v);
            //上架
            $this->putOn($stock,$warehouse_id,$v['code']);
        })->toArray();
        //确认入库
       $batch = Batch::find($batch_id);
       $batch->status = Batch::STATUS_ACCOMPLISH;
       $batch->save();
        return $stocks;
    }


    //上架
    public  function putOn(ProductStock $stock,$warehouse_id, $code){

        if (! $location = WarehouseLocation::ofWarehouse($warehouse_id)->where('code', $code)->where('is_enabled',1)->first()) {
            return eRet('货位不存在或未启用('.$code.')');
        }

        $stock->warehouse_location_id = $location->id;
        $stock->shelf_num             = $stock->stockin_num;
        $stock->total_shelf_num       = $stock->stockin_num;
        $stock->status                = ProductStock::GOODS_STATUS_ONLINE;
        $stock->save();
        // 添加记录
        $stock->addLog(ProductStockLog::TYPE_BATCH_SHELF, $stock->stockin_num,$stock->batch->batch_code);

        return $stock;
    }


    //入库
    public function In($warehouse_id,$data)
    {
        $stock = ProductStock::ofWarehouse($warehouse_id)->findOrFail($data['stock_id']);
        $stock->load(['batch', 'spec.product.category']);

        if (! $stock->batch->canStockIn()) {
            return eRet('id为'.$data['stock_id']."的入库单状态不是待入库或入库中");
        }
        $category = $stock->spec->product->category;
        if ($category) {
            $rules = [];
            $category->need_expiration_date == 1 AND
            $rules['expiration_date'] = 'required|date_format:Y-m-d';
            $category->need_best_before_date == 1 AND
            $rules['best_before_date'] = 'required|date_format:Y-m-d';
            $category->need_production_batch_number == 1 AND
            $rules['production_batch_number'] = 'required|string|max:255';
            $rules &&
            validator($data,$rules);
        }
        // 入库单状态，修改为，入库中
        $stock->batch->status = Batch::STATUS_PROCEED;
        $stock->batch->save();
        // 入库单信息完善

        $stock->distributor_code        = isset($data['distributor_code'])?$data['distributor_code']:"";
        $stock->ean                     = $data['ean'];
        $stock->expiration_date         = $data['expiration_date'] ?: null;
        $stock->best_before_date        = $data['best_before_date'] ?: null;
        $stock->production_batch_number = $data['production_batch_number']?: '';
        $stock->remark                  = $data['remark'];
        $stock->save();
        // 入库数量更新
        $stock->increment('stockin_num', $data['stockin_num']);
        $stock->increment('total_stockin_num', $data['stockin_num']);
        // 添加入库单记录
//        $stock->addLog(ProductStockLog::TYPE_BATCH, $data['stockin_num']);

        return $stock;
    }
}