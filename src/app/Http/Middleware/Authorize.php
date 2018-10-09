<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Privilege;
use App\Models\Warehouse;
use App\Models\WarehouseEmployee;

class Authorize
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The gate instance.
     *
     * @var \Illuminate\Contracts\Auth\Access\Gate
     */
    protected $gate;

    /**
     * 当前请求的 method
     *
     * @var string
     */
    protected $method;

    /**
     * 当前请求的 uri
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function __construct(Auth $auth, Gate $gate)
    {
        $this->auth = $auth;
        $this->gate = $gate;
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
            throw new AuthorizationException('账号被锁');
        }

        $roles =  explode('|', $roles);

        // 必须是平台管理员，其他的角色没有用
        if (in_array('admin', $roles)) {
            $this->isAdmin($user);
        } else {
            // 如果不需要是平台管理员，就看是否拥有仓库的角色
            $role_ids = WarehouseEmployee::NameToId($roles);	

    	    // 判断该用户在仓库中是否有角色
        	$user_role = $this->auth->roleId();
        	if ( ! in_array($user_role,$role_ids)) {
        		throw new AuthorizationException('无访问权限，请联系管理员');	
            }
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

    /**
     * 手持端和PC端
     */
    protected function isWarehouseEmployee($user)
    {
        $warehouse = $this->auth->warehouse();

        // 产权方？
        if ($warehouse->owner_id == $user->id) {
            return true;
        }

        $warehouse->load('employees');

        // 仓库工作人员？
        if (in_array($user->id, $warehouse->employees->pluck('user_id')->toArray())) {
            return true;
        }

        throw new AuthorizationException('无权限访问该仓库');
    }


    protected function authorize($request)
    {
        $privilege = Privilege::where('method', $request->method())
                              ->where('uri', $request->getPathInfo())
                              ->first();

        $result = $request->user()->can($privilege->id);

        return $result ? $this->allow() : $this->deny();
    }

    /**
     * Create a new access response.
     */
    protected function allow()
    {
        //
    }

    /**
     * Throws an unauthorized exception.
     *
     * @param  string  $message
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function deny($message = 'This action is unauthorized.')
    {
        throw new AuthorizationException($message);
    }
}
