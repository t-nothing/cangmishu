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

use App\Exceptions\BusinessException;
use App\Http\Requests\BaseRequests;
use App\Models\Token;
use App\Models\User;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;

trait AuthMiniProgram
{
    public static int $OK = 0;
    public static int $IllegalAesKey = -41001;
    public static int $IllegalIv = -41002;
    public static int $IllegalBuffer = -41003;
    public static int $DecodeBase64Error = -41004;

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
            'iv'                =>  ['required', 'string'],
            'encrypted_data'    =>  ['required', 'string'],
            'nick_name'         => 'required|string',
            'gender'            => 'string',
            'country'           => 'string',
            'city'              => 'string',
            'avatar_url'        => 'url',
            'language'          => 'string',
            'mobile'            => 'string',
        ]);

        info('处理小程序的自动登陆和注册',$request->all());
        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = Factory::miniProgram(config('wechat.mini_program_cms.default'));
        $data = $miniProgram->auth->session($request->code);

        if (isset($data['errcode'])) {
            return formatRet(500, 'code已过期或不正确', [], 200);
        }

        $decrypt = $this->decrypt(
            config('wechat.mini_program_cms.default.app_id'),
            $data['session_key'],
            $request['iv'],
            $request['encrypted_data']
        );

        info('解密后的数据为:' . $decrypt);

        if ($decrypt === '') {
            return formatRet(0, '加密数据解密失败,请重试');
        }

        $openData = json_decode($decrypt, true);
        //拿到分辨率高点的头像
        $avatar_url = str_replace('/132', '/0', $request->avatar_url);

        $request->merge([
            'email'                         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
            'province'                      =>  $request->province??'',
            'country'                       =>  $request->country??'',
            'city'                          =>  $request->city??'',
            'avatar'                        =>  $avatar_url,
            'nickname'                      =>  $request->mobile??'',
            'wechat_mini_program_open_id'   =>  $data['openid']??'',
            'union_id' => $openData['unionId'] ?? null,
            'app_openid' => $openData['openId'] ?? null,
        ]);//合并参数

        try {
            if (empty($request->union_id)) {
                throw new \Exception("UNION ID 无法获取", 1);
            }
            /** @var User $user */
            $user = User::where('union_id', $request->union_id)->first();
            //如果用户不存在
            if (! $user) {
                $oldUser = User::where('wechat_mini_program_open_id', $request->wechat_mini_program_open_id)->first();

                if ($oldUser) {
                    return $this->bindOpenIdAndLogin($oldUser, $openData);
                }
                //交给前端去判断要不要创新新用户还是绑定新用户
                return formatRet(500, trans("message.userNotExist"), [
                    "user" => null
                ]);
            }

            $data = array_merge($data, $this->responseWithTokenAndUserInfo($user));

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
        info('处理小程序的自动登陆和注册', $request->all());

        $this->validate($request, [
            'code'              => 'required|string',
            'nick_name'         => 'required|string',
            'iv'                =>  ['required', 'string'],
            'encrypted_data'    =>  ['required', 'string'],
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

        $miniProgram = Factory::miniProgram(config('wechat.mini_program_cms.default'));
        // 根据 code 获取微信 openid 和 session_key
        $miniData = $miniProgram->auth->session($request->code);

        if (isset($miniData['errcode'])) {
            return formatRet(500, 'code已过期或不正确', []);
        }

        $decrypt = $this->decrypt(
            config('wechat.mini_program_cms.default.app_id'),
            $miniData['session_key'],
            $request['iv'],
            $request['encrypted_data']
        );

        info('解密后的数据为:' . $decrypt);

        if ($decrypt === '') {
            return formatRet(0, '加密数据解密失败,请重试');
        }

        $openData = json_decode($decrypt, true);

        $user = NULL;

        if (trim($request->bind_username ?? "") != ""
            && trim($request->bind_password ?? "") != ""
        ) {
            $request->type = "bind";
        }

        if ($request->type == "bind") {
            $guard = app('auth')->guard();

            $loginData = [
                'email' => $request->bind_username,
                'password' => $request->bind_password
            ];

            if (! $data = $guard->login($loginData)) {
                Log::info('登录失败', $request->all());
                return formatRet(500, $guard->sendFailedLoginResponse());
            }

            $user = $guard->user();

        }

        info("mp", $miniData);

        $avatar_url = str_replace('/132', '/0', $request->avatar_url);//拿到分辨率高点的头像

        $request->merge([
            'email'                         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
            'province'                      =>  $request->province??'',
            'country'                       =>  $request->country??'',
            'city'                          =>  $request->city??'',
            'avatar'                        =>  $avatar_url,
            'nickname'                      =>  $request->nick_name??'',
            'wechat_mini_program_open_id'   =>  $miniData['openid'] ?? '',
            'union_id' => $openData['unionId'] ?? null,
            'app_openid' => $openData['openId'] ?? null,
        ]);

        try {
            if(! $request->union_id) {
                throw new \Exception("UNION_ID无法获取", 1);
            }

            //不是绑定用户
            if (! $user) {
                $user = User::where('union_id', $request->union_id)->first();
                //如果用户不存在
                if (! $user) {
                    $oldUser = User::where('wechat_mini_program_open_id', $request->wechat_mini_program_open_id)->first();

                    if ($oldUser) {
                        return $this->bindOpenIdAndLogin($oldUser, $openData);
                    }

                    $user = app('user')->quickRegister($request);
                }
            } else {
                //更新新的信息
                $user->wechat_mini_program_open_id = $request->wechat_mini_program_open_id;
                $user->union_id = $openData['unionId'] ?? null;
                $user->app_openid = $openData['openId'] ?? null;
                $user->save();
            }

            $data = array_merge($data ?? [], $this->responseWithTokenAndUserInfo($user));

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
        info('测试体验帐号登录', $request->all());

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

        info('测试体验帐号登录',$request->all());

        try {
            //绑定的一个固定帐号
            if (app()->environment() === 'production') {
                $user = User::query()->find(483);
            } else {
                $user = User::query()->find(421);
            }

            $data = $this->responseWithTokenAndUserInfo($user);

            return formatRet(0, '', $data);

        } catch (\Exception $e) {
            app('log')->error($e->getMessage());

            return formatRet(500, trans("message.userNotExist"));
        }
    }

    /**
     * @param  $user
     * @return mixed
     */
    protected function responseWithTokenAndUserInfo($user)
    {
        $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);
        $userId = $user->id;

        $data['token'] = $token;
        $data['modules'] = [];
        $data['user'] = User::with(['defaultWarehouse:id,name_cn'])
            ->select(['avatar', 'email', 'boss_id', 'id', 'nickname', 'default_warehouse_id'])
            ->find($userId);

        return $data;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($encryptedData, $iv, $sessionKey, $appId, &$data)
    {
        if (strlen($sessionKey) !== 24) {
            return self::$IllegalAesKey;
        }

        $aesKey = base64_decode($sessionKey);

        if (strlen($iv) !== 24) {
            return self::$IllegalIv;
        }

        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);

        if ($dataObj === null) {
            return self::$IllegalBuffer;
        }

        if ($dataObj->watermark->appid !== $appId) {
            return self::$IllegalBuffer;
        }

        $data = $result;

        return self::$OK;
    }

    /**
     * @param $appId
     * @param $sessionKey
     * @param $iv
     * @param $encryptedData
     * @return string
     */
    protected function decrypt($appId, $sessionKey, $iv, $encryptedData)
    {
        $errCode = $this->decryptData($encryptedData, $iv, $sessionKey, $appId, $data);

        info('解密结果为:' . $data);
        if ($errCode === 0) {
            return $data;
        }

        info('解密微信数据失败,错误码为:' . $errCode);
        return '';
    }

    /**
     * @param  User  $oldUser
     * @param  array  $openData
     * @return array|mixed|void
     * @throws BusinessException
     */
    protected function bindOpenIdAndLogin(User $oldUser, array $openData)
    {
        $res = $oldUser->update([
            'app_openid' =>  $openData['openId'],
            'union_id'  =>   $openData['unionId'],
        ]);

        if ($res) {
           return $this->responseWithTokenAndUserInfo($oldUser);
        }

        throw new BusinessException('登录失败');
    }
}
