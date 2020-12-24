<?php
namespace App\Http\Controllers;
use App\Http\Requests\BaseRequests;
use App\Http\Requests\CreateUserRequest;
use App\Mail\UserCallBackEmail;
use App\Models\User;
use App\Models\VerifyCode;
use App\Services\UserService;
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

    /**
     * 获取邮箱验证码
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getEmailVerifyCode(BaseRequests $request)
    {
        $this->validate($request, [
            'email' => ['required', 'email', Rule::unique('user', 'email')],
        ]);

        $userService = new UserService();

        $code = $userService->getRandCode();
        $userService->createUserEmailVerifyCode($code, $request->email);

        return formatRet(0, "");
    }

    /**
     * 用户邮箱绑定
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindEmail(BaseRequests $request)
    {
        $request->validate([
            'code' => 'required',
            'email' => ['required', 'email', Rule::unique('user', 'email')],
        ]);

        $verifyCode = VerifyCode::where('code', $request['code'])
            ->where('email',$request['email'])
            ->where('expired_at','>', time())
            ->first();

        if(! $verifyCode){
            return formatRet(500, trans("message.userSMSExpired"));
        }

        /** @var User $user */
        $user = \auth()->user();

        $user->update([
            'email' => $request['email'],
        ]);

        return formatRet(0, trans("message.success"));
    }

    /**
     * 用户邮箱绑定
     *
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bindPhone(BaseRequests $request)
    {
        $request->validate([
            'code' => 'required',
            'phone' => ['required', 'string', Rule::unique('user', 'phone')],
        ]);

        $verifyCode = VerifyCode::where('code', $request['code'])
            ->where('email', $request['phone'])
            ->where('expired_at','>', time())
            ->first();

        if(! $verifyCode){
            return formatRet(500, trans("message.userSMSExpired"));
        }

        /** @var User $user */
        $user = \auth()->user();

        $user->update([
            'phone' => $request['phone'],
        ]);

        return formatRet(0, trans("message.success"));
    }

    /**
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public  function getSmsVerifyCode(BaseRequests $request)
    {
        $this->validate($request, [
            'mobile' => ['required', 'mobile', Rule::unique('user', 'phone')],
            'captcha_key' => 'required|string|min:1',
            'captcha' => 'required|string',
        ]);

        if ($request->captcha_key != "app") {
            if (strtoupper(Cache::tags(['captcha'])->get($request->captcha_key)) != strtoupper($request->captcha)) {
                return formatRet(500, trans("message.userRegisterEmailVerifyCodeFailed"));
            }
            Cache::forget($request->captcha_key);
        }

        $code = app('user')->getRandCode();
        app('user')->createUserSMSVerifyCode($code, $request->mobile);

        return formatRet(0, trans("message.userRegisterSendSuccess"));

    }

    /**
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public  function getPhoneVerifyCode(BaseRequests $request)
    {
        $this->validate($request, [
            'phone' => ['required', 'mobile', Rule::unique('user', 'phone')],
        ]);

        $code = app('user')->getRandCode();

        app('user')->createUserSMSVerifyCode($code, $request->phone);

        return formatRet(0, trans("message.userRegisterSendSuccess"));
    }

    public function privilege(BaseRequests $request,$user_id)
    {
        app('log')->info('拉取用户模块', $request->all());

        $this->validate($request, [
            'warehouse_id' => [
                'required', 'min:0',
                Rule::exists('warehouse', 'id')->where('owner_id', Auth::ownerId()),
            ],
        ]);

        $user = User::find($user_id);

        if ($user_id != Auth::id()) {
            return formatRet(500, trans("message.noPermission"));
        }

        if ( ! $user) {

            return formatRet(500, trans("message.userNotExist"));
        }

        $warehouse_id = $request->input('warehouse_id');
        $res = app('module')->getModulesByUser($user, $warehouse_id);
        $res = collect($res)->pluck('id')->toArray();

        return formatRet(0, trans("message.success"), $res);
    }

    /**
     * 修改密码
     *
     * @param  BaseRequests  $request
     * @param $user_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(BaseRequests $request){
        $this->validate($request, [
            'old_password' => 'required|string',
            'password' => 'required|max:255|confirmed',
            'password_confirmation' => 'required|max:255',
        ]);

        info('user change password', ['request' => $request->all()]);

        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return formatRet(500, trans("message.userNotExist"));
        }

        if (! password_verify($request->password, $user->password)) {
            return formatRet(500, trans("message.invalidOldPassword"));
        }

        $user->password = Hash::make($request->password);

        if (! $user->save()) {
            return formatRet(500, trans("message.failed"));
        }

        return success();
    }

    /**
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateProfile(BaseRequests $request){
        $data = $this->validate($request, [
            'name' => 'required|string|max:80',
            'contact_address' => 'sometimes|nullable|string|max:150',
            'contact' => 'sometimes|nullable|string|max:150',
            'industry' => 'sometimes|nullable|string|max:150',
        ]);

        /** @var User $user */
        $user = \auth()->user();

        $res = $user->update([
            'name' => $data['name'],
            'contact_address' => $data['contact_address'] ?? '',
            'contact' => $data['contact'] ?? '',
            'industry' => $data['industry'] ?? '',
        ]);

        if (! $res) {
            return formatRet(500, trans("message.failed"));
        }

        return formatRet(0);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        /** @var User $user */
        $user = \auth()->user();

        return success($user->only('id', 'avatar', 'name', 'contact_address', 'contact', 'industry'));
    }
    
    /**
     * @param  BaseRequests  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateAvatar(BaseRequests $request)
    {
        $this->validate($request, [
            'avatar' => 'required|string|max:255',
        ]);

        /** @var User $user */
        $user = \auth()->user();

        $user->avatar = $request->avatar;

        if (! $user->save()) {
            return formatRet(500, trans("message.failed"));
        }

        return formatRet(0);
    }

    public function show(BaseRequests $requests,$user_id)
    {
        $user = User::find($user_id);

        if ($user_id != Auth::id()) {
            return formatRet(500, trans("message.noPermission"));
        }

        return formatRet(0, trans("message.success"), $user->toArray());
    }


    public function callUser()
    {
        $users = User::all();

        $logo = env("APP_URL")."/images/logo.png";
        $qrCode = env("APP_URL")."/images/qrCode.png";
        $url = env('RESET_PASSWORD_URL');

        foreach ($users as $user) {
            $name = explode("@", $user->email)[0];
            $message = new UserCallBackEmail($logo, $qrCode, $url, $name);
            $message->onQueue('cangmishu_emails');

            Mail::to($user->email)->send($message);
        }

        return formatRet(0);
    }
}
