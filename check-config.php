<?php

// Check database configuration and connection details
echo "=== APS Dream Home Database Configuration Analysis ===\n\n";

// Check environment variables
echo "📋 ENVIRONMENT VARIABLES:\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'not set') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n";
echo "DB_PASSWORD: " . (empty($_ENV['DB_PASSWORD']) ? 'empty' : 'set') . "\n";
echo "DB_PASS: " . (empty($_ENV['DB_PASS']) ? 'empty' : 'set') . "\n\n";

// Check constants
echo "📋 DEFINED CONSTANTS:\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'not defined') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'not defined') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'not defined') . "\n";
echo "DB_PASS: " . (defined('DB_PASS') ? DB_PASS : 'not defined') . "\n\n";

// Check what Database class will use
echo "🔍 DATABASE CLASS CONFIGURATION:\n";
$host = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
$dbname = defined('DB_NAME') ? DB_NAME : ($_ENV['DB_DATABASE'] ?? getenv('DB_NAME') ?: 'apsdreamhome');
$user = defined('DB_USER') ? DB_USER : ($_ENV['DB_USERNAME'] ?? getenv('DB_USER') ?: 'root');
$pass = defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASSWORD'] ?? getenv('DB_PASS') ?: '');

echo "Final Host: $host\n";
echo "Final Database: $dbname\n";
echo "Final User: $user\n";
echo "Final Password: " . (empty($pass) ? 'empty' : 'set') . "\n\n";

// Test connection to both databases
echo "🧪 CONNECTION TESTS:\n";

try {
    $pdo1 = new PDO("mysql:host=$host;dbname=apsdreamhome", $user, $pass);
    $stmt1 = $pdo1->query('SHOW TABLES');
    $tables1 = $stmt1->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ apsdreamhome: " . count($tables1) . " tables (connected)\n";
} catch (Exception $e) {
    echo "❌ apsdreamhome: " . $e->getMessage() . "\n";
}

try {
    $pdo2 = new PDO("mysql:host=$host;dbname=apsdreamhomes", $user, $pass);
    $stmt2 = $pdo2->query('SHOW TABLES');
    $tables2 = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ apsdreamhomes: " . count($tables2) . " tables (connected)\n";
} catch (Exception $e) {
    echo "❌ apsdreamhomes: " . $e->getMessage() . "\n";
}

echo "\n📊 SUMMARY:\n";
echo "• Current configuration connects to: $dbname\n";
echo "• Available databases:\n";
echo "  - apsdreamhome: " . (isset($tables1) ? count($tables1) . " tables" : "not accessible") . "\n";
echo "  - apsdreamhomes: " . (isset($tables2) ? count($tables2) . " tables" : "not accessible") . "\n";

if (isset($tables2) && count($tables2) === 596) {
    echo "\n💡 RECOMMENDATION:\n";
    echo "Change DB_DATABASE from 'apsdreamhome' to 'apsdreamhomes'\n";
    echo "to access all 596 tables with complete features!\n";
}
?>
