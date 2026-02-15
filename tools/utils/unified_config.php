<?php
/**
 * APS Dream Home - Unified Configuration
 * Consolidates all configuration files into one place
 */

// Security: Prevent direct access
if (!defined('APS_CONFIG_LOADED')) {
    define('APS_CONFIG_LOADED', true);
    
    // Basic Settings
    define('APP_NAME', 'APS Dream Home');
    define('APP_VERSION', '2.0.0');
    define('APP_ENV', 'development'); // development, staging, production
    
    // Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'apsdreamhome');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
    
    // Base URLs
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . $host . '/apsdreamhome/');
    define('ADMIN_URL', BASE_URL . 'admin/');
    define('API_URL', BASE_URL . 'api/');
    
    // Session Configuration
    define('SESSION_NAME', 'APS_DREAM_HOME_SESSID');
    define('SESSION_LIFETIME', 86400); // 24 hours
    define('SESSION_PATH', '/');
    define('SESSION_DOMAIN', $_SERVER['HTTP_HOST'] ?? '');
    define('SESSION_SECURE', isset($_SERVER['HTTPS']));
    define('SESSION_HTTPONLY', true);
    define('SESSION_SAMESITE', 'Lax');
    
    // Security Settings
    define('ENCRYPTION_KEY', 'your-secret-key-here');
    define('JWT_SECRET', 'your-jwt-secret-here');
    define('API_KEY_PREFIX', 'aps_');
    
    // Email Configuration
    define('EMAIL_FROM', 'noreply@apsdreamhome.com');
    define('EMAIL_FROM_NAME', 'APS Dream Home');
    
    // File Upload Settings
    define('UPLOAD_MAX_SIZE', 5242880); // 5MB
    define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
    
    // Cache Settings
    define('CACHE_ENABLED', true);
    define('CACHE_LIFETIME', 3600); // 1 hour
    
    // Performance Settings
    define('DEBUG_MODE', false); // Set to true in development
    define('PERFORMANCE_MONITORING', true);
    define('ASSET_OPTIMIZATION', true);
    define('MINIFY_ASSETS', true);
    
    // Error Reporting (based on environment)
    if (APP_ENV === 'production') {
        error_reporting(0);
        ini_set('display_errors', 0);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    
    // Timezone
    date_default_timezone_set('Asia/Kolkata');
    
    // Include database connection
    function getDatabaseConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die("Database connection failed: " . $e->getMessage());
                } else {
                    die("Database connection failed. Please try again later.");
                }
            }
        }
        
        return $pdo;
    }
    
    // Include site settings
    function getSiteSetting($key, $default = '') {
        static $settings = null;
        
        if ($settings === null) {
            $settings = [
                'site_title' => APP_NAME,
                'site_description' => 'APS Dream Homes Pvt Ltd - Leading real estate developer in Gorakhpur',
                'site_keywords' => 'real estate, property, Gorakhpur, apartments, villas, plots',
                'site_author' => 'APS Dream Homes Pvt Ltd',
                'contact_email' => 'info@apsdreamhome.com',
                'contact_phone' => '+91-XXXXXXXXXX',
                'contact_address' => 'Gorakhpur, Uttar Pradesh, India'
            ];
        }
        
        return $settings[$key] ?? $default;
    }
}
?>
