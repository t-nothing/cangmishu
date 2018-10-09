<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserEmployee;
use App\Rules\PageSize;
use App\Rules\AlphaNumDash;

/**
 * 用户员工管理
 */
class EmployeeController extends Controller
{
    /**
     * 员工列表
     */
	public function list(Request $request)
	{
		$this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'keywords'     => 'string',
        ]);

        $userEmployee = UserEmployee::with(['employee:id,email,last_login_at'])
                                    ->where('boss_id', app('auth')->id())
        							->latest();

        if ($request->filled('keywords')) {
            $userEmployee->hasKeyword($request->keywords);
        }

        $data = $userEmployee->paginate($request->input('page_size'), [
            'id',
            'user_id',
            'boss_id',
            'name',
            'phone',
            'created_at',
            'updated_at',
        ]);

        return formatRet(0, '', $data->toArray());
	}

    /**
     * 获取员工信息
     */
    public function show($user_id)
    {
        $boss_id = app('auth')->id();

        $userEmployee = UserEmployee::where('user_id', $user_id)
                                    ->where('boss_id', $boss_id)
                                    ->first([
                                        'id',
                                        'user_id',
                                        'boss_id',
                                        'name',
                                        'phone',
                                        'created_at',
                                        'updated_at',
                                    ]);

        if (! $userEmployee) {
            return formatRet(404, '在员工列表中找不到该员工');
        }

        $userEmployee->load(['employee:id,email,last_login_at']);

        return formatRet(0, '', $userEmployee->toArray());
    }

    /**
     * 添加员工
     */
	public function create(Request $request)
	{
		$this->validate($request, [
            'user_id' => 'required|integer|min:1',
            'name'    => 'present|string|max:255',
            'phone'   => 'present|string|max:20',
        ]);

        $boss_id = app('auth')->id();

        if (! $user = User::find($request->user_id)) {
            return formatRet(404, '找不到该用户');
        }

        if ($user->id == $boss_id) {
            return formatRet(500, '不能操作自己');
        }

        if (UserEmployee::where('user_id', $user->id)->where('boss_id', $boss_id)->first()) {
            return formatRet(500, '已添加该用户，请勿重复操作');
        }

        if (UserEmployee::where('user_id', $boss_id)->where('boss_id', $user->id)->first()) {
            return formatRet(500, '无法添加该用户');
        }

        $userEmployee = new UserEmployee;
        $userEmployee->user_id = $user->id;
        $userEmployee->boss_id = $boss_id;
        $userEmployee->name    = $request->name;
        $userEmployee->phone   = $request->phone;

        if (! $userEmployee->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
	}

    /**
     * 更新员工信息
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|min:1',
            'name'    => 'present|string|max:255',
            'phone'   => 'present|string|max:20',
        ]);

        $boss_id = app('auth')->id();

        $userEmployee = UserEmployee::where('user_id', $request->user_id)
                                    ->where('boss_id', $boss_id)
                                    ->first();

        if (! $userEmployee) {
            return formatRet(404, '在员工列表中找不到该员工');
        }

        $userEmployee->name  = $request->name;
        $userEmployee->phone = $request->phone;

        if (! $userEmployee->save()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    /**
     * 删除员工
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|min:1',
        ]);

        $boss_id = app('auth')->id();

        $userEmployee = UserEmployee::where('user_id', $request->user_id)
                                    ->where('boss_id', $boss_id)
                                    ->first();

        if (! $userEmployee) {
            return formatRet(404, '在员工列表中找不到该员工');
        }

        if (! $userEmployee->delete()) {
            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
