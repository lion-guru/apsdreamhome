<?php
/**
 * Throttle Login Middleware
 */

namespace App\Http\Middleware;

class ThrottleLogin
{
    /**
     * Handle an incoming request
     */
    public function handle($request, $next)
    {
        // Only apply to login attempts
        if ($this->isLoginRequest($request)) {
            $clientIp = $this->getClientIp();
            $key = 'login_throttle_' . $clientIp;

            // Check login throttling
            if ($this->isLoginThrottled($key)) {
                return $this->loginThrottledResponse();
            }

            // Increment login attempt counter
            $this->incrementLoginAttempts($key);
        }

        return $next($request);
    }

    /**
     * Check if this is a login request
     */
    protected function isLoginRequest($request)
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        return ($method === 'POST') && 
               (strpos($uri, '/login') !== false || strpos($uri, '/auth/login') !== false);
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
     * Check if login is throttled
     */
    protected function isLoginThrottled($key)
    {
        $maxAttempts = 5;
        $decayMinutes = 15; // 15 minutes for login attempts

        // Use session for login throttling
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }

        $sessionData = $_SESSION[$key];
        
        // Reset if time window has passed
        if (time() - $sessionData['first_attempt'] > ($decayMinutes * 60)) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
            return false;
        }

        return $sessionData['attempts'] >= $maxAttempts;
    }

    /**
     * Increment login attempt counter
     */
    protected function incrementLoginAttempts($key)
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
     * Login throttled response
     */
    protected function loginThrottledResponse()
    {
        header('HTTP/1.1 429 Too Many Requests');
        header('Content-Type: application/json');
        header('Retry-After: 900'); // Suggest retry after 15 minutes
        
        echo json_encode([
            'error' => 'Too Many Login Attempts',
            'message' => 'Too many failed login attempts. Please try again in 15 minutes.'
        ]);
        exit;
    }
}
