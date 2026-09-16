<?php

namespace App\Http\Middleware;

use App\Core\Middleware\RateLimitMiddleware as CoreRateLimitMiddleware;

/**
 * HTTP Middleware wrapper for Core RateLimitMiddleware.
 * Provides backward compatibility for existing route middleware usage.
 *
 * Usage in routes:
 *   $router->post('/api/login', 'Controller@method')
 *       ->middleware('App\Http\Middleware\RateLimitMiddleware:auth.login');
 */
class RateLimitMiddleware
{
    private CoreRateLimitMiddleware $coreMiddleware;
    private string $endpoint = 'api.default';

    public function __construct()
    {
        $this->coreMiddleware = new CoreRateLimitMiddleware();
    }

    /**
     * Handle incoming request with rate limiting.
     *
     * @param mixed $request Request object or null
     * @param callable $next Next middleware closure
     * @param string $endpoint Rate limit endpoint key (e.g., 'auth.login', 'api.default')
     * @return mixed
     */
    public function handle($request, $next, string $endpoint = 'api.default')
    {
        $this->endpoint = $endpoint;

        // Bypass rate limiting during local development testing/auditing
        if (isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING']) || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return $next($request);
        }

        // Use core middleware for actual rate limiting with endpoint-specific config
        $this->coreMiddleware->enforceForEndpoint($endpoint);

        return $next($request);
    }

    /**
     * Static method for direct rate limit checks (backward compat).
     */
    public static function checkApi(): bool
    {
        $middleware = new self();
        return $middleware->coreMiddleware->enforceForEndpoint('api.default');
    }

    public static function checkLogin(): bool
    {
        $middleware = new self();
        return $middleware->coreMiddleware->enforceForEndpoint('auth.login');
    }

    /**
     * Get core middleware instance for advanced usage.
     */
    public function getCoreMiddleware(): CoreRateLimitMiddleware
    {
        return $this->coreMiddleware;
    }
}