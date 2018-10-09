<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Token;
use App\Rules\PageSize;
use Carbon\Carbon;
use App\Rules\AlphaNumDash;
use App\Models\WarehouseEmployee;

class UserController extends Controller
{
    /**
     * 获取用户信息
     */
    public function find(Request $request)
    {
        $this->validate($request, [
            'keywords'  => 'required|string',
        ]);

        $user = User::where('email', $request->keywords)->first(['id', 'email']);

        $data = $user ? $user->toArray() : [];

        return formatRet(0, '', $data);
    }

    /**
     * 获取用户信息
     */
    public function show($user_id)
    {
        if ($user = User::find($user_id)) {
            return formatRet(0, '', $user->toArray());
        }

        return formatRet(404, '用户不存在', [], 404);
    }

    public function warehouseLists()
    {
        $warehouse_ids = WarehouseEmployee::with('warehouse')
			->where('user_id',app('auth')->id())
			->get();
        return formatRet(0, '', $warehouse_ids->toArray());
    }

    public function setWarehouse($warehouse_id)
    {
        $is_exist =  WarehouseEmployee::where('warehouse_id',$warehouse_id)->where('user_id',app('auth')->id())->first();
        if (!$is_exist){
            return formatRet(404, '用户不存在', [], 404);
        }
        $user = User::find(app('auth')->id());
        $user->default_warehouse_id = $warehouse_id;
        if(!$user->save()){
            return formatRet(400, '切换仓库失败', [], 404);
        }
        return formatRet(0, '切换仓库成功');
    }

    /**
     * 保存新用户数据
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|unique:user,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        app('db')->beginTransaction();
        try {
            $user = new User;
            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            $user->extra()->create();

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '系统保存数据失败');
        }

        // 发送激活邮件
        app('auth')->guard()->createUserActivation($user);

        return formatRet(0, '已保存到系统', $user->toArray());
    }

    /**
     * 修改用户信息
     */
    public function edit(Request $request)
    {
        $this->validate($request, [
            'user_id'  => 'required|integer|min:1',
            'nickname' => 'required|string|max:18',
        ]);

        if (! $user = User::find($request->user_id)) {
            return formatRet(5, '用户不存在');
        }

        if ($user->isLocked()) {
            return formatRet(3, '用户已被锁定');
        }

        // if (! $user->isActivated()) {
        //     return formatRet(4, '用户未被激活');
        // }

        $user->nickname = $request->nickname;

        if ($user->save()) {
            return formatRet(0, '成功');
        }

        return formatRet(1, '失败');
    }

    /**
     * 用户激活
     */
    public function activate($token_value)
    {
        $token = Token::where('token_value', '=', $token_value)
                      ->where('token_type', '=', Token::TYPE_EMAIL_CONFIRM)
                      ->valid()
                      ->latest('expired_at')
                      ->first();

        if (! $token) {
            abort(404);
        }

        // $token->setInvalid();

        if (! $user = User::find($token->owner_user_id)) {
            abort(404);
        }

        $user->setActivated();

        return view('userActivationSuccess');
    }
}
