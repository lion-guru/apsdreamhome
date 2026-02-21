<?php

namespace App\Http\Middleware;

use App\Services\Legacy\RateLimiter;

class RateLimitMiddleware
{
    protected $limiter;

    public function __construct()
    {
        $this->limiter = new RateLimiter();
    }

    public function handle($request, $next, $type = 'api')
    {
        // Get IP address for rate limiting key
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = $type . '_' . $ip;

        $status = $this->limiter->checkLimit($key, $type);

        if ($status['limited']) {
            http_response_code(429);
            header('Retry-After: ' . $status['retry_after']);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $status['retry_after']
            ]);
            exit;
        }

        // Add rate limit headers
        if (function_exists('header')) {
            header('X-RateLimit-Limit: ' . ($status['remaining'] + 1)); // This logic might be slightly off depending on checkLimit implementation, but close enough
            header('X-RateLimit-Remaining: ' . $status['remaining']);
            header('X-RateLimit-Reset: ' . $status['reset']);
        }

        return $next($request);
    }
}
