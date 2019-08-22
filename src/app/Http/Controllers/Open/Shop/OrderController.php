<?php
/**
 * 店铺订单
 */

namespace App\Http\Controllers\Open\Shop;
use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequests;
use App\Rules\PageSize;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * 我的订单列表
     **/
    public function list(BaseRequests $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
            'is_enabled'   => 'boolean',
        ]);


        $dataList = Order::getIns()->ofShopUser($request->shop->id, Auth::user()->id)
                    ->orderBy('id','DESC')
                    ->with('orderItems:order_id,name_cn,amount,sale_price,sale_currency,spec_name_cn,pic')
                    ->paginate(
                        $request->input('page_size',50),
                        ['id', 'out_sn', 'status', 'remark', 'express_code', 'delivery_date', 'express_code', 
                            'receiver_country', 
                            'receiver_city',
                            'receiver_postcode',
                            'receiver_district',
                            'receiver_address',
                            'receiver_fullname',
                            'receiver_phone',
                            'receiver_province',
                            'created_at',
                            'updated_at',
                            'sub_pay',
                            'sub_total'
                        ]
                    );

        return formatRet(0, '', $dataList->toArray());
    }

    /**
     * 订单详细
     */
    public function show(BaseRequests $request, $id)
    {
        app('log')->info('订单详细',['id'=>$id]);
        $order = Order::getIns()->ofShopUser($request->shop->id, Auth::user()->id)->find($id);

        if(!$order){
            return formatRet(404,"订单不存在", 404);
        }

        $order->load("orderItems:order_id,name_cn,amount,sale_price,sale_currency,spec_name_cn,pic");
        $order->setVisible([
                'id', 
                'out_sn', 
                'status', 
                'remark', 
                'express_code', 
                'delivery_date', 
                'delivery_type', 
                'receiver_country', 
                'receiver_city',
                'receiver_postcode',
                'receiver_district',
                'receiver_address',
                'receiver_fullname',
                'receiver_phone',
                'receiver_province',
                'created_at',
                'updated_at',
                'sub_pay',
                'sub_total',
                'orderItems'
            ]);

       
        return formatRet(0, '', $order->toArray());
    }
}
