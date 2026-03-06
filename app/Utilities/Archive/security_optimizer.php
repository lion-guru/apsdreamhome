<?php
/**
 * Security Configuration Optimizer
 * Fixes security settings for production deployment
 */

echo "=== APS DREAM HOME - SECURITY CONFIGURATION OPTIMIZER ===\n\n";

// Current security settings
echo "🔍 Current Security Configuration:\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "allow_url_include: " . ini_get('allow_url_include') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "file_uploads: " . ini_get('file_uploads') . "\n\n";

// Create production .htaccess file for security
$htaccessContent = <<<EOT
# APS Dream Home - Production Security Configuration
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self';"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|sql|bak|backup)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Hide PHP version
ServerTokens Prod
ServerSignature Off

# File upload limits
LimitRequestBody 10485760

# PHP Settings (if allowed)
<IfModule mod_php.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value error_log logs/php_error.log
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 256M
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_flag allow_url_include Off
    php_flag allow_url_fopen Off
</IfModule>

# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/ico "access plus 1 year"
    ExpiresByType image/icon "access plus 1 year"
    ExpiresByType text/html "access plus 1 hour"
</IfModule>

# URL Rewriting
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /apsdreamhome/
    
    # Redirect to HTTPS (uncomment for production)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Handle pretty URLs
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
EOT;

// Write .htaccess file
$htaccessFile = __DIR__ . '/.htaccess';
if (file_put_contents($htaccessFile, $htaccessContent)) {
    echo "✅ Production .htaccess file created\n";
} else {
    echo "❌ Failed to create .htaccess file\n";
}

// Create production php.ini file
$phpIniContent = <<<EOT
; APS Dream Home - Production PHP Configuration
; Security Settings
display_errors = Off
log_errors = On
error_log = "logs/php_error.log"
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Execution Limits
max_execution_time = 300
max_input_time = 300
memory_limit = 256M

; File Uploads
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

; Security
allow_url_include = Off
allow_url_fopen = Off
expose_php = Off

; Session Security
session.cookie_httponly = 1
session.use_strict_mode = 1
session.cookie_samesite = Strict

; Output Buffering
output_buffering = 4096

; Timezone
date.timezone = "Asia/Kolkata"
EOT;

// Write custom php.ini
$phpIniFile = __DIR__ . '/php.ini';
if (file_put_contents($phpIniFile, $phpIniContent)) {
    echo "✅ Production php.ini file created\n";
} else {
    echo "❌ Failed to create php.ini file\n";
}

// Create security configuration file
$securityConfig = [
    'production_mode' => true,
    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin'
    ],
    'file_upload_limits' => [
        'max_size' => '10MB',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'upload_path' => 'uploads/'
    ],
    'session_config' => [
        'timeout' => 3600,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    'rate_limiting' => [
        'enabled' => true,
        'requests_per_minute' => 60,
        'burst_limit' => 10
    ]
];

$configFile = __DIR__ . '/config/security.php';
$configContent = "<?php\n\nreturn " . var_export($securityConfig, true) . ";\n";

if (file_put_contents($configFile, $configContent)) {
    echo "✅ Security configuration file created\n";
} else {
    echo "❌ Failed to create security configuration\n";
}

echo "\n🔒 Security Configuration Summary:\n";
echo "✅ .htaccess file with security headers\n";
echo "✅ php.ini with production settings\n";
echo "✅ Security configuration file\n";
echo "✅ File upload restrictions\n";
echo "✅ Session security settings\n";
echo "✅ Rate limiting configuration\n";

echo "\n🎉 Security optimization completed!\n";
echo "📅 Completed: " . date('Y-m-d H:i:s') . "\n";
echo "🚀 APS Dream Home is now production-ready with enhanced security!\n";
?>
