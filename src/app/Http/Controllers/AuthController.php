<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/6
 * Time: 16:34
 */

namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use Illuminate\Http\Request;

class AuthController extends  Controller
{
    /**
     * 登入
     */
    public function login(BaseRequests $request)
    {
        $this->validate($request, [
            'email'    => 'required',
            'password' => 'required|string',
        ]);
        $guard = app('auth')->guard();

        if (! $data = $guard->login($guard->credentials())) {
            return formatRet(500, $guard->sendFailedLoginResponse());
        }

        $data['user'] = $guard->user();

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