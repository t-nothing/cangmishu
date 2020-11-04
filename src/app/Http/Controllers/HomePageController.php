<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\Request;
use App\Models\HomePageNotice;
use App\Models\HomePageAnalyze;
use App\Models\Warehouse;
use App\Models\Batch;
use App\Models\Order;
use DB;

class HomePageController extends Controller
{
    protected $service;

    public function __construct(StatisticsService $service)
    {
        $this->service = $service;
    }

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

            $warehouse_id = app('auth')->warehouseId();
        } else {
            $warehouse_id = intval($request->input('warehouse_id'));
        }

        $warehouse = Warehouse::where("owner_id", app('auth')->ownerId())->find($warehouse_id);
        if (!$warehouse) {
            return formatRet(0, trans("message.404NotFound"));
        }

        $sql = "select
(select sum(stock_num)  from `product_stock` where warehouse_id = ?) as all_count,
(select sum(stock_num) from `product_stock` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m%d' ) = date_format( curdate( ) , '%y%m%d' )) as today_count,
(select sum(stock_num) from `product_stock` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m' ) = date_format( curdate( ) , '%y%m' ) ) as month_count,
(select sum(stock_num) from `product_stock` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y' ) = date_format( curdate( ) , '%y' ) ) as year_count";
        $stock = DB::select($sql, [$warehouse_id, $warehouse_id, $warehouse_id, $warehouse_id ,$warehouse_id]);


        $stock = [
            'all_count'     =>$stock[0]->all_count??0,
            'today_count'   =>$stock[0]->today_count??0,
            'month_count'   =>$stock[0]->month_count??0,
            'year_count'    =>$stock[0]->year_count??0,
        ];

        $sql = "select
(select count(total_stock_num)  from `product` where warehouse_id = ?) as all_count,
(select count(total_stock_num) from `product` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m%d' ) = date_format( curdate( ) , '%y%m%d' )) as today_count,
(select count(total_stock_num) from `product` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m' ) = date_format( curdate( ) , '%y%m' ) ) as month_count,
(select count(total_stock_num) from `product` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y' ) = date_format( curdate( ) , '%y' ) ) as year_count";
        $product = DB::select($sql, [$warehouse_id, $warehouse_id, $warehouse_id, $warehouse_id ,$warehouse_id]);

        $product = [
            'all_count'     =>$product[0]->all_count??0,
            'today_count'   =>$product[0]->today_count??0,
            'month_count'   =>$product[0]->month_count??0,
            'year_count'    =>$product[0]->year_count??0,
        ];

        $sql = "select
(select count(id)  from `order` where warehouse_id = ?) as all_count,
(select count(id) from `order` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m%d' ) = date_format( curdate( ) , '%y%m%d' )) as today_count,
(select count(id) from `order` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y%m' ) = date_format( curdate( ) , '%y%m' ) ) as month_count,
(select count(id) from `order` where warehouse_id = ? and date_format( FROM_UNIXTIME(created_at), '%y' ) = date_format( curdate( ) , '%y' ) ) as year_count";
        $order = DB::select($sql, [$warehouse_id, $warehouse_id, $warehouse_id, $warehouse_id ,$warehouse_id]);

        $order = [
            'all_count'     =>$order[0]->all_count??0,
            'today_count'   =>$order[0]->today_count??0,
            'month_count'   =>$order[0]->month_count??0,
            'year_count'    =>$order[0]->year_count??0,
        ];

        $sql = "select
(select count(product.id) as count from product,category where product.category_id = category.id and  product.total_stock_num <= category.warning_stock and category.warning_stock >0 and product.warehouse_id = ?) as stock_warning,
(select count(id) from `batch` where warehouse_id = ? and `status` = ".Batch::STATUS_PREPARE.") as unshelf,
(select count(id) from `order` where warehouse_id = ? and `status` <= ".Order::STATUS_PICK_DONE.") as unconfirm";
        $todo = DB::select($sql, [$warehouse_id, $warehouse_id, $warehouse_id, $warehouse_id ,$warehouse_id]);


        $todo = [
            'stock_warning' =>$todo[0]->stock_warning??0,
            'unshelf'       =>$todo[0]->unshelf??0,
            'unconfirm'     =>$todo[0]->unconfirm??0,
        ];



        $homePageAnalyze = [
            "stock" => $stock,
            "product" => $product,
            "order" => $order,
            "todo" => $todo,
            'warehouse_id' => $warehouse_id,
        ];
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

            $warehouse_id = app('auth')->warehouseId();
        } else {
            $warehouse_id = intval($request->input('warehouse_id'));
        }

        $warehouse = Warehouse::where("owner_id", app('auth')->ownerId())->find($warehouse_id);
        if (!$warehouse) {
            return formatRet(0, trans("message.404NotFound"));
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
            $inputDataInfo[$val->record_time][$val->type] = $val->count;
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



        $sql = "
select count(*) as count ,Date(from_unixtime(created_at))  as record_time, 'batch_count' as `type`  from `batch` where warehouse_id = ? and created_at >= ? and created_at <= ? and `status` >1 group by record_time
union all
select sum(stock_num),Date(from_unixtime(created_at))  as record_time, 'batch_product_num' as `type`   from `batch` where warehouse_id = ? and created_at >= ? and created_at <= ?  group by record_time
union all
select count(*),Date(from_unixtime(created_at))  as record_time, 'order_count' as `type`   from `order` where warehouse_id = ? and created_at >= ? and created_at <= ?  group by record_time
union all
select sum(sub_order_qty),Date(from_unixtime(created_at)) as record_time, 'order_product_num' as `type`     from `order` where warehouse_id = ? and created_at >= ? and created_at <= ? group by record_time

";
        $data = DB::select($sql,
            [
                $warehouseId,
                $startTime,
                $endTime,

                $warehouseId,
                $startTime,
                $endTime,

                $warehouseId,
                $startTime,
                $endTime,

                $warehouseId,
                $startTime,
                $endTime]);




        return $data;
    }

    public function getRequestParams()
    {
        $days = \request()->input('days', 1);

        $begin = \request()->input('begin', '');
        $end = \request()->input('end', '');

        if ($begin && $end) {
            return [$begin, $end];
        }

        return $days;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getTotalData()
    {
        return success($this->service::getIndexCountData($this->getRequestParams()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getSalesData()
    {
        $data = [
            'total' => $this->service::getSalesTotalData($this->getRequestParams()),
            'pie' => $this->service::getSalesDataByShop($this->getRequestParams()),
            'daily' => $this->service::getSalesDataByDay($this->getRequestParams()),
        ];

        return success($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     */
    public function getStockData()
    {
        $total = $this->service::getStockTotalData($this->getRequestParams());

        $pie = [
            [
                'type' => '总入库',
                'count' => $total['stock_in_num'],
            ],
            [
                'type' => '总出库',
                'count' => $total['stock_out_num'],
            ],
        ];

        $data = [
            'total' => $total,
            'pie' => $pie,
            'daily' => $this->service::getStockDataByDate($this->getRequestParams()),
        ];

        return success($data);
    }
}
