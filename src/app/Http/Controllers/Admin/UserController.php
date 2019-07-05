<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;

/**
 * 用户管理
 */
class UserController extends Controller
{
    /**
     * 用户 - 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'keywords'     => 'string',
            'created_at_b' => 'date_format:Y-m-d',
            'created_at_e' => 'date_format:Y-m-d|after_or_equal:created_at_b',
        ]);

        $users = new User();

        if ($request->filled('keywords')) {
            $users->hasKeyword($request->keywords);
        }

        if ($request->filled('created_at_b')) {
            $users->where('created_at', '>', strtotime($request->created_at_b . ' 00:00:00'));
        }

        if ($request->filled('created_at_e')) {
            $users->where('created_at', '<', strtotime($request->created_at_e . ' 23:59:59'));
        }

        $list = $users->where('boss_id','=',0)->latest()->paginate($request->input('page_size'));

        return formatRet(0, '', $list->toArray());
    }

    /**
     * 用户 - 详情
     */
    public function show($user_id)
    {
        if (! $user = User::find($user_id)) {
            return formatRet(404, '用户不存在', [], 404);
        }

        return formatRet(0, '', $user->toArray());
    }

    /**
     * 用户 - 禁用
     */
    public function lock(Request $request)
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

        $user->is_locked = User::LOCKED;

        if (! $user->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    /**
     * 用户 - 启用
     */
    public function unlock(Request $request)
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

        $user->is_locked = User::UNLOCKED;

        if (! $user->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
