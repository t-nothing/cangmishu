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
use App\Http\Requests\CreateBatchTypeRequest;
use App\Http\Requests\EditBatchTypeRequest;
use App\Models\BatchType;
use Illuminate\Support\Facades\Auth;


class BatchTypeController extends Controller
{
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'is_enabled'   => 'boolean'
        ]);

        $batchType = BatchType::with('warehouseArea')
                              ->ofWhose(Auth::ownerId())
                              ->ofWarehouse(app('auth')->warehouse()->id)
                              ->when($request->filled('is_enabled'),function($q)use($request) {
                                    $q->where('is_enabled', $request->is_enabled);
                              })
                              ->paginate($request->input('page_size',10));
        return formatRet(0, '', $batchType->toArray());
    }

    /**
     * 创建分类
     *
     * @param Request $request
     */
    public function store(CreateBatchTypeRequest $request)
    {
       app('log')->info('新增入库单分类', $request->all());
        try{
            $data = $request->all();
            $data = array_merge($data, [
              'owner_id' =>Auth::ownerId(), 
              'warehouse_id'=>app('auth')->warehouse()->id
            ]);
            BatchType::create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('新增入库单分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchTypeAddFailed"));
        }
    }

    /**
     * 修改分类
     *
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(EditBatchTypeRequest $request,$batch_type_id)
    {
        app('log')->info('编辑入库单分类', $request->all());
        try{
            $data = $request->all();
            BatchType::where('id',$batch_type_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑入库单分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchTypeUpdateFailed"));
        }
    }

    public function destroy($batch_type_id)
    {
        app('log')->info('删除入库单分类',['id' =>$batch_type_id]);
        $batch = BatchType::find($batch_type_id);

        if(!$batch){
            return formatRet(500, trans("message.batchTypeNotExist"));
        }
        if ($batch->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        $count = $batch->batches->count();
        if($count >0){
            return formatRet(500, trans("message.batchTypeCannotDelete"));
        }
        try{
            BatchType::where('id',$batch_type_id)->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('删除入库单分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.batchTypeDeleteFailed"));
        }
    }

    /**
     * 供应商 - 查看
     *
     * @author liusen
     */
    public function show( BaseRequests $request,$id)
    {
        $id = intval($id);
        $batchType = BatchType::find($id);
        if(!$batchType){
            return formatRet(404, trans("message.batchTypeNotExist"), [], 404);
        }

        if ($batchType->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        

        return formatRet(0, '', $batchType->toArray());
       
    }
}
