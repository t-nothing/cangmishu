<?php

namespace App\Http\Controllers;

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

        $roles = Role::where('type', 3)->paginate($request->input('page_size'));

        return formatRet(0, '', $roles->toArray());
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
}
