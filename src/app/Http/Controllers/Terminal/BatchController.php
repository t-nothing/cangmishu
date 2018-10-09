<?php

namespace App\Http\Controllers\Terminal;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 入库单 - 详情，根据入库单号拉取
     *
     * @author liusen
     */
    public function retrieveByCode(Request $request)
    {
        $this->validate($request, [
            'batch_code' => 'required|string|max:255',
        ]);

        if (! $batch = Batch::ofWarehouse($this->warehouse->id)->where('batch_code', $request->batch_code)->first()) {
            return formatRet(404, '入库单号不存在');
        }

        switch ($batch->status) {
            case Batch::STATUS_ACCOMPLISH:
                // 已经入库完成的入库单，手持端仍然可以继续扫描，查询信息，打印 SKU
                // return formatRet(500, '入库单已经完成');
                break;
            case Batch::STATUS_CANCEL:
                return formatRet(500, '入库单已被取消');
                break;
            default:
                # code...
                break;
        }

        $batch->load('batchProducts');

        return formatRet(0, '', $batch->toArray());
    }

    /**
     * 入库单 - 货品详情（手持端扫描货品外部代码或供应商代码）
     */
    public function getProductByCode(Request $request)
    {
        $this->validate($request, [
            'batch_id' => 'required|integer|min:1',
            'code'     => 'required|string|min:1',
        ]);

        $batchProduct = ProductStock::with('spec.product.category')
                                    ->ofWarehouse($this->warehouse->id)
                                    ->where('batch_id', $request->batch_id)
                                    ->where(function ($query) use ($request) {
                                        $query->where('relevance_code', $request->code)
                                              ->orWhere('distributor_code', $request->code);
                                    })->first();

        if (! $batchProduct) {
            return formatRet(404, '在入库单中查无该货品', []);
        }

        return formatRet('0', '', $batchProduct->toArray());
    }

    /**
     * 入库单 - 完成入库单
     */
    public function purchaseNoteEnd(Request $request)
    {
        $this->validate($request, [
            'batch_id' => 'required|integer|min:1',
        ]);

        if (! $batch = Batch::ofWarehouse($this->warehouse->id)->find($request->batch_id)) {
            return formatRet(404, '入库单不存在');
        }

        if ($batch->status !== Batch::STATUS_PROCEED) {
            return formatRet(500, '入库单状态不是入库中');
        }

        $batch->status = Batch::STATUS_ACCOMPLISH;

        if ($batch->save()) {
            return formatRet(0);
        }

        return formatRet(500, '操作失败');
    }
}


