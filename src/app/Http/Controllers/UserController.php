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
    public function register(CreateUserRequest $request)
    {

        $type       = $request->type;
        $code       = $request->code;
        $mobile     = $request->mobile;
        $email      = $request->email;

        $codeFieldValue = $email;
        if($type === "mobile")
        {
            $codeFieldValue = $mobile;
        }

        if($type == "wechat") {
            if (Cache::tags(['wechat'])->has($code)) {
                $data = Cache::tags(['wechat'])->get($code);
                if(!$data['is_valid']) {
                    return formatRet(500, trans("message.userRegisterExpired"));
                }

                if($data['user_id'] >0) {
                    return formatRet(500, trans("message.userBindRepeat"));
                }

                //生成一个随机邮箱
                //{"subscribe":1,"openid":"o8kADjzoCmeTkNfQpMG1o7bZ-R8w","nickname":"胡斌杰","sex":1,"language":"zh_CN","city":"长沙","province":"湖南","country":"中国","headimgurl":"http://thirdwx.qlogo.cn/mmopen/Q3auHgzwzM7JjWb1gibEs5kvAKAgyjic08eKDa74RaIMbX08V8rlNaWskBZNF0sRnOhyQWrMnENgmMrOAV9noXYQ/132","subscribe_time":1568025988,"unionid":"osj6R5-kslcC3x03-K1Kf-FthFMM","remark":"","groupid":0,"tagid_list":[],"subscribe_scene":"ADD_SCENE_QR_CODE","qr_scene":0,"qr_scene_str":"44d3b27b9bbd013afc9269206f415b5e"}
                $request->merge([
                    'email'         =>  sprintf("%s_%s@cangmishu.com", time(), app('user')->getRandCode()),
                    'province'      =>  $data['wechat_user']['province']??'',
                    'country'       =>  $data['wechat_user']['country']??'',
                    'city'          =>  $data['wechat_user']['city']??'',
                    'avatar'        =>  $data['wechat_user']['headimgurl']??'',
                    'nickname'      =>  $data['wechat_user']['nickname']??'',
                    'wechat_openid' =>  $data['open_id']??'',
                ]);//合并参数
                
            }
        } else {
            $verify_code = VerifyCode::where('code',$code)->where('email',$codeFieldValue)->where('expired_at','>',time())->first();
            if(!$verify_code){
                return formatRet(500, trans("message.userRegisterExpired"));
            }
        }
        try {
            $user = app('user')->quickRegister($request);
        } catch (\Exception $e) {
            app('log')->error($e->getMessage());
            return formatRet(500, $e->getMessage());
        }

        return formatRet(0, trans("message.userRegisterSuccess"), $user->toArray());
    }

    public  function getEmailVerifyCode(BaseRequests $request)
    {
        $this->validate($request,[
            'email'         =>['required','email',Rule::unique('user','email')],
            'captcha_key'   =>  'required|string|min:1',
            'captcha'       =>  'required|string'
        ]);

        if (strtoupper(Cache::get(trim($request->captcha_key))) != strtoupper(trim($request->captcha))) {
            app('log')->info('验证码错误', [
                'cache'=>Cache::get($request->captcha_key),
                'request'=>$request->captcha,
            ]);
            return formatRet(500, trans("message.userRegisterEmailVerifyCodeFailed"));
        }
        Cache::forget($request->captcha_key);

        $code = app('user')->getRandCode();
        app('user')->createUserEmailVerifyCode($code,$request->email);
        return formatRet(0, trans("message.userRegisterEmailVerifyCodeFailed"));
    }

    public  function getSmsVerifyCode(BaseRequests $request)
    {
        $this->validate($request,[
            'mobile'        =>  ['required','mobile',Rule::unique('user','phone')],
            'captcha_key'   =>  'required|string|min:1',
            'captcha'       =>  'required|string'
        ]);

        if($request->captcha_key != "app") {
            if (strtoupper(Cache::get($request->captcha_key)) != strtoupper($request->captcha)) {
                return formatRet(500, trans("message.userRegisterEmailVerifyCodeFailed"));
            }
            Cache::forget($request->captcha_key);
        }

        $code = app('user')->getRandCode();
        app('user')->createUserSMSVerifyCode($code,$request->mobile);
        return formatRet(0, trans("message.userRegisterSendSuccess"));

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
            return formatRet(500, trans("message.noPermission"));
        }
        if(!$user){

            return formatRet(500, trans("message.userNotExist"));
        }
        $warehouse_id =$request->input('warehouse_id');
        $res= app('module')->getModulesByUser($user ,$warehouse_id);
        $res = collect($res)->pluck('id')->toArray();
        return formatRet(0, trans("message.success"), $res);
    }

    /**
     * 重设密码
     */
    public function resetPassword(BaseRequests $request,$user_id){
        $this->validate($request, [
            'password'=>'required|max:255|confirmed',
            'password_confirmation' => 'required|max:255',
        ]);
        app('log')->info('user',['request'=>$request->all(),'user_id'=>$user_id]);
        $user = User::find($user_id);
        if(!$user){
            return formatRet(500, trans("message.userNotExist"));
        }
        $auth = Auth::user();
        if($user->boss_id !=0){
            if($user->boss_id !=$auth->id){
                return formatRet(500, trans("message.noPermission"));
            }
        }else{
            if($user->id != $auth->id){
                return formatRet(500, trans("message.noPermission"));
            }
        }

        $user->password = Hash::make($request->password);
        if (! $user->save()) {
            return formatRet(500, trans("message.failed"));
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
            return formatRet(500, trans("message.noPermission"));
        }
        $user->nickname = $request->nickname;
//        $user->avatar = $request->avatar;
        if (! $user->save()) {
            return formatRet(500, trans("message.failed"));
        }
        return formatRet(0);
    }

    public function avatar(BaseRequests $request,$user_id){
        $this->validate($request, [
            'avatar'=>'required|string|max:255',
        ]);

        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet(500, trans("message.noPermission"));
        }
        $user->avatar = $request->avatar;
        if (! $user->save()) {
            return formatRet(500, trans("message.failed"));
        }
        return formatRet(0);
    }

    public function show(BaseRequests $requests,$user_id)
    {
        $user = User::find($user_id);
        if($user_id != Auth::id()){
            return formatRet(500, trans("message.noPermission"));
        }
        return formatRet(0,trans("message.success"),$user->toArray());
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