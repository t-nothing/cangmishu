<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Origin;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;

class OriginController extends Controller
{
    public function list(Request $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $origin = Origin::whose(Auth::id())->paginate($request->input('page_size'));

        return formatRet(0, '', $origin->toArray());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name_cn' => 'required|string|max:255',
        ]);

        if (Origin::whose(Auth::id())->where('name_cn', $request->name_cn)->first()) {
            return formatRet(1, '产地名重复!');
        }

        $origin = new Origin();
        $origin->name_cn = $request->name_cn;
        $origin->user_id = Auth::id();

        if ($origin->save()) {
            return formatRet(0, '新增成功!');
        }

        return formatRet(500, '新增失败!');
    }

    public function edit(Request $request)
    {
        $this->validate($request, [
            'id' => 'integer|min:1',
            'name_cn' => 'required|string|max:255',
        ]);

        $origin = Origin::whose(Auth::id())->find($request->id);

        if (!$origin) {
            return formatRet(404, '找不到相关数据!', [], 404);
        }

        if (Origin::whose(Auth::id())
            ->where('name_cn', $request->name_cn)
            ->where('id', '!=', $origin['id'])->first()
        ) {
            return formatRet(1, '产地名重复!');
        }

        $origin->name_cn = $request->name_cn;
        $origin->user_id = Auth::id();

        if ($origin->save()) {
            return formatRet(0, '修改成功!');
        }

        return formatRet(500, '修改失败!');
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'integer|min:1',
        ]);

        $origin = Origin::whose(Auth::id())->find($request->id);
        
        if (!$origin) {
            return formatRet(404, '找不到相关数据!', [], 404);
        }

        if ($origin->delete()) {
            return formatRet(0);
        }

        return formatRet(500, '删除失败!');
    }

}
