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
use App\Models\ProductStock;
use App\Models\ProductStockLog;
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
        $order = Order::ofWarehouse($request->warehouse_id)
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

        $orders = $order->latest()->paginate($request->input('page_size',10));

        foreach ($orders  as $k => $v) {
            $sum = 0;
            foreach ($v->orderItems as $k1 => $v1) {
                $sum += $v1->amount;
            }
            $v->load(['orderItems:id,name_cn,name_en,amount,relevance_code,product_stock_id,order_id,pick_num,sale_price','orderItems.stock:id', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser']);
            $v->append(['out_sn_barcode', 'sub_pick_num', 'sub_order_qty']);

            $v->setHidden(['receiver_email,receiver_country','receiver_province','receiver_city','receiver_postcode','receiver_district','receiver_address','send_country','send_province','send_city','send_postcode','send_district','send_address','is_tobacco','mask_code','updated_at','line_name','line_id']);
            $v->sum = $sum;
        }

        return formatRet(0, '', $orders->toArray());
    }

    public function show(BaseRequests $request, $order_id)
    {
        $order = Order::find($order_id);
        if(!$order){
            return formatRet("500",'找不到该出库单');
        }
        $order->load(['orderItems:id,name_cn,name_en,amount,relevance_code,product_stock_id,order_id,pick_num,sale_price','orderItems.stock:id', 'warehouse:id,name_cn', 'orderType:id,name', 'operatorUser']);
        $order->append(['out_sn_barcode']);

        $order->setHidden(['receiver_email,receiver_country','receiver_province','receiver_city','receiver_postcode','receiver_district','receiver_address','send_country','send_province','send_city','send_postcode','send_district','send_address','is_tobacco','mask_code','updated_at','line_name','line_id']);
        $order = $order->toArray();

       return formatRet(0,"成功",$order);
    }


    public function store(CreateOrderRequest $request)
    {
        app('log')->info('新增出库单',$request->all());
        app('db')->beginTransaction();
        try {
            $order = app('order')->create($request);
            if(!isset($order->out_sn))
            {
                throw new \Exception("出库单新增失败", 1);
                
            }
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

        app('log')->info('新增出库拣货单',$request->all());
        app('db')->beginTransaction();
        try {
            app("store")->pickAndOut($request->all());
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('出库拣货失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '出库拣货失败:'.$e->getMessage());
        }
        
        return formatRet(0,'出库拣货成功');

        

    }

    /**
     * 取消订单
     */
    public function cancelOrder(BaseRequests $request,$order_id)
    {
        app('log')->info('request',$request->all());
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

        $order->update(['status'=>Order::STATUS_CANCEL]);
        return formatRet(0,'成功');
    }

    /**
     * 更新运单号
     */
    public function updateExpressNumber(BaseRequests $request,$order_id)
    {
        $this->validate($request,[
            'delivery_type'               => 'string|string|max:255',
            'express_num'                 => 'string|max:255',
        ]);

        $order = Order::find($order_id);
        if(!$order){
            return formatRet(500,"订单不存在");
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }

        $order->update(
            [
                'delivery_type'=>$request->delivery_type,
                'express_num'=>$request->express_num,
            ]
        );
        return formatRet(0,'成功');
    }

     /**
     * 更新支付价格
     */
    public function updatePayStatus(BaseRequests $request,$order_id)
    {
        $this->validate($request,[
            'pay_status'              => 'required|integer|min:0',
            'sub_pay'                 => 'required|numeric|min:0',
        ]);
        
        $order = Order::find($order_id);
        if(!$order){
            return formatRet(500,"订单不存在");
        }
        if ($order->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }

        $order->update(
            [
                'pay_status'=>$request->pay_status,
                'sub_pay'=>$request->sub_pay,
            ]
        );
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
