<?php
/**
 * 小店铺登录鉴权.
 */

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use Log;

class WeChatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志


        return $app->server->serve();
    }
}