<?php
namespace  App\Services\Service;

use App\Models\Pick;
use App\Models\ProductStock;
use App\Models\ProductStockLock;
use App\Events\StockPick;

class PickService
{
    protected  $warehouse_id;
    protected  $redis;

    public function __construct($warehouse_id)
    {
        $this->redis = app('redis.connection');
        $this->warehouse_id = $warehouse_id;
    }

    /*
     * 查看拣货单库存详情
     */
    public function getPickShelf(Pick $pick, $kep_code = null)
    {
        $stockService = app('StockService');
        $data = [];
        $lock = [];
        $data['shipment_num'] = $pick['shipment_num'];
        $data['kep_code'] = $kep_code ?:0;
        $data['order_items'] = [];
        $data['empty_pick'] = [];
        foreach ($pick->orderItems as $v) {
            $order = $pick->order;
            $owner_id = $order['owner_id'];
            $item = [];
            $item['order_item_id'] = $v['id'];
            $item['relevance_code'] = $v['relevance_code'];
            $item['name'] = $v['product_name'];
            $item['amount'] = $v['amount'];
            $item['photos'] = isset($v->spec->product->photos) ? $v->spec->product->photos : '';
            $item['order'] = [
                'created_at' => $order->created_at->toDateTimeString(),
                'delivery_date' => $order->delivery_date?$order->delivery_date->toDateString():'',
            ];

            //去redis里查询是否有对应的库存
            $name = 'pick_'.$owner_id.'_'.$v['relevance_code'];
            $cache_stock = $this->redis->hgetall($name);
            $stock= "";
            $cacahe_id = [];
            if($cache_stock){ //如果redis里有缓存
                foreach ($cache_stock as $stock_id => $rest_num){
                    // 可用库存 = 实际库存 - 锁定库存
                    //判断redis库存是否可用
                    $rest_stock = ProductStock::find($stock_id);
                    if($rest_stock->status == ProductStock::GOODS_STATUS_ONLINE){
                        $lock_num = ProductStockLock::where('stock_id', $stock_id)->where('over_time', '>=', time())->sum('lock_amount');
                        $use_num = $rest_num - $lock_num;
                        if($use_num >= $v['amount'] ){ //可用库存足够
                            $stock = $rest_stock;
                            break;
                        }
                    }
                    $cacahe_id[] = $stock_id;
                }
            }
            //如过没有记录则去数据库拿
            if(empty($stock)){
                $stock = $stockService->getStockByAmount($v['amount'], $owner_id, $v['relevance_code'], $cacahe_id);
                $stock = $stockService->getStockOverAmount($v['amount'], $stock);
            }
            if(empty($stock)){//库存真的不足
                    eRet('库存不足');
            }

            else{
                $item['shipment_num'] = $pick->shipment_num;
                $item['stock'] = [
                    'stock_id' => $stock->id,
                    'sku' => $stock->sku,
                    'ean' => $stock->ean,
                    'shelf_num' => $stock->shelf_num,
                    'location' => isset($stock->location->code) ? $stock->location->code : '',
                ];
                $lock []=[
                    'stock_id' => $stock->id,
                    'order_id' => $order->id,
                    'relevance_code' => $v['relevance_code'],
                    'order_item_id' => $v['id'],
                    'lock_amount' => $v['amount'],
                ];
            }
            $data['order_items'][] = $item;
        }
        return compact('data', 'lock');
    }

    public function deleteLock(Pick $pick)
    {
        $order_item_ids = $pick->orderItems->pluck('id')->toArray();
        $count = ProductStockLock::where("order_id", $pick->order->id)->whereIn("order_item_id", $order_item_ids)->count();
        if($count){
            ProductStockLock::where("order_id", $pick->order->id)->whereIn("order_item_id", $order_item_ids)->delete();
        }
    }
}