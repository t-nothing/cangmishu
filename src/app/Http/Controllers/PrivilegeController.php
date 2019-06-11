<?php
namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PrivilegeController extends Controller
{
    public  function  store(BaseRequests $requests)
    {
        $this->validate($requests,[
            'modules'                => 'required|array',
            'modules.*'              =>  [
                'required','integer','min:0',
                Rule::exists('modules','id')
            ],
            'warehouse_id'           => [
                'required','integer','min:0',
                Rule::exists('warehouse','id')->where('owner_id',Auth::ownerId())
            ],
            'group_id' =>[
                'required','integer','min:0',
                Rule::exists('groups','id')->where('user_id',Auth::ownerId())
            ],
        ]);

        app('db')->beginTransaction();
        try{
            app('group')->bindBase($requests->warehouse_id,$requests->group_id);
            app('group')->updateRelatedModules($requests->modules,$requests->group_id);
        }catch (\Exception $exception){
            app('log')->info('用户组模块更新错误', ['message' => $exception->getMessage()]);
            app('db')->rollBack();
            return eRet('分配权限失败');
        }
        app('db')->commit();
        return formatRet(0, '分配权限成功');
    }


//    public function update(BaseRequests $requests)
//    {
//        $this->validate($requests,[
//            'modules'                => 'required|array',
//            'modules.*'              =>  [
//                'required','integer','min:0',
//                Rule::exists('modules','id')
//            ],
//            'warehouse_id'           => [
//                'required','integer','min:0',
//                Rule::exists('warehouse','id')->where('owner_id',Auth::ownerId())
//            ],
//            'group_id' =>[
//                'required','integer','min:0',
//                Rule::exists('groups','id')->where('user_id',Auth::ownerId())
//            ],
//        ]);
//        app('db')->beginTransaction();
//        try{
//            app('group')->bindBase($requests->warehouse_id,$requests->group_id);
//            app('group')->updateRelatedModules($requests->modules,$requests->group_id);
//        }catch (\Exception $exception){
//            app('log')->info('用户组模块更新错误', ['message' => $exception->getMessage()]);
//            app('db')->rollBack();
//            return eRet('用户组模块更新错误');
//        }
//        app('db')->commit();
//        return formatRet(0);
//    }
}