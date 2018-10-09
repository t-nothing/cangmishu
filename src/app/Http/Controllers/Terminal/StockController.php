<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ProductSku;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\WarehouseLocation;

class StockController extends Controller
{
	/**
     * 确认入库，并打印sku
     */
    public function in(Request $request)
    {
        app('log')->info('手持端 - 分批入库', $request->input());

        $this->validate($request, [
            'stock_id' 				  => 'required|integer|min:1',
            'stockin_num' 		      => 'required|integer|min:1|max:9999',
            'pieces_num' 		      => 'present|integer|min:1',
            'box_code' 				  => 'present|string|max:255',
            'distributor_code' 		  => 'required|string|max:255',
            'ean' 				      => 'required|string|max:255',
            'expiration_date' 		  => 'date_format:Y-m-d',
            'best_before_date'        => 'date_format:Y-m-d',
            'production_batch_number' => 'string|max:255',
            'remark'                  => 'present|string|max:255',
        ]);

        $user_id = Auth::id();
        $warehouse = Auth::warehouse();

        $stock = ProductStock::ofWarehouse($warehouse->id)->findOrFail($request->stock_id);

        $stock->load(['batch', 'spec.product.category']);

        if (! $stock->batch) {
            return formatRet(404, '找不到该入库单');
        }

        if (! $stock->batch->canStockIn()) {
            return formatRet(500, '入库单已完成');
        }

        $category = $stock->spec->product->category;

        if ($category) {
            $rules = [];

            $category->need_expiration_date == 1 AND
                $rules['expiration_date'] = 'required|date_format:Y-m-d';

            $category->need_best_before_date == 1 AND
                $rules['best_before_date'] = 'required|date_format:Y-m-d';

            $category->need_production_batch_number == 1 AND
                $rules['production_batch_number'] = 'required|string|max:255';

            $rules &&
                $this->validate($request, $rules);
        }

        app('db')->beginTransaction();
        try {
            // 入库单状态，修改为，入库中
            $stock->batch->status = Batch::STATUS_PROCEED;
            $stock->batch->save();
            // 入库单信息完善
            $stock->pieces_num              = $request->pieces_num;
            $stock->box_code                = $request->box_code;
            $stock->distributor_code        = $request->distributor_code;
            $stock->ean                     = $request->ean;
            $stock->expiration_date         = $request->input('expiration_date') ?: null;
            $stock->best_before_date        = $request->input('best_before_date') ?: null;
            $stock->production_batch_number = $request->input('production_batch_number', '');
            $stock->remark                  = $request->remark;
            $stock->save();
            // 入库数量更新
            $stock->increment('stockin_num', $request->stockin_num);
            $stock->increment('total_stockin_num', $request->stockin_num);

            // 添加入库单记录
            $stock->addLog(ProductStockLog::TYPE_BATCH, $request->stockin_num);

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();

            info('入库', ['exception msg' => $e->getMessage()]);
            return formatRet(500, '数据库处理数据失败');
        }

        return formatRet(0, '入库单商品入库成功', $stock->toArray());
    }

    /**
     * 查询某货位上的所有SKU
     */
    public function getSkus(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string',
        ]);

        $warehouse = app('auth')->warehouse();

        $location = WarehouseLocation::ofWarehouse($warehouse->id)->enabled()->where('code', $request->code)->first();

        $stock = ProductStock::with('spec.product')->ofWarehouse($warehouse->id)->enabled();

        // sku 还是 货位
        if ($location) {
            $stock->where('warehouse_location_id', $location->id);
        } else {
            $stock->where('sku', $request->code);
        }

        if ($stock) {
            $stocks = $stock->get();
        }

        if (! $stocks->toArray()) {
            if ($location) {
                return formatRet(500, '货位 '.$request->code. ' 暂无库存记录');
            } else {
                return formatRet(500, '什么也没有找到，扫别的试试？');
            }
        }

        $data = [];
        foreach ($stocks as $s) {
            $data[] = [
                'stock_id' => $s->id,
                'sku' => $s->sku,
                'product_name' => $s->spec->product_name,
                'shelf_num' => $s->shelf_num,
            ];
        }

        return formatRet(0, '', $data);
    }

    /**
     * 库存 - 详情
     */
    public function show(Request $request)
    {
        $this->validate($request, [
            'stock_id' => 'required|integer',
        ]);

        $warehouse = app('auth')->warehouse();

        $stock = ProductStock::with([
            'spec.product.category',
            'location:id,code',
            'logs' => function ($query) {
                $query->where('type_id', ProductStockLog::TYPE_COUNT);
                // 'id', 'type_id', 'sku_total_shelf_num_old', 'sku_total_shelf_num', 'created_at'
            },
        ])->ofWarehouse($warehouse->id)->enabled()->findOrFail($request->stock_id);

        return formatRet(0, '', [
            'stock' => $stock,
        ]);
    }

    /**
     * 库存 - 编辑
     */
    public function edit(Request $request)
    {
        app('log')->info('手持端 - 库存盘点', $request->input());

        $this->validate($request, [
            'stock_id'                => 'required|integer|min:1',
            'stock_num'               => 'required|integer|min:0|max:9999',
            'ean'                     => 'required|string|max:255',
            'expiration_date'         => 'date_format:Y-m-d',
            'best_before_date'        => 'date_format:Y-m-d',
            'production_batch_number' => 'string|max:255',
            'location_code'           => 'required|string',
            'remark'                  => 'string|max:255',
        ]);

        $warehouse = Auth::warehouse();

        $stock = ProductStock::with('spec.product.category')
            ->ofWarehouse($warehouse->id)
            ->where('status', ProductStock::GOODS_STATUS_ONLINE)
            ->findOrFail($request->stock_id);

        $category = $stock->spec->product->category;
        if ($category) {
            $rules = [];
            $category->need_expiration_date == 1 AND
                $rules['expiration_date'] = 'required|date_format:Y-m-d';
            $category->need_best_before_date == 1 AND
                $rules['best_before_date'] = 'required|date_format:Y-m-d';
            $category->need_production_batch_number == 1 AND
                $rules['production_batch_number'] = 'required|string|max:255';
            $rules AND
                $this->validate($request, $rules);
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

            $stock->shelf_num               = $request->stock_num;
            $stock->stockin_num             = $stock->getCurrentStockinNum($request->stock_num);
            $stock->ean                     = $request->ean;
            $stock->expiration_date         = $request->input('expiration_date') ?: null;
            $stock->best_before_date        = $request->input('best_before_date') ?: null;
            $stock->production_batch_number = $request->input('production_batch_number', '');
            $stock->warehouse_location_id   = $location->id;
            $stock->save();

            // 添加入库单记录
            $stock->addLog(ProductStockLog::TYPE_COUNT, $request->stock_num, '', $sku_total_shelf_num_old, $request->input('remark', ''));

            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            info('手持端 - 库存盘点', ['exception msg' => $e->getMessage()]);

            return formatRet(500, '失败');
        }

        return formatRet(0);
    }

    /**
     * 作废
     */
    public function off(Request $request)
    {
        $this->validate($request, [
            'stock_id' => 'required|integer',
        ]);

        $warehouse = app('auth')->warehouse();

        $stock = ProductStock::ofWarehouse($warehouse->id)->enabled()->findOrFail($request->stock_id);

        try {
            $stock->status = ProductStock::GOODS_STATUS_OFFLINE;
            $stock->save();

            $stock->addLog(ProductStockLog::TYPE_OFFLINE, 0);

        } catch (\Exception $e) {
            app('db')->rollback();
            app('log')->info('手持端 - 作废', ['exception msg' => $e->getMessage()]);

            return formatRet(500, '失败');
        }

        return formatRet(0);
    }
}