<?php
/**
 * Database Structure Checker
 * Check current users table structure and identify missing columns for RBAC
 */

// Database configuration from .env
$host = 'localhost';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL successfully\n";
    echo "📊 Database: $dbname\n\n";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Database '$dbname' does not exist!\n";
        echo "Available databases:\n";
        $dbs = $pdo->query("SHOW DATABASES");
        while ($row = $dbs->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Database']}\n";
        }
        exit(1);
    }
    
    $pdo->exec("USE $dbname");
    echo "✅ Using database: $dbname\n\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Users table does not exist!\n";
        echo "Available tables:\n";
        $tables = $pdo->query("SHOW TABLES");
        while ($row = $tables->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . array_values($row)[0] . "\n";
        }
        exit(1);
    }
    
    echo "✅ Users table exists\n\n";
    
    // Get users table structure
    echo "📋 USERS TABLE STRUCTURE:\n";
    echo str_repeat("=", 80) . "\n";
    
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[] = $column['Field'];
        echo sprintf(
            "  %-20s %-25s %-10s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key']
        );
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    // Required columns for RBAC system
    $requiredColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'customer_id' => 'VARCHAR(20) UNIQUE',
        'name' => 'VARCHAR(255) NOT NULL',
        'email' => 'VARCHAR(255) UNIQUE NOT NULL',
        'phone' => 'VARCHAR(20)',
        'password' => 'VARCHAR(255) NOT NULL',
        'role' => "ENUM('admin', 'customer', 'associate', 'agent', 'employee')",
        'status' => "ENUM('active', 'inactive', 'pending')",
        'referral_code' => 'VARCHAR(20) UNIQUE',
        'referred_by' => 'INT NULL',
        'email_verified_at' => 'TIMESTAMP NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    // Check missing columns
    echo "🔍 MISSING COLUMNS ANALYSIS:\n";
    echo str_repeat("=", 80) . "\n";
    
    $missingColumns = [];
    foreach ($requiredColumns as $columnName => $columnType) {
        if (!in_array($columnName, $existingColumns)) {
            $missingColumns[$columnName] = $columnType;
            echo "  ❌ MISSING: $columnName ($columnType)\n";
        } else {
            echo "  ✅ EXISTS: $columnName\n";
        }
    }
    
    echo str_repeat("=", 80) . "\n\n";
    
    if (empty($missingColumns)) {
        echo "✅ All required columns exist!\n";
        echo "✅ Users table is ready for RBAC system\n";
    } else {
        echo "⚠️  Missing " . count($missingColumns) . " columns\n";
        echo "\n📝 SQL TO ADD MISSING COLUMNS:\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($missingColumns as $columnName => $columnType) {
            if ($columnName === 'referred_by') {
                echo "ALTER TABLE users ADD COLUMN $columnName INT NULL, ADD INDEX idx_referred_by (referred_by);\n";
            } else {
                echo "ALTER TABLE users ADD COLUMN $columnName $columnType";
                if (strpos($columnType, 'PRIMARY KEY') !== false) {
                    echo ", ADD PRIMARY KEY ($columnName)";
                } elseif (strpos($columnType, 'UNIQUE') !== false) {
                    echo ", ADD UNIQUE KEY unique_$columnName ($columnName)";
                }
                echo ";\n";
            }
        }
        
        echo str_repeat("=", 80) . "\n";
        
        // Add foreign key constraint for referred_by
        if (in_array('referred_by', array_keys($missingColumns))) {
            echo "\n📝 ADD FOREIGN KEY CONSTRAINT (run after adding referred_by column):\n";
            echo str_repeat("=", 80) . "\n";
            echo "ALTER TABLE users ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL;\n";
            echo str_repeat("=", 80) . "\n";
        }
    }
    
    // Check sample data
    echo "\n📊 SAMPLE DATA (First 5 users):\n";
    echo str_repeat("=", 80) . "\n";
    
    $stmt = $pdo->query("SELECT * FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "  No users found in database\n";
    } else {
        foreach ($users as $user) {
            echo "  ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}";
            if (isset($user['role'])) {
                echo ", Role: {$user['role']}";
            }
            if (isset($user['status'])) {
                echo ", Status: {$user['status']}";
            }
            echo "\n";
        }
    }
    
    echo str_repeat("=", 80) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Database structure check complete!\n";
?>
