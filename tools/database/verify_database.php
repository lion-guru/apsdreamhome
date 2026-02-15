<?php
/**
 * Database Verification Script - APS Dream Homes
 * Verifies all required tables and relationships
 */

require_once 'includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "=== Database Tables Verification ===\n";

// Check main tables
$tables = ['users', 'admin', 'remember_tokens'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    echo $table . ': ' . ($result->num_rows > 0 ? '✅ EXISTS' : '❌ MISSING') . "\n";
}

echo "\n=== User Table Structure ===\n";
$result = $conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ")\n";
}

echo "\n=== Admin Table Structure ===\n";
$result = $conn->query("DESCRIBE admin");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ")\n";
}

echo "\n=== Remember Tokens Table Structure ===\n";
$result = $conn->query("DESCRIBE remember_tokens");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' (' . $row['Type'] . ")\n";
}

echo "\n=== Sample Data Count ===\n";
echo "Users: " . $conn->query("SELECT COUNT(*) FROM users")->fetch_column() . "\n";
echo "Admin: " . $conn->query("SELECT COUNT(*) FROM admin")->fetch_column() . "\n";
echo "Remember Tokens: " . $conn->query("SELECT COUNT(*) FROM remember_tokens")->fetch_column() . "\n";

echo "\n=== Verification Complete ===\n";
?>
