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
            $model->app_key = Warehouse::find(app('auth')->warehouse()->id)->code;
            $model->app_secret = AppAccount::generateAppSecret(app('auth')->warehouse()->id, $model->app_key);
            $model->warehouse_id = app('auth')->warehouse()->id;
            $model->owner_id = $request->owner_id;
            $model->is_enabled_push = 1;
            $model->save();
            app('db')->commit();
            return formatRet(0);
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('新增APP失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.appKeyAddFailed"));
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
            return formatRet(500, trans("message.appKeyNotExist"));
        }
        
        if($model->owner_id != Auth::ownerId()){
            return formatRet(500, trans("message.noPermission"));
        }
        try{
          $model->delete();
          return formatRet(0,'success');
        }catch (\Exception $e){
            app('db')->rollback();
            app('log')->error('删除APP KEY失败',['msg' =>$e->getMessage()]);
            return formatRet(500, trans("message.appKeyDeleteFailed"));
        }
    }

}