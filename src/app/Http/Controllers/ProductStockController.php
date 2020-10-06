<?php
namespace App\Http\Controllers;

use App\Exports\SkuExport;
use App\Exports\StockExport;
use App\Http\Requests\BaseRequests;
use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\ProductStockLog;
use App\Models\ProductStockLocation;
use App\Models\WarehouseLocation;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Events\StockIn;
use App\Events\StockOut;

class ProductStockController extends  Controller
{
    /**
     * 库存管理 - 列表
     */
    public function index(BaseRequests $request)
    {
        app('log')->info('拉取库存', $request->all());
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



        $results = ProductSpec::with(['stocks:spec_id,sku,best_before_date,expiration_date,production_batch_number,ean,relevance_code,stockin_num,shelf_num,warehouse_location_id,recount_times,stock_num,id'])
            ->leftjoin('product', 'product.id','=', 'product_spec.product_id')
            ->leftjoin('category', 'category.id','=', 'product.category_id')
            ->ofWarehouse($warehouse_id)
            ->where('product_spec.owner_id', app('auth')->ownerId())
            ->when($relevance_code = $request->input('relevance_code'), function ($query) use ($relevance_code) {
                $query->where('product_spec.relevance_code', $relevance_code);
            })
            ->when(($request->filled('show_low_stock') && $request->show_low_stock == 1), function($q) {
                    return  $q->whereRaw('product_spec.total_stock_num <= category.warning_stock and category.warning_stock >0');
                }
            )
            // API向前兼容
            ->when($keywords = $request->input('keywords'), function ($query) use ($keywords) {
                return $query->where('product_spec.name_cn', 'like', '%' . $keywords . '%')
                  ->orWhere('product_spec.name_en', 'like', '%' . $keywords . '%')
                  ->orWhere('product.hs_code', 'like', '%' . $keywords . '%')
                  ->orWhere('product.origin', 'like', '%' . $keywords . '%');
            })
            ->when($sku = $request->input('sku'), function ($query) use ($sku) {
                $query->hasSku($sku);
            })
            ->when($product_name = $request->input('product_name'), function ($query) use ($product_name) {
                return $query->where('product.name_cn', 'like', "%{$product_name}%")->orWhere('product.name_en', 'like', "%{$product_name}%")->orWhere('product_spec.relevance_code',  $product_name);
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
            ->select(['product_spec.id','product_spec.created_at','product_spec.name_cn','product_spec.name_en','product_spec.product_id','product_spec.purchase_price','product_spec.sale_price','product_spec.total_floor_num','product_spec.total_lock_num','product_spec.total_shelf_num','product_spec.total_stockin_num','product_spec.total_stockout_num','product_spec.warehouse_id','product_spec.relevance_code','product_spec.total_stockin_times','product_spec.total_stockout_times','product_spec.total_stock_num','product.name_cn as origin_product_name_cn','product.name_cn as origin_product_name_en',])
            // sortBy
            ->orderBy('product_spec.created_at', 'desc')
            ->orderBy('product_spec.id', 'desc')
            // 分页
            ->paginate($request->input('page_size',10))->toArray();

        $lang = app('translator')->locale()?:'cn';
        if ($results['data']) {
            foreach ($results['data'] as $k => &$v) {


                $product_name_cn = sprintf("%s (%s)" , $v["origin_product_name_cn"],  $v["name_cn"]);
                $product_name_en = sprintf("%s (%s)" , $v["origin_product_name_en"],  $v["name_en"]);
                // $results['data'][$k]['product_name'] = $lang == 'en'?$product_name_en:$product_name_cn;
                $results['data'][$k]['product_name'] = $product_name_cn;
                foreach ($v['stocks'] as $key => &$value) {
                    $value['warehouse_location_code'] = WarehouseLocation::getCode($value['warehouse_location_id']);
                }

            }
        }
        return formatRet(0, '', $results);
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

        $data = $log->orderby('created_at', 'desc')->orderby('id', 'desc')->paginate($request->input('page_size', 10), [
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
            'spec_total_stock_num',
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

        $data = $log->latest()->paginate($request->input('page_size', 10), [
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
            'stock_total_stock_num as sku_total_stock_num',
            'spec_id',
            'type_id',
            'created_at',
        ])->toArray();

        return formatRet(0, '', $data);
    }

    /**
     * 导出库存
     * 根据规格导出库存记录
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
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',app('auth')->ownerId());
                })
            ]
        ]);
        set_time_limit(0);
        $owner_id = app('auth')->ownerId();
        $warehouse_id = $request->input('warehouse_id');

        $option = $request->input('option');

        $specs = ProductSpec::with( 'product.category')
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
            ->limit(5000)//限制一下
            // 分页
            ->select(
                'id',
                'warehouse_id',
                'product_id',
                'name_cn',
                'name_en',
                'relevance_code',
                'owner_id',
                'total_stock_num',
                'total_stockin_times',
                'total_stockin_num',
                'total_stockout_times',
                'total_stockout_num'
            );

        $export = new StockExport();
        $export->setQuery($specs);

        return app('excel')->download($export, trans("message.productStockExportCaption").date('Y-m-d').'.xlsx');

    }

    /**
     * 导出货品规格列表
     */
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
                Rule::exists('warehouse','id')->where(function($q){
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
        $stocks = ProductStock::with(['locations'])
            ->ofWarehouse($warehouse_id)
            ->where('owner_id', app('auth')->ownerId())
            ->whereIn('spec_id', $spec_ids)
            ->where('status', '!=', ProductStock::GOODS_STATUS_OFFLINE);

        $export = new SkuExport();
        $export->setQuery($stocks);

        return app('excel')->download($export, trans("message.productSkuExportCaption").date('Y-m-d').'.xlsx');
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

        $stock = ProductStockLocation::with('spec.product')
            ->where('owner_id', app('auth')->ownerId())
            ->ofWarehouse($warehouse->id);

        // sku 还是 货位
        if ($location) {
            $stock->where('warehouse_location_id', $location->id);
        } else {
            $stock->where('sku', $request->code)->orWhere('ean', $request->code)->orWhere('relevance_code', $request->code);
        }

        $stocks= $stock->paginate();

        $re= [];
        foreach ($stocks as $s) {
            $re[] = [
                'ean' => $s->ean,
                'id'  => $s->id,
                'stock_id' => $s->stock_id,
                'sku' => $s->sku,
                'product_name' => $s->product_name,
                'shelf_num' => $s->shelf_num,
                'relevance_code' =>$s->relevance_code,
                'location_code'=>$s->location->code??'',
                'production_batch_number'=>$s->production_batch_number,
                'best_before_date'=>$s->best_before_date?$s->best_before_date->toDateString():"",
                'remark'=>"",
                'expiration_date'=>$s->expiration_date?$s->expiration_date->toDateString():"",
                'need_expiration_date' =>$s->need_expiration_date,
                'need_best_before_date' =>$s->need_best_before_date,
                'need_production_batch_number' => $s->need_production_batch_number,
                'need_expiration_date_name' =>$s->need_expiration_date_name,
                'need_best_before_date_name' =>$s->need_best_before_date_name,
                'need_production_batch_number_name' => $s->need_production_batch_number_name,
            ];
        }

        $result = $stocks->toArray();
        unset($result['data']);
        $result['data'] = $re;
        return formatRet(0, '', $result);
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
        $stock->append(['need_expiration_date','need_best_before_date','need_production_batch_number','need_expiration_date_name','need_best_before_date_name','need_production_batch_number_name',]);
        $stock->setHidden(['spec']);
        return formatRet(0, '', [
            'stock' => $stock->toArray(),
        ]);
    }

    /**
     * 库存 - 盘点
     */
    public function update(BaseRequests $request,$stock_id)
    {

        app('log')->info('桌面端 - 库存编辑', $request->post());

        $this->validate($request, [
            'id'                        => 'required|integer|min:1',
            'ean'                       => 'required|string|max:255',
            'expiration_date'           => 'date_format:Y-m-d',
            'best_before_date'          => 'date_format:Y-m-d',
            'production_batch_number'   => 'string|max:255',
            'locations'                 => 'required|array',
            'locations.*.id'            => 'required|integer|min:0',
            'locations.*.shelf_num'     => 'required|integer|min:0',
            'locations.*.remark'       => 'string|max:255',
        ]);

        $warehouse = app('auth')->warehouse();
        $stock = ProductStock::ofWarehouse($warehouse->id)
            ->when(app('auth')->isLimited(),function($q){
                return $q->whereIn('owner_id',app('auth')->ownerId());
            })
            ->findOrFail($request->id);


        $category = $stock->spec->product->category;
        if ($category) 
        {
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

        app('db')->beginTransaction();
        try {

            $stock->ean                     = $request->ean;
            $stock->expiration_date         = $request->input('expiration_date') ?: null;
            $stock->best_before_date        = $request->input('best_before_date') ?: null;
            $stock->production_batch_number = $request->input('production_batch_number', '');
            $stock->save();
            //同位货位上面的信息
            $stock->syncLocationInfo();

            $old_stock_num = $stock->stock_num;
            $new_stock_num = 0;
            foreach ($request->locations as $key => $location) {

                $stockLocation = ProductStockLocation::ofWarehouse($warehouse->id)
                ->where("stock_id", $request->id)
                ->findOrFail($location["id"]);

                //盘点后的库存减去盘点前的上架库存
                $final_num = $location["shelf_num"] - $stockLocation["shelf_num"];

                $stockLocation->adjustShelfNum($location["shelf_num"]);


            }


            app('db')->commit();
            

        } catch (\Exception $e) {
            app('db')->rollback();
            info('桌面端 - 库存盘点', ['exception msg' => $e->getMessage()]);

            return formatRet(500, trans('message.failed'));
        }

        return formatRet(0);
    }

    /**
     * 
     */
    public  function  getInfoBySku(BaseRequests $request, $sku)
    {
        app('log')->info('查看库存详情', $request->input());

        $this->validate($request, [
            'warehouse_id'            =>[
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where('owner_id',Auth::ownerId())
            ],
            'batch_id' => 'required|integer|min:1',
        ]);

        $stock = ProductStock::where('relevance_code',$sku)->where('batch_id',$request->input('batch_id'))->where('warehouse_id',$request->input('warehouse_id'))->first();
        if(!$stock){
            return formatRet(500,'sku不存在');
        }
        $stock->append(['need_expiration_date','need_best_before_date','need_production_batch_number','need_expiration_date_name','need_best_before_date_name','need_production_batch_number_name','product_name']);
        $stock->setHidden(['spec']);
        return formatRet(0,'',$stock->toArray());

    }

    public function getLocations(BaseRequests $request)
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

        $stock = ProductStockLocation::with('spec.product')
            ->where('owner_id', app('auth')->ownerId())
            ->ofWarehouse($warehouse->id);

        // sku 还是 货位
        if ($location) {
            $stock->where('warehouse_location_id', $location->id);
        } else {
            $stock->where('sku', $request->code)->orWhere('ean', $request->code)->orWhere('relevance_code', $request->code);
        }

        $stocks= $stock->paginate($request->input('page_size',100));

        $arr= [];
        foreach ($stocks as $stockLoation) {

            $arr[] = [
                    'id'                    =>  $stockLoation->id,
                    'name_cn'               =>  $stockLoation->stock->product_name_cn,
                    'name_en'               =>  $stockLoation->stock->product_name_en,
                    'relevance_code'        =>  $stockLoation->stock->spec->relevance_code,
                    'stock_sku'             =>  $stockLoation->stock->sku,
                    'shelf_num_orgin'       =>  $stockLoation->shelf_num,
                    'shelf_num_now'         =>  $stockLoation->shelf_num,
                    'total_purcharse_orgin' =>  $stockLoation->stock->purchase_price * $stockLoation->shelf_num,
                    'total_purcharse_now'   =>  $stockLoation->stock->purchase_price * $stockLoation->shelf_num,
                    'status'                =>  1,
                    'location_code'         =>  $stockLoation->warehouse_location_code,
                    'location_id'           =>  $stockLoation->warehouse_location_id,
                ];
        }

        $result = $stocks->toArray();
        unset($result['data']);
        $result['data'] = $arr;
        return formatRet(0, '', $result);
    }

    /**
     * 根据规格ID得到位置
     *
     */
    public function getLocationBySpec(BaseRequests $request){

        $this->validate($request, [
            'warehouse_id'            =>[
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where('owner_id',Auth::ownerId())
            ],
            'spec'      => 'required|array',
            'spec.*' => 'required|integer|min:1',
        ]);


        return formatRet(0,'',app("recount")->getLocationBySpec($request->all()));
    }


    /**
     * 日志类型
     */
    public function getLogType(){
        return formatRet(0,'', ProductStockLog::getAllType());
    }

}