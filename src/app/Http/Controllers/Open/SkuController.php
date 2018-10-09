<?php

namespace App\Http\Controllers\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\Batch;

class SkuController extends Controller
{
    public function __construct()
    {
        $this->user = app('auth')->user();

        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 查询仓库库存
     */
    public function retrieveByCode(Request $request)
    {
        $this->validate($request, [
            'relevance_code' => 'required|string|max:255',
        ]);

        $user_id = app('auth')->user()->id;
        $warehouse_id = app('auth')->warehouse()->id;

        $data = [];

        $spec = ProductSpec::whose($user_id)
            ->ofWarehouse($warehouse_id)
            ->where('relevance_code', $request->relevance_code)
            ->first();

        if (! $spec) {
            return formatRet(500, '外部编码不存在');
        };

        $data = [
            'name_cn' => $spec->product_name_cn,
            'name_en' => $spec->product_name_en,
            'relevance_code' => $request->relevance_code,
            'items' => [],
        ];

        // select * from `product_stock` where `warehouse_id` = ? and `owner_id` = ? and `spec_id` = ? and `status` != ? and not exists (
        //    select * from `batch` where `product_stock`.`batch_id` = `batch`.`id` and (`status` = ? or `status` = ?)
        // )

        $stocks = ProductStock::ofWarehouse($warehouse_id)
            ->whose($user_id)
            ->where('spec_id', $spec->id)
            ->where('status', '!=', ProductStock::GOODS_STATUS_OFFLINE)
            ->doesntHave('batch', 'and', function ($query) {
                $query->where('status', Batch::STATUS_PREPARE)->orWhere('status', Batch::STATUS_CANCEL);
            })
            ->get();

        $stocksArray = [];
        if (Arr::accessible($stocks)) {
            foreach ($stocks as $k => $stock) {
                $stocksArray[$k]['id'] = $stock->id;
                $stocksArray[$k]['lot'] = $stock->production_batch_number ?? '';
                $stocksArray[$k]['bbd'] = isset($stock->best_before_date) ? $stock->best_before_date->toDateString() : '';
                $stocksArray[$k]['total_num'] = $stock->stockin_num;
                $stocksArray[$k]['reserved_num'] = $stock->getCurrentLockNum();;
            }
        }

        if ($stocksArray) {
            $collection = collect($stocksArray);

            $grouped = $collection->groupBy(['lot', 'bbd']);

            if (Arr::accessible($grouped)) {
                $items = [];

                foreach ($grouped as $lot => $lv1) {
                    if (Arr::accessible($lv1)) {
                        foreach ($lv1 as $bbd => $lv2) {

                            $m = [];
                            $total_num = 0;
                            $reserved_num = 0;
                            $available_num = 0;

                            if (Arr::accessible($lv2)) {
                                foreach ($lv2 as $lv3) {
                                    $total_num += $lv3['total_num'];
                                    $reserved_num += $lv3['reserved_num'];
                                    $available_num = max($total_num - $reserved_num, 0);
                                }
                            }

                            $m = compact('lot', 'bbd', 'total_num', 'available_num', 'reserved_num');
                            $items[] = $m;
                        }
                    }
                }

                $data['items'] = $items;
            }
        }

        return formatRet(0, '', $data);
    }

    // /**
    //  * 批量查询仓库库存
    //  */
    // public function retrieveByCodes(Request $request)
    // {
    //     $this->validate($request, [
    //         'relevance_code' => 'required|string|max:255',
    //     ]);

    //     $user_id = app('auth')->user()->id;
    //     $warehouse_id = app('auth')->warehouse()->id;

    //     $data = [];

    //     $spec = ProductSpec::whose($user_id)
    //         ->ofWarehouse($warehouse_id)
    //         ->where('relevance_code', $request->relevance_code)
    //         ->first();

    //     if (! $spec) {
    //         return formatRet(500, '商品外部编码不存在');
    //     };

    //     $data = [
    //         'name_cn' => $spec->product_name_cn,
    //         'name_en' => $spec->product_name_en,
    //     ];

    //     $stocks = ProductStock::with(['batch', 'location'])//:id,code
    //         ->doesntHave('batch', 'and', function ($query) {
    //             $query->where('status', Batch::STATUS_PREPARE)->orWhere('status', Batch::STATUS_CANCEL);
    //         })
    //         ->ofWarehouse($warehouse_id)
    //         ->whose($user_id)
    //         ->where('spec_id', $spec->id)
    //         ->where('status', '!=', ProductStock::GOODS_STATUS_OFFLINE)
    //         ->get();

    //     if ($stocks) {

    //         $skus = [];
    //         foreach ($stocks as $stock) {
    //             $sku = [];

    //             $sku['sku'] = $stock->sku;
    //             $sku['ean'] = $stock->ean;
    //             $sku['bbd'] = isset($stock->best_before_date) ? $stock->best_before_date->toDateString() : '';
    //             $sku['exp'] = isset($stock->expiration_date) ? $stock->expiration_date->toDateString() : '';
    //             $sku['pdn'] = $stock->production_batch_number ?? '';
    //             $sku['entry_date'] = isset($stock->batch->updated_at) ? $stock->batch->updated_at->toDateString() : '';
    //             $sku['location_code'] = isset($stock->location->code) ? $stock->location->code : '';

    //             $sku['total_num'] = $stock->stockin_num;

    //             $sku['lock_num'] = $stock->getCurrentLockNum();
    //             $sku['available_num'] = max($sku['total_num'] - $sku['lock_num'], 0);

    //             $skus[] = $sku;
    //         }

    //         $data['skus'] = $skus;
    //     }

    //     return formatRet(0, '', $data);
    // }
}
