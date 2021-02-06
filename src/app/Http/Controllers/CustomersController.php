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

class CustomersController extends Controller
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
        return success($this->service::getUserTotalData($this->getRequestParams()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getDailyData()
    {
        return success($this->service::getUserDataByDay($this->getRequestParams()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getOrderRank()
    {
        return success($this->service::getUserOrderRank($this->getRequestParams()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getSupplierRank()
    {
        return success($this->service::getSupplierRank($this->getRequestParams()));
    }
}
