<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserEmployee;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;

/**
 * 管理员账户管理
 */
class AdminController extends Controller
{
    /**
     * 列表
     */
	public function list(Request $request)
	{
		$this->validate($request, [
            'page'      => 'integer|min:1',
            'page_size' => new PageSize,
            'keywords'  => 'string',
        ]);

        $admin = User::onlyAdmin()->latest();

        if ($request->filled('keywords')) {
            $admin->hasKeyword($request->keywords);
        }

        $data = $admin->paginate($request->input('page_size'), [
            'id',
            'email',
            'last_login_at',
        ]);

        return formatRet(0, '', $data->toArray());
	}

    /**
     * 添加到管理员列表
     */
	public function create(Request $request)
	{
		$this->validate($request, [
            'user_id'   => 'required|integer|min:1',
        ]);

        if (! $user = User::find($request->user_id)) {
            return formatRet(404, '用户不存在');
        }

        if ($user->id == app('auth')->id()) {
            return formatRet(500, '不能操作自己');
        }

        if ($user->isAdmin()) {
            return formatRet(500, '当前用户已经是管理员了，请勿重复操作');
        }

        if (! $user->setAdmin()) {
            return formatRet(500, '操作失败');
        }

        return formatRet(0);
	}

    /**
     * 移除出管理员列表
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|min:1',
        ]);

        if (! $user = User::find($request->user_id)) {
            return formatRet(404, '用户不存在');
        }

        if ($user->id == app('auth')->id()) {
            return formatRet(500, '不能操作自己');
        }

        if (! $user->isAdmin()) {
            return formatRet(500, '当前用户已经不是管理员了，请勿重复操作');
        }

        if (! $user->cancelAdmin()) {
            return formatRet(500, '操作失败');
        }

        return formatRet(0);
    }
}
