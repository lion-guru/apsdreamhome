<?php

namespace App\Services\Security\Legacy;
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
?>
