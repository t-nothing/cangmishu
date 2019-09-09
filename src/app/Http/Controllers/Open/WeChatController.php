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
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeChatController extends Controller
{

    /**
     * 开放平台自动登录
     */
    public function wechatLogin(BaseRequests $request)
    {

        // $info = config('wechat.open_platform.default');

        // $openPlatform = Factory::openPlatform($info);

        // $openPlatform->getPreAuthorizationUrl('https://api.changmishu.com/open/wechat/scan/login_callback'); // 传入回调URI即可



        // exit;
        // $url = sprintf("https://open.weixin.qq.com/connect/qrconnect?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=STATE#wechat_redirect", 
        //     $info['app_id'],
        //     urlencode('https://api.changmishu.com/open/wechat/scan/login_callback'),
        //     'snsapi_login'
        // );

        // redirect_url($url);
    }

    public function wechatQrCallback(BaseRequests $request)
    {

        $this->validate($request, [
            'qr_key'      => 'required|string',
        ]);

        if (Cache::tags(['wechat'])->has($request->qr_key)) {
            $data = Cache::tags(['wechat'])->get($request->qr_key);
            if($data['is_valid']) {
                return formatRet(0, '扫描成功', $data);
            }
            
        }

        return formatRet(500, '等待中...');
        
    }


    public function wechatQr()
    {
        $wechat = app('wechat.official_account');

        $key = Cache::increment('CMS-WECHAT-KEY');
        $key = md5(md5($key).'cms');
        Cache::tags(['wechat'])->put($key, [
            'is_valid'      =>  false,
            'user_id'       =>  0,
            'token'         =>  null,
        ], 1200);

        $result = $wechat->qrcode->temporary("{$key}", 1200);
        $qrcodeUrl = $wechat->qrcode->url($result['ticket']);
        $arr = [
            'qr'       => 'data:png;base64,'.base64_encode(file_get_contents($qrcodeUrl)),
            'qr_key'   => $key,
        ];
        return formatRet(0, '', $arr);
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
        $app->server->push(function($message) use($config, $app) {
            \Log::info('扫码登录外面', $message);
            if ($message['Event'] === 'SCAN' && $config == "wechat.official_account") {
                $openid = $message['FromUserName'];

                    $qrKey = $message['EventKey']??'';

                    if(!empty($qrKey)) {
                        \Log::info('扫码登录', $message);
                        $wechatUser = $app->user->get($openid);
                        \Log::info('扫码用户', $wechatUser);
                        $userId = 0;
                        $user = User::where('wechat_openid', $openid)->first();
                        $token = null;
                        
                        if ($user) {
                            // TODO: 这里根据情况加入其它鉴权逻辑
                            \Log::info('找到用户', $user->toArray());
                            // 使用 laravel-passport 的个人访问令牌
                            

                                /**
                                 * 生成一个新的 token，token 哈希来保证唯一性。
                                 *
                                 * @param  \Illuminate\Contracts\Auth\Authenticatable $user
                                 * @return \App\Models\Token|null
                                 */
                                $createToken = function($user, $type)
                                {
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
                                };

                                $token = $createToken($user, Token::TYPE_ACCESS_TOKEN);

                            // 广播扫码登录的消息，以便前端处理
                            // event(new WechatScanLogined($token));

                            // \Log::info('haha login');
                            // return '登录成功！';

                            $userId = $user->id;
                        }

                        if (Cache::tags(['wechat'])->has($qrKey)) {

                            $data = Cache::tags(['wechat'])->get($qrKey);
                            if($data['is_valid']) {
                                return '请不要重复扫描';
                            }

                            Cache::tags(['wechat'])->put($qrKey, [
                                'is_valid'      =>  true,
                                'user_id'       =>  $userId,
                                'token'         =>  $token,
                            ], 180);

                            return $token?'老用户欢迎回来':'欢迎使用仓秘书，请进行帐号绑定';
                        }

                        return '登录过期，请重新扫描';
                    }
            } else {
                // TODO： 用户不存在时，可以直接回返登录失败，也可以创建新的用户并登录该用户再返回
                return "你好，欢迎登陆仓秘书！\n\n仓秘书——专为中小型企业、个体经营者提供的免费WMS系统 \n\n无需付费，人人都用得起的专业仓储+订货管理系统 \n\n如果你正在寻找一款仓储软件，或许你可以点击下方直达通道体验一下我们的仓储系统\n直达通道→https://my.cangmishu.com \n\n不定期进行功能迭代更新，如果您有意见或建议可以直接将您的建议打包好发给我哦！\n <img src='https://www.cangmishu.com/static/images/Wechatcard2.png' />";
            }
        }, \EasyWeChat\Kernel\Messages\Message::EVENT);

        return $app->server->serve();
    }
}