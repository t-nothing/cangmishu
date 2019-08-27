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
use App\Models\OrderItemStockLocation;
use Illuminate\Support\Facades\DB;
use App\Events\StockLocationIn;
use App\Events\StockLocationPutOn;
use App\Events\StockLocationPick;
use App\Events\StockLocationOut;
use App\Events\OrderCompleted;
use App\Events\OrderOutReady;

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
            //入库到虚拟货位
            $locationStock = $this->inAndMoveTo($warehouse_id,$v, $v['code']);

            $stock_num += $v['stockin_num'];
            //上架
            // $this->moveTo($locationStock,$warehouse_id,$v['code']);

        })->toArray();

        //确认入库
        $batch = Batch::find($batch_id);
        $batch->status = Batch::STATUS_ACCOMPLISH;
        $batch->stock_num = $stock_num; //实际数量
        $batch->save();

        return $stocks;
    }


    //上架
    public  function moveTo($stockLocation, $warehouse_id, $code){

        if (! $location = WarehouseLocation::ofWarehouse($warehouse_id)->where('code', $code)->where('is_enabled',1)->first()) {
            return eRet('货位不存在或未启用('.$code.')');
        }
        //从虚拟库存整体移动到新位置
        $newStockLocation = $stockLocation->moveTo($stockLocation->shelf_num, $location);

        event(new StockLocationPutOn($newStockLocation, $newStockLocation->shelf_num));

        return $newStockLocation;
    }


    //入库
    public function inAndMoveTo($warehouse_id,$data, $code)
    {
        $batchProduct = BatchProduct::ofWarehouse($warehouse_id)->findOrFail($data['stock_id']);
        $batchProduct->load(['batch', 'spec.product.category']);

        if (! $batchProduct->batch->canStockIn()) {
            return eRet('id为'.$data['stock_id']."的入库单状态不是待入库或入库中");
        }

        if (! $location = WarehouseLocation::ofWarehouse($warehouse_id)->where('code', $code)->where('is_enabled',1)->first()) {
            return eRet('货位不存在或未启用('.$code.')');
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
        $batchProduct->expiration_date         = isset($data['expiration_date']) && !empty($data['expiration_date']) ?strtotime($data['expiration_date']." 00:00:00"): null;
        $batchProduct->best_before_date        = isset($data['best_before_date']) && !empty($data['best_before_date'])?strtotime($data['best_before_date']." 00:00:00"): null;
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
                        ->setLocationId($location->id)//目前版本是一对一的
                        ->convertToStock();

        // 入库单状态，修改为，入库中
        $batchProduct->batch->status = Batch::STATUS_PROCEED;
        $batchProduct->batch->save();
        // 入库单信息完善

        
        $batchProduct->stockin_num             = $data["stockin_num"];//记录已经入库数量
        $batchProduct->save();

        //直接上到货位上面
        $locationStock = $productStock->pushToLocation($location->id, $data['stockin_num']);

        //入库
        event(new StockLocationIn($locationStock, $data['stockin_num']));
        //上架
        event(new StockLocationPutOn($locationStock, $locationStock->shelf_num));
        return $locationStock;
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
        app('log')->info('拣货流程完成');
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
        app('log')->info('开始拣货', [
            'out_sn'=> $order->out_sn
        ]);
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

            // app('log')->info('库存不足', $stockInLocations->toArray());
            if($stockInLocations)
            {
                //保留原来结构
                //一个货位库存对应一个位置
                $stockInLocations = app('stock')->getStockOverAmount($i['pick_num'], $stockInLocations);
            }

            if(is_null($stockInLocations) || empty($stockInLocations) || count($stockInLocations)==0)
            {
                // app('log')->info('库存不足', $stockInLocations->toArray());
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

        //这里可以先生成拣货单

        $subPickNum = 0;
        foreach ($pickStockResult as $k => $v){

            app('log')->info('开始从库位拣货AAA');
            // $v['item']->product_stock_id = $v['stock']->id;
            $v['item']->pick_num = $v['pick_num'];
            $v['item']->verify_num = $v['pick_num'];
            $v['item']->save();

            $subPickNum += $i['pick_num'];
            app('log')->info('开始从库位拣货AAA');

            foreach ($v['pick_locations'] as $locationStock) {
                // 添加记录
                // 这里的pick_num 实际上是拣货数量


                app('log')->info('开始从库位拣货', [
                    'location_id'   =>  $locationStock["id"],
                    'out_sn'        => $locationStock['pick_num']
                ]);

                //这里是要拼出来存到出库清单对应的位置中去的
                $tmp['stock_id'] = $locationStock["stock_id"];
                $tmp['warehouse_location_id'] = $locationStock["warehouse_location_id"];
                $tmp['warehouse_location_code'] = $locationStock["warehouse_location_code"];
                $tmp['warehouse_id'] = $locationStock["warehouse_id"];
                $tmp['product_stock_location_id'] = $locationStock["id"];
                $tmp['item_id'] = $v['item']["id"];
                $tmp['pick_num'] = $locationStock['pick_num'];
                $tmp['shipment_num'] = $locationStock["shipment_num"]??"";
                $tmp['stock_sku'] = $locationStock["sku"];
                $tmp['relevance_code'] = $v['item']->relevance_code;
                $tmp['verify_num'] = $locationStock['pick_num'];//自动验货

                OrderItemStockLocation::create($tmp);


                event(new StockLocationPick($locationStock, $locationStock['pick_num']));
            }
            
 
        }

        app('log')->info('更新出库信息', [
            'status' => Order::STATUS_PICK_DONE,
            'sub_pick_num'  => $subPickNum,
            'verify_status'=>2,
            'delivery_data'=>time()
        ]);
        
        $order->update([
            'status' => Order::STATUS_PICK_DONE,
            'sub_pick_num'  => $subPickNum,
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

            foreach ($v['pick_locations'] as $locationStock) {
                event(new StockLocationOut($locationStock, $locationStock['pick_num']));
            }
 
        }

        $order->delivery_date = strtotime($deliveryDate." 00:00:00");
        $order->status = Order::STATUS_WAITING;
        $order->verify_status = 2;
        $order->save();

        // 记录出库单拣货完成的时间
        OrderHistory::addHistory($order, Order::STATUS_WAITING);
        event(new OrderOutReady($order));

    }


}