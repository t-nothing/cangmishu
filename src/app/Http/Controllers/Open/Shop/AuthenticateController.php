<?php
/**
 * 小店铺登录鉴权.
 */

namespace App\Http\Controllers\Open\Shop;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;
use App\Models\ShopUser;
use EasyWeChat\Factory;
use App\Http\Requests\BaseRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Access\AuthorizationException;


class AuthenticateController extends  Controller
{
    use AuthenticatesUsers;

    private $app;

    /**
     * 用户名即OPEN ID
     */
    public function username()
    {
        return 'openid';
    }

    /**
     * 处理小程序的自动登陆和注册
     * @param $oauth
     */
    public function autoLogin(BaseRequests $request)
    {
        $this->validate($request, [
            'code'              => 'required|string',
            // 'shopid'            => 'required|int',
            'nick_name'         => 'required|string',
            'gender'            => 'required|string',
            'country'           => 'required|string',
            'city'              => 'required|string',
            'avatar_url'        => 'required|url',
            'language'          => 'required|string',
            'mobile'             => 'string',
        ]);

        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = Factory::miniProgram(config('wechat.mini_program.default'));
        $data = $miniProgram->auth->session($request->code);
        if (isset($data['errcode'])) {
            return formatRet(401, 'code已过期或不正确', 401);
        }

        $openid = $data['openid'];
        $weixinSessionKey = $data['session_key'];

        $avatar_url = str_replace('/132', '/0', $request->avatar_url);//拿到分辨率高点的头像
        $country    = $request->country??'';
        $province   = $request->province??'';
        $city       = $request->city??'';
        $gender     = $request->gender == '1' ? '1' : '2';//没传过性别的就默认女的吧，体验好些
        $language   = $request->language??'';
        $mobile      = $request->mobile??'';


        $user = ShopUser::where('weapp_openid', $openid)->first();
        if(!$user)
        {

            $user = new ShopUser;
            $user->weapp_openid     = $openid;
            $user->password         = $weixinSessionKey;
            $user->nick_name        = $request->nick_name??'';
            $user->avatar_url       = $avatar_url;
            $user->gender           = $gender;
            $user->country          = $country;
            $user->province         = $province;
            $user->city             = $city;
            $user->language         = $language;
            $user->city             = $city;
            $user->save();
        }

        $user->last_login_time  = Carbon::now()->timestamp;
        $user->last_login_ip  = $request->getClientIP();
        $user->weapp_session_key  = $weixinSessionKey;
        $user->avatar_url  = $avatar_url;
        $user->save();

        // 直接创建token并设置有效期
        $createToken = $user->createToken($user->weapp_openid);
        $createToken->token->expires_at = Carbon::now()->addDays(30);
        $createToken->token->save();
        $token = $createToken->accessToken;

        return formatRet(0, "登录成功", [
            'access_token'=>    $token,
            'token_type' => "Bearer",
            'expires_in' => Carbon::now()->addDays(30),
            'data' => $user,
        ]);
    }

}