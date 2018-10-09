<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\Batch;
use App\Models\Warehouse;

class StockService
{
    /**
     * 根据外部编号
     */
    public function retrieveByCodeAndGroupedByLOTAndBBD(User $user, Warehouse $warehouse, ProductSpec $spec)
    {
        $data = [
            'relevance_code' => $spec->relevance_code,
            'name_cn' => $spec->product_name_cn,
            'name_en' => $spec->product_name_en,
            'items' => [],
        ];

        // select * from `product_stock` 
        // where `warehouse_id` = ? and `owner_id` = ? and `spec_id` = ? and `status` != ? and not exists (
        //     select * from `batch` 
        //     where `product_stock`.`batch_id` = `batch`.`id` and (`status` = ? or `status` = ?)
        // )

        $stocks = ProductStock::ofWarehouse($warehouse->id)
            ->whose($user->id)
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

        return $data;
    }
}
