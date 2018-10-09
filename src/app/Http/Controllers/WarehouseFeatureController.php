<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Rules\NameCn;
use App\Rules\NameEn;
use App\Models\WarehouseFeature;

class WarehouseFeatureController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'warehouse_id' => 'required|integer|min:1',
            'is_enabled'   => 'boolean',
        ]);

        $feature = WarehouseFeature::ofWarehouse($request->input('warehouse_id'));

        $request->filled('is_enabled') AND
            $feature->where('is_enabled', $request->is_enabled);

        $features = $feature->paginate($request->input('page_size'));

        return formatRet(0, '', $features->toArray());
    }

    public function show($id)
    {
        $feature = WarehouseFeature::findOrFail($id);

        return formatRet(0, '', $feature->toArray());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id'   => 'required|integer|exists:warehouse,id',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'is_enabled'     => 'required|boolean',
            'logo'           => 'present|string|max:255|in:常温,雪花,风扇',
            'remark'         => 'present|string|max:255',
        ]);

        $feature = new WarehouseFeature;

        $this->validate($request, [
            'name_cn' => Rule::unique($feature->getTable())->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id)
                             ->where('name_cn', $request->name_cn);
            }),
            'name_en' => Rule::unique($feature->getTable())->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id)
                             ->where('name_en', $request->name_en);
            }),
        ]);

        $feature->warehouse_id = $request->warehouse_id;
        $feature->name_cn      = $request->name_cn;
        $feature->name_en      = $request->name_en;
        $feature->is_enabled   = $request->is_enabled;
        $feature->logo         = $request->logo;
        $feature->remark       = $request->remark;

        if (! $feature->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    public function edit(Request $request)
    {
        $this->validate($request, [
            'id'             => 'required|integer|min:1',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'is_enabled'     => 'required|boolean',
            'logo'           => 'present|string|max:255|in:常温,雪花,风扇',
            'remark'         => 'present|string|max:255',
        ]);

        $feature = WarehouseFeature::findOrFail($request->id);

        $this->validate($request, [
            'name_cn' => Rule::unique($feature->getTable())->ignore($request->id)->where(function ($query) use ($request, $feature) {
                return $query->where('warehouse_id', $feature->warehouse_id)
                             ->where('name_cn', $request->name_cn);
            }),
            'name_en' => Rule::unique($feature->getTable())->ignore($request->id)->where(function ($query) use ($request, $feature) {
                return $query->where('warehouse_id', $feature->warehouse_id)
                             ->where('name_en', $request->name_en);
            }),
        ]);

        $feature->name_cn      = $request->name_cn;
        $feature->name_en      = $request->name_en;
        $feature->is_enabled   = $request->is_enabled;
        $feature->logo         = $request->logo;
        $feature->remark       = $request->remark;

        if (! $feature->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        $feature = WarehouseFeature::findOrFail($request->id);

        if ( ! $feature->delete()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
