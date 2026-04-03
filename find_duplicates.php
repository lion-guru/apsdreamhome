<?php
/**
 * APS DREAM HOME - DEEP DUPLICATE ANALYSIS
 * Find actual duplicates, their references, and plan migration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$dbname = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$projectRoot = __DIR__;

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

// ============================================
// STEP 1: Find potential duplicate groups
// ============================================

$duplicateGroups = [
    'users' => ['users', 'customers', 'employees', 'agents', 'associates', 'admin', 'admins', 'admin_users'],
    'leads' => ['leads', 'crm_leads', 'marketing_leads', 'inquiries', 'contact_requests', 'contact_backup', 'customer_inquiries'],
    'properties' => ['properties', 'real_estate_properties', 'resale_properties', 'rental_properties'],
    'activity' => ['activities', 'activity_log', 'activity_logs', 'system_activities'],
    'log' => ['audit_log', 'audit_trail', 'system_logs', 'error_logs', 'api_logs', 'mcp_logs'],
    'ai' => ['ai_logs', 'ai_api_logs', 'ai_agent_logs', 'ai_interaction_logs', 'ai_call_logs'],
    'commission' => ['commissions', 'mlm_commissions', 'resale_commissions', 'hybrid_commission_records'],
    'ledger' => ['mlm_commission_ledger', 'commission_transactions'],
    'admin' => ['admin', 'admins', 'admin_users'],
    'settings' => ['settings', 'site_settings', 'system_settings', 'app_config'],
];

echo "================================================================================\n";
echo "APS DREAM HOME - DEEP DUPLICATE ANALYSIS\n";
echo "================================================================================\n\n";

// ============================================
// STEP 2: Analyze each group
// ============================================

$report = [];

foreach ($duplicateGroups as $groupName => $tableNames) {
    echo "--------------------------------------------------------------------------------\n";
    echo "GROUP: " . strtoupper($groupName) . "\n";
    echo "--------------------------------------------------------------------------------\n\n";
    
    $groupReport = [
        'tables' => [],
        'total_rows' => 0,
        'code_references' => [],
        'recommendation' => ''
    ];
    
    foreach ($tableNames as $tableName) {
        // Check if table exists
        $exists = in_array($tableName, $tables);
        if (!$exists) continue;
        
        // Get row count
        $rowCount = $pdo->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
        
        // Get table structure
        $columns = $pdo->query("DESCRIBE `$tableName`")->fetchAll(PDO::FETCH_ASSOC);
        
        // Search for code references
        $refCount = 0;
        $refFiles = [];
        searchCodeReferences($projectRoot, $tableName, $refCount, $refFiles);
        
        $groupReport['tables'][$tableName] = [
            'exists' => $exists,
            'rows' => $rowCount,
            'columns' => array_column($columns, 'Field'),
            'code_refs' => $refCount,
            'files' => $refFiles
        ];
        $groupReport['total_rows'] += $rowCount;
        
        echo "  TABLE: $tableName\n";
        echo "    Rows: $rowCount | Code References: $refCount\n";
        echo "    Columns: " . implode(', ', array_slice(array_column($columns, 'Field'), 0, 8)) . "\n";
        if (!empty($refFiles)) {
            echo "    Files: " . implode(', ', array_slice($refFiles, 0, 5)) . "\n";
        }
        echo "\n";
    }
    
    // Determine recommendation
    $tablesWithRefs = array_filter($groupReport['tables'], fn($t) => $t['code_refs'] > 0);
    $tablesWithData = array_filter($groupReport['tables'], fn($t) => $t['rows'] > 0);
    
    if (count($tablesWithRefs) <= 1 && count($tablesWithData) <= 1) {
        $groupReport['recommendation'] = "MIGRATE - Keep one table, migrate all references";
    } else {
        $groupReport['recommendation'] = "INVESTIGATE - Multiple tables have data/refs";
    }
    
    echo "  RECOMMENDATION: {$groupReport['recommendation']}\n\n";
    $report[$groupName] = $groupReport;
}

// ============================================
// STEP 3: Generate Migration Script
// ============================================

echo "================================================================================\n";
echo "MIGRATION PLAN\n";
echo "================================================================================\n\n";

$migrationSql = "-- ============================================\n";
$migrationSql .= "-- APS DREAM HOME - DUPLICATE TABLE MIGRATION\n";
$migrationSql .= "-- Run after backing up database\n";
$migrationSql .= "-- ============================================\n\n";

foreach ($report as $groupName => $group) {
    $tables = $group['tables'];
    $tablesWithRefs = array_filter($tables, fn($t) => $t['code_refs'] > 0);
    $tablesWithData = array_filter($tables, fn($t) => $t['rows'] > 0);
    
    if (empty($tablesWithRefs) || empty($tablesWithData)) continue;
    
    // Find primary table (most references)
    $primaryTable = array_keys($tablesWithRefs)[0];
    
    echo "GROUP: $groupName\n";
    echo "  PRIMARY TABLE: $primaryTable (most used)\n";
    
    $migrateTables = array_filter(array_keys($tablesWithRefs), fn($t) => $t !== $primaryTable);
    if (!empty($migrateTables)) {
        echo "  TO MIGRATE FROM: " . implode(', ', $migrateTables) . "\n\n";
        
        $migrationSql .= "-- $groupName GROUP\n";
        $migrationSql .= "-- Primary: $primaryTable\n";
        $migrationSql .= "-- Migrate from: " . implode(', ', $migrateTables) . "\n\n";
        
        foreach ($migrateTables as $dupTable) {
            $migrationSql .= "-- Data migration for $dupTable (DO MANUALLY)\n";
            $migrationSql .= "-- INSERT INTO $primaryTable (...) SELECT ... FROM $dupTable;\n\n";
            
            // List files to update
            $files = $tables[$dupTable]['files'] ?? [];
            if (!empty($files)) {
                $migrationSql .= "-- Files referencing $dupTable (update to use $primaryTable):\n";
                foreach ($files as $file) {
                    $migrationSql .= "--   - $file\n";
                }
                $migrationSql .= "\n";
            }
        }
    }
}

echo "\n";
echo "================================================================================\n";
echo "FILES REQUIRING UPDATES\n";
echo "================================================================================\n\n";

$allFilesToUpdate = [];
foreach ($report as $groupName => $group) {
    foreach ($group['tables'] as $tableName => $tableInfo) {
        foreach ($tableInfo['files'] as $file) {
            $allFilesToUpdate[$file][] = $tableName;
        }
    }
}

foreach ($allFilesToUpdate as $file => $tables) {
    echo "FILE: $file\n";
    echo "  References: " . implode(', ', $tables) . "\n\n";
}

// Save migration SQL
file_put_contents(__DIR__ . '/sql/duplicate_migration_plan.sql', $migrationSql);
echo "Migration plan saved to: sql/duplicate_migration_plan.sql\n";

// ============================================
// HELPER FUNCTIONS
// ============================================

function searchCodeReferences($dir, $tableName, &$count, &$files) {
    $extensions = ['.php', '.html', '.blade.php'];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if (!in_array($file->getExtension(), ['php', 'html', 'blade'])) continue;
        
        $content = file_get_contents($file->getPathname());
        
        // Match table references
        $patterns = [
            "/\\b$tableName\\b/i",
            "/FROM `$tableName`/i",
            "/INTO `$tableName`/i",
            "/TABLE `$tableName`/i",
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $count += count($matches[0]);
                $files[] = str_replace(__DIR__ . '/', '', $file->getPathname());
            }
        }
    }
    
    $files = array_unique($files);
}

echo "\nDone!\n";
