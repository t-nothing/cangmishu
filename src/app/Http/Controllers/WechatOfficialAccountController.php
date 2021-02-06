<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
