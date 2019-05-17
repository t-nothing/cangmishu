<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateWarehouseLocationRequest;
use App\Http\Requests\UpdateWarehouseLocationRequest;
use App\Models\ProductStock;
use App\Models\WarehouseLocation;
use Illuminate\Support\Facades\Auth;

class WarehouseLocationController extends Controller
{
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'warehouse_id' => 'required|integer|min:1',
            'is_enabled'   => 'boolean',
        ]);

        $features = WarehouseLocation::ofWarehouse($request->input('warehouse_id'))
            ->where('owner_id',Auth::ownerId())
            ->when($request->filled('is_enabled'),function($query) use($request){
                $query->where('is_enabled', $request->is_enabled);
            })
            ->paginate($request->input('page_size',10));

        return formatRet(0, '', $features->toArray());
    }

    public function store(CreateWarehouseLocationRequest $request)
    {
        app('log')->info('新增仓库货位', $request->all());
        try{
            $data = $request->all();
            $data = array_merge($data, ['owner_id' =>Auth::ownerId()]);
            WarehouseLocation::create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('新增仓库货位失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增仓库货位失败");
        }
    }

    public function update( UpdateWarehouseLocationRequest $request,$warehouse_location_id)
    {
        app('log')->info('编辑仓库货位', ['warehouse_location_id'=>$warehouse_location_id]);
        try{
            $data = $request->all();
            WarehouseLocation::where('id',$warehouse_location_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑仓库货位失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"编辑仓库货位失败");
        }
    }

    public function destroy($warehouse_location_id)
    {
        app('log')->info('删除仓库货位',['id'=>$warehouse_location_id]);
        $area = WarehouseLocation::find($warehouse_location_id);
        if(!$area){
            return formatRet(500,"仓库货位不存在");
        }
        if ($area->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }

        $stocks = ProductStock::where('warehouse_location_id',$warehouse_location_id)->where('shelf_num','>',0)->get();
        if(count($stocks)){
            return formatRet(500,"此货区下有货品存在，不能删除");
        }
        try{
            WarehouseLocation::where('id',$warehouse_location_id)->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('删除仓库货位失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"删除仓库货位失败");
        }
    }
}
