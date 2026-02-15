<?php
/**
 * Debug Database Connection Test
 */

// Include the correct config file
require_once 'includes/config.php';

echo "=== Database Connection Test ===\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? '***' : '(empty)') . "\n\n";

// Test MySQLi connection
echo "Testing MySQLi connection...\n";
try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_error) {
        echo "❌ MySQLi Connection FAILED: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ MySQLi Connection SUCCESSFUL!\n";
        echo "Server version: " . $mysqli->server_version . "\n";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ MySQLi Exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test PDO connection
echo "Testing PDO connection...\n";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "✅ PDO Connection SUCCESSFUL!\n";
    
    // Test a simple query
    $stmt = $pdo->query('SELECT DATABASE() as db_name');
    $result = $stmt->fetch();
    echo "Connected database: " . $result['db_name'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ PDO Connection FAILED: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>