<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse|void
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $credentials = [
            $this->username() => $request['username'],
            'password' => $request['password'],
        ];

        $token = $this->guard()->attempt($credentials);

        if (! $token) {
            return eRet('用户名或密码错误！');
        }

        if (auth('super_admin')->user()->forbid_login === true) {
            auth('super_admin')->logout();

            return eRet('暂时无法登录');
        }

        $user = \auth('super_admin')->user();

        $this->updateLastLoginAt($user);

        $this->updateLastLoginIp($user);

        return $this->respondWithToken($token, $user);
    }

    /**
     *
     * @return JsonResponse
     */
    public function me()
    {
        return response()->json(auth('super_admin')->user());
    }

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth('super_admin')->logout();

        return formatRet(1, '注销成功！');
    }

    /**
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('super_admin')->refresh(), \auth('super_admin')->user());
    }

    /**
     * @param  Authenticatable  $user
     * @return bool
     */
    protected function updateLastLoginAt(Authenticatable $user): bool
    {
        $user->last_login_at = now();

        return $user->save();
    }

    /**
     * @param  Authenticatable  $user
     * @return bool
     */
    protected function updateLastLoginIp(Authenticatable $user): bool
    {
        $user->last_login_ip = \request()->getClientIp();

        return $user->save();
    }

    /**
     * @param $token
     * @param  Authenticatable  $user
     * @return JsonResponse
     */
    protected function respondWithToken($token, Authenticatable $user)
    {
        return formatRet(1, 'success', [
            'username' => $user->username,
            'email' => $user->email,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('super_admin')->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            #'key' => 'required|string',
            #'captcha' => 'required|captcha_api:' . $request->input('key', ''),
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        $username = request()->input('username');

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('super_admin');
    }
}
