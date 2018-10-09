<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kep;
use App\Rules\PageSize;
use Illuminate\Validation\Rule;

class KepController extends Controller
{
    /**
     * 篮子 - 列表
     *
     * @author liusen
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'keywords'     => 'string|max:255',
            'warehouse_id' => 'required|integer|min:1',
        ]);

        $kep = Kep::ofWarehouse($request->input('warehouse_id'));

        if ($request->filled('keywords')) {
            $kep->hasKeyword($request->keywords);
        }

        $keps = $kep->paginate($request->input('page_size'));

        return formatRet(0, '', $keps->toArray());
    }

    /**
     * 篮子 - 详情
     *
     * @param  $kep_id
     * @author liusen
     */
    public function show($kep_id)
    {
        if (! $kep = Kep::find($kep_id)) {
            return formatRet(404, '篮子不存在', [], 404);
        }

        return formatRet(0, '', $kep->toArray());
    }

    /**
     * 篮子 - 创建
     *
     * @author liusen
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'code'         => 'required|string|max:255',
            'capacity'     => 'numeric',
            'weight'       => 'numeric',
            'is_enabled'   => 'required|boolean',
        ]);

        $kep = new Kep;

        $this->validate($request, [
            'code' => Rule::unique($kep->getTable())->where(function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id)
                             ->where('code', $request->code);
            }),
        ]);

        $kep->warehouse_id = $request->warehouse_id;
        $kep->code         = $request->code;
        $kep->capacity     = $request->input('capacity');
        $kep->weight       = $request->input('weight');
        $kep->is_enabled   = $request->is_enabled;

        if ($kep->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 篮子 - 编辑
     *
     * @author liusen
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'kep_id'       => 'required|integer|min:1',
            'code'         => 'required|string|max:255',
            'capacity'     => 'present|numeric',
            'weight'       => 'present|numeric',
            'is_enabled'   => 'required|boolean',
        ]);

        if (! $kep = Kep::find($request->kep_id)) {
            return formatRet(404, '篮子不存在', [], 404);
        }

        $this->validate($request, [
            'code' => Rule::unique($kep->getTable())->ignore($kep->id)->where(function ($query) use ($request, $kep) {
                return $query->where('warehouse_id', $kep->warehouse_id)
                             ->where('code', $request->code);
            }),
        ]);

        $kep->code         = $request->code;
        $kep->capacity     = $request->input('capacity');
        $kep->weight       = $request->input('weight');
        $kep->is_enabled   = $request->is_enabled;

        if ($kep->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 篮子 - 删除
     *
     * @author liusen
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'kep_id' => 'required|integer|min:1',
        ]);

        $kep = Kep::find($request->kep_id);

        if (! $kep) {
            return formatRet(404, '删除失败,篮子不存在!', [], 404);
        }

        if(!empty($kep['shipment_num'])){
            return formatRet(1, '删除失败,篮子目前在使用中!');
        }
        if ($kep->delete()) {
            return formatRet(0,"删除成功!");
        }

        return formatRet(500, '删除失败!');
    }
}
