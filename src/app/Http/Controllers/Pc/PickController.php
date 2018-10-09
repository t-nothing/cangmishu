<?php

namespace App\Http\Controllers\Pc;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\Rules\PageSize;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Models\Pick;
use App\Jobs\NotifyExpress;
use App\Jobs\NotifyThirdParty;

class PickController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'                 => 'integer|min:1',
            'page_size'            => new PageSize,
            'warehouse_feature_id' => 'integer',
            'status'               => 'string|max:20',
            'shipment_num'         => 'string|max:50',
            'out_sn'               => 'string|max:50',
            'express_num'          => 'string|max:50',
            'postcode'             => 'string|max:10',
            'delivery_date_b'      => 'date_format:Y-m-d H:i:s',
            'delivery_date_e'      => 'date_format:Y-m-d H:i:s|after:delivery_date_b',
        ]);

        $user = app('auth')->user();
        $warehouse = app('auth')->warehouse();

        $pick = Pick::with([
            'order:id,out_sn,mask_code,express_code,express_num,receiver_postcode,delivery_date,line_name,remark,created_at',
        ])->ofWarehouse($warehouse->id)->where('status', '!=', Pick::STATUS_CANCEL);

        $request->filled('warehouse_feature_id') AND
            $pick->where('warehouse_feature_id', $request->warehouse_feature_id);

        $request->filled('shipment_num') AND
            $pick->where('shipment_num', $request->shipment_num);

        $request->filled('status') AND
            $pick->whereIn('status', explode(',', $request->status));

        if ($request->hasAny(['out_sn', 'express_num', 'postcode', 'delivery_date_b', 'delivery_date_e'])) {
            // $order = Order::where('status', '!=', 0);
            $order = Order::newModelInstance()->newQuery();

            $request->filled('out_sn') AND
                $order->where('out_sn', $request->out_sn);

            $request->filled('express_num') AND
                $order->where('express_num', $request->express_num);

            $request->filled('postcode') AND
                $order->where('receiver_postcode', $request->postcode);

            $request->filled('delivery_date_b') AND
                $order->where('delivery_date', '>=', strtotime($request->delivery_date_b));

            $request->filled('delivery_date_e') AND
                $order->where('delivery_date', '<=', strtotime($request->delivery_date_e));

            $ids = $order->get()->pluck('id')->toArray();

            $pick->whereIn('order_id', $ids);
        }

        $picks = $pick->paginate($request->input('page_size'));

        return formatRet(0, '', $picks->toArray());
    }

    /**
     * 打印拣货单
     */
    public function printPick(Request $request)
    {
        $this->validate($request, [
            'shipment_nums'   => 'required|array',
            'shipment_nums.*' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $picks = [];

        foreach ($request->shipment_nums as $shipment_num) {
            $pick = Pick::with([
                'order:id,out_sn,remark,owner_id,warehouse_id,receiver_postcode',
                'feature:id,name_cn,name_en',
                'orderItems:id,owner_id,warehouse_id,order_id,relevance_code,amount,shipment_num',
            ])->ofWarehouse($warehouse->id)->where('shipment_num', $shipment_num)->first([
                'id',
                'order_id',
                'warehouse_feature_id',
                'shipment_num',
            ]);

            if (! $pick) {
                // continue;
                return formatRet(500, "拣货单($shipment_num)不存在");
            }

            if (! $pick->order) {
                // continue;
                return formatRet(500, "数据异常，拣货单($shipment_num)丢失了与出库单的联系");
            }

            try {
                Pick::where('id', $pick->id)->where('status', Pick::STATUS_DEFAULT)->update(['status' => Pick::STATUS_PICKING]);

                if (Order::where('id', $pick->order->id)->where('status', Order::STATUS_DEFAULT)->update(['status' => Order::STATUS_PICKING])) {

                    $history = OrderHistory::addHistory($pick->order, Order::STATUS_PICKING);

                    // 开始拣货了
                    // 通知物流系统
                    dispatch(new NotifyExpress($pick->order, $history));
                    // 通知商城系统
                    dispatch(new NotifyThirdParty($pick->order, $history));
                }

                app('db')->commit();
            } catch (\Exception $e) {
                app('db')->rollback();

                app('log')->info('打印拣货单出错了:'.$e->getMessage());
                return formatRet(500, '系统错误，更新状态失败');
            }

            $picks[] = $pick->toArray();
        }

        return formatRet(0, '', $picks);
    }

    /**
     * 打印打包单
     */
    public function printPack(Request $request)
    {
        $this->validate($request, [
            'shipment_nums'   => 'required|array',
            'shipment_nums.*' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $data = [];

        foreach ($request->shipment_nums as $shipment_num) {
            $pick = Pick::with([
                'order:id,out_sn,user_app_id,delivery_date,receiver_country,receiver_city,receiver_postcode,receiver_doorno,receiver_address,receiver_fullname',
                'order.userApp:id,type_pack',
                'orderItems:id,owner_id,warehouse_id,order_id,relevance_code,amount,shipment_num,name_cn,name_en',
            ])->where('shipment_num', $shipment_num)
              ->first([
                'id',
                'order_id',
                'warehouse_feature_id',
                'shipment_num',
            ]);

            if (! $pick) {
                return formatRet(500, '拣货单号'.$shipment_num.'不存在');
            }

            $data[] = $pick->toArray();
        }

        return formatRet(0, '', $data);
    }

    /**
     * 打印快递单
     */
    public function printExpress(Request $request)
    {
        $this->validate($request, [
            'shipment_num' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $shipment_num = $request->shipment_num;

        $pick = Pick::with('order')
            ->ofWarehouse($warehouse->id)
            ->where('shipment_num', $shipment_num)
            ->first();

        if (! $pick)  {
            return formatRet(500, '拣货单不存在');
        }

        $old_status = $pick->status;

        if ($pick->status == Pick::STATUS_CANCEL) {
            return formatRet(500, '拣货单已取消');
        }

        if (! in_array($pick->status, [Pick::STATUS_PICK_DONE, Pick::STATUS_WAITING, Pick::STATUS_SENDING, Pick::STATUS_SUCCESS])) {
            return formatRet(500, '请先拣货');
        }

        if ($pick->verify_status == Pick::VERIFY_STATUS_INIT) {
            return formatRet(500, '请先验货');
        }

        $order = $pick->order;

        if (! $order) {
            return formatRet(500, '系统查不到此拣货单所属的出库单');
        }

        if ($order->status == Order::STATUS_CANCEL) {
            return formatRet(500, '出库单已取消');
        }

        switch ($express_code = $order->express_code) {
            case 'nle':
            case 'express':
            case 'agency':
                $label_data = $order->toArray();
                break;
            case 'postnl':
            case 'eax':
                $label_data = app('eax')->getLabelData($order->express_num);
                break;
            default:
                return formatRet(500, '出库单快递公司是错误的');
        }

        if (! $order->orderItems) {
            return formatRet(500, '出库单物品信息是空的');
        }

        $label_data['logos'] = $order->orderItems->filter(function ($value, $key) {
            return ! empty($value);
        })->unique('feature.logo')->pluck('feature.logo');

        // 打印快递单，状态由拣货完成更新为待出库
        app('db')->beginTransaction();
        try {
            if ($pick->status == Pick::STATUS_PICK_DONE) {
                $pick->status = Pick::STATUS_WAITING;
                $pick->save();
            }

            $count_picking = Pick::where('order_id', $order->id)
                ->count();

            $count_waiting = Pick::where('order_id', $order->id)
                ->where('status', Pick::STATUS_WAITING)
                ->count();

            if ($order->status == Order::STATUS_PICK_DONE && $count_picking == $count_waiting) {
                $order->status = Order::STATUS_WAITING;
                $order->save();

                $history = OrderHistory::addHistory($order, Order::STATUS_WAITING);

                // 开始拣货了
                // 通知物流系统
                dispatch(new NotifyExpress($order, $history));
                // 通知商城系统
                dispatch(new NotifyThirdParty($order, $history));
            }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '更新出库单和拣货单状态为待出库失败');
        }

        return formatRet(
            0,
            in_array($old_status, [Pick::STATUS_WAITING, Pick::STATUS_SENDING, Pick::STATUS_SUCCESS]) ? '重复打印快递单' : '',
            compact('express_code', 'shipment_num', 'label_data')
        );

        // $labelPath = '/labels/postnl/' . date('Ymd', strtotime($orderInfo['created_at'])) . '/' . $orderInfo['express_num'] . '.pdf';

        //     if (Flysystem::has($labelPath)) {
        //         $content = Flysystem::read($labelPath);
        //     } else {
        //         $totalWeight = 0;

        //         $orderItems = OrderItem::where('shipment_num', $shipment_num)->get();

        //         if ($orderItems) {
        //             foreach ($orderItems as $orderItem) {
        //                 $orderItem->load(['productstock' => function ($query) use ($orderItem) {
        //                     $query->where('shelf_num', '>=', $orderItem->amount);
        //                 }]);
        //             }
        //         }

        //         $orderItemInfo = $orderItems->toArray();
        //         if (!empty($orderItemInfo)) {// 计算重量信息
        //             foreach ($orderItemInfo as $val) {
        //                 $totalWeight += $val['weight'];
        //             }
        //         }

        //         $orderInfo['goods_weight'] = $totalWeight;
        //         // 生成物流单
        //         $tmp = explode(" ", $orderInfo['receiver_fullname']);
        //         $firstName = array_shift($tmp);
        //         $postnlData['receiverName'] = implode(' ', $tmp);
        //         $postnlData['receiverFirstName'] = $firstName;
        //         $postnlData['receiverStreet'] = $orderInfo['receiver_address'];
        //         $postnlData['receiverHouseNr'] = $orderInfo['receiver_doorno'];
        //         $postnlData['receiverCity'] = $orderInfo['receiver_city'];
        //         $postnlData['receiverZipcode'] = $orderInfo['receiver_postcode'];
        //         $postnlData['receiverCountrycode'] = $orderInfo['receiver_country'];
        //         $postnlData['receiverEmail'] = $orderInfo['receiver_email'];
        //         $postnlData['receiverSMSNr'] = $orderInfo['receiver_phone'];
        //         $postnlData['receiverTelNr'] = $orderInfo['receiver_phone'];
        //         $postnlData['weight'] = $orderInfo['goods_weight'];
        //         $postnlData['barcode'] = $orderInfo['express_num'];
        //         $postnlData['created_at'] = $orderInfo['created_at'];
        //         $labelInfo = app("PostnlService")->generateLabel($postnlData);

        //         if ($labelInfo['status'] != 0) {
        //             return formatRet('1', $labelInfo['msg']);
        //         }

        //         if (Flysystem::has($labelPath)) {// 生成后再看下
        //             $content = Flysystem::read($labelPath);
        //         } else {
        //             return formatRet('1', $orderInfo['express_num'] . '物流单还未生成，请联系开发处理！');
        //         }
        //     }
        //     $data['deliver_type'] = $orderInfo['delivery_type'];
        //     $data['label_data'] = base64_encode($content);
        // } else {
        //     $data['deliver_type'] = $orderInfo['delivery_type'];
        //     $data['label_data'] = $orderInfo;
        // }
        // if ($orderInfo['status']) {//更改订单状态 == Order::STATUS_PICKING
//                $responseData = app("MallService")->notifyShipped($orderInfo);
//
//                if ($responseData['ret'] == 1) {
//                    $http = new Client(['verify' => false]);
//                    $trackingUrl ='https://dev-tracking.nle-tech.com/api/NleExpress/inputExpressInfo';
//                    $trackingApiData = [
//                        'internationalExpressNumber' => $orderInfo['express_num'],
//                        'context' => '包裹捡货完毕，打包完毕，待派送',
//                    ];
//
//                    $http->post($trackingUrl, [
//                        'form_params' => $trackingApiData
//                    ]);
//
//                    Order::where('id', $orderInfo['id'])->update(['status' => Order::STATUS_WAITING]);
//                } else {
//                    return formatRet('1', '通知商城修改订单状态失败:' . $responseData['msg']);
//                }
            // TODO 更改订单状态 暂不推送到商城
        // }

        //添加出库单记录

        // if (ProductStockLog::where('order_sn',$orderInfo['express_num'])->where('type_id',ProductStockLog::TYPE_OUTPUT)->first()){
        //     foreach($orderInfo['pick'] as $key=>$value){
        //         $stock=ProductStock::getIns()->getOne($value['product_stock_id']);
        //         
        //         $addLog = [
        //             'warehouse_id' => $orderInfo['warehouse_id'],
        //             'operator' => Auth::id(),
        //             'order_sn' => $orderInfo['express_num'],
        //             'owner_id' => $stock['distributor_id'],
        //             'operation_num' => $value['pick_num'],
        //             'spec_id' => $stock['spec_id'],
        //             'sku' => $stock['sku'],
        //             'type_id' => ProductStockLog::TYPE_OUTPUT
        //         ];
        //         ProductStockLog::getIns()->addLog($addLog);
        //     }
        // }
    }
}
