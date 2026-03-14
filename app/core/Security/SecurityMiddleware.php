<?php

namespace App\Core\Security;

/**
 * Security Middleware Class
 * Handles all security-related operations
 */
class SecurityMiddleware
{
    private static $instance = null;
    private $config = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->config = [
            'max_login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'session_timeout' => 3600, // 1 hour
            'csrf_token_lifetime' => 3600,
            'rate_limit' => [
                'requests_per_minute' => 60,
                'burst_limit' => 10
            ]
        ];
    }

    /**
     * Initialize security measures
     */
    public function initialize()
    {
        // Set secure headers
        $this->setSecurityHeaders();
        
        // Start secure session
        $this->startSecureSession();
        
        // Initialize CSRF protection
        $this->initializeCSRF();
        
        // Rate limiting
        $this->checkRateLimit();
        
        // Check for suspicious activity
        $this->checkSuspiciousActivity();
    }

    /**
     * Set security headers
     */
    private function setSecurityHeaders()
    {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Force HTTPS in production
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self'");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    /**
     * Start secure session
     */
    private function startSecureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
            
            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regeneration']) || 
                time() - $_SESSION['last_regeneration'] > 300) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
            
            // Set session timeout
            $_SESSION['last_activity'] = time();
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && 
            time() - $_SESSION['last_activity'] > $this->config['session_timeout']) {
            $this->destroySession();
            $this->redirect('/login?timeout=1');
        }
        
        $_SESSION['last_activity'] = time();
    }

    /**
     * Initialize CSRF protection
     */
    private function initializeCSRF()
    {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            time() - $_SESSION['csrf_token_time'] > $this->config['csrf_token_lifetime']) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRF($token)
    {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            time() - $_SESSION['csrf_token_time'] > $this->config['csrf_token_lifetime']) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token
     */
    public function getCSRFToken()
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Check rate limiting
     */
    private function checkRateLimit()
    {
        $clientIP = $this->getClientIP();
        $key = 'rate_limit_' . md5($clientIP);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'requests' => 0,
                'reset_time' => time() + 60,
                'burst_count' => 0,
                'burst_reset' => time() + 10
            ];
        }
        
        $rateData = $_SESSION[$key];
        
        // Reset counters if time passed
        if (time() > $rateData['reset_time']) {
            $rateData['requests'] = 0;
            $rateData['reset_time'] = time() + 60;
        }
        
        if (time() > $rateData['burst_reset']) {
            $rateData['burst_count'] = 0;
            $rateData['burst_reset'] = time() + 10;
        }
        
        // Check limits
        if ($rateData['requests'] >= $this->config['rate_limit']['requests_per_minute']) {
            $this->blockRequest('Rate limit exceeded');
        }
        
        if ($rateData['burst_count'] >= $this->config['rate_limit']['burst_limit']) {
            $this->blockRequest('Too many requests in short time');
        }
        
        // Increment counters
        $rateData['requests']++;
        $rateData['burst_count']++;
        $_SESSION[$key] = $rateData;
    }

    /**
     * Check for suspicious activity
     */
    private function checkSuspiciousActivity()
    {
        $clientIP = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Check for common attack patterns
        $suspiciousPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i'
        ];
        
        foreach (['GET', 'POST', 'REQUEST'] as $method) {
            $input = $_SERVER['REQUEST_METHOD'] === $method ? $_REQUEST : [];
            
            foreach ($input as $key => $value) {
                if (is_string($value)) {
                    foreach ($suspiciousPatterns as $pattern) {
                        if (preg_match($pattern, $value)) {
                            $this->logSecurityEvent('SUSPICIOUS_INPUT', [
                                'ip' => $clientIP,
                                'method' => $method,
                                'field' => $key,
                                'value' => substr($value, 0, 100),
                                'pattern' => $pattern
                            ]);
                            $this->blockRequest('Suspicious input detected');
                        }
                    }
                }
            }
        }
        
        // Check for unusual user agents
        $botPatterns = [
            '/bot/i', '/crawler/i', '/spider/i', '/scraper/i'
        ];
        
        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent) && !str_contains($userAgent, 'Googlebot')) {
                $this->logSecurityEvent('SUSPICIOUS_USER_AGENT', [
                    'ip' => $clientIP,
                    'user_agent' => $userAgent
                ]);
            }
        }
    }

    /**
     * Block request with security response
     */
    private function blockRequest($reason)
    {
        $this->logSecurityEvent('REQUEST_BLOCKED', [
            'ip' => $this->getClientIP(),
            'reason' => $reason,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ]);
        
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Your request has been blocked for security reasons.</p>';
        exit;
    }

    /**
     * Log security events
     */
    private function logSecurityEvent($event, $data)
    {
        $logEntry = sprintf(
            "[%s] SECURITY: %s | IP: %s | URI: %s | Data: %s\n",
            date('Y-m-d H:i:s'),
            $event,
            $data['ip'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown',
            json_encode($data)
        );
        
        error_log($logEntry, 3, __DIR__ . '/../../../logs/security.log');
    }

    /**
     * Get client IP address
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }

    /**
     * Destroy session securely
     */
    private function destroySession()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Secure redirect
     */
    private function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Input sanitization
     */
    public function sanitize($input, $type = 'string')
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Password hashing
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]);
    }

    /**
     * Password verification
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure token
     */
    public function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}


