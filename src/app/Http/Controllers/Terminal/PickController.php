<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Kep;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Models\Pick;
use App\Models\ProductPick;
use App\Models\ProductStock;
use App\Models\ProductStockLog;

/**
 * 拣货
 */
class PickController extends Controller
{
    protected $stockCache;

    protected $stockCached = [];

    /**
     * 开始捡货
     */
    public function start(Request $request)
    {
        app('log')->info('开始拣货', $request->post());

        $this->validate($request, [
            'bindings.*'              => 'required|array',
            'bindings.*.shipment_num' => 'required|string|distinct',
            'bindings.*.code'         => 'required|string|distinct',
        ]);

        $warehouse = app('auth')->warehouse();

        $picks = [];

        foreach ($request->bindings as $key => $value) {
            $pick = Pick::with(['order', 'orderItems'])
                ->ofWarehouse($warehouse->id)
                ->where('shipment_num', $value['shipment_num'])
                ->first();

            if (! $pick) {
                return formatRet(500, '捡货单'.$value['shipment_num'].'不存在');
            }

            if ($pick->status != Pick::STATUS_PICKING) {
                return formatRet(500, '拣货单'.$value['shipment_num'].'已拣货完成或没有打印');
            }

            if ($pick->kep) {
                return formatRet(500, '拣货单'.$value['shipment_num'].'已经绑定了篮子'.$pick->kep->code);
            }

            $kep = Kep::ofWarehouse($warehouse->id)->where('code', $value['code'])->first();

            if (! $kep) {
                return formatRet(500, '篮子'.$value['code'].'在该仓库不存在，请核对');
            }

            if ($kep->is_enabled != 1) {
                return formatRet(500, '篮子'.$value['code'].'未启用，请在桌面端启用');
            }

            if ($kep->shipment_num != '') {
                return formatRet(500, '篮子'.$value['code'].'已绑定捡货单'.$kep['shipment_num'].'，请先解除绑定');
            }

            $data['shipment_num'] = $pick['shipment_num'];
            $data['kep_code'] = $value['code'];
            $data['order_items'] = [];

            if (! $pick->orderItems) {
                return formatRet(500, '拣货单'.$value['shipment_num'].'缺少出库货品数据');
            }

            if (! $pick->order) {
                return formatRet(500, '拣货单'.$value['shipment_num'].'没有绑定出库单');
            }

            foreach ($pick->orderItems as $v) {
                $item = [];
                $stock = null;
                $order = $pick->order->toArray();
                $owner_id = $order['owner_id'];

                $item['order_item_id'] = $v['id'];
                $item['relevance_code'] = $v['relevance_code'];
                $item['name'] = $v['product_name'];
                $item['amount'] = $v['amount'];
                $item['photos'] = isset($v->spec->product->photos) ?  $v->spec->product->photos : '';

                if ($v['amount'] <= 0) {
                    return formatRet(500, '拣货单' . $v['shipment_num'] . '物品' . $v['product_name'] . '出库数量是' . $v['amount']);
                }

                $item['order'] = [
                    'created_at' => $order['created_at'],
                    'delivery_date' => $order['delivery_date'],
                ];

                // 本次分配中，之前有查过这个用户这个商品的库存
                if (isset($this->stockCache[$owner_id][$v['relevance_code']])) {

                    // 某一个物品的缓存记录数据类型是数组

                        foreach ($this->stockCache[$owner_id][$v['relevance_code']] as $stock_id => $c) {
                            if ($c['shelf_num_rest'] >= $v['amount']) {
                                $this->stockCache[$owner_id][$v['relevance_code']][$stock_id]['shelf_num_rest'] = $c['shelf_num_rest'] - $v['amount'];
                                $stock = $c['stock'];
                            }
                        }
                }

                // 本次分配中，之前没有查过这个用户这个商品的库存，去数据库里取
                if (empty($stock)) {
                    $query = ProductStock::with('location')
                        ->has('location')
                        ->whose($owner_id)
                        ->ofWarehouse($warehouse->id)
                        ->enabled()
                        ->where('relevance_code', $v['relevance_code'])
                        ->where('shelf_num', '>=' , $v['amount'])
                        ->oldest('expiration_date')
                        ->oldest('created_at');

                    if ($this->stockCached) {
                        $query->whereNotIn('id', $this->stockCached);
                    }

                    $stock = $query->first();

                    if ($stock) {
                        $this->stockCached[] = $stock->id;

                        $this->stockCache[$owner_id][$v['relevance_code']][$stock->id]['shelf_num_rest'] = $stock['shelf_num'] - $v['amount'];
                        $this->stockCache[$owner_id][$v['relevance_code']][$stock->id]['stock'] = $stock;
                    }
                }

                // 真·库存不足了
                if (empty($stock)) {
                    return formatRet(500, "库存不足，拣货单{$v['shipment_num']}货品{$v['product_name']}[{$v['relevance_code']}]");
                }

                $item['stock'] = [
                    'stock_id' => $stock->id,
                    'sku' => $stock->sku,
                    'ean' => $stock->ean,
                    'shelf_num' => $stock->shelf_num,
                    'location' => isset($stock->location->code) ? $stock->location->code : '',
                ];

                $data['order_items'][] = $item;
            }

            $picks[] = $data;
        }

        // app('db')->beginTransaction();
        // try {
        //     foreach ($request->bindings as $key => $value) {
        //         Kep::ofWarehouse($warehouse->id)->where('code', $value['code'])->update([
        //             'shipment_num' => $value['shipment_num'],
        //         ]);
        //     }

        //     app('db')->commit();
        // } catch (\Exception $e) {
        //     app('db')->rollback();
        //     return formatRet(500, '绑定篮子失败');
        // }

        return formatRet(0, '', $picks);
    }

