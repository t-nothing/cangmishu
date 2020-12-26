<?php

namespace App\Guard;

use App\Mail\UserActivationMail;
use App\Mail\UserForgetPasswordMail;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class JwtGuard implements Guard
{
    use GuardHelpers, WarehouseGuardHelpers;

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
    private TokenCreator $token_creator;

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
        $this->token_creator = new TokenCreator();
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
        if (!is_null($this->user)) {
//            dd($this->user);
            return $this->user;
        }

        $user = null;

        $token_value = $this->getTokenForRequest();

        if (! empty($token_value)) {
            $token = Token::valid()->where('token_value', '=', $token_value)->first();

            if (!empty($token)) {
                $user = $this->provider->retrieveById($token->owner_user_id);
            }
        }

        return $this->user = $user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        $token = $this->getRequest()->header('Authorization', '');

        if (empty($token)) {
            $token = $this->request->query($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->session()->get($this->inputKey);
        }


        if (Str::startsWith($token, 'Bearer ')) {
            return Str::substr($token, 7);
        }
        return $token;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @return array
     */
    public function credentials()
    {
        $request = $this->getRequest();

        return $request->only('email', 'password');
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [$this->storageKey => $credentials[$this->inputKey]];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
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
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array $credentials
     * @return array
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function login(array $credentials = [], $onlyAdmin = false)
    {
        if(empty($credentials['email']))
        {
            Log::info('用户名、邮箱或密码不正确 A', $credentials);
            // eRet('用户名、邮箱或密码不正确');
            return false;
        }
//        $user = $this->provider->retrieveByCredentials($credentials);
        $user= User::with(['defaultWarehouse:id,name_cn'])->where('phone',$credentials['email'])->orWhere('email',$credentials['email'])->first();
        if(!$user){
            Log::info('用户名、邮箱或密码不正确 B', $credentials);
            // eRet('用户名、邮箱或密码不正确');
            return false;
        }
        if(!Hash::check($credentials['password'], $user->password)){
            \Log::info('用户名、邮箱或密码不正确 C', $credentials);
            // eRet('用户名、邮箱或密码不正确');
            return false;
        }

        if ($user->isLocked()) {
            // eRet('帐户被锁，禁止登入！');
            return false;
        }

        // if (!$user->isActivated()) {
        //     eRet('帐户尚未激活，禁止登入！');
        // }


        if ($this->hasValidCredentials($user, $credentials)) {
            // If we have an event dispatcher instance set we will fire an event so that
            // any listeners will hook into the authentication events and run actions
            // based on the login and logout events fired from the guard instances.
            // $this->fireLoginEvent($user, $remember);

            // $this->clearLoginAttempts($this->getResquest());

            // 更新最后登入时间

            $user->last_login_at = time();
            $user->save();

            $this->setUser($user);

            // $request = $this->getRequest();
            // if (!is_null($user) && $latest = $user->latestAccessToken) {
            //     return [
            //         'token' => $latest->toArray(),
            //     ];
            // }

            $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);

            return [
                'token' => $token->toArray(),
            ];
        }

        // If the authentication attempt fails we will fire an event so that the user
        // may be notified of any suspicious attempts to access their account from
        // an unrecognized user. A developer may listen to this event as needed.
        // $this->fireFailedEvent($user, $credentials);

        // throw new AuthenticationException($this->sendFailedLoginResponse());
        return false;
    }

    /**
     * @param  User  $user
     * @return false|mixed
     */
    public function token(User $user)
    {
        if($user->isLocked()){
            return false;
        }

        $user->last_login_at = time();
        $user->save();

        $this->setUser($user);

        $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);

        return $token['token_value'];
    }

    /**
     * @param  int  $id
     * @return array|false
     */
    public function userLogin(int $id)
    {
        $user = User::with(['defaultWarehouse:id,name_cn'])
            ->whereKey($id)
            ->first();

        if(! $user || $user->isLocked()){
            return false;
        }

        $user->last_login_at = time();
        $user->save();

        $this->setUser($user);

        $token = $this->createToken($user, Token::TYPE_ACCESS_TOKEN);

        return [
            'token' => $token->toArray(),
        ];
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed $user
     * @param  array $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return ! is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Get the failed login response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendFailedLoginResponse()
    {
        return trans('auth.failed');
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        // $this->clearUserDataFromStorage();

        $token_value = $this->getTokenForRequest();

        if (!empty($token_value)) {
            $token = Token::where('token_value', '=', $token_value)->delete();
        }

        // if (! is_null($this->user)) {
        //     $this->cycleRememberToken($user);
        // }

        // if (isset($this->events)) {
        //     $this->events->dispatch(new Events\Logout($user));
        // }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        // $this->loggedOut = true;
    }

    public  function isValidPassWord(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if ($this->hasValidCredentials($user, $credentials)) {
            return true;
        }

        return false;
    }

    /**
     * 生成一个新的 token，token 哈希来保证唯一性。
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return \App\Models\Token|null
     */
    public function createToken(AuthenticatableContract $user, $type)
    {
        return $this->token_creator->create($user, $type);
    }

    /**
     * 创建用户激活验证
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  int $type
     * @return bool
     */
    public function createUserActivation(AuthenticatableContract $user)
    {
        if (!$token = $this->createToken($user, Token::TYPE_EMAIL_CONFIRM)) {
            return false;
        }

        $toMail = $user->email;
        $name = $user->nickname;
        $url = route('user-activation', ['token_value' => $token->token_value]);

        $message = new UserActivationMail($toMail, $name, $url);
        $message->onQueue('emails');

        Mail::send($message);
    }

    /**
     * 创建用户重置密码验证
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  int $type
     * @return bool
     */
    public function createUserResetActivation(AuthenticatableContract $user)
    {
        $hasToken = Token::where("owner_user_id", '=', $user->id)
            ->where("token_type", '=', Token::TYPE_FORGET_PASSWORD)
            ->where('expired_at', '>', time())
            ->latest('expired_at')
            ->first();
        if (empty($hasToken)) {
            if (!$token = $this->createToken($user, Token::TYPE_FORGET_PASSWORD)) {
                return false;
            }
        } else
            $token = $hasToken;
        $toMail = $user->email;
        $name = $user->nickname;
        $url = route('pwd-activation', ['token_value' => $token->token_value]);

        $logo=env("APP_URL")."/images/logo.png";
        $qrCode =env("APP_URL")."/images/qrCode.png";
        $message = new UserForgetPasswordMail($toMail, $name, $url,$logo,$qrCode);
        $message->onQueue('cangmishu_emails');

        Mail::send($message);
    }
}
