<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;
use App\Models\WarehouseArea;

class WarehouseAreaController extends Controller
{
    /**
     * 获取货区列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'warehouse_id' => 'required|integer|min:1',
            'is_enabled'   => 'integer|min:1',
            'keywords'     => 'string',
        ]);

        $area = WarehouseArea::with('feature:id,name_cn,name_en')->ofWarehouse($request->input('warehouse_id'));

        if ($request->filled('is_enabled')) {
            $area->where('is_enabled',$request->is_enabled);
        }

        if ($request->filled('keywords')) {
            $area->hasKeyword($request->keywords);
        }

        $warehouseAreas = $area->latest()->paginate($request->input('page_size'));

        return formatRet(0, '', $warehouseAreas->toArray());
    }

    public function show($warehouse_area_id)
    {
        $warehouseArea = WarehouseArea::findOrFail($warehouse_area_id);

        return formatRet(0, '', $warehouseArea->toArray());
    }

    /**
     * 创建货区
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id'         => 'required|integer|min:1',
            'warehouse_feature_id' => 'required|integer',
            'code'                 => ['required', new AlphaNumDash, 'max:255'],
            'name_cn'              => 'required|string|max:255',
            'name_en'              => 'required|string|max:255',
            'is_enabled'           => 'required|boolean',
            'functions'            => 'sometimes|array',
            'functions.*'          => 'sometimes|required|integer|min:1',
            'remark'               => 'string|max:255',
        ]);

        $warehouseArea = new WarehouseArea;

        $this->validate($request, [
            'code' => Rule::unique($warehouseArea->getTable())->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id)
                             ->where('code', $request->code);
            }),
            'warehouse_feature_id' => Rule::exists('warehouse_feature', 'id')->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            }),
        ]);

        $warehouseArea->warehouse_id = $request->warehouse_id;
        $warehouseArea->warehouse_feature_id = $request->warehouse_feature_id;
        $warehouseArea->code         = $request->code;
        $warehouseArea->name_cn      = $request->name_cn;
        $warehouseArea->name_en      = $request->name_en;
        $warehouseArea->is_enabled   = $request->is_enabled;
        $warehouseArea->functions    = $request->filled('functions') ? $request->functions : [];
        $warehouseArea->remark       = $request->remark;

        if ($warehouseArea->save()) {
            return formatRet(0, '新增成功');
        }

        return formatRet(500, '新增失败');
    }

    /**
     * 修改货区
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'area_id'              => 'required|integer|min:1',
            'warehouse_feature_id' => 'required|integer',
            'code'                 => 'required|string|max:255',
            'name_cn'              => 'required|string|max:255',
            'name_en'              => 'required|string|max:255',
            'is_enabled'           => 'required|boolean',
            'functions'            => 'present|array',
            'functions.*'          => 'present|integer|min:1',
            'remark'               => 'present|string|max:255',
        ]);

        $warehouseArea = WarehouseArea::findOrFail($request->area_id);

        $this->validate($request, [
            'code' => Rule::unique($warehouseArea->getTable())->ignore($warehouseArea->id)->where(function ($query) use ($request, $warehouseArea) {
                return $query->where('warehouse_id', $warehouseArea->warehouse_id)
                             ->where('code', $request->code);
            }),
            'warehouse_feature_id' => Rule::exists('warehouse_feature', 'id')->where(function ($query) use ($warehouseArea) {
                return $query->where('warehouse_id', $warehouseArea->warehouse_id);
            }),
        ]);

        $warehouseArea->warehouse_feature_id = $request->warehouse_feature_id;
        $warehouseArea->code        = $request->code;
        $warehouseArea->name_cn     = $request->name_cn;
        $warehouseArea->name_en     = $request->name_en;
        $warehouseArea->is_enabled  = $request->is_enabled;
        $warehouseArea->functions   = $request->filled('functions') ? $request->functions : [];
        $warehouseArea->remark      = $request->remark;

        if ($warehouseArea->save()) {
            return formatRet(0, '修改成功!');
        }

        return formatRet(500, '修改失败!');
    }

    /**
     * 删除货区
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'area_id' => 'required|integer|min:1',
        ]);

        $warehouseArea = WarehouseArea::findOrFail($request->area_id);

        if (! $warehouseArea->delete()) {
            return formatRet(500, '删除失败');
        }

        return formatRet(0, '删除成功');
    }

    /**
     * 货区功能列表
     *
     * @author liusen
     */
    public function functions()
    {
        return formatRet(0, '', [
            WarehouseArea::AREA_FUNCTION_RECEIVING => '收货区',
            WarehouseArea::AREA_FUNCTION_PICKING   => '拣货区',
            WarehouseArea::AREA_FUNCTION_STOCKING  => '备货区',
            WarehouseArea::AREA_FUNCTION_SHIPPING  => '集货区',
        ]);
    }
}
