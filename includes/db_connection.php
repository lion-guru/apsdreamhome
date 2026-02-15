<?php
if (defined('DB_CONNECTION_LOADED')) {
    return;
}
define('DB_CONNECTION_LOADED', true);

/**
 * Database Connection Handler
 * Establishes and manages database connections
 */

try {
    // Include the database configuration
    require_once __DIR__ . '/config/config.php';

    // Get the singleton PDO instance from App core
    $db = \App\Core\App::database();
    $pdo = $db->getConnection();

    // Set timezone (if not already set in singleton)
    $pdo->exec("SET time_zone = '+05:30';");

    // Initialize global $conn for MySQLi compatibility
    $con = getMysqliConnection();
    $conn = $con;
} catch (PDOException $e) {
    // Log error and show generic message
    error_log('Database Connection Error: ' . $e->getMessage() . ' DSN: ' . $dsn);

    // For debugging - show detailed error in development
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        die('Database connection failed. Please check your database configuration.<br>
             Error: ' . $e->getMessage() . '<br>
             Host: ' . DB_HOST . '<br>
             Database: ' . DB_NAME . '<br>
             User: ' . DB_USER . '<br>
             <a href="debug_test.php">Run Debug Test</a>');
    } else {
        die('Could not connect to the database. Please try again later.');
    }
} catch (Exception $e) {
    // Handle other exceptions
    error_log('Database Setup Error: ' . $e->getMessage());

    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        die('Database setup error: ' . $e->getMessage());
    } else {
        die('Could not connect to the database. Please try again later.');
    }
}

/**
 * Get database connection (PDO)
 * @return PDO
 */
function getPdoConnection()
{
    return \App\Core\App::database()->getConnection();
}

/**
 * Get database connection for mysqli compatibility
 * @return mysqli
 */
function getMysqliConnection()
{
    static $mysqli_conn = null;

    if ($mysqli_conn === null) {
        $mysqli_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($mysqli_conn->connect_error) {
            throw new Exception("MySQLi connection failed: " . $mysqli_conn->connect_error);
        }

        $mysqli_conn->set_charset("utf8mb4");
    }

    return $mysqli_conn;
}

/**
 * Execute a PDO query with parameters
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function executeQuery($sql, $params = [])
{
    $db = \App\Core\App::database();

    // Check if we should use named bindings for better security
    // This is a bridge to allow the legacy executeQuery to work with named params
    $stmt = $db->getConnection()->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement");
    }

    if (!$stmt->execute($params)) {
        $error = $stmt->errorInfo();
        throw new Exception("Failed to execute statement: " . ($error[2] ?? 'Unknown error'));
    }

    return $stmt;
}

/**
 * Execute a mysqli query with parameters (for backward compatibility)
 * @param string $sql
 * @param string $types
 * @param array $params
 * @return mysqli_stmt
 */
function executeMysqliQuery($sql, $types = '', $params = [])
{
    $conn = getMysqliConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare mysqli statement: " . $conn->error);
    }

    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute mysqli statement: " . $stmt->error);
    }

    return $stmt;
}
