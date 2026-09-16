<?php

namespace App\Services\Cache;

use App\Core\Database\Database;
use Redis;

/**
 * Redis-backed Rate Limiter with tiered limits and graceful fallback.
 *
 * Features:
 * - Tiered limits (free/basic/pro/admin)
 * - Per-endpoint overrides
 * - Sliding window algorithm
 * - Standard rate limit headers
 * - File-based fallback when Redis unavailable
 */
class RedisRateLimiter
{
    private Redis|null $redis = null;
    private array $config;
    private bool $redisAvailable = false;
    private string $fallbackDir;
    private string $prefix;

    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $configPath = __DIR__ . '/../../../config/rate_limits.php';
            $this->config = is_file($configPath) ? (require $configPath) : [];
        } else {
            $this->config = $config;
        }
        
        $this->prefix = $this->config['redis_prefix'] ?? 'apsdream:ratelimit:';
        $this->fallbackDir = sys_get_temp_dir() . '/apsdream_ratelimit';

        if (!is_dir($this->fallbackDir)) {
            @mkdir($this->fallbackDir, 0777, true);
        }

        $this->initRedis();
    }

    private function initRedis(): void
    {
        if (!extension_loaded('redis')) {
            return;
        }

        try {
            $cacheConfigPath = __DIR__ . '/../../../config/cache.php';
            $redisConfig = is_file($cacheConfigPath) ? (require $cacheConfigPath) : [];
            $rc = $redisConfig['redis'] ?? [];

            $this->redis = new Redis();
            $this->redis->connect(
                $rc['host'] ?? '127.0.0.1',
                $rc['port'] ?? 6379,
                $rc['timeout'] ?? 2.0
            );

            if (!empty($rc['password'])) {
                $this->redis->auth($rc['password']);
            }

            if (isset($rc['database'])) {
                $this->redis->select($rc['database']);
            }

            // Test connection
            $this->redis->ping();
            $this->redisAvailable = true;
        } catch (\Throwable $e) {
            $this->redis = null;
            $this->redisAvailable = false;
        }
    }

    /**
     * Check rate limit for a given identifier and endpoint.
     *
     * @param string $identifier Unique identifier (user_id, IP, API key, etc.)
     * @param string $endpoint Endpoint key from config (e.g., 'api.default', 'auth.login')
     * @param string|null $tier Optional tier override (free/basic/pro/admin)
     * @return array ['allowed' => bool, 'limit' => int, 'remaining' => int, 'reset' => int, 'retry_after' => int|null]
     */
    public function check(string $identifier, string $endpoint = 'api.default', ?string $tier = null): array
    {
        $endpointConfig = $this->config['endpoints'][$endpoint] ?? $this->config['endpoints']['api.default'];
        $keyPrefix = $endpointConfig['key_prefix'] ?? 'api';
        $window = $endpointConfig['window'] ?? 60;
        $maxRequests = $endpointConfig['limit'] ?? 60;

        // Apply tier-based multiplier if no explicit endpoint limit
        if ($tier && !isset($this->config['endpoints'][$endpoint])) {
            $tierConfig = $this->config['tiers'][$tier] ?? $this->config['tiers'][$this->config['default_tier']];
            if ($tierConfig['requests_per_minute'] > 0) {
                $maxRequests = $tierConfig['requests_per_minute'];
            }
        }

        // Admin/unlimited tier
        if ($maxRequests < 0) {
            return [
                'allowed' => true,
                'limit' => -1,
                'remaining' => -1,
                'reset' => time() + $window,
                'retry_after' => null,
            ];
        }

        $key = $this->prefix . $keyPrefix . ':' . $identifier;

        if ($this->redisAvailable && $this->redis) {
            return $this->checkRedis($key, $maxRequests, $window);
        }

        return $this->checkFile($key, $maxRequests, $window);
    }

    /**
     * Check using Redis (sliding window with sorted set).
     */
    private function checkRedis(string $key, int $maxRequests, int $window): array
    {
        try {
            $now = time();
            $windowStart = $now - $window;

            // Remove expired entries
            $this->redis->zRemRangeByScore($key, 0, $windowStart);

            // Count current requests
            $current = $this->redis->zCard($key);

            if ($current >= $maxRequests) {
                // Get oldest entry to calculate retry-after
                $oldest = $this->redis->zRange($key, 0, 0, ['WITHSCORES' => true]);
                $retryAfter = $oldest ? max(1, (int)$oldest[1] + $window - $now) : $window;

                return [
                    'allowed' => false,
                    'limit' => $maxRequests,
                    'remaining' => 0,
                    'reset' => $now + $retryAfter,
                    'retry_after' => $retryAfter,
                ];
            }

            // Add current request
            $this->redis->zAdd($key, $now, "$now." . uniqid('', true));
            $this->redis->expire($key, $window + 1);

            return [
                'allowed' => true,
                'limit' => $maxRequests,
                'remaining' => $maxRequests - $current - 1,
                'reset' => $now + $window,
                'retry_after' => null,
            ];
        } catch (\Throwable $e) {
            // Fallback to file on Redis error
            $this->redisAvailable = false;
            return $this->checkFile($key, $maxRequests, $window);
        }
    }

    /**
     * Check using file-based storage (fallback).
     */
    private function checkFile(string $key, int $maxRequests, int $window): array
    {
        $file = $this->fallbackDir . '/' . md5($key) . '.json';
        $now = time();
        $windowStart = $now - $window;

        $data = [
            'window_start' => $now,
            'requests' => [],
        ];

        if (file_exists($file)) {
            $json = @file_get_contents($file);
            $decoded = @json_decode($json, true);
            if (is_array($decoded) && isset($decoded['requests'])) {
                $data = $decoded;
            }
        }

        // Filter out expired requests
        $data['requests'] = array_filter($data['requests'], fn($ts) => $ts > $windowStart);
        $current = count($data['requests']);

        if ($current >= $maxRequests) {
            $oldest = min($data['requests']) ?? $now;
            $retryAfter = max(1, $oldest + $window - $now);

            return [
                'allowed' => false,
                'limit' => $maxRequests,
                'remaining' => 0,
                'reset' => $now + $retryAfter,
                'retry_after' => $retryAfter,
            ];
        }

        // Add current request
        $data['requests'][] = $now;
        $data['window_start'] = $now;

        @file_put_contents($file, json_encode($data));

        return [
            'allowed' => true,
            'limit' => $maxRequests,
            'remaining' => $maxRequests - $current - 1,
            'reset' => $now + $window,
            'retry_after' => null,
        ];
    }

    /**
     * Get rate limit headers for response.
     */
    public function getHeaders(array $result): array
    {
        if (!$this->config['include_headers']) {
            return [];
        }

        $headers = $this->config['headers'] ?? [];

        $out = [];
        if ($result['limit'] >= 0) {
            $out[$headers['limit'] ?? 'X-RateLimit-Limit'] = (string)$result['limit'];
            $out[$headers['remaining'] ?? 'X-RateLimit-Remaining'] = (string)$result['remaining'];
            $out[$headers['reset'] ?? 'X-RateLimit-Reset'] = (string)$result['reset'];
        }
        if ($result['retry_after'] !== null) {
            $out[$headers['retry'] ?? 'Retry-After'] = (string)$result['retry_after'];
        }

        return $out;
    }

    /**
     * Send rate limit headers and optionally terminate with 429.
     */
    public function enforce(string $identifier, string $endpoint = 'api.default', ?string $tier = null): bool
    {
        $result = $this->check($identifier, $endpoint, $tier);

        // Send headers
        foreach ($this->getHeaders($result) as $name => $value) {
            header("$name: $value");
        }

        if (!$result['allowed']) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $result['retry_after'],
            ]);
            exit;
        }

        return true;
    }

    /**
     * Get current tier for a user based on role.
     */
    public function getTierForUser(int $userId): string
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $role = $stmt->fetchColumn();

            if ($role && isset($this->config['role_tier_map'][$role])) {
                return $this->config['role_tier_map'][$role];
            }
        } catch (\Throwable) {
            // Ignore
        }

        return $this->config['default_tier'] ?? 'free';
    }

    /**
     * Get client identifier from request (IP + User Agent hash).
     */
    public static function getClientIdentifier(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy
            'HTTP_X_REAL_IP',        // Nginx proxy
            'REMOTE_ADDR'            // Direct
        ];

        $ip = '0.0.0.0';
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                break;
            }
        }

        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return md5($ip . $ua);
    }

    /**
     * Check if Redis is available.
     */
    public function isRedisAvailable(): bool
    {
        if (!$this->redisAvailable) {
            return false;
        }

        try {
            $this->redis->ping();
            return true;
        } catch (\Throwable) {
            $this->redisAvailable = false;
            return false;
        }
    }

    /**
     * Reset rate limit for a key (admin use).
     */
    public function reset(string $identifier, string $endpoint = 'api.default'): bool
    {
        $endpointConfig = $this->config['endpoints'][$endpoint] ?? $this->config['endpoints']['api.default'];
        $keyPrefix = $endpointConfig['key_prefix'] ?? 'api';
        $key = $this->prefix . $keyPrefix . ':' . $identifier;

        if ($this->redisAvailable && $this->redis) {
            try {
                $this->redis->del($key);
                return true;
            } catch (\Throwable) {
                // Fall through to file
            }
        }

        $file = $this->fallbackDir . '/' . md5($key) . '.json';
        if (file_exists($file)) {
            @unlink($file);
            return true;
        }

        return false;
    }

    /**
     * Get current usage stats for a key.
     */
    public function getUsage(string $identifier, string $endpoint = 'api.default'): array
    {
        $endpointConfig = $this->config['endpoints'][$endpoint] ?? $this->config['endpoints']['api.default'];
        $keyPrefix = $endpointConfig['key_prefix'] ?? 'api';
        $window = $endpointConfig['window'] ?? 60;
        $key = $this->prefix . $keyPrefix . ':' . $identifier;

        if ($this->redisAvailable && $this->redis) {
            try {
                $now = time();
                $this->redis->zRemRangeByScore($key, 0, $now - $window);
                $count = $this->redis->zCard($key);
                return ['current' => $count, 'window' => $window];
            } catch (\Throwable) {
                // Fall through
            }
        }

        $file = $this->fallbackDir . '/' . md5($key) . '.json';
        if (file_exists($file)) {
            $json = @file_get_contents($file);
            $decoded = @json_decode($json, true);
            if (is_array($decoded) && isset($decoded['requests'])) {
                $now = time();
                $valid = array_filter($decoded['requests'], fn($ts) => $ts > $now - $window);
                return ['current' => count($valid), 'window' => $window];
            }
        }

        return ['current' => 0, 'window' => $window];
    }
}