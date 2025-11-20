\u003c?php
/**
 * Security Configuration
 * Centralized security settings and constants
 */

// CSRF Protection
$config['security'] = [
    'csrf' =\u003e [
        'enabled' =\u003e true,
        'token_name' =\u003e 'csrf_token',
        'timeout' =\u003e 3600, // 1 hour
        'regenerate' =\u003e true,
    ],

    // Rate limiting
    'rate_limiting' =\u003e [
        'enabled' =\u003e true,
        'max_attempts' =\u003e [
            'login' =\u003e 5,
            'password_reset' =\u003e 3,
            'contact_form' =\u003e 10,
            'api' =\u003e 100,
        ],
        'decay_minutes' =\u003e [
            'login' =\u003e 15,
            'password_reset' =\u003e 60,
            'contact_form' =\u003e 5,
            'api' =\u003e 1,
        ],
    ],

    // Session security
    'session' =\u003e [
        'secure' =\u003e isset($_SERVER['HTTPS']),
        'httponly' =\u003e true,
        'samesite' =\u003e 'Lax',
        'lifetime' =\u003e 7200, // 2 hours
        'domain' =\u003e null,
        'path' =\u003e '/',
    ],

    // Password requirements
    'password' =\u003e [
        'min_length' =\u003e 8,
        'require_uppercase' =\u003e true,
        'require_lowercase' =\u003e true,
        'require_numbers' =\u003e true,
        'require_symbols' =\u003e false,
        'max_age' =\u003e 90, // days
    ],

    // File upload security
    'upload' =\u003e [
        'allowed_extensions' =\u003e ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'max_size' =\u003e 10 * 1024 * 1024, // 10MB
        'scan_viruses' =\u003e true,
        'sanitize_names' =\u003e true,
    ],

    // Headers security
    'headers' =\u003e [
        'x_frame_options' =\u003e 'SAMEORIGIN',
        'x_content_type_options' =\u003e 'nosniff',
        'x_xss_protection' =\u003e '1; mode=block',
        'strict_transport_security' =\u003e 'max-age=31536000; includeSubDomains',
        'content_security_policy' =\u003e "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; connect-src 'self'",
        'referrer_policy' =\u003e 'strict-origin-when-cross-origin',
    ],

    // Input sanitization
    'sanitization' =\u003e [
        'html_purifier' =\u003e true,
        'strip_tags' =\u003e true,
        'escape_html' =\u003e true,
        'validate_urls' =\u003e true,
    ],

    // Authentication
    'auth' =\u003e [
        'remember_me' =\u003e true,
        'two_factor' =\u003e false,
        'password_reset' =\u003e true,
        'email_verification' =\u003e false,
        'account_lockout' =\u003e [
            'enabled' =\u003e true,
            'attempts' =\u003e 5,
            'lockout_duration' =\u003e 900, // 15 minutes
        ],
    ],

    // API Security
    'api' =\u003e [
        'require_auth' =\u003e false,
        'rate_limiting' =\u003e true,
        'throttle_requests' =\u003e true,
        'validate_tokens' =\u003e true,
        'cors' =\u003e [
            'enabled' =\u003e true,
            'origins' =\u003e ['*'],
            'methods' =\u003e ['GET', 'POST', 'PUT', 'DELETE'],
            'headers' =\u003e ['Content-Type', 'Authorization'],
        ],
    ],

    // Database security
    'database' =\u003e [
        'prepared_statements' =\u003e true,
        'parameter_binding' =\u003e true,
        'connection_pooling' =\u003e true,
        'query_logging' =\u003e ENVIRONMENT === 'development',
        'slow_query_threshold' =\u003e 1000, // milliseconds
    ],
];

// Initialize security headers
if (!headers_sent()) {
    $map = [
        'x_frame_options' =\u003e 'X-Frame-Options',
        'x_content_type_options' =\u003e 'X-Content-Type-Options',
        'x_xss_protection' =\u003e 'X-XSS-Protection',
        'strict_transport_security' =\u003e 'Strict-Transport-Security',
        'content_security_policy' =\u003e 'Content-Security-Policy',
        'referrer_policy' =\u003e 'Referrer-Policy',
    ];
    foreach ($config['security']['headers'] as $key =\u003e $value) {
        $name = $map[$key] ?? str_replace('_', '-', $key);
        header($name . ': ' . $value);
    }
}

// Security utility functions
if (!class_exists('SecurityHelper')) {
    class SecurityHelper {
        public static function generateCSRFToken() {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) \u0026\u0026 hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitizeInput($input, $type = 'string') {
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

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' =\u003e 65536,
            'time_cost' =\u003e 4,
            'threads' =\u003e 3,
        ]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}
}

return $config;

?\u003e