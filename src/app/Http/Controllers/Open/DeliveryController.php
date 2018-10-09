<?php

namespace App\Http\Controllers\Open;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Pick;
use App\Models\OrderHistory;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 包裹 - 获取包裹信息
     */
    public function package(Request $request)
    {
        app('log')->info('API 包裹信息', $request->input());

        $this->validate($request, [
        	'express_nums' 	 => 'required|array',
            'express_nums.*' => 'required|string|distinct',
        ]);

        $warehouse = $this->warehouse;

        foreach ($request->express_nums as $express_num) {
        	$order = Order::with('picks.feature')
	                      ->whose(app('auth')->id())
	                      ->ofWarehouse($warehouse->id)
	                      ->where('express_num', $express_num)
	                      ->first();

            $d = [
            	'express_num' => $express_num,
            	'result' => null,
            ];

	        if ($order && isset($order->picks)) {
	        	$d['result'] = [
	        		'mask_code' => $order->mask_code,
		        	'picks' => [],
	        	];

	        	foreach ($order->picks as $pick) {
	        		$d['result']['picks'][] = [
	        			'shipment_num' => $pick['shipment_num'],
	        			'feature_logo' => $pick['feature']['logo'],
	        		];
	        	}
	        }

	        $data[] = $d;
        }

        return formatRet(0, '', $data);
    }

    /**
     * 配送 - 包裹出库，开始配送
     */
    public function delivery(Request $request)
    {
        app('log')->info('API 包裹出库', $request->input());

        $this->validate($request, [
        	'data' 				     => 'required|array',
            'data.*.express_num' 	 => 'required|string|distinct',
            'data.*.shipment_nums' 	 => 'required|array',
            'data.*.shipment_nums.*' => 'required|string|distinct',
        ]);

        $warehouse = $this->warehouse;

        $data = [];

        foreach ($request->data as $d) {

        	$order = Order::with('picks')
                ->whose(app('auth')->id())
                ->ofWarehouse($warehouse->id)
                ->where('express_num', $d['express_num'])
                ->first();

            if (empty($order)) {
            	$data[] = [
            		'express_num' => $d['express_num'],
            		'result' => false,
            		'msg' => '快递单号不正确',
            	];

            	continue;
            }

            if ($order->status != Order::STATUS_WAITING) {
                $data[] = [
                    'express_num' => $d['express_num'],
                    'result' => false,
                    'msg' => '状态不是待出库',
                ];

                continue;
            }

            $snInRq = $d['shipment_nums'];
            $snInDb = $order->picks->pluck('shipment_num')->toArray();
            sort($snInRq);
            sort($snInDb);

            if ($snInRq === $snInDb) {

                // 包裹已取齐
                $data[] = [
                	'express_num' => $d['express_num'],
                	'result' => true,
                	'msg' => '出库成功',
                ];

                app('db')->beginTransaction();
                try {
                    // 更新出库单
                    Order::where('id', $order->id)->where('status', Order::STATUS_WAITING)->update([
                        'status' => Order::STATUS_SENDING,
                    ]);

                    // 更新拣货单
                    Pick::where('order_id', $order->id)->where('status', Pick::STATUS_WAITING)->update([
                        'status' => Pick::STATUS_SENDING,
                    ]);

                    OrderHistory::addHistory($order, Order::STATUS_SENDING);

                    app('db')->commit();
                } catch (\Exception $e) {
                    app('db')->rollback();

                    // 数据库异常
                    $data[] = [
                        'express_num' => $d['express_num'],
                        'result' => false,
                        'msg' => '操作失败',
                    ];
                }

            } else {
                // 包裹未取齐
                $data[] = [
                    'express_num' => $d['express_num'],
                    'result' => false,
                    'msg' => '包裹未取齐',
                ];
            }
        }

    	return formatRet(0, '', $data);
    }

    /**
     * 客户签收
     */
    public function receipt(Request $request)
    {
        app('log')->info('API 包裹签收', $request->input());

        $this->validate($request, [
            'data'                   => 'required|array',
            'data.*.express_num'     => 'required|string|distinct',
            'data.*.shipment_nums'   => 'required|array',
            'data.*.shipment_nums.*' => 'required|string|distinct',
        ]);

        $warehouse = $this->warehouse;

        $data = [];

        foreach ($request->data as $d) {

            $order = Order::with('picks')
                ->whose(app('auth')->id())
                ->ofWarehouse($warehouse->id)
                ->where('express_num', $d['express_num'])
                ->first();

            if (empty($order)) {
                $data[] = [
                    'express_num' => $d['express_num'],
                    'result' => false,
                    'msg' => '快递单号不正确',
                ];

                continue;
            }

            if ($order->status != Order::STATUS_SENDING) {
                $data[] = [
                    'express_num' => $d['express_num'],
                    'result' => false,
                    'msg' => '状态不是配送中',
                ];

                continue;
            }

            $snInRq = $d['shipment_nums'];
            $snInDb = $order->picks->pluck('shipment_num')->toArray();
            sort($snInRq);
            sort($snInDb);

            if ($snInRq === $snInDb) {
                // 包裹已取齐
                $data[] = [
                    'express_num' => $d['express_num'],
                    'result' => true,
                    'msg' => '签收成功',
                ];

                app('db')->beginTransaction();
                try {
                    // 更新出库单
                    Order::where('id', $order->id)->where('status', Order::STATUS_SENDING)->update([
                        'status' => Order::STATUS_SUCCESS,
                    ]);

                    // 更新拣货单
                    Pick::where('order_id', $order->id)->where('status', Pick::STATUS_SENDING)->update([
                        'status' => Pick::STATUS_SUCCESS,
                    ]);

                    OrderHistory::addHistory($order, Order::STATUS_SUCCESS);

                    app('db')->commit();
                } catch (\Exception $e) {
                    app('db')->rollback();
                    return formatRet(500, '操作失败');
                }

            } else {
                // 包裹未取齐
                $data[] = [
                    'express_num' => $d['express_num'],
                    'result' => false,
                    'msg' => '缺少包裹',
                ];
            }
        }

        return formatRet(0, '', $data);
    }
}
