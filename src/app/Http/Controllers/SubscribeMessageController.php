<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Models\SubscribeMessageConfig;

class SubscribeMessageController extends Controller
{
    public function update(string $type, BaseRequests $request)
    {
        $request->validate([
           '*.type' => 'required',
           '*.status' => 'required|in:0,1',
        ]);

        switch ($type) {
            case 'wechat':
                $channel = SubscribeMessageConfig::CHANNEL_WECHAT;
                break;
            case 'phone':
                $channel = SubscribeMessageConfig::CHANNEL_PHONE;
                break;
            case 'email':
                $channel = SubscribeMessageConfig::CHANNEL_EMAIL;
                break;
            default:
                return formatRet(404);
        }

        SubscribeMessageConfig::updateChannelConfig($channel, $request->all());

        return formatRet(0, '', );
    }

    /**
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(string $type)
    {
        switch ($type) {
            case 'wechat':
                $channel = SubscribeMessageConfig::CHANNEL_WECHAT;
                break;
            case 'phone':
                $channel = SubscribeMessageConfig::CHANNEL_PHONE;
                break;
            case 'email':
                $channel = SubscribeMessageConfig::CHANNEL_EMAIL;
                break;
            default:
                return formatRet(404);
        }

        return formatRet(0, '', SubscribeMessageConfig::getChannelConfig($channel));
    }
}
