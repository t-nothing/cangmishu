<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateOrderTypeRequest;
use App\Http\Requests\UpdateOrderTypeRequest;
use App\Models\OrderType;
use Illuminate\Support\Facades\Auth;

class OrderTypeController extends Controller
{
    /**
     * 获取列表
     */
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'page'         => 'integer|min:1',
            'is_enabled'   => 'boolean',
        ]);
        $orderTypes = OrderType::ofWhose(Auth::ownerId())
                              ->ofWarehouse($request->warehouse_id)  
                              ->when($request->filled('is_enabled'),function($q)use($request) {
                                  $q->where('is_enabled', $request->is_enabled);
                              })
                              ->paginate($request->input('page_size',10));
        return formatRet(0, '', $orderTypes->toArray());
    }


    public function store(CreateOrderTypeRequest $request)
    {
        app('log')->info('新增出库单分类', $request->all());
        try{
            $data = $request->all();
            $data = array_merge($data, ['owner_id' =>Auth::ownerId()]);
            OrderType::create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('新增出库单分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.orderTypeAddFailed"));
        }
    }

    /**
     * 修改分类
     */
    public function update(UpdateOrderTypeRequest $request,$order_type_id)
    {
        app('log')->info('编辑出库单分类', $request->all());
        try{
            $data = $request->all();
            OrderType::where('id',$order_type_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑出库单分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.orderTypeUpdateFailed"));
        }
    }

    public function destroy($order_type_id)
    {
        app('log')->info('删除出库单分类',['id'=>$order_type_id]);
        $type = OrderType::find($order_type_id);
        if(!$type){
            return formatRet(500, trans("message.orderTypeNotExist"));
        }
        if ($type->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        $count = $type->orders->count();
        if($count >0){
            return formatRet(500, trans("message.orderTypeCannotDelete"));
        }
        try{
            OrderType::where('id',$order_type_id)->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->info('删除出库单分类失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.orderTypeDeleteFailed"));
        }
    }
}
