<?php
/**
 * Database Verification Script
 * Check all improvements and database health
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "🔍 DATABASE VERIFICATION REPORT\n";
    echo "================================\n\n";

    // 1. Check total tables
    echo "📊 TABLE COUNT:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "- Total tables: " . count($tables) . "\n\n";

    // 2. Check indexes
    echo "📈 INDEX VERIFICATION:\n";
    $indexesCount = 0;
    foreach ($tables as $table) {
        $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll();
        $indexesCount += count($indexes) - 1; // Subtract 1 for PRIMARY KEY
    }
    echo "- Total indexes: $indexesCount\n";

    // Show some key indexes
    $keyIndexes = [
        'users' => ['idx_users_email', 'idx_users_role', 'idx_users_created'],
        'properties' => ['idx_properties_type', 'idx_properties_city', 'idx_properties_price'],
        'leads' => ['idx_leads_status', 'idx_leads_source', 'idx_leads_score'],
        'invoices' => ['idx_invoices_client', 'idx_invoices_status', 'idx_invoices_date']
    ];

    foreach ($keyIndexes as $table => $expectedIndexes) {
        if (in_array($table, $tables)) {
            echo "- $table indexes: ";
            $existingIndexes = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name != 'PRIMARY'")->fetchAll(PDO::FETCH_COLUMN, 2);
            $found = array_intersect($expectedIndexes, $existingIndexes);
            echo count($found) . "/" . count($expectedIndexes) . " found\n";
        }
    }
    echo "\n";

    // 3. Check views
    echo "📋 VIEW VERIFICATION:\n";
    $views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);
    echo "- Total views: " . count($views) . "\n";

    $expectedViews = ['user_summary', 'property_performance', 'business_overview', 'revenue_summary', 'employee_performance'];
    echo "- Expected views: " . count($expectedViews) . "\n";
    $foundViews = array_intersect($expectedViews, $views);
    echo "- Found views: " . count($foundViews) . "/" . count($expectedViews) . "\n";

    if (!empty($foundViews)) {
        echo "- Views found: " . implode(', ', $foundViews) . "\n";
    }
    echo "\n";

    // 4. Check foreign keys
    echo "🔗 FOREIGN KEY VERIFICATION:\n";
    $fkCount = 0;
    foreach ($tables as $table) {
        try {
            $fks = $pdo->query("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = '$dbname'
                AND TABLE_NAME = '$table'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ")->fetchAll(PDO::FETCH_COLUMN);
            $fkCount += count($fks);
        } catch (Exception $e) {
            // Some tables might not have FKs or query might fail
        }
    }
    echo "- Total foreign keys: $fkCount\n\n";

    // 5. Check triggers
    echo "🎯 TRIGGER VERIFICATION:\n";
    $triggers = $pdo->query("SHOW TRIGGERS")->fetchAll();
    echo "- Total triggers: " . count($triggers) . "\n";

    $expectedTriggers = ['update_user_last_login', 'update_property_views', 'update_lead_score_timestamp'];
    if (!empty($triggers)) {
        $triggerNames = array_column($triggers, 'Trigger');
        $foundTriggers = array_intersect($expectedTriggers, $triggerNames);
        echo "- Expected triggers: " . count($expectedTriggers) . "\n";
        echo "- Found triggers: " . count($foundTriggers) . "/" . count($expectedTriggers) . "\n";
    }
    echo "\n";

    // 6. Check stored procedures
    echo "⚙️  STORED PROCEDURE VERIFICATION:\n";
    $procedures = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = '$dbname'")->fetchAll();
    echo "- Total procedures: " . count($procedures) . "\n";

    $expectedProcedures = ['calculate_monthly_revenue', 'get_user_activity_summary'];
    if (!empty($procedures)) {
        $procedureNames = array_column($procedures, 'Name');
        $foundProcedures = array_intersect($expectedProcedures, $procedureNames);
        echo "- Expected procedures: " . count($expectedProcedures) . "\n";
        echo "- Found procedures: " . count($foundProcedures) . "/" . count($expectedProcedures) . "\n";
    }
    echo "\n";

    // 7. Check summary tables
    echo "📊 SUMMARY TABLES VERIFICATION:\n";
    $summaryTables = ['daily_metrics_summary', 'monthly_metrics_summary'];
    $foundSummaryTables = array_intersect($summaryTables, $tables);
    echo "- Expected summary tables: " . count($summaryTables) . "\n";
    echo "- Found summary tables: " . count($foundSummaryTables) . "/" . count($summaryTables) . "\n";

    if (!empty($foundSummaryTables)) {
        echo "- Summary tables: " . implode(', ', $foundSummaryTables) . "\n";
    }
    echo "\n";

    // 8. Check data integrity
    echo "✅ DATA INTEGRITY CHECK:\n";

    // Check for orphaned records
    $integrityChecks = [
        'Leads without valid properties' => "
            SELECT COUNT(*) as count FROM leads l
            LEFT JOIN properties p ON l.property_id = p.id
            WHERE l.property_id IS NOT NULL AND p.id IS NULL
        ",
        'Invoices without valid clients' => "
            SELECT COUNT(*) as count FROM invoices i
            LEFT JOIN users u ON i.client_id = u.id AND i.client_type = 'customer'
            WHERE i.client_id IS NOT NULL AND u.id IS NULL
        ",
        'Enrollments without valid courses' => "
            SELECT COUNT(*) as count FROM user_course_enrollments uce
            LEFT JOIN training_courses tc ON uce.course_id = tc.id
            WHERE tc.id IS NULL
        "
    ];

    foreach ($integrityChecks as $checkName => $query) {
        try {
            $result = $pdo->query($query)->fetch();
            $count = $result['count'] ?? 0;
            echo "- $checkName: $count issues\n";
        } catch (Exception $e) {
            echo "- $checkName: Check failed\n";
        }
    }
    echo "\n";

    // 9. Performance check
    echo "⚡ PERFORMANCE METRICS:\n";

    // Get some basic performance stats
    $performanceStats = [
        'Largest tables' => "
            SELECT table_name, ROUND(data_length/1024/1024, 2) as size_mb, table_rows
            FROM information_schema.tables
            WHERE table_schema = '$dbname'
            ORDER BY data_length DESC LIMIT 5
        ",
        'Most indexed tables' => "
            SELECT table_name, COUNT(*) as index_count
            FROM information_schema.statistics
            WHERE table_schema = '$dbname'
            GROUP BY table_name
            ORDER BY index_count DESC LIMIT 5
        "
    ];

    foreach ($performanceStats as $statName => $query) {
        try {
            echo "- $statName:\n";
            $results = $pdo->query($query)->fetchAll();
            foreach ($results as $result) {
                if (isset($result['size_mb'])) {
                    echo "  • {$result['table_name']}: {$result['size_mb']}MB, {$result['table_rows']} rows\n";
                } else {
                    echo "  • {$result['table_name']}: {$result['index_count']} indexes\n";
                }
            }
        } catch (Exception $e) {
            echo "- $statName: Query failed\n";
        }
    }
    echo "\n";

    // 10. Overall health score
    echo "🏥 DATABASE HEALTH SCORE:\n";

    $healthScore = 100;
    $issues = [];

    // Deduct points for missing indexes
    if ($indexesCount < 30) {
        $healthScore -= 10;
        $issues[] = "Low index count ($indexesCount)";
    }

    // Deduct points for missing views
    if (count($foundViews) < count($expectedViews)) {
        $healthScore -= 5;
        $issues[] = "Missing views (" . (count($expectedViews) - count($foundViews)) . ")";
    }

    // Deduct points for missing triggers
    if (count($triggers) < 3) {
        $healthScore -= 5;
        $issues[] = "Low trigger count (" . count($triggers) . ")";
    }

    // Deduct points for missing procedures
    if (count($procedures) < 2) {
        $healthScore -= 5;
        $issues[] = "Low procedure count (" . count($procedures) . ")";
    }

    echo "- Overall Health Score: $healthScore/100\n";
    if (!empty($issues)) {
        echo "- Issues found:\n";
        foreach ($issues as $issue) {
            echo "  • $issue\n";
        }
    } else {
        echo "- No major issues detected! ✅\n";
    }

    echo "\n🎯 VERIFICATION COMPLETE!\n";
    echo "Your database is " . ($healthScore >= 90 ? "excellent" : ($healthScore >= 80 ? "good" : "needs improvement")) . " condition.\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
