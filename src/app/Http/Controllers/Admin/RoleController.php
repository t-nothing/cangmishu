<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;

/**
 * 角色管理
 */
class RoleController extends Controller
{
	/**
     * 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $roles = Role::paginate($request->input('page_size'));

        return formatRet(0, '', $roles->toArray());
    }

    /**
     * 详情
     */
    public function show($role_id)
    {
        if (! $role = Role::find($role_id)) {
            return formatRet(404, '', [], 404);
        }

        return formatRet(0, '', $role->toArray());
    }

    /**
     * 角色所有权限
     */
    public function privileges($role_id)
    {
        if (! $role = Role::find($request->role_id)) {
            return formatRet(404, '用户不存在');
        }

        $role->load('privileges');

        return formatRet(0, '', $role->toArray());
    }

    /**
     *  角色 - 添加
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
        ]);

        $role = new Role;

        $this->validate($request, [
            'name_cn' => [new NameCn, Rule::unique($role->getTable())],
            'name_en' => [new NameEn, Rule::unique($role->getTable())],
        ]);

        $role->owner_id  = Auth::id();
        $role->name_cn   = $request->name_cn;
        $role->name_en   = $request->name_en;
        $role->is_system = 0;

        if (! $role->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
