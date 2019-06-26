<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Batch;
use App\Models\Groups;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductStock;
use App\Models\WarehouseArea;
use App\Models\WarehouseLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Warehouse;

/**
 * 仓库管理
 */
class WarehouseController extends Controller
{
    /**
     * 仓库 - 列表
     */
    public function index(BaseRequests $request)
    {
        $user_id = app('auth')->ownerId();
        $warehouses = Warehouse::where('owner_id',$user_id)->paginate($request->input('page_size',10));
        foreach ($warehouses as $wa){
          $wa->append('warehouse_address');
        }
        return formatRet(0, '', $warehouses->toArray());
    }

    /**
     * 仓库 - 新增
     */
    public function store(CreateWarehouseRequest $request)
    {
        app('log')->info('新增仓库',$request->all());
        $user_id = app('auth')->ownerId();
        $data = $request->all();
        $data = array_merge($data,['owner_id'=>$user_id]);
        try{
            Warehouse::create($data);
        }catch(\Exception $e) {
            app('log')->error('新增仓库失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '失败');
        }
        return formatRet(0, '');
    }

    /**
     * 仓库 - 修改
     */
    public function update(UpdateWarehouseRequest $request,$warehouse_id)
    {
        app('log')->info('编辑仓库',$request->all());
        $this->validate($request, [
            'name_cn' => [
                'required','string','max:255',
                Rule::unique('warehouse')->ignore($warehouse_id)
            ],
            'area' => 'required|numeric',
        ]);
        $data = $request->all();
        try{
            Warehouse::where('id',$warehouse_id)->update($data);
        }catch(\Exception $e) {
            app('log')->error('编辑仓库失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '失败');
        }
        return formatRet(0, '');
    }

    public function  destroy($warehouse_id)
    {
        app('log')->info('删除仓库',['warehouse_id'=>$warehouse_id]);
        $ownerId = Auth::ownerId();
        try{
            Warehouse::where('id',$warehouse_id)->where('owner_id',$ownerId)->delete();
            //删除商品
            Product::where('warehouse_id',$warehouse_id)->forceDelete();
            //删除规格
            ProductSpec::where('warehouse_id',$warehouse_id)->forceDelete();
            //删除货区
            WarehouseArea::where('warehouse_id',$warehouse_id)->delete();
            //删除货位
            WarehouseLocation::where('warehouse_id',$warehouse_id)->delete();
            //删除入库单
            Batch::where('warehouse_id',$warehouse_id)->forceDelete();
            //删除出库单
            Order::where('warehouse_id',$warehouse_id)->forceDelete();
            //删除库存
            ProductStock::where('warehouse_id',$warehouse_id)->forceDelete();
            //删除分组
            Groups::where('warehouse_id',$warehouse_id)->forceDelete();
            //删除order_item
            OrderItem::where('warehouse_id',$warehouse_id)->forceDelete();
        }catch(\Exception $e) {
            app('log')->error('删除仓库失败',['msg'=>$e->getMessage()]);
            return formatRet(500, '失败');
        }
    }

}
