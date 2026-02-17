<?php
/**
 * Database Status Checker
 * This script checks the database connection and displays table information
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Status Report</h1>";

// Try to load configuration
echo "<h2>Configuration Files</h2>";
$configFiles = [
    'config.php',
    'config/config.php',
    'config/database.php',
    'includes/config.php',
    'admin/config/config.php',
    'api/config/database.php'
];

$configFound = false;
foreach ($configFiles as $file) {
    if (file_exists($file)) {
        echo "<p>✅ Found configuration file: $file</p>";
        $configFound = true;
    }
}

if (!$configFound) {
    echo "<p>❌ No configuration files found!</p>";
}

// Database connection test
echo "<h2>Database Connection Test</h2>";

try {
    // Default connection parameters
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    // Connect to database
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<p>✅ Database connection successful!</p>";
    
    // Get database information
    $stmt = $pdo->query("SELECT DATABASE() as db_name, VERSION() as db_version");
    $dbInfo = $stmt->fetch();
    
    echo "<p>Database Name: <strong>{$dbInfo['db_name']}</strong></p>";
    echo "<p>Database Version: <strong>{$dbInfo['db_version']}</strong></p>";
    
    // Get table information
    echo "<h2>Database Tables</h2>";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p>✅ Found " . count($tables) . " tables in database</p>";
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Table Name</th><th>Rows</th><th>Status</th></tr>";
        
        foreach ($tables as $table) {
            // Get row count
            $rowCount = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            
            // Check if table has data
            $status = $rowCount > 0 ? "✅ Has data" : "⚠️ Empty";
            
            echo "<tr>";
            echo "<td>$table</td>";
            echo "<td>$rowCount</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>❌ No tables found in database!</p>";
    }
    
    // Check for specific tables that should exist
    echo "<h2>Required Tables Check</h2>";
    $requiredTables = [
        'users',
        'properties',
        'customers',
        'agents',
        'bookings'
    ];
    
    $missingTables = [];
    foreach ($requiredTables as $table) {
        if (!in_array($table, $tables)) {
            $missingTables[] = $table;
        }
    }
    
    if (count($missingTables) > 0) {
        echo "<p>❌ Missing required tables: " . implode(', ', $missingTables) . "</p>";
    } else {
        echo "<p>✅ All required tables exist</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}