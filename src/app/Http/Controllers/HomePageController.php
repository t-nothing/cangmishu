<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HomePageNotice;
use App\Models\HomePageAnalyze;
use App\Models\Warehouse;
use DB;

class HomePageController extends Controller
{
    //首页通知
    public function notice()
    {
        $noticeInfo = HomePageNotice::whose(app('auth')->realUser())->latest()->get();
        return formatRet(0, '', $noticeInfo->toArray());
    }

    //首页仓库信息
    public function analyze(Request $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'integer|min:1',
        ]);

        //默认选择一个仓库
        if (!$request->filled('warehouse_id')) {
            $warehouse = Warehouse::whose(app('auth')->ownerId())->latest()->first();
            if (!$warehouse) {
                return formatRet(0, '暂无任何数据');
            }
            $warehouse_id = $warehouse->id;
        } else {
            $warehouse_id = $request->input('warehouse_id');
        }

        $sql = "select 
(select count(*)  from `batch` where warehouse_id = ? ) as batch_count,
(select count(*) from `order` where warehouse_id = ? ) as order_count,
(select count(*) from `order` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m' ) = date_format( curdate( ) , '%y%m' ) ) as month_order_count,
(select count(*) from `batch` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m' ) = date_format( curdate( ) , '%y%m' ) ) as month_batch_count,
(select sum(shelf_num) from `product_stock` where warehouse_id = ? ) as product_total";
        $homePageAnalyze = DB::select($sql, [$warehouse_id, $warehouse_id, $warehouse_id, $warehouse_id ,$warehouse_id]);
        $homePageAnalyze = [
            "warehouse_id" => $warehouse_id,
            "batch_count" => $homePageAnalyze[0]->batch_count, // 今日入库次数
            "order_count" => $homePageAnalyze[0]->order_count, // 今日出库次数
            "month_order_count" => $homePageAnalyze[0]->month_order_count, // 本月入库次数
            "month_batch_count" => $homePageAnalyze[0]->month_batch_count, // 本月出库次数
            "product_total" => $homePageAnalyze[0]->product_total, // 可用库存数
        ];
        // //获取这一天的数据
        // $homePageAnalyze = HomePageAnalyze::where('warehouse_id',$warehouse_id)
        //     ->where('created_at', '>', strtotime(date('Y-m-d 00:00:00', time())))
        //     ->first();

        // //获取这一月的数据
        // $analyzes = HomePageAnalyze::where('warehouse_id',$warehouse_id)->where('created_at', '>',
        //     date('Y-m-1 00:00:00', time()))->get();

        // if (!$homePageAnalyze) {
        //     $homePageAnalyze = [
        //         "warehouse_id" => $warehouse_id,
        //         "batch_count" => 0, // 今日入库次数
        //         "order_count" => 0, // 今日出库次数
        //         // "batch_product_num" => 0, // 本月入库次数
        //         // "order_product_num" => 0, // 本月出库次数
        //         "product_total" => 0, // 可用库存数
        //     ];
        // }else{
        //     $homePageAnalyze = $homePageAnalyze->toArray();
        // }

        // $monthAnalyze = ['month_order_count' => 0, 'month_batch_count' => 0, 'month_product_stock' => 0];
        // if ($analyzes->toArray()) {
        //     foreach ($analyzes as $k => $v) {
        //         $monthAnalyze['month_order_count'] += $v['order_count']; //本月出库次数：
        //         $monthAnalyze['month_batch_count'] += $v['batch_count']; //本月入库次数：
        //         $monthAnalyze['month_product_stock'] += $v['product_total']; //
        //     }
        // }


        // $homePageAnalyze = array_merge($homePageAnalyze, $monthAnalyze);

        return formatRet(0, '', $homePageAnalyze);

    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function batchOrOrderCount(Request $request)
    {
        $this->validate($request, [
            'start_time' => 'required|date_format:Y-m-d',
            'end_time' => 'required|date_format:Y-m-d',
            'warehouse_id' => 'integer|min:1',
        ]);

        //默认选择一个仓库
        if (!$request->filled('warehouse_id')) {
            $warehouse = Warehouse::where('owner_id',app('auth')->ownerId())->latest()->first();
            if (!$warehouse) {
                return formatRet(0, '暂无任何数据');
            }
            $warehouse_id = $warehouse->id;
        } else {
            $warehouse_id = $request->input('warehouse_id');
        }

        $startDateTime = new \DateTime($request->start_time);
        $endDateTime = new \DateTime($request->end_time);
        $interval = $startDateTime->diff($endDateTime);
        $days = $interval->days;
        $startTime = strtotime($request->start_time);
        // 统一拿出来性能好些
        $inputData = $this->getTotalOutputNum(strtotime($request->start_time), strtotime($request->end_time),
            $warehouse_id);

        $inputDataInfo = [];
        foreach ($inputData as $val) {
            $inputDataInfo[$val['date_time']] = $val;
        }

        for ($k = $days; $k >= 0; $k--) {
            $data['data'][$k]['time'] = date("m/d", ($startTime + $k * 86400));

            $startDate = date("Y-m-d", ($startTime + $k * 86400));

            $data['data'][$k]['batch_count'] = isset($inputDataInfo[$startDate]['batch_count']) ? $inputDataInfo[$startDate]['batch_count'] : 0;
            $data['data'][$k]['order_count'] = isset($inputDataInfo[$startDate]['order_count']) ? $inputDataInfo[$startDate]['order_count'] : 0;
            $data['data'][$k]['batch_product_num'] = isset($inputDataInfo[$startDate]['batch_product_num']) ? $inputDataInfo[$startDate]['batch_product_num'] : 0;
            $data['data'][$k]['order_product_num'] = isset($inputDataInfo[$startDate]['order_product_num']) ? $inputDataInfo[$startDate]['order_product_num'] : 0;
        }
        $data['data'] = array_values($data['data']);


        return formatRet(0, '', $data);
    }

    public function getTotalOutputNum($startTime, $endTime, $warehouseId)
    {
        $data = HomePageAnalyze::getIns()
            ->select(app("db")->raw('Date(from_unixtime(record_time)) as date_time,
                                                sum(batch_count) as batch_count,
                                                sum(order_count) as order_count,
                                                sum(batch_product_num) as batch_product_num,
                                                sum(order_product_num) as order_product_num
                                                '))
            ->whereBetween('record_time', [$startTime, $endTime])
            ->where('warehouse_id', $warehouseId)
            ->groupBy(app("db")->raw('from_unixtime(record_time)'))
            ->get();
        return $data;
    }

}