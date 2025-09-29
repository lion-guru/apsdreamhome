<?php
/**
 * Direct Database Connection Test
 * Test database connection without web server dependency
 */

echo "=== DATABASE CONNECTION TEST ===\n\n";

try {
    // Include database connection
    define('INCLUDED_FROM_MAIN', true);
    require_once 'includes/db_connection.php';

    if ($pdo) {
        echo "✅ DATABASE CONNECTION: SUCCESSFUL\n";
        echo "✅ PDO object created successfully\n\n";

        // Test if we can query
        echo "Testing database queries...\n";

        try {
            // Test 1: Check if database exists
            $stmt = $pdo->query("SELECT DATABASE()");
            $db_name = $stmt->fetchColumn();
            echo "✅ Current database: $db_name\n";

            // Test 2: List available tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($tables) > 0) {
                echo "✅ Tables found: " . count($tables) . "\n";
                echo "📋 Available tables:\n";
                foreach ($tables as $table) {
                    echo "   - $table\n";
                }
            } else {
                echo "⚠️  No tables found in database\n";
                echo "💡 Run database_setup.php to create tables\n";
            }

            // Test 3: Check specific required tables
            $required_tables = ['company_settings', 'properties', 'users'];
            echo "\n🔍 Checking required tables:\n";
            foreach ($required_tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
                    $result = $stmt->fetch();
                    echo "   ✅ $table: EXISTS\n";
                } catch (Exception $e) {
                    echo "   ❌ $table: MISSING\n";
                }
            }

        } catch (Exception $e) {
            echo "❌ Query failed: " . $e->getMessage() . "\n";
        }

    } else {
        echo "❌ DATABASE CONNECTION: FAILED\n";
        echo "❌ PDO object is null\n";
    }

} catch (Exception $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. If connection failed: Start XAMPP MySQL service\n";
echo "2. If tables missing: Run http://localhost/apsdreamhomefinal/database_setup.php\n";
echo "3. If database missing: Create 'apsdreamhomefinal' in phpMyAdmin\n";

echo "\n=== END OF TEST ===\n";
?>
