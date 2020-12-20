<?php
/**
 * @Author: h9471
 * @Created: 2020/11/2 16:28
 */

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Guard\TokenCreator;
use App\Models\Token;
use App\Services\UserService;
use App\Services\WechatOAuthService;
use \App\Models\User;
use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class WebsiteAppController extends Controller
{
    public function info()
    {
        return formatRet(0, __('message.success'), [
            'app_id' => config('wechat.website_app.default.app_id'),
            'callback_url' => config('app.url') . '/wechatOAuth/callback',
        ]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     * @deprecated
     */
    public function oldCallback(Request $request)
    {
        [$state, $token, $userInfo] =  $this->parseOAuthInfo($request);

        $user = User::query()->where('union_id', $token['unionid'])->first();

        if (! $user) {
            app('log')->info('网页登录没找到用户, 新建用户');

            $userService = new UserService();

            $request->merge([
                'email'     => sprintf("%s_%s@cangmishu.com", time(), $userService->getRandCode()),
                'province'  => $userInfo['original']['province'] ?? '',
                'country'   => $userInfo['original']['country'] ?? '',
                'city'      => $userInfo['original']['city'] ?? '',
                'avatar'    => $userInfo['original']['headimgurl'] ?? '',
                'nickname'  => $userInfo['original']['nickname'] ?? '',
                'app_openid' =>  $userInfo['original']['openid'] ?? null,
                'union_id'  =>   $userInfo['original']['unionid'] ?? null,
            ]);

            info('创建用户的参数为:', $request->toArray());

            $user = $userService->quickRegister($request);
        }

        $token = auth('admin')->token($user);

        return redirect(base64_decode($state) . '?token=' . $token, 302);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws BusinessException
     */
    public function callback(Request $request)
    {
        [$state, $token, $userInfo] =  $this->parseOAuthInfo($request);

        /** @var User $user */
        $user = User::query()->where('union_id', $token['unionid'])->first();

        if (! $user) {
            app('log')->info('网页登录没找到用户, 新建用户');

            $data = [$state, $token, $userInfo];

            $key = md5(Str::random() . Carbon::now()->unix());

            if (Cache::put($key, $data, 60 * 15)) {
                return formatRet(10000, '进入绑定流程', ['secret' => $key]);
            }
        }

        $token = auth('admin')->token($user);

        return redirect(base64_decode($state) . '?token=' . $token, 302);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws BusinessException
     */
    public function bindAccount(Request $request)
    {
        $data = $request->validate([
            'secret' => 'required',
            'type' => 'required',
            'email' => 'required_if:type,1|string',
            'password' => 'required_if:type,1|string',
        ]);

        if (! $authData = Cache::get($data['secret'])) {
            throw new BusinessException('认证密钥错误');
        }

        switch ($data['type']) {
            //直接使用用户名和密码登录
            case 1:
                /** @var User $user */
                $user = User::query()->where('email', $data['email'])->first();

                if (! password_verify($data['password'], $user->password)) {
                    throw new BusinessException('用户认证失败');
                }
                break;
            case 2: //扫描公众二维码登录
                break;
            default:
                info('网页登录没找到用户, 新建用户');

                $userService = new UserService();

                $request->merge([
                    'email'     => sprintf("%s_%s@cangmishu.com", time(), $userService->getRandCode()),
                    'province'  => $userInfo['original']['province'] ?? '',
                    'country'   => $userInfo['original']['country'] ?? '',
                    'city'      => $userInfo['original']['city'] ?? '',
                    'avatar'    => $userInfo['original']['headimgurl'] ?? '',
                    'nickname'  => $userInfo['original']['nickname'] ?? '',
                    'app_openid' =>  $userInfo['original']['openid'] ?? null,
                    'union_id'  =>   $userInfo['original']['unionid'] ?? null,
                ]);

                info('创建用户的参数为:', $request->toArray());

                $user = $userService->quickRegister($request);
                break;
        }

        $this->updateUserInfo($user, $authData[2]);

        return success(['data' => (new TokenCreator())->create($user, Token::TYPE_ACCESS_TOKEN)]);
    }

    /**
     * @param  User  $user
     * @param  ArrayAccess $userInfo
     * @throws BusinessException
     */
    protected function updateUserInfo(User $user, ArrayAccess $userInfo)
    {
        $res = $user->update([
            'app_openid' =>  $userInfo['original']['openid'] ?? null,
            'union_id'  =>   $userInfo['original']['unionid'] ?? null,
        ]);

        if (! $res) {
            throw new BusinessException('用户信息绑定失败');
        }
    }

    /**
     * 获取并解析授权毁掉信息
     *
     * @param  Request  $request
     * @return array|\Illuminate\Http\JsonResponse
     * @throws BusinessException
     */
    protected function parseOAuthInfo(Request $request)
    {
        info('网页授权回调的参数为:', $request->input());

        $code = $request->input('code');
        $state = $request->input('state');

        $config = config('wechat.website_app.default');

        if ( ! $config) {
            return formatRet(0, '尚未配置网站应用');
        }

        $oauth = (new WechatOAuthService())->app()->oauth;

        $token = $oauth->getAccessToken($code);

        info('获取到的 token 为:', $token->toArray() ?? []);

        $userInfo = $oauth->user($token);

        info('授权获取的 userInfo 信息为:', $userInfo->toArray() ?? []);

        if ( ! $token || isset($token['errcode'])) {
            info('网站应用授权失败', ['token' => $token->toArray()]);
            throw new BusinessException('网站应用授权失败');
        }

        return [$state, $token, $userInfo];
    }
}
