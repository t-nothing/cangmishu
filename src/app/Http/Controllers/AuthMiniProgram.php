<?php
/**
 * @Author: h9471
 * @Created: 2020/12/20 16:00
 */

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Models\Token;
use App\Models\User;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;

trait AuthMiniProgram
{
    /**
     * 处理小程序的自动登陆和注册
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Illuminate\Validation\ValidationException
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
            'language'          => 'string',
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
            'wechat_mini_program_open_id'   =>  $data['openid']??'',
        ]);//合并参数

        try {
            $user = User::where('wechat_mini_program_open_id', $request->wechat_mini_program_open_id)->first();

            if(empty($request->wechat_mini_program_open_id)) {
                throw new \Exception("OPEN ID 无法获取", 1);
            }
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
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
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
            'language'          => 'string',
            'type'              => 'required|string|in:bind,register',
            'mobile'            => 'string',
            'bind_username'     => 'required_if:type,bind|string',
            'bind_password'     => 'required_if:type,bind|string',
        ]);

        app('log')->info('处理小程序的自动登陆和注册',$request->all());
        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = Factory::miniProgram(config('wechat.mini_program_cms.default'));
        $miniData = $miniProgram->auth->session($request->code);
        if (isset($miniData['errcode'])) {
            return formatRet(500, 'code已过期或不正确', [], 200);
        }

        $user = NULL;
        if(trim($request->bind_username??"") != "" && trim($request->bind_password??"") != "") {
            $request->type = "bind";
        }
        if($request->type == "bind") {

            $guard = app('auth')->guard();

            $loginData = [
                'email'     =>  $request->bind_username ,
                'password'  =>  $request->bind_password
            ];
            if (! $data = $guard->login($loginData)) {
                Log::info('登录失败', $request->all());
                return formatRet(500, $guard->sendFailedLoginResponse());
            }

            $user = $guard->user();

        }

        info("mp", $miniData);



        $openid = $miniData['openid'];
        $weixinSessionKey = $miniData['session_key'];

        $avatar_url = str_replace('/132', '/0', $request->avatar_url);//拿到分辨率高点的头像


        $request->merge([
            'email'                         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
            'province'                      =>  $request->province??'',
            'country'                       =>  $request->country??'',
            'city'                          =>  $request->city??'',
            'avatar'                        =>  $avatar_url,
            'nickname'                      =>  $request->nick_name??'',
            'wechat_mini_program_open_id'   =>  $miniData['openid']??'',
        ]);//合并参数

        try {

            if(empty($request->wechat_mini_program_open_id)) {
                throw new \Exception("OPEN ID 无法获取", 1);
            }

            if(!$user) {
                $user = User::where('wechat_mini_program_open_id', $request->wechat_mini_program_open_id)->first();

                //如果用户不存在
                if(!$user)
                {
                    $user = app('user')->quickRegister($request);
                }
            } else {
                $user->wechat_mini_program_open_id =  $request->wechat_mini_program_open_id;
                $user->save();
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
     * 测试体验帐号登录
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testMiniProgramLogin(BaseRequests $request)
    {
        app('log')->info('测试体验帐号登录',$request->all());
        $this->validate($request, [
            'code'              => 'required|string',
            'nick_name'         => 'required|string',
            'gender'            => 'string',
            'country'           => 'string',
            'city'              => 'string',
            'avatar_url'        => 'url',
            'language'          => 'string',
            'mobile'            => 'string',
        ]);

        app('log')->info('测试体验帐号登录',$request->all());

        try {
            //绑定的一个固定帐号
            if (app()->environment() === 'production') {
                $user = User::query()->find(483);
            } else {
                $user = User::query()->find(421);
            }

            $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);
            $userId = $user->id;

            $data['token'] = $token;
            $data['modules'] = [];
            $data['user'] = User::with(['defaultWarehouse:id,name_cn'])
                ->select(['avatar', 'email','boss_id','id', 'nickname', 'default_warehouse_id'])
                ->find($userId);

            return formatRet(0, '', $data);

        } catch (\Exception $e) {
            app('log')->error($e->getMessage());

            return formatRet(500, trans("message.userNotExist"));
        }
    }
}
