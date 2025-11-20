<?php
/**
 * Application Configuration
 *
 * Central configuration for APS Dream Home. Defines application constants,
 * environment-aware BASE_URL, database settings, feature toggles, and helpers.
 */

// Application Settings
if (!defined('APP_NAME')) {
    define('APP_NAME', 'APS Dream Home');
}

// Environment-aware BASE_URL
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Compute project path relative to document root, works for both Apache (subdir) and PHP built-in server
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
    $projRoot = rtrim(str_replace('\\','/', realpath(__DIR__ . '/..')), '/');
    $relative = '';
    if ($docRoot && $projRoot && strpos($projRoot, $docRoot) === 0) {
        $relative = substr($projRoot, strlen($docRoot));
    }
    $basePath = $relative ? '/' . trim($relative, '/') . '/' : '/';
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}

// Project root (filesystem path)
if (!defined('ROOT')) {
    define('ROOT', __DIR__ . '/../');
}

// Database Configuration
if (!defined('DB_HOST')) { define('DB_HOST', 'localhost'); }
if (!defined('DB_NAME')) { define('DB_NAME', 'apsdreamhome'); }
if (!defined('DB_USER')) { define('DB_USER', 'root'); }
if (!defined('DB_PASS')) { define('DB_PASS', ''); }

// Email Configuration
$config['email'] = [
    'enabled' => true,
    'smtp_host' => getenv('MAIL_HOST'),
    'smtp_port' => (int) (getenv('MAIL_PORT')),
    'smtp_username' => getenv('MAIL_USERNAME'),
    'smtp_password' => getenv('MAIL_PASSWORD'),
    'smtp_encryption' => getenv('MAIL_ENCRYPTION'),
    'from_email' => getenv('MAIL_FROM_ADDRESS'),
    'from_name' => getenv('MAIL_FROM_NAME'),
    'reply_to' => getenv('MAIL_REPLY_TO'),
    'bcc_admin' => true,
    'admin_email' => getenv('MAIL_ADMIN')
];

// WhatsApp Configuration
$config['whatsapp'] = [
    'enabled' => true,
    'phone_number' => getenv('WHATSAPP_PHONE'),
    'country_code' => getenv('WHATSAPP_COUNTRY_CODE'),
    'api_provider' => getenv('WHATSAPP_API_PROVIDER'),
    'business_account_id' => getenv('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    'access_token' => getenv('WHATSAPP_ACCESS_TOKEN'),
    'webhook_verify_token' => getenv('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'notification_types' => [
        'welcome_message' => true,
        'property_inquiry' => true,
        'booking_confirmation' => true,
        'payment_reminder' => true,
        'appointment_reminder' => true,
        'commission_alert' => true,
        'system_alerts' => true
    ],
    'auto_responses' => [
        'greeting_hours' => '09:00-18:00',
        'welcome_message' => 'Welcome to APS Dream Home! 🏠 How can we help you find your dream property today?',
        'away_message' => 'Thank you for your message! Our team is currently away. We\'ll respond to you within 24 hours.',
        'business_hours' => 'Mon-Fri: 9:00 AM - 6:00 PM, Sat: 9:00 AM - 2:00 PM'
    ]
];

// Include WhatsApp system if enabled
if (!empty($config['whatsapp']['enabled'])) {
    require_once __DIR__ . '/whatsapp_integration.php';
}

// AI Configuration
$config['ai'] = [
    'enabled' => true,
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
    'model' => getenv('OPENROUTER_MODEL'),
    'features' => [
        'property_descriptions' => true,
        'property_valuation' => true,
        'chatbot' => true,
        'recommendations' => true,
        'market_analysis' => true,
        'investment_insights' => true,
        'marketing_content' => true,
        'code_analysis' => true,
        'development_assistance' => true
    ]
];

// Include AI systems
require_once __DIR__ . '/ai_personality_system.php';
require_once __DIR__ . '/ai_learning_system.php';

class AppConfig {
    private static $instance = null;
    private $config = [];

    private function __construct() {
        // Build a simple config map using existing constants and arrays
        $this->config['app'] = [
            'name' => defined('APP_NAME') ? APP_NAME : 'APS Dream Home',
            'url' => defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome/',
        ];

        // Database settings (keys expected by legacy code/tests)
        $this->config['database'] = [
            'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
            'user' => defined('DB_USER') ? DB_USER : 'root',
            'pass' => defined('DB_PASS') ? DB_PASS : (defined('DB_PASSWORD') ? DB_PASSWORD : ''),
            'name' => defined('DB_NAME') ? DB_NAME : 'apsdreamhome',
            'charset' => 'utf8mb4',
        ];

        // Email/WhatsApp/AI configs
        global $config;
        $this->config['email'] = $config['email'] ?? [];
        $this->config['whatsapp'] = $config['whatsapp'] ?? [];
        $this->config['ai'] = $config['ai'] ?? [];
        $this->config['admin_email'] = $config['email']['admin_email'] ?? 'admin@apsdreamhome.com';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Basic getter supporting dot-notation (e.g., 'email.enabled')
    public function get($key, $default = null) {
        if (strpos($key, '.') === false) {
            return $this->config[$key] ?? $default;
        }
        $parts = explode('.', $key);
        $value = $this->config;
        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return $default;
            }
        }
        return $value;
    }

    // Legacy convenience: return mysqli connection (or null if unavailable)
    public function getDatabaseConnection() {
        $db = $this->config['database'];
        try {
            $mysqli = new \mysqli($db['host'], $db['user'], $db['pass'], $db['name']);
            if ($mysqli->connect_error) {
                error_log('MySQLi connection failed: ' . $mysqli->connect_error);
                return null;
            }
            $mysqli->set_charset($db['charset']);
            return $mysqli;
        } catch (\Throwable $e) {
            error_log('MySQLi connection exception: ' . $e->getMessage());
            return null;
        }
    }
}

// Initialize legacy globals for managers if not already set
if (!isset($GLOBALS['db_connection']) || !($GLOBALS['db_connection'] instanceof \mysqli)) {
    $GLOBALS['db_connection'] = AppConfig::getInstance()->getDatabaseConnection();
}
// Some legacy code uses $db
if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof \mysqli)) {
    $GLOBALS['db'] = $GLOBALS['db_connection'];
}

// Provide a simple global helper for config('admin_email') used in managers
if (!function_exists('config')) {
    function config($key, $default = null) {
        $cfg = AppConfig::getInstance();
        $value = $cfg->get($key, $default);
        return $value ?? $default;
    }
}

// MLM Configuration
if (!defined('MLM_ENABLED')) {
    define('MLM_ENABLED', true);
    define('MLM_MAX_LEVELS', 5);
    define('MLM_COMMISSION_STRUCTURE', [
        1 => 5.0,
        2 => 3.0,
        3 => 2.0,
        4 => 1.5,
        5 => 1.0
    ]);
}
