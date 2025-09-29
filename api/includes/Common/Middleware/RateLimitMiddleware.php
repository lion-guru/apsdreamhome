<?php
namespace App\Common\Middleware;

class RateLimitMiddleware {
    private $rateLimits = [
        'default' => [
            'limit' => 60,        // requests
            'window' => 60,       // seconds
            'message' => 'Too many requests. Please try again later.'
        ],
        'search' => [
            'limit' => 60,        // 60 requests per minute
            'window' => 60,
            'message' => 'Too many search requests. Please try again later.'
        ],
        'schedule_visit' => [
            'limit' => 10,        // 10 requests per hour
            'window' => 3600,
            'message' => 'Too many visit scheduling attempts. Please try again later.'
        ]
    ];

    public function handle(string $endpoint = 'default'): void {
        // Get rate limit config for the endpoint or use default
        $config = $this->rateLimits[$endpoint] ?? $this->rateLimits['default'];
        
        // Generate a unique key for this IP and endpoint
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip . '_' . $endpoint);
        
        // Initialize rate limit data if not exists
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'first_request' => time(),
                'reset_time' => time() + $config['window']
            ];
        }
        
        $rateData = &$_SESSION[$key];
        
        // Reset counter if window has passed
        if (time() > $rateData['reset_time']) {
            $rateData = [
                'count' => 0,
                'first_request' => time(),
                'reset_time' => time() + $config['window']
            ];
        }
        
        // Increment request count
        $rateData['count']++;
        
        // Check rate limit
        if ($rateData['count'] > $config['limit']) {
            $waitTime = $rateData['reset_time'] - time();
            
            // Set rate limit headers
            header('Retry-After: ' . $waitTime);
            header('X-RateLimit-Limit: ' . $config['limit']);
            header('X-RateLimit-Remaining: 0');
            header('X-RateLimit-Reset: ' . $rateData['reset_time']);
            
            // Log the rate limit event
            error_log(sprintf(
                'Rate limit exceeded for endpoint %s from IP %s. Limit: %d, Window: %ds',
                $endpoint,
                $ip,
                $config['limit'],
                $config['window']
            ));
            
            // Send error response
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => $config['message'],
                    'retry_after' => $waitTime,
                    'limit' => $config['limit'],
                    'remaining' => 0,
                    'reset' => $rateData['reset_time']
                ]
            ]);
            exit();
        }
        
        // Set rate limit headers for all responses
        header('X-RateLimit-Limit: ' . $config['limit']);
        header('X-RateLimit-Remaining: ' . max(0, $config['limit'] - $rateData['count']));
        header('X-RateLimit-Reset: ' . $rateData['reset_time']);
        
        // Store in globals for access in sendSecurityResponse
        $GLOBALS['rate_limit_config'] = [
            'limit' => $config['limit'],
            'remaining' => max(0, $config['limit'] - $rateData['count']),
            'reset' => $rateData['reset_time']
        ];
    }
}
