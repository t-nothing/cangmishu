<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateWarehouseFeatureRequest;
use App\Http\Requests\UpdateWarehouseFeatureRequest;
use App\Models\WarehouseFeature;
use Illuminate\Support\Facades\Auth;

class WarehouseFeatureController extends Controller
{
    public function index(BaseRequests $request)
    {
        $this->validate($request, [
            'is_enabled'   => 'boolean',
        ]);
        $features = WarehouseFeature::where('owner_id',Auth::ownerId())
                    ->when($request->filled('is_enabled'),function($query) use($request){
                        $query->where('is_enabled', $request->is_enabled);
                    })
                    ->paginate($request->input('page_size',10));
        return formatRet(0, '', $features->toArray());
    }

    public function store(CreateWarehouseFeatureRequest $request)
    {
        app('log')->info('新增仓库特性', $request->all());
        try{
            $data = $request->all();
            $data = array_merge($data, ['owner_id' =>Auth::ownerId()]);
            WarehouseFeature::create($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('新增仓库特性失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增仓库特性失败");
        }
    }

    public function update(UpdateWarehouseFeatureRequest $request,$warehouse_feature_id)
    {
        app('log')->info('编辑仓库特性', ['warehouse_feature_id'=>$warehouse_feature_id]);
        try{
            $data = $request->all();
            WarehouseFeature::where('id',$warehouse_feature_id)->update($data);
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('编辑仓库特性失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"编辑仓库特性失败");
        }
    }

    public function destroy($warehouse_feature_id)
    {
        app('log')->info('删除仓库特性',['id'=>$warehouse_feature_id]);
        $feature = WarehouseFeature::find($warehouse_feature_id);
        if(!$feature){
            return formatRet(500,"仓库特性不存在");
        }
        if ($feature->owner_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }
        try{
            WarehouseFeature::where('id',$warehouse_feature_id)->delete();
            return formatRet(0);
        }catch (\Exception $e){
            app('log')->error('删除仓库特性失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"删除仓库特性失败");
        }
    }
}
