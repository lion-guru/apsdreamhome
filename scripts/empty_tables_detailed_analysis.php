<?php
/**
 * Detailed Empty Tables Analysis
 * Check what work was planned and what code references exist
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== EMPTY TABLES DETAILED ANALYSIS ===\n\n";

// Get all empty tables
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$emptyTables = [];

foreach ($allTables as $table) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($count == 0) {
            $emptyTables[] = $table;
        }
    } catch (Exception $e) {
        // Skip broken tables
    }
}

echo "Found " . count($emptyTables) . " empty tables\n\n";

// Check schema for each empty table
echo "=== SCHEMA ANALYSIS ===\n\n";

$tableSchemas = [];
foreach ($emptyTables as $table) {
    try {
        $columns = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $tableSchemas[$table] = $columns;
        
        echo "📋 Table: $table\n";
        echo "   Columns: " . count($columns) . "\n";
        foreach ($columns as $col) {
            echo "   - {$col['Field']} ({$col['Type']})" . ($col['Key'] ? " [KEY: {$col['Key']}]" : "") . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "❌ Error reading schema for $table: {$e->getMessage()}\n\n";
    }
}

// Check code references
echo "=== CODE REFERENCES CHECK ===\n\n";

$projectRoot = dirname(__DIR__);
$searchDirs = ['app', 'routes', 'public', 'scripts'];

$tableReferences = [];
foreach ($emptyTables as $table) {
    $references = [];
    
    foreach ($searchDirs as $dir) {
        $fullDir = $projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($fullDir)) continue;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (strpos($file->getPathname(), 'vendor') !== false) continue;
            if (strpos($file->getPathname(), 'node_modules') !== false) continue;

            $content = file_get_contents($file->getPathname());
            if ($content && stripos($content, $table) !== false) {
                $references[] = str_replace($projectRoot, '', $file->getPathname());
            }
        }
    }
    
    $tableReferences[$table] = $references;
    
    if (!empty($references)) {
        echo "🔗 Table: $table\n";
        echo "   References: " . count($references) . "\n";
        foreach (array_slice($references, 0, 5) as $ref) {
            echo "   - $ref\n";
        }
        if (count($references) > 5) {
            echo "   ... and " . (count($references) - 5) . " more\n";
        }
        echo "\n";
    }
}

// Categorize empty tables
echo "=== CATEGORIZATION ===\n\n";

$categories = [
    'SAFE_TO_DELETE' => [],      // No references, clearly unused
    'NEEDS_IMPLEMENTATION' => [], // Has references but empty - needs work
    'MERGE_CANDIDATE' => [],      // Can be merged into similar tables
    'KEEP_FUTURE_USE' => []       // Keep for future features
];

foreach ($emptyTables as $table) {
    $refs = $tableReferences[$table];
    $schema = $tableSchemas[$table] ?? [];
    
    // Analyze table name and schema to determine category
    if (empty($refs)) {
        // No references - check if it's clearly unused
        if (preg_match('/cache|temp|test|backup|old|legacy|_bak/i', $table)) {
            $categories['SAFE_TO_DELETE'][] = $table;
        } elseif (preg_match('/virtual_tour|ar_vr|gamification|blockchain|iot/i', $table)) {
            $categories['KEEP_FUTURE_USE'][] = $table;
        } else {
            $categories['SAFE_TO_DELETE'][] = $table;
        }
    } else {
        // Has references - needs implementation or merge
        if (preg_match('/log|audit|history|track/i', $table)) {
            // Log tables - can often be merged
            $categories['MERGE_CANDIDATE'][] = $table;
        } else {
            $categories['NEEDS_IMPLEMENTATION'][] = $table;
        }
    }
}

foreach ($categories as $cat => $tables) {
    echo "📂 $cat (" . count($tables) . " tables)\n";
    foreach ($tables as $t) {
        $refCount = count($tableReferences[$t]);
        echo "   - $t" . ($refCount > 0 ? " [$refCount refs]" : "") . "\n";
    }
    echo "\n";
}

// Generate consolidation recommendations
echo "=== CONSOLIDATION RECOMMENDATIONS ===\n\n";

echo "1. TABLES TO DELETE (Safe - no references):\n";
foreach ($categories['SAFE_TO_DELETE'] as $table) {
    echo "   DROP TABLE `$table`;\n";
}
echo "\n";

echo "2. TABLES THAT NEED IMPLEMENTATION:\n";
foreach ($categories['NEEDS_IMPLEMENTATION'] as $table) {
    $schema = $tableSchemas[$table] ?? [];
    $refs = $tableReferences[$table];
    echo "   📋 $table\n";
    echo "      Schema: " . count($schema) . " columns\n";
    echo "      References: " . count($refs) . " files\n";
    echo "      Action: Implement feature or merge into related table\n";
    echo "      Referenced in:\n";
    foreach (array_slice($refs, 0, 3) as $r) {
        echo "         - $r\n";
    }
    echo "\n";
}

echo "3. MERGE CANDIDATES:\n";
$mergeGroups = [
    'audit_logs' => ['admin_audit_logs', 'audit_log_archive', 'audit_trail', 'data_change_log'],
    'login_logs' => ['login_attempts', 'login_logs', 'failed_login_attempts'],
    'api_logs' => ['api_request_logs', 'api_logs'],
    'email_logs' => ['email_logs', 'email_tracking'],
    'sms_logs' => ['sms_logs', 'sms_otp_logs'],
    'cache_tables' => ['cache_entries', 'cache_tags', 'performance_cache'],
];

foreach ($mergeGroups as $target => $sources) {
    $validSources = array_intersect($sources, $emptyTables);
    if (!empty($validSources)) {
        echo "   Merge into: $target\n";
        foreach ($validSources as $source) {
            echo "      - $source\n";
        }
        echo "\n";
    }
}

echo "\n=== ANALYSIS COMPLETE ===\n";
