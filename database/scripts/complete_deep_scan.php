<?php
/**
 * Complete Deep Database Scan & Analysis Script
 * Maximum level database health check and comprehensive analysis
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

echo "🔬 COMPLETE DEEP DATABASE SCAN & ANALYSIS\n";
echo "========================================\n\n";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection established\n\n";

    // 1. Complete Database Overview
    echo "📊 COMPLETE DATABASE OVERVIEW\n";
    echo "-----------------------------\n";

    // Database size and statistics
    $dbStats = $pdo->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb,
            ROUND(SUM(data_length) / 1024 / 1024, 2) as data_mb,
            ROUND(SUM(index_length) / 1024 / 1024, 2) as index_mb,
            COUNT(*) as total_tables
        FROM information_schema.tables
        WHERE table_schema = '$dbname'
    ")->fetch();

    echo "- Database Size: {$dbStats['size_mb']} MB\n";
    echo "- Data Size: {$dbStats['data_mb']} MB\n";
    echo "- Index Size: {$dbStats['index_mb']} MB\n";
    echo "- Total Tables: {$dbStats['total_tables']}\n";

    // MySQL version and configuration
    $mysqlVersion = $pdo->query("SELECT VERSION() as version")->fetch()['version'];
    echo "- MySQL Version: $mysqlVersion\n";

    // Character set and collation
    $charset = $pdo->query("
        SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
        FROM information_schema.SCHEMATA
        WHERE SCHEMA_NAME = '$dbname'
    ")->fetch();
    echo "- Character Set: {$charset['DEFAULT_CHARACTER_SET_NAME']}\n";
    echo "- Collation: {$charset['DEFAULT_COLLATION_NAME']}\n";

    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "- Table Count: " . count($tables) . "\n\n";

    // 2. Comprehensive Table Analysis
    echo "🗂️  COMPREHENSIVE TABLE ANALYSIS\n";
    echo "-------------------------------\n";

    $tableAnalysis = [];
    $issues = [];
    $warnings = [];
    $optimizations = [];

    foreach ($tables as $table) {
        $tableInfo = [
            'name' => $table,
            'engine' => '',
            'rows' => 0,
            'size' => 0,
            'has_pk' => false,
            'has_ai' => false,
            'indexes' => 0,
            'foreign_keys' => 0,
            'issues' => [],
            'recommendations' => []
        ];

        // Table engine and size
        $tableDetails = $pdo->query("
            SELECT ENGINE, TABLE_ROWS, ROUND(DATA_LENGTH + INDEX_LENGTH, 2) as size_bytes
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'
        ")->fetch();

        $tableInfo['engine'] = $tableDetails['ENGINE'];
        $tableInfo['rows'] = $tableDetails['TABLE_ROWS'];
        $tableInfo['size'] = $tableDetails['size_bytes'];

        // Primary key analysis
        $pkInfo = $pdo->query("
            SELECT COLUMN_NAME, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table' AND COLUMN_KEY = 'PRI'
        ")->fetchAll();

        $tableInfo['has_pk'] = !empty($pkInfo);
        if (!empty($pkInfo)) {
            $tableInfo['has_ai'] = strpos($pkInfo[0]['EXTRA'], 'auto_increment') !== false;
        }

        // Index count
        $indexCount = $pdo->query("
            SELECT COUNT(*) as count
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'
        ")->fetch()['count'];

        $tableInfo['indexes'] = $indexCount;

        // Foreign key count
        $fkCount = $pdo->query("
            SELECT COUNT(*) as count
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ")->fetch()['count'];

        $tableInfo['foreign_keys'] = $fkCount;

        // Issues and recommendations
        if (!$tableInfo['has_pk']) {
            $tableInfo['issues'][] = 'No primary key';
            $issues[] = "$table: Missing primary key";
        }

        if ($tableInfo['has_pk'] && !$tableInfo['has_ai'] && $pkInfo[0]['COLUMN_NAME'] === 'id') {
            $tableInfo['recommendations'][] = 'Consider auto-increment for primary key';
            $warnings[] = "$table: Primary key not auto-increment";
        }

        if ($tableInfo['engine'] !== 'InnoDB') {
            $tableInfo['issues'][] = 'Not using InnoDB engine';
            $issues[] = "$table: Using {$tableInfo['engine']} instead of InnoDB";
        }

        if ($tableInfo['rows'] > 10000 && $tableInfo['indexes'] < 3) {
            $tableInfo['recommendations'][] = 'Consider adding more indexes for large tables';
            $optimizations[] = "$table: Large table with few indexes";
        }

        $tableAnalysis[] = $tableInfo;
    }

    echo "- Tables Analyzed: " . count($tableAnalysis) . "\n";
    echo "- Critical Issues: " . count($issues) . "\n";
    echo "- Warnings: " . count($warnings) . "\n";
    echo "- Optimization Opportunities: " . count($optimizations) . "\n\n";

    // 3. Data Integrity Deep Scan
    echo "🔍 DATA INTEGRITY DEEP SCAN\n";
    echo "--------------------------\n";

    $integrityChecks = [
        'Orphaned Records' => [
            'leads_without_users' => "
                SELECT COUNT(*) as count FROM leads l
                LEFT JOIN users u ON l.created_by = u.id
                WHERE l.created_by IS NOT NULL AND u.id IS NULL
            ",
            'properties_without_creators' => "
                SELECT COUNT(*) as count FROM properties p
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.created_by IS NOT NULL AND u.id IS NULL
            ",
            'invoices_without_clients' => "
                SELECT COUNT(*) as count FROM invoices i
                LEFT JOIN users u ON i.client_id = u.id AND i.client_type = 'customer'
                WHERE i.client_id IS NOT NULL AND u.id IS NULL
            "
        ],
        'Data Validation' => [
            'invalid_emails' => "
                SELECT COUNT(*) as count FROM users
                WHERE email NOT REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$'
                AND email IS NOT NULL AND email != ''
            ",
            'negative_prices' => "
                SELECT COUNT(*) as count FROM properties WHERE price < 0
            ",
            'invalid_dates' => "
                SELECT COUNT(*) as count FROM invoices WHERE invoice_date > CURDATE()
            ",
            'invalid_scores' => "
                SELECT COUNT(*) as count FROM leads WHERE lead_score < 0 OR lead_score > 100
            "
        ],
        'Consistency Checks' => [
            'overdue_invoices' => "
                SELECT COUNT(*) as count FROM invoices
                WHERE due_date < CURDATE() AND status NOT IN ('paid', 'cancelled')
            ",
            'duplicate_emails' => "
                SELECT COUNT(*) as count FROM users
                WHERE email IN (
                    SELECT email FROM users GROUP BY email HAVING COUNT(*) > 1
                )
            ",
            'inactive_users_with_activity' => "
                SELECT COUNT(*) as count FROM users u
                LEFT JOIN user_sessions s ON u.id = s.user_id
                WHERE u.status = 'inactive' AND s.last_activity >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            "
        ]
    ];

    $integrityIssues = 0;
    foreach ($integrityChecks as $category => $checks) {
        echo "\n$category:\n";
        foreach ($checks as $checkName => $query) {
            try {
                $result = $pdo->query($query)->fetch();
                $count = $result['count'] ?? 0;
                if ($count > 0) {
                    echo "  ⚠️  $checkName: $count issues\n";
                    $integrityIssues += $count;
                } else {
                    echo "  ✅ $checkName: No issues\n";
                }
            } catch (Exception $e) {
                echo "  ❌ $checkName: Check failed - " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n- Total Integrity Issues: $integrityIssues\n\n";

    // 4. Performance Analysis
    echo "⚡ PERFORMANCE ANALYSIS\n";
    echo "---------------------\n";

    // Largest tables
    $largestTables = $pdo->query("
        SELECT table_name, ROUND(data_length/1024/1024, 2) as size_mb, table_rows
        FROM information_schema.tables
        WHERE table_schema = '$dbname'
        ORDER BY data_length DESC LIMIT 10
    ")->fetchAll();

    echo "- Largest Tables by Size:\n";
    foreach ($largestTables as $table) {
        echo "  • {$table['table_name']}: {$table['size_mb']}MB, {$table['table_rows']} rows\n";
    }

    // Most indexed tables
    $mostIndexedTables = $pdo->query("
        SELECT table_name, COUNT(*) as index_count
        FROM information_schema.statistics
        WHERE table_schema = '$dbname'
        GROUP BY table_name
        ORDER BY index_count DESC LIMIT 10
    ")->fetchAll();

    echo "\n- Most Indexed Tables:\n";
    foreach ($mostIndexedTables as $table) {
        echo "  • {$table['table_name']}: {$table['index_count']} indexes\n";
    }

    // Tables with most rows
    $mostRowsTables = $pdo->query("
        SELECT table_name, table_rows
        FROM information_schema.tables
        WHERE table_schema = '$dbname'
        ORDER BY table_rows DESC LIMIT 10
    ")->fetchAll();

    echo "\n- Tables with Most Rows:\n";
    foreach ($mostRowsTables as $table) {
        echo "  • {$table['table_name']}: {$table['table_rows']} rows\n";
    }

    // Index efficiency analysis
    echo "\n- Index Efficiency Analysis:\n";
    foreach ($tableAnalysis as $table) {
        if ($table['rows'] > 1000) {
            $indexRatio = $table['indexes'] / max(1, $table['rows'] / 1000);
            if ($indexRatio < 0.01) {
                echo "  ⚠️  {$table['name']}: Low index ratio for table size\n";
            } elseif ($indexRatio > 0.1) {
                echo "  ℹ️  {$table['name']}: High index count (may impact writes)\n";
            }
        }
    }

    // 5. Security Analysis
    echo "\n🔒 SECURITY ANALYSIS\n";
    echo "------------------\n";

    // Check for sensitive data exposure
    $securityChecks = [
        'passwords_in_plaintext' => "
            SELECT COUNT(*) as count FROM information_schema.columns
            WHERE table_schema = '$dbname'
            AND column_name LIKE '%password%'
            AND data_type NOT LIKE '%hash%'
        ",
        'sensitive_columns' => "
            SELECT COUNT(*) as count FROM information_schema.columns
            WHERE table_schema = '$dbname'
            AND column_name IN ('ssn', 'credit_card', 'bank_account', 'pan', 'aadhaar')
        ",
        'admin_tables' => "
            SELECT COUNT(*) as count FROM information_schema.tables
            WHERE table_schema = '$dbname'
            AND table_name LIKE '%admin%'
        "
    ];

    foreach ($securityChecks as $checkName => $query) {
        try {
            $result = $pdo->query($query)->fetch();
            $count = $result['count'] ?? 0;
            if ($count > 0) {
                echo "  ⚠️  $checkName: $count items found\n";
            } else {
                echo "  ✅ $checkName: No issues\n";
            }
        } catch (Exception $e) {
            echo "  ❌ $checkName: Check failed\n";
        }
    }

    // 6. Database Objects Analysis
    echo "\n📋 DATABASE OBJECTS ANALYSIS\n";
    echo "---------------------------\n";

    // Views
    $views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);
    echo "- Views: " . count($views) . "\n";

    // Stored procedures
    $procedures = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = '$dbname'")->fetchAll();
    echo "- Stored Procedures: " . count($procedures) . "\n";

    // Functions
    $functions = $pdo->query("SHOW FUNCTION STATUS WHERE Db = '$dbname'")->fetchAll();
    echo "- Functions: " . count($functions) . "\n";

    // Triggers
    $triggers = $pdo->query("SHOW TRIGGERS")->fetchAll();
    echo "- Triggers: " . count($triggers) . "\n";

    // Events
    $events = $pdo->query("SHOW EVENTS")->fetchAll();
    echo "- Events: " . count($events) . "\n";

    // 7. Missing Objects Analysis
    echo "\n🔍 MISSING OBJECTS ANALYSIS\n";
    echo "-------------------------\n";

    $expectedObjects = [
        'views' => ['user_summary', 'property_performance', 'business_overview', 'revenue_summary', 'employee_performance'],
        'procedures' => ['calculate_monthly_revenue', 'get_user_activity_summary'],
        'triggers' => ['update_user_last_login', 'update_property_views', 'update_lead_score_timestamp']
    ];

    foreach ($expectedObjects as $type => $expectedList) {
        $existing = $type === 'views' ? $views : 
                   ($type === 'procedures' ? array_column($procedures, 'Name') :
                   ($type === 'triggers' ? array_column($triggers, 'Trigger') : []));

        $missing = array_diff($expectedList, $existing);
        
        echo "- Missing $type: " . count($missing) . "\n";
        if (!empty($missing)) {
            foreach ($missing as $item) {
                echo "  • $item\n";
            }
        }
    }

    // 8. Database Health Score
    echo "\n🏥 DATABASE HEALTH SCORE\n";
    echo "=====================\n";

    $healthScore = 100;
    $scoreDetails = [];

    // Deduct for critical issues
    $healthScore -= count($issues) * 10;
    $scoreDetails['critical_issues'] = count($issues) * 10;

    // Deduct for warnings
    $healthScore -= count($warnings) * 5;
    $scoreDetails['warnings'] = count($warnings) * 5;

    // Deduct for integrity issues
    $healthScore -= min($integrityIssues * 2, 20);
    $scoreDetails['integrity_issues'] = min($integrityIssues * 2, 20);

    // Bonus points for good practices
    if (count($views) >= 5) $healthScore += 5;
    if (count($procedures) >= 2) $healthScore += 5;
    if (count($triggers) >= 3) $healthScore += 5;
    if ($dbStats['index_mb'] > $dbStats['data_mb'] * 0.3) $healthScore += 5;

    $scoreDetails['bonus_points'] = 15;

    $healthScore = max(0, min(100, $healthScore));

    echo "🎯 Overall Health Score: $healthScore/100\n";

    if ($healthScore >= 90) {
        echo "🏆 EXCELLENT: Database is in excellent condition!\n";
    } elseif ($healthScore >= 80) {
        echo "✅ GOOD: Database is in good condition with minor issues.\n";
    } elseif ($healthScore >= 70) {
        echo "⚠️  FAIR: Database needs some attention.\n";
    } else {
        echo "🚨 POOR: Database needs significant improvements.\n";
    }

    echo "\nScore Breakdown:\n";
    foreach ($scoreDetails as $factor => $score) {
        if ($score < 0) {
            echo "  • $factor: $score points\n";
        } else {
            echo "  • $factor: +$score points\n";
        }
    }

    // 9. Recommendations
    echo "\n💡 RECOMMENDATIONS\n";
    echo "==================\n";

    $recommendations = [];

    if (!empty($issues)) {
        $recommendations[] = "Address critical issues immediately";
    }

    if (!empty($warnings)) {
        $recommendations[] = "Review warnings for optimization opportunities";
    }

    if ($integrityIssues > 0) {
        $recommendations[] = "Fix data integrity issues";
    }

    if (count($views) < 5) {
        $recommendations[] = "Create missing database views for better analytics";
    }

    if (count($procedures) < 2) {
        $recommendations[] = "Add more stored procedures for complex operations";
    }

    if (count($triggers) < 3) {
        $recommendations[] = "Implement triggers for automated data maintenance";
    }

    if ($dbStats['index_mb'] < $dbStats['data_mb'] * 0.2) {
        $recommendations[] = "Consider adding more indexes for better query performance";
    }

    foreach ($recommendations as $i => $rec) {
        echo ($i + 1) . ". $rec\n";
    }

    // 10. Export Summary Report
    echo "\n📊 SUMMARY REPORT\n";
    echo "===============\n";

    $summary = [
        'database_name' => $dbname,
        'scan_date' => date('Y-m-d H:i:s'),
        'health_score' => $healthScore,
        'total_tables' => count($tables),
        'total_views' => count($views),
        'total_procedures' => count($procedures),
        'total_triggers' => count($triggers),
        'database_size_mb' => $dbStats['size_mb'],
        'critical_issues' => count($issues),
        'warnings' => count($warnings),
        'integrity_issues' => $integrityIssues,
        'optimization_opportunities' => count($optimizations)
    ];

    foreach ($summary as $key => $value) {
        echo "$key: $value\n";
    }

    echo "\n🎉 COMPLETE DEEP SCAN FINISHED!\n";
    echo "Your database has been thoroughly analyzed and is ready for optimization.\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    // DEBUG CODE REMOVED: 2026-02-22 19:56:15 CODE REMOVED: 2026-02-22 19:56:15
}
?>
