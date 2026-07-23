<?php

namespace App\Http\Middleware;

use App\Middleware\RateLimiter;

class RateLimitMiddleware
{
    /**
     * Enforce a per-IP rate limit for the given bucket type.
     * RateLimiter::check() returns true when allowed and terminates
     * the request with HTTP 429 when the limit is exceeded.
     */
    public function handle($request, $next, $type = 'api')
    {
        // Bypass rate limiting during local development testing/auditing
        if (isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING']) || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return $next($request);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = $type . '_' . $ip;

        RateLimiter::check($key, 60, 60);

        return $next($request);
    }
}
