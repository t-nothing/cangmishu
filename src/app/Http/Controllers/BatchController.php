<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use App\Models\Warehouse;
use App\Models\Batch;
use App\Models\BatchType;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\WarehouseEmployee;
use App\Models\BatchMarkLog;
use App\Models\SkuMarkLog;

class BatchController extends Controller
{
    /**
     * 入库单 - 列表
     */
    public function list(Request $request)
    {
        $this->validate($request, [
            'page'              => 'integer|min:1',
            'page_size'         => new PageSize,
            'warehouse_id'      => 'integer|min:1',
            'type_id'           => 'integer|min:1',
            'status'            => 'integer|min:1',
            'created_at_b'      => 'date_format:Y-m-d',
            'created_at_e'      => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'keywords'          => 'string|max:255',
        ]);

        $batch = Batch::with(['warehouse', 'batchType'])->ofWarehouse(app('auth')->warehouse()->id);

        if(app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER){
            $batch->whose(app('auth')->id());
        }

        if ($request->filled('type_id')) {
            $batch->where('type_id', $request->type_id);
        }

        if ($request->filled('status')) {
            $batch->where('status', $request->status);
        }

        if ($request->filled('keywords')) {
            $batch->hasKeyword($request->keywords);
        }

        if ($request->filled('created_at_b')) {
            $batch->where('created_at', '>', strtotime($request->created_at_b));
        }

        if ($request->filled('created_at_e')) {
            $batch->where('created_at', '<', strtotime($request->created_at_e));
        }

        $batches = $batch->latest()->paginate($request->input('page_size'));

        return formatRet(0, '', $batches->toArray());
    }

