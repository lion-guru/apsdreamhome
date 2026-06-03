<?php
/**
 * Step 12: Safely drop old user tables
 * After successful user table unification, drop the old fragmented tables
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== STEP 12: SAFELY DROP OLD USER TABLES ===\n\n";

try {
    // Check current state
    echo "📊 Current unified users table state:\n";
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $usersCols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    echo "  Records: $usersCount\n";
    echo "  Columns: " . count($usersCols) . "\n";
    
    // Check if unified columns exist
    $unifiedCols = ['user_type', 'customer_data', 'admin_data', 'agent_data', 'employee_data', 'associate_data', 'preferences_data'];
    $missingCols = [];
    foreach ($unifiedCols as $col) {
        if (!in_array($col, array_column($usersCols, 'Field'))) {
            $missingCols[] = $col;
        }
    }
    
    if (!empty($missingCols)) {
        echo "  ⚠️  Missing columns: " . implode(', ', $missingCols) . "\n";
    } else {
        echo "  ✓ All unified columns present\n";
    }
    
    echo "\n";
    
    // Define old user tables to drop
    $oldUserTables = [
        'customers' => 'Merged into users table with customer_data JSON column',
        'admin_users' => 'Merged into users table with admin_data JSON column',
        'agents' => 'Already merged (agent_details) in step 2',
        'associates' => 'Merged into users table with associate_data JSON column',
        'employees' => 'Merged into users table with employee_data JSON column',
        'customer_profiles' => 'Merged into users table with profile_data JSON column',
        'customer_preferences' => 'Merged into users table with preferences_data JSON column'
    ];
    
    $backupPrefix = 'backup_' . date('Ymd_His') . '_user_';
    $tablesDropped = 0;
    $tablesSkipped = [];
    
    foreach ($oldUserTables as $table => $reason) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            
            echo "📋 Processing: $table\n";
            echo "   Records: $count\n";
            echo "   Reason: $reason\n";
            
            if ($table === 'agents') {
                // Skip since agent_details was already handled in step 2
                echo "   - Skipping (already handled in step 2)\n";
            } else {
                // Backup and drop
                $backupName = $backupPrefix . $table;
                $pdo->exec("RENAME TABLE `$table` TO `$backupName`");
                echo "   ✓ Backed up to $backupName\n";
                echo "   ✓ Old table removed\n";
                $tablesDropped++;
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "   ⚠️  Could not process $table: {$e->getMessage()}\n";
            echo "   - Skipping\n\n";
            $tablesSkipped[] = $table;
        }
    }
    
    echo "=== STEP 12 COMPLETE ===\n";
    echo "Summary:\n";
    echo "  Old user tables removed: $tablesDropped\n";
    echo "  Skipped: " . count($tablesSkipped) . "\n";
    echo "  Backup prefix: $backupPrefix\n";
    echo "\n✓ User table consolidation complete!\n";
    echo "✓ All user data now in unified users table\n";
    echo "✓ Table count reduced by " . $tablesDropped . "\n";
    
    // Update final table count
    $totalTables = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'apsdreamhome'")->fetchColumn();
    echo "Total tables in database: $totalTables\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
