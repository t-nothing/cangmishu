<?php
/**
 * 店铺订单
 */

namespace App\Http\Controllers\Open\Shop;

use App\Events\CartCheckouted;
use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequests;
use App\Models\OrderType;
use App\Models\ProductSpec;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Models\ShopProductSpec;
use App\Rules\PageSize;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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

        $dataList = Order::getIns()
            ->ofShopUser($request->shop->id, Auth::user()->id)
            ->when($request->filled('status'), function ($query) use ($request) {
                $this->parseStatus($request->input('status'), $query);
            })
            ->orderBy('id', 'DESC')
            ->with('orderItems:order_id,name_cn,amount,sale_price,sale_currency,spec_name_cn,pic,relevance_code')
            ->paginate(
                $request->input('page_size', 50),
                [
                    'id', 'out_sn', 'status', 'remark', 'express_code', 'delivery_date', 'express_code',
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
                    'sale_currency',
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

    protected function parseStatus($status, Builder $query)
    {
        switch ($status) {
            case 1:
            case 5:
            case 7:
                $query->where('status', $status);
                break;
            case 2:
                $query->whereIn('status', [
                    Order::STATUS_PICKING,
                    Order::STATUS_PICK_DONE,
                    Order::STATUS_WAITING,
                ]);
                break;
            default:
                $query->where('status', $status);
        }
    }

    /**
     * 直接下单
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Throwable
     */
    public function store(BaseRequests $request)
    {
        $data = $request->validate([
            'specs' => 'required|array',
            'specs.*.id' => 'required',
            'specs.*.qty' => 'required|integer|gt:0',
            'verify_money'  => 'required|numeric|min:0',
            'fullname'      => 'required|string',
            'phone'         => 'required|string',
            'country'       => 'required|string',
            'province'      => 'required|string',
            'city'          => 'required|string',
            'district'      => 'required|string',
            'address'       => 'required|string',
            'postcode'      => 'string',
            'remark'        => 'string',
        ]);

        info('商品直接下单', $request->all());

        app('db')->beginTransaction();

        try {
            $specs = ShopProductSpec::query()
                ->with('productSpec')
                ->whereIn('spec_id', Arr::pluck($data['specs'], '*.id'))
                ->get();

            if ($specs->isEmpty()) {
                throw new \Exception('下单商品不存在', 1);
            }

            $totalPrice = $specs->sum(function ($spec) use ($data) {
                return $spec['sale_price'] * collect($data['specs'])->firstWhere('id', $spec['spec_id'])['qty'] ?? 1;
            });

            if ($request->verify_money != $totalPrice) {
                info('验证价格Debug', [
                    'request' => $request->verify_money,
                    'total' => $totalPrice,
                ]);

                throw new \Exception("下单金额不一致", 1);
            }

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
            foreach($specs as $spec)  {
                $orderItem[] = [
                    'relevance_code'    =>  $spec->productSpec->relevance_code,
                    'pic'               =>  $spec->productSpec->pic,
                    'num'               =>  collect($data['specs'])
                            ->firstWhere('id', $spec['spec_id'])['qty'] ?? 1,
                    'sale_price'        =>  $spec->sale_price,
                ];
            }

            $data->goods_data = collect($orderItem);

            $orderResult = app('order')
                ->setSource($request->shop->name_cn)
                ->create($data, $request->shop->owner_id);

            app('db')->commit();

            $outSn =  $orderResult->out_sn;
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
