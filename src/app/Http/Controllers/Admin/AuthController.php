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

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * 登入
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $guard = app('auth')->guard();

        if (! $data = $guard->login($guard->credentials(), true)) {
            return formatRet(500, $guard->sendFailedLoginResponse());
        }

        $data['user'] = $guard->user();

        return formatRet(0, '', $data);
    }
}
