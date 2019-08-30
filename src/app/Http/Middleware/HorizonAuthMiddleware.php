<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Factory as Auth;

class HorizonAuthMiddleware
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

    public function handle($request, Closure $next)
    {
        print_r($request->user());exit;
        throw new AuthorizationException('您没有权限访问');
        print_r(Auth::user()->id);
        $shopId = $this->getShopIdRequest($request);

        $shopInfo = Model::find($shopId);

        if (!$shopInfo) {
            app('log')->info('店铺不存在', [$shopId]);
            throw new AuthorizationException('店铺不存在');
        }

        $shopInfo->load('senderAddress');
        
        $request->merge([
            'shop'  =>  $shopInfo
        ]);//合并参数

        return $next($request);
    }
}