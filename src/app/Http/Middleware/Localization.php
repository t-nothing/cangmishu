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

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $this->getLocaleForRequest($request);

        if (! is_null($locale) && in_array($locale, ['en', 'cn','zh-CN'])) {
            if(in_array($locale, ['cn','zh-CN'])){
                $locale = "cn";
            }
            app('translator')->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale for the current request.
     * default lang ="cn"
     */
    public function getLocaleForRequest($request)
    {
        $locale = $request->header('Language');
        if (!is_null($locale) && trim($locale) != "") {
            $locale = $request->header('Language');
        }
        

        $locale = $request->query('lang');

        if (! is_null($locale) && trim($locale) != "") {
            return $locale;
        }

        $locale = "cn";

        return $locale;
    }
}
