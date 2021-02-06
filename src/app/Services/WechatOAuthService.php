<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