    /**
     * 完成捡货或者放弃拣货
     */
    public function submit(Request $request)
    {
        app('log')->info('完成捡货或者放弃拣货', $request->input());

        $this->validate($request, [
            'skip'                         => 'required|boolean',
            'data'                         => 'required|array',
            'data.*.shipment_num'          => 'required|string',
            'data.*.items'                 => 'required_if:skip,0|array',
            'data.*.items.*.order_item_id' => 'required_if:skip,0|integer',
            'data.*.items.*.stock_id'      => 'required_if:skip,0|integer',
            'data.*.items.*.pick_num'      => 'required_if:skip,0|integer|min:0',
        ]);

        $user_id = app('auth')->id();
        $warehouse = app('auth')->warehouse();

        // 一维数组，元素是拣货单号
        $shipment_nums = [];
        // 一维数组，元素是拣货单号
        $picks = [];
        // 二维数组，出库单物品对象，库存记录对象和计划出库数量
        $obejcts = [];

        foreach ($request->data as $k => $v) {
            $pick = Pick::ofWarehouse($warehouse->id)
                ->where('shipment_num', $v['shipment_num'])
                ->first();

            if (! $pick) {
                return formatRet(500, '捡货单'.$v['shipment_num'].'不存在');
            }

            if ($pick->status != Pick::STATUS_PICKING) {
                return formatRet(500, $v['shipment_num'].'没有打印拣货单或者已完成');
            }

            // 记录下释放
            $shipment_nums[] = $v['shipment_num'];

            // 如果当前请求是放弃拣货，下面的完成拣货的代码不执行了
            if ($request['skip'] == 1) {
                continue;
            }

            $picks[] = $pick;

            $item_in_rq = array_pluck($v['items'], 'order_item_id');
            $item_in_db = $pick->orderItems->pluck('id')->toArray();
            sort($item_in_rq);
            sort($item_in_db);

            if ($item_in_rq != $item_in_db) {
                return formatRet(500, "拣货单物品项数据有误");
            }

            foreach ($v['items'] as $i) {
                if (! $item = OrderItem::find($i['order_item_id'])) {
                    return formatRet(500, '拣货单物品不存在'.$i['order_item_id']);
                }

                if ($item['shipment_num'] != $v['shipment_num']) {
                    return formatRet(500, '拣货单'.$v['shipment_num'].'没有编码为'.$item['relevance_code'].'货品');
                }

                if (! $stock = ProductStock::ofWarehouse($warehouse->id)->find($i['stock_id'])) {
                    return formatRet(500, '库存记录不存在');
                }

                if ($stock->status != ProductStock::GOODS_STATUS_ONLINE) {
                    return formatRet(500, '无法操作的库存');
                }

                // 捡货数量超出出库数量，提示错误
                if ($i['pick_num'] > $item['amount']) {
                    return formatRet(500, '拣货单'.$v['shipment_num'].'货品'.$stock['relevance_code'].'出库数量为'.$item['amount']);
                }

                if ($stock['shelf_num'] < $item['amount']) {
                    return formatRet(500, '货品' . $stock['relevance_code'] . '上架数量不足');
                }

                $obejcts[] = [
                    'item'  => $item,
                    'stock' => $stock,
                    'amount' => $i['pick_num'],
                ];
            }
        }

        app('db')->beginTransaction();
        try {
            // 拣货完成释放篮子
            if ($shipment_nums) {
                Kep::ofWarehouse($warehouse->id)->whereIn('shipment_num', $shipment_nums)->update(['shipment_num' => '']);
            }

            foreach ($picks as $p) {
                // 更新拣货单为拣货完成
                Pick::ofWarehouse($warehouse->id)->where('id', $p->id)->where('status', Pick::STATUS_PICKING)->update(['status' => Pick::STATUS_PICK_DONE]);

                $all = Pick::where('order_id', $p->order_id)->get();
                $count = 0;
                if ($all) {
                    foreach ($all as $a) {
                        if ($a->status == Pick::STATUS_PICK_DONE || $a->status == Pick::STATUS_WAITING) {
                            $count ++;
                        }
                    }

                    if ($count == count($all)) {
                        // 更新出库单为拣货完成
                        Order::ofWarehouse($warehouse->id)->where('id', $p->order_id)->where('status', Pick::STATUS_PICKING)->update(['status' => Pick::STATUS_PICK_DONE]);
                        // 记录出库单拣货完成的时间
                        OrderHistory::addHistory($p->order, Order::STATUS_PICK_DONE);
                    }
                } else {
                    app('db')->rollback();
                    return formatRet(500, '出库单下的拣货单数据异常');
                }
            }

            foreach ($obejcts as $obejct) {
                $obejct['item']->product_stock_id = $obejct['stock']->id;
                $obejct['item']->pick_num = $obejct['amount'];
                $obejct['item']->save();
                $obejct['stock']->decrement('shelf_num', $obejct['amount']);

                // 添加记录
                $obejct['stock']->addLog(ProductStockLog::TYPE_PICKING, $obejct['amount'], $obejct['item']->shipment_num);
            }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();

            info('完成捡货或者放弃拣货', ['exception msg' => $e->getMessage()]);
            return formatRet(500, '操作失败');
        }

        return formatRet(0, '操作成功');
    }

    /**
     * 释放篮子
     */
    public function release(Request $request)
    {
        $this->validate($request, [
            'codes' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        if ($request->codes == 'all') {
            Kep::ofWarehouse($warehouse->id)->where('shipment_num', '!=', '')->update([
                'shipment_num' => '',
            ]);
        } else {
            $codes = explode(',', $request->codes);

            Kep::ofWarehouse($warehouse->id)->where('shipment_num', '!=', '')
                ->whereIn('code', $codes)
                ->update([
                    'shipment_num' => '',
                ]);
        }

        return formatRet(0,'释放成功');
    }
}
