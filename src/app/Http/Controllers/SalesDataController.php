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

use App\Services\StatisticsService;

class SalesDataController extends Controller
{
    use HasDateParams;

    protected $service;

    public function __construct(StatisticsService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getTotalData()
    {
        return success($this->service::getOrderTotalData($this->getRequestParams()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getDailyDetailData()
    {
        return success($this->service::getSalesDetailByDay($this->getRequestParams()));
    }
}
