<?php
/**
 * Step 8: Apply proper naming conventions throughout
 * Ensure tables, columns, and code follow consistent naming standards
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 8: APPLY PROPER NAMING CONVENTIONS ===\n\n";

$namingIssues = [];
$fixesApplied = 0;

// Check 1: Table names should be snake_case
echo "📋 Checking table naming conventions...\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$tableNamingIssues = [];
foreach ($tables as $table) {
    // Check for camelCase, PascalCase, or spaces
    if (preg_match('/[A-Z]/', $table) || strpos($table, ' ') !== false) {
        $tableNamingIssues[] = $table;
        echo "  ⚠️  $table - should be snake_case\n";
    }
}

if (empty($tableNamingIssues)) {
    echo "  ✓ All tables follow snake_case convention\n";
} else {
    echo "  ⚠️  Found " . count($tableNamingIssues) . " tables with naming issues\n";
}

// Check 2: Standard timestamp columns
echo "\n📋 Checking standard timestamp columns...\n";
$requiredTimestamps = ['created_at', 'updated_at'];
$coreTables = ['users', 'leads', 'properties', 'activities', 'notifications_unified', 'activity_logs_unified'];

$timestampIssues = [];
foreach ($coreTables as $table) {
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($requiredTimestamps as $ts) {
            if (!in_array($ts, $columns)) {
                $timestampIssues[] = "$table missing $ts";
                echo "  ⚠️  $table missing $ts column\n";
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist
    }
}

if (empty($timestampIssues)) {
    echo "  ✓ Core tables have standard timestamp columns\n";
} else {
    echo "  ⚠️  Found " . count($timestampIssues) . " timestamp issues\n";
}

// Check 3: Index naming conventions
echo "\n📋 Checking index naming conventions...\n";
$indexIssues = [];

foreach ($coreTables as $table) {
    try {
        $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($indexes as $index) {
            $keyName = $index['Key_name'];
            
            // Check for non-standard index naming
            if (!preg_match('/^(idx_|fk_|unique_)/i', $keyName) && $keyName !== 'PRIMARY') {
                $indexIssues[] = "$table.$keyName - should use idx_ prefix for regular indexes";
                echo "  ⚠️  $table.$keyName - non-standard naming\n";
            }
        }
    } catch (Exception $e) {
        // Table doesn't exist
    }
}

if (empty($indexIssues)) {
    echo "  ✓ Indexes follow naming conventions\n";
} else {
    echo "  ⚠️  Found " . count($indexIssues) . " index naming issues\n";
}

// Fix common issues automatically
echo "\n🔧 Applying automatic fixes...\n";

try {
    // Add updated_at to tables that only have created_at
    foreach ($coreTables as $table) {
        try {
            $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
            
            if (in_array('created_at', $columns) && !in_array('updated_at', $columns)) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
                echo "  ✓ Added updated_at to $table\n";
                $fixesApplied++;
            }
        } catch (Exception $e) {
            // Skip if error
        }
    }
    
    // Add deleted_at for soft delete support (optional)
    $softDeleteTables = ['users', 'leads', 'properties'];
    foreach ($softDeleteTables as $table) {
        try {
            $columns = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
            
            if (!in_array('deleted_at', $columns)) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN deleted_at TIMESTAMP NULL AFTER updated_at");
                echo "  ✓ Added deleted_at to $table (soft delete support)\n";
                $fixesApplied++;
            }
        } catch (Exception $e) {
            // Skip if error
        }
    }
    
} catch (Exception $e) {
    echo "  ⚠️  Some automatic fixes could not be applied\n";
}

echo "\n=== NAMING CONVENTIONS SUMMARY ===\n";
echo "Tables checked: " . count($tables) . "\n";
echo "Table naming issues: " . count($tableNamingIssues) . "\n";
echo "Timestamp issues: " . count($timestampIssues) . "\n";
echo "Index naming issues: " . count($indexIssues) . "\n";
echo "Automatic fixes applied: $fixesApplied\n";

echo "\n=== STEP 8 COMPLETE ===\n";
echo "Naming conventions have been reviewed and basic fixes applied.\n";
echo "Advanced naming changes require manual review due to impact on code.\n";
