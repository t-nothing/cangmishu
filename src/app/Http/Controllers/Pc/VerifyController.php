<?php

namespace App\Http\Controllers\Pc;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pick;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use Illuminate\Support\Facades\Log;

/**
 * 核对商品（验货）
 */
class VerifyController extends Controller
{
    /**
     * 扫描拣货单号获取商品详情
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'shipment_num' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $pick = Pick::with([
            'order',
            'orderItems.stock.spec.product',
        ])->ofWarehouse($warehouse->id)->where('shipment_num', $request['shipment_num'])->first();

        if (! $pick) {
            return formatRet(500, '捡货单不存在');
        }

        $pick->checkStatusWhenVerifying();

        if (! $pick->orderItems) {
            return formatRet(500, '拣货单丢失了出库货品数据');
        }

        if (! $pick->order) {
            return formatRet(500, '拣货单丢失了出库单数据');
        }

        $data['pick'] = $pick->only([
            'id',
            'warehouse_id',
            'order_id',
            'shipment_num',
        ]);

        $data['pick']['order'] = $pick->order->only([
            'id',
            'out_sn',
            'remark',
        ]);

        foreach ($pick->orderItems as $k => $v) {
            $item = [];

            if (! $v->stock) {
                return formatRet(500, '库存记录不存在或丢失了');
            }

            $item['id']             = $v->id;
            $item['relevance_code'] = $v->relevance_code;
            $item['amount']         = $v->amount;
            $item['pick_num']       = $v->pick_num;
            $item['verify_num']     = $v->verify_num;
            $item['product_name']   = $v->product_name;
            $item['ean']            = $v->stock->ean;
            $item['location']       = $v->stock->location->code;

            $item['photos']         = isset($v->stock->spec->product->photos) && ! empty($v->stock->spec->product->photos)
                ? $v->stock->spec->product->photos
                : '';

            $data['pick']['order_items'][] = $item;
        }

        $msg = $pick->verify_status == Pick::VERIFY_STATUS_DONE
            ? '重复验货'
            : '查询成功';

        return formatRet('0', $msg, $data);
    }

    /**
     * 核对商品 - 完成验货
     */
    public function confirm(Request $request)
    {
        $this->validate($request, [
            'shipment_num'       => 'required|string',
            'items'              => 'required|array',
            'items.*.id'         => 'required|integer',
            'items.*.verify_num' => 'required|integer|min:0',
        ]);

        $warehouse = app('auth')->warehouse();

        $pick = Pick::withCount('orderItems')
            ->with('order')
            ->ofWarehouse($warehouse->id)
            ->where('shipment_num', $request['shipment_num'])
            ->first();

        if (! $pick) {
            return formatRet(500, '捡货单不存在');
        }

        $pick->checkStatusWhenVerifying();

        if (! $pick->order) {
            return formatRet(500, '拣货单丢失了出库单数据');
        }

        // 桌面端需要显示 快递单号，预计出库日期，线路名称
        $data =  Arr::only($pick->order->toArray(), [
            'express_code',
            'delivery_date',
            'line_name',
        ]);

        if ($pick->verify_status == Pick::VERIFY_STATUS_DONE) {
            return formatRet(0, '操作成功', $data);
        }

        if ($pick->order_items_count == 0) {
            return formatRet(500, '拣货单丢失了货品数据');
        }

        if ($pick->order_items_count != count($request->items)) {
            return formatRet(500, '拣货单物品项数是'.$pick->order_items_count);
        }

        $complete_num = 0;
        $obejcts = [];

        foreach ($request->items as $i) {
            if (! $item = OrderItem::find($i['id'])) {
                return formatRet(500, '拣货单物品不存在'.$i['id']);
            }

            if ($item['shipment_num'] != $request['shipment_num']) {
                return formatRet(500, '拣货单'.$request['shipment_num'].'没有商品编码为'.$item['relevance_code'].'货品');
            }

            if ($i['verify_num'] != $item['pick_num']) {
                return formatRet(500, '验货数量必须等于拣货数量');
            }

            if ($i['verify_num'] > $item['amount']) {
                return formatRet(500, '验货数量超过了待出库数量');
            } elseif ($i['verify_num'] == $item['amount']) {
                $complete_num ++;
            }

            $stockin_num_decrement = $i['verify_num'] - $item['verify_num'];

            if (! $stock = ProductStock::ofWarehouse($warehouse->id)->find($item->product_stock_id)) {
                return formatRet(500, '库存记录不存在');
            }

            if ($stock['stockin_num'] < $stockin_num_decrement) {
                return formatRet(500, '货品' . $stock['relevance_code'] . '库存不足，剩余库存:'.$stock['stockin_num']);
            }

            if ($i['verify_num'] < $item['verify_num']) {
                return formatRet(500, '本次验货数量不能小于上次验货数量');
            } elseif ($i['verify_num'] == $item['verify_num']) {
                continue;
            } else {
                $obejcts[] = [
                    'item'  => $item,
                    'verify_num' => $i['verify_num'],
                    'stock' => $stock,
                    'stockin_num_decrement' => $stockin_num_decrement,
                ];
            }
        }

        app('db')->beginTransaction();
        try {
            if ($obejcts) {
                foreach ($obejcts as $obejct) {
                    // 更新验货数量
                    $obejct['item']->verify_num = $obejct['verify_num'];
                    $obejct['item']->save();
                    // 更新库存
                    $obejct['stock']->decrement('stockin_num', $obejct['stockin_num_decrement']);

                    // 添加记录
                    $obejct['stock']->addLog(ProductStockLog::TYPE_OUTPUT, $obejct['stockin_num_decrement'], $obejct['item']->shipment_num);
                }
            }

            if ($complete_num == $pick->order_items_count) {
                $pick->verify_status = Pick::VERIFY_STATUS_DONE;
                $pick->save();
            } else {
                $pick->verify_status = Pick::VERIFY_STATUS_ERR;
                $pick->save();
            }

            // Pick::ofWarehouse($warehouse->id)->where('shipment_num', $request['shipment_num'])
            //     ->where('status', Pick::STATUS_PICK_DONE)
            //     ->update(['status' => Pick::STATUS_WAITING]);


            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '操作成功', $data);
    }
}
