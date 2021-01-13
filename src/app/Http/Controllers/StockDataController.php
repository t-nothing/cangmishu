<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;

class StockDataController extends Controller
{
    use HasDateParams;

    protected $service;

    public function __construct(StatisticsService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalData()
    {
        return success($this->service::getStockTopData());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getSalesRank()
    {
        return success($this->service::getSalesRank($this->getRequestParams()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getStockWarningRank()
    {
        return success($this->service::getStockWarningRank($this->getRequestParams()));
    }


    /**
     * 得到货区货位库存统计
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getLocationStockCountData()
    {
        return success($this->service::getLocationStockCountData($this->getRequestParams()));
    }
}
