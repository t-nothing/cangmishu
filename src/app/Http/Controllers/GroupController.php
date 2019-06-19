<?php
/**
 * Created by PhpStorm.
 * User: NLE-Tech
 * Date: 2019/5/6
 * Time: 14:19
 */

namespace App\Http\Controllers;


use App\Http\Requests\BaseRequests;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Groups;
use App\Models\Modules;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GroupController extends  Controller
{
    protected  $user;
    public  function __construct()
    {
        $this->user = Auth::user();
        if($this->user->boss_id !=0){
            return formatRet(500,'没有权限');
        }
    }
    public function index(BaseRequests $request)
    {
        $groups =  app('group')->getGroupsByUser($request->input('page_size',10), $this->user);
        return formatRet(0,"",$groups);
    }

    public function show($id)
    {
        $group = Groups::where('user_id',$this->user->id)->find($id);
        if(!$group){
            return formatRet(500,'未找到分组');
        }

        $modules = $group->modules()->pluck('modules.id')->toArray();
        $group->load(['warehouse:id,name_cn,name_en']);
        $group = $group->toArray();
        unset($group['modules']);
        $group['module'] = $modules;

        //授权用户的权限
        $authorize =[];

        $authorize['modules']= Modules::get(['id','name']);

        $authorize['warehouse'] = Warehouse::where('owner_id',$this->user->id)->get(['id','name_cn','name_en']);

        return formatRet(0,"",compact('group', 'authorize'));
    }

    public function store(BaseRequests $request)
    {
        $this->validate($request, [
            'name'        => ['required','string',
                Rule::unique('groups','name')->where(function($query){
                    return $query->where('user_id',Auth::ownerId());
                })],
            'remark'              => 'string'
        ], ['name.unique' => '此分组已存在']);
        app('db')->beginTransaction();
        try{
            $remark = $request->input('remark',"");
            app('group')->createGroup($request->name,$this->user->id,$remark);
        }catch (\Exception $e){
            app('log')->error('创建用户组失败', ['message' => $e->getMessage()]);
            app('db')->rollBack();
            return eRet('创建用户组失败');
        }
        app('db')->commit();
        return formatRet(0,'创建用户组成功');
    }

    public function update(UpdateGroupRequest $request,$group_id)
    {
        app('db')->beginTransaction();
        try{
            app('group')->updateAttribute($group_id, $request->name,$request->input('remark',""));
        }catch (\Exception $exception){
            app('log')->error('用户组修改失败', ['message' => $exception->getMessage()]);
            app('db')->rollBack();
            return eRet('用户组修改失败');
        }
        app('db')->commit();
        return formatRet(0, '用户组修改成功');
    }

    public function destroy(BaseRequests $request,$group_id)
    {
        app('db')->beginTransaction();
        $group = Groups::find($group_id);
        if(!$group){
            return formatRet(500,"货品分类不存在");
        }
        if ($group->user_id != Auth::ownerId()){
            return formatRet(500,"没有权限");
        }
        try{
            app('group')->deleteGroup($request->group_id);
        }catch (\Exception $exception){
            app('log')->info('用户组删除错误', ['message' => $exception->getMessage()]);
            app('db')->rollBack();
            return eRet(trans('message.failed'));
        }
        app('db')->commit();
        return formatRet(0);
    }

}