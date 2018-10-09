<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

        if (! is_null($locale) && in_array($locale, ['en', 'cn'])) {
            app('translator')->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale for the current request.
     */
    public function getLocaleForRequest($request)
    {
        $locale = $request->header('Language');

        if (! is_null($locale)) {
            return $locale;
        }

        $locale = $request->query('lang');

        if (! is_null($locale)) {
            return $locale;
        }

        return;
    }
}
