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
        throw new AuthorizationException('您没有权限访问');
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