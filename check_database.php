<?php
// Database Analysis Script
try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "Database connection failed: " . $conn->connect_error . "\n";
        exit;
    }
    
    echo "=== CURRENT DATABASE ANALYSIS ===\n";
    
    // Get current tables
    $result = $conn->query('SHOW TABLES');
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "Current tables count: " . count($tables) . "\n";
    echo "First 10 tables: " . implode(', ', array_slice($tables, 0, 10)) . (count($tables) > 10 ? '...' : '') . "\n\n";
    
    // Check for tables from your diagram
    $expectedTables = ['users', 'roles', 'user_roles', 'associates', 'associate_levels', 'properties', 'property_types', 'projects', 'companies', 'bookings', 'transactions'];
    
    echo "=== COMPARING WITH YOUR ORIGINAL DESIGN ===\n";
    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ $table - EXISTS\n";
        } else {
            echo "❌ $table - MISSING\n";
        }
    }
    
    echo "\n=== CHECKING SPECIFIC TABLES FOR DASHBOARD ===\n";
    $dashboardTables = ['customers', 'plots', 'commission_transactions', 'expenses'];
    foreach ($dashboardTables as $table) {
        if (in_array($table, $tables)) {
            $result = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $result->fetch_assoc()['count'];
            echo "✅ $table - EXISTS ($count records)\n";
        } else {
            echo "❌ $table - MISSING\n";
        }
    }
    
    echo "\n=== CHECKING CUSTOMERS TABLE STRUCTURE ===\n";
    if (in_array('customers', $tables)) {
        $result = $conn->query("DESCRIBE customers");
        echo "Customers table columns:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>