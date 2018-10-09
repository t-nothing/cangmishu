<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\WarehouseLocation;

class StoreController extends Controller
{
    /**
     * 上架 - 扫描 SKU 返回 商品名,入库数量,sku,入库编号
     *
     * @author liusen
     */
    public function retrieveBySku(Request $request)
    {
        $this->validate($request, [
            'sku' => 'required|string',
        ]);

        $warehouse_id = app('auth')->warehouse()->id;

        if (! $stock = ProductStock::ofWarehouse($warehouse_id)->where('sku', $request->sku)->first()) {
            return formatRet(500, 'SKU不存在');
        }

        $stock->load(['spec.product', 'batch']);

        $stock->append('recommended_locations');

        if (! $stock->batch) {
            return formatRet(500, 'SKU所属的入库单数据丢失');
        }

        if ($stock->batch->status !== Batch::STATUS_ACCOMPLISH) {
            return formatRet(500, '请先完成'. $request->sku . '所属的入库单');
        }

        return formatRet(0, '', $stock->toArray());
    }

    /**
     * 上架 - 商品放入货位
     *
     * @author liusen
     */
    public function putOn(Request $request)
    {
        $this->validate($request, [
            'skus'       => 'required|array',
            'skus.*'     => 'required|string|max:255',
            'code'       => 'required|string|max:255',
        ]);

        $warehouse_id = app('auth')->warehouse()->id;

        if (! $location = WarehouseLocation::ofWarehouse($warehouse_id)->where('code', $request->code)->first()) {
            return formatRet(500, '货位不存在('.$request->code.')');
        }

        if ($location->is_enabled == 0) {
            return formatRet(500, '货位未启用('.$request->code.')');
        }

        foreach ($request->skus as $sku) {
            if (! $stock = ProductStock::ofWarehouse($warehouse_id)->where('sku', $sku)->first()) {
                return formatRet(500, 'SKU不存在('.$sku.')');
            }

            if ($stock->status == ProductStock::GOODS_STATUS_ONLINE) {
                return formatRet(500, '已上架');
            }

            if (! $stock->batch) {
                return formatRet(500, 'SKU所属的入库单数据丢失');
            }

            if ($stock->batch->status != Batch::STATUS_ACCOMPLISH) {
                return formatRet(500, 'SKU所属的入库单状态不是入库完成');
            }

            $stocks[] = $stock;
        }

        app('db')->beginTransaction();
        try {
            if ($stocks) {
                foreach ($stocks as $stock) {
                    $stock->warehouse_location_id = $location->id;
                    $stock->shelf_num             = $stock->stockin_num;
                    $stock->total_shelf_num       = $stock->stockin_num;
                    $stock->status                = ProductStock::GOODS_STATUS_ONLINE;
                    $stock->save();

                    // 添加记录
                    $stock->addLog(ProductStockLog::TYPE_SHELF, $stock->stockin_num);
                }
            }

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();

            info('上架', ['exception msg' => $e->getMessage()]);
            return formatRet(500, '数据库处理数据失败');
        }

        return formatRet(0);
    }
}
