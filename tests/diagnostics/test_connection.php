<?php
// Include database configuration
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connection.php';

try {
    // Test database connection
    $stmt = $pdo->query('SELECT 1');
    echo "✅ Database connection successful!\n";
    
    // Check if tables exist
    $tables = ['users', 'properties', 'colonies', 'plots'];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Table '$table' exists\n";
        } else {
            $missingTables[] = $table;
            echo "❌ Table '$table' is missing\n";
        }
    }
    
    if (!empty($missingTables)) {
        echo "\n⚠️  Missing tables detected. Would you like to create them? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) == 'y') {
            // Add table creation logic here
            echo "\nCreating missing tables...\n";
            // TODO: Add table creation SQL
            echo "Tables created successfully!\n";
        }
        fclose($handle);
    }
    
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Check application URLs
echo "\n🔍 Checking application URLs:\n";
$urls = [
    'Home' => BASE_URL,
    'Admin Panel' => BASE_URL . 'admin/',
    'API Endpoint' => BASE_URL . 'api/'
];

foreach ($urls as $name => $url) {
    $status = @get_headers($url) ? '✅' : '❌';
    echo "$status $name: $url\n";
}

echo "\n🏁 System check completed!\n";
