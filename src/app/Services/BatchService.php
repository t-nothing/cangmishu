<?php
namespace  App\Services;

use App\Models\ProductSpec;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Batch;
use App\Models\BatchMarkLog;
class BatchService
{
    /**
     * 创建入库单
     **/
    public function create($request)
    {
        $stocks = [];
        $batch_num  = 0;
        $total_purchase_price = 0;

        foreach ($request['product_stock'] as $k => $v) {
            // 检查入库单商品外部编码
            $spec = ProductSpec::ofWarehouse($request['warehouse_id'])
                ->with('product')
                ->whose(app('auth')->ownerId())
                ->where('relevance_code', $v['relevance_code'])
                ->firstOrFail();
            $batch_num+=$v['need_num'];
            $stocks[] = [
                'name_cn'          => $spec->product_name_cn,
                'name_en'          => $spec->product_name_en,
                'owner_id'         => Auth::ownerId(),
                'spec_id'          => $spec->id,
                'purchase_price'   => $v['purchase_price'],
                'relevance_code'   => $v['relevance_code'],
                'need_num'         => $v['need_num'],
                'remark'           => $v['remark'],
                'distributor_id'   => isset($request['distributor_id'])?$request['distributor_id']:0,
                'distributor_code' => isset($v['distributor_code'])?$v['distributor_code']:"",
                'warehouse_id'     => $request['warehouse_id'],
                'status'           => Product::PRODUCT_STATUS_PREPARE,
                'sku'	           => ProductSpec::newSku($spec),
            ];
            //单价乘以数量
            $total_purchase_price += $v['purchase_price'] * $v['need_num'];
        }
        $data = [
            "warehouse_id"        => $request['warehouse_id'],
            "type_id"             => $request['type_id'],
            "confirmation_number" => isset($request['confirmation_number'])?$request['confirmation_number']:"",
            "distributor_id"      => isset($request['distributor_id'])?$request['distributor_id']:0,
            "need_num"            => $batch_num,
            "total_purchase_price"=> $total_purchase_price,
            "status"              => Batch::STATUS_PREPARE,
            "owner_id"            => Auth::ownerId(),
        ];

        if (isset($request['plan_time'])) {
            $data['plan_time']= strtotime($request['plan_time']);
        }

        if (isset($request['over_time'])) {
            $data['over_time'] = strtotime($request['over_time']);
        }
        if (isset($request['remark'])) {
            $data['remark'] = $request['remark'];
        }
        $warehouse = Warehouse::find($request['warehouse_id']);
        $data['batch_code'] = $this->batchCode($warehouse);
        if(trim($data["confirmation_number"]) == "") {
            $data['confirmation_number'] = $data["batch_code"];
        }

        $batch = Batch::create($data);
        $batch->batchProducts()->createMany($stocks);
        BatchMarkLog::saveBatchCode($batch);

        return $batch;
    }

    public function update($request,$batch)
    {
        //删除原来的库存记录
        $batch->stocks()->forceDelete();

        $stocks = [];
        $batch_num  = 0;

        foreach ($request['product_stock'] as $k => $v) {
            // 检查入库单商品外部编码
            $spec = ProductSpec::ofWarehouse($request['warehouse_id'])
                ->whose(app('auth')->ownerId())
                ->where('relevance_code', $v['relevance_code'])
                ->firstOrFail();
            $batch_num+=$v['need_num'];
            $stocks[] = [
                'owner_id'         => Auth::ownerId(),
                'spec_id'          => $spec->id,
                'purchase_price'   => $v['purchase_price'],
                'relevance_code'   => $v['relevance_code'],
                'need_num'         => $v['need_num'],
                'remark'           => $v['remark'],
                'distributor_id'   => isset($request['distributor_id'])?$request['distributor_id']:0,
                'distributor_code' => isset($v['distributor_code'])?$v['distributor_code']:"",
                'warehouse_id'     => $request['warehouse_id'],
                'status'           => Product::PRODUCT_STATUS_PREPARE,
                'sku'	           => ProductSpec::newSku($spec),
            ];
        }
        $data = [
            "type_id"             => $request['type_id'],
            "confirmation_number" => isset($request['confirmation_number'])?$request['confirmation_number']:"",
            "distributor_id"      => isset($request['distributor_id'])?$request['distributor_id']:0,
            "need_num"            => $batch_num,
            "status"              => Batch::STATUS_PREPARE,
        ];

        if (isset($request['plan_time'])) {
            $data['plan_time']= strtotime($request['plan_time']);
        }

        if (isset($request['over_time'])) {
            $data['over_time'] = strtotime($request['over_time']);
        }
        if (isset($request['remark'])) {
            $data['remark'] = $request['remark'];
        }

        Batch::binds($batch,$data);
        $batch->save();
        $batch->stocks()->createMany($stocks);
        BatchMarkLog::saveBatchCode($batch);

        return true;

    }

    /*
     *生成 -入库单号
     * */
    public function batchCode($warehouse)
    {
        $warehouse_code = $warehouse->code;
        $batch_time = date('y').date('W').date('w');
        $batch_mark = BatchMarkLog::newMark($warehouse_code);
        $code = $warehouse_code.$batch_time.sprintf("%04d", $batch_mark);
        return $code;
    }
}
