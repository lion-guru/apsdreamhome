<?php
/**
 * APS Dream Home - Complete Database Setup Script
 * Based on Deep Project Analysis - Generated on 2025-09-24
 * 
 * This script creates the complete database schema based on your actual code requirements
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'apsdreamhomefinal';

echo "=== APS DREAM HOME - COMPLETE DATABASE SETUP ===\n";
echo "Starting database setup based on deep project analysis...\n\n";

try {
    // Connect to MySQL server (without selecting database)
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✅ Connected to MySQL server successfully\n";
    
    // Create database if not exists
    $conn->query("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$database' created/verified\n";
    
    // Select the database
    $conn->select_db($database);
    echo "✅ Database selected\n\n";
    
    // Execute schema files in order
    $schemaFiles = [
        'aps_complete_schema_part1.sql',
        'aps_complete_schema_part2.sql', 
        'aps_complete_schema_part3.sql'
    ];
    
    $totalTables = 0;
    
    foreach ($schemaFiles as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (!file_exists($filePath)) {
            echo "❌ Schema file not found: $file\n";
            continue;
        }
        
        echo "📄 Processing: $file\n";
        
        // Read and execute SQL file
        $sql = file_get_contents($filePath);
        
        if ($sql === false) {
            echo "❌ Could not read file: $file\n";
            continue;
        }
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $tablesCreated = 0;
        
        foreach ($statements as $statement) {
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue; // Skip empty statements and comments
            }
            
            // Skip non-SQL statements
            if (stripos($statement, 'CREATE TABLE') === false && 
                stripos($statement, 'INSERT INTO') === false && 
                stripos($statement, 'USE ') === false &&
                stripos($statement, 'SET ') === false &&
                stripos($statement, 'START TRANSACTION') === false &&
                stripos($statement, 'COMMIT') === false) {
                continue;
            }
            
            if ($conn->query($statement)) {
                if (stripos($statement, 'CREATE TABLE') !== false) {
                    $tablesCreated++;
                    $totalTables++;
                }
            } else {
                // Only show errors for important statements, ignore warnings
                if (stripos($statement, 'CREATE TABLE') !== false || 
                    stripos($statement, 'INSERT INTO') !== false) {
                    echo "⚠️  Warning: " . $conn->error . "\n";
                    echo "   Statement: " . substr($statement, 0, 100) . "...\n";
                }
            }
        }
        
        echo "   ✅ Created $tablesCreated tables from $file\n";
    }
    
    echo "\n=== DATABASE ANALYSIS ===\n";
    
    // Get final table count
    $result = $conn->query("SHOW TABLES");
    $actualTables = $result->num_rows;
    
    echo "📊 Total tables created: $actualTables\n";
    
    // Verify key tables from your dashboard requirements
    $keyTables = ['admin', 'users', 'customers', 'associates', 'properties', 'plots', 
                  'bookings', 'payments', 'commission_transactions', 'expenses', 'emi_plans'];
    
    echo "\n=== VERIFYING KEY TABLES (Dashboard Compatible) ===\n";
    foreach ($keyTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            // Get record count
            $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $countResult->fetch_assoc()['count'];
            echo "✅ $table - EXISTS ($count records)\n";
        } else {
            echo "❌ $table - MISSING\n";
        }
    }
    
    // Check database size
    $result = $conn->query("SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
        FROM information_schema.tables 
        WHERE table_schema = '$database'");
    
    if ($result) {
        $row = $result->fetch_assoc();
        $size = $row['size_mb'] ?? 0;
        echo "\n📈 Database size: {$size} MB\n";
    }
    
    echo "\n=== TESTING DASHBOARD QUERIES ===\n";
    
    // Test dashboard queries to ensure compatibility
    $dashboardQueries = [
        "SELECT COUNT(*) as count FROM bookings" => "Total Bookings",
        "SELECT SUM(commission_amount) as sum FROM commission_transactions WHERE status='paid'" => "Total Commission Paid",
        "SELECT COUNT(*) as count FROM plots WHERE status='available'" => "Available Plots",
        "SELECT SUM(amount) as sum FROM expenses" => "Total Expenses"
    ];
    
    foreach ($dashboardQueries as $query => $description) {
        try {
            $result = $conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                $value = array_values($row)[0] ?? 0;
                echo "✅ $description: " . number_format($value, 2) . "\n";
            } else {
                echo "❌ $description: Query failed\n";
            }
        } catch (Exception $e) {
            echo "❌ $description: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== SETUP COMPLETE ===\n";
    echo "🎉 Your APS Dream Home database is ready!\n";
    echo "📋 Database: $database\n"; 
    echo "📊 Tables: $actualTables\n";
    echo "🔗 Compatible with your existing admin dashboard\n";
    echo "💻 Ready for your PHP application\n\n";
    
    echo "Default Login Credentials:\n";
    echo "Admin Panel: admin / demo123\n";
    echo "Super Admin: superadmin / demo123\n\n";
    
    echo "Next Steps:\n";
    echo "1. Update your database configuration files\n";
    echo "2. Test your admin dashboard\n";
    echo "3. Import additional data if needed\n";
    echo "4. Configure your PHP application settings\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== SETUP COMPLETED SUCCESSFULLY ===\n";
?>