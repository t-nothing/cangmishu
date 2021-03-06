<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Open\Api;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderType;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Models\Order;
use App\Http\Requests\CreateThirdPartyOrderRequest;
use App\Http\Requests\GetThirdPartyOrderRequest;


class OrderController extends Controller
{

    /**
     * 第三方下单
     **/
    public function store(CreateThirdPartyOrderRequest $request)
    {
        app('log')->info('第三方API接口下单',$request->all());
        app('db')->beginTransaction();

        try {

            $data = new BaseRequests;            

            $data->express_code  = "";
            $data->remark           = $request->remark??'';
            $data->warehouse_id     = Auth::warehouseId();
            $data->order_type       = OrderType::where('warehouse_id', Auth::warehouseId())->oldest()->first()->id??0;
            $data->shop_remark      = "";
            $data->express_num      = "";
            $data->sale_currency    =  $request->items[0]->sale_currency??'CNY';
            $data->out_sn           = $request->out_sn??'';

            $data->receiver = new ReceiverAddress([
                "country"       =>  $request->receiver_country,
                "province"      =>  $request->receiver_province,
                "city"          =>  $request->receiver_city,
                "postcode"      =>  $request->receiver_postcode,
                "district"      =>  $request->receiver_district,
                "address"       =>  $request->receiver_address,
                "fullname"      =>  $request->receiver_fullname,
                "phone"         =>  $request->receiver_phone,
            ]);

            //查找店铺默认发件人

            $data->sender = new SenderAddress([
                "country"       =>  $request->sender_country,
                "province"      =>  $request->sender_province,
                "city"          =>  $request->sender_city,
                "postcode"      =>  $request->sender_postcode,
                "district"      =>  $request->sender_district,
                "address"       =>  $request->sender_address,
                "fullname"      =>  $request->sender_fullname,
                "phone"         =>  $request->sender_phone
            ]);



            $orderItem = [];
            foreach($request->items as $item)  {
                $orderItem[] = [
                    'relevance_code'    =>  $item["sku"],
                    'pic'               =>  $item["pic"]??'',
                    'num'               =>  $item["qty"],
                    'sale_price'        =>  $item["sale_price"]??0,
                    'sale_currency'        =>  $item["sale_currency"]??'CNY',
                ];
            }

            $data->goods_data = collect($orderItem);
            
            $orderResult = app('order')->setSource($request->source??'API')->create($data, Auth::OwnerId());
            app('db')->commit();

            $outSn =  $orderResult->out_sn;

        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('下单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, trans("message.openOrderAddFailed", ["message"=>$e->getMessage()]));
        }


        return formatRet(200, trans("message.openOrderAddSuccess"),[
            'out_sn'  =>  $outSn
        ]);
    }

    /**
     * 查询订单
     **/
    public function show(GetThirdPartyOrderRequest $request)
    {
        $order = Order::with(['orderItems:order_id,name_cn,spec_name_cn,relevance_code as sku,amount as qty,sale_price,sale_currency','warehouse:id,name_cn', 'orderType:id,name'])->ofWarehouse(Auth::warehouseId())->where('out_sn',$request->out_sn)->select('id','out_sn','source','status','remark','shop_remark','express_code','delivery_date','receiver_country','receiver_city','receiver_postcode','receiver_district','receiver_address','receiver_fullname','receiver_phone','send_country','send_city','send_postcode','send_district','send_address','send_fullname','send_phone','receiver_province','created_at','order_type','express_num','warehouse_id','verify_status','send_province','sub_total','sub_pay','pay_currency','pay_status','pay_type','payment_account_number','sale_currency','sub_order_qty')->get();
        if(!$order){
            return formatRet(500, trans("message.openOrderNotExist"));
        }

        $order = $order->toArray();

       return formatRet(200, trans("message.success"),$order);
    }

    /**
     * 取消订单
     **/
    public function cancel(GetThirdPartyOrderRequest $request)
    {
        $order = Order::ofWarehouse(Auth::warehouseId())->where('out_sn',$request->out_sn)->first();
        if(!$order){
            return formatRet(500,trans("message.openOrderNotExist"));
        }

        if($order->status != Order::STATUS_DEFAULT){
            return formatRet(500, trans("message.openOrderCancelFailed"));
        }
        $order->status = Order::STATUS_CANCEL;
        $order->save();

       return formatRet(200, trans("message.success"));
    }
}
