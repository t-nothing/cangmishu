<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\BatchType;
use App\Models\WarehouseArea;
use App\Models\Warehouse;
use App\Rules\PageSize;
use Illuminate\Validation\Rule;

class BatchTypeController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'warehouse_id' => 'required|integer|min:1',
            'is_enabled'   => 'boolean',
        ]);

        $batchType = BatchType::with('warehouseArea')
                              ->withCount('batches')
                              ->ofWarehouse($request->input('warehouse_id'));

        if ($request->filled('is_enabled')) {
            $batchType->where('is_enabled', $request->is_enabled);
        }

        $batchTypes = $batchType->paginate($request->input('page_size'));

        if ($batchTypes) {
            foreach ($batchTypes as $k => $v) {
                if ($v->warehouseArea) {
                    $batchTypes[$k]->warehouseArea['function_names'] = $v->warehouseArea->function_names;
                }
            }
        }

        return formatRet(0, '', $batchTypes->toArray());
    }

    /**
     * 创建分类
     *
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'name'         => 'required|string|max:50',
            'is_enabled'   => 'required|boolean',
            'area_id'      => 'required|integer|min:0',
        ]);

        $user_id = app('auth')->id();

        $batchType = new BatchType;

        if (! $warehouse = Warehouse::find($request->warehouse_id)) {
            return formatRet(404, '仓库不存在');
        }

        if ($warehouse->owner_id != $user_id) {
            return formatRet(403, '未拥有仓库的使用权');
        }

        $this->validate($request, [
            'name' => Rule::unique($batchType->getTable())->where(function ($query) use ($request) {
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

        $batchType->warehouse_id = $request->warehouse_id;
        $batchType->name         = $request->name;
        $batchType->is_enabled   = $request->is_enabled;
        $batchType->area_id      = $request->area_id;
        if ($batchType->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 修改分类
     *
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'batch_type_id' => 'required|integer|min:1',
            'name'          => 'required|string|max:50',
            'is_enabled'    => 'required|boolean',
            'area_id'       => 'required|integer|min:0',
        ]);

        if (! $batchType = BatchType::find($request->batch_type_id)) {
            return formatRet(404, '入库单分类不存在');
        }

        $this->validate($request, [
            'name' => Rule::unique($batchType->getTable())->ignore($batchType->id)->where(function ($query) use ($request, $batchType) {
                return $query->where('warehouse_id', $batchType->warehouse_id)
                             ->where('name', $request->name);
            }),
        ]);

        if ($request->area_id != 0) {
            if (! $area = WarehouseArea::find($request->area_id)) {
                return formatRet(404, '货区不存在');
            }

            if ($area->warehouse_id != $batchType->warehouse_id) {
                return formatRet(404, '仓库无此货区');
            }
        }

        $batchType->name       = $request->name;
        $batchType->is_enabled = $request->is_enabled;
        $batchType->area_id    = $request->area_id;
        if ($batchType->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'batch_type_id' => 'required|integer|min:1',
        ]);

        if (! $batch = BatchType::find($request->batch_type_id)) {
            return formatRet(404, '入库单分类不存在');
        }

        if (Batch::where('type_id', $request->batch_type_id)->first()) {
            return formatRet(500, '入库单分类已被使用');
        }

        if ($batch->delete()) {
            return formatRet(0);
        }

        return formatRet(500, '删除失败');
    }
}
