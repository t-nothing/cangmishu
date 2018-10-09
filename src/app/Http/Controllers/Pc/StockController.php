<?php

namespace App\Http\Controllers\PC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\PageSize;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\Shelf;
use App\Models\WarehouseLocation;

class StockController extends Controller
{
    /**
     * 库存 - 列表
     *
     * @author liusen
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'keywords'     => 'string',
            'warehouse_id' => 'required|integer|min:1',
        ]);

        $warehouse = app('auth')->warehouse();

        $stock = ProductStock::with(['spec.product', 'location.warehouseArea'])
                             ->ofWarehouse($warehouse->id)
                             ->where('status', ProductStock::GOODS_STATUS_ONLINE)
                             ->orderByDesc('relevance_code');

        if ($request->filled('keywords')) {
            $stock->hasKeyword($request->keywords);
        }

        $stocks = $stock->paginate($request->input('page_size'));

        return formatRet(0, '', $stocks->toArray());
    }

    /**
     * 库存 - 编辑
     *
     * @author liusen
     */
    public function edit(Request $request)
    {
        app('log')->info('桌面端 - 库存编辑', $request->post());

        $this->validate($request, [
            'stock_id'                => 'required|integer|min:1',
            'shelf_num'               => 'required|integer|min:0|max:9999',
            'ean'                     => 'required|string|max:255',
            'location_code'           => 'required|string|max:255',
            'production_batch_number' => 'present|string|max:255',
            'expiration_date'         => 'present|date_format:Y-m-d',
            'best_before_date'        => 'present|date_format:Y-m-d',
            'remark'                  => 'string|max:255',
        ]);

        $warehouse = app('auth')->warehouse();

        if (! $stock = ProductStock::ofWarehouse($warehouse->id)->find($request->stock_id)) {
            return formatRet(404, '库存记录不存在');
        }

        $stock->load(['spec.product.category', 'location.warehouseArea']);

        if (isset($stock->spec->product->category)) {
            $category = $stock->spec->product->category;
            if ($category->need_production_batch_number == 1) {
                $this->validate($request, ['production_batch_number' => 'required|string|max:255']);
            }

            if ($category->need_expiration_date == 1) {
                $this->validate($request, ['expiration_date' => 'required|date_format:Y-m-d']);
            }

            if ($category->need_best_before_date == 1) {
                $this->validate($request, ['best_before_date' => 'required|date_format:Y-m-d']);
            }
        }

        $location = WarehouseLocation::ofWarehouse($warehouse->id)
            ->enabled()
            ->where('code', $request->location_code)
            ->first();

        if (! $location) {
            return formatRet(500, '货位不存在或未启用');
        }

        app('db')->beginTransaction();
        try {
            // 原库存
            $sku_total_shelf_num_old = ProductStock::ofWarehouse($stock->warehouse_id)
                ->enabled()
                ->whose($stock->owner_id)
                ->where('sku', $stock->sku)->sum('shelf_num');

            // 修改库存记录
            $stock->shelf_num               = $request->shelf_num;
            $stock->stockin_num             = $stock->getCurrentStockinNum($request->shelf_num);
            $stock->ean                     = $request->ean;
            $stock->production_batch_number = $request->input('production_batch_number', '');
            $stock->expiration_date         = $request->input('expiration_date') ?: null;
            $stock->best_before_date        = $request->input('best_before_date') ?: null;
            $stock->warehouse_location_id   = $location->id;
            $stock->save();

            // 添加入库单记录
            $stock->addLog(ProductStockLog::TYPE_COUNT, $request->shelf_num, '', $sku_total_shelf_num_old, $request->input('remark', ''));

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            info('桌面端 - 库存盘点', ['exception msg' => $e->getMessage()]);

            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}
