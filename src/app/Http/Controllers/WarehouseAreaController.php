<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateWarehouseAreaRequest;
use App\Http\Requests\UpdateWarehouseAreaRequest;
use App\Models\WarehouseArea;
use Illuminate\Support\Facades\Auth;

class WarehouseAreaController extends Controller
{
    /**
     * 获取货区列表
     */
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'is_enabled'   => 'boolean',
        ]);

        $features = WarehouseArea::ofWarehouse($request->input('warehouse_id'))
            ->whose(Auth::ownerId())
            ->when($request->filled('is_enabled'),function($query) use($request){
                $query->where('is_enabled', $request->is_enabled);
            })
            ->paginate($request->input('page_size',10));

        return formatRet(0, '', $features->toArray());
    }

    /**
     * 创建货区
     */
    public function store(CreateWarehouseAreaRequest $request)
    {
        app('log')->info('新增仓库货区', $request->all());
        try{
            $data = $request->all();
            $data = array_merge($data, ['owner_id' =>Auth::ownerId()]);
            WarehouseArea::create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('新增仓库货区失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.warehouseAreaAddFailed"));
        }
    }

    /**
     * 修改货区
     */
    public function update(UpdateWarehouseAreaRequest $request,$area_id)
    {
        app('log')->info('编辑仓库货区', ['warehouse_area_id'=>$area_id]);
        try{
            $data = $request->all();
            WarehouseArea::where('id',$area_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑仓库货区失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.warehouseAreaUpdateFailed"));
        }
    }

    /**
     * 删除货区
     */
    public function destroy($warehouse_area_id)
    {
        app('log')->info('删除仓库货区',['id'=>$warehouse_area_id]);
        $area = WarehouseArea::find($warehouse_area_id);
        if(!$area){
            return formatRet(500, trans("message.warehouseAreaNotExist"));
        }
        if ($area->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }

        $location = $area->locations()->whereHas('stock',function($q){
            return $q->where('shelf_num','>',0);
        })->get();
        if(count($location)){
            return formatRet(500,trans("message.warehouseAreaCannotDelete"));
        }

        try{
            WarehouseArea::where('id',$warehouse_area_id)->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->info('删除仓库货区失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.warehouseAreaDeleteFailed"));
        }
    }

}
