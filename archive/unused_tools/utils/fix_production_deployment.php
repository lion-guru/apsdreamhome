<?php
/**
 * APS Dream Home - Production Deployment Fixer
 * Fix critical issues for production deployment
 */

require_once 'includes/config.php';

class ProductionDeploymentFixer {
    private $conn;
    private $fixes = [];
    
    public function __construct() {
        $this->conn = $this->getConnection();
        $this->initFixes();
    }
    
    /**
     * Initialize production fixes
     */
    private function initFixes() {
        echo "<h1>🔧 APS Dream Home - Production Deployment Fixes</h1>\n";
        echo "<div class='fix-container'>\n";
        
        // Fix database issues
        $this->fixDatabaseIssues();
        
        // Fix missing extensions
        $this->fixExtensionIssues();
        
        // Setup SSL configuration
        $this->setupSSLConfiguration();
        
        // Optimize production settings
        $this->optimizeProductionSettings();
        
        // Setup security headers
        $this->setupSecurityHeaders();
        
        echo "</div>\n";
    }
    
    /**
     * Fix database issues
     */
    private function fixDatabaseIssues() {
        echo "<h2>🗄️ Fixing Database Issues</h2>\n";
        
        // Fix missing visit_time column
        $fixes = [
            'visit_time_column' => "
                ALTER TABLE user_analytics 
                ADD COLUMN IF NOT EXISTS visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ",
            'performance_indexes' => "
                ALTER TABLE performance_metrics 
                ADD INDEX IF NOT EXISTS idx_metric_time_type (metric_type, timestamp)
            ",
            'cache_optimization' => "
                ALTER TABLE cache_entries 
                ADD INDEX IF NOT EXISTS idx_expiration_status (expiration_time, status)
            "
        ];
        
        foreach ($fixes as $fixName => $sql) {
            try {
                $result = $this->conn->query($sql);
                echo "<div style='color: green;'>✅ Fixed: {$fixName}</div>\n";
                $this->fixes[] = $fixName;
            } catch (Exception $e) {
                echo "<div style='color: orange;'>⚠️ {$fixName}: " . $e->getMessage() . "</div>\n";
            }
        }
    }
    
    /**
     * Fix extension issues
     */
    private function fixExtensionIssues() {
        echo "<h2>🔌 Fixing Extension Issues</h2>\n";
        
        // Check for GD extension
        if (!extension_loaded('gd')) {
            echo "<div style='color: red;'>❌ GD Extension Missing</div>\n";
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>\n";
            echo "<h4>🔧 To Fix GD Extension:</h4>\n";
            echo "<p><strong>Ubuntu/Debian:</strong> sudo apt-get install php-gd</p>\n";
            echo "<p><strong>CentOS/RHEL:</strong> sudo yum install php-gd</p>\n";
            echo "<p><strong>Windows:</strong> Uncomment extension=gd in php.ini</p>\n";
            echo "<p><strong>After installation, restart Apache/Nginx</strong></p>\n";
            echo "</div>\n";
        } else {
            echo "<div style='color: green;'>✅ GD Extension Available</div>\n";
        }
        
        // Check for other required extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring', 'openssl'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo "<div style='color: green;'>✅ {$ext} Extension Available</div>\n";
            } else {
                echo "<div style='color: red;'>❌ {$ext} Extension Missing</div>\n";
            }
        }
    }
    
    /**
     * Setup SSL configuration
     */
    private function setupSSLConfiguration() {
        echo "<h2>🔒 Setting Up SSL Configuration</h2>\n";
        
        // Create SSL configuration file
        $sslConfig = "<?php
/**
 * SSL Configuration for APS Dream Home
 */

// Force HTTPS in production
if (\$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' || \$_SERVER['HTTPS'] === 'on') {
    \$_SERVER['HTTPS'] = 'on';
}

// Redirect to HTTPS if not in development
if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'development') {
    if (!isset(\$_SERVER['HTTPS']) || \$_SERVER['HTTPS'] !== 'on') {
        \$redirect_url = 'https://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI'];
        header('Location: ' . \$redirect_url);
        exit();
    }
}

// Set secure headers
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Set secure cookie parameters
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
?>";
        
        file_put_contents(__DIR__ . '/../includes/ssl_config.php', $sslConfig);
        echo "<div style='color: green;'>✅ SSL Configuration Created: includes/ssl_config.php</div>\n";
        
        // Create .htaccess for HTTPS
        $htaccessContent = "# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
    Header always set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;\"
