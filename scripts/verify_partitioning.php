<?php
/**
 * Partition Verification Script
 * 
 * Purpose: Verify partitioning works correctly with EXPLAIN ANALYZE,
 * check partition pruning, and validate performance improvements.
 * Works with generated column partitioning (created_at_year / due_date_year)
 * 
 * Usage: php scripts/verify_partitioning.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

$tables = [
    'mlm_commission_ledger' => [
        'partition_column' => 'created_at',
        'year_column' => 'created_at_year',
        'tenant_column' => 'tenant_id',
        'test_queries' => [
            'Single year (2025)' => "SELECT * FROM mlm_commission_ledger WHERE created_at_year = 2025",
            'Single year + tenant' => "SELECT * FROM mlm_commission_ledger WHERE created_at_year = 2026 AND tenant_id = 1",
            'Date range (2026)' => "SELECT * FROM mlm_commission_ledger WHERE created_at >= '2026-01-01' AND created_at < '2027-01-01'",
            'Tenant + status' => "SELECT * FROM mlm_commission_ledger WHERE tenant_id = 1 AND status = 'approved'",
            'Booking ID + year' => "SELECT * FROM mlm_commission_ledger WHERE booking_id = 1 AND created_at_year = 2026",
            'Full scan (no pruning)' => "SELECT * FROM mlm_commission_ledger WHERE commission_type = 'direct_sale'",
        ]
    ],
    'booking_payment_schedules' => [
        'partition_column' => 'due_date',
        'year_column' => 'due_date_year',
        'tenant_column' => 'tenant_id',
        'test_queries' => [
            'Single year (2026)' => "SELECT * FROM booking_payment_schedules WHERE due_date_year = 2026",
            'Single year + tenant' => "SELECT * FROM booking_payment_schedules WHERE due_date_year = 2027 AND tenant_id = 1",
            'Date range (2026)' => "SELECT * FROM booking_payment_schedules WHERE due_date >= '2026-01-01' AND due_date < '2027-01-01'",
            'Tenant + status' => "SELECT * FROM booking_payment_schedules WHERE tenant_id = 1 AND status = 'pending'",
            'Booking ID + year' => "SELECT * FROM booking_payment_schedules WHERE booking_id = 1 AND due_date_year = 2026",
            'Full scan (no pruning)' => "SELECT * FROM booking_payment_schedules WHERE amount > 10000",
        ]
    ]
];

echo "=== Partition Verification Report ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table => $config) {
    echo str_repeat('=', 80) . "\n";
    echo "TABLE: $table\n";
    echo str_repeat('=', 80) . "\n\n";
    
    // 1. Check if partitioned
    $stmt = $pdo->prepare("
        SELECT PARTITION_METHOD, SUBPARTITION_METHOD, PARTITION_EXPRESSION, SUBPARTITION_EXPRESSION
        FROM information_schema.PARTITIONS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND PARTITION_NAME IS NOT NULL
        LIMIT 1
    ");
    $stmt->execute([$table]);
    $partitionInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$partitionInfo || $partitionInfo['PARTITION_METHOD'] === 'NULL') {
        echo "STATUS: NOT PARTITIONED\n\n";
        continue;
    }
    
    echo "PARTITION METHOD: {$partitionInfo['PARTITION_METHOD']}\n";
    echo "SUBPARTITION METHOD: {$partitionInfo['SUBPARTITION_METHOD']}\n";
    echo "PARTITION EXPRESSION: {$partitionInfo['PARTITION_EXPRESSION']}\n";
    echo "SUBPARTITION EXPRESSION: {$partitionInfo['SUBPARTITION_EXPRESSION']}\n\n";
    
    // 2. Partition details
    $stmt = $pdo->prepare("
        SELECT 
            PARTITION_NAME, 
            SUBPARTITION_NAME,
            PARTITION_ORDINAL_POSITION,
            SUBPARTITION_ORDINAL_POSITION,
            PARTITION_DESCRIPTION,
            TABLE_ROWS,
            DATA_LENGTH,
            INDEX_LENGTH,
            DATA_FREE
        FROM information_schema.PARTITIONS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
        ORDER BY PARTITION_ORDINAL_POSITION, SUBPARTITION_ORDINAL_POSITION
    ");
    $stmt->execute([$table]);
    $partitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "PARTITION DETAILS:\n";
    echo sprintf("%-20s %-20s %6s %10s %12s %12s %10s\n", 
        'PARTITION', 'SUBPARTITION', 'POS', 'ROWS', 'DATA(MB)', 'INDEX(MB)', 'FREE(MB)');
    echo str_repeat('-', 95) . "\n";
    
    $totalRows = 0;
    $totalDataMb = 0;
    $totalIndexMb = 0;
    $partitionCount = 0;
    $subpartitionCount = 0;
    $currentPartition = '';
    
    foreach ($partitions as $row) {
        $dataMb = round(($row['DATA_LENGTH'] ?? 0) / 1024 / 1024, 2);
        $indexMb = round(($row['INDEX_LENGTH'] ?? 0) / 1024 / 1024, 2);
        $freeMb = round(($row['DATA_FREE'] ?? 0) / 1024 / 1024, 2);
        
        if ($row['PARTITION_NAME'] !== $currentPartition) {
            $currentPartition = $row['PARTITION_NAME'];
            $partitionCount++;
        }
        $subpartitionCount++;
        $totalRows += (int)($row['TABLE_ROWS'] ?? 0);
        $totalDataMb += $dataMb;
        $totalIndexMb += $indexMb;
        
        $subName = $row['SUBPARTITION_NAME'] ?? '';
        echo sprintf("%-20s %-20s %6s %10s %12s %12s %10s\n",
            $row['PARTITION_NAME'],
            $subName,
            $row['PARTITION_ORDINAL_POSITION'],
            $row['TABLE_ROWS'] ?? 0,
            $dataMb,
            $indexMb,
            $freeMb
        );
    }
    
    echo str_repeat('-', 95) . "\n";
    echo sprintf("TOTALS: %d partitions, %d subpartitions, ~%d rows, %.2f MB data, %.2f MB index\n\n",
        $partitionCount, $subpartitionCount, $totalRows, $totalDataMb, $totalIndexMb);
    
    // 3. Partition pruning tests (EXPLAIN PARTITIONS)
    echo "PARTITION PRUNING TESTS (EXPLAIN PARTITIONS):\n";
    echo str_repeat('-', 80) . "\n\n";
    
    $pruningResults = [];
    
    foreach ($config['test_queries'] as $label => $sql) {
        echo "Test: $label\n";
        echo "SQL: $sql\n";
        
        try {
            $stmt = $pdo->query("EXPLAIN PARTITIONS $sql");
            $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $partitionsUsed = [];
            $selectType = '';
            $type = '';
            $key = '';
            $rows = 0;
            $extra = '';
            
            foreach ($plan as $row) {
                $partitionsUsed = array_merge($partitionsUsed, 
                    $row['partitions'] ? explode(',', $row['partitions']) : []);
                $selectType = $row['select_type'];
                $type = $row['type'];
                $key = $row['key'] ?? 'NULL';
                $rows = (int)($row['rows'] ?? 0);
                $extra = $row['Extra'] ?? '';
            }
            
            $uniquePartitions = array_unique($partitionsUsed);
            $pruned = count($uniquePartitions) < $subpartitionCount && count($uniquePartitions) > 0;
            
            echo "  Partitions accessed: " . (empty($uniquePartitions) ? 'ALL (full scan)' : implode(', ', $uniquePartitions)) . "\n";
            echo "  Partition count: " . count($uniquePartitions) . " / $subpartitionCount total\n";
            echo "  Pruning: " . ($pruned ? '✓ EFFECTIVE' : '✗ NO PRUNING') . "\n";
            echo "  Type: $type | Key: $key | Rows: $rows | Extra: $extra\n";
            
            $pruningResults[] = [
                'label' => $label,
                'partitions_accessed' => count($uniquePartitions),
                'total_partitions' => $subpartitionCount,
                'pruned' => $pruned,
                'type' => $type,
                'key' => $key,
                'rows' => $rows
            ];
        } catch (PDOException $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
            $pruningResults[] = [
                'label' => $label,
                'error' => $e->getMessage()
            ];
        }
        echo "\n";
    }
    
    // 4. EXPLAIN ANALYZE (if supported - MariaDB 10.5+)
    echo "EXPLAIN ANALYZE (actual execution):\n";
    echo str_repeat('-', 80) . "\n\n";
    
    $yearCol = $config['year_column'];
    $analyzeQueries = [
        "SELECT COUNT(*) FROM $table WHERE {$config['year_column']} = " . date('Y'),
        "SELECT COUNT(*) FROM $table WHERE tenant_id = 1 AND {$config['year_column']} = " . date('Y'),
    ];
    
    foreach ($analyzeQueries as $sql) {
        echo "Query: $sql\n";
        try {
            $stmt = $pdo->query("EXPLAIN ANALYZE $sql");
            $plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($plan as $row) {
                $keys = array_keys($row);
                foreach ($keys as $k) {
                    if ($row[$k] !== null && $row[$k] !== '') {
                        echo "  $k: {$row[$k]}\n";
                    }
                }
                echo "\n";
            }
        } catch (PDOException $e) {
            // MariaDB 10.4 may not support EXPLAIN ANALYZE
            echo "  Not supported (MariaDB 10.4): " . $e->getMessage() . "\n\n";
            break;
        }
    }
    
    // 5. Summary
    $effectivePruning = array_filter($pruningResults, fn($r) => $r['pruned'] ?? false);
    $totalTests = count($pruningResults);
    $passedTests = count($effectivePruning);
    
    echo "PRUNING SUMMARY:\n";
    echo sprintf("  Tests passed: %d / %d (%.0f%%)\n", $passedTests, $totalTests, 
        $totalTests > 0 ? ($passedTests / $totalTests * 100) : 0);
    
    if ($passedTests === $totalTests && $totalTests > 0) {
        echo "  Status: ✓ ALL TESTS PASSED - Partition pruning working correctly\n";
    } elseif ($passedTests > 0) {
        echo "  Status: ⚠ PARTIAL - Some queries not pruning\n";
    } else {
        echo "  Status: ✗ FAILING - No partition pruning detected\n";
    }
    echo "\n";
}

// Overall status
echo str_repeat('=', 80) . "\n";
echo "OVERALL VERIFICATION STATUS\n";
echo str_repeat('=', 80) . "\n";

$allPartitioned = true;
foreach ($tables as $table => $config) {
    $stmt = $pdo->prepare("SELECT PARTITION_METHOD FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1");
    $stmt->execute([$table]);
    $method = $stmt->fetchColumn();
    $isPartitioned = $method && $method !== 'NULL';
    echo sprintf("  %-30s: %s\n", $table, $isPartitioned ? 'PARTITIONED ✓' : 'NOT PARTITIONED ✗');
    if (!$isPartitioned) $allPartitioned = false;
}

if ($allPartitioned) {
    echo "\n✓ All target tables are partitioned.\n";
    echo "Run this script after deployment to verify pruning in production.\n";
} else {
    echo "\n✗ Some tables are not partitioned. Run migration scripts first.\n";
}

exit(0);