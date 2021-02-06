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

use App\Exceptions\BusinessException;
use App\Models\User;
use EasyWeChat\Factory;
use EasyWeChat\OfficialAccount\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class WechatOfficialAccountService
{
    protected Application $app;

    protected string $openid;

    public function __construct()
    {
        $this->app = Factory::officialAccount(config('wechat.official_account.default'));
    }

    /**
     * 获取二维码图片
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getWxPic(Request $request)
    {
        // 查询 cookie，如果没有就重新生成一次
        if (! $weChatFlag = $request->cookie('WECHAT_FLAG')) {
            $weChatFlag = Uuid::uuid4()->getHex();
        }

        // 缓存微信带参二维码
        if (!$url = Cache::get($weChatFlag)) {
            // 有效期 1 天的二维码
            $qrCode = $this->app->qrcode;
            $result = $qrCode->temporary($weChatFlag, 3600 * 24);
            $url    = $qrCode->url($result['ticket']);

            Cache::put($weChatFlag, ['user_id' => auth()->id()], now()->addDay());
        }
        // 自定义参数返回给前端，前端轮询
        return formatRet(0, __('message.success'), compact('url', 'weChatFlag'))
            ->cookie('WECHAT_FLAG', $weChatFlag, 24 * 60);
    }

    /**
     * 获取二维码图片
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getBindWxPic(Request $request)
    {
        $request->validate([
            'secret' => 'required',
        ]);

        if (! Cache::get($request->input('secret'))) {
            throw new BusinessException('请求失败');
        }

        $bindKey = Uuid::uuid4()->getHex()->toString();

        // 有效期
        $qrCode = $this->app->qrcode;
        $result = $qrCode->temporary($bindKey, 60 * 5);
        $url = $qrCode->url($result['ticket']);

        Cache::put($bindKey, ['type' => 'bind', 'url' => $url], 60 * 5);

        info('当前绑定密钥', ['key' => $bindKey]);
        // 自定义参数返回给前端，前端轮询
        return formatRet(0, __('message.success'), ['url' => $url, 'bind_key' => $bindKey]);
    }

    /**
     * 微信消息接入（这里拆分函数处理）
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    public function serve()
    {
        $app = $this->app;

        $app->server->push(function ($message) {
            if ($message) {
                $method = camel_case('handle_' . $message['MsgType']);

                if (method_exists($this, $method)) {
                    $this->openid = $message['FromUserName'];

                    return call_user_func_array([$this, $method], [$message]);
                }

                Log::info('无此处理方法:' . $method);
            }
        });

        return $app->server->serve();
    }

    /**
     * 事件引导处理方法（事件有许多，拆分处理）
     *
     * @param $event
     *
     * @return mixed
     */
    protected function handleEvent($event)
    {
        Log::info('事件参数：', [$event]);

        $method = camel_case('event_' . $event['Event']);
        Log::info('处理方法:' . $method);

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], [$event]);
        }

        Log::info('无此事件处理方法:' . $method);
    }

    /**
     * 取消订阅
     *
     * @param $event
     */
    protected function eventUnsubscribe($event)
    {
        if ($wxUser = User::where('wechat_openid', $this->openid)->first()) {
            // 标记前端可登陆
            $wxUser->is_subscribed = 0;
            $wxUser->subscribed_at = null;

            $wxUser->save();
            return;
        }
    }

    /**
     * 用户已关注
     * 扫描带参二维码事件
     *
     * @param $event
     */
    public function eventSCAN($event)
    {
        //用户未找到但是已关注
        //更新用户openid
        if (! $wxUser = User::where('wechat_openid', $this->openid)->first()) {


            return;
        }
    }

    /**
     * 订阅
     *
     * @param $event
     *
     * @throws \Throwable
     */
    protected function eventSubscribe($event)
    {
        $openId = $this->openid;

        if ($wxUser = User::where('wechat_openid', $openId)->first()) {
            // 标记前端可登陆
            $wxUser->is_subscribed = 1;
            $wxUser->subscribed_at = now();

            $wxUser->save();

        } else {
            $key = Cache::get(Str::after($event['EventKey'], 'qrscene_'));
            /** @var User $wxUser */
            $wxUser = User::query()->whereKey($key['user_id'])->get();

            $wxUser->is_subscribed = 1;
            $wxUser->subscribed_at = now();

            $wxUser->save();
        }

        return;
    }

    /**
     * 标记可登录
     *
     * @param $event
     * @param $uid
     */
    public function updateWechatOpenID($event, $uid)
    {

    }
}
