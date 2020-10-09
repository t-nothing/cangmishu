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
use App\Models\User;
use App\Models\Token;
use App\Models\VerifyCode;
use Carbon\Carbon;
use Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AuthController extends  Controller
{

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
            \Log::info('找到不用户', $request->all());
            return formatRet(500, trans("message.userNotExist"));
        }

        $code = app('user')->getRandCode();
        app('user')->createUserSMSVerifyCode($code,$request->mobile);
        return formatRet(0, trans("message.userRegisterSendSuccess"));

    }

    /**
     *  创建TOKEN登录
     * 生成一个新的 token，token 哈希来保证唯一性。
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return \App\Models\Token|null
     */
    private function createToken($user, $type) {
        $token = new Token;
        $token->token_type = $type;
        $token->token_value = hash_hmac('sha256', $user->getAuthIdentifier() . microtime(), config('APP_KEY'));
        $token->expired_at = Carbon::now()->addWeek();
        $token->owner_user_id = $user->getAuthIdentifier();
        $token->is_valid = Token::VALID;

        if ($token->save()) {
            return $token;
        }

        return;
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

        $verify_code = VerifyCode::where('code',$request->code)->where('email',$request->mobile)->where('expired_at','>',time())->first();
        if(!$verify_code){
            return formatRet(500, trans("message.userSMSExpired"));
        }

        $user = User::where('phone', $request->mobile)->first();

        if(!$user) {
            \Log::info('找到不用户', $request->all());
            return formatRet(500, trans("message.userNotExist"));
        }

   
        \Log::info('找到用户', $user->toArray());
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
            \Log::info('登录失败', $request->all());
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

    /**
     * 处理小程序的自动登陆和注册
     * @param $oauth
     */
    public function checkMiniProgramLogin(BaseRequests $request)
    {
        app('log')->info('检查小程序的自动登陆和注册',$request->all());
        $this->validate($request, [
            'code'              => 'required|string',
            'nick_name'         => 'required|string',
            'gender'            => 'string',
            'country'           => 'string',
            'city'              => 'string',
            'avatar_url'        => 'url',
            'language'          => 'required|string',
            'mobile'            => 'string',
        ]);

        app('log')->info('处理小程序的自动登陆和注册',$request->all());
        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = Factory::miniProgram(config('wechat.mini_program_cms.default'));
        $data = $miniProgram->auth->session($request->code);
        if (isset($data['errcode'])) {
            return formatRet(500, 'code已过期或不正确', [], 200);
        }

        $openid = $data['openid'];
        $weixinSessionKey = $data['session_key'];

        $avatar_url = str_replace('/132', '/0', $request->avatar_url);//拿到分辨率高点的头像


        $request->merge([
            'email'                         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
            'province'                      =>  $request->province??'',
            'country'                       =>  $request->country??'',
            'city'                          =>  $request->city??'',
            'avatar'                        =>  $avatar_url,
            'nickname'                      =>  $request->mobile??'',
            'wechat_mini_program_open_id'   =>  $data['wechat_mini_program_open_id']??'',
        ]);//合并参数

        try {
            $user = User::where('wechat_mini_program_open_id', $request->wechat_mini_program_open_id)->first();

            //如果用户不存在
            if(!$user)
            {
                //交给前端去判断要不要创新新用户还是绑定新用户
                return formatRet(500, trans("message.userNotExist") , [
                    "user"  =>  null
                ],200);
            } 

            $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);
            $userId = $user->id;

            $data['token'] = $token;
            $data['modules'] = [];
            $data['user'] = User::with(['defaultWarehouse:id,name_cn'])->select(['avatar', 'email','boss_id','id', 'nickname', 'default_warehouse_id'])->find($userId);

            return formatRet(0, '', $data);

        } catch (\Exception $e) {
            app('log')->error($e->getMessage());

            return formatRet(500, trans("message.userNotExist"));
        }
        
    }

    /**
     * 处理小程序的自动登陆和注册
     * @param $oauth
     */
    public function autoMiniProgramLogin(BaseRequests $request)
    {
        app('log')->info('处理小程序的自动登陆和注册',$request->all());
        $this->validate($request, [
            'code'              => 'required|string',
            'nick_name'         => 'required|string',
            'gender'            => 'string',
            'country'           => 'string',
            'city'              => 'string',
            'avatar_url'        => 'url',
            'language'          => 'required|string',
            'type'              => 'required|string|in:bind,register',
            'mobile'            => 'string',
            'bind_username'     => 'required_if:type,bind|string',
            'bind_password'     => 'required_if:type,bind|string',
        ]);

        app('log')->info('处理小程序的自动登陆和注册',$request->all());
        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = Factory::miniProgram(config('wechat.mini_program_cms.default'));
        $data = $miniProgram->auth->session($request->code);
        if (isset($data['errcode'])) {
            return formatRet(500, 'code已过期或不正确', [], 200);
        }

        $user = NULL;
        if($request->type == "bind") {

            $guard = app('auth')->guard();

            if (! $data = $guard->login($request->only('bind_username', 'bind_password'))) {
                \Log::info('登录失败', $request->all());
                return formatRet(500, $guard->sendFailedLoginResponse());
            }

            $user = $guard->user();

        }

       

        $openid = $data['openid'];
        $weixinSessionKey = $data['session_key'];

        $avatar_url = str_replace('/132', '/0', $request->avatar_url);//拿到分辨率高点的头像


        $request->merge([
            'email'                         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
            'province'                      =>  $request->province??'',
            'country'                       =>  $request->country??'',
            'city'                          =>  $request->city??'',
            'avatar'                        =>  $avatar_url,
            'nickname'                      =>  $request->mobile??'',
            'wechat_mini_program_open_id'   =>  $data['wechat_mini_program_open_id']??'',
        ]);//合并参数

        try {

            if(!$user) {
                $user = User::where('wechat_mini_program_open_id', $request->wechat_mini_program_open_id)->first();

                //如果用户不存在
                if(!$user)
                {
                    $user = app('user')->quickRegister($request);
                } 
            } else {
                User::find($bindUser->id)->update(
                    "wechat_mini_program_open_id", 
                    $request->wechat_mini_program_open_id
                );
            }
            

            $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);
            $userId = $user->id;

            $data['token'] = $token;
            $data['modules'] = [];
            $data['user'] = User::with(['defaultWarehouse:id,name_cn'])->select(['avatar', 'email','boss_id','id', 'nickname', 'default_warehouse_id'])->find($userId);

            return formatRet(0, '', $data);

        } catch (\Exception $e) {
            app('log')->error($e->getMessage());

            return formatRet(500, trans("message.userNotExist"));
        }
        
    }
}