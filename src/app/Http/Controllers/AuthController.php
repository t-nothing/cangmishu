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

use App\Guard\JwtGuard;
use App\Guard\TokenCreator;
use App\Http\Requests\BaseRequests;
use App\Services\UserService;
use App\Models\User;
use App\Models\Token;
use App\Models\VerifyCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthController extends  Controller
{
    use AuthMiniProgram;

    /**
     * 获取验证码
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getSmsVerifyCode(BaseRequests $request)
    {
        $this->validate($request, [
            'mobile' => ['required', 'mobile'],
            'captcha_key' => 'required|string|min:1',
            'captcha' => 'required|string',
        ]);

        if ($request->captcha_key != "app") {
            if (strtoupper(Cache::tags(['captcha'])->get($request->captcha_key)) != strtoupper($request->captcha)) {
                return formatRet(500, trans("message.userRegisterEmailVerifyCodeFailed"));
            }
            Cache::tags(['captcha'])->forget($request->captcha_key);
        }

        $user = User::where('phone', $request->mobile)->first();

        if (! $user) {
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
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
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

        $data =  $this->responseWithTokenAndUserInfo($user);

        return formatRet(0, '', $data);
    }

    /**
     * 登录
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
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
     * 体验账号登录
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function expLogin()
    {
        /** @var JwtGuard $guard */
        $guard = auth()->guard('admin');

        if (app()->environment() === 'production') {
            if (! $data = $guard->userLogin(483)) {
                return formatRet(500, $guard->sendFailedLoginResponse());
            }
        } else {
            if (! $data = $guard->userLogin(421)) {
                return formatRet(500, $guard->sendFailedLoginResponse());
            }
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $guard = app('auth')->guard();

        $guard->logout();

        return formatRet(0, '');
    }

    /**
     * 个人信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
