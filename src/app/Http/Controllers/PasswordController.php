<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Models\Token;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    //忘记密码-根据用户邮箱发送忘记密码邮件
    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $user = User::where('email', '=', $request->email)->first();

        if (empty($user)) {
            return formatRet(404, trans("message.userEmailNotExist"), [], 404);
        }

        app('auth')->guard()->createUserResetActivation($user);// 发送重置邮件

        return formatRet(0, trans("message.userEmailSendSuccess"));
    }

    // 忘记密码-重置密码链接
    public function show(Request $request, $token_value)
    {
        $token = Token::where('token_value', '=', $token_value)
            ->where('token_type', '=', Token::TYPE_FORGET_PASSWORD)
            ->where('expired_at', '>', time())
            ->valid()
            ->latest('expired_at')
            ->first();

        if (!$token) {
            info('Invalid token.', compact('token_value'));
            return response('', 404);
        }

        $user = User::find($token->owner_user_id);

        if (!$user) {
            info('User activation fails, user does not exist.', $token->toArray());
            return response('', 404);
        }//存在token且未过期 跳转前端修改密码界面

        $url = env('RESET_PASSWORD_URL')."/#/backPassword?token=" . $token->token_value;
        app('log')->info('url地址为'.$url);
        return redirect($url);
    }

    //忘记密码--重置密码
    public function edit(Request $request)
    {
//        dd($request->all());
        $this->validate($request, [
            'token' => 'required|string|min:1',
            'password' => 'required|string|confirmed|min:6',
            'password_confirmation' => 'required|string|min:6'
        ]);

        $token = Token::where('token_value', '=', $request->token)
            ->where('token_type', '=', Token::TYPE_FORGET_PASSWORD)
            ->where('expired_at', '>', time())
            ->valid()
            ->latest('expired_at')
            ->first();

        if (!$token) {
            return formatRet(404,  trans("message.userEmailTokenInvalid"), [], 404);
        }

        if (!$user = User::find($token->owner_user_id)) {
            return formatRet(404, trans("message.userNotExist"), [], 404);
        }

        if ($user->isLocked()) {
            return formatRet(1, trans("message.userIsLocked"));
        }

        $user->password = Hash::make($request->password);

        if ($user->save()) {
            $token->delete();
            return formatRet(0, trans("message.userChangePasswordSuccess"));
        }

        return formatRet(500, trans("message.userChangePasswordFailed"));
    }

    //个人中心-修改密码
    public function selfedit(Request $request)
    {
        $this->validate($request, [
            'password_old' => 'required|string|min:1',
            'password' => 'required|string|confirmed|min:6'
        ]);

        if ($request->password_old == $request->password) {
            return formatRet(500, trans('message.userNewPassSameWithOld'));
        }

        $guard = app('auth')->guard();

        $user = User::where('id', $guard->user()->getAuthIdentifier())->first();

        if (!$guard->isValidPassWord(['email' => $user->email, 'password' => $request->password_old])) {
            return formatRet(500, trans('message.userPassWordIsWrong'));
        }

        $token = Token::where('owner_user_id', '=', $user->id)
            ->valid()
//            ->where('expired_at', '>', time())
            ->latest('expired_at')
            ->first();

        if (!$token) {
            return formatRet(404, trans("message.tokenInvalid"), [], 404);
        }

        $user->password = Hash::make($request->password);

        if ($user->save()) {
            $token->delete();
            return formatRet(0, trans('message.success'));
        }

        return formatRet(500, trans('message.failed'));
    }

}
