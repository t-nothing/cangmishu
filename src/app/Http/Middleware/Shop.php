<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Models\Shop as Model;

class Shop
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
        $shopId = $this->getShopIdRequest($request);

        $shopInfo = Model::find($shopId);

        if (!$shopInfo) {
            app('log')->info('店铺不存在', [$shopInfo->id]);
            throw new AuthorizationException('店铺不存在');
        }

        $shopInfo->load('senderAddress');
        
        $request->merge([
            'shop'  =>  $shopInfo
        ]);//合并参数

        return $next($request);
    }

    /**
     * Get the locale for the current request.
     * default lang ="cn"
     */
    public function getShopIdRequest($request)
    {
        $shop = $request->header('Shop');
        if (is_null($shop)) {
            $shop = $request->header('Shop');
        }
        
        if (! is_null($shop)) {
            return $shop;
        } 

        $shop = $request->query('lang');

        if (! is_null($shop)) {
            return $shop;
        }

        return $shop;
    }

}
