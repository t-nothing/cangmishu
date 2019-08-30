<?php
namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateUserRequest;
use App\Mail\UserCallBackEmail;
use App\Models\User;
use App\Models\VerifyCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class UserController extends  Controller
{
    /**
     * 激活并注册
     */
    public function Register(CreateUserRequest $request)
    {
        if (strtoupper(Cache::tags(['captcha'])->get($request->captcha_key)) != strtoupper($request->captcha)) {
            return formatRet(500, '图片验证失败');
        }
        Cache::tags(['captcha'])->forget($request->captcha_key);

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

    public  function getEmailVerifyCode(BaseRequests $request)
    {
        $this->validate($request,[
            'email'=>'required|email',
            'captcha_key' =>  'required|string|min:1',
            'captcha' =>  'required|string'
        ]);

        $code = app('user')->getRandCode();
        app('user')->createUserVerifyCode($code,$request->email);
        return formatRet("0","发送成功");
    }

    public  function getSmsVerifyCode(BaseRequests $request)
    {
        $this->validate($request,[
            'mobile'        =>  'required|mobile',
            'captcha_key'   =>  'required|string|min:1',
            'captcha'       =>  'required|string'
        ]);

        $code = app('user')->getRandCode();
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


    public function callUser()
    {
        $users  = User::all();
        $logo=env("APP_URL")."/images/logo.png";
        $qrCode =env("APP_URL")."/images/qrCode.png";
        $url =env('RESET_PASSWORD_URL');
        foreach ($users as $user){
            $name = explode("@",$user->email)[0];
            $message = new UserCallBackEmail($logo,$qrCode,$url,$name);
            $message->onQueue('cangmishu_emails');
            Mail::to($user->email)->send($message);
        }
        return formatRet(0);
    }
}