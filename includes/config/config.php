<?php
if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

// Load configuration constants safely
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/../../core/functions.php';

// Create database connection with error handling
try {
    $con = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // Check connection
    if ($con->connect_error) {
        throw new Exception("Connection failed: " . $con->connect_error);
    }

    // Set charset
    if (!$con->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $con->error);
    }

    // Set SQL mode to strict
    $con->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

    // Compatibility: expose $conn alias used by various includes
    if (!isset($conn)) {
        $conn = $con;
    }

} catch (Exception $e) {
    // Log error and show generic message
    error_log("Database connection error: " . $e->getMessage());
    die("A database error occurred. Please try again later or contact support.");
}


// --- RBAC ENFORCEMENT SNIPPET ---
if (!function_exists('require_role')) {
    function require_role($role_name) {
        global $con;
        if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
        $user_id = $_SESSION['auser'];
        $sql = "SELECT COUNT(*) as c FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id=? AND r.name=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('is', $user_id, $role_name);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res['c'] == 0) { echo '<div class=\'alert alert-danger\'>Access denied for role: '.htmlspecialchars($role_name).'</div>'; exit(); }
    }
}

// --- ACTION-LEVEL PERMISSION ENFORCEMENT ---
if (!function_exists('require_permission')) {
    function require_permission($action) {
        global $con;
        if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
        $user_id = $_SESSION['auser'];
        $sql = "SELECT COUNT(*) as c FROM user_roles ur
                JOIN role_permissions rp ON ur.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.id
                WHERE ur.user_id=? AND p.action=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('is', $user_id, $action);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res['c'] == 0) {
            // Log permission denial
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $details = 'Permission denied for action: ' . $action;
            $stmt2 = $con->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'Permission Denied', ?, ?)");
            $stmt2->bind_param('iss', $user_id, $details, $ip);
            $stmt2->execute();
            echo '<div class=\'alert alert-danger\'>Access denied for action: '.htmlspecialchars($action).'</div>';
            exit();
        }
    }
}

// WhatsApp Integration Configuration
$config = [
    'whatsapp' => [
        'enabled' => true,
        'phone_number' => '9277121112',
        'country_code' => '91',
        'api_provider' => 'whatsapp_business_api',
        'business_account_id' => '',
        'access_token' => '',
        'webhook_verify_token' => 'aps_dream_home_webhook_token',
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
    ],

    // Email System Configuration
    'email' => [
        'enabled' => true,
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => 'apsdreamhomes44@gmail.com',
        'smtp_pass' => 'your_app_password_here', // Replace with actual app password
        'smtp_encryption' => 'tls',
        'from_name' => 'APS Dream Home',
        'from_email' => 'apsdreamhomes44@gmail.com'
    ],

    // AI Integration Configuration
    'ai' => [
        'enabled' => true,
        'provider' => 'openrouter',
        'api_key' => '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', // Updated with provided key
        'model' => 'openai/gpt-4o',
        'base_url' => 'https://openrouter.ai/api/v1',
        'features' => [
            'property_descriptions' => true,
            'chatbot' => true,
            'code_analysis' => true,
            'development_assistance' => true,
            'customer_support' => true,
            'content_generation' => true
        ],
        'headers' => [
            'HTTP-Referer' => 'https://apsdreamhomes.com',
            'X-Title' => 'APS Dream Home'
        ]
    ]
];

// Include security configuration if it exists
$securityConfigPath = __DIR__ . '/includes/security_config.php';
if (file_exists($securityConfigPath)) {
    require_once $securityConfigPath;
}

// Include database configuration if it exists
$dbConfigPath = __DIR__ . '/includes/db_config.php';
if (file_exists($dbConfigPath)) {
    require_once $dbConfigPath;
}

// Initialize security configurations
if (function_exists('initializeSecurity')) {
    initializeSecurity();
}
?>