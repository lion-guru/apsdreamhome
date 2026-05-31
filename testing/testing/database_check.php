<?php
/**
 * Database Verification Script
 * Check all 597 tables exist
 */

header('Content-Type: text/plain');

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ DATABASE CONNECTION: SUCCESS\n";
    echo "📊 TOTAL TABLES: " . count($tables) . "\n";
    echo "🎯 EXPECTED: 597\n";
    echo "\n";
    
    // Show first 30 tables
    echo "📋 SAMPLE TABLES (first 30):\n";
    echo str_repeat("-", 50) . "\n";
    foreach (array_slice($tables, 0, 30) as $i => $table) {
        echo sprintf("%3d. %s\n", $i + 1, $table);
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    if (count($tables) < 597) {
        echo "⚠️ WARNING: Tables missing!\n";
        echo "Expected: 597, Found: " . count($tables) . "\n";
        echo "Missing: " . (597 - count($tables)) . " tables\n";
    } else {
        echo "✅ ALL TABLES PRESENT!\n";
    }
    
    // Check some critical tables
    $critical_tables = [
        'users', 'customers', 'properties', 'projects', 'plots',
        'states', 'districts', 'colonies', 'bookings', 'payments',
        'emi_plans', 'support_tickets', 'network_tree', 'commissions',
        'gallery', 'testimonials', 'news', 'campaigns', 'leads'
    ];
    
    echo "\n🔍 CRITICAL TABLES CHECK:\n";
    echo str_repeat("-", 50) . "\n";
    
    $missing_critical = [];
    foreach ($critical_tables as $table) {
        $exists = in_array($table, $tables);
        echo sprintf("%-25s %s\n", $table, $exists ? '✅' : '❌ MISSING');
        if (!$exists) {
            $missing_critical[] = $table;
        }
    }
    
    if (!empty($missing_critical)) {
        echo "\n❌ CRITICAL TABLES MISSING:\n";
        foreach ($missing_critical as $table) {
            echo "   - $table\n";
        }
    }
    
    echo "\n✅ DATABASE VERIFICATION COMPLETE!\n";
    
} catch (PDOException $e) {
    echo "❌ DATABASE CONNECTION FAILED:\n";
    echo $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. XAMPP MySQL service is running\n";
    echo "2. Database 'apsdreamhome' exists\n";
    echo "3. Port 3307 is correct\n";
}