</IfModule>";
        
        file_put_contents(__DIR__ . '/../.htaccess_https', $htaccessContent);
        echo "<div style='color: green;'>✅ HTTPS .htaccess Created: .htaccess_https</div>\n";
        
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h4>🔧 To Complete SSL Setup:</h4>\n";
        echo "<p>1. Install SSL certificate (Let's Encrypt recommended)</p>\n";
        echo "<p>2. Replace .htaccess with .htaccess_https content</p>\n";
        echo "<p>3. Include ssl_config.php in your main index files</p>\n";
        echo "</div>\n";
    }
    
    /**
     * Optimize production settings
     */
    private function optimizeProductionSettings() {
        echo "<h2>⚡ Optimizing Production Settings</h2>\n";
        
        // Create production php.ini recommendations
        $phpIniConfig = "; APS Dream Home - Production PHP Configuration
; Memory and Performance
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; File Uploads
upload_max_filesize = 40M
post_max_size = 40M
max_file_uploads = 20

; Session Security
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.use_strict_mode = 1

; Error Reporting (Production)
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; OPcache Configuration
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.validate_timestamps = 0

; Database Connection Pooling
mysqli.allow_persistent = On
mysqli.max_persistent = 10
mysqli.max_links = 100";
        
        file_put_contents(__DIR__ . '/php_production.ini', $phpIniConfig);
        echo "<div style='color: green;'>✅ Production PHP Configuration Created: tools/php_production.ini</div>\n";
        
        // Create production environment file
        $envConfig = "<?php
/**
 * Production Environment Configuration
 */

define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('ERROR_LOGGING', true);

// Database Configuration (Production)
define('DB_HOST', 'your_production_db_host');
define('DB_NAME', 'apsdreamhome_production');
define('DB_USER', 'your_production_db_user');
define('DB_PASSWORD', 'your_production_db_password');

// Security Configuration
define('ENCRYPTION_KEY', 'your_32_character_encryption_key_here');
define('JWT_SECRET', 'your_jwt_secret_key_here');

// API Configuration
define('OPENROUTER_API_KEY', 'your_openrouter_api_key');
define('WHATSAPP_API_KEY', 'your_whatsapp_api_key');

// Email Configuration
define('SMTP_HOST', 'your_smtp_host');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_smtp_username');
define('SMTP_PASSWORD', 'your_smtp_password');

// Payment Gateway
define('RAZORPAY_KEY_ID', 'your_razorpay_key_id');
define('RAZORPAY_KEY_SECRET', 'your_razorpay_key_secret');

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600);
define('REDIS_HOST', 'localhost');
define('REDIS_PORT', 6379);

// CDN Configuration
define('CDN_ENABLED', true);
define('CDN_URL', 'https://cdn.yourdomain.com');
?>";
        
        file_put_contents(__DIR__ . '/../config_production.php', $envConfig);
        echo "<div style='color: green;'>✅ Production Environment Config Created: config_production.php</div>\n";
    }
    
    /**
     * Setup security headers
     */
    private function setupSecurityHeaders() {
        echo "<h2>🛡️ Setting Up Security Headers</h2>\n";
        
        $securityHeaders = "<?php
/**
 * Security Headers for APS Dream Home
 */

// Content Security Policy
\$csp = \"default-src 'self'; \" .
        \"script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.google.com; \" .
        \"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; \" .
        \"font-src 'self' https://fonts.gstatic.com; \" .
        \"img-src 'self' data: https:; \" .
        \"connect-src 'self' https://api.openrouter.ai; \" .
        \"frame-src 'none'; \" .
        \"object-src 'none';\";

header('Content-Security-Policy: ' . \$csp);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Remove PHP version header
header_remove('X-Powered-By');
?>";
        
        file_put_contents(__DIR__ . '/../includes/security_headers.php', $securityHeaders);
        echo "<div style='color: green;'>✅ Security Headers Created: includes/security_headers.php</div>\n";
        
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h4>🔧 To Apply Security Headers:</h4>\n";
        echo "<p>Include security_headers.php at the top of your main files:</p>\n";
        echo "<code>require_once 'includes/security_headers.php';</code>\n";
        echo "</div>\n";
    }
    
    /**
     * Get database connection
     */
    private function getConnection() {
        return $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
    }
    
    /**
     * Display fixes summary
     */
    public function displaySummary() {
        echo "<h2>📋 Fixes Summary</h2>\n";
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>\n";
        echo "<h3>✅ Production Deployment Fixes Complete!</h3>\n";
        echo "<p><strong>Database Fixes:</strong> " . count($this->fixes) . " issues resolved</p>\n";
        echo "<p><strong>SSL Configuration:</strong> HTTPS setup prepared</p>\n";
        echo "<p><strong>Security Headers:</strong> CSP and security headers configured</p>\n";
        echo "<p><strong>Production Settings:</strong> Optimized PHP configuration</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Install GD extension (system-level)</li>\n";
        echo "<li>Install SSL certificate (Let's Encrypt recommended)</li>\n";
        echo "<li>Update config_production.php with actual values</li>\n";
        echo "<li>Apply .htaccess_https configuration</li>\n";
        echo "<li>Restart web server after changes</li>\n";
        echo "</ul>\n";
        echo "</div>\n";
    }
}

// Run fixes if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $fixer = new ProductionDeploymentFixer();
        $fixer->displaySummary();
    } catch (Exception $e) {
        echo "<h1>❌ Fix Error</h1>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}
?>
