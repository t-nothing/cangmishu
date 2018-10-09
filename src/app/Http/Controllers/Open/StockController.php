<?php

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\UserApp;
use App\Models\Warehouse;
use App\Models\WarehouseEmployee;
use App\Models\ProductStock;
use App\Models\ProductSpec;
use App\Models\ProductSku;

class StockController extends Controller
{
	/**
     * 确认入库，并打印sku
     */
    public function query(Request $request)
    {
        $this->validate($request, [
            'relevance_code' => 'required|string',
        ]);

        $user = app('auth')->user();
        $warehouse = app('auth')->warehouse();

        $spec = ProductSpec::with('product.category')
            ->whose($user->id)
            ->ofWarehouse($warehouse->id)
            ->where('relevance_code', $request->relevance_code)
            ->first();

        if (! $spec) {
            return formatRet(500, '商品外部编码不存在');
        }

        $product_name_cn = $spec->product->name_cn."（{$spec->name_cn}）";
        $product_name_en = $spec->product->name_en."({$spec->name_en})";

        $stock = ProductStock::whose($user->id)
            ->ofWarehouse($warehouse->id)
            ->where('relevance_code', $request->relevance_code);

        if (isset($spec->product->category->need_expiration_date) && $spec->product->category->need_expiration_date == 1) {
            $stock->where('expiration_date', '>', Carbon::now());
        }

        $stockin_num = $stock->sum('stockin_num');

        $data = [
            'warehouse' => [
                'warehouse_code' => $warehouse['code'],
                'warehouse_name_cn' => $warehouse['name_cn'],
                'warehouse_name_en' => $warehouse['name_en'],
                'warehouse_address' => $warehouse['country']
                               . ' ' . $warehouse['city']
                               . ' ' . $warehouse['street']
                               . ' ' . $warehouse['door_no'],
                'warehouse_postcode' => $warehouse['door_no'],
                'warehouse_phone' => $warehouse['contact_number'],
            ],
            'stock' => [
                'relevance_code'  => $request->relevance_code,
                'product_name_cn' => $product_name_cn,
                'product_name_en' => $product_name_en,
                'stock_num' => $stockin_num,
            ],
        ];

        return formatRet(0, '查询成功', $data);
    }

    /**
     * 批量查询仓库库存
     */
    public function total(Request $request)
    {
        $this->validate($request, [
            'relevance_codes'   => 'required|array',
            'relevance_codes.*' => 'required|string|distinct',
        ]);

        $user = app('auth')->user();
        $warehouse = app('auth')->warehouse();

        $data = [];

        foreach ($request->relevance_codes as $relevance_code) {
            $stock = ProductStock::whose($user->id)
                ->ofWarehouse($warehouse->id)
                ->whereIn('status', [
                    ProductStock::GOODS_STATUS_PREPARE,
                    ProductStock::GOODS_STATUS_ONLINE,
                ])
                ->where('relevance_code', $relevance_code)
                ->sum('stockin_num');

            $data[] = [
                'relevance_code'  => $relevance_code,
                'num' => $stock,
            ];
        }

        // if (isset($spec->product->category->need_expiration_date) && $spec->product->category->need_expiration_date == 1) {
        //     $stock->where('expiration_date', '>', Carbon::now());
        // }

        return formatRet(0, '查询成功', $data);
    }




    // 如果用一个APPKEY查多个仓库用下面代码

    // $warehouse_ids = WarehouseEmployee::where('user_id', $user->id)
    //     ->where('role_id', WarehouseEmployee::ROLE_RENTER)
    //     ->get()
    //     ->pluck('warehouse_id');

    // $warehouses = Warehouse::whereIn($warehouse_ids)->get([
    //     'id',
    //     'name_cn',
    //     'name_en',
    //     'code',
    //     'country',
    //     'city',
    //     'street',
    //     'door_no',
    //     'postcode',
    //     'contact_number',
    // ]);

    // $warehouses = $warehouses->keyBy('id')->all();

    // select `warehouse_id`, `expiration_date`, SUM(stockin_num) as stock_num from `product_stock` where `relevance_code` = 'JD001' and `warehouse_id` in (1, 2) group by `warehouse_id`, `expiration_date` having stock_num > 0



    // SELECT warehouse_id, expiration_date, SUM(stockin_num) AS stock_num FROM `product_stock` WHERE relevance_code = 'JD001' AND warehouse_id IN (1, 2) GROUP BY warehouse_id, expiration_date HAVING stock_num > 2

    // ;

    // $data = ProductStock::select('warehouse_id', DB::raw('SUM(stockin_num) as stock_num'))
    //                     ->where('relevance_code', $request->relevance_code)
    //                     ->groupBy('expiration_date')
    //                     ->having('stockin_num', '>=', $request->product_num);

    // $d = DB::table('product_stock')
    //           ->select('warehouse_id', 'expiration_date', DB::raw('SUM(stockin_num) as stock_num'))
    //           ->where('relevance_code', $request->relevance_code)
    //           ->whereIn('warehouse_id', $warehouse_ids)
    //           ->groupBy('warehouse_id', 'expiration_date')
    //           ->having('stock_num', '>=', $request->product_num)
    //           ->get();

    // if (! $d) {
    //     return formatRet(500, '查询不到数据');
    // }

    // $d = $d->groupBy('warehouse_id');

    // return DB::table('product_stock')
    //                   ->select('warehouse_id', 'expiration_date', DB::raw('SUM(stockin_num) as stock_num'))
    //                   ->where('relevance_code', $request->relevance_code)
    //                   ->whereIn('warehouse_id', $warehouse_ids)
    //                   ->groupBy('warehouse_id', 'expiration_date')
    //                   ->having('stock_num', '>=', $request->product_num)->toSql();

    
    // $data = [];

    // foreach ($d as $k => $v) {
    //     $e = [
    //         'warehouse_code' => $warehouses[$k]['code'],
    //         'warehouse_name_cn' => $warehouses[$k]['name_cn'],
    //         'warehouse_name_en' => $warehouses[$k]['name_en'],
    //         'warehouse_address' => $warehouses[$k]['country'].' '.
    //                                $warehouses[$k]['city'].' '.
    //                                $warehouses[$k]['street'].' '.
    //                                $warehouses[$k]['door_no'],
    //         'warehouse_postcode' => $warehouses[$k]['door_no'],
    //         // 'warehouse_user' => 'xiongshi',
    //         'warehouse_phone' => $warehouses[$k]['contact_number'],
    //     ];

    //     $products = [];
    //     if ($v) {
    //         foreach ($v as $p) {
    //             $products[] = [
    //                 'product_name_cn' => $product_name_cn,
    //                 'product_name_en' => $product_name_en,
    //                 'expiration_date' => $p->expiration_date,
    //                 'stock_num' => $p->stock_num,
    //             ];
    //         }
    //     };

    //     $e['products'] = $products;

    //     $data[] = $e;
    // }
}
