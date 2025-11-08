<?php
// Proper Database Connection Test for APS Dream Home

echo "=== APS Dream Home Database Connection Test ===\n\n";

// Include the main config file
require_once 'config.php';

echo "Database Configuration:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";
echo "Password: " . (DB_PASSWORD ? '[set]' : '[empty]') . "\n\n";

// Check if connection was established
if (isset($con) && $con instanceof mysqli) {
    echo "✅ MySQLi Connection Object Found\n";
    
    // Check connection status
    if ($con->connect_error) {
        echo "❌ Connection Error: " . $con->connect_error . "\n";
    } else {
        echo "✅ Connection Successful!\n";
        echo "Server Info: " . $con->server_info . "\n";
        echo "Host Info: " . $con->host_info . "\n\n";
        
        // Test basic queries
        echo "Testing Database Operations:\n";
        
        // Check if database exists
        $result = $con->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Database '" . DB_NAME . "' exists\n";
            
            // Select the database
            $con->select_db(DB_NAME);
            
            // List tables
            $tables = $con->query("SHOW TABLES");
            if ($tables && $tables->num_rows > 0) {
                echo "✅ Tables found: " . $tables->num_rows . "\n";
                while ($table = $tables->fetch_array()) {
                    echo "   - " . $table[0] . "\n";
                }
            } else {
                echo "⚠️  No tables found in database. Database may be empty.\n";
            }
            
        } else {
            echo "❌ Database '" . DB_NAME . "' does not exist\n";
            echo "Creating database...\n";
            
            if ($con->query("CREATE DATABASE " . DB_NAME)) {
                echo "✅ Database created successfully\n";
                $con->select_db(DB_NAME);
            } else {
                echo "❌ Failed to create database: " . $con->error . "\n";
            }
        }
    }
} else {
    echo "❌ MySQLi Connection Object Not Found\n";
    echo "Checking if PDO connection is available...\n";
    
    // Try PDO connection
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ PDO Connection Successful!\n";
        
        // Check if database exists
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Database exists\n";
        } else {
            echo "❌ Database does not exist\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ PDO Connection Failed: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Test Completed ===\n";
?>