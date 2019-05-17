<?php
namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends  Controller
{
    /**
     * 激活并注册
     */
    public function Register(CreateUserRequest $request)
    {
        try {
            $user = app('user')->quickRegister($request);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            return formatRet(500, $e->getMessage());
        }
        return formatRet(0, '已保存到系统', $user->toArray());
    }

    public function privilege(BaseRequests $request,$user_id)
    {
        app('log')->info('拉取用户模块',$request->all());
        $this->validate($request,[
            'warehouse_id' =>[
                'required','min:0',
                Rule::exists('warehouse','id')->where('owner_id',Auth::ownerId())
            ]
        ]);

        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet('无权修改');
        }
        if(!$user){
            return formatRet(500, "用户不存在");
        }
        $warehouse_id =$request->input('warehouse_id');
        $res= app('module')->getModulesByUser($user ,$warehouse_id);
        $res = collect($res)->pluck('id')->toArray();
        return formatRet(0, "查询成功", $res);
    }

    public function resetPassword(BaseRequests $request,$user_id){
        $this->validate($request, [
            'password'=>'required|max:255|confirmed',
            'password_confirmation' => 'required|max:255',
        ]);

        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet('无权修改');
        }
        $user->password = Hash::make($request->password);
        if (! $user->save()) {
            return formatRet(500,'操作失败');
        }

        return formatRet(0);
    }

    public function updateInfo(BaseRequests $request,$user_id){
        $this->validate($request, [
            'nickname'=>'required|string|max:255',
        ]);

        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet('无权修改');
        }
        $user->nickname = $request->nickname;
        if (! $user->save()) {
            return formatRet(500,'操作失败');
        }
        return formatRet(0);
    }

    public function avatar(BaseRequests $request,$user_id){
        $this->validate($request, [
            'avatar'=>'required|string|max:255',
        ]);

        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet('无权修改');
        }
        $user->avatar = $request->avatar;
        if (! $user->save()) {
            return formatRet(500,'操作失败');
        }
        return formatRet(0);
    }

    public function show(BaseRequests $requests,$user_id)
    {
        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet('无权查看');
        }
        return formatRet(0,'成功',$user->toArray());
    }
}