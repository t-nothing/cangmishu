<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/6
 * Time: 16:34
 */

namespace App\Http\Controllers;

use App\Guard\JwtGuard;
use App\Guard\TokenCreator;
use App\Http\Requests\BaseRequests;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use App\Models\VerifyCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthController extends  Controller
{
    use AuthMiniProgram;

    public  function getSmsVerifyCode(BaseRequests $request)
    {
        $this->validate($request,[
            'mobile'        =>  ['required','mobile'],
            'captcha_key'   =>  'required|string|min:1',
            'captcha'       =>  'required|string'
        ]);

        if($request->captcha_key != "app") {
            if (strtoupper(Cache::tags(['captcha'])->get($request->captcha_key)) != strtoupper($request->captcha)) {
                return formatRet(500, trans("message.userRegisterEmailVerifyCodeFailed"));
            }
            Cache::tags(['captcha'])->forget($request->captcha_key);
        }

        $user = User::where('phone', $request->mobile)->first();

        if(!$user) {
            Log::info('找到不用户', $request->all());
            return formatRet(500, trans("message.userNotExist"));
        }

        $userService = new UserService;

        $code = $userService->getRandCode();

        $userService->createUserSMSVerifyCode($code, $request->mobile);

        return formatRet(0, trans("message.userRegisterSendSuccess"));
    }

    /**
     *  创建TOKEN登录
     * 生成一个新的 token，token 哈希来保证唯一性。
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return \App\Models\Token|null
     */
    private function createToken($user, $type)
    {
        return (new TokenCreator())->create($user, $type);
    }

    /**
     * 手机短信验证码登录
     **/
    public function smsLogin(BaseRequests $request)
    {
        $this->validate($request, [
            'mobile'     => 'required|mobile|string',
            'code'      => 'required|string',
        ]);

        $verify_code = VerifyCode::where('code',$request->code)
            ->where('email',$request->mobile)
            ->where('expired_at','>',time())
            ->first();

        if(!$verify_code){
            return formatRet(500, trans("message.userSMSExpired"));
        }

        $user = User::where('phone', $request->mobile)->first();

        if(!$user) {
            Log::info('找到不用户', $request->all());
            return formatRet(500, trans("message.userNotExist"));
        }


        Log::info('找到用户', $user->toArray());
        $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);
        $userId = $user->id;

        $data['token'] = $token;
        $data['modules'] = [];
        $data['user'] = User::with(['defaultWarehouse:id,name_cn'])->select(['avatar', 'email','boss_id','id', 'nickname', 'default_warehouse_id'])->find($userId);

        return formatRet(0, '', $data);
    }

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
            Log::info('登录失败', $request->all());
            return formatRet(500, $guard->sendFailedLoginResponse());
        }

        $data['user'] = $guard->user();


        $filtered = collect($data['user'])
            ->only(['avatar', 'email', 'phone', 'boss_id','id', 'nickname', 'default_warehouse']);
        $data['user'] = $filtered->all();
        //如果有填写qrkey
        if($request->filled('qr_key')) {
            if (Cache::tags(['wechat'])->has($request->qr_key)) {
                $data = Cache::tags(['wechat'])->get($request->qr_key);
                if($data['is_valid']) {
                    User::find($guard->user()->id)->update(
                        [
                            "wechat_openid" => $data['open_id']
                        ]);
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
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function expLogin()
    {
        /** @var JwtGuard $guard */
        $guard = auth()->guard('admin');

        if (! $data = $guard->userLogin(421)) {
            return formatRet(500, $guard->sendFailedLoginResponse());
        }

        $data['user'] = $guard->user();

        $filtered = collect($data['user'])
            ->only(['avatar', 'email', 'phone', 'boss_id','id', 'nickname', 'default_warehouse']);
        $data['user'] = $filtered->all();

        //获取用户权限
        $modules = app('module')->getModulesByUser(
            $guard->user(),
            $guard->user()->default_warehouse_id
        );

        $modules = collect($modules)->pluck('id')->toArray();
        $modules = array_unique($modules);
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

    public function me()
    {
        $user = auth('admin')->user();

        if (! $user) {
            return formatRet(401, '');
        }

        $data['user'] = User::with(['defaultWarehouse:id,name_cn'])->find($user->id);

        $filtered = collect($data['user'])
            ->only(['avatar', 'email', 'phone', 'boss_id', 'id', 'nickname', 'default_warehouse']);
        $data['user'] = $filtered->all();

        //获取用户权限
        $modules = app('module')->getModulesByUser(
            $user,
            $user->default_warehouse_id
        );

        $modules = collect($modules)->pluck('id')->toArray();
        $modules = array_unique($modules);
        sort($modules);
        $data['modules'] = $modules;

        return formatRet(0, '', $data);
    }
}
