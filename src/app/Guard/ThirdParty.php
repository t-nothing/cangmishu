<?php

namespace App\Guard;

use App\Mail\UserActivationMail;
use App\Mail\UserForgetPasswordMail;
use App\Models\Token;
use App\Models\AppAccount;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ThirdParty implements Guard
{
    use GuardHelpers;


    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The name of the query string item from the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The name of the token "column" in persistent storage.
     *
     * @var string
     */
    protected $storageKey;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     * @param  \Illuminate\Http\Request $request
     * @param  string $inputKey
     * @param  string $storageKey
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request, $inputKey = 'api_token', $storageKey = 'api_token')
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->storageKey = $storageKey;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        $requestData = $this->getRequest()->all();

        $sign = $this->request->input('sign', '');
        $app_key = $this->request->input('app_key', '');
        $timestamp = intval($this->request->input('timestamp', 0));

        if ($timestamp <= 0) {
            return false;
        }

        if(trim($app_key) == "") {
            return false;
        }

        if(trim($sign) == "") {
            return false;
        }

        unset($requestData['sign']);

        // print_r($requestData);

        $max_expire = 120; // 秒
        // if (ENVIRONMENT != 'production') {
        //     $max_expire *= 1000;
        // }
        //开发环境20分钟过期
        if (abs(time() - $timestamp) > $max_expire) {
            return false;
        }

        //在这里要查询一下KEY的是否正常
        $info = AppAccount::where('app_key',$app_key)->first();

        if (!$info) {
            return false;
        }

        ksort($requestData);

        $_sign = $info->app_secret . http_build_query($requestData);
        $sign_str = md5($_sign);

        //如果签名不一样
        if ($sign_str != $sign) {
            return false;
        }
        $this->setUser($info);
        return true;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(){
        return $this->user;
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the current request instance.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request ?: Request::createFromGlobals();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    public function warehouse()
    {
        return $this->user()->warehouse;
    }

    public function warehouseId()
    {
        return $this->user()->warehouse->id;
    }

    public function ownerId(){
        return $this->user()->warehouse->owner_id;
    }

}