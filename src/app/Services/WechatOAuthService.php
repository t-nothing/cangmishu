<?php
/**
 * @Author: h9471
 * @Created: 2020/11/2 11:36
 */

namespace App\Services;

use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;

class WechatOAuthService
{
    protected Application $app;

    public function __construct()
    {
        $this->app = Factory::officialAccount(config('wechat.website_app.default'));
    }

    public function getAppId()
    {
        return $this->app->config['app_id'];
    }

    /**
     * @return Application
     */
    public function app()
    {
        return $this->app;
    }

    public function oauth()
    {
        return $this->app->oauth
            ->setRedirectUrl('https://dev-api.cangmishu.com/wechatOAuth/callback')
            ->scopes(['snsapi_login'])
            ->with(['state' => base64_encode('https://dev.cangmishu.com/#/initPage/home')])
            ->redirect();
    }
}
