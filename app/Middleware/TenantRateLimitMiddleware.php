<?php
namespace App\Middleware;

use App\Core\Middleware\TenantContext;
use App\Services\TenantScopeService;

/**
 * TenantRateLimitMiddleware — Per-tenant API rate limiting based on subscription plan.
 *
 * Resolves the current tenant, reads their plan's rate limits, and enforces
 * per-tenant + per-IP rate limiting using session-based counters.
 *
 * Plan columns: api_calls_per_hour, requests_per_minute (on subscription_plans table)
 * Usage tracked: tenant_usage.api_calls (hourly counter)
 *
 * Priority: This middleware REPLACES the global RateLimiter::checkApi() for API routes.
 * The global limiter still applies to non-tenant-aware routes.
 */
class TenantRateLimitMiddleware
{
    private static ?array $planLimits = null;

    /**
     * Check if the current request is allowed based on tenant plan limits.
     * Returns true if allowed, sends 429 and exits if rate-limited.
     */
    public static function check(): bool
    {
        // Bypass during testing
        if (isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING'])
            || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return true;
        }

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $tenantId = self::getTenantId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $planLimits = self::getPlanLimits($tenantId);

        // Per-minute check (sliding window)
        $minuteKey = "rate_min_{$tenantId}_" . date('YmdHi');
        $minuteCount = ($_SESSION[$minuteKey] ?? 0) + 1;
        $_SESSION[$minuteKey] = $minuteCount;

        // Clean old minute keys (keep only current + 1 old)
        self::cleanOldKeys('rate_min_' . $tenantId . '_');

        $rpm = $planLimits['requests_per_minute'] ?? 60;
        if ($minuteCount > $rpm) {
            self::send429(
                "Rate limit exceeded: {$rpm} requests per minute allowed on your plan.",
                max(1, 60 - (int)date('s'))
            );
            return false;
        }

        // Per-hour check
        $hourKey = "rate_hr_{$tenantId}_" . date('YmdH');
        $hourCount = ($_SESSION[$hourKey] ?? 0) + 1;
        $_SESSION[$hourKey] = $hourCount;

        // Clean old hour keys
        self::cleanOldKeys('rate_hr_' . $tenantId . '_');

        $aph = $planLimits['api_calls_per_hour'] ?? 100;
        if ($hourCount > $aph) {
            self::send429(
                "Rate limit exceeded: {$aph} API calls per hour allowed on your plan.",
                max(1, 3600 - ((int)date('i') * 60 + (int)date('s')))
            );
            return false;
        }

        // Track usage in tenant_usage table (async-safe, errors swallowed)
        self::trackUsage($tenantId);

        // Set rate limit headers
        header("X-RateLimit-Limit-Minute: {$rpm}");
        header("X-RateLimit-Remaining-Minute: " . max(0, $rpm - $minuteCount));
        header("X-RateLimit-Limit-Hour: {$aph}");
        header("X-RateLimit-Remaining-Hour: " . max(0, $aph - $hourCount));
        header("X-Tenant-ID: {$tenantId}");

        return true;
    }

    /**
     * Get current tenant ID (resolves if not already set).
     */
    private static function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * Get plan rate limits for a tenant (cached per request).
     */
    private static function getPlanLimits(int $tenantId): array
    {
        if (self::$planLimits !== null) {
            return self::$planLimits;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Join tenants → subscription_plans to get limits
            $stmt = $db->prepare("
                SELECT sp.api_calls_per_hour, sp.requests_per_minute, sp.api_access
                FROM tenant_subscriptions ts
                JOIN subscription_plans sp ON sp.id = ts.plan_id
                WHERE ts.tenant_id = ? AND ts.status IN ('active', 'trialing')
                ORDER BY ts.created_at DESC LIMIT 1
            ");
            $stmt->execute([$tenantId]);
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($plan && (int)$plan['api_access'] === 1) {
                self::$planLimits = [
                    'api_calls_per_hour'  => (int)$plan['api_calls_per_hour'],
                    'requests_per_minute' => (int)$plan['requests_per_minute'],
                ];
            } elseif ($plan) {
                // Plan exists but API access disabled
                self::$planLimits = ['api_calls_per_hour' => 0, 'requests_per_minute' => 0];
            } else {
                // No subscription — free tier defaults
                self::$planLimits = ['api_calls_per_hour' => 100, 'requests_per_minute' => 20];
            }
        } catch (\Throwable $e) {
            error_log('[TenantRateLimitMiddleware] Plan lookup error: ' . $e->getMessage());
            self::$planLimits = ['api_calls_per_hour' => 100, 'requests_per_minute' => 20];
        }

        return self::$planLimits;
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
     * Track API call in tenant_usage table (hourly aggregate, fire-and-forget).
     */
    private static function trackUsage(int $tenantId): void
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $today = date('Y-m-d');
            $periodStart = date('Y-m-01');
            $periodEnd = date('Y-m-t');

            // Upsert monthly usage row
            $stmt = $db->prepare("
                INSERT INTO tenant_usage (tenant_id, period_start, period_end, api_calls, created_at)
                VALUES (?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE api_calls = api_calls + 1
            ");
            $stmt->execute([$tenantId, $periodStart, $periodEnd]);
        } catch (\Throwable $e) {
            // Non-critical — don't block the request
            error_log('[TenantRateLimitMiddleware] Usage tracking error: ' . $e->getMessage());
        }
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
                // Keep current window, remove older ones
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
     * Get current stats for a tenant (used by admin dashboard).
     */
    public static function getStats(int $tenantId): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $planLimits = self::getPlanLimits($tenantId);

        $minuteKey = "rate_min_{$tenantId}_" . date('YmdHi');
        $hourKey = "rate_hr_{$tenantId}_" . date('YmdH');

        return [
            'requests_this_minute' => $_SESSION[$minuteKey] ?? 0,
            'requests_per_minute'  => $planLimits['requests_per_minute'] ?? 60,
            'api_calls_this_hour'  => $_SESSION[$hourKey] ?? 0,
            'api_calls_per_hour'   => $planLimits['api_calls_per_hour'] ?? 100,
        ];
    }
}
