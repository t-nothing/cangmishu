<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pick;

class OrderController extends Controller
{
    /**
     * 派送失败仓库签收
     *
     * 1. 出库单和它的拣货单更新状态（配送中 -> 待出库）
     * 2. 出库单的预约信息清空（收件人信息，预约出库时间，线路名称）
     */
    public function expressDefeated(Request $request)
    {
        app('log')->info('手持端 - 派送失败，仓库签收', $request->input());

        $this->validate($request, [
            'express_num' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::ofWarehouse($warehouse->id)->where('express_num', $request->express_num)->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        if ($order->status == Order::STATUS_SENDING) {
            // 就算别的系统没有推送状态，只要包裹回到了仓库
            app('db')->beginTransaction();
            try {
                Order::where('id', $order->id)->where('status', Order::STATUS_SENDING)->update([
                    'status' => Order::STATUS_WAITING,
                ]);

                Pick::where('order_id', $order->id)->where('status', Pick::STATUS_SENDING)->update([
                    'status' => Pick::STATUS_WAITING,
                ]);

                app('db')->commit();
            } catch (\Exception $e) {
                app('db')->rollback();
                app('log')->info('手持端 - 派送失败签收', ['exception msg' => $e->getMessage()]);

                return formatRet(500, '失败');
            }

            app('log')->info('手持端 - 派送失败，仓库签收成功。出库单状态是配送中（外部系统未通知更新）');

        } elseif ($order->status == Order::STATUS_WAITING) {
            // 不做任何操作

            app('log')->info('手持端 - 派送失败，仓库签收成功。出库单状态是待出库（外部系统已通知更新）');
        } else {
            return formatRet(500, '出库单状态不是待出库');
        }

        return formatRet(0, '订单派送失败，仓库签收成功');
    }

    /**
     * 仓库自提订单确认收货
     */
    public function receiving(Request $request)
    {
        app('log')->info('手持端 - 仓库自提', $request->input());

        $this->validate($request, [
            'express_num' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::ofWarehouse($warehouse->id)
            ->where('express_num', $request->express_num)
            ->first();

        if (! $order) {
            return formatRet(500, '订单不存在');
        }

        if ($order->express_code != 'express') {
            return formatRet(500, '该订单不是自提订单');
        }

        if ($order->status == Order::STATUS_CANCEL) {
            return formatRet(500, '订单已退款');
        }

        if ($order->status == Order::STATUS_SUCCESS) {
            return formatRet(500, '订单已签收');
        }

        if ($order->status != Order::STATUS_WAITING) {
            return formatRet(500, '订单状态不是待出库');
        }

        if ($order->is_tobacco == 1) {
            if (!$request->filled('adult_name') || !$request->filled('adult_birth') || !$request->filled('signature')) {
                return formatRet(0, '烟酒包裹缺少用户信息', ['is_tobacco' => 1]);
            }

            $age = birthday($request->adult_birth);
            if ($age < 18) {
                return formatRet(500, '用户未成年');
            }
        }

        app('db')->beginTransaction();
        try {
            if (! Order::where('id', $order->id)
                    ->where('status', Order::STATUS_WAITING)
                    ->update([
                        'status' => Order::STATUS_SUCCESS,
                        'output_time' => Carbon::now(),
                    ])) {
                app('db')->rollback();
                return formatRet(500, '订单不存在或订单状态不是待出库');
            }

            if (! Pick::where('order_id', $order->id)->where('status', Pick::STATUS_WAITING)
                    ->update(['status' => Pick::STATUS_SUCCESS])) {
                app('db')->rollback();
                return formatRet(500, '拣货单不是待出库');
            }

            $params['out_sn'] = $order['out_sn'];

            if ($order->is_tobacco == 1) {
                $params['adult_name']  = $request->adult_name;
                $params['adult_birth'] = $request->adult_birth;
                $params['signature']   = $request->signature;
            }

            $responseData = app('MallService')->orderOneselfOver($params);

            app('log')->info('手持端 - 仓库自提结果', [
                'params' => $params,
                'express_num' => $request->express_num,
                'order' => $order->toArray(),
                'response' => $responseData,
            ]);

            if ($responseData['ret'] == 1) {
                app('db')->commit();
                return formatRet(0, '收货成功');
            } else {
                app('db')->rollback();
                return formatRet(500, $responseData['msg']);
            }
        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->info('手持端 - 仓库自提烟酒结果', [
                'url' => env('MALL_API_URL'),
                'exception msg' => $e->getMessage(),
            ]);
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '仓库自提成功');
    }
}
