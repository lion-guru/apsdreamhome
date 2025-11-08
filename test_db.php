<?php
/**
 * Database Connection Test Script
 */

echo "🔍 Testing Database Connection...\n\n";

// Test MySQL Connection
try {
    $pdo = new PDO(
        "mysql:host=localhost;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "✅ Connected to MySQL server successfully!\n";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'apsdreamhomefinal'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Database 'apsdreamhomefinal' exists\n";
    } else {
        echo "❌ Database 'apsdreamhomefinal' does not exist. Creating it...\n";
        $pdo->exec("CREATE DATABASE apsdreamhomefinal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database 'apsdreamhomefinal' created successfully!\n";
    }
    
} catch (PDOException $e) {
    die("❌ MySQL Connection Error: " . $e->getMessage() . "\n");
}

// Test Apache
echo "\n🌐 Testing Apache...\n";
$url = 'http://localhost/apsdreamhome/';
$headers = @get_headers($url);

if ($headers && strpos($headers[0], '200')) {
    echo "✅ Apache is running and serving the application\n";
} else {
    echo "❌ Apache is not running or not serving the application\n";
    echo "   Please check if Apache is running in XAMPP control panel\n";
}

echo "\n🏁 Test completed!\n";
