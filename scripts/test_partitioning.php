<?php
/**
 * Test Partitioning on Copy Tables
 * 
 * Purpose: Test partitioning strategy on copied tables before applying to production
 * Uses regular column + trigger approach (not generated column)
 * Run this first to validate the approach!
 * 
 * Usage: php scripts/test_partitioning.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "=== Partition Testing on Copy Tables ===\n\n";

$testTables = [
    'mlm_commission_ledger' => [
        'partition_col' => 'created_at',
        'year_col' => 'created_at_year',
        'year_func' => 'YEAR(created_at)',
    ],
    'booking_payment_schedules' => [
        'partition_col' => 'due_date',
        'year_col' => 'due_date_year',
        'year_func' => 'YEAR(due_date)',
    ],
];

foreach ($testTables as $table => $config) {
    $copyTable = $table . '_partition_test';
    $partitionCol = $config['partition_col'];
    $yearCol = $config['year_col'];
    $yearFunc = $config['year_func'];
    
    echo "Testing table: $table -> $copyTable\n";
    
    // 1. Drop old test table if exists
    $pdo->exec("DROP TABLE IF EXISTS $copyTable");
    
    // 2. Create copy with same structure
    echo "  Creating copy table...\n";
    $pdo->exec("CREATE TABLE $copyTable LIKE $table");
    
    // 3. Add year column to copy
    $pdo->exec("ALTER TABLE $copyTable ADD COLUMN $yearCol INT NOT NULL DEFAULT 0");
    
    // 4. Copy data with year column
    echo "  Copying data...\n";
    $start = microtime(true);
    
    // Get column list excluding the new year column
    $stmt = $pdo->query("SHOW COLUMNS FROM $table");
    $columns = [];
    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $col['Field'];
    }
    $columnList = implode(', ', $columns);
    $columnListWithYear = "$columnList, $yearCol";
    $selectList = "$columnList, $yearFunc";
    
    $pdo->exec("INSERT INTO $copyTable ($columnListWithYear) SELECT $selectList FROM $table");
    $copyTime = microtime(true) - $start;
    echo "  Copied in " . round($copyTime, 2) . "s\n";
    
    // 5. Verify row count
    $origCount = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    $copyCount = $pdo->query("SELECT COUNT(*) FROM $copyTable")->fetchColumn();
    echo "  Row count: Original=$origCount, Copy=$copyCount " . ($origCount == $copyCount ? '✓' : '✗ MISMATCH') . "\n";
    
    // 6. Get year range
    $stmt = $pdo->query("SELECT MIN($yearCol) as min_y, MAX($yearCol) as max_y FROM $copyTable");
    $range = $stmt->fetch(PDO::FETCH_ASSOC);
    $minYear = (int)$range['min_y'];
    $maxYear = (int)$range['max_y'];
    $startYear = $minYear - 1;
    $endYear = $maxYear + 2;
    
    echo "  Year range: $minYear - $maxYear, Partition: $startYear - $endYear\n";
    
    // 7. Drop and recreate PK + unique keys
    try {
        if ($table === 'mlm_commission_ledger') {
            $pdo->exec("ALTER TABLE $copyTable DROP INDEX uq_no_double_commission");
        } else {
            $pdo->exec("ALTER TABLE $copyTable DROP INDEX uniq_booking_inst");
        }
        $pdo->exec("ALTER TABLE $copyTable DROP PRIMARY KEY, MODIFY id BIGINT UNSIGNED NOT NULL, ADD PRIMARY KEY (id, $yearCol, tenant_id)");
        
        if ($table === 'mlm_commission_ledger') {
            $pdo->exec("ALTER TABLE $copyTable ADD UNIQUE KEY uq_no_double_commission (booking_id, receipt_id, beneficiary_user_id, commission_type, $yearCol, tenant_id)");
        } else {
            $pdo->exec("ALTER TABLE $copyTable ADD UNIQUE KEY uniq_booking_inst (booking_id, installment_no, $yearCol, tenant_id)");
        }
    } catch (PDOException $e) {
        echo "  Key update error: " . $e->getMessage() . "\n";
    }
    
    // 8. Build partition definition
    $partitions = [];
    for ($year = $startYear; $year <= $endYear; $year++) {
        $nextYear = $year + 1;
        $partitions[] = "PARTITION p{$year} VALUES LESS THAN ({$nextYear}) 
            (SUBPARTITION p{$year}_s0 ENGINE=InnoDB,
             SUBPARTITION p{$year}_s1 ENGINE=InnoDB,
             SUBPARTITION p{$year}_s2 ENGINE=InnoDB,
             SUBPARTITION p{$year}_s3 ENGINE=InnoDB)";
    }
    $partitions[] = "PARTITION p_max VALUES LESS THAN MAXVALUE 
        (SUBPARTITION p_max_s0 ENGINE=InnoDB,
         SUBPARTITION p_max_s1 ENGINE=InnoDB,
         SUBPARTITION p_max_s2 ENGINE=InnoDB,
         SUBPARTITION p_max_s3 ENGINE=InnoDB)";
    $partitionDef = implode(",\n", $partitions);
    
    // 9. Apply partitioning
    $alterSql = "ALTER TABLE $copyTable PARTITION BY RANGE ($yearCol) SUBPARTITION BY HASH(tenant_id) SUBPARTITIONS 4 ($partitionDef)";
    
    echo "  Applying partitioning...\n";
    $start = microtime(true);
    try {
        $pdo->exec($alterSql);
        $alterTime = microtime(true) - $start;
        echo "  Partitioned in " . round($alterTime, 2) . "s ✓\n";
    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        continue;
    }
    
    // 10. Verify partition pruning
    echo "  Testing partition pruning...\n";
    
    $testQueries = [
        "SELECT COUNT(*) FROM $copyTable WHERE $yearCol = " . $maxYear,
        "SELECT COUNT(*) FROM $copyTable WHERE $yearCol = " . $maxYear . " AND tenant_id = 1",
        "SELECT COUNT(*) FROM $copyTable WHERE $partitionCol >= '" . $maxYear . "-01-01' AND $partitionCol < '" . ($maxYear+1) . "-01-01'",
        "SELECT COUNT(*) FROM $copyTable WHERE tenant_id = 1",
    ];
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$copyTable' AND SUBPARTITION_NAME IS NOT NULL");
    $totalSubparts = (int)$stmt->fetchColumn();
    
    foreach ($testQueries as $sql) {
        $stmt = $pdo->query("EXPLAIN PARTITIONS $sql");
        $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $partsUsed = [];
        foreach ($plan as $row) {
            if ($row['partitions']) {
                $partsUsed = array_merge($partsUsed, explode(',', $row['partitions']));
            }
        }
        $uniqueParts = array_unique($partsUsed);
        $pruned = count($uniqueParts) < $totalSubparts && count($uniqueParts) > 0;
        echo "    " . ($pruned ? '✓' : '✗') . " Partitions: " . (empty($uniqueParts) ? 'ALL' : implode(',', $uniqueParts)) . " ($uniqueParts of $totalSubparts)\n";
    }
    
    // 11. Performance comparison
    echo "  Performance test...\n";
    
    // Non-partitioned query (full scan on original)
    $start = microtime(true);
    $pdo->query("SELECT COUNT(*) FROM $table WHERE $yearFunc = $maxYear")->fetchColumn();
    $origTime = microtime(true) - $start;
    
    // Partitioned query
    $start = microtime(true);
    $pdo->query("SELECT COUNT(*) FROM $copyTable WHERE $yearCol = $maxYear")->fetchColumn();
    $partTime = microtime(true) - $start;
    
    echo "    Original table: " . round($origTime * 1000, 2) . "ms\n";
    echo "    Partitioned table: " . round($partTime * 1000, 2) . "ms\n";
    if ($origTime > 0) {
        $improvement = round(($origTime - $partTime) / $origTime * 100, 1);
        echo "    Improvement: " . ($improvement > 0 ? "+$improvement%" : "$improvement%") . "\n";
    }
    
    // 12. Clean up
    $pdo->exec("DROP TABLE $copyTable");
    echo "  Test table cleaned up.\n\n";
}

echo "=== All Tests Complete ===\n";
echo "If tests pass, run the actual migrations:\n";
echo "  php database/migrations/partition_ledger.php\n";
echo "  php database/migrations/partition_schedules.php\n";

exit(0);