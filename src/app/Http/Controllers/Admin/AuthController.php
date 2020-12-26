<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * 登入
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $guard = app('auth')->guard();

        if (! $data = $guard->login($guard->credentials(), true)) {
            return formatRet(500, $guard->sendFailedLoginResponse());
        }

        $data['user'] = $guard->user();

        return formatRet(0, '', $data);
    }
}
