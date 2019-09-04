<?php
/**
 * 小店铺登录鉴权.
 */

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use Log;
use App\Models\User;
use App\Models\Token;
use App\Events\WechatScanLogined;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use App\Http\Requests\BaseRequests;

class WeChatController extends Controller
{

    /**
     * 开放平台自动登录
     */
    public function wechatLogin(BaseRequests $request)
    {

        $info = config('wechat.open_platform.default');

        $openPlatform = Factory::openPlatform($info);

        $openPlatform->getPreAuthorizationUrl('https://api.changmishu.com/open/wechat/scan/login_callback'); // 传入回调URI即可



        exit;
        $url = sprintf("https://open.weixin.qq.com/connect/qrconnect?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=STATE#wechat_redirect", 
            $info['app_id'],
            urlencode('https://api.changmishu.com/open/wechat/scan/login_callback'),
            'snsapi_login'
        );

        redirect_url($url);
    }

    public function wechatLoginCallback(BaseRequests $request)
    {

        $this->validate($request, [
            'code'      => 'required|string',
            'state'     => 'required|string',
        ]);

        //https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code

        $info = config('wechat.open_platform.default');

        $url = sprintf("https://open.weixin.qq.com/connect/qrconnect?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=STATE#wechat_redirect", 
            $info['app_id'],
            urlencode('https://api.changmishu.com/open/wechat/scan/login_callback'),
            'snsapi_login'
        );

        redirect_url($url);
    }


    public function wechatQr()
    {
        $wechat = app('wechat.official_account');

        $result = $wechat->qrcode->temporary('foo', 600);
        $qrcodeUrl = $wechat->qrcode->url($result['ticket']);

        return view('wechatQr', compact('qrcodeUrl'));
    }

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve($id = 'mini_program')
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $config = sprintf("wechat.%s", $id);
        switch ($config) {
            case 'wechat.mini_program':
            case 'wechat.official_account':
            case 'wechat.open_platform':
                # code...
                break;
            
            default:
                return "配置无效";
        }

        $app = app($config);
        $app->server->push(function($message) use($config) {
            \Log::info('扫码登录外面', $message);
            if ($message['Event'] === 'SCAN' && $config == "wechat.official_account") {
                $openid = $message['FromUserName'];

                $user = User::where('wechat_openid', $openid)->first();
                \Log::info('扫码登录', $message);
                if ($user) {
                    // TODO: 这里根据情况加入其它鉴权逻辑

                    // 使用 laravel-passport 的个人访问令牌
                    $token = $user->createToken($user, Token::TYPE_ACCESS_TOKEN);

                    // 广播扫码登录的消息，以便前端处理
                    event(new WechatScanLogined($token));

                    \Log::info('haha login');
                    return '登录成功！';
                }

                return '失败鸟';
            } else {
                // TODO： 用户不存在时，可以直接回返登录失败，也可以创建新的用户并登录该用户再返回
                return '登录失败';
            }
        }, \EasyWeChat\Kernel\Messages\Message::EVENT);

        return $app->server->serve();
    }
}