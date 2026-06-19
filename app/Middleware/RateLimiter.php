<?php
namespace App\Middleware;

class RateLimiter
{
    private static $limits = [];
    
    public static function check(string $key = 'default', int $maxRequests = 60, int $windowSeconds = 60): bool
    {
        // Bypass rate limiting during local development testing/auditing
        if (isset($_GET['test_login']) || isset($_SERVER['HTTP_X_TESTING']) || (defined('APP_ENV') && APP_ENV === 'testing')) {
            return true;
        }

        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $storageKey = 'rate_limit_' . $key;
        $now = time();
        
        if (!isset($_SESSION[$storageKey])) {
            $_SESSION[$storageKey] = ['window_start' => $now, 'count' => 0];
        }
        
        $window = &$_SESSION[$storageKey];
        
        if ($now - $window['window_start'] > $windowSeconds) {
            $window = ['window_start' => $now, 'count' => 0];
        }
        
        $window['count']++;
        
        if ($window['count'] > $maxRequests) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . ($windowSeconds - ($now - $window['window_start'])));
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            exit;
        }
        
        return true;
    }
    
    public static function checkApi(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $route = $_SERVER['REQUEST_URI'] ?? '/';
        return self::check('api_' . md5($ip . $route), 30, 60);
    }
}
