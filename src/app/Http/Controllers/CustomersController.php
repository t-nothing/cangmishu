<?php

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
