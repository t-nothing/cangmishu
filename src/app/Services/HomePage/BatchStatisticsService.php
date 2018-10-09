<?php

namespace App\Services\HomePage;

use App\Models\HomePageAnalyze;
use App\Models\Warehouse;

use Carbon\Carbon;


/**
 * 首页入库单统计
 * User: xs
 * Date: 2018/5/22
 * Time: 11:11
 * 1  保存某一天入库次数（batch_count）入库商品（batch_product_num）数据
 */
class BatchStatisticsService
{

    /** 保存某一天入库次数（batch_count）入库商品（batch_product_num）数据
     * @param \DateTime $record_time exp:2018-05-02
     * @return mixed
     */
    public function store($record_time = null)
    {
        app('log')->info('入库单统计开始：' . date('Y-m-d H:i:s'));
        if (!$record_time) {
            $record_time = date("Y-m-d");
        }
        $warehouse_infos = Warehouse::with([
            'batch' => function ($query) use ($record_time) {
                $query->where('created_at', '>', strtotime($record_time))
                    ->where('created_at', '<', strtotime($record_time) + 86399);
            }
        ])->get();
        // app('log')->info('插入前数据：' . json_encode($warehouse_infos->toArray()));
        $warehouseData = [];
        foreach ($warehouse_infos as $key => $val) {
        //    if($val['batch'] && $val['batch']['status']==3){
                $warehouseData[$key]['warehouse_id'] = $val['id'];
                $warehouseData[$key]['warehouse_name'] = $val['name_cn'];
                $warehouseData[$key]['batch_count'] = 0;
                $warehouseData[$key]['batch_product_num'] = 0;
                //入库单数据和入库商品数量

                    foreach ($val['batch'] as $k => $v) {
                        $warehouseData[$key]['batch_product_num'] += $v['total_num']['total_stockin_num'];
                        $warehouseData[$key]['batch_count']++;
                    }
                $warehouseData[$key]['record_time'] = strtotime($record_time);
            }
       // }
        // app('log')->info('入库单统计数据拼接完成：' . json_encode($warehouseData));
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
            // app('log')->info('入库单条件查询：' . json_encode($analyze->toArray()));
            $analyze->warehouse_id = $v['warehouse_id'];
            $analyze->warehouse_name = $v['warehouse_name'];
            $analyze->batch_count = $v['batch_count'];
            $analyze->batch_product_num = $v['batch_product_num'];
            $analyze->record_time = $v['record_time'];
            // app('log')->info('入库单记录时间：' . $v['record_time']);
            $analyze->save();

        }

    }

}
