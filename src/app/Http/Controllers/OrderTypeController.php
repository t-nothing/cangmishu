<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderType;
use App\Models\Warehouse;
use App\Models\WarehouseArea;
use App\Rules\PageSize;
use Illuminate\Validation\Rule;

class OrderTypeController extends Controller
{
    /**
     * 获取列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'warehouse_id' => 'required|integer|min:1',
            'is_enabled'   => 'boolean',
            'is_partial'   => 'boolean',
        ]);

        $orderType = OrderType::with('warehouseArea')
                              ->withCount('orders')
                              ->ofWarehouse($request->input('warehouse_id'));

        if ($request->filled('is_enabled')) {
            $orderType->where('is_enabled', $request->is_enabled);
        }

        if ($request->filled('is_partial')) {
            $orderType->where('is_partial', $request->is_partial);
        }

        $orderTypes = $orderType->paginate($request->input('page_size'));

        return formatRet(0, '', $orderTypes->toArray());
    }

    /**
     * 创建分类
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'name'         => 'required|string|max:50',
            'is_enabled'   => 'required|boolean',
            'is_partial'   => 'required|boolean',
            'area_id'      => 'required|integer|min:0',
        ]);

        $user_id = app('auth')->id();

        $order = new OrderType;

        if (! $warehouse = Warehouse::find($request->warehouse_id)) {
            return formatRet(404, '仓库不存在');
        }

        if ($warehouse->owner_id != $user_id) {
            return formatRet(403, '未拥有仓库的使用权');
        }

        $this->validate($request, [
            'name' => Rule::unique($order->getTable())->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id)
                             ->where('name', $request->name);
            }),
        ]);

        if ($request->area_id != 0) {
            if (! $area = WarehouseArea::find($request->area_id)) {
                return formatRet(404, '货区不存在');
            }

            if ($area->warehouse_id != $request->warehouse_id) {
                return formatRet(404, '仓库无此货区');
            }
        }

        $order->warehouse_id = $request->warehouse_id;
        $order->name         = $request->name;
        $order->is_enabled   = $request->is_enabled;
        $order->is_partial   = $request->is_partial;
        $order->area_id      = $request->area_id;

        if ($order->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 修改分类
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'order_type_id' => 'required|integer|min:1',
            'name'          => 'required|string|max:50',
            'is_enabled'    => 'required|boolean',
            'is_partial'    => 'required|boolean',
            'area_id'       => 'required|integer|min:0',
        ]);

        if (! $orderType = OrderType::find($request->order_type_id)) {
            return formatRet(404, '出库单分类不存在');
        }

        $this->validate($request, [
            'name' => Rule::unique($orderType->getTable())->ignore($orderType->id)->where(function ($query) use ($request, $orderType) {
                return $query->where('warehouse_id', $orderType->warehouse_id)
                             ->where('name', $request->name);
            }),
        ]);

        if ($request->area_id != 0) {
            if (! $area = WarehouseArea::find($request->area_id)) {
                return formatRet(404, '货区不存在');
            }

            if ($area->warehouse_id != $orderType->warehouse_id) {
                return formatRet(404, '仓库无此货区');
            }
        }

        $orderType->name       = $request->name;
        $orderType->area_id    = $request->area_id;
        $orderType->is_enabled = $request->is_enabled;
        $orderType->is_partial = $request->is_partial;

        if ($orderType->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'order_type_id' => 'required|integer|min:1',
        ]);

        if (! $order = OrderType::find($request->order_type_id)) {
            return formatRet(404, '出库单分类不存在');
        }

        if (Order::where('order_type', $request->order_type_id)->first()) {
            return formatRet(500, '出库单分类已被使用');
        }

        if ($order->delete()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }
}
