<?php
namespace  App\Services\Service;

use App\Models\ProductSpec;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseItemLog;
use App\Models\BatchMarkLog;

class PurchaseService
{
    /**
     * 创建采购
     **/
    public function create($request)
    {
        $items = [];
        $total_num  = 0;
        $sub_total = 0;

        foreach ($request['items'] as $k => $v) {
            // 检查采购商品外部编码
            $spec = ProductSpec::ofWarehouse($request['warehouse_id'])
                ->with('product')
                ->whose(app('auth')->ownerId())
                ->where('relevance_code', $v['relevance_code'])
                ->firstOrFail();
            $total_num+=$v['need_num'];
            $sub_total += $v['need_num']*floatval($v['purchase_price']);
            $items[] = [
                'product_spec_name'=> $spec->product_name_cn,
                'owner_id'         => Auth::ownerId(),
                'spec_id'          => $spec->id,
                'purchase_price'   => $v['purchase_price'],
                'relevance_code'   => $v['relevance_code'],
                'need_num'         => $v['need_num'],
                'warehouse_id'     => $request['warehouse_id'],
                'status'           => PurchaseItem::STATUS_PREPARE,
            ];
        }
        $data = [
            "warehouse_id"        => $request['warehouse_id'],
            "purchase_code"       => $request['purchase_code'],
            "order_invoice_number"    => $request['order_invoice_number'],
            "distributor_id"      => $request['distributor_id']??0,
            "need_num"            => $total_num,
            "sub_total"           => $sub_total,
            "status"              => Purchase::STATUS_PREPARE,
            "created_date"        => $request['created_date'],
            "owner_id"            => Auth::ownerId(),
        ];

        
        $batch = Purchase::create($data);
        $batch->items()->createMany($items);

        return true;
    }

    public function update($request,$purchase)
    {
        //删除原来的库存记录
        $purchase->items()->forceDelete();

        $stocks = [];
        $total_num  = 0;
        $sub_total = 0;

        foreach ($request['items'] as $k => $v) {
            // 检查采购商品外部编码
            $spec = ProductSpec::ofWarehouse($request['warehouse_id'])
                ->with('product')
                ->whose(app('auth')->ownerId())
                ->where('relevance_code', $v['relevance_code'])
                ->firstOrFail();
            $total_num+=$v['need_num'];
            $sub_total += $v['need_num']*floatval($v['purchase_price']);
            $items[] = [
                'product_spec_name'=> $spec->product_name_cn,
                'owner_id'         => Auth::ownerId(),
                'spec_id'          => $spec->id,
                'purchase_price'   => $v['purchase_price'],
                'relevance_code'   => $v['relevance_code'],
                'need_num'         => $v['need_num'],
                'warehouse_id'     => $request['warehouse_id'],
                'status'           => PurchaseItem::STATUS_PREPARE,
            ];
        }
        $data = [
            "warehouse_id"        => $request['warehouse_id'],
            "order_invoice_number"    => $request['order_invoice_number'],
            "distributor_id"      => $request['distributor_id']??0,
            "need_num"            => $total_num,
            "sub_total"           => $sub_total,
            "status"              => Purchase::STATUS_PREPARE,
            "created_date"        => $request['created_date'],
            "owner_id"            => Auth::ownerId(),
        ];

        Purchase::binds($purchase,$data);
        $purchase->save();
        $purchase->items()->createMany($items);

        return true;

    }

    public function setDone($id)
    {
        $data = Purchase::find($id);
        if(!$data) throw new Exception("Error Processing Request", 1);

        $data->status = Purchase::STATUS_ACCOMPLISH;
        $data->save();
        return true;
    }

    public function setItemDone($id)
    {
        $model = PurchaseItem::find($id);

        if(!$model) throw new Exception("Error Processing Request", 1);
        
        $model->status = PurchaseItem::STATUS_ACCOMPLISH;
        $model->confirm_num = $model->need_num;
        $model->last_confirm_date = date("Y-m-d");
        $model->save();

        $purchase = Purchase::find($model->purchase_id);
        $purchase->confirm_num += $model->confirm_num;
        $purchase->save();

        $log = new PurchaseItemLog;
        $log->purchase_id = $model->purchase_id;
        $log->purchase_item_id = $model->id;
        $log->warehouse_id = $model->warehouse_id;
        $log->need_num = $model->need_num;
        $log->confirm_num = $model->need_num;
        $log->total_confirm_num = $model->need_num;
        $log->confirm_date = date("Y-m-d");
        $log->owner_id = $model->owner_id;
        $log->save();

        return true;
    }

    public function setItemArrived($data, $id)
    {
        $model = PurchaseItem::find($id);

        if(!$model) throw new Exception("Error Processing Request", 1);
        $model->last_confirm_date = $data["arrived_date"];
        $model->confirm_num = $data["arrived_num"];
        $model->status = PurchaseItem::STATUS_PROCEED;
        
        if($model->confirm_num >= $model->need_num)
        {
            $model->status = PurchaseItem::STATUS_ACCOMPLISH;
        }

        $model->save();

        $purchase = Purchase::find($model->purchase_id);
        $purchase->confirm_num += $data["arrived_num"];
        $purchase->save();

        $log = new PurchaseItemLog;
        $log->purchase_id = $model->purchase_id;
        $log->purchase_item_id = $model->id;
        $log->warehouse_id = $model->warehouse_id;
        $log->need_num = $model->need_num;
        $log->confirm_num = $data["arrived_num"];
        $log->total_confirm_num = $model->confirm_num;
        $log->confirm_date = $data["arrived_date"];
        $log->owner_id = $model->owner_id;
        $log->save();
        
    }

    /*
     *生成 -采购号
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