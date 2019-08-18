<?php
namespace  App\Services\Service;

use App\Models\Batch;
use App\Models\BatchProduct;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\WarehouseLocation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;
use App\Events\StockLocationIn;
use App\Events\StockLocationPutOn;
use App\Events\StockLocationPick;
use App\Events\StockLocationOut;
use App\Events\StockLocationAdjust;

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
//        dd($stocks);
        $stock_num = 0;
        $stocks = collect($stocks)->map(function ($v) use ($warehouse_id, &$stock_num){
            //入库
            $stock = $this->In($warehouse_id,$v);

            $stock_num += $v['stockin_num'];
            //上架
            $this->putOn($stock,$warehouse_id,$v['code']);
        })->toArray();
        //确认入库
        $batch = Batch::find($batch_id);
        $batch->status = Batch::STATUS_ACCOMPLISH;
        $batch->stock_num = $stock_num; //实际数量
        $batch->save();

        return $stocks;
    }


    //上架
    public  function putOn(ProductStock $stock,$warehouse_id, $code){

        if (! $location = WarehouseLocation::ofWarehouse($warehouse_id)->where('code', $code)->where('is_enabled',1)->first()) {
            return eRet('货位不存在或未启用('.$code.')');
        }

        $stock->warehouse_location_id = $location->id;
        $stock->save();//先兼容一下旧数据
        //库存上架
        $stock->pushToLocation($location->id, $stock->stockin_num);
        
        event(new StockLocationPutOn($stock, $stock->stockin_num));

        return $stock;
    }


    //入库
    public function In($warehouse_id,$data)
    {
        $batchProduct = BatchProduct::ofWarehouse($warehouse_id)->findOrFail($data['stock_id']);
        $batchProduct->load(['batch', 'spec.product.category']);

        if (! $batchProduct->batch->canStockIn()) {
            return eRet('id为'.$data['stock_id']."的入库单状态不是待入库或入库中");
        }
        $category = $batchProduct->spec->product->category;
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

        $batchProduct->distributor_code        = isset($data['distributor_code'])?$data['distributor_code']:"";
        $batchProduct->ean                     = $data['ean'];
        $batchProduct->expiration_date         = isset($data['expiration_date']) ?strtotime($data['expiration_date']." 00:00:00"): null;
        $batchProduct->best_before_date        = isset($data['best_before_date']) ?strtotime($data['best_before_date']." 00:00:00"): null;
        $batchProduct->production_batch_number = $data['production_batch_number']??'';
        $batchProduct->remark                  = $data['remark'];

        // 添加入库单记录
        $productStock = $batchProduct->setStockQty($data["stockin_num"])
                     ->setBoxCode($data["box_code"]??'')
                     ->setDistributorCode($batchProduct->distributor_code)
                     ->setEan($batchProduct->ean)
                     ->setProductionBatchNumber($batchProduct->production_batch_number)
                     ->setExpirationDate($batchProduct->expiration_date)
                     ->setBestBeforeDate($batchProduct->best_before_date)
                     ->convertToStock();

        // 入库单状态，修改为，入库中
        $batchProduct->batch->status = Batch::STATUS_PROCEED;
        $batchProduct->batch->save();
        // 入库单信息完善

        
        $batchProduct->stockin_num             = $data["stockin_num"];//记录已经入库数量
        $batchProduct->save();

        event(new StockLocationIn($productStock, $data['stockin_num']));
        return $productStock;
    }

    //拣货并出库
    public function pickAndOut($data)
    {
   
        $order = Order::find($data["order_id"]);
        if(!$order) 
        {
            throw new \Exception("订单不存在", 1);
        }

        //先拣货
        $pick = $this->pick($data["items"], $order);
        $pick_num = collect($pick)->sum('pick_num');
        if($pick_num <=0) {
            throw new \Exception("拣货失败,不需要出库", 1);
        }

        //再出库
        $this->out($pick, $data["delivery_date"], $order);
    }

    /**
     * 拣货单， 订单
     **/
    public function pick($pickItems, $order)
    {
        $pickItemIdArr = array_pluck($pickItems, 'order_item_id');
        $orderItemArr = $order->orderItems->pluck('id')->toArray();
        sort($pickItemIdArr);
        sort($orderItemArr);
        if ($pickItemIdArr != $orderItemArr) {
            throw new \Exception("拣货单物品项数据有误", 1);
        }

        $pickStockResult = [];

        foreach ($pickItems as $k=>$i)
        {

            $item = OrderItem::find($i['order_item_id']);
            if(!$item){
                throw new \Exception("拣货数量有误,订单明细丢失", 1);
                
            }

            if(intval($i['pick_num']) > intval($item->amount)){

                throw new \Exception("拣货数量超出应捡数目", 1);
            }

 
            //如过没有记录则去数据库拿
            $stockInLocations = app('stock')->getStockByAmount($i['pick_num'], $order->owner_id, $item->relevance_code);

        
            if($stockInLocations)
            {
                //保留原来结构
                //一个货位库存对应一个位置
                $stockInLocations = app('stock')->getStockOverAmount($i['pick_num'], $stockInLocations);
            }

            if(is_null($stockInLocations) || empty($stockInLocations) || count($stockInLocations)==0)
            {
                //库存真的不足
                throw new \Exception($item->product_name.'库存不足', 1);
            }

            $pickStockResult[] = [
                'item'      =>  $item,
                'pick_locations'     =>  $stockInLocations,
                'pick_num'  =>  $i['pick_num']
            ];
        }

        if(count($pickStockResult) ==0) {
            throw new \Exception("拣货数量不能为零", 1);
        }

        foreach ($pickStockResult as $k => $v){

            $v['item']->product_stock_id = $v['stock']->id;
            $v['item']->pick_num = $v['pick_num'];
            $v['item']->verify_num = $v['pick_num'];
            $v['item']->save();

            foreach ($v['pick_locations'] as $location) {
                // 添加记录
                event(new StockLocationPick($location->stock, $location['shelf_num']));
            }
            
 
        }

        $order->update([
            'status' => Order::STATUS_PICK_DONE,
            'verify_status'=>2,
            'delivery_data'=>time()
        ]);

        // 记录出库单拣货完成的时间
        OrderHistory::addHistory($order, Order::STATUS_PICK_DONE);

        return $pickStockResult;

        
    }

    /**
     * 拣货单， 出库
     **/
    public function out(Array $pickStockResult, $deliveryDate, $order)
    {
        foreach ($pickStockResult as $k => $v){
            event(new StockOut($v['stock'], $v['pick_num']));
 
        }

        $order->delivery_date = strtotime($deliveryDate." 00:00:00");
        $order->status = Order::STATUS_WAITING;
        $order->verify_status = 2;
        $order->save();

        // 记录出库单拣货完成的时间
        OrderHistory::addHistory($order, Order::STATUS_WAITING);

    }

    /**
     * 盘点
     **/
    public function recount($stock, $qty)
    {
        event(new StockLocationAdjust($stock, $qty));

    }

}