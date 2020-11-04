<?php

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
