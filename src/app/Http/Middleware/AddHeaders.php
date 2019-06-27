<?php

namespace App\Http\Middleware;

use Closure;

class AddHeaders
{

    public function handle($request, Closure $next)
    {
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
         return $next($request);
    }
}
