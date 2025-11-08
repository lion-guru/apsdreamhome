<?php
/**
 * Database Connection Handler
 * Establishes and manages database connections
 */

try {
    // Include the database configuration
    require_once __DIR__ . '/config.php';

    // Check if database constants are defined
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Database configuration constants are not defined. Please check config.php');
    }

    // Create database connection with better error handling
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Set timezone
    $pdo->exec("SET time_zone = '+05:30';");

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
function getDbConnection() {
    global $pdo;
    return $pdo;
}

/**
 * Get database connection for mysqli compatibility
 * @return mysqli
 */
function getMysqliConnection() {
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
function executeQuery($sql, $params = []) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $pdo->errorInfo()[2]);
    }

    if (!$stmt->execute($params)) {
        throw new Exception("Failed to execute statement: " . $stmt->errorInfo()[2]);
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
function executeMysqliQuery($sql, $types = '', $params = []) {
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
?>