    /**
     * 入库单 - 添加普通入库单
     *
     * @author liusen
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'warehouse_id'                     => 'required|integer|min:1',
            'type_id'                          => 'required|integer|min:1',
            'batch_code'                       => 'required|string|max:255',
            'confirmation_number'              => 'required|string|max:255',
            'distributor_id'                   => 'required|integer|min:1',
            'plan_time'                        => 'date_format:Y-m-d H:i:s',//修改可为空
            'over_time'                        => 'date_format:Y-m-d H:i:s|after_or_equal:plan_time',//修改可为空
            'remark'                           => 'string|max:255',//修改可为空
            'product_stock'                    => 'required|array',
            'product_stock.*.relevance_code'   => 'required|string|max:255',
            'product_stock.*.need_num'         => 'required|integer|min:1',
            'product_stock.*.pieces_num'       => 'present|integer|min:1',
            'product_stock.*.distributor_code' => 'present|string|max:255|distinct',
            'product_stock.*.remark'           => 'present|string|max:255',
            'transportation_type'              => 'present|integer|min:0',
            'waybill_number'                   => 'present|string|max:255',
        ]);

        $warehouse = app('auth')->warehouse();

        if (! $warehouse) {
            return formatRet(500, '仓库不存在');
        }

        $this->validate($request, [
            'batch_code' => Rule::unique('batch', 'batch_code')->where(function ($query) use ($warehouse) {
                return $query->where('warehouse_id', $warehouse->id);
            }),
        ]);

        $stocks = [];

        $batch_num  = 0;
        foreach ($request->product_stock as $k => $v) {
            // 检查入库单商品外部编码

            $spec = ProductSpec::ofWarehouse($warehouse->id)
                ->whose(app('auth')->id())
                ->where('relevance_code', $v['relevance_code'])
                ->first();

            if (! $spec) {
                return formatRet(1, '系统中未找到外部编码是' . $v['relevance_code'] . '的货品');
            }

            $batch_num+=$v['need_num'];
            $stocks[] = [
                'owner_id'         => Auth::id(),
                'spec_id'          => $spec->id,
                'relevance_code'   => $v['relevance_code'],
                'need_num'         => $v['need_num'],
                'pieces_num'       => $v['pieces_num'],
                'remark'           => $v['remark'],
                'distributor_id'   => $request->distributor_id,
                'distributor_code' => $v['distributor_code'],
                'warehouse_id'     => $warehouse->id,
                'batch_id'         => 0,
                'status'           => Product::PRODUCT_STATUS_PREPARE,
		'sku'	           => ProductSpec::newSku($spec),
            ];
        }

        $batch = new Batch;
        $batch->warehouse_id        = $warehouse->id;
        $batch->type_id             = $request->type_id;
        $batch->batch_code          = $request->batch_code;
        $batch->confirmation_number = $request->confirmation_number;
        $batch->distributor_id      = $request->distributor_id;
        $batch->need_num              = $batch_num;
        $batch->transportation_type = $request->transportation_type;
        $batch->waybill_number      = $request->waybill_number;
        $batch->status              = Batch::STATUS_PREPARE;
        $batch->owner_id            = Auth::id();

        if ($request->filled('plan_time')) {
            $batch->plan_time = strtotime($request->plan_time);
        }

        if ($request->filled('over_time')) {
            $batch->over_time = strtotime($request->over_time);
        }

        if ($request->filled('remark')) {
            $batch->remark = $request->remark;
        }

        app('db')->beginTransaction();
        try {
            $batch->save();

//            foreach ($stocks as $k => $v) {
//                $stocks[$k]['sku'] = $batch->batch_code . sprintf("%04d", $k + 1);
//            }


	    $batch->stocks()->createMany($stocks);
	    BatchMarkLog::saveBatchCode($batch); 
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            info('添加入库单', ['Exception msg' => $e->getMessage()]);
            return formatRet(500, '添加入库单失败');
        }

        return formatRet(0, '添加入库单成功');
    }

    /**
     * 入库单 - 删除
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'batch_id' => 'required|integer|min:1',
        ]);

        if (! $batch = Batch::whose(Auth::id())->find($request->batch_id)) {
            return formatRet(404, '入库单不存在', [], 404);
        }

        if ($batch->status !== Batch::STATUS_PREPARE) {
            return formatRet(1, '只能删除状态为‘待入库’的入库单!');
        }

        if ($batch->delete()) {
            ProductStock::where('batch_id', $request->batch_id)->delete();
            return formatRet(0, '入库单删除成功!');
        }

        return formatRet(1, '入库单删除失败!');
    }

    /**
     * 入库单 - 详情，根据入库单 ID 拉取
     *
     * @author liusen
     */
    public function retrieveById($batch_id)
    {
        $batch = Batch::with(['batchProducts', 'distributor', 'warehouse', 'batchType', 'operatorUser'])
            ->ofWarehouse(app('auth')->warehouse()->id);

        if (app('auth')->roleId() == WarehouseEmployee::ROLE_RENTER){
            $batch->whose(app('auth')->id());
        }

        $batch = $batch->findOrFail($batch_id);

        $batch->append(['batch_code_barcode']);

        return formatRet(0, '', $batch->toArray());
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

        if (! $batch = Batch::where('batch_code', $request->batch_code)->first()) {
            return formatRet(404, '单号无效');
        }

        $batch->load('batchProducts');

        return formatRet(0, '', $batch->toArray());
    }

    /**
     * 入库单货品 - 手持端扫描货品外部编码，查询详情
     *
     * @param  Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getProductByCode(Request $request)
    {
        $this->validate($request, [
            'warehouse_id'   => 'required|integer|min:1',
            'batch_id'       => 'required|integer|min:1',
            'relevance_code' => 'required|string|max:255',
        ]);

        $batchProduct = ProductStock::with('spec.product')
                                    ->where('relevance_code', $request->relevance_code)
                                    ->where('batch_id', $request->batch_id)
                                    ->first()
                                    ->toArray();

        if ($request->warehouse_id != $batchProduct['warehouse_id']) {
            return formatRet(1, '在此仓库中不存在此入库单货品');
        }

        if (! $batchProduct) {
            return formatRet(1, '查询不到此入库单货品');
        }

        return formatRet(0, '', $batchProduct);
    }

    /**
     * 入库单 - 预览
     *
     * @author liusen
     */
    public function pdf($batch_id)
    {
        if (! $batch = Batch::find($batch_id)) {
            return formatRet(404, '入库单不存在', [], 404);
        }

        $batch->load(['batchProducts', 'distributor', 'warehouse', 'batchType', 'operatorUser']);

        $batch->append('batch_code_barcode');

        if ($batch['batchProducts']) {
            foreach ($batch['batchProducts'] as $k => $v) {
                $v->append('recommended_location');
            }
        }

        return view('pdfs.batch', [
            'batch' => $batch->toArray(),
        ]);
    }

    /**
     * 入库单 - 下载
     *
     * @author liusen
     */
    public function download(Request $request, $batch_id)
    {
        // set_time_limit(0);

        // $this->validate($request, [
        //     'batch_id' => 'required|integer|min:1',
        //     'refresh'  => 'boolean',
        // ]);

        if (! $batch = Batch::find($batch_id)) {
            return formatRet(404, '入库单不存在', [], 404);
        }

        $path = $this->makePDF($request, $batch);

        if ($path === false) {
            return formatRet(500, '生成入库单PDF失败');
        }

        return response()->download($path);

        // $path = Storage::url($path);
        // $url = app('url')->to('/') . $path;
        // return formatRet(0, '', compact('url', 'path'));
    }

    protected function makePDF(Request $request, Batch $batch)
    {
        $path = storage_path('app/pdfs/batch');

        // 保存入库单PDF文件夹是否不存在，否则创建
        if (! app('files')->exists($path)) {
            app('files')->makeDirectory($path, $mode = 0755, $recursive = true, $force = false);
        }

        $token = app('auth')->getTokenForRequest();

        $view = route('batch-pdf', ['batch_id' => $batch->id]).'?api_token='.$token;// 
        $file = $batch->batch_code . '.pdf';
        $path = $path . DIRECTORY_SEPARATOR . $file;

        // 打印的入库单里竟然还要已入库数量，服了，那还判断个啥，写Job是不可能的
        // 如果文件已经存在，直接返回保存路径
        // if (app('files')->exists($path)) {
            // return $path;
            // app('files')->delete($path);
        // }

        $command = "wkhtmltopdf -O Landscape '{$view}' {$path}";

        exec($command, $out, $status);

        if ($status == 0) {
            return $path;
        } else {
            info('生成PDF出错', [
                'batch'   => $batch->toArray(),
                'command' => $command,
            ]);
            return false;
        }
    }

    /*
     *生成 -入库单号
     * */
    public function batchCode()
    { 
    	$warehouse_code = app('auth')->warehouse()->code;
	$batch_time = date('y').date('W').date('w');
	$batch_mark = BatchMarkLog::newMark($warehouse_code);
	$data = [
		'batch_code'	=> $warehouse_code.$batch_time.sprintf("%04d", $batch_mark),
	];
	return formatRet(0, '',$data);
    }

    public function generateSKU(Request $request){
	    $this->validate($request, [
	    	'product_spec_ids' => 'array|required',
	    ]);
      $warehouse = app('auth')->warehouse();
      $warehouse_code = $warehouse->code; 
      $specs = ProductSpec::ofWarehouse($warehouse->id)
//	      ->whose(app('auth')->id())
	      ->with('product')
	      ->whereIn('id',$request->product_spec_ids)
	      ->get();
      $data = [];
      $spec_ids =  $request->product_spec_ids;
      foreach($specs as $key=>$spec){
	if(empty($spec->product->category_id)){
           return formatRet(404, '不能选择该产品规格');
	}
	$data[$spec_ids[$key]] = ProductSpec::newSku($spec);
      }
      return formatRet(0, '',$data);
    }

}
