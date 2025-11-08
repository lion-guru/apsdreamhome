<?php
/**
 * APS Dream Home - Database Configuration
 * Database connection settings
 */

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection parameters - use defined() to prevent redefinition
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'apsdreamhome'); // Correct database name
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// Compatibility alias
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}

// PDO connection options
if (!defined('DB_OPTIONS')) {
    define('DB_OPTIONS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
        PDO::ATTR_PERSISTENT => true
    ]);
}

// Database configuration array
$db_config = [
    'host' => DB_HOST,
    'username' => DB_USER,
    'password' => DB_PASSWORD,
    'database' => DB_NAME,
    'charset' => DB_CHARSET,
    'options' => DB_OPTIONS
];

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Test database connection
if (!function_exists('testDatabaseConnection')) {
    function testDatabaseConnection() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, DB_OPTIONS);
            return $pdo;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Get database connection string
if (!function_exists('getDatabaseDSN')) {
    function getDatabaseDSN() {
        return "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    }
}

// Get database credentials
if (!function_exists('getDatabaseCredentials')) {
    function getDatabaseCredentials() {
        return [
            'username' => DB_USER,
            'password' => DB_PASSWORD
        ];
    }
}

// Check if database exists
if (!function_exists('checkDatabaseExists')) {
    function checkDatabaseExists() {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
            $result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
            $exists = $result && $result->num_rows > 0;
            $conn->close();
            return $exists;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Create database if not exists
if (!function_exists('createDatabaseIfNotExists')) {
    function createDatabaseIfNotExists() {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

            if ($conn->connect_error) {
                return false;
            }

            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
                    CHARACTER SET " . DB_CHARSET . "
                    COLLATE utf8mb4_unicode_ci";

            $result = $conn->query($sql);
            $conn->close();

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
}

// Get database size
if (!function_exists('getDatabaseSize')) {
    function getDatabaseSize() {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $result = $conn->query("SELECT
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_schema = '" . DB_NAME . "'");

            if ($result) {
                $row = $result->fetch_assoc();
                $conn->close();
                return $row['size_mb'] ?? 0;
            }

            $conn->close();
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Get table count
if (!function_exists('getTableCount')) {
    function getTableCount() {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $result = $conn->query("SHOW TABLES");
            $count = $result ? $result->num_rows : 0;
            $conn->close();
            return $count;
        } catch (Exception $e) {
            return 0;
        }
    }
}

?>