<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSpec;
use App\Models\ProductSku;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\WarehouseEmployee;
use App\Rules\PageSize;

/**
 * 库存管理
 */
class ProductStockController extends Controller
{
    public function __construct()
    {
        $this->warehouse = app('auth')->warehouse();
    }

    /**
     * 库存管理 - 列表
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'keywords'                => 'string',
            'sku'                     => 'string',
            'relevance_code'          => 'string',
            'production_batch_number' => 'string',
            'product_name'            => 'string',
            'option'                  => 'integer|min:1|max:3',
        ]);

        $owner_id = app('auth')->id();
        $warehouse_id = $this->warehouse->id;

        $option = $request->input('option');

        $results = ProductSpec::with('owner:id,email', 'product.category.feature')
            ->ofWarehouse($warehouse_id)
            ->when(app('auth')->isRenter(), function ($query) use ($owner_id) {
                $query->whose($owner_id);
            })
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
            ->paginate($request->input('page_size'), [
                'id',
                'warehouse_id',
                'product_id',
                'name_cn',
                'name_cn',
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
                        $sku['shelf_num_waiting']       = 
                            $stock->status == ProductStock::GOODS_STATUS_PREPARE && in_array($stock->batch->status, [Batch::STATUS_PROCEED, Batch::STATUS_ACCOMPLISH])
                                ? $s['stockin_num']
                                : 0;

                        $sku['edit_count'] = $s['edit_count'];
                        $sku['location_code'] = isset($stock->location->code) ? $stock->location->code : '';

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
    public function log(Request $request)
    {
        $this->validate($request, [
            'spec_id'     => 'required|integer',
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'created_at_b' => 'date_format:Y-m-d',
            'created_at_e' => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'type_id'      => 'integer',
        ]);

        $warehouse_id = $this->warehouse->id;

        $log = ProductStockLog::ofWarehouse($warehouse_id)->where('spec_id', $request->spec_id);

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
    public function getLogsForSku(Request $request)
    {
        $this->validate($request, [
            'stock_id'     => 'required|integer',
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'created_at_b' => 'date_format:Y-m-d',
            'created_at_e' => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'type_id'      => 'integer',
        ]);

        $warehouse_id = $this->warehouse->id;

        $log = ProductStockLog::ofWarehouse($warehouse_id)->where('product_stock_id', $request->stock_id);

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
}