<?php

namespace App\Http\Middleware;

class ThrottleLoginMiddleware extends RateLimitMiddleware
{
    public function handle($request, $next, $type = 'login')
    {
        return parent::handle($request, $next, 'login');
    }
}
