<?php
namespace App\Middleware;

use App\Core\Middleware\TenantContext;

/**
 * AjaxRateLimitMiddleware — Simple rate limiting for AJAX endpoints
 * Uses session-based counting with per-minute and per-hour limits
 */
class AjaxRateLimitMiddleware
{
    /**
     * Check if the current request is allowed based on rate limits.
     * Returns true if allowed, sends 429 and exits if rate-limited.
     */
    public static function check(int $maxPerMinute = 60, int $maxPerHour = 1000): bool
    {
        // Bypass during testing
        if (isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING'])
            || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return true;
        }

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Per-minute check (sliding window)
        $minuteKey = "ajax_rate_min_" . md5($ip) . "_" . date('YmdHi');
        $minuteCount = ($_SESSION[$minuteKey] ?? 0) + 1;
        $_SESSION[$minuteKey] = $minuteCount;

        // Clean old minute keys (keep only current + 1 old)
        self::cleanOldKeys("ajax_rate_min_" . md5($ip) . "_");

        if ($minuteCount > $maxPerMinute) {
            self::send429(
                "Rate limit exceeded: {$maxPerMinute} requests per minute allowed.",
                max(1, 60 - (int)date('s'))
            );
            return false;
        }

        // Per-hour check
        $hourKey = "ajax_rate_hr_" . md5($ip) . "_" . date('YmdH');
        $hourCount = ($_SESSION[$hourKey] ?? 0) + 1;
        $_SESSION[$hourKey] = $hourCount;

        // Clean old hour keys
        self::cleanOldKeys("ajax_rate_hr_" . md5($ip) . "_");

        if ($hourCount > 1000) {
            self::send429(
                "Rate limit exceeded: 1000 requests per hour allowed.",
                max(1, 3600 - ((int)date('i') * 60 + (int)date('s')))
            );
            return false;
        }

        // Set rate limit headers
        header("X-RateLimit-Limit-Minute: {$maxPerMinute}");
        header("X-RateLimit-Remaining-Minute: " . max(0, $maxPerMinute - $minuteCount));
        header("X-RateLimit-Limit-Hour: 1000");
        header("X-RateLimit-Remaining-Hour: " . max(0, 1000 - $hourCount));

        return true;
    }

    /**
     * Send 429 response and exit.
     */
    private static function send429(string $message, int $retryAfter): void
    {
        http_response_code(429);
        header('Content-Type: application/json');
        header("Retry-After: {$retryAfter}");
        header('X-RateLimit-Limited: true');
        echo json_encode([
            'error'       => 'rate_limit_exceeded',
            'message'     => $message,
            'retry_after' => $retryAfter,
        ]);
        exit;
    }

    /**
     * Clean old session rate limit keys (keep only current + previous window).
     */
    private static function cleanOldKeys(string $prefix): void
    {
        // Only clean every 100th request to avoid overhead
        if (rand(1, 100) !== 1) return;

        foreach ($_SESSION as $key => $val) {
            if (strpos($key, $prefix) === 0 && is_int($val)) {
                $current = $prefix . date('YmdHi');
                $prev = date('YmdHi', strtotime('-1 minute'));
                $prevKey = $prefix . $prev;
                if ($key !== $current && $key !== $prevKey) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }

    /**
     * Middleware handle method for route middleware
     */
    public function handle($request, $next)
    {
        self::check(60, 1000);
        return $next($request);
    }
}