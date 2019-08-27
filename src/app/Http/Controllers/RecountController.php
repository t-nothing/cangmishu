<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateRecountRequest;
use App\Models\Recount;
use App\Models\RecountStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\PageSize;
use PDF;

class RecountController extends Controller
{
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page' => 'integer|min:1',
            'page_size' => new PageSize(),
            'created_at_b' => 'date:Y-m-d',
            'created_at_e' => 'date:Y-m-d',
            'keywords' => 'string',
            'warehouse_id' =>  [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ]
        ]);

        $batchType = Recount::with('stocks')
                            ->ofWarehouse($request->warehouse_id)
                            ->whose(Auth::ownerId())
                            ->when($request->filled('created_at_b'),function ($q) use ($request){
                                return $q->where('created_at', '>', strtotime($request->input('created_at_b')));
                            })
                            ->when($request->filled('created_at_e'),function ($q) use ($request){
                                return $q->where('created_at', '<', strtotime($request->input('created_at_e')));
                            })
                            ->when($request->filled('keywords'),function ($q) use ($request){
                                return $q->hasKeyword($request->input('keywords'));
                            })
                            ->orderby('id', 'desc')
                            ->paginate($request->input('page_size',10));
        return formatRet(0, '', $batchType->toArray());
    }

    /**
     * 创建分类
     *
     * @param Request $request
     */
    public function store(CreateRecountRequest $request)
    {
       app('log')->info('新增盘点单', $request->all());
        try{
            $data = $request->all();
            $data = array_merge($data, ['owner_id' =>Auth::ownerId()]);
            app('recount')->create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('新增盘点单失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增盘点单失败");
        }
    }

    /**
     * 盘点单详细
     **/
    public function show(BaseRequests $request, $id)
    {
        app('log')->info('查看盘点单',['id' =>$id]);
        $recount = Recount::find($id);

        if(!$recount){
            return formatRet(500,"盘点单不存在");
        }
        if ($recount->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }

        $recount->load('stocks');

        return formatRet(0, '', $recount->toArray());
    }

    /**
     * 删除盘点单
     **/
    public function destroy($id)
    {
        return false;//不开放
    }

    public function pdf($id, $template = '')
    {
        $recount = Recount::with(['warehouse', 'operatorUser'])->find($id);

        if(!$recount){
            return formatRet(500,"盘点单不存在");
        }
        if ($recount->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }
        $recount->append(['recount_no_barcode']);
        $recount->load('stocks');

        // $template = "pdfs.recount.template_".strtolower($template);
        // if(!in_array(strtolower($template), ['entry','purchase','batchno'])){
        //     $template = "pdfs.recount";
        // }

        $template = "pdfs.recount";
        return view($template, [
            'data' => $recount->toArray(),
        ]);
    }

    /**下载PDF**/
    public function download(BaseRequests $request, $id, $template = '')
    {

        $recount = Recount::with(['warehouse', 'operatorUser'])->find($id);

        if(!$recount){
            return formatRet(500,"盘点单不存在");
        }
        if ($recount->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }

        $recount->load('stocks');

        $template = "pdfs.recount";
        $recount->append(['recount_no_barcode']);

        $pdf = PDF::setPaper('a4', 'Landscape');


        $file = $recount->recount_no . '{$template}.pdf';

        return $pdf->loadView($template, ['data' => $recount->toArray()])->download($file);

    }
}