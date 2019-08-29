<?php
/**
 * 第三方APP接入
 * 
 */

namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateAppAccountRequest;
use App\Models\AppAccount;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class AppController extends  Controller
{
    /**
     * 列表
     **/
    public function index(BaseRequests $request)
    {
        app('log')->info('APP 列表',$request->all());
        $this->validate($request,[
            'warehouse_id' => [
                'required','integer','min:1',
                Rule::exists('warehouse','id')->where(function($q){
                    $q->where('owner_id',Auth::ownerId());
                })
            ],
        ]);
        $data =   AppAccount::with('warehouse')
            ->ofWarehouse($request->input('warehouse_id'))
            ->where('owner_id',Auth::ownerId())
            ->when($request->filled('created_at_b'),function ($q) use ($request){
                return $q->where('created_at', '>', strtotime($request->input('created_at_b')));
            })
            ->when($request->filled('created_at_e'),function ($q) use ($request){
                return $q->where('created_at', '<', strtotime($request->input('created_at_e')));
            })
            ->when($request->filled('is_enabled'),function ($q) use ($request){
                return $q->where('is_enabled', $request->input('is_enabled'));
            })
           ->latest()->paginate($request->input('page_size',10));
        return formatRet(0,'',$re);
    }

    /**
     * APP存储
     **/
    public function  store(CreateAppAccountRequest $request)
    {
        app('log')->info('新增APP KEY', $request->all());
        app('db')->beginTransaction();
        try{
            $model = new AppAccount;
            $model->remark = $request->remark;
            $model->app_key = Warehouse::find($request->warehouse_id)->code;
            $model->app_secret = AppAccount::generateAppSecret($request->warehouse_id, $model->app_key);
            $model->warehouse_id = $request->warehouse_id;
            $model->owner_id = $request->owner_id;
            $model->is_enabled_push = 1;
            $model->save();
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('新增APP失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"新增APP失败");
        }
    }

    /**
     * 删除KEY
     **/
    public function  destroy($id)
    {
        app('log')->info('删除APP KEY', ['id'=>$id]);

        $model = AppAccount::find($id);

        if(!$model){
            return formatRet(500,'APP KEY不存在或已被删除');
        }
        
        if($model->owner_id != Auth::ownerId()){
            return formatRet(500,'没有权限');
        }
        try{
          $model->delete();
          return formatRet(0,'success');
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('删除APP KEY失败',['msg' =>$e->getMessage()]);
            return formatRet(500,"删除APP KEY失败");
        }
    }

}