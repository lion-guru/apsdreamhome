<?php
/**
 * Rate Limit Middleware
 * 
 * Prevents brute force attacks by limiting request rates
 * - Auth endpoints: 5 requests per minute
 * - API endpoints: 60 requests per minute
 * - Web endpoints: 100 requests per minute
 */

namespace App\Core\Middleware;

use App\Core\Database\Database;

class RateLimitMiddleware
{
    private $db;
    private $limits = [
        'auth' => ['limit' => 10, 'window' => 60],      // 10 per minute
        'api' => ['limit' => 120, 'window' => 60],      // 120 per minute
        'web' => ['limit' => 600, 'window' => 60],      // 600 per minute
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if request is within rate limit
     */
    public function check(string $type = 'web'): bool
    {
        $limit = $this->limits[$type] ?? $this->limits['web'];
        $identifier = $this->getIdentifier();
        $key = "rate_limit:{$type}:{$identifier}";
        $window = $limit['window'];
        $maxRequests = $limit['limit'];

        // Clean old entries
        $this->cleanup($key, $window);

        // Count current requests
        $current = $this->count($key);

        if ($current >= $maxRequests) {
            return false;
        }

        // Log this request
        $this->log($key, $window);

        return true;
    }

    /**
     * Get client identifier (IP + User Agent hash)
     */
    private function getIdentifier(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return md5($ip . $ua);
    }

    /**
     * Clean old rate limit entries
     */
    private function cleanup(string $key, int $window): void
    {
        $cutoff = time() - $window;
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM rate_limit_logs WHERE request_key = ? AND created_at < ?"
            );
            $stmt->execute([$key, date('Y-m-d H:i:s', $cutoff)]);
        } catch (\Throwable $e) {
            // Silently fail - rate limiting should not break app
        }
    }

    /**
     * Count requests in current window
     */
    private function count(string $key): int
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM rate_limit_logs WHERE request_key = ?"
            );
            $stmt->execute([$key]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Log a request
     */
    private function log(string $key, int $window): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO rate_limit_logs (request_key, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $key,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                date('Y-m-d H:i:s', time() + $window)
            ]);
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    /**
     * Get remaining requests
     */
    public function getRemaining(string $type = 'web'): int
    {
        $limit = $this->limits[$type] ?? $this->limits['web'];
        $identifier = $this->getIdentifier();
        $key = "rate_limit:{$type}:{$identifier}";
        $current = $this->count($key);
        return max(0, $limit['limit'] - $current);
    }
}
