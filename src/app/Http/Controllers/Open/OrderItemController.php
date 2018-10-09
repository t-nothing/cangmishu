<?php

namespace App\Http\Controllers\Open;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderItemController extends Controller
{
    public function info(Request $request)
    {
        app('log')->info('API 查询出库物品信息', $request->post());

        $this->validate($request, [
            'express_num' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $order = Order::with('orderItems.stock')
            ->whose(app('auth')->id())
            ->ofWarehouse($warehouse->id)
            ->where('express_num', $request->express_num)
            ->first();

        if (empty($order)) {
            return formatRet(500, '订单不存在');
        }

        switch ($order->status) {
            case Order::STATUS_CANCEL:
                return formatRet(500, '订单已取消');
                break;

            case Order::STATUS_DEFAULT:// 待拣货
            case Order::STATUS_PICKING:// 拣货中
                return formatRet(500, '拣货未完成，暂无货品的详细信息');
                break;

            case Order::STATUS_PICK_DONE:// 已拣货
            case Order::STATUS_WAITING:// 待出库（已验货）
            case Order::STATUS_SENDING:// 配送中
            case Order::STATUS_SUCCESS:// 已收货
                break;

            default:
                return formatRet(500, '订单状态异常');
                break;
        }

        if (empty($order->orderItems)) {
            return formatRet(500, '出库物品数据异常');
        }

        $data = [];

        foreach ($order->orderItems as $i) {
            $item['relevance_code'] = $i->relevance_code;
            $item['product_name_cn'] = $i->name_cn;
            $item['product_name_en'] = $i->name_en;
            $item['amount'] = $i->amount;
            $item['ean'] = '';
            $item['exp'] = '';
            $item['bbd'] = '';
            $item['product_batch_number'] = '';

            if ($i->stock) {
                $stock = $i->stock->toArray();

                $item['ean'] = $stock['ean']?:'';
                $item['exp'] = $stock['expiration_date']?:'';
                $item['bbd'] = $stock['best_before_date']?:'';
                $item['product_batch_number'] = $stock['production_batch_number']?:'';
            }

            $data[] = $item;
        }

        return formatRet(0, '', $data);
    }
}
