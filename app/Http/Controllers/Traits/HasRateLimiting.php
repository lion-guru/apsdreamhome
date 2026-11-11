<?php

namespace App\Http\Controllers\Traits;

/**
 * Trait HasRateLimiting
 *
 * Provides rate limiting enforcement for controllers.
 * Include this trait in controllers that need rate limiting.
 */
trait HasRateLimiting
{
    /**
     * Enforce rate limiting based on request type.
     * Called automatically in BaseController::__construct() for POST and API requests.
     */
    protected function enforceRateLimit(): void
    {
        if (!class_exists('\\App\\Core\\Middleware\\RateLimitMiddleware')) {
            return;
        }

        // Determine rate limit type
        $type = 'web';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($uri, '/auth/') === 0 || strpos($uri, '/login') === 0) {
            $type = 'auth';
        } elseif (strpos($uri, '/api/') === 0) {
            $type = 'api';
        }

        try {
            $rateLimiter = new \App\Core\Middleware\RateLimitMiddleware();
            if (!$rateLimiter->check($type)) {
                $remaining = $rateLimiter->getRemaining($type);
                header('X-RateLimit-Remaining: ' . $remaining);
                header('Retry-After: 60');
                \App\Core\ErrorHandler::render(429, "Too many requests. Please try again later.");
                exit;
            }
        } catch (\Throwable $e) {
            // Rate limiting should never break the app
            error_log('Rate limit error: ' . $e->getMessage());
        }
    }
}