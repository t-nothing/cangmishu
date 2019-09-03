<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateBatchRequest;
use App\Http\Requests\CreateShelfRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\ProductStock;
use App\Models\BatchMarkLog;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use PDF;

class BatchController extends Controller
{
    /**
     * 入库单首页
     **/
    public function index(BaseRequests $request)
    {
        app('log')->info('查询入库单',$request->all());
        $this->validate($request,[
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
            'type_id'           => 'integer|min:1',
            'status'            => 'integer|min:1',
            'created_at_b'      => 'date_format:Y-m-d',
            'created_at_e'      => 'date_format:Y-m-d|after_or_equal:created_at_b',
            'keywords'          => 'string|max:255',
            'distributor_id'    => 'integer',
        ]);
        $batchs =   Batch::with([
            'warehouse:id,name_cn',
            'batchType:id,name',
            'distributor:id,name_cn,name_en',
        ])
            ->ofWarehouse($request->input('warehouse_id'))
            ->where('owner_id',Auth::ownerId())
            ->when($request->filled('created_at_b'),function ($q) use ($request){
                return $q->where('created_at', '>', strtotime($request->input('created_at_b')));
            })
            ->when($request->filled('created_at_e'),function ($q) use ($request){
                return $q->where('created_at', '<', strtotime($request->input('created_at_e')));
            })
            ->when($request->filled('status'),function ($q) use ($request){
                return $q->where('status', $request->input('status'));
            })
            ->when($request->filled('keywords'),function ($q) use ($request){
                return $q->hasKeyword($request->input('keywords'));
            })
            ->when($request->filled('distributor_id'),function ($q) use ($request){
                return $q->where('distributor_id', $request->input('distributor_id'));
            })
            ->when($request->filled('type_id'),function ($q) use ($request){
                return $q->where('type_id', $request->input('type_id'));
            })
           ->latest()->paginate($request->input('page_size',10));

            $re = $batchs->toArray();

//
            $data = collect($re['data'])->map(function($v){
                unset($v['batch_products']);
                return $v;
            })->toArray();
            $re['data'] = $data;
        return formatRet(0,'',$re);
    }

    /**
     * 入库单存储
     **/
    public function  store(CreateBatchRequest $request)
    {
        app('log')->info('新增入库单', $request->all());
        app('db')->beginTransaction();
        try{
            $data = $request->all();
            app('batch')->create($data);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('新增入库单失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增入库单失败");
        }
    }

    /**
     * 修改入库单
     **/
    public function  update(UpdateBatchRequest $request,$batch_id)
    {
        app('log')->info('新增入库单', $request->all());

        $batch = Batch::find($batch_id);
        if($batch->status != 1){
            return formatRet(500,'该入库单不可编辑');
        }
        app('db')->beginTransaction();
        try{
            $data = $request->all();
            app('batch')->update($data,$batch);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('新增入库单失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增入库单失败");
        }
    }

    /**
     * 删除入库单
     **/
    public function  destroy($batch_id)
    {
        app('log')->info('删除入库单', ['id'=>$batch_id]);

        $batch = Batch::find($batch_id);

        if(!$batch){
            return formatRet(500,'入库单不存在或已被删除');
        }
        if($batch->status != Batch::STATUS_PREPARE ){
            return formatRet(500,'不允许删除');
        }
        if($batch->owner_id != Auth::ownerId()){
            return formatRet(500,'没有权限');
        }
        try{
          $batch->delete();
          ProductStock::where('batch_id',$batch_id)->delete();
          return formatRet(0,'success');
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('删除入库单失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"删除入库单失败");
        }
    }


    /**
     * 入库上架
     */
    public function shelf(CreateShelfRequest $request)
    {
        app('log')->info('入库上架', $request->all());
        app('db')->beginTransaction();
        try{
            $data = $request->stock;
            $res = app('store')->InAndPutOn($request->warehouse_id,$data,$request->batch_id);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('入库上架失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"入库上架失败:".$e->getMessage());
        }
    }


    public function pdf($batch_id, $template = '')
    {
        if (! $batch = Batch::find($batch_id)) {
            return formatRet(404, '入库单不存在', [], 404);
        }

        if($batch->owner_id != Auth::ownerId()){
            return formatRet(500,'没有权限');
        }

        $batch->load(['batchProducts', 'distributor', 'warehouse', 'batchType', 'operatorUser']);

        $batch->append('batch_code_barcode');

        if ($batch['batchProducts']) {
            foreach ($batch['batchProducts'] as $k => $v) {
                $v->append('recommended_location');
                $v->append('sku_barcode');
            }
        }

        
        app('log')->info('template', [strtolower($template)]);
        $templateName = "pdfs.batch.template_".strtolower($template);
        if(!in_array(strtolower($template), ['entry','purchase','batchno'])){
            $templateName = "pdfs.batch";
        }
        app('log')->info('template', [strtolower($template)]);
        return view($templateName, [
            'batch' => $batch->toArray(),
            'showInStock'=>1
        ]);
    }

    /**下载PDF**/
    public function download(BaseRequests $request, $batch_id, $template = '')
    {

        if (! $batch = Batch::where('owner_id',Auth::ownerId())->find($batch_id)) {
            return formatRet(404, '入库单不存在或者没有权限操作', [], 404);
        }

        if($batch->owner_id != Auth::ownerId()){
            return formatRet(500,'没有权限');
        }

        $batch->load(['batchProducts', 'distributor', 'warehouse', 'batchType', 'operatorUser']);

        $batch->append('batch_code_barcode');

        if ($batch['batchProducts']) {
            foreach ($batch['batchProducts'] as $k => $v) {
                $v->append('recommended_location');
                $v->append('sku_barcode');
            }
        }

        $templateName = "pdfs.batch.template_".strtolower($template);
        if(!in_array(strtolower($template), [ 'entry','purchase','batchno'])){
            $templateName = "pdfs.batch";
        }


        $pdf = PDF::setPaper('a4');

        if($templateName == "pdfs.batch.template_batchno" )
        {
            $pdf->setOption('page-width', '70')->setOption('page-height', '50')->setOption('margin-left', '0')->setOption('margin-right', '0')->setOption('margin-top', '5')->setOption('margin-bottom', '0');
        }

        $file = sprintf("%s_%s.pdf", $batch->batch_code, template_download_name($templateName));

        return $pdf->loadView($templateName, ['batch' => $batch->toArray(), 'showInStock'=>0])->download($file);

    }

    public function show(BaseRequests $request, $id)
    {
        app('log')->info('查询入库单详情',$request->all());
        $this->validate($request,[
            'warehouse_id' => [
                'required','integer','min:1'
            ]
        ]);

        $batch = Batch::where('warehouse_id',$request->warehouse_id)->where('owner_id',Auth::ownerId())
            ->with([
                'warehouse:id,name_cn',
                'batchType:id,name',
                'distributor:id,name_cn,name_en',
                'batchProducts.spec.product.category'
            ])
            ->where('id',$id)
            ->first();
        if(!$batch){
            return formatRet(500,"入库单不存在");
        }
        $batch->append(['batch_code_barcode']);

        $data = $batch->toArray();
        if($data)
        {
            $model = new ProductSpec;
            foreach ($data['batch_products'] as $k => $v) {
            
                $model->product = $v['spec']['product'];
                $model->name_cn = $v['spec']['name_cn'];
                $model->name_en = $v['spec']['name_en'];
                $data['batch_products'][$k]['spec']['product_name'] = $model->product_name;


                $data['batch_products'][$k]['need_production_batch_number'] = $v['spec']['product']['category']['need_production_batch_number'];
                $data['batch_products'][$k]['need_expiration_date'] = $v['spec']['product']['category']['need_expiration_date'];
                $data['batch_products'][$k]['need_best_before_date'] = $v['spec']['product']['category']['need_best_before_date'];
            }
        }

        unset($batch['batch_products']);
        return formatRet(0,"成功",$data);
    }

    /*
     * 生成 -入库单号
     * */
    public function batchCode()
    { 
        $warehouse_code = app('auth')->warehouse()->code;
        $batch_time = date('y').date('W').date('w');
        $batch_mark = BatchMarkLog::newMark($warehouse_code);
        $data = [
            'batch_code'    => $warehouse_code.$batch_time.sprintf("%04d", $batch_mark),
        ];
        return formatRet(0, '',$data);
    }

}
