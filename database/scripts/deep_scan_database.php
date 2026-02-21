<?php
/**
 * Deep Database Scan & Analysis Script
 * Maximum level database health check and integrity verification
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

echo "🔬 DEEP DATABASE SCAN & ANALYSIS\n";
echo "==================================\n\n";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection established\n\n";

    // 1. Basic Database Information
    echo "📊 DATABASE OVERVIEW\n";
    echo "--------------------\n";

    // Database size
    $dbSize = $pdo->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
        FROM information_schema.tables
        WHERE table_schema = '$dbname'
    ")->fetch()['size_mb'];

    echo "- Database size: {$dbSize} MB\n";

    // Table count
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "- Total tables: " . count($tables) . "\n";

    // Row count across all tables
    $totalRows = 0;
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
        $totalRows += $count;
    }
    echo "- Total records: " . number_format($totalRows) . "\n";

    // MySQL version and settings
    $version = $pdo->query("SELECT VERSION() as version")->fetch()['version'];
    echo "- MySQL version: $version\n";

    $charset = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$dbname'")->fetch();
    echo "- Character set: {$charset['DEFAULT_CHARACTER_SET_NAME']}\n";
    echo "- Collation: {$charset['DEFAULT_COLLATION_NAME']}\n\n";

    // 2. Table Structure Analysis
    echo "🗂️  TABLE STRUCTURE ANALYSIS\n";
    echo "----------------------------\n";

    $issues = [];
    $warnings = [];

    foreach ($tables as $table) {
        // Check for tables without primary keys
        $pkCheck = $pdo->query("
            SELECT COUNT(*) as pk_count
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = '$dbname'
            AND TABLE_NAME = '$table'
            AND CONSTRAINT_NAME = 'PRIMARY'
        ")->fetch()['pk_count'];

        if ($pkCheck == 0) {
            $issues[] = "Table '$table' has no primary key";
        }

        // Check for tables without AUTO_INCREMENT on primary keys
        $aiCheck = $pdo->query("
            SELECT COLUMN_NAME, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '$dbname'
            AND TABLE_NAME = '$table'
            AND COLUMN_KEY = 'PRI'
        ")->fetchAll();

        foreach ($aiCheck as $col) {
            if (strpos($col['EXTRA'], 'auto_increment') === false) {
                $warnings[] = "Primary key '{$col['COLUMN_NAME']}' in table '$table' is not auto-increment";
            }
        }

        // Check table engine
        $engine = $pdo->query("
            SELECT ENGINE
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '$dbname'
            AND TABLE_NAME = '$table'
        ")->fetch()['ENGINE'];

        if ($engine !== 'InnoDB') {
            $warnings[] = "Table '$table' uses $engine engine instead of InnoDB";
        }
    }

    echo "- Tables analyzed: " . count($tables) . "\n";
    echo "- Issues found: " . count($issues) . "\n";
    echo "- Warnings found: " . count($warnings) . "\n";

    if (!empty($issues)) {
        echo "\n🚨 CRITICAL ISSUES:\n";
        foreach ($issues as $issue) {
            echo "  • $issue\n";
        }
    }

    if (!empty($warnings)) {
        echo "\n⚠️  WARNINGS:\n";
        foreach ($warnings as $warning) {
            echo "  • $warning\n";
        }
    }
    echo "\n";

    // 3. Index Analysis
    echo "📈 INDEX ANALYSIS\n";
    echo "-----------------\n";

    $totalIndexes = 0;
    $indexDetails = [];

    foreach ($tables as $table) {
        $indexes = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name != 'PRIMARY'")->fetchAll();
        $totalIndexes += count($indexes);

        if (count($indexes) > 10) {
            $indexDetails[] = "'$table': " . count($indexes) . " indexes";
        }
    }

    echo "- Total indexes: $totalIndexes\n";
    echo "- Average indexes per table: " . round($totalIndexes / count($tables), 2) . "\n";

    if (!empty($indexDetails)) {
        echo "- Tables with many indexes:\n";
        foreach ($indexDetails as $detail) {
            echo "  • $detail\n";
        }
    }
    echo "\n";

    // 4. Foreign Key Analysis
    echo "🔗 FOREIGN KEY ANALYSIS\n";
    echo "-----------------------\n";

    $fkStats = $pdo->query("
        SELECT
            COUNT(*) as total_fks,
            COUNT(DISTINCT TABLE_NAME) as tables_with_fks
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$dbname'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ")->fetch();

    echo "- Total foreign keys: {$fkStats['total_fks']}\n";
    echo "- Tables with FKs: {$fkStats['tables_with_fks']}\n";

    // Check for orphaned records (sample check)
    $orphanedChecks = [
        'Leads without valid users' => "
            SELECT COUNT(*) as count FROM leads l
            LEFT JOIN users u ON l.created_by = u.id
            WHERE l.created_by IS NOT NULL AND u.id IS NULL
        ",
        'Properties without valid creators' => "
            SELECT COUNT(*) as count FROM properties p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.created_by IS NOT NULL AND u.id IS NULL
        ",
        'Invoices without valid clients' => "
            SELECT COUNT(*) as count FROM invoices i
            LEFT JOIN users u ON i.client_id = u.id AND i.client_type = 'customer'
            WHERE i.client_id IS NOT NULL AND u.id IS NULL
        "
    ];

    $orphanedFound = false;
    foreach ($orphanedChecks as $checkName => $query) {
        try {
            $result = $pdo->query($query)->fetch();
            $count = $result['count'] ?? 0;
            if ($count > 0) {
                echo "- ⚠️  $checkName: $count orphaned records\n";
                $orphanedFound = true;
            }
        } catch (Exception $e) {
            echo "- $checkName: Check failed\n";
        }
    }

    if (!$orphanedFound) {
        echo "- ✅ No orphaned records detected\n";
    }
    echo "\n";

    // 5. Data Integrity Checks
    echo "🔍 DATA INTEGRITY CHECKS\n";
    echo "-------------------------\n";

    $integrityChecks = [
        'Users with invalid emails' => "
            SELECT COUNT(*) as count FROM users
            WHERE email NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
            AND email IS NOT NULL AND email != ''
        ",
        'Negative prices in properties' => "
            SELECT COUNT(*) as count FROM properties WHERE price < 0
        ",
        'Future invoice dates' => "
            SELECT COUNT(*) as count FROM invoices WHERE invoice_date > CURDATE()
        ",
        'Invalid lead scores' => "
            SELECT COUNT(*) as count FROM leads WHERE lead_score < 0 OR lead_score > 100
        ",
        'Overdue invoices not marked' => "
            SELECT COUNT(*) as count FROM invoices
            WHERE due_date < CURDATE() AND status NOT IN ('paid', 'cancelled')
        "
    ];

    $integrityIssues = 0;
    foreach ($integrityChecks as $checkName => $query) {
        try {
            $result = $pdo->query($query)->fetch();
            $count = $result['count'] ?? 0;
            if ($count > 0) {
                echo "- ⚠️  $checkName: $count issues\n";
                $integrityIssues++;
            }
        } catch (Exception $e) {
            echo "- $checkName: Check failed\n";
        }
    }

    if ($integrityIssues == 0) {
        echo "- ✅ All data integrity checks passed\n";
    }
    echo "\n";

    // 6. Performance Analysis
    echo "⚡ PERFORMANCE ANALYSIS\n";
    echo "-----------------------\n";

    // Largest tables
    $largestTables = $pdo->query("
        SELECT table_name, ROUND(data_length/1024/1024, 2) as size_mb, table_rows
        FROM information_schema.tables
        WHERE table_schema = '$dbname'
        ORDER BY data_length DESC LIMIT 10
    ")->fetchAll();

    echo "- Largest tables by size:\n";
    foreach ($largestTables as $table) {
        echo "  • {$table['table_name']}: {$table['size_mb']}MB, {$table['table_rows']} rows\n";
    }

    // Most active tables (by row count)
    $mostActiveTables = $pdo->query("
        SELECT table_name, table_rows
        FROM information_schema.tables
        WHERE table_schema = '$dbname'
        ORDER BY table_rows DESC LIMIT 10
    ")->fetchAll();

    echo "\n- Most active tables by row count:\n";
    foreach ($mostActiveTables as $table) {
        echo "  • {$table['table_name']}: {$table['table_rows']} rows\n";
    }
    echo "\n";

    // 7. Trigger Analysis
    echo "🎯 TRIGGER ANALYSIS\n";
    echo "-------------------\n";

    $triggers = $pdo->query("SHOW TRIGGERS")->fetchAll();
    echo "- Total triggers: " . count($triggers) . "\n";

    if (!empty($triggers)) {
        $triggerEvents = array_count_values(array_column($triggers, 'Event'));
        foreach ($triggerEvents as $event => $count) {
            echo "- $event triggers: $count\n";
        }
    }
    echo "\n";

    // 8. Stored Procedure Analysis
    echo "⚙️  STORED PROCEDURE ANALYSIS\n";
    echo "-----------------------------\n";

    $procedures = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = '$dbname'")->fetchAll();
    echo "- Total procedures: " . count($procedures) . "\n";

    if (!empty($procedures)) {
        foreach ($procedures as $proc) {
            echo "- {$proc['Name']}: {$proc['Type']}\n";
        }
    }
    echo "\n";

    // 9. View Analysis
    echo "👁️  VIEW ANALYSIS\n";
    echo "-----------------\n";

    $views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);
    echo "- Total views: " . count($views) . "\n";

    if (!empty($views)) {
        foreach ($views as $view) {
            try {
                $count = $pdo->query("SELECT COUNT(*) as count FROM `$view` LIMIT 1")->fetch()['count'];
                echo "- $view: Accessible\n";
            } catch (Exception $e) {
                echo "- $view: ❌ Error accessing view\n";
            }
        }
    }
    echo "\n";

    // 10. Backup & Recovery Status
    echo "💾 BACKUP & RECOVERY STATUS\n";
    echo "----------------------------\n";

    try {
        $binlogStatus = $pdo->query("SHOW BINARY LOGS LIMIT 1")->fetchAll();
        echo "- Binary logging: " . (!empty($binlogStatus) ? "Enabled" : "Disabled") . "\n";
    } catch (Exception $e) {
        echo "- Binary logging: Unable to check\n";
    }

    try {
        $masterStatus = $pdo->query("SHOW MASTER STATUS")->fetch();
        echo "- Master status: " . (!empty($masterStatus) ? "Configured" : "Not configured") . "\n";
    } catch (Exception $e) {
        echo "- Master status: Unable to check\n";
    }
    echo "\n";

    // 11. Final Health Score
    echo "🏥 DATABASE HEALTH SCORE\n";
    echo "=========================\n";

    $healthScore = 100;

    // Deduct for issues
    $healthScore -= count($issues) * 15; // Critical issues
    $healthScore -= count($warnings) * 5; // Warnings
    $healthScore -= $integrityIssues * 10; // Integrity issues

    // Bonus for good practices
    if ($fkStats['total_fks'] > 0) $healthScore += 5;
    if ($totalIndexes > count($tables) * 2) $healthScore += 5;
    if (count($triggers) > 0) $healthScore += 5;
    if (count($views) > 0) $healthScore += 5;

    $healthScore = max(0, min(100, $healthScore));

    echo "🎯 Overall Health Score: $healthScore/100\n\n";

    if ($healthScore >= 90) {
        echo "🏆 EXCELLENT: Database is in excellent condition!\n";
    } elseif ($healthScore >= 80) {
        echo "✅ GOOD: Database is in good condition with minor issues.\n";
    } elseif ($healthScore >= 70) {
        echo "⚠️  FAIR: Database needs some attention.\n";
    } else {
        echo "🚨 POOR: Database needs significant improvements.\n";
    }

    // Recommendations
    echo "\n💡 RECOMMENDATIONS:\n";
    if (!empty($issues)) {
        echo "- Address critical issues immediately\n";
    }
    if (!empty($warnings)) {
        echo "- Review warnings for optimization opportunities\n";
    }
    if ($integrityIssues > 0) {
        echo "- Fix data integrity issues\n";
    }
    if ($fkStats['total_fks'] == 0) {
        echo "- Consider adding foreign key constraints for data integrity\n";
    }
    if ($totalIndexes < count($tables)) {
        echo "- Add more indexes for better query performance\n";
    }

    echo "\n🔬 DEEP SCAN COMPLETED!\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
