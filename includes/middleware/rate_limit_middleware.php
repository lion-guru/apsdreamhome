<?php
/**
 * Rate Limiting Middleware
 * Applies rate limiting to API endpoints
 */

require_once __DIR__ . '/../rate_limiter.php';

class RateLimitMiddleware {
    private $rateLimiter;
    private $enabled = true;

    public function __construct($rateLimiter = null) {
        $this->rateLimiter = $rateLimiter ?? new RateLimiter();
    }

    /**
     * Handle the rate limiting
     */
    public function handle($type = 'api') {
        if (!$this->enabled) {
            return true;
        }

        // Skip rate limiting for whitelisted IPs (e.g., internal tools)
        if ($this->isWhitelisted()) {
            return true;
        }

        // Get unique key for current request
        $key = $this->getRequestKey();
        
        // Check rate limit
        $result = $this->rateLimiter->checkLimit($key, $type);
        
        if ($result['limited']) {
            // Set rate limit headers
            $this->setRateLimitHeaders($result);
            
            // Return 429 Too Many Requests
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $result['retry_after']
            ]);
            exit;
        }

        // Set rate limit headers even for successful requests
        $this->setRateLimitHeaders($result);
        return true;
    }

    /**
     * Set rate limit headers
     */
    private function setRateLimitHeaders($result) {
        header('X-RateLimit-Limit: ' . ($result['remaining'] + $result['count']));
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        header('X-RateLimit-Reset: ' . $result['reset']);
        
        if ($result['limited']) {
            header('Retry-After: ' . $result['retry_after']);
        }
    }

    /**
     * Get unique key for current request
     */
    private function getRequestKey() {
        $parts = [
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'
        ];
        
        return implode(':', $parts);
    }

    /**
     * Check if current IP is whitelisted
     */
    private function isWhitelisted() {
        $whitelistedIPs = [
            '127.0.0.1',
            '::1'
        ];

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($ip, $whitelistedIPs);
    }

    /**
     * Enable/disable rate limiting
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }
}

// Create global middleware instance
$rateLimitMiddleware = new RateLimitMiddleware($rateLimiter ?? null);
