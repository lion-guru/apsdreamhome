<?php

namespace App\Http\Middleware;

use Closure;

class DeprecationHeaderMiddleware
{
    public function handle($request, Closure $next)
    {
        header('Sunset: Wed, 31 Dec 2025 23:59:59 GMT');
        header('Deprecation: true');

        return $next($request);
    }
}

