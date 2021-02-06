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
        $result =  $this->service::getLocationStockCountData($this->getRequestParams());
        $newData = [];
        if($result) {

            foreach ($result as $key => $value) {
                if(!isset($newData[$value->area_name])) {
                    $newData[$value->area_name]["name"] =  $value->area_name;
                    $newData[$value->area_name]["items"][] = $value;
                    $newData[$value->area_name]["total_shelf_num"] = $value->total_shelf_num;
                    $newData[$value->area_name]["count_location"] = 1;
                } else {
                    $newData[$value->area_name]["total_shelf_num"] += $value->total_shelf_num;
                    $newData[$value->area_name]["count_location"] ++;
                }
                
            }
        }
        return success(array_values($newData));
    }
}
