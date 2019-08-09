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
use GuzzleHttp\Client as HttpClient;
use App\Models\ShopUser;
use EasyWeChat\Factory;
use App\Http\Requests\BaseRequests;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class AuthenticateController extends  Controller
{
    use AuthenticatesUsers;

    /**
     * 用户名即OPEN ID
     */
    public function username()
    {
        return 'openid';
    }

    public function easyWechatGetSession($code)
    {
        $config = config('wechat.mini_program.default');
        $app = Factory::miniProgram($config);
        return $app->auth->session($code);
    }

    /**
     * 处理小程序的自动登陆和注册
     * @param $oauth
     */
    public function autoLogin(BaseRequests $request)
    {
        // 获取openid
        if ($request->filled('code')) {
            $wechatUserInfo = $this->easyWechatGetSession($request->code);
        }

        if (!$request->openid && empty($wechatUserInfo['openid'])) 
        {
            if (isset($wechatUserInfo) && !empty($wechatUserInfo['errmsg'])) {
                return formatRet(500, $wechatUserInfo['errmsg']);
            } else {
                return formatRet(401, '用户openid没有获取到');
            }
        }

        $openid = empty($wechatUserInfo['openid'])?$request->openid:$wechatUserInfo['openid'];
        $userInfo = ShopUser::where('openid', $openid)->first();

        if ($userInfo && $userInfo->toArray()) 
        {
            //执行登录
            $userInfo->last_login_ip = $this->getClientIP();
            $userInfo->last_login_time = Carbon::now();
            $userInfo->save();
            $token = $userInfo->createToken($openid)->accessToken;

            return formatRet(0, "登录成功", compact('token','userInfo'));
        } 
        else 
        {
            //执行注册
            return $this->register($request, $openid);
        }
    }

    /*
     * 用户注册
    * @param Request $request
    */
    public function register(BaseRequests $request, $openid)
    {
        //  进行基本验证
        $user_info = \GuzzleHttp\json_decode($request->input('rawData'),true);
        $newUser = [
            'openid'                =>  $openid, //openid
            'nickname'              =>  $user_info['nickName'],// 昵称
            'email'                 =>  time().'unknow@cangmishu.com',// 邮箱
            'name'                  =>  $user_info['nickName'],// 昵称
            'avatar'                =>  $user_info['avatarUrl'], //头像
            'password'              =>  Hash::make(Str::random(16)),
            'last_login_ip'         =>  $this->getClientIP(),
            'last_login_time'       =>  Carbon::now()
        ];

        $userInfo = ShopUser::create($newUser);
        $token = $userInfo->createToken($openid)->accessToken;
        return formatRet(0, "登录成功", compact('token','userInfo'));
    }


}