<?php
/**
 * APS Dream Home - DATABASE SETUP SCRIPT
 * Automatically create database and import tables
 */

echo "🏠 APS Dream Home - DATABASE SETUP SCRIPT\n";
echo "========================================\n\n";

$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$envFile = $projectRoot . '/.env';

// 1. Read .env configuration
echo "1. 📋 READING CONFIGURATION\n";
echo "==========================\n";

$envVars = [];
$envContent = file_get_contents($envFile);

foreach (explode("\n", $envContent) as $line) {
    if (strpos($line, '=') !== false && !empty(trim($line)) && substr($line, 0, 1) !== '#') {
        list($key, $value) = explode('=', $line, 2);
        $envVars[trim($key)] = trim($value);
    }
}

$dbHost = $envVars['DB_HOST'] ?? 'localhost';
$dbName = $envVars['DB_NAME'] ?? 'apsdreamhome';
$dbUser = $envVars['DB_USER'] ?? 'root';
$dbPass = $envVars['DB_PASS'] ?? '';

echo "   Host: $dbHost\n";
echo "   Database: $dbName\n";
echo "   User: $dbUser\n";
echo "   Password: " . (empty($dbPass) ? '(empty)' : '***') . "\n";

// 2. Test MySQL connection (without database)
echo "\n2. 🔗 TESTING MYSQL CONNECTION\n";
echo "==============================\n";

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    
    if ($conn->connect_error) {
        echo "   ❌ MySQL connection failed: " . $conn->connect_error . "\n";
        echo "\n   🔧 SOLUTION:\n";
        echo "   1. Check if XAMPP/MySQL is running\n";
        echo "   2. Verify MySQL credentials\n";
        echo "   3. Check MySQL port (usually 3306)\n";
        exit(1);
    }
    
    echo "   ✅ MySQL connection successful!\n";
    
    // 3. Create database if not exists
    echo "\n3. 🗄️ CREATING DATABASE\n";
    echo "======================\n";
    
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if ($conn->query($createDbSql)) {
        echo "   ✅ Database '$dbName' created/verified\n";
    } else {
        echo "   ❌ Database creation failed: " . $conn->error . "\n";
        $conn->close();
        exit(1);
    }
    
    // 4. Select database
    $conn->select_db($dbName);
    echo "   ✅ Database selected\n";
    
    // 5. Check for SQL files
    echo "\n4. 📄 LOOKING FOR SQL FILES\n";
    echo "========================\n";
    
    $sqlFiles = [
        $projectRoot . '/database/apsdreamhome.sql',
        $projectRoot . '/database/schema.sql',
        $projectRoot . '/database/structure.sql',
        $projectRoot . '/database/migrations/01_create_tables.sql'
    ];
    
    $foundSqlFiles = [];
    foreach ($sqlFiles as $sqlFile) {
        if (file_exists($sqlFile)) {
            $foundSqlFiles[] = $sqlFile;
            echo "   ✅ Found: " . basename($sqlFile) . "\n";
        }
    }
    
    if (empty($foundSqlFiles)) {
        echo "   ⚠️  No SQL files found\n";
        echo "   📝 Creating basic database structure...\n";
        
        // Create basic tables
        $basicTables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user', 'associate') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS properties (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                location VARCHAR(255) NOT NULL,
                type ENUM('apartment', 'house', 'villa', 'plot') NOT NULL,
                status ENUM('available', 'sold', 'booked') DEFAULT 'available',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS associates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                sponsor_id INT NULL,
                level INT DEFAULT 1,
                commission_rate DECIMAL(5,2) DEFAULT 0.00,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (sponsor_id) REFERENCES users(id)
            )",
            
            "CREATE TABLE IF NOT EXISTS commissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                associate_id INT NOT NULL,
                property_id INT NULL,
                amount DECIMAL(10,2) NOT NULL,
                level INT DEFAULT 1,
                status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (associate_id) REFERENCES associates(id),
                FOREIGN KEY (property_id) REFERENCES properties(id)
            )"
        ];
        
        foreach ($basicTables as $sql) {
            if ($conn->query($sql)) {
                echo "   ✅ Table created\n";
            } else {
                echo "   ❌ Table creation failed: " . $conn->error . "\n";
            }
        }
        
    } else {
        // 6. Import SQL files
        echo "\n5. 📥 IMPORTING SQL FILES\n";
        echo "======================\n";
        
        foreach ($foundSqlFiles as $sqlFile) {
            echo "   📄 Importing: " . basename($sqlFile) . "\n";
            
            $sqlContent = file_get_contents($sqlFile);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                    if ($conn->query($statement)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        if (strpos($conn->error, 'already exists') === false) {
                            echo "      ⚠️  Error: " . $conn->error . "\n";
                        }
                    }
                }
            }
            
            echo "      ✅ $successCount statements executed\n";
            if ($errorCount > 0) {
                echo "      ⚠️  $errorCount statements had issues\n";
            }
        }
    }
    
    // 7. Final verification
    echo "\n6. 🔍 FINAL VERIFICATION\n";
    echo "======================\n";
    
    $result = $conn->query("SHOW TABLES");
    $tableCount = $result->num_rows;
    echo "   ✅ Total tables: $tableCount\n";
    
    if ($tableCount > 0) {
        echo "   📋 Tables found:\n";
        while ($row = $result->fetch_array()) {
            echo "      - " . $row[0] . "\n";
        }
    }
    
    // 8. Test application database connection
    echo "\n7. 🧪 TESTING APPLICATION CONNECTION\n";
    echo "===================================\n";
    
    try {
        $appConn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        
        if ($appConn->connect_error) {
            echo "   ❌ Application connection failed\n";
        } else {
            echo "   ✅ Application connection successful!\n";
            
            // Test basic query
            $result = $appConn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$dbName'");
            $row = $result->fetch_assoc();
            echo "   ✅ Database accessible: {$row['count']} tables\n";
            
            $appConn->close();
        }
    } catch (Exception $e) {
        echo "   ❌ Application test failed: " . $e->getMessage() . "\n";
    }
    
    $conn->close();
    
    echo "\n🎉 DATABASE SETUP COMPLETED!\n";
    echo "===========================\n";
    echo "   ✅ Database: $dbName\n";
    echo "   ✅ Tables: $tableCount\n";
    echo "   ✅ Status: READY\n";
    echo "\n   🚀 Your APS Dream Home is now ready!\n";
    echo "   📱 You can now access your application!\n";
    
} catch (Exception $e) {
    echo "   ❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\n   🔧 MANUAL SETUP REQUIRED:\n";
    echo "   1. Start XAMPP/MySQL service\n";
    echo "   2. Open phpMyAdmin\n";
    echo "   3. Create database 'apsdreamhome'\n";
    echo "   4. Import SQL files from database/ directory\n";
    echo "   5. Run this script again\n";
}

?>
