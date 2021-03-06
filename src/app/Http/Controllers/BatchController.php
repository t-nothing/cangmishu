<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateBatchRequest;
use App\Http\Requests\CreateShelfRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\ProductStock;
use App\Models\BatchMarkLog;
use App\Models\ProductSpec;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Exceptions\LocationException;
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
            ->ofWarehouse(app('auth')->warehouse()->id)
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
            $data["warehouse_id"] = app('auth')->warehouse()->id;;
            $batch = app('batch')->create($data);
            app('db')->commit();
            return formatRet(0, '', $batch->toArray());
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('新增入库单失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans('message.batchAddFailed'));
        }
    }

    /**
     * 修改入库单
     **/
    public function  update(UpdateBatchRequest $request,$batch_id)
    {
        app('log')->info('修改入库单', $request->all());

        $batch = Batch::find($batch_id);
        if($batch->status != 1){
            return formatRet(500, trans('message.batchCannotEdit'));
        }
        app('db')->beginTransaction();
        try{
            $data = $request->all();
            app('batch')->update($data,$batch);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('修改入库单失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans('message.batchUpdateFailed'));
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
            return formatRet(500, trans('message.batchNotExist')); //'入库单不存在或已被删除'
        }
        if($batch->status != Batch::STATUS_PREPARE ){
            return formatRet(500, trans('message.batchCannotDelete'));//'不允许删除'
        }
        if($batch->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
        }
        try{
          $batch->delete();
          ProductStock::where('batch_id',$batch_id)->delete();
          return formatRet(0,'success');
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('删除入库单失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchDeleteFailed"));//"删除入库单失败"
        }
    }


    /**
     * 入库上架
     */
    public function shelf(CreateShelfRequest $request)
    {
        $autoCreateLocation = $request->auto_create_location??0;
        app('log')->info('入库上架', $request->all());
        app('db')->beginTransaction();
        try{
            $data = $request->stock;
            $res = app('store')->InAndPutOn(app('auth')->warehouse()->id,$data,$request->batch_id, $autoCreateLocation);
            app('db')->commit();
            return formatRet(0);
        }catch(LocationException $e) {
            app('db')->rollback();
            app('log')->error('货位不存在',['msg' =>$e->getMessage()]);
            return formatRet(404, $e->getMessage(), $e->getLocations());
        }
        catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('入库上架失败',['msg' =>$e->getMessage()]);
            //"入库上架失败:".$e->getMessage()
            return formatRet(500, trans("message.batchOnshelfFailed"));
        }
    }


    public function pdf($batch_id, $template = '')
    {
        if (! $batch = Batch::find($batch_id)) {
            return formatRet(404, trans('message.batchNotExist'), [], 404);
        }

        if($batch->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
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
            return formatRet(404, trans('message.batchNotExist'), [], 404);
        }

        if($batch->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
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

        $fileName = sprintf("%s_%s_%s.pdf", $batch->id, template_download_name($templateName, "en"), md5($batch->id.$batch->created_at));

        $filePath = sprintf("%s/%s", storage_path('app/public/pdfs/'), $fileName);
        if(!file_exists($filePath)) {

            if($templateName == "pdfs.batch.template_batchno" )
            {
                $pdf->setOption('page-width', '70')->setOption('page-height', '50')->setOption('margin-left', '0')->setOption('margin-right', '0')->setOption('margin-top', '5')->setOption('margin-bottom', '0');
            }


            $pdf->loadView($templateName, ['batch' => $batch->toArray(), 'showInStock'=>0])->save($filePath);
        }

        if($request->filled("require_url") && $request->require_url == 1) {

            $url = asset('storage/pdfs/'.$fileName);
            return formatRet(0,trans("message.success"), ["url"=>$url]);
        }

        return response()->download($filePath, $fileName);

    }

    public function show(BaseRequests $request, $id)
    {
        app('log')->info('查询入库单详情',$request->all());
        // $this->validate($request,[
        //     'warehouse_id' => [
        //         'required','integer','min:1'
        //     ]
        // ]);

        $batch = Batch::where('warehouse_id',app('auth')->warehouse()->id)->where('owner_id',Auth::ownerId())
            ->with([
                'warehouse:id,name_cn',
                'batchType:id,name',
                'distributor:id,name_cn,name_en',
                'batchProducts.spec.product.category'
            ])
            ->where('id',$id)
            ->first();
        if(!$batch){
            return formatRet(500, trans('message.batchNotExist'));
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
                $data['batch_products'][$k]['expiration_date'] = $v['expiration_date'] ? Carbon::parse(
                    $v['expiration_date'],
                    new \DateTimeZone('Asia/Shanghai')
                )->toDateString() : null;
                $data['batch_products'][$k]['best_before_date'] = $v['best_before_date'] ? Carbon::parse(
                    $v['best_before_date'],
                    new \DateTimeZone('Asia/Shanghai')
                )->toDateString() : null;

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
