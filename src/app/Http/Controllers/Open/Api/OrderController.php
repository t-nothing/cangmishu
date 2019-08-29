<?php
/**
 * 在线下单
 */

namespace App\Http\Controllers\Open\Api;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderType;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Http\Requests\CreateThirdPartyOrderRequest;

class OrderController extends Controller
{

    /**
     * 结算提交订单
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
            return formatRet(500, '下单失败:'.$e->getMessage());
        }


        return formatRet(200,'下单成功',[
            'out_sn'  =>  $outSn
        ]);
    }
}
