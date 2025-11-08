<?php
/**
 * Database Configuration
 * Simple database settings for APS Dream Home
 */

// Database connection settings
$config['database'] = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'database' => getenv('DB_NAME') ?: 'apsdreamhome',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'port' => getenv('DB_PORT') ?: 3306,
    'socket' => getenv('DB_SOCKET') ?: null,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ],
    'pool' => [
        'min_connections' => 1,
        'max_connections' => 10,
        'timeout' => 30,
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'queries' => ['SELECT'],
    ],
    'migrations' => [
        'table' => 'migrations',
        'path' => APP_ROOT . '/database/migrations',
    ],
    'seeds' => [
        'path' => APP_ROOT . '/database/seeds',
    ],
];

// Legacy compatibility - keep old constants for backward compatibility
if (!defined('DB_HOST')) {
    define('DB_HOST', $config['database']['host']);
}
if (!defined('DB_USER')) {
    define('DB_USER', $config['database']['username']);
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', $config['database']['password']);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $config['database']['database']);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}

// Global connection variable for legacy code
global $con, $conn;
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['database']['host'],
        $config['database']['port'],
        $config['database']['database'],
        $config['database']['charset']
    );

    $pdo = new PDO(
        $dsn,
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );

    // Legacy mysqli connection for backward compatibility
    $con = mysqli_connect(
        $config['database']['host'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['database']
    );

    if (!$con) {
        throw new Exception('MySQLi connection failed: ' . mysqli_connect_error());
    }

    mysqli_set_charset($con, 'utf8mb4');

    // Both connections available
    $conn = $con;

} catch (Exception $e) {
    // Log error but don't expose details in production
    error_log('Database connection failed: ' . $e->getMessage());

    if (ENVIRONMENT === 'development') {
        die('Database connection error. Please check your configuration.');
    } else {
        die('Service temporarily unavailable.');
    }
}

?>
