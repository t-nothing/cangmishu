<?php
/**
 * @Author: h9471
 * @Created: 2020/11/2 16:25
 */

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ReceiverAddress;
use App\Models\SenderAddress;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\ShopUser;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService
{
    public static int $warehouseId;

    /**
     * 解析时间格式参数
     *
     * @param $params
     * @param  bool  $unix
     * @return array
     * @throws BusinessException
     */
    protected static function parseDateParams($params, bool $unix = false)
    {
        //默认选择一个仓库
        if (! request()->filled('warehouse_id')) {
            self::$warehouseId = auth('admin')->getWarehouseIdForRequest();
        } else {
            self::$warehouseId = intval(request()->input('warehouse_id'));
        }

        $warehouse = Warehouse::where('owner_id', app('auth')->ownerId())
            ->find(self::$warehouseId);

        if (! $warehouse) {
            throw new BusinessException(__('message.warehouseNotExist'));
        }

        if ($unix) {
            if (is_int($params) || is_string($params)) {
                if ($params == -1) {
                    return [
                        Carbon::yesterday()->startOfDay()->unix(),
                        Carbon::yesterday()->endOfDay()->unix()
                    ];
                }

                return [
                    now()->subDays($params - 1)->startOfDay()->unix(),
                    now()->endOfDay()->unix()
                ];
            }

            if (is_array($params)) {
                return [
                    Carbon::parse($params[0])->startOfDay()->unix(),
                    Carbon::parse($params[1])->startOfDay()->unix()
                ];
            }

            return [now()->startOfDay()->unix(), now()->endOfDay()->unix()];
        }

        if (is_int($params) || is_string($params)) {
            if ($params == -1) {
                return [
                    Carbon::yesterday()->startOfDay()->unix(),
                    Carbon::yesterday()->endOfDay()->unix()
                ];
            }

            return [
                now()->subDays($params - 1)->startOfDay(),
                now()->endOfDay()
            ];
        }

        if (is_array($params)) {
            return [
                Carbon::parse($params[0])->startOfDay(),
                Carbon::parse($params[1])->startOfDay()
            ];
        }

        return [now()->startOfDay(), now()->endOfDay()];
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getIndexCountData($params)
    {
        self::parseDateParams($params);

        $total_stock = (int) ProductStock::query()
            ->where('warehouse_id', self::$warehouseId)
            ->sum('stock_num');

        $total_product = Product::query()
            ->where('warehouse_id', self::$warehouseId)
            ->count('id');

        $total_order = Order::query()
            ->where('warehouse_id', self::$warehouseId)
            ->whereNotIn('status', [Order::STATUS_CANCEL, Order::STATUS_DEFAULT])
            ->count('id');

        $stock_warning = Product::query()
            ->where('product.warehouse_id', self::$warehouseId)
            ->leftJoin('category as c', 'c.id', '=', 'product.category_id')
            ->whereColumn('total_stock_num', '<=', 'c.warning_stock')
            ->whereRaw('c.warning_stock > 0')
            ->count();

        $wait_shelf = Batch::query()->where('warehouse_id', self::$warehouseId)
            ->where('status', '=', Batch::STATUS_PREPARE)
            ->count();

        $wait_shipment = Order::query()->where('warehouse_id', self::$warehouseId)
            ->where('status', '=', Order::STATUS_WAITING)
            ->count();

        return compact('total_product',
            'total_stock',
            'total_order',
            'stock_warning',
            'wait_shelf',
            'wait_shipment');
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getSalesTotalData($params)
    {
        $date = self::parseDateParams($params, true);

        $data =  Order::query()
            ->where('warehouse_id', self::$warehouseId)
            ->whereNotIn('status', [Order::STATUS_CANCEL, Order::STATUS_DEFAULT])
            ->whereBetween('created_at', $date)
            ->selectRaw("sum(sub_total) as total, sum(sub_pay) as total_pay, count(id) as order_count")
            ->first();

        return [
            'total' => $data['total'] ?? '0.00',
            'total_pay' => $data['total_pay'] ?? '0.00',
            'total_wait_pay' => bcsub($data['total'], $data['total_pay'], 2),
            'order_count' => $data['order_count'],
        ];
    }

    /**
     * @param $params
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws BusinessException
     */
    public static function getSalesDataByShop($params)
    {
        $date = self::parseDateParams($params, true);

        return Order::query()
            ->where('order.warehouse_id', self::$warehouseId)
            ->whereNotIn('status', [Order::STATUS_CANCEL, Order::STATUS_DEFAULT])
            ->leftJoin('shop as s', 's.id', '=', 'order.shop_id')
            ->whereBetween('order.created_at', $date)
            ->selectRaw('source, sum(sub_pay) as sales')
            ->groupBy('shop_id', 'source')
            ->get()
            ->each(function ($order) {
                return $order->setAppends([]);
            });
    }

    /**
     * @param $params
     * @return Collection
     * @throws BusinessException
     */
    public static function getSalesDataByDay($params)
    {
        $date = self::parseDateParams($params, true);

        $data = Order::query()
            ->where('warehouse_id', self::$warehouseId)
            ->whereNotIn('status', [Order::STATUS_CANCEL, Order::STATUS_DEFAULT])
            ->whereBetween('created_at', $date)
            ->selectRaw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as days, sum(sub_pay) as sales")
            ->groupBy('days')
            ->get()
            ->map(function ($order) {
                $order->setAppends([]);
                $order['sales'] = number_format($order->sales, 2, '.', '');

                return $order;
            });

        $data = self::generateSalesDataOfZeroDay(
            $data,
            Carbon::createFromTimestamp($date[0]),
            Carbon::createFromTimestamp($date[1])
        );

        return $data;
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getStockTotalData($params)
    {
        $date = self::parseDateParams($params, true);

        $data = ProductStock::query()
            ->where('warehouse_id', self::$warehouseId)
            ->whereBetween('created_at', $date)
            ->selectRaw("sum(stockin_num) as stock_in_num, sum(stockout_num) as stock_out_num")
            ->first();

        $stock_bad = Product::query()
            ->where('product.warehouse_id', self::$warehouseId)
            ->leftJoin(Category::getIns()->getTable() . ' as c', 'c.id', '=', 'product.category_id')
            ->whereRaw('product.total_stock_num <= c.warning_stock and c.warning_stock >0')
            ->count();

        return [
            'stock_in_num' =>  (int) $data['stock_in_num'] ?? 0,
            'stock_out_num' => (int) $data['stock_out_num'] ?? 0,
            'stock_shortage' => $stock_bad,
        ];
    }

    /**
     * @param $params
     * @return Collection
     * @throws BusinessException
     */
    public static function getStockDataByDate($params)
    {
        $date = self::parseDateParams($params, true);

        $data = ProductStock::query()
            ->where('warehouse_id', self::$warehouseId)
            ->whereBetween('created_at', $date)
            ->selectRaw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as days, sum(stockin_num) as stock_in_num, sum(stockout_num) as stock_out_num")
            ->groupBy('days')
            ->get()
            ->map(function ($order) {
                $order->setAppends([]);
                $order['stock_in_num'] = (int) $order['stock_in_num'];
                $order['stock_out_num'] = (int) $order['stock_out_num'];

                return $order;
            });

        $data = self::generateStockDataOfZeroDay(
            $data,
            Carbon::createFromTimestamp($date[0]),
            Carbon::createFromTimestamp($date[1])
        );

        return $data;
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getOrderTotalData($params)
    {
        self::parseDateParams($params, true);

        $created = Order::query()
            ->where('warehouse_id', self::$warehouseId)
            ->whereBetween('created_at', [now()->startOfDay()->unix(), now()->endOfDay()->unix()])
            ->count();

        $wait = Order::query()
            ->where('warehouse_id', self::$warehouseId)
            ->where('status', Order::STATUS_DEFAULT)
            ->count();

        $wait_ship = Order::query()
            ->where('warehouse_id', self::$warehouseId)
            ->where('status', Order::STATUS_WAITING)
            ->count();

        return compact('created', 'wait', 'wait_ship');
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getSalesDetailByDay($params)
    {
        $date = self::parseDateParams($params, true);

        $data = Order::query()
            ->where('order.warehouse_id', self::$warehouseId)
            ->whereBetween('order.created_at', $date)
            ->join('order_item as i', 'i.order_id', '=', 'order.id')
            ->addSelect([
                'product_amount' => OrderItem::query()
                    ->selectRaw('sum(amount) as product_amount')
                    ->whereColumn('order_id', '=', 'order.id'),
                'pickup_amount' => OrderItem::query()
                    ->selectRaw('sum(pick_num) as pickup_amount')
                    ->whereColumn('order_id', '=', 'order.id'),
            ])
            ->selectRaw("FROM_UNIXTIME(order.created_at,'%Y-%m-%d') as days,
            count(distinct order.id) as order_count,
            sum(sub_total) as total,
            sum(sub_pay) as total_pay")
            ->groupBy('days')
            ->get()->map(function ($order) {
                return [
                    'days' => $order['days'],
                    'order_count' => $order['order_count'],
                    'total' => $order->total,
                    'total_pay' => $order->total_pay,
                    'product_amount' => (int) $order->product_amount,
                    'pickup_amount' => (int) $order->pickup_amount,
                ];
            });

        return $data;
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getShopTotalData($params)
    {
        self::parseDateParams($params, true);

        $shopIds = Shop::query()->where('warehouse_id', self::$warehouseId)->select('id')->get();
        $orderIds = Order::query()->whereIn('shop_id', $shopIds->modelKeys())->select('id')->get();

        return [
            'count' => $shopIds->count(),
            'sales_count' => (int) OrderItem::query()
                ->whereIn('order_id', $orderIds->modelKeys())
                ->sum('amount'),
            'up_shelf_count' => ShopProduct::query()->whereIn('shop_id', $shopIds->modelKeys())
                ->where('is_shelf', 1)
                ->count(),
        ];
    }

    /**
     * @param $params
     * @return array
     * @throws BusinessException
     */
    public static function getUserTotalData($params)
    {
        self::parseDateParams($params, true);

        return [
            'receiver_count' => ReceiverAddress::query()
                ->where('owner_id', auth()->id())
                //->where('warehouse_id', self::$warehouseId)
                ->count(),
            'sender_count' => SenderAddress::query()
                ->where('owner_id', auth()->id())
                //->where('warehouse_id', self::$warehouseId)
                ->count(),
            'supplier_count' => Distributor::query()
                ->where('user_id', auth()->id())
            ->count(),
            'member_count' => ShopUser::query()->count(),
        ];
    }

    /**
     * @param $params
     * @return Collection
     * @throws BusinessException
     */
    public static function getUserDataByDay($params)
    {
        $date = self::parseDateParams($params, true);

        $data = ShopUser::query()
            ->whereBetween('created_at', $date)
            ->selectRaw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as days, count(id) as counts")
            ->groupBy('days')
            ->get();

        $data = self::generateCountsDataOfZeroDay(
            $data,
            Carbon::createFromTimestamp($date[0]),
            Carbon::createFromTimestamp($date[1])
        );

        return $data;
    }

    /**
     * @param $params
     * @return Collection
     * @throws BusinessException
     */
    public static function getUserOrderRank($params)
    {
        self::parseDateParams($params, true);

        $data = ShopUser::query()
            ->join('order as o', 'o.shop_user_id', '=', 'shop_user.id')
            ->whereRaw('o.status > ' . Order::STATUS_DEFAULT)
            ->selectRaw('shop_user.id as user_id,
            shop_user.avatar_url as avatar,
            shop_user.nick_name as name, count(o.id) as order_count,
            sum(case when o.created_at >='
                . now()->startOfMonth()->unix()
                . ' and o.created_at <= '
                . now()->endOfMonth()->unix()
                . ' then 1 else 0 end) as current_month_order_count')
            ->groupBy(['user_id', 'name'])
            ->orderByDesc('order_count')
            ->limit(10)
            ->get()->map(function ($value) {
                $value['current_month_order_count'] = (int) $value['current_month_order_count'];
                return $value;
            });

        return $data;
    }

    /**
     * @return Collection
     * @throws BusinessException
     */
    public static function getSupplierRank($params)
    {
        self::parseDateParams($params, true);

        $data = Distributor::query()
            ->where('warehouse_id', self::$warehouseId)
            ->join('purchase as p', 'distributor.id', '=', 'p.distributor_id')
            ->selectRaw('name_cn as distributor_name, distributor.id as distributor_id, sum(p.confirm_num) as count,
            sum(case when p.created_at >='
                . now()->startOfMonth()->unix()
                . ' and p.created_at <= '
                . now()->endOfMonth()->unix()
                . ' then 1 else 0 end) as current_month_count')
            ->groupBy(['distributor_id', 'distributor_name'])
            ->orderByDesc('count')
            ->limit(10)
            ->get()->map(function ($value) {
                $value['count'] = (int) $value['count'];
                $value['current_month_count'] = (int) $value['current_month_count'];
                return $value;
            })->each->setAppends([]);

        return $data;
    }

    /**
     * @return array
     * @throws BusinessException
     */
    public static function getStockTopData()
    {
        self::parseDateParams(1, true);

        $stock_count = (int) ProductStock::query()
            ->where('warehouse_id', self::$warehouseId)
            ->sum('stock_num');

        $stock_lack_count = Product::query()
            ->where('product.warehouse_id', self::$warehouseId)
            ->leftJoin(Category::getIns()->getTable() . ' as c', 'c.id', '=', 'product.category_id')
            ->whereRaw('product.total_stock_num <= c.warning_stock and c.warning_stock >0')
            ->count();

        $product_count = Product::query()
            ->where('product.warehouse_id', self::$warehouseId)
            ->count();

        return compact('stock_count', 'stock_lack_count', 'product_count');
    }

    /**
     * @param $params
     * @return mixed
     * @throws BusinessException
     */
    public static function getSalesRank($params)
    {
        $date = self::parseDateParams($params, true);

        $data = OrderItem::query()
            ->where('warehouse_id', self::$warehouseId)
            ->selectRaw("name_cn as name, sum(amount) as sales, pic as picture")
            ->groupBy('name_cn')
            ->orderByDesc('sales')
            ->get()->each->setAppends([]);

        return $data;
    }

    /**
     * @param $params
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws BusinessException
     */
    public static function getStockWarningRank($params)
    {
        self::parseDateParams($params, true);

        return Product::query()
            ->selectRaw('total_stock_num as stock,product.name_cn as name, photos as pictures')
            ->where('product.warehouse_id', self::$warehouseId)
            ->leftJoin('category as c', 'c.id', '=', 'product.category_id')
            ->whereRaw('product.total_stock_num <= c.warning_stock and c.warning_stock >0')
            ->orderBy('product.total_stock_num')
            ->get();
    }

    /**
     * 数据为空的日期生成零数据
     * @param Collection $data
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public static function generateSalesDataOfZeroDay(Collection $data, Carbon $start, Carbon $end): Collection
    {
        $dates = self::generateDateRange($start, $end);

        $realDates = [];

        $data->flatMap(function ($value) use (&$realDates) {
            $realDates[] = $value->days;
        });

        $dates = array_diff($dates, $realDates);

        foreach ($dates as $date) {
            $data->prepend(['days' => $date, 'sales' => '0.00']);
        }

        return $data->sortBy('days')->values();
    }

    /**
     * 数据为空的日期生成零数据
     * @param Collection $data
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public static function generateStockDataOfZeroDay(Collection $data, Carbon $start, Carbon $end): Collection
    {
        $dates = self::generateDateRange($start, $end);

        $realDates = [];

        $data->flatMap(function ($value) use (&$realDates) {
            $realDates[] = $value->days;
        });

        $dates = array_diff($dates, $realDates);

        foreach ($dates as $date) {
            $data->prepend(['days' => $date, 'stock_in_num' => 0, 'stock_out_num' => 0]);
        }

        return $data->sortBy('days')->values();
    }

    /**
     * 数据为空的日期生成零数据
     * @param Collection $data
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public static function generateCountsDataOfZeroDay(Collection $data, Carbon $start, Carbon $end): Collection
    {
        $dates = self::generateDateRange($start, $end);

        $realDates = [];

        $data->flatMap(function ($value) use (&$realDates) {
            $realDates[] = $value->days;
        });

        $dates = array_diff($dates, $realDates);

        foreach ($dates as $date) {
            $data->prepend(['days' => $date, 'counts' => 0]);
        }

        return $data->sortBy('days')->values();
    }

    /**
     * 数据为空的月份生成零数据
     * @param Collection $data
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    private static function generateDataOfZeroMonth(Collection $data, Carbon $start, Carbon $end): Collection
    {
        $months = self::generateMonthRange($start, $end);

        $realDates = [];

        $data->flatMap(function ($value) use (&$realDates) {
            $realDates[] = $value->months;
        });

        $months = array_diff($months, $realDates);

        foreach ($months as $month) {
            $data->prepend(['months' => $month, 'count' => 0]);
        }

        return $data->sortBy('months')->values();
    }

    /**
     * 生成月份范围的数组
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    private static function generateMonthRange(Carbon $start_date, Carbon $end_date): array
    {
        $months = [];
        for ($month = $start_date; $month->lte($end_date); $month->addMonth()) {
            $months[] = $month->format('Y-m');
        }

        return $months;
    }

    /**
     * 生成日期范围的数组
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    public static function generateDateRange(Carbon $start_date, Carbon $end_date): array
    {
        $dates = [];
        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    /**
     * @param  string  $date
     * @return float|int
     */
    protected static function getCacheTTLByDate(string $date)
    {
        //如果是今天或者未来
        if (Carbon::parse($date)->isToday() || Carbon::parse($date)->isFuture()) {
            return 60;
        }

        return 60 * 60 * 24;
    }
}
