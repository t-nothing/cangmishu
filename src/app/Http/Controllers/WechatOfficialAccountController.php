<?php

namespace App\Http\Controllers;

use App\Services\WechatOfficialAccountService;
use Illuminate\Http\Request;

class WechatOfficialAccountController extends Controller
{
    protected $service;

    public function __construct(WechatOfficialAccountService $service)
    {
        $this->service = $service;
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getQrCode(Request $request)
    {
        return $this->service->getWxPic($request);
    }
}
