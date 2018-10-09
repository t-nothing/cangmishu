<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Validation\ValidationException;
use App\Services\Auth\WarehouseGuardHelpers;
use App\Exceptions\BusinessException;
use App\Models\UserApp;

class SignGuard implements Guard
{
    use GuardHelpers, WarehouseGuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    protected $userApp;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     * @param  \Illuminate\Http\Request $request
     * @param  string $inputKey
     * @param  string $storageKey
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $app = $this->userApp();

        if (! $user = $app->user) {
            throw new BusinessException('appkey绑定了无效的账户');
        }

        if (! $warehouse = $app->warehouse) {
            throw new BusinessException('appkey绑定了无效的仓库');
        }

        $this->setWarehouse($warehouse);

        return $this->user = $user;
    }

    public function userApp()
    {
        if (! $this->userApp = UserApp::where('app_key', $this->getAppKeyForRequest())->first()) {
            throw new BusinessException('appkey无效');
        }

        return $this->userApp;
    }

    /**
     * Get the appkey for the current request.
     *
     * @return string
     */
    public function getAppKeyForRequest()
    {
        $appKey = $this->request->query('app_key');

        if (empty($appKey)) {
            $appKey = $this->request->input('app_key');
        }

        if (empty($appKey)) {
            $appKey = $this->request->header('APPKEY', '');
        }

        return $appKey;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    // protected function validator(array $data)
    // {
    //     return app('validator')->make($data, [
    //         'app_key' => 'required|string',
    //         'format' => 'required|string',
    //         'sign' => 'required|string',
    //         'sign_method' => 'required|string',
    //         'timestamp' => 'required|integer',
    //     ]);
    // }

    // public function getSystemParametersForRequest()
    // {
    //     $query = $this->getRequest()->query();

    //     $validator = $this->validator($query);

    //     if ($validator->fails()) {
    //         $this->throwValidationException($this->getRequest(), $validator);
    //     }

    //     // 获取 app
    //     if (! $this->userApp = UserApp::where('app_key', $query['app_key'])->first()) {
    //         return formatRet(500, 'appkey不正确');
    //     }

    //     return $query;
    // }

    // public function getPostDataForRequest()
    // {
    //     return $this->getRequest()->post();
    // }

    // protected function generateSign($params, $app_secret)
    // {
    //     ksort($params);

    //     $stringToBeSigned = $app_secret;

    //     foreach ($params as $k => $v) {
    //         if (is_string($v) && "@" != substr($v, 0, 1)) {
    //             $stringToBeSigned .= "$k$v";
    //         }
    //     }

    //     unset($k, $v);

    //     return strtoupper(md5($stringToBeSigned));
    // }

    // protected function throwValidationException(Request $request, $validator, $message = '')
    // {
    //     $response = null;

    //     if (is_string($message) && $message != '') {
    //         $response = formatRet(500, $message);
    //     } else {
    //         $response = formatRet(422, $validator->errors()->first(), $validator->errors()->getMessages(), 422);
    //     }

    //     throw new ValidationException($validator, $response);
    // }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        //
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
}
