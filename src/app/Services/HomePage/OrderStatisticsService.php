<?php

namespace App\Services\HomePage;

use App\Models\HomePageAnalyze;
use App\Models\Warehouse;

/**
 * 首页入库单统计
 * User: xs
 * Date: 2018/5/22
 * Time: 11:11
 * 1  保存某一天出库次数（order_count）出库商品（order_product_num）数据
 */
class OrderStatisticsService
{
    public function store($record_time = null)
    {
        app('log')->info('出库单统计开始：' . date('Y-m-d H:i:s'));
        if (!$record_time) {
            $record_time = date("Y-m-d");
        }
        //匹配的是orderItems的创建时间
        $warehouse_infos = Warehouse::with([
            'order.orderItems' => function ($query) use ($record_time) {
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
            $warehouseData[$key]['order_count'] = 0;
            $warehouseData[$key]['order_product_num'] = 0;
            //出库单数据和出库商品数量
            foreach ($val['order'] as $o_k => $o_v) {

                //出库单不为空
                if ($o_v['order_items']) {
                    foreach ($o_v['order_items'] as $order_k => $order_v) {
                        $warehouseData[$key]['order_product_num'] += $order_v['amount'];
                        $warehouseData[$key]['order_count']++;
                    }
                }
            }
            $warehouseData[$key]['record_time'] = strtotime($record_time);
        }
        // app('log')->info('出库单统计数据拼接完成：' . json_encode($warehouseData));
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
            $analyze->order_count = $v['order_count'];
            $analyze->order_product_num = $v['order_product_num'];
            $analyze->record_time = $v['record_time'];
            app('log')->info('出库单记录时间：' . $v['record_time']);
            $analyze->save();

        }

    }

}