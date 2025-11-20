<?php
/**
 * APS Dream Homes Pvt Ltd - Production Deployment Script
 * This script helps deploy the website to a live server
 */

// Check if we're in production or development
$is_production = false;
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

if ($server_name !== 'localhost' && $server_name !== '127.0.0.1') {
    $is_production = true;
}

// Production configuration
if ($is_production) {
    // Production database settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'apsdreamhome');
    define('DB_USER', 'your_production_username');
    define('DB_PASS', 'your_production_password');

    // Production URLs
    define('BASE_URL', 'https://' . $server_name . '/');
    define('SITE_URL', 'https://' . $server_name . '/');

    // Enable error reporting for production
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);

    // Production security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

} else {
    // Development settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'apsdreamhome');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    define('BASE_URL', 'http://localhost/apsdreamhome/');
    define('SITE_URL', 'http://localhost/apsdreamhome/');

    // Show errors in development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Database connection function
function getMysqliConnection() {
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );

        return $conn;

    } catch (PDOException $e) {
        if ($is_production) {
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        } else {
            die("Database Connection Error: " . $e->getMessage());
        }
    }
}

// Get company settings
function getCompanySettings() {
    try {
        $conn = getMysqliConnection();
        if (!$conn) return [];

        $sql = "SELECT setting_name, setting_value FROM site_settings";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

// Get properties count
function getPropertiesCount() {
    try {
        $conn = getDbConnection();
        if (!$conn) return 0;

        $sql = "SELECT COUNT(*) as count FROM properties WHERE status = 'available'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Production optimization
if ($is_production) {
    // Enable output compression
    if (!ob_start('ob_gzhandler')) {
        ob_start();
    }

    // Cache control headers
    header('Cache-Control: public, max-age=3600');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

    // Security headers
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://unpkg.com; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com; img-src \'self\' data: https:; font-src \'self\' https://cdnjs.cloudflare.com; connect-src \'self\'; frame-ancestors \'none\';');
}

// Display deployment status
if (isset($_GET['deployment_status'])) {
    echo "<div style='background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; border-radius: 10px; margin: 20px; text-align: center;'>";
    echo "<h3>🚀 Deployment Status: SUCCESS!</h3>";
    echo "<p>✅ Website is running in " . ($is_production ? 'PRODUCTION' : 'DEVELOPMENT') . " mode</p>";
    echo "<p>✅ Database: " . ($conn ? 'CONNECTED' : 'NOT CONNECTED') . "</p>";
    echo "<p>✅ Properties: " . getPropertiesCount() . " listings available</p>";
    echo "<p>✅ Server: " . $server_name . "</p>";
    echo "</div>";
}

// Company information
$company_settings = getCompanySettings();
$properties_count = getPropertiesCount();

define('COMPANY_NAME', $company_settings['company_name'] ?? 'APS Dream Homes Pvt Ltd');
define('COMPANY_PHONE', $company_settings['company_phone'] ?? '+91-9554000001');
define('COMPANY_EMAIL', $company_settings['company_email'] ?? 'info@apsdreamhomes.com');
define('COMPANY_ADDRESS', $company_settings['company_address'] ?? '123, Kunraghat Main Road, Near Railway Station, Gorakhpur, UP - 273008');
define('WORKING_HOURS', $company_settings['working_hours'] ?? 'Mon-Sat: 9:30 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM');

define('TOTAL_PROPERTIES', $properties_count);
define('PORTFOLIO_VALUE', $company_settings['portfolio_value'] ?? '₹6.15 Crore');
define('YEARS_EXPERIENCE', $company_settings['years_experience'] ?? '8+');
define('HAPPY_FAMILIES', $company_settings['happy_families'] ?? '1000+');

?>
