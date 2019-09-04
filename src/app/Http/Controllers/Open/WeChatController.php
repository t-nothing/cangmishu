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

class WeChatController extends Controller
{

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