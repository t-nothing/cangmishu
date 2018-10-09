<?php

namespace App\Services\HomePage;

use App\Models\HomePageAnalyze;
use App\Models\Warehouse;

/**
 * 库存信息保存
 * User: admin
 * Date: 2018/5/22
 * Time: 11:12
 */
class StockStatisticsService
{
    public function store($record_time = null)
    {
        app('log')->info('出库单统计开始：' . date('Y-m-d H:i:s'));
        if (!$record_time) {
            $record_time = date("Y-m-d");
        }
        //匹配的是orderItems的创建时间
        $warehouse_infos = Warehouse::with([
            'productStock' => function ($query) use ($record_time) {
                $query->where('created_at', '>', strtotime($record_time))
                    ->where('created_at', '<', strtotime($record_time) + 86399);
            }
        ])->get();
        // app('log')->info('插入前数据：' . json_encode($warehouse_infos->toArray()));
        //  var_dump($warehouse_infos->toArray());exit;
        $warehouseData = [];
        foreach ($warehouse_infos->toArray() as $key => $val) {

            $warehouseData[$key]['warehouse_id'] = $val['id'];
            $warehouseData[$key]['warehouse_name'] = $val['name_cn'];
            $warehouseData[$key]['product_total'] = 0;
            foreach ($val['product_stock'] as $stock_k => $stock_v) {
                $warehouseData[$key]['product_total'] += $stock_v['stockin_num'];
            }
            $warehouseData[$key]['record_time'] = strtotime($record_time);
        }
        // app('log')->info('库存统计数据拼接完成：' . json_encode($warehouseData));
        $this->warehouse($warehouseData);
    }

    protected function warehouse($inData)
    {
        foreach ($inData as $k => $v) {
            $analyze = HomePageAnalyze::updateOrCreate(
                [
                    'warehouse_id' => $v['warehouse_id'],
                    'record_time' => $v['record_time'],
                ]);

            $analyze->warehouse_id = $v['warehouse_id'];
            $analyze->warehouse_name = $v['warehouse_name'];

            $analyze->product_total = $v['product_total'];
            $analyze->record_time = $v['record_time'];
            // app('log')->info('库存记录时间：' . $v['record_time']);
            $analyze->save();

        }

    }
}