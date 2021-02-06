<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Rules\PageSize;

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

        if ($user->id == app('auth')->ownerId()) {
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

        if ($user->id == app('auth')->ownerId()) {
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
