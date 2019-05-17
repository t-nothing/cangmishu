<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\PickAndOutRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Pick;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{

    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize(),
            'created_at_b' => 'date:Y-m-d',
            'created_at_e' => 'date:Y-m-d',
            'status' => 'integer',
            'keywords' => 'string',
            'delivery_date' => 'date_format:Y-m-d',
            'warehouse_id' =>  [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ]
        ]);
        $order = Order::with(['orderItems:id,relevance_code,amount,name_cn,order_id', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser'])
            ->ofWarehouse($request->warehouse_id)
            ->whose(app('auth')->ownerId());
        if ($request->filled('created_at_b')) {
            $order->where('created_at', '>', strtotime($request->created_at_b));
        }

        if ($request->filled('created_at_e')) {
            $order->where('created_at', '<', strtotime($request->created_at_e));
        }

        if ($request->filled('status')) {
            $order->where('status', $request->status);
        }

        if ($request->filled('keywords')) {
            $order->hasKeywords($request->keywords);
        }
        $order->when($request->filled('delivery_date'), function($query) use ($request){
            return $query->whereBetween ("delivery_date",
                [strtotime($request->delivery_date),strtotime($request->delivery_date ."+1 day")*1-1]);
        });

        $orders = $order->latest()->paginate($request->input('page_size',10),['id','created_at','send_phone','receiver_phone','send_fullname','receiver_fullname','delivery_date','warehouse_id','order_type','delivery_type'])->toArray();
        foreach ($orders['data'] as $k => $v) {
            $sum = 0;
            if (!empty($v['order_items'])) {
                foreach ($v['order_items'] as $k1 => $v1) {
                    $sum += $v1['amount'];
                }
            }
            $orders['data'][$k]['sum'] = $sum;
        }

        return formatRet(0, '', $orders);
    }


    public function store(CreateOrderRequest $request)
    {
        app('log')->info('新增出库单',$request->all());
        app('db')->beginTransaction();
        try {
            app('order')->create($request);
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('新增出库单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '出库单新增失败');
        }
        return formatRet(0,'出库单新增成功');
    }

//    public function destroy(BaseRequests $request,$order_id)
//    {
//        app('log')->info('取消订单',['order_id'=>$order_id,'warehouse_id' =>$request->warehouse_id]);
//        $this->validate($request,[
//            'warehouse_id' =>  [
//                'required','integer','min:1',
//                Rule::exists('warehouse')->where(function($q){
//                    $q->where('owner_id',Auth::ownerId());
//                })
//            ],
//        ]);
//
//        $order = Order::where('warehouse_id',$request->warehouse_id)->find($order_id);
//        if(!$order){
//            return formatRet(500,"订单不存在");
//        }
//        if ($order->owner_id != Auth::ownerId()){
//            return formatRet(500,"没有权限");
//        }
//        try{
//            Order::where('id',$order_id)->delete();
//            return formatRet(0);
//        }catch (\Exception $e){
//            app('log')->info('取消订单失败',['msg' =>$e->getMessage()]);
//            return formatRet(500,"取消订单失败");
//        }
//    }


    public function pickAndOut(PickAndOutRequest $request)
    {
        app('log')->info('一键出库',$request->all());
        $owner_id = Auth::ownerId();
        $order = Order::find($request->order_id);
        $items = $request->input('items');

        $item_in_rq = array_pluck($request->items, 'order_item_id');
        $item_in_db = $order->orderItems->pluck('id')->toArray();
        sort($item_in_rq);
        sort($item_in_db);
        if ($item_in_rq != $item_in_db) {
            return formatRet(500, "拣货单物品项数据有误");
        }

        $redis = app('redis.connection');
        $pick_stock = [];
        foreach ($items as $k=>$i){
            $item = OrderItem::find($i['order_item_id']);
            if($i['pick_num'] > $item->amount){
                return formatRet(500,'拣货数量超出应捡数目');
            }
            $name = 'cangmishu_pick_'.$owner_id.'_'.$item->relevance_code;
            $cache_stock = $redis->hgetall($name);
            $cacahe_id =[];
            if($cache_stock){ //如果redis里有缓存
                foreach ($cache_stock as $stock_id => $rest_num){
                    //判断redis库存是否可用
                    $rest_stock = ProductStock::find($stock_id);
                    if($rest_stock->status == ProductStock::GOODS_STATUS_ONLINE){
                        if($rest_num >= $i['pick_num'] ){ //可用库存足够
                            $stock = $rest_stock;
                            break;
                        }
                    }
                    $cacahe_id[] = $stock_id;
                }
            }
            //如过没有记录则去数据库拿
            if(empty($stock)){
                $stock = app('stock')->getStockByAmount($i['pick_num'], $owner_id, $item->relevance_code, $cacahe_id);
            }

            if(empty($stock)){//库存真的不足
                eRet($item->product_name.'库存不足');
            }
            $pick_stock[] =[
                'item'=>$item,
                'stock'=>$stock,
                'pick_num' =>$i['pick_num']
            ];
        }
        DB::beginTransaction();
        $res = [];
        try{
            foreach ($pick_stock as $v){
                $v['item']->product_stock_id = $v['stock']->id;
                $v['item']->pick_num = $v['pick_num'];
                $v['item']->vaerify_num = $v['pick_num'];
                $v['item']->save();
                $v['stock']->decrement('shelf_num', $v['pick_num']);
                // 添加记录
                $v['stock']->addLog(ProductStockLog::TYPE_PICKING, $v['pick_num'],$request->order_id);
                $v['stock']->addLog(ProductStockLog::TYPE_OUTPUT, $v['pick_num'],$request->order_id);
                $res[]=[
                    'owner_id'=>$v['stock']->owner_id,
                    'relevance_code' =>$v['stock']->relevance_code,
                    'stock_id' =>$v['stock']->id,
                    'shelf_num' =>$v['stock']->shelf_num
                ];
            }
            $order->update(['status' => Order::STATUS_WAITING,'verify_status'=>2]);
            // 记录出库单拣货完成的时间
            OrderHistory::addHistory($order, Order::STATUS_DEFAULT);
            OrderHistory::addHistory($order, Order::STATUS_PICKING);
            OrderHistory::addHistory($order, Order::STATUS_PICK_DONE);
            OrderHistory::addHistory($order, Order::STATUS_WAITING);
            DB::commit();
        }
        catch (BusinessException $exception){
            app('db')->rollback();
            $message = $exception->getResponse()->getData()->msg;
            info('完成拣货失败', ['exception msg' =>$message]);
            return formatRet(500, $message);
        }
        catch (\Exception $e){
            DB::rollBack();
            app('log')->error('出库失败',['msg'=>$e->getMessage()]);
            return formatRet(500,'出库失败');
        }
        foreach ($res as $s){
            $name = 'pick_'.$s['owner_id'].'_'.$s['relevance_code'];
            app('log')->info('写入缓存', ['message'=> '名称'.$name.'序号'.$s['stock_id'].'数量'.$s['shelf_num']]);
            $redis->hset($name, $s['stock_id'], $s['shelf_num']);
        }
        return formatRet(0,'出库成功');

    }

    public function updateStatus(BaseRequests $request,$order_id)
    {
        app('log')->info('取消订单',['order_id'=>$order_id,'warehouse_id' =>$request->warehouse_id]);
        $this->validate($request,[
            'warehouse_id' =>  [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
        ]);
        $order = Order::where('warehouse_id',$request->warehouse_id)->find($order_id);
        if(!$order){
            return formatRet(500,"订单不存在");
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }

        $order->update(['status'=>0]);
        return formatRet(0,'成功');
    }

    public function  UpdateData(UpdateOrderRequest $request,$order_id )
    {
        app('log')->info('修改出库单数据',['order_id'=>$order_id,'warehouse_id' =>$request->warehouse_id]);
        $order = Order::where('warehouse_id',$request->warehouse_id)->find($order_id);
        if(!$order){
            return formatRet(500,"订单不存在");
        }
        try {
            app('order')->updateData($request,$order);
            app('db')->commit();
            return formatRet(0,'成功');
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('修改出库单数据失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '修改出库单数据失败');
        }
    }
}
