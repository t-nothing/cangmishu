<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
        $shopId = intval($this->getShopIdRequest($request));

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
