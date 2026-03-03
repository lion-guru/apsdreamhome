<?php
/**
 * Rate Limit Middleware
 */

namespace App\Http\Middleware;

class RateLimit
{
    /**
     * Handle an incoming request
     */
    public function handle($request, $next)
    {
        $clientIp = $this->getClientIp();
        $key = 'rate_limit_' . $clientIp;

        // Check rate limit
        if ($this->isRateLimited($key)) {
            return $this->rateLimitExceededResponse();
        }

        // Increment rate limit counter
        $this->incrementRateLimit($key);

        return $next($request);
    }

    /**
     * Get client IP address
     */
    protected function getClientIp()
    {
        $ipKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Check if rate limited
     */
    protected function isRateLimited($key)
    {
        $config = require APP_ROOT . '/app/config/security.php';
        $rateLimit = $config['rate_limiting'];

        // Use session for rate limiting (simplified)
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $sessionData = $_SESSION[$key];
        
        // Reset if time window has passed
        if (time() - $sessionData['first_attempt'] > ($rateLimit['decay_minutes'] * 60)) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
            return false;
        }

        return $sessionData['attempts'] >= $rateLimit['max_attempts'];
    }

    /**
     * Increment rate limit counter
     */
    protected function incrementRateLimit($key)
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $_SESSION[$key]['attempts']++;
    }

    /**
     * Rate limit exceeded response
     */
    protected function rateLimitExceededResponse()
    {
        header('HTTP/1.1 429 Too Many Requests');
        header('Content-Type: application/json');
        header('Retry-After: 60'); // Suggest retry after 60 seconds
        
        echo json_encode([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.'
        ]);
        exit;
    }
}
