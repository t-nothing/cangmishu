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
                    ->with('orderItems:order_id,name_cn,amount,sale_price,sale_currency,spec_name_cn,pic,relevance_code')
                    ->paginate(
                        $request->input('page_size',50),
                        ['id', 'out_sn', 'status', 'remark', 'express_code', 'delivery_date', 'express_code',
                            'express_num',
                            'receiver_country',
                            'receiver_city',
                            'receiver_postcode',
                            'receiver_district',
                            'receiver_address',
                            'receiver_fullname',
                            'receiver_phone',
                            'receiver_province',
                            'sub_order_qty',
                            'created_at',
                            'updated_at',
                            'sub_pay',
                            'sub_total',
                            'sale_currency'
                        ]
                    );
        $dataList = $dataList->toArray();
        foreach ($dataList['data'] as $key => &$result) {
            $result["ship"] = $result['status']>3 && !empty($result["express_num"]) ? [
                "express_name" => app("ship")->getExpressName($result["express_code"]),
                "express_num" => $result["express_num"],
            ] : NULL;
        }

        return formatRet(0, '', $dataList);
    }

    /**
     * 订单详细
     */
    public function show(BaseRequests $request, $id)
    {
        app('log')->info('订单详细',['id'=>$id]);
        $order = Order::getIns()->ofShopUser($request->shop->id, Auth::user()->id)->select([
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
                'sub_order_qty',
                'created_at',
                'updated_at',
                'sub_pay',
                'sub_total',
                'express_num',
                'express_code',
                'sale_currency'
            ])->find($id);

        if(!$order){
            return formatRet(404,"订单不存在", []);
        }
        $order->load("orderItems:order_id,name_cn,amount,sale_price,sale_currency,spec_name_cn,pic,relevance_code");

        $result = $order->toArray();
        $result["ship"] = $result['status']>3 && !empty($result["express_num"]) ? [
            "express_name" => app("ship")->getExpressName($result["express_code"]),
            "express_num" => $result["express_num"],
        ] : NULL;

        unset($result['express_num']);
        unset($result['express_code']);
        unset($result['verify_status_name']);
        unset($result['send_full_address']);
        unset($result['delivery_type']);

        return formatRet(0, '', $result);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderCount()
    {
        $counts = [
            'wait_confirm' => Order::query()
                ->ofShopUser(request()->shop->id, Auth::user()->id)
                ->where('status', Order::STATUS_DEFAULT)
                ->count(),
            'wait_ship' => Order::query()
                ->ofShopUser(request()->shop->id, Auth::user()->id)
                ->whereIn('status', [
                        Order::STATUS_PICKING,
                        Order::STATUS_PICK_DONE,
                        Order::STATUS_WAITING,
                    ]
                )->count(),
            'shipped' => Order::query()
                ->ofShopUser(request()->shop->id, Auth::user()->id)
                ->where('status', Order::STATUS_SENDING)
                ->count(),
            'signed' => Order::query()
                ->ofShopUser(request()->shop->id, Auth::user()->id)
                ->where('status', Order::STATUS_SUCCESS)
                ->count(),
        ];

        return formatRet(0, '', $counts);
    }
}
