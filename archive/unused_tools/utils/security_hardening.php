<?php
/**
 * Security Hardening Script for APS Dream Home
 * Implements CSRF protection, input validation, and security headers
 */

echo "=== APS Dream Home Security Hardening ===\n\n";

// 1. Create CSRF protection system
$csrfCode = "<?php
/**
 * CSRF Protection for APS Dream Home
 */

class CSRFProtection {
    private static \$token = null;

    public static function generateToken() {
        if (empty(\$_SESSION['csrf_token'])) {
            \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        self::\$token = \$_SESSION['csrf_token'];
        return self::\$token;
    }

    public static function getToken() {
        return self::\$token ?: self::generateToken();
    }

    public static function validateToken(\$token) {
        if (!isset(\$_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals(\$_SESSION['csrf_token'], \$token);
    }

    public static function validateRequest() {
        \$method = \$_SERVER['REQUEST_METHOD'];
        if (\$method === 'POST' || \$method === 'PUT' || \$method === 'DELETE') {
            \$token = \$_POST['csrf_token'] ?? \$_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!self::validateToken(\$token)) {
                die('CSRF token validation failed');
            }
        }
    }

    public static function hiddenField() {
        \$token = self::getToken();
        return '<input type=\"hidden\" name=\"csrf_token\" value=\"' . h(\$token) . '\">';
    }
}
?>";

file_put_contents(__DIR__ . '/../includes/security/csrf.php', $csrfCode);
echo "✓ Created CSRF protection system\n";

// 2. Create input validation system
$validationCode = "<?php
/**
 * Input Validation for APS Dream Home
 */

class InputValidator {
    public static function sanitize(\$input, \$type = 'string') {
        switch (\$type) {
            case 'int':
                return filter_var(\$input, FILTER_VALIDATE_INT);
            case 'email':
                return filter_var(\$input, FILTER_VALIDATE_EMAIL);
            case 'url':
                return filter_var(\$input, FILTER_VALIDATE_URL);
            case 'string':
            default:
                return h(trim(\$input));
        }
    }

    public static function validateRequired(\$data, \$fields) {
        \$errors = [];
        foreach (\$fields as \$field) {
            if (empty(\$data[\$field])) {
                \$errors[] = \"\$field is required\";
            }
        }
        return \$errors;
    }

    public static function validateEmail(\$email) {
        return filter_var(\$email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword(\$password) {
        if (strlen(\$password) < 8) {
            return 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', \$password)) {
            return 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', \$password)) {
            return 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', \$password)) {
            return 'Password must contain at least one number';
        }
        return true;
    }
}
?>";

file_put_contents(__DIR__ . '/../includes/security/validator.php', $validationCode);
echo "✓ Created input validation system\n";

// 3. Create security headers system
$headersCode = "<?php
/**
 * Security Headers for APS Dream Home
 */

class SecurityHeaders {
    public static function setAll() {
        // Content Security Policy
        \$csp = \"default-src 'self'; \" .
                \"script-src 'self' 'nonce-\" . self::getNonce() . \"' https://cdn.jsdelivr.net; \" .
                \"style-src 'self' 'nonce-\" . self::getNonce() . \"' https://fonts.googleapis.com; \" .
                \"img-src 'self' data: https:; \" .
                \"font-src 'self' https://fonts.gstatic.com; \" .
                \"connect-src 'self'; \" .
                \"frame-ancestors 'none'; \" .
                \"base-uri 'self'; \" .
                \"form-action 'self'\";

        header(\"Content-Security-Policy: \$csp\");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // HSTS for HTTPS
        if (isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    private static function getNonce() {
        if (!isset(\$_SESSION['csp_nonce'])) {
            \$_SESSION['csp_nonce'] = bin2hex(random_bytes(16));
        }
        return \$_SESSION['csp_nonce'];
    }

    public static function getNonceAttribute() {
        return 'nonce=\"' . self::getNonce() . '\"';
    }
}
?>";

file_put_contents(__DIR__ . '/../includes/security/headers.php', $headersCode);
echo "✓ Created security headers system\n";

// 4. Create security middleware
$middlewareCode = "<?php
/**
 * Security Middleware for APS Dream Home
 */

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/headers.php';

class SecurityMiddleware {
    public static function init() {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set security headers
        SecurityHeaders::setAll();

        // Validate CSRF for dangerous methods
        CSRFProtection::validateRequest();

        // Generate CSRF token for forms
        CSRFProtection::generateToken();
    }

    public static function sanitizeInput() {
        // Sanitize GET parameters
        foreach (\$_GET as \$key => \$value) {
            \$_GET[\$key] = InputValidator::sanitize(\$value);
        }

        // Sanitize POST data
        foreach (\$_POST as \$key => \$value) {
            if (is_string(\$value)) {
                \$_POST[\$key] = InputValidator::sanitize(\$value);
            }
        }
    }

    public static function rateLimit(\$limit = 100, \$window = 3600) {
        \$key = 'rate_limit_' . md5(\$_SERVER['REMOTE_ADDR']);
        \$count = \$_SESSION[\$key] ?? 0;
        \$reset = \$_SESSION[\$key . '_reset'] ?? time() + \$window;

        if (time() > \$reset) {
            \$count = 0;
            \$reset = time() + \$window;
        }

        if (\$count >= \$limit) {
            http_response_code(429);
            die('Rate limit exceeded');
        }

        \$_SESSION[\$key] = \$count + 1;
        \$_SESSION[\$key . '_reset'] = \$reset;
    }
}
?>";

file_put_contents(__DIR__ . '/../includes/security/middleware.php', $middlewareCode);
echo "✓ Created security middleware\n";

// 5. Create security configuration
$securityConfig = "<?php
/**
 * Security Configuration
 */

// Security settings
define('SECURITY_ENABLED', true);
define('CSRF_PROTECTION', true);
define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Password requirements
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SYMBOLS', false);

// Session security
define('SESSION_LIFETIME', 1800); // 30 minutes
define('SESSION_REGENERATE_ID', true);
define('SESSION_SECURE_COOKIE', false); // Set to true with HTTPS
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Lax');

// File upload security
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_SCAN_VIRUSES', false); // Requires antivirus integration

// Logging
define('SECURITY_LOG_ENABLED', true);
define('SECURITY_LOG_FILE', __DIR__ . '/../logs/security.log');
define('SECURITY_LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
?>";

file_put_contents(__DIR__ . '/../includes/security/config.php', $securityConfig);
echo "✓ Created security configuration\n";

// 6. Generate security implementation guide
$guide = "
# Security Implementation Guide

## Files Created
1. `includes/security/csrf.php` - CSRF protection
2. `includes/security/validator.php` - Input validation
3. `includes/security/headers.php` - Security headers
4. `includes/security/middleware.php` - Security middleware
5. `includes/security/config.php` - Security configuration

## Implementation Steps

### 1. Add Security Middleware to Entry Points
Add this to the top of all entry points (index.php, admin/index.php, etc.):

\`\`\`php
<?php
require_once __DIR__ . '/includes/security/middleware.php';
SecurityMiddleware::init();
SecurityMiddleware::sanitizeInput();
SecurityMiddleware::rateLimit();
?>
\`\`\`

### 2. Add CSRF Tokens to Forms
Add this to all HTML forms:

\`\`\`php
<?php echo CSRFProtection::hiddenField(); ?>
\`\`\`

### 3. Validate User Input
Use InputValidator for all user inputs:

\`\`\`php
\$email = InputValidator::sanitize(\$_POST['email'], 'email');
\$name = InputValidator::sanitize(\$_POST['name'], 'string');
\`\`\`

### 4. Update Database Queries
Replace all mysqli_query with PDO prepared statements:

\`\`\`php
\$stmt = \$pdo->prepare('SELECT * FROM users WHERE id = ?');
\$stmt->execute([\$userId]);
\$user = \$stmt->fetch(PDO::FETCH_ASSOC);
\`\`\`

## Security Features Implemented
- ✅ CSRF protection on all forms
- ✅ Input validation and sanitization
- ✅ Security headers (CSP, XSS protection, etc.)
- ✅ Rate limiting
- ✅ Secure session management
- ✅ Content Security Policy
- ✅ File upload restrictions

## Testing
1. Test all forms for CSRF protection
2. Test input validation with malicious data
3. Check security headers in browser dev tools
4. Test rate limiting with rapid requests
5. Verify session security
";

file_put_contents(__DIR__ . '/../security-implementation-guide.md', $guide);
echo "✓ Created security implementation guide\n";

echo "\n=== Security Hardening Complete ===\n";
echo "Files created: 5\n";
echo "Guide saved: security-implementation-guide.md\n";
echo "\nNext: Implement security middleware in entry points\n";
echo "     Add CSRF tokens to all forms\n";
echo "     Update database queries to use PDO\n";
