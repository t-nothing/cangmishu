<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Rules\NameCn;
use App\Rules\NameEn;
use App\Models\WarehouseLocation;

class WarehouseLocationController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'              => 'integer|min:1',
            'page_size'         => new PageSize,
            'warehouse_id'      => 'required|integer|min:1',
            'is_enabled'        => 'boolean',
            'warehouse_area_id' => 'integer|min:1',
        ]);

        $location = WarehouseLocation::with('warehouseArea:id,code,name_cn,name_en')->ofWarehouse($request->input('warehouse_id'));

        $request->filled('is_enabled') AND
            $location->where('is_enabled', $request->is_enabled);

        $request->filled('warehouse_area_id') AND
            $location->where('warehouse_area_id', $request->warehouse_area_id);

        $locations = $location->paginate($request->input('page_size'));

        return formatRet(0, '', $locations->toArray());
    }

    public function show($id)
    {
        $location = WarehouseLocation::findOrFail($id);

        return formatRet(0, '', $location->toArray());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id'      => 'required|integer|exists:warehouse,id',
            'warehouse_area_id' => 'required|integer',
            'code'              => 'required|string|max:255',
            'capacity'          => 'required|numeric',
            'is_enabled'        => 'required|boolean',
            'passage'           => 'present|string|max:15',
            'row'               => 'present|string|max:15',
            'col'               => 'present|string|max:15',
            'floor'             => 'present|string|max:15',
            'remark'            => 'present|string|max:255',
        ]);

        $location = new WarehouseLocation;

        $this->validate($request, [
            'warehouse_area_id' => Rule::exists('warehouse_area', 'id')->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            }),
            'code' => Rule::unique('warehouse_location', 'code')->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            }),
        ]);

        $location->warehouse_id      = $request->warehouse_id;
        $location->warehouse_area_id = $request->warehouse_area_id;
        $location->code              = $request->code;
        $location->capacity          = $request->capacity;
        $location->is_enabled        = $request->is_enabled;
        $location->passage           = $request->passage;
        $location->row               = $request->row;
        $location->col               = $request->col;
        $location->floor             = $request->floor;
        $location->remark            = $request->remark;

        if (! $location->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    public function edit(Request $request)
    {
        $this->validate($request, [
            'id'                => 'required|integer|min:1',
            'warehouse_area_id' => 'required|integer',
            'capacity'          => 'required|numeric',
            'is_enabled'        => 'required|boolean',
            'passage'           => 'present|string|max:15',
            'row'               => 'present|string|max:15',
            'col'               => 'present|string|max:15',
            'floor'             => 'present|string|max:15',
            'remark'            => 'present|string|max:255',
        ]);

        $location = WarehouseLocation::findOrFail($request->id);

        $this->validate($request, [
            'warehouse_area_id' => Rule::exists('warehouse_area', 'id')->where(function ($query) use ($location) {
                return $query->where('warehouse_id', $location->warehouse_id);
            }),
        ]);

        $location->warehouse_area_id = $request->warehouse_area_id;
        $location->capacity          = $request->capacity;
        $location->is_enabled        = $request->is_enabled;
        $location->passage           = $request->passage;
        $location->row               = $request->row;
        $location->col               = $request->col;
        $location->floor             = $request->floor;
        $location->remark            = $request->remark;

        if (! $location->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        // $location = WarehouseLocation::findOrFail($request->id);

        // if ( ! $location->delete()) {
        //     return formatRet(500, '失败');
        // }

        return formatRet(0);
    }
}
