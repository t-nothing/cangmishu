<?php
/**
 * @Author: h9471
 * @Created: 2020/11/2 16:28
 */

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\WechatOAuthService;
use \App\Models\User;
use Illuminate\Http\Request;

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
     */
    public function callback(Request $request)
    {
        info('网页授权回调的参数为:', $request->input());

        $code = $request->input('code');

        $config = config('wechat.website_app.default');

        if (! $config) {
            return formatRet(0, '尚未配置网站应用');
        }

        $oauth = (new WechatOAuthService())->app()->oauth;

        $token = $oauth->getAccessToken($code);

        info('获取到的 token 为:', $token->toArray() ?? []);

        $userInfo = $oauth->user($token);

        info('授权获取的 userInfo 信息为:', $userInfo->toArray() ?? []);

        if (!$token || isset($token['errcode'])) {
            return formatRet(0, '授权失败');
        }

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
                'wechat_openid' => $userInfo['original']['openid'] ?? '',
                'union_id'  =>  $userInfo['origin']['unionid'] ?? '',
            ]);

            info('创建用户的参数为:', $request->toArray());

            $user = $userService->quickRegister($request);
        }

        $token = auth('jwt')->token($user);

        return redirect('https://dev.cangmishu.com/#/initPage/home', 302, [''])
            ->withHeaders(['Authorization' => 'Bearer ' . $token]);
    }
}
