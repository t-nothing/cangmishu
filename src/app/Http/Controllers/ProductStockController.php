<?php
namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Models\Batch;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\WarehouseLocation;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductStockController extends  Controller
{
    /**
     * 库存管理 - 列表
     */
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'keywords'                => 'string',
            'sku'                     => 'string',
            'relevance_code'          => 'string',
            'production_batch_number' => 'string',
            'product_name'            => 'string',
            'option'                  => 'integer|min:1|max:3',
            'warehouse_id'            => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ]
        ]);

        $owner_id = app('auth')->ownerId();
        $warehouse_id = $request->input('warehouse_id');
        $option = $request->input('option');

        $results = ProductSpec::with('owner:id,email', 'product.category.feature')
            ->ofWarehouse($warehouse_id)
            ->where('owner_id', app('auth')->ownerId())
            ->when($relevance_code = $request->input('relevance_code'), function ($query) use ($relevance_code) {
                $query->where('relevance_code', $relevance_code);
            })
            // API向前兼容
            ->when($keywords = $request->input('keywords'), function ($query) use ($keywords) {
                $query->hasKeyword($keywords);
            })
            ->when($sku = $request->input('sku'), function ($query) use ($sku) {
                $query->hasSku($sku);
            })
            ->when($product_name = $request->input('product_name'), function ($query) use ($product_name) {
                $query->hasProductName($product_name);
            })
            ->when($production_batch_number = $request->input('production_batch_number'), function ($query) use ($production_batch_number) {
                $query->hasProductBatchNumber($production_batch_number);
            })
            // options
            ->when($option == 1, function ($query) {
                $query->onlyEdited();
            })
            ->when($option == 2, function ($query) {
                $query->onlyNeverEdited();
            })
            ->when($option == 3, function ($query) use ($warehouse_id, $owner_id) {
                $query->onlyToBeOnShelf($warehouse_id, $owner_id);
            })
            // sortBy
            ->latest()
            // 分页
            ->paginate($request->input('page_size',10), [
                'id',
                'warehouse_id',
                'product_id',
                'name_cn',
                'name_en',
                'relevance_code',
                'owner_id',
            ]);

        if ($results) {
            foreach ($results as $key => $spec) {

                $spec->append([
                    'product_name',
                    'stock_in_warehouse',// 仓库库存
                    'stock_on_shelf',// 上架库存
                    'stock_entrance_times',// 入库次数
                    'stock_out_times',// 出库次数
                    'stock_entrance_qty',// 入库次数
                    'stock_out_qty',// 出库次数
                    'reserved_num',// 锁定库存
                    'available_num',// 可用库存
                    'stock_to_be_on_shelf',// 待上架库存
                ]);
            }
        }
        $data = $results->toArray();
        if ($data['data']) {
            foreach ($data['data'] as $k => $v) {
                $spec_id = $v['id'];

                $data['data'][$k]['owner'] = $v['owner']['email'];

                $stocks = ProductStock::with(['batch', 'location'])//:id,code
                ->withCount(['logs as edit_count' => function ($query) {
                    $query->where('type_id', ProductStockLog::TYPE_COUNT);
                }])
                    ->doesntHave('batch', 'and', function ($query) {
                        $query->where('status', Batch::STATUS_PREPARE)->orWhere('status', Batch::STATUS_CANCEL);
                    })
                    ->ofWarehouse($warehouse_id)
                    ->whose($v['owner_id'])
                    ->where('spec_id', $spec_id)
                    ->where('status', '!=', ProductStock::GOODS_STATUS_OFFLINE)
                    ->get();
                //根据商品库存单号查出对应的待验货数量、待上架数量
                $stocks = $stocks->map(function($stock) {
                    $stock->append(['to_be_verify', 'shelf_num_waiting']);
                    return $stock;
                });
                //待验货数量
                $data['data'][$k]['total_to_be_verify'] =$stocks->sum('to_be_verify');

                // SKU数
                $data['data'][$k]['sku_count'] = $stocks->count();
                $data['data'][$k]['feature_name_cn'] = $v['product']['category']['feature']['name_cn'] ?? '';

                // SKU
                $data['data'][$k]['stocks'] = [];

                if ($stocks) {
                    $skus = [];

                    foreach ($stocks as $stock) {
                        $sku = [];
                        $s = $stock->toArray();

                        $sku['stock_id']                = $s['id'];
                        $sku['spec_id']                 = $s['spec_id'];
                        $sku['sku']                     = $s['sku'];
                        $sku['ean']                     = $s['ean'];
                        $sku['production_batch_number'] = $s['production_batch_number'];
                        $sku['expiration_date']         = $s['expiration_date'];
                        $sku['best_before_date']        = $s['best_before_date'];
                        $sku['stockin_num']             = $s['stockin_num'];
                        $sku['shelf_num']               = $s['shelf_num'];
                        $sku['shelf_num_waiting']       = $s['shelf_num_waiting'];
                        $sku['edit_count'] = $s['edit_count'];
                        $sku['location_code'] = isset($stock->location->code) ? $stock->location->code : '';
                        $sku['to_be_verify']        = $s['to_be_verify'];
                        unset($sku['spec_id']);

                        $skus[] = $sku;
                    }

                    $data['data'][$k]['stocks'] = $skus;
                }

                unset(
                    $data['data'][$k]['warehouse_id'],
                    $data['data'][$k]['product_id'],
                    $data['data'][$k]['owner_id'],
                    $data['data'][$k]['name_cn'],
                    $data['data'][$k]['name_en'],
                    $data['data'][$k]['product']
                );
            }
        }

        return formatRet(0, '', $data);
    }

    /**
     * 商品规格的出入库记录
     */
    public function getLogsForSpec(BaseRequests $request,$spec_id)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'created_at_b' => 'date_format:Y-m-d',
            'created_at_e' => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'type_id'      => 'integer',
            'warehouse_id'            => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ]
        ]);

        $warehouse_id = $request->input('warehouse_id');

        $log = ProductStockLog::ofWarehouse($warehouse_id)
            ->with(['operatorUser:id,nickname,email'])
            ->where('owner_id', app('auth')->ownerId())
            ->where('spec_id', $spec_id);

        if ($request->filled('created_at_b')) {
            $log->where('created_at', '>', strtotime($request->created_at_b . ' 00:00:00'));
        }

        if ($request->filled('created_at_e')) {
            $log->where('created_at', '<', strtotime($request->created_at_e . ' 23:59:59'));
        }

        if ($request->filled('type_id')) {
            $log->where('type_id', $request->type_id);
        }

        $data = $log->latest()->paginate($request->input('page_size'), [
            'id',
            'operation_num',
            'operator',
            'order_sn',
            'owner_id',
            'warehouse_id',
            'product_stock_id',
            'remark',
            'sku',
            'spec_id',
            'spec_total_shelf_num',
            'spec_total_stockin_num',
            'type_id',
            'created_at',
        ])->toArray();

        return formatRet(0, '', $data);
    }

    /**
     * SKU的出库入记录
     */
    public function getLogsForSku(BaseRequests $request,$stock_id)
    {
        app('log')->info('获取sku出入库记录',$request->all());
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize(),
            'created_at_b' => 'date_format:Y-m-d',
            'created_at_e' => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'type_id'      => 'integer',
            'warehouse_id'            => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ]
        ]);

        $warehouse_id = $request->input('warehouse_id');

        $log = ProductStockLog::ofWarehouse($warehouse_id)
            ->with(['operatorUser:id,email,nickname'])
            ->where('owner_id', app('auth')->ownerId())
            ->where('product_stock_id', $stock_id);

        if ($request->filled('created_at_b')) {
            $log->where('created_at', '>', strtotime($request->created_at_b . ' 00:00:00'));
        }

        if ($request->filled('created_at_e')) {
            $log->where('created_at', '<', strtotime($request->created_at_e . ' 23:59:59'));
        }

        if ($request->filled('type_id')) {
            $log->where('type_id', $request->type_id);
        }

        $data = $log->latest()->paginate($request->input('page_size'), [
            'id',
            'operation_num',
            'operator',
            'order_sn',
            'owner_id',
            'warehouse_id',
            'product_stock_id',
            'remark',
            'sku',
            'sku_total_shelf_num',
            'sku_total_stockin_num',
            'spec_id',
            'type_id',
            'created_at',
        ])->toArray();

        return formatRet(0, '', $data);
    }

    /**
     * 导出库存
     */
    public function export(BaseRequests $request)
    {
        $this->validate($request, [
            'keywords'                => 'string',
            'sku'                     => 'string',
            'relevance_code'          => 'string',
            'production_batch_number' => 'string',
            'product_name'            => 'string',
            'option'                  => 'integer|min:1|max:3',
            'warehouse_id'            => [
                'required','integer','min:1',
                Rule::exists('warehouse')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ]
        ]);
        set_time_limit(0);
        $owner_id = app('auth')->ownerId();
        $warehouse_id = $request->input('warehouse_id');

        $option = $request->input('option');

        $specs = ProductSpec::with('owner:id,email,nickname', 'product.category.feature')
            ->ofWarehouse($warehouse_id)
            ->where('owner_id', app('auth')->ownerId())
            ->when($relevance_code = $request->input('relevance_code'), function ($query) use ($relevance_code) {
                $query->where('relevance_code', $relevance_code);
            })
            // API向前兼容
            ->when($keywords = $request->input('keywords'), function ($query) use ($keywords) {
                $query->hasKeyword($keywords);
            })
            ->when($sku = $request->input('sku'), function ($query) use ($sku) {
                $query->hasSku($sku);
            })
            ->when($product_name = $request->input('product_name'), function ($query) use ($product_name) {
                $query->hasProductName($product_name);
            })
            ->when($production_batch_number = $request->input('production_batch_number'), function ($query) use ($production_batch_number) {
                $query->hasProductBatchNumber($production_batch_number);
            })
            // options
            ->when($option == 1, function ($query) {
                $query->onlyEdited();
            })
            ->when($option == 2, function ($query) {
                $query->onlyNeverEdited();
            })
            ->when($option == 3, function ($query) use ($warehouse_id, $owner_id) {
                $query->onlyToBeOnShelf($warehouse_id, $owner_id);
            })
            // sortBy
            ->latest()
            // 分页
            ->select(
                'id',
                'warehouse_id',
                'product_id',
                'name_cn',
                'name_en',
                'relevance_code',
                'owner_id'
            );

        $export = new StockExport();
        $export->setQuery($specs);

        return app('excel')->download($export, '货品总库存'.date('Y-m-d').'.xlsx');

    }

    public function exportBySku(BaseRequests $request)
    {
        $this->validate($request, [
            'keywords'                => 'string',
            'sku'                     => 'string',
            'relevance_code'          => 'string',
            'production_batch_number' => 'string',
            'product_name'            => 'string',
            'option'                  => 'integer|min:1|max:3',
            'warehouse_id'            => [
                'required','integer','min:1',
                Rule::exists('warehouse')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ]
        ]);
        set_time_limit(0);
        $owner_id = app('auth')->ownerId();
        $warehouse_id = $request->input('warehouse_id');

        $option = $request->input('option');

        $spec_ids = ProductSpec::ofWarehouse($warehouse_id)
            ->where('owner_id', app('auth')->ownerId())
            ->when($relevance_code = $request->input('relevance_code'), function ($query) use ($relevance_code) {
                $query->where('relevance_code', $relevance_code);
            })
            // API向前兼容
            ->when($keywords = $request->input('keywords'), function ($query) use ($keywords) {
                $query->hasKeyword($keywords);
            })
            ->when($sku = $request->input('sku'), function ($query) use ($sku) {
                $query->hasSku($sku);
            })
            ->when($product_name = $request->input('product_name'), function ($query) use ($product_name) {
                $query->hasProductName($product_name);
            })
            ->when($production_batch_number = $request->input('production_batch_number'), function ($query) use ($production_batch_number) {
                $query->hasProductBatchNumber($production_batch_number);
            })
            // options
            ->when($option == 1, function ($query) {
                $query->onlyEdited();
            })
            ->when($option == 2, function ($query) {
                $query->onlyNeverEdited();
            })
            ->when($option == 3, function ($query) use ($warehouse_id, $owner_id) {
                $query->onlyToBeOnShelf($warehouse_id, $owner_id);
            })
            // sortBy
            ->latest()
            ->pluck('id')
            ->toArray();

        $stocks = ProductStock::with(['batch', 'location', 'owner:id,nickname'])
            ->withCount(['logs as edit_count' => function ($query) {
                $query->where('type_id', ProductStockLog::TYPE_COUNT);
            }])
            ->doesntHave('batch', 'and', function ($query) {
                $query->where('status', Batch::STATUS_PREPARE)->orWhere('status', Batch::STATUS_CANCEL);
            })
            ->ofWarehouse($warehouse_id)
            ->where('owner_id', app('auth')->ownerId())
            ->whereIn('spec_id', $spec_ids)
            ->where('status', '!=', ProductStock::GOODS_STATUS_OFFLINE);

        $export = new SkuExport();
        $export->setQuery($stocks);

        return app('excel')->download($export, 'SKU 库存'.date('Y-m-d').'.xlsx');
    }

    /**
     * 查询某货位上的所有SKU
     */
    public function getSkus(BaseRequests $request)
    {

        app('log')->info('查询某货位上的所有SKU',['owner_id'=>app('auth')->ownerId(),'request'=>$request->all()]);
        $this->validate($request, [
            'code' => 'required|string',
            'warehouse_id'=>[
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ]
        ]);

        $warehouse = app('auth')->warehouse();

        $location = WarehouseLocation::ofWarehouse($warehouse->id)->enabled()
            ->where('code', $request->code)->first();

        $stock = ProductStock::with('spec.product')
            ->where('owner_id', app('auth')->ownerId())
            ->ofWarehouse($warehouse->id)->enabled();

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
                'shelf_num_waiting'=>$s->shelf_num_waiting
            ];
        }

        return formatRet(0, '成功', $data);
    }


    /**
     * 库存 - 详情
     */
    public function show(BaseRequests $request)
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
        ])
            ->where('owner_id', app('auth')->ownerId())
            ->ofWarehouse($warehouse->id)
            ->enabled()
            ->findOrFail($request->stock_id);
        $stock->append(['to_be_verify', 'shelf_num_waiting']);
        return formatRet(0, '', [
            'stock' => $stock,
        ]);
    }

    /**
     * 库存 - 编辑
     */
    public function update(BaseRequests $request,$stock_id)
    {
        app('log')->info('手持端 - 库存盘点', $request->input());

        $this->validate($request, [
            'stock_num'               => 'required|integer|min:0|max:9999',
            'ean'                     => 'required|string|max:255',
            'expiration_date'         => 'date_format:Y-m-d',
            'best_before_date'        => 'date_format:Y-m-d',
            'production_batch_number' => 'string|max:255',
            'location_code'           => 'required|string',
            'remark'                  => 'string|max:255',
            'warehouse_id'            =>[
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where('owner_id',Auth::ownerId())
            ]
        ]);

        $warehouse = Auth::warehouse();

        $stock = ProductStock::ofWarehouse($warehouse->id)
            ->where('owner_id',app('auth')->ownerId())
            ->where('status', ProductStock::GOODS_STATUS_ONLINE)
            ->findOrFail($stock_id);

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
            $stock->stockin_num             = $request->stock_num;
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

}