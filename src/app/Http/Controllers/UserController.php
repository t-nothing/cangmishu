<?php
namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateUserRequest;
use App\Models\User;
use App\Models\VerifyCode;
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
        $code = $request->input('code');
        $email = $request->input('email');
        $verify_code = VerifyCode::where('code',$code)->where('email',$email)->where('expired_at','>',time())->first();
        if(!$verify_code){
            return formatRet(500, "验证码已过期或不存在");
        }
        try {
            $user = app('user')->quickRegister($request);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            return formatRet(500, $e->getMessage());
        }
        return formatRet(0, '已保存到系统', $user->toArray());
    }

    public  function getCode(BaseRequests $request)
    {
        $this->validate($request,[
            'email'=>'required|email'
        ]);

        $code = app('user')->getCode();
        app('user')->createUserVerifyCode($code,$request->email);
        return formatRet("0","发送成功");

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
        app('log')->info('user',['request'=>$request->all(),'user_id'=>$user_id]);
        $user = User::find($user_id);
        if(!$user){
            return formatRet(500,"请选择用户");
        }
        app('log')->info('user',$user->toArray());
        $auth = Auth::user();
        if($user->boss_id !=0){
            if($user->boss_id !=$auth->id){
                return formatRet(500,"无权修改");
            }
        }else{
            if($user->id != $auth->id){
                return formatRet(500,"无权修改");
            }
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
//            'avatar'=>'required|string|max:255',
        ]);

        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet('无权修改');
        }
        $user->nickname = $request->nickname;
//        $user->avatar = $request->avatar;
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