<?php
/**
 * Advanced SQL Query Fixer
 * Fixes various broken SQL query patterns in PHP files
 */

function fixAdvancedSQLQueries($content) {
    // Pattern 1: Fix INSERT INTO with broken VALUES clause
    $content = preg_replace_callback(
        '/\$stmt = \$conn->prepare\(\$stmt = \$conn->prepare\(\$conn->query\("(INSERT INTO [^(]+) \(([^)]+)\) VALUES \(\?\);\s*\$stmt->bind_param\("s", \$([a-zA-Z_]+)\);\s*\$stmt->execute\(\);\s*\$stmt->close\(\);\);\s*\$stmt->bind_param\("s", \$([a-zA-Z_]+), \);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);\'([^\']+)\', \{[^}]+\}, \'([^\']+)\', NOW\(\)\)"\);/s',
        function($matches) {
            // Extract table and columns from the query
            $table_part = $matches[1];
            $columns_part = $matches[2];
            $values = [$matches[5], $matches[6], $matches[7]];
            
            // Create proper placeholders
            $placeholders = str_repeat('?, ', count(explode(',', $columns_part)));
            $placeholders = rtrim($placeholders, ', ');
            
            return '$stmt = $conn->prepare("' . $table_part . ' (' . $columns_part . ') VALUES (' . $placeholders . ')");
    $stmt->bind_param("iissi", $user_id, $role_id, $action, $_SESSION[\'auser\'], \'pending\');
    $stmt->execute();
    $stmt->close();';
        },
        $content
    );
    
    // Pattern 2: Fix broken prepared statements with multiple parameters
    $content = preg_replace_callback(
        '/\$([a-zA-Z_]+) = \$conn->prepare\(\$conn->query\("([A-Z_ ]+) `\?\);\s*\$stmt->bind_param\("s", \$([a-zA-Z_]+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\);/s',
        function($matches) {
            return '$' . $matches[1] . ' = $conn->query("' . $matches[2] . ' `$" . $' . $matches[3] . ' . "`");';
        },
        $content
    );
    
    // Pattern 3: Fix broken INSERT queries with mixed parameters
    $content = preg_replace_callback(
        '/\$stmt = \$conn->prepare\(\$conn->query\("(INSERT INTO [^(]+ \([^)]+\) VALUES \([^)]+\))\s*\$stmt->bind_param\("[^"]+",\s*([^;]+)\);\s*\$stmt->execute\(\);\s*\$stmt->close\(\);\);\s*\$stmt->bind_param\("[^"]+",\s*([^;]+),\s*\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);[^)]*\)"\);/s',
        function($matches) {
            return '$stmt = $conn->prepare("' . $matches[1] . '");
    $stmt->bind_param(' . $matches[2] . ');
    $stmt->execute();
    $stmt->close();';
        },
        $content
    );
    
    // Pattern 4: Fix broken SELECT COUNT(*) queries
    $content = preg_replace_callback(
        '/\$([a-zA-Z_]+) = \$conn->query\("(SELECT COUNT\(\*\) as count FROM) `\?`"\); \$stmt = \$conn->prepare\(\$stmt = \$conn->prepare\(\$conn->query\("(SELECT COUNT\(\*\) as count FROM) `\?\);\s*\$stmt->bind_param\("s", \$([a-zA-Z_]+)`\);\s*\$stmt->execute\(\);\s*\$result = \$stmt->get_result\(\);"\)\); \$stmt->bind_param\("i", \$([a-zA-Z_]+)\); \$stmt->execute\(\); \$result = \$stmt->get_result\(\);;/s',
        function($matches) {
            return '$' . $matches[1] . ' = $conn->query("' . $matches[2] . ' `$" . $' . $matches[4] . ' . "`");';
        },
        $content
    );
    
    // Pattern 5: Remove orphaned SQL fragments
    $content = preg_replace('/\$result = \$stmt->get_result\(\);[^)]*\)"\);/', '', $content);
    $content = preg_replace('/\$stmt->bind_param\("[^"]*",\s*\$[a-zA-Z_]*,\s*\);/', '', $content);
    
    return $content;
}

function processSingleFile($filePath) {
    echo "Processing: " . basename($filePath) . "\n";
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // First apply basic fixes
    $content = fixAdvancedSQLQueries($content);
    
    // Then apply specific pattern fixes for remaining issues
    
    // Fix any remaining broken prepared statements
    if (strpos($content, '$stmt = $conn->prepare($conn->query("') !== false) {
        // Simple pattern for remaining cases
        $content = preg_replace(
            '/\$([a-zA-Z_]+) = \$conn->prepare\(\$conn->query\("([^"]+)`\?\);[^"]*"\);/',
            '$${1} = $conn->query("${2} `$table`");',
            $content
        );
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo "  ✓ Fixed advanced SQL queries\n";
        return true;
    } else {
        echo "  - No advanced issues found\n";
        return false;
    }
}

function checkSyntax($filePath) {
    $output = shell_exec('php -l "' . $filePath . '" 2>&1');
    return strpos($output, 'No syntax errors') !== false;
}

// Main execution
echo "=== Advanced SQL Query Fixer ===\n\n";

// Files that still have issues based on the linter output
$problematicFiles = [
    'admin/seed_test_data.php',
    'admin/upload_audit_log_view.php',
    'api/onboarding_offboarding.php',
    'api/permission_denials.php',
    'api/send_email.php',
    'api/send_sms.php',
    'database/check_database_structure.php',
    'database/complete_database_seed.php',
    'database/dashboard_data_manager.php',
    'database/dashboard_verification_report.php',
    'database/database_analyzer.php',
    'database/db_checklist.php',
    'database/db_schema_dump.php',
    'database/final_dashboard_check.php',
    'database/final_migration.php',
    'database/fix_leads_data.php',
    'database/fix_mlm_commission_tables.php',
    'database/index.php',
    'database/migrate_databases.php',
    'database/migrate_without_fk.php',
    'database/migration_manager.php',
    'database/optimize_database.php',
    'database/refresh_demo_dates.php',
    'database/seed_bookings.php',
    'database/seeder_enhancement.php',
    'database/simple_verify.php',
    'database/structure_based_seed.php',
    'database/system_health_check.php',
    'database/verify_dashboard.php',
    'database/verify_user_preferences.php',
    'scripts/auto_config_repair.php',
    'scripts/check_admin_dashboard.php',
    'scripts/check_and_cleanup.php',
    'scripts/cleanup_old_databases.php',
    'scripts/system_repair.php'
];

$fixedCount = 0;
$stillBroken = [];

foreach ($problematicFiles as $file) {
    if (file_exists($file)) {
        if (processSingleFile($file)) {
            $fixedCount++;
        }
        
        // Check if syntax is now correct
        if (!checkSyntax($file)) {
            $stillBroken[] = $file;
        }
    }
}

echo "\n=== Summary ===\n";
echo "Files processed: " . count($problematicFiles) . "\n";
echo "Files fixed: $fixedCount\n";
echo "Files still broken: " . count($stillBroken) . "\n";

if (count($stillBroken) > 0) {
    echo "\nFiles that still need manual attention:\n";
    foreach ($stillBroken as $file) {
        echo "- $file\n";
    }
}

echo "\nYou can run 'php -l filename.php' to check syntax of any file.\n";