<?php
/**
 * 在线下单
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Requests\BaseRequests;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderType;
use App\Http\Requests\CreateThirdPartyOrderRequest;

class OrderController extends Controller
{

    /**
     * 结算提交订单
     **/
    public function checkout(CreateThirdPartyOrderRequest $request)
    {
        app('log')->info('第三方API接口下单',$request->all());
        app('db')->beginTransaction();
        $outSn = "";
        try {

            $data = new BaseRequests;            

            $data->express_code  = "";
            $data->remark           = $request->input('remark', '');
            $data->shop_id          = $request->shop->id;
            $data->shop_user_id     = Auth::user()->id;
            $data->warehouse_id     =  $request->shop->warehouse_id;
            $data->order_type       = OrderType::where('warehouse_id', $request->shop->warehouse_id)->oldest()->first()->id??0;
            $data->shop_remark      = "";
            $data->express_num      = "";
            $data->sale_currency    =  $request->shop->default_currency;


            $data->receiver = new ReceiverAddress([
                "country"       =>  $request->country,
                "province"      =>  $request->province,
                "city"          =>  $request->city,
                "postcode"      =>  $request->postcode,
                "district"      =>  $request->district,
                "address"       =>  $request->address,
                "fullname"      =>  $request->fullname,
                "phone"         =>  $request->phone,
            ]);

            //查找店铺默认发件人

            $data->sender = new SenderAddress([
                "country"       =>  $request->shop->senderAddress->country,
                "province"      =>  $request->shop->senderAddress->province,
                "city"          =>  $request->shop->senderAddress->city,
                "postcode"      =>  $request->shop->senderAddress->postcode,
                "district"      =>  $request->shop->senderAddress->district,
                "address"       =>  $request->shop->senderAddress->address,
                "fullname"      =>  $request->shop->senderAddress->fullname,
                "phone"         =>  $request->shop->senderAddress->phone
            ]);



            $orderItem = [];
            foreach(app('cart')->name($this->getWhoesCart())->all($request->id) as $row)  {
                $orderItem[] = [
                    'relevance_code'    =>  $row->relevance_code,
                    'pic'               =>  $row->pic,
                    'num'               =>  $row->qty,
                    'sale_price'        =>  $row->price,
                ];
            }

           
            $data->goods_data = collect($orderItem);
            
            $orderResult = app('order')->setSource($request->shop->name_cn)->create($data, $request->shop->owner_id);
            app('db')->commit();

            $outSn =  $orderResult->out_sn;

            app('cart')->name($this->getWhoesCart())->removeBy($request->id);

            event(new CartCheckouted($orderResult));

        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->error('下单失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '下单失败:'.$e->getMessage());
        }


        return formatRet(0,'下单成功',[
            'out_sn'  =>  $outSn
        ]);
    }
}
