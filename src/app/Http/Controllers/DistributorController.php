<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rules\PageSize;
use App\Models\Distributor;
use Illuminate\Support\Facades\Auth;

class DistributorController extends Controller
{
    /**
     * 供应商 - 列表
     *
     * @author liusen
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $distributors = Distributor::whose(Auth::id())->paginate($request->input('page_size'));

        return formatRet(0, '', $distributors->toArray());
    }

    /**
     * 供应商 - 详情
     *
     * @param  $distributor_id
     * @author liusen
     */
    public function show($distributor_id)
    {
        if (! $distributor = Distributor::whose(Auth::id())->find($distributor_id)) {
            return formatRet(404, '供应商不存在', [], 404);
        }

        return formatRet(0, '', $distributor->toArray());
    }

    /**
     * 供应商 - 创建
     *
     * @author liusen
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name_cn' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $distributor = new Distributor;
        $distributor->user_id = Auth::id();
        $distributor->name_cn = $request->name_cn;
        $distributor->name_en = $request->name_en;

        if ($distributor->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 供应商 - 修改
     *
     * @author liusen
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'distributor_id' => 'required|integer|min:1',
            'name_cn'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
        ]);

        if (! $distributor = Distributor::whose(Auth::id())->find($request->distributor_id)) {
            return formatRet(404, '供应商不存在', [], 404);
        }

        $distributor->name_cn = $request->name_cn;
        $distributor->name_en = $request->name_en;

        if ($distributor->save()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }

    /**
     * 供应商 - 删除
     *
     * @author liusen
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'distributor_id' => 'required|integer|min:1',
        ]);

        if (! $distributor = Distributor::whose(Auth::id())->find($request->distributor_id)) {
            return formatRet(404, '供应商不存在', [], 404);
        }

        if ($distributor->delete()) {
            return formatRet(0);
        }

        return formatRet(500, '失败');
    }
}
