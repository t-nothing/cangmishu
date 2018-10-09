<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderType;
use App\Models\ProductSpec;
use App\Models\UserApp;
use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Models\UserSender;
use App\Models\UserReceiver;
use App\Models\WarehouseEmployee;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 出库单列表
     *
     * @author xiongshi
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
            'warehouse_id' => 'integer|min:1',
            'created_at_b' => 'date:Y-m-d',
            'created_at_e' => 'date:Y-m-d',
            'keywords' => 'string',
        ]);

        $order = Order::with(['orderItems', 'warehouse', 'orderType', 'operatorUser'])
            ->ofWarehouse($this->warehouse->id);
	
        if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER) {
            $order->whose(app('auth')->id());
        }

        if ($request->filled('created_at_b')) {
            $order->where('created_at', '>', strtotime($request->created_at_b));
        }

        if ($request->filled('created_at_e')) {
            $order->where('created_at', '<', strtotime($request->created_at_e));
        }

        if ($request->filled('keywords')) {
            $order->hasKeywords($request->keywords);
        }

        $orders = $order->latest()->paginate($request->input('page_size'))->toArray();

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

    public function show($order_id)
    {
        $order = Order::ofWarehouse($this->warehouse->id);
    
        if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER) {
            $order->whose(app('auth')->id());
        }

        $order = $order->findOrFail($order_id);

        $order->load(['orderItems', 'warehouse', 'orderType', 'operatorUser']);

        return formatRet(0, '', $order->toArray());
    }

    /**
     * 出库单 - 添加
     */
    public function store(Request $request)
    {
 //        $this->validate($request, [
 //            'warehouse_id'                => 'required|integer|min:1',
 //            // 出库单数据
 //            'order_code'                  => 'required|string|max:255',
 //            'order_type'                  => 'required|integer|min:1',
 //            'out_sn'                      => 'required|string|min:1',
 //            'goods_data'                  => 'required|array',
 //            'goods_data.*.relevance_code' => 'required|string|distinct',
 //            'goods_data.*.num'            => 'required|integer|min:1',
 //            // 快递单数据
 //            'delivery_date'               => 'required|string|date_format:Y-m-d',
 //            'delivery_type'               => 'required|integer|in:1,2,3',
 //            'receiver_id'                 => 'required|integer|min:1',
 //            'sender_id'                   => 'required|integer|min:1',
 //        ]);

 //        $user_id = Auth::id();

 //        $order = new Order;
	// $warehouse = app('auth')->warehouse();  
 //        $this->validate($request, [
 //            'out_sn' => Rule::unique($order->getTable())->where(function ($query) use ($request, $user_id) {
 //                return $query->where('owner_id', $user_id)->where('out_sn', $request->out_sn);
 //            }),
 //            'order_code' => Rule::unique($order->getTable())->where(function ($query) use ($request, $user_id) {
 //                return $query->where('owner_id', $user_id)->where('order_code', $request->out_sn);
 //            }),
 //        ]);

 //        // 分配仓库
 //        if (! $warehouse) {
 //            return formatRet(404, '仓库不存在');
 //        }

 //        if (! $orderType = OrderType::ofWarehouse($warehouse->id)
 //            ->where('is_enabled', 1)
 //            ->where('id', $request->order_type)) {
 //            return formatRet(404, '出库单分类不正确');
 //        }

 //        $orderItems = [];
 //        $order_num = 0;

 //        foreach ($request->goods_data as $k => $v) {
 //            $orderItems[] = [
 //                'amount' => $v['num'],
 //                'relevance_code' => $v['relevance_code'],
 //            ];

 //            $order_num += $v['num'];
 //        }

 //        if (! $sender = UserSender::where('user_id', $user_id)->where('id', $request->sender_id)->first()) {
 //            return formatRet(404, '发件人不存在');
 //        }

 //        if (! $receiver = UserReceiver::where('user_id', $user_id)->where('id', $request->receiver_id)->first()) {
 //            return formatRet(404, '收件人不存在');
 //        }

 //        $order->owner_id       = $user_id;
 //        $order->order_code     = $request->order_code;
 //        $order->order_type     = $request->order_type;
 //        $order->delivery_date  = $request->delivery_date;
 //        $order->delivery_type  = $request->delivery_type;
 //        $order->source         = 'web';
 //        $order->out_sn         = $request->out_sn;
 //        $order->warehouse_id   = $warehouse->id;
 //        $order->status         = Order::ORDER_STATUS_AFFIRM;

 //        // 收件人信息
 //        $order->receiver_country  = $receiver->country;
 //        $order->receiver_province = $receiver->province;
 //        $order->receiver_city     = $receiver->city;
 //        $order->receiver_postcode = $receiver->postcode;
 //        $order->receiver_doorno   = $receiver->doorno;
 //        $order->receiver_address  = $receiver->address;
 //        $order->receiver_fullname = $receiver->fullname;
 //        $order->receiver_phone    = $receiver->phone;
        
 //        // 发件人信息
 //        $order->send_country  = $sender->country;
 //        $order->send_city     = $sender->city;
 //        $order->send_postcode = $sender->postcode;
 //        $order->send_doorno   = $sender->doorno;
 //        $order->send_address  = $sender->address;
 //        $order->send_fullname = $sender->fullname;
 //        $order->send_phone    = $sender->phone;

 //        if (! $order->save()) {
 //            return formatRet(500, '出库单新增失败');
 //        }

 //        // 生成拣货单号和快递单号
 //        $shipment_num = $this->user_pick_no($warehouse->id, $order->id);
 //        $express_num = $this->user_no($warehouse->id, $order->id, $order->delivery_type);

 //        if ($orderItems) {
 //            foreach ($orderItems as $k => $v) {
 //                $orderItems[$k]['order_id'] = $order->id;
 //                $orderItems[$k]['shipment_num'] = $shipment_num;
 //                $orderItems[$k]['express_num'] = $express_num;
 //                $orderItems[$k]['created_at'] = $order->created_at;
 //                // $orderItems[$k]['wms_sku']      = '';
 //            }
 //        }

 //        if (! OrderItem::insert($orderItems)) {
 //            return formatRet(500, '出库单新增时保存订单商品信息失败');
 //        }

 //        $reData = [
 //            'shipment_num' => $shipment_num,
 //            'express_num' => $express_num,
 //        ];

 //        //增加 订单物流单号和打包单号更新 2018/05/15 xs
 //        $orderUpdate = Order::where('id', $order->id)->update([
 //            'shipment_num' => $shipment_num,
 //            'express_num' => $express_num
 //        ]);

 //        if (! $orderUpdate) {
 //            return formatRet(500, '出库单物流单号和打包单号更新失败');
 //        }

        return formatRet(0, '', $reData);
    }
}
