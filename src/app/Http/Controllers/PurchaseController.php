<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreatePurchaseRequest;
use App\Http\Requests\UpdatePurchaseItemRequest;
use App\Http\Requests\UpdatePurchaseRequest;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseItemLog;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use PDF;

class PurchaseController extends Controller
{
    /**
     * 采购单首页
     **/
    public function index(BaseRequests $request)
    {
        app('log')->info('查询采购单',$request->all());
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
        $batchs =   Purchase::with([
            'warehouse:id,name_cn',
            'distributor:id,name_cn,name_en',
            'items'
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
     * 采购单存储
     **/
    public function  store(CreatePurchaseRequest $request)
    {
        app('log')->info('新增采购单', $request->all());
        app('db')->beginTransaction();
        try{
            $data = $request->all();
            app('purchase')->create($data);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('新增采购单失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans('message.batchPurcharseAddFailed'));
        }
    }

    /**
     * 修改采购单
     **/
    public function  update(UpdatePurchaseRequest $request,$purchase_id)
    {
        app('log')->info('修改采购单', $request->all());

        $purchase = Purchase::find($purchase_id);
        if($purchase->status != Purchase::STATUS_PREPARE){
            return formatRet(500, trans('message.batchPurcharseCannotEdit'));
        }
        app('db')->beginTransaction();
        try{
            $data = $request->all();
            app('purchase')->update($data, $purchase);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('修改采购单失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans('message.batchPurcharseUpdateFailed'));
        }
    }

    /**
     * 删除采购单
     **/
    public function  destroy($id)
    {
        app('log')->info('删除采购单', ['id'=>$id]);

        $purchase = Purchase::find($id);

        if(!$purchase){
            return formatRet(500, trans('message.batchPurchaseNotExist')); //'采购单不存在或已被删除'
        }
        if($purchase->status != Purchase::STATUS_PREPARE ){
            return formatRet(500, trans('message.batchPurchaseCannotDelete'));//'不允许删除'
        }
        if($purchase->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
        }
        try{
          $purchase->delete();
          PurchaseItem::where('purchase_id',$id)->delete();
          return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('删除采购单失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchPurchaseDeleteFailed"));//"删除采购单失败"
        }
    }



    /*
     * 完成采购单状态 -采购单号
     * */
    public function done($id)
    {
        app('log')->info('完成采购单状态', [$id]);
        

        $purchase = Purchase::find($id);

        if(!$purchase){
            return formatRet(500, trans('message.batchPurchaseNotExist')); //'采购单不存在或已被删除'
        }
        if($purchase->status != Purchase::STATUS_PREPARE ){
            return formatRet(500, trans('message.batchPurchaseUpdateFailed'));//'不允许删除'
        }
        if($purchase->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
        }

        app('db')->beginTransaction();

        try{
            app('purchase')->setDone($id);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('完成采购单状态失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchPurchaseUpdateFailed"));
        }
    }

    /*
     * 采购明细单行完成
     * */
    public function itemDone($id)
    {
        app('log')->info('采购明细单行完成', [$id]);
        

        $purchaseItem = PurchaseItem::find($id);

        if(!$purchaseItem){
            return formatRet(500, trans('message.batchPurchaseNotExist')); //'采购单不存在或已被删除'
        }
        if($purchaseItem->status != Purchase::STATUS_PREPARE ){
            return formatRet(500, trans('message.batchPurchaseUpdateFailed'));//'不允许删除'
        }
        if($purchaseItem->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
        }

        app('db')->beginTransaction();

        try{
            app('purchase')->setItemDone($id);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('完成采购单明细状态失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchPurchaseUpdateFailed"));
        }
    }


    /*
     * 采购单单行修改
     * */
    public function itemUpdate(UpdatePurchaseItemRequest $request,$id)
    {
        app('log')->info('完成采购单状态', $request->all());
        

        $purchaseItem = PurchaseItem::find($id);

        if(!$purchaseItem){
            return formatRet(500, trans('message.batchPurchaseNotExist')); //'采购单不存在或已被删除'
        }
        if($purchaseItem->status == PurchaseItem::STATUS_ACCOMPLISH ){
            return formatRet(500, trans('message.batchPurchaseUpdateFailed'));//'不允许删除'
        }
        if($purchaseItem->owner_id != Auth::ownerId()){
            return formatRet(500, trans('message.noPermission'));
        }

        app('db')->beginTransaction();

        try{
            $data = $request->all();
            app('purchase')->setItemArrived($data,$id);
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('完成采购单明细状态失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchPurchaseUpdateFailed"));
        }
    }

    /*
     * 查看PDF
     * */
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

        if($templateName == "pdfs.batch.template_batchno" )
        {
            $pdf->setOption('page-width', '70')->setOption('page-height', '50')->setOption('margin-left', '0')->setOption('margin-right', '0')->setOption('margin-top', '5')->setOption('margin-bottom', '0');
        }

        $file = sprintf("%s_%s.pdf", $batch->batch_code, template_download_name($templateName));

        return $pdf->loadView($templateName, ['batch' => $batch->toArray(), 'showInStock'=>0])->download($file);

    }

    public function show(BaseRequests $request, $id)
    {
        app('log')->info('查询采购单详情',$request->all());
        $this->validate($request,[
            'warehouse_id' => [
                'required','integer','min:1'
            ]
        ]);

        $purchase = Purchase::where('warehouse_id',$request->warehouse_id)->where('owner_id',Auth::ownerId())
            ->with([
                'warehouse:id,name_cn',
                'distributor:id,name_cn,name_en',
                'items.logs'
            ])
            ->where('id',$id)
            ->first();
        if(!$purchase){
            return formatRet(500, trans('message.batchPurchaseNotExist'));
        }

        $data = $purchase->toArray();
        
        return formatRet(0,'',$data);
    }

    public function showLogs(BaseRequests $request, $id)
    {
        app('log')->info('查询采购单日志详情',$request->all());
        $this->validate($request,[
            'warehouse_id' => [
                'required','integer','min:1'
            ]
        ]);

        $purchase = PurchaseItem::where('warehouse_id',$request->warehouse_id)->where('owner_id',Auth::ownerId())
            ->with([
                'logs'
            ])
            ->where('id',$id)
            ->first();
        if(!$purchase){
            return formatRet(500, trans('message.batchPurchaseNotExist'));
        }

        $data = $purchase->toArray();
        
        return formatRet(0,'',$data);
    }


    /*
     * 生成 -采购单号
     * */
    public function purchaseCode()
    { 
        $warehouse_code = app('auth')->warehouse()->code;
        $batch_time = date('y').date('W').date('w');
        $batch_mark = BatchMarkLog::newMark($warehouse_code);
        $data = [
            'purchase_code'    => $warehouse_code.$batch_time.sprintf("%04d", $batch_mark),
        ];
        return formatRet(0, '',$data);
    }

}