<?php
// Deep Database Scan - Check what's already built
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 === APS DREAM HOME - DEEP DATABASE SCAN ===\n\n";
    
    // Get all tables
    $stmt = $pdo->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 DATABASE TABLES (" . count($tables) . " total):\n";
    echo str_repeat("=", 60) . "\n";
    
    foreach ($tables as $table) {
        echo "\n📁 TABLE: $table\n";
        echo str_repeat("-", 40) . "\n";
        
        // Get table structure
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Columns (" . count($columns) . "):\n";
        foreach ($columns as $column) {
            $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $column['Default'] ? " DEFAULT $column[Default]" : '';
            echo "  • {$column['Field']} ({$column['Type']}) $null$default\n";
        }
        
        // Get row count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
        $stmt->execute();
        $count = $stmt->fetch();
        echo "Rows: {$count['count']}\n";
        
        // Show sample data for important tables
        if ($count['count'] > 0 && in_array($table, ['users', 'properties', 'commissions'])) {
            $stmt = $pdo->prepare("SELECT * FROM $table LIMIT 3");
            $stmt->execute();
            $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Sample Data:\n";
            foreach ($sample as $row) {
                echo "  • ";
                foreach ($row as $key => $value) {
                    if (strlen($value) > 50) $value = substr($value, 0, 47) . '...';
                    echo "$key: $value | ";
                }
                echo "\n";
            }
        }
    }
    
    echo "\n\n🎯 === USER ROLE ANALYSIS ===\n";
    echo str_repeat("=", 60) . "\n";
    
    // Check user roles and structure
    $stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stmt->execute();
    $roles = $stmt->fetchAll();
    
    echo "User Role Distribution:\n";
    foreach ($roles as $role) {
        echo "  • {$role['role']}: {$role['count']} users\n";
    }
    
    // Check MLM structure
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE referrer_id IS NOT NULL");
    $stmt->execute();
    $sponsoredCount = $stmt->fetch();
    
    echo "\nMLM Structure:\n";
    echo "  • Users with sponsors: {$sponsoredCount['count']}\n";
    
    // Check commission data
    if (in_array('commissions', $tables)) {
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count, SUM(commission_amount) as total FROM commissions GROUP BY status");
        $stmt->execute();
        $commissionStats = $stmt->fetchAll();
        
        echo "\nCommission System:\n";
        foreach ($commissionStats as $stat) {
            echo "  • {$stat['status']}: {$stat['count']} commissions (₹" . number_format($stat['total'] ?? 0, 2) . ")\n";
        }
    }
    
    echo "\n\n🏠 === PROPERTY SYSTEM ANALYSIS ===\n";
    echo str_repeat("=", 60) . "\n";
    
    if (in_array('properties', $tables)) {
        // Check property status
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
        $stmt->execute();
        $propertyStatus = $stmt->fetchAll();
        
        echo "Property Status:\n";
        foreach ($propertyStatus as $status) {
            echo "  • {$status['status']}: {$status['count']} properties\n";
        }
        
        // Check property types
        $stmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM properties GROUP BY type");
        $stmt->execute();
        $propertyTypes = $stmt->fetchAll();
        
        echo "\nProperty Types:\n";
        foreach ($propertyTypes as $type) {
            echo "  • {$type['type']}: {$type['count']} properties\n";
        }
    }
    
    echo "\n\n💰 === FINANCIAL SYSTEM ANALYSIS ===\n";
    echo str_repeat("=", 60) . "\n";
    
    // Check payment tables
    $paymentTables = ['payments', 'invoices', 'transactions'];
    foreach ($paymentTables as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $count = $stmt->fetch();
            echo "  • $table: {$count['count']} records\n";
        }
    }
    
    echo "\n\n🔗 === RELATIONSHIP ANALYSIS ===\n";
    echo str_repeat("=", 60) . "\n";
    
    // Check foreign key relationships
    $stmt = $pdo->prepare("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_SCHEMA = 'apsdreamhome'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute();
    $relationships = $stmt->fetchAll();
    
    echo "Foreign Key Relationships:\n";
    foreach ($relationships as $rel) {
        echo "  • {$rel['TABLE_NAME']}.{$rel['COLUMN_NAME']} → {$rel['REFERENCED_TABLE_NAME']}.{$rel['REFERENCED_COLUMN_NAME']}\n";
    }
    
    echo "\n\n📈 === PERFORMANCE METRICS ===\n";
    echo str_repeat("=", 60) . "\n";
    
    // Check table sizes
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $table");
        $stmt->execute();
        $count = $stmt->fetch();
        
        if ($count['count'] > 100) {
            echo "  • $table: {$count['count']} rows (Large table)\n";
        }
    }
    
    echo "\n\n🎯 === SYSTEM HEALTH CHECK ===\n";
    echo str_repeat("=", 60) . "\n";
    
    // Check for required columns in users table
    $stmt = $pdo->prepare("DESCRIBE users");
    $stmt->execute();
    $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($userColumns, 'Field');
    
    $requiredColumns = ['id', 'name', 'email', 'role', 'status', 'password', 'created_at'];
    $missingColumns = array_diff($requiredColumns, $columnNames);
    
    if (empty($missingColumns)) {
        echo "✅ Users table has all required columns\n";
    } else {
        echo "❌ Users table missing: " . implode(', ', $missingColumns) . "\n";
    }
    
    // Check for MLM columns
    $mlmColumns = ['referrer_id', 'total_sales', 'team_sales', 'commission_level'];
    $missingMLMColumns = array_diff($mlmColumns, $columnNames);
    
    if (empty($missingMLMColumns)) {
        echo "✅ MLM system columns present\n";
    } else {
        echo "⚠️  MLM columns missing: " . implode(', ', $missingMLMColumns) . "\n";
    }
    
    echo "\n\n🏆 === SCAN COMPLETE ===\n";
    echo "Total Tables: " . count($tables) . "\n";
    echo "Database appears to be " . (count($tables) > 10 ? "WELL STRUCTURED" : "BASIC") . "\n";
    echo "MLM System: " . (empty($missingMLMColumns) ? "READY" : "NEEDS SETUP") . "\n";
    
} catch (Exception $e) {
    echo "❌ Error scanning database: " . $e->getMessage() . "\n";
}
?>
