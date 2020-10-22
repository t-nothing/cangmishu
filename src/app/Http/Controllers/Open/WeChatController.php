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
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Image;



class WeChatController extends Controller
{

    /**
     * 开放平台自动登录
     */
    public function wechatLogin(BaseRequests $request)
    {
        $app = app('wechat.official_account');
        $config = config('wechat.mini_program.default');
        $buttons = [
            [
                "type" => "miniprogram",
                "name" => "订货老司机",
                "appid"  => $config['app_id'],
                "url"=>"http://mp.weixin.qq.com",
                "pagepath"  => "pages/index/index",
            ],
            [
                "type" => "view",
                "name" => "官方网站",
                "url"  => "https://www.cangmishu.com/",
            ],
            [
                "type" => "media_id",
                "name" => "联系产品经理",
                "media_id"  => "Y2UZBJIujBqIsLIduCiNC7TFRrXq40xlonzxaJWEah8",
            ],
        ];
        $app->menu->create($buttons);

        $list = $app->menu->list();

        print_r($list);
        // $app = app('wechat.official_account');
        // $list = $app->material->list('image', 0, 10);

        // print_r($list);
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

                unset($data['open_id']);
                unset($data['wechat_user']);
                return formatRet(0, '扫描成功', $data);
            }
            
        }

        return formatRet(0, '等待中...');
        
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
    public function serve(BaseRequests $request, $id = 'mini_program')
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
                \Log::info('配置无效');
                return "配置无效";
        }

        
        $app = app($config);
        $app->server->push(function($message) use($config, $app, $request) {
            \Log::info('扫码登录外面', $message);

            $str = "你好，欢迎登录仓秘书！\n您可以在微信小程序中搜索:仓秘书，可以同步使用哦！）";

            if(($message['Content']??'') == "产品") {
                return new Image('Y2UZBJIujBqIsLIduCiNC7TFRrXq40xlonzxaJWEah8');
            }
            if (in_array(strtoupper($message['Event']??''), ['SCAN', 'SUBSCRIBE']) && $config == "wechat.official_account") {
                $openid = $message['FromUserName'];

                \Log::info('扫码登录', $message);
                $wechatUser = $app->user->get($openid);
                \Log::info('扫码用户', $wechatUser);

                $qrKey = $message['EventKey']??$wechatUser['qr_scene_str'];
                $qrKey = str_replace("qrscene_", "", $qrKey);
                //如果是关注，就随机生成一个

                $isNewUser = false;
                if(!empty($qrKey) ) {

                    $userId = 0;
                    $user = User::where('wechat_openid', $openid)->first();
                    $token = null;


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
                    
                    if ($user) {
                        // TODO: 这里根据情况加入其它鉴权逻辑
                        \Log::info('找到用户', $user->toArray());
                        // 使用 laravel-passport 的个人访问令牌
                        

                        $token = $createToken($user, Token::TYPE_ACCESS_TOKEN);

                        // 广播扫码登录的消息，以便前端处理
                        // event(new WechatScanLogined($token));

                        // \Log::info('haha login');
                        // return '登录成功！';

                        $userId = $user->id;
                    } else {
                        \Log::info('自动注册一个新用户');
                        //创建一个新用户
                        $request->merge([
                            'email'         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
                            'province'      =>  $wechatUser['province']??'',
                            'country'       =>  $wechatUser['country']??'',
                            'city'          =>  $wechatUser['city']??'',
                            'avatar'        =>  $wechatUser['headimgurl']??'',
                            'nickname'      =>  $wechatUser['nickname']??'',
                            'wechat_openid' =>  $openid,
                        ]);//合并参数
                        \Log::info('合并注册信息');
                        try 
                        {
                            \Log::info('开始注册');
                            $user = app('user')->quickRegister($request);
                            $token = $createToken($user, Token::TYPE_ACCESS_TOKEN);
                            $userId = $user->id;
                        } 
                        catch (\Exception $e) 
                        {
                            \Log::info($e->getMessage());
                            // app('log')->error($e->getMessage());
                            // return formatRet(500, $e->getMessage());
                        }
                        $isNewUser = true;
                    }

                    Cache::tags(['wechat'])->put($qrKey, [
                            'is_valid'      =>  true,
                            'user_id'       =>  $userId,
                            'token'         =>  $token,
                            'user'          =>  User::with(['defaultWarehouse:id,name_cn'])->select(['avatar', 'email','boss_id','id', 'nickname', 'default_warehouse_id'])->find($userId),
                            'open_id'       =>  $openid,
                            'modules'       =>  [],
                            'wechat_user'   =>  $wechatUser
                        ], 180);

                    \Log::info('登录用户信息', [$qrKey]);

                    return $isNewUser?$str:'欢迎使用仓秘书';

                }

                return $str;
            } 

            return $str;
        });

        return $app->server->serve();
    }

}