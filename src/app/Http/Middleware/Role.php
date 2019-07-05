<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\Access\AuthorizationException;

class Role
{

    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;



    public function __construct( Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function handle($request, Closure $next, $roles)
    {
        $user = $this->auth->authenticate();

        if ($user->isLocked()) {
            throw new AuthorizationException('账号被锁,请联系管理员');
        }

        $roles =  explode('|', $roles);

        // 必须是平台管理员，其他的角色没有用
        if (in_array('admin', $roles)) {
            $this->isAdmin($user);
        }
        return $next($request);
    }

    /**
     * 是不是平台管理员
     */
    protected function isAdmin($user)
    {
        if (! $user->isAdmin()) {
            throw new AuthorizationException('非管理员，无权限 ');
        }

        return true;
    }
}
