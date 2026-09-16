<?php
/**
 * Rate Limit Middleware
 *
 * Redis-backed rate limiting with tiered limits and per-endpoint configuration.
 * Falls back to file-based storage if Redis is unavailable.
 *
 * Limits:
 * - Auth endpoints: 5 requests per minute
 * - API endpoints: 120 requests per minute (tier adjusted)
 * - Search endpoints: 30 requests per minute
 * - Admin endpoints: 300 requests per minute (tier adjusted)
 * - Web endpoints: 600 requests per minute
 */

namespace App\Core\Middleware;

use App\Services\Cache\RedisRateLimiter;

class RateLimitMiddleware
{
    private RedisRateLimiter $limiter;
    private array $endpointMap = [
        // Auth routes
        '/auth/login'        => 'auth.login',
        '/auth/register'     => 'auth.register',
        '/auth/forgot-password' => 'auth.password',
        '/auth/reset-password'  => 'auth.password',
        '/auth/verify-otp'   => 'auth.otp',
        '/auth/air-login'    => 'auth.air_login',
        '/auth/air-login/verify' => 'auth.otp',

        // API routes
        '/api/auth/login'    => 'auth.login',
        '/api/auth/register' => 'auth.register',
        '/api/auth/forgot-password' => 'auth.password',
        '/api/auth/verify-otp' => 'auth.otp',
        '/api/search'        => 'api.search',
        '/api/properties'    => 'api.properties',
        '/api/bookings'      => 'api.bookings',
        '/api/payments'      => 'api.payments',
        '/api/profile'       => 'api.profile',
        '/api/notifications' => 'api.notifications',

        // Admin routes
        '/admin/api'         => 'admin.default',
        '/admin/export'      => 'admin.export',
        '/admin/bulk'        => 'admin.bulk',
    ];

    private array $patternMap = [
        '#^/api/auth/#'      => 'auth.login',
        '#^/api/search#'     => 'api.search',
        '#^/api/properties#' => 'api.properties',
        '#^/api/bookings#'   => 'api.bookings',
        '#^/api/payments#'   => 'api.payments',
        '#^/api/profile#'    => 'api.profile',
        '#^/api/notifications#' => 'api.notifications',
        '#^/admin/api#'      => 'admin.default',
        '#^/admin/export#'   => 'admin.export',
        '#^/admin/bulk#'     => 'admin.bulk',
    ];

    public function __construct()
    {
        $configPath = __DIR__ . '/../../../config/rate_limits.php';
        $config = is_file($configPath) ? (require $configPath) : [];
        $this->limiter = new RedisRateLimiter($config);
    }

    /**
     * Check rate limit for current request.
     * Returns false if limit exceeded (and sends 429 response).
     */
    public function check(): bool
    {
        // Skip for test requests
        if ($this->isTestRequest()) {
            return true;
        }

        $identifier = $this->getIdentifier();
        $endpoint = $this->resolveEndpoint();
        $tier = $this->resolveTier();

        return $this->limiter->enforce($identifier, $endpoint, $tier);
    }

    /**
     * Get remaining requests for current request context.
     */
    public function getRemaining(): int
    {
        $identifier = $this->getIdentifier();
        $endpoint = $this->resolveEndpoint();
        $tier = $this->resolveTier();

        $result = $this->limiter->check($identifier, $endpoint, $tier);
        return $result['remaining'] ?? 0;
    }

    /**
     * Determine if this is a test request that should bypass rate limiting.
     */
    private function isTestRequest(): bool
    {
        return isset($_GET['test_login'])
            || isset($_SERVER['HTTP_X_TESTING'])
            || (defined('APP_ENV') && APP_ENV === 'testing');
    }

    /**
     * Get client identifier (IP + User Agent hash).
     */
    private function getIdentifier(): string
    {
        return RedisRateLimiter::getClientIdentifier();
    }

    /**
     * Resolve endpoint configuration key from current request path.
     */
    private function resolveEndpoint(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($path, PHP_URL_PATH) ?? '/';

        // Exact match first
        if (isset($this->endpointMap[$path])) {
            return $this->endpointMap[$path];
        }

        // Pattern match
        foreach ($this->patternMap as $pattern => $endpoint) {
            if (preg_match($pattern, $path)) {
                return $endpoint;
            }
        }

        // Default based on path prefix
        if (str_starts_with($path, '/api/')) {
            return 'api.default';
        }
        if (str_starts_with($path, '/admin/')) {
            return 'admin.default';
        }
        if (str_starts_with($path, '/auth/')) {
            return 'auth.login';
        }

        return 'web.default';
    }

    /**
     * Resolve user tier from session.
     */
    private function resolveTier(): ?string
    {
        // Check for authenticated user
        $userId = null;

        if (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
        } elseif (isset($_SESSION['admin_id'])) {
            $userId = (int)$_SESSION['admin_id'];
        } elseif (isset($_SESSION['employee_id'])) {
            $userId = (int)$_SESSION['employee_id'];
        } elseif (isset($_SESSION['associate_id'])) {
            $userId = (int)$_SESSION['associate_id'];
        } elseif (isset($_SESSION['agent_id'])) {
            $userId = (int)$_SESSION['agent_id'];
        }

        if ($userId) {
            return $this->limiter->getTierForUser($userId);
        }

        // Check for API token (Bearer token)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            // Could look up API key tier here
            return 'basic'; // Default for API tokens
        }

        return null; // Use default tier
    }

    /**
     * Manually enforce rate limit for a specific endpoint (for controllers).
     */
    public function enforceForEndpoint(string $endpoint, ?string $identifier = null, ?string $tier = null): bool
    {
        $identifier = $identifier ?? $this->getIdentifier();
        $tier = $tier ?? $this->resolveTier();

        return $this->limiter->enforce($identifier, $endpoint, $tier);
    }

    /**
     * Get rate limiter instance for advanced usage.
     */
    public function getLimiter(): RedisRateLimiter
    {
        return $this->limiter;
    }

    /**
     * Check if Redis backend is available.
     */
    public function isRedisAvailable(): bool
    {
        return $this->limiter->isRedisAvailable();
    }
}