<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/6
 * Time: 16:34
 */

namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use App\Models\GroupModuleRel;
use App\Models\Modules;
use Illuminate\Http\Request;
use EasyWeChat\Factory;

class AuthController extends  Controller
{

    /**
     * 登入
     */
    public function login(BaseRequests $request)
    {
        $this->validate($request, [
            'email'     => 'required|string',
            'password'  => 'required|string',
            'qr_key'    => 'string',
        ]);

        $guard = app('auth')->guard();

        if (! $data = $guard->login($guard->credentials())) {
            return formatRet(500, $guard->sendFailedLoginResponse());
        }

        $data['user'] = $guard->user();
        
        $filtered = collect($data['user'])->only(['avatar', 'email','boss_id','id', 'nickname', 'default_warehouse']);
        $data['user'] = $filtered->all();
        //如果有填写qrkey
        if($request->filled('qr_key')) {
            if (Cache::tags(['wechat'])->has($request->qr_key)) {
                $data = Cache::tags(['wechat'])->get($request->qr_key);
                if($data['is_valid']) {
                    User::find($guard->user()->id)->update("wechat_openid", $data['open_id']);
                }
            }
        }
        
        //获取用户权限
        $modules =app('module')->getModulesByUser($guard->user(),$guard->user()->default_warehouse_id);
        $modules = collect($modules)->pluck('id')->toArray();
        $modules =array_unique($modules);
        sort($modules);
        $data['modules'] = $modules;
        return formatRet(0, '', $data);
    }

    /**
     * 登出
     */
    public function logout(Request $request)
    {
        $guard = app('auth')->guard();

        $guard->logout();

        return formatRet(0, '');
    }

    /**
     * 当前用户信息
     */
    public function me()
    {
        $user = app('auth')->user();
        $data = $user->toArray();

        $data['certification_owner_status'] = 0;
        $data['certification_renter_status'] = 0;

        if ($user['is_activated'] != 1) {
            return formatRet(0, trans('message.activeAccount'), $user->toArray());
        }

        if (isset($user->extra->is_certificated_creator) && $user->extra->is_certificated_creator == 1) {
            $data['certification_owner_status'] = 2;
        } else {
            if ($owner_info = UserCertificationOwner::where('user_id', app('auth')->realUser())->latest()->first()) {
                $data['certification_owner_status'] = $owner_info->status;
            }
        }

        if (isset($user->extra->is_certificated_renter) && $user->extra->is_certificated_renter == 1) {
            $data['certification_renter_status'] = 2;
        } else {
            if ($renter_info = UserCertificationRenters::where('user_id', app('auth')->realUser())->latest()->first()) {
                $data['certification_renter_status'] = $renter_info->status;
            }
        }

        return formatRet(0, '', $data);
    }
}