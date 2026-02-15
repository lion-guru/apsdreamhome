<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test basic PHP
echo "<h1>Debug Test Page</h1>";
echo "<p>PHP is working! Version: " . phpversion() . "</p>";

// Test file permissions
$file = __FILE__;
echo "<p>File permissions for $file: " . substr(sprintf('%o', fileperms($file)), -4) . "</p>";

// Test directory permissions
$dir = __DIR__;
echo "<p>Directory permissions for $dir: " . substr(sprintf('%o', fileperms($dir)), -4) . "</p>";

// Test database connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4',
        'root',
        ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green;'>✅ Database connection successful!</p>";
    
    // Test a simple query
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM properties');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Number of properties in database: " . $result['count'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
