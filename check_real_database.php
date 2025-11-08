<?php
/**
 * Database Connection and Schema Checker
 * Connects to the actual database and shows what's currently there
 */

try {
    // Database connection details
    $host = 'localhost';
    $user = 'root';
    $password = ''; // Default XAMPP password
    $database = 'apsdreamhome';

    echo "🔍 Connecting to database: {$database}\n";
    echo "Host: {$host}\n";
    echo "User: {$user}\n";
    echo "=====================================\n\n";

    // Try to connect
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "✅ Database connection successful!\n\n";

    // Get all tables
    echo "📋 Available Tables:\n";
    echo "===================\n";

    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();

    if (empty($tables)) {
        echo "❌ No tables found in database\n";
    } else {
        foreach ($tables as $table) {
            $table_name = array_values($table)[0];
            echo "• {$table_name}\n";
        }
        echo "\n";
    }

    // Check for our required tables
    $required_tables = [
        'users' => 'User authentication and management',
        'properties' => 'Property listings',
        'property_types' => 'Property categories',
        'property_images' => 'Property photos',
        'property_favorites' => 'User favorites',
        'property_inquiries' => 'Property inquiries',
        'site_settings' => 'System configuration',
        'user_sessions' => 'Session management',
        'activity_logs' => 'Audit trail'
    ];

    echo "🔍 Checking Required Tables:\n";
    echo "===========================\n";

    foreach ($required_tables as $table => $description) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                // Get table structure
                $stmt = $pdo->query("DESCRIBE {$table}");
                $columns = $stmt->fetchAll();
                $column_count = count($columns);

                echo "✅ {$table} ({$column_count} columns) - {$description}\n";
            } else {
                echo "❌ {$table} - {$description} [MISSING]\n";
            }
        } catch (Exception $e) {
            echo "❌ {$table} - {$description} [ERROR: " . $e->getMessage() . "]\n";
        }
    }

    // Check for advanced MLM/Associate tables
    $mlm_tables = [
        'sites' => 'Project sites and land management',
        'associates' => 'MLM associates',
        'associate_levels' => 'Associate commission levels',
        'bookings' => 'Property bookings',
        'projects' => 'Development projects'
    ];

    echo "\n🏗️  Checking MLM/Advanced Tables:\n";
    echo "================================\n";

    foreach ($mlm_tables as $table => $description) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->query("DESCRIBE {$table}");
                $columns = $stmt->fetchAll();
                $column_count = count($columns);

                echo "✅ {$table} ({$column_count} columns) - {$description}\n";
            } else {
                echo "⚠️  {$table} - {$description} [NOT FOUND]\n";
            }
        } catch (Exception $e) {
            echo "❌ {$table} - {$description} [ERROR]\n";
        }
    }

    // Check sample data
    echo "\n📊 Sample Data Check:\n";
    echo "===================\n";

    // Check users
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "Users: {$result['count']} records\n";
    } catch (Exception $e) {
        echo "Users: Error checking\n";
    }

    // Check properties
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties");
        $result = $stmt->fetch();
        echo "Properties: {$result['count']} records\n";
    } catch (Exception $e) {
        echo "Properties: Error checking\n";
    }

    // Check property types
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM property_types");
        $result = $stmt->fetch();
        echo "Property Types: {$result['count']} records\n";
    } catch (Exception $e) {
        echo "Property Types: Error checking\n";
    }

    // Check site settings
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM site_settings");
        $result = $stmt->fetch();
        echo "Site Settings: {$result['count']} records\n";
    } catch (Exception $e) {
        echo "Site Settings: Error checking\n";
    }

    // Check associates (MLM)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM associates");
        $result = $stmt->fetch();
        echo "Associates (MLM): {$result['count']} records\n";
    } catch (Exception $e) {
        echo "Associates (MLM): Error checking\n";
    }

    echo "\n🎯 Database Status Summary:\n";
    echo "==========================\n";

    // Count total tables
    $total_tables = count($tables);
    echo "Total Tables: {$total_tables}\n";

    // Check if core tables exist
    $core_tables_exist = true;
    foreach (['users', 'properties', 'property_types', 'site_settings'] as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() === 0) {
                $core_tables_exist = false;
                break;
            }
        } catch (Exception $e) {
            $core_tables_exist = false;
            break;
        }
    }

    if ($core_tables_exist) {
        echo "✅ Core system tables: READY\n";
        echo "🚀 Basic APS Dream Home system can run!\n";
    } else {
        echo "❌ Core system tables: MISSING\n";
        echo "⚠️  Need to run database setup script\n";
    }

    // Check MLM features
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'associates'");
        if ($stmt->rowCount() > 0) {
            echo "✅ MLM/Associate features: AVAILABLE\n";
        } else {
            echo "⚠️  MLM/Associate features: NOT INSTALLED\n";
        }
    } catch (Exception $e) {
        echo "⚠️  MLM/Associate features: ERROR\n";
    }

    echo "\n💡 Recommendations:\n";
    echo "==================\n";

    if (!$core_tables_exist) {
        echo "1. Run: setup_complete_database.sql\n";
        echo "2. This will create all required core tables\n";
        echo "3. Then run sample data setup\n";
    } else {
        echo "1. Database is ready for APS Dream Home!\n";
        echo "2. Consider adding sample properties\n";
        echo "3. Configure email settings in admin panel\n";
    }

    echo "\n🎉 Database check completed!\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "💡 Troubleshooting:\n";
    echo "1. Make sure XAMPP is running\n";
    echo "2. Check if MySQL service is started\n";
    echo "3. Verify database credentials\n";
    echo "4. Create database 'apsdreamhome' if it doesn't exist\n";
}
?>
