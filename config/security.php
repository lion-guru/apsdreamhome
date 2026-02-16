<?php

/**
 * Security Configuration
 * Centralized security settings and constants
 */

// CSRF Protection
$config['security'] = [
    'csrf' => [
        'enabled' => true,
        'token_name' => 'csrf_token',
        'timeout' => 3600, // 1 hour
        'regenerate' => true,
    ],

    // Rate limiting
    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => [
            'login' => 5,
            'password_reset' => 3,
            'contact_form' => 10,
            'api' => 100,
        ],
        'decay_minutes' => [
            'login' => 15,
            'password_reset' => 60,
            'contact_form' => 5,
            'api' => 1,
        ],
    ],

    // Session security
    'session' => [
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
        'lifetime' => 7200, // 2 hours
        'domain' => null,
        'path' => '/',
    ],

    // Password requirements
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'max_age' => 90, // days
    ],

    // File upload security
    'upload' => [
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'max_size' => 10 * 1024 * 1024, // 10MB
        'scan_viruses' => true,
        'sanitize_names' => true,
    ],

    // Headers security
    'headers' => [
        'x_frame_options' => 'SAMEORIGIN',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
        'content_security_policy' => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; connect-src 'self'",
        'referrer_policy' => 'strict-origin-when-cross-origin',
    ],

    // Input sanitization
    'sanitization' => [
        'html_purifier' => true,
        'strip_tags' => true,
        'escape_html' => true,
        'validate_urls' => true,
    ],

    // Authentication
    'auth' => [
        'remember_me' => true,
        'two_factor' => false,
        'password_reset' => true,
        'email_verification' => false,
        'account_lockout' => [
            'enabled' => true,
            'attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
        ],
    ],

    // API Security
    'api' => [
        'require_auth' => false,
        'rate_limiting' => true,
        'throttle_requests' => true,
        'validate_tokens' => true,
        'cors' => [
            'enabled' => true,
            'origins' => ['*'],
            'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'headers' => ['Content-Type', 'Authorization'],
        ],
    ],

    // Database security
    'database' => [
        'prepared_statements' => true,
        'parameter_binding' => true,
        'connection_pooling' => true,
        'query_logging' => ENVIRONMENT === 'development',
        'slow_query_threshold' => 1000, // milliseconds
    ],
];

// Initialize security headers
if (!headers_sent()) {
    $map = [
        'x_frame_options' => 'X-Frame-Options',
        'x_content_type_options' => 'X-Content-Type-Options',
        'x_xss_protection' => 'X-XSS-Protection',
        'strict_transport_security' => 'Strict-Transport-Security',
        'content_security_policy' => 'Content-Security-Policy',
        'referrer_policy' => 'Referrer-Policy',
    ];
    if (isset($config['security']['headers'])) {
        foreach ($config['security']['headers'] as $key => $value) {
            $name = $map[$key] ?? str_replace('_', '-', $key);
            header($name . ': ' . $value);
        }
    }
}

// Security utility functions
if (!class_exists('SecurityHelper')) {
    class SecurityHelper
    {
        public static function generateCSRFToken()
        {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }

        public static function validateCSRFToken($token)
        {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }

        public static function sanitizeInput($input, $type = 'string')
        {
            switch ($type) {
                case 'email':
                    return filter_var($input, FILTER_SANITIZE_EMAIL);
                case 'url':
                    return filter_var($input, FILTER_SANITIZE_URL);
                case 'int':
                    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                case 'float':
                    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                case 'string':
                default:
                    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
            }
        }

        public static function hashPassword($password)
        {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3,
            ]);
        }

        public static function verifyPassword($password, $hash)
        {
            return password_verify($password, $hash);
        }

        public static function generateSecureToken($length = 32)
        {
            return bin2hex(random_bytes($length));
        }
    }
}

return isset($config) ? $config : [];
