<?php

namespace App\Http\Controllers;

use App\Models\UserGroupRel;
use App\Models\User;
use App\Rules\PageSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\BaseRequests;

/**
 * 用户员工管理
 */
class EmployeeController extends Controller
{
    public  function  __construct()
    {
        $user= Auth::user();
        if($user->boss_id !=0){
            return  formatRet(0, '没有权限操作');
        }
    }

    /**
     * 员工列表
     */
	public function index(BaseRequests $request)
	{
        $this->validate($request, [
            'page'         => 'integer|min:1',
            'page_size'    => new PageSize,
            'group_id'      => 'integer|min:1|exists:groups,id',
            'user_name'      => 'string|min:1',
            'warehouse_id'  => 'integer|min:1|exists:warehouse,id',
            'rm'            =>'integer|min:0'
        ], [
            'group_id.exists' => '未找到此分组',
            'warehouse_id.exists' => '未找到此仓库',
            'warehouse_id.required' => '请选择仓库',
        ]);
        $user = Auth::ownerId();
        $username = $request->input('user_name',"");
        $group_id = $request->input('group_id',0);
        $warehouse_id =  $request->input('warehouse_id',0);
        $rm = $request->input('rm',0);
        $data = app('employee')->getEmployeeList($user,$warehouse_id,$request->input('page_size',10), $username, $group_id,$rm);
        return formatRet(0, '', $data);
	}

    /**
     * 获取员工信息
     */
    public function show($user_id)
    {
        $user =  User::where('id',$user_id)->first();
        if(!$user){
            return formatRet(0, trans('message.employeeNotExist'));
        }
        return formatRet(0, '', $user->toArray());
    }

    /**
     * 添加员工
     */
	public function store(BaseRequests $request)
	{
		$this->validate($request, [
            'nickname'=> ['required','string','max:255','regex:/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]+$/u'],
            'name'    => 'required|string|max:255',
            'password'=>'required|max:255|confirmed',
            'password_confirmation' => 'required|max:255',
            'email'   => 'required|email|max:255',
            'remark'  => 'string|max:255',
            'phone'   => 'string|max:20',
        ],[
            'nickname.regex'=>'用户名只能包含数字、字母和下划线'
        ]);

        $boss_id = app('auth')->id();

        $user = User::where('email',$request->email)->orWhere('nickname',$request->nickname)->first();
        if($user){
            return formatRet(500,'用户已存在');
        }
        app('db')->beginTransaction();
        try {
            $user = new User;
            $user->email    = $request->email;
            $user->name     = $request->name;
            $user->nickname = $request->nickname;
            $user->password = Hash::make($request->password);
            $user->phone    = $request->input('phone',"");
            $user->boss_id  = $boss_id;
            $user->remark   = $request->input('remark',"");
            $user->is_activated  = 1;
            $user->save();
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500, '添加员工失败');
        }

        return formatRet(0);
	}

    /**
     * 更新员工信息
     */
    public function update(BaseRequests $request,$user_id)
    {

        $this->validate($request, [
            'name'    => 'required|string|max:255',
            'phone'   => 'string|max:255',
            'remark'   => 'string|max:255',
        ]);
        $user = User::find($user_id);

        if (! $user || $user->boss_id != Auth::id()){
            return formatRet(500,'用户不存在或无权限编辑');
        }
        $user->name  = $request->name;
        $user->phone = $request->phone;
        $user->remark = $request->input('remark', "");

        if (! $user->save()) {
            return formatRet(500,'失败');
        }
        return formatRet(0);
    }

    /**
     * 删除员工
     */
    public function destroy(BaseRequests $request,$user_id)
    {
       $user = User::find($user_id);
        if (! $user || $user->boss_id != Auth::id()) {
            return formatRet(500, '员工不存在或者无权删除');
        }
        app('db')->beginTransaction();
        try {
            $user->forceDelete();
            UserGroupRel::where('user_id',$user_id)->forceDelete();
            app('db')->commit();
        } catch (\Exception $e) {
            app('db')->rollback();
            return formatRet(500,'删除员工失败');
        }
        return formatRet(0);
    }

    public function lock(BaseRequests $request,$user_id){
        $this->validate($request, [
            'lock' =>'required|integer|min:0',
        ]);
        $user = User::find($user_id);
        if (! $user || $user->boss_id != Auth::id()) {
            return formatRet(500, '员工不存在或者无权操作');
        }
        $user->is_locked = $request->lock;

        if (! $user->save()) {
            return formatRet(500,'操作失败');
        }
        return formatRet(0);
    }

}
