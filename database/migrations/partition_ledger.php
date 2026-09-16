<?php
/**
 * Migration: Partition mlm_commission_ledger by created_at_year + tenant_id HASH
 * 
 * Strategy: 
 * 1. Add regular column created_at_year (INT) populated via trigger
 * 2. RANGE by created_at_year, SUBPARTITION by HASH(tenant_id) with 4 subpartitions
 * 3. Primary key must include partitioning columns: (id, created_at_year, tenant_id)
 * 4. All unique keys must include partitioning columns
 * 
 * Run with: php database/migrations/partition_ledger.php
 * 
 * IMPORTANT: Test on copy table first!
 * CREATE TABLE mlm_commission_ledger_partitioned LIKE mlm_commission_ledger;
 * Then run partitioning on the copy, verify, then RENAME TABLE.
 * 
 * For zero-downtime: Use pt-online-schema-change or native ALGORITHM=INPLACE
 * Note: Partitioning changes require ALGORITHM=COPY (table rebuild)
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "=== mlm_commission_ledger Partitioning Migration ===\n\n";

// Check current table structure
echo "1. Checking current table structure...\n";
$stmt = $pdo->query("SHOW CREATE TABLE mlm_commission_ledger");
$createTable = $stmt->fetchColumn(1);
echo "Current table exists: YES\n";

// Check if already partitioned
$stmt = $pdo->query("SELECT PARTITION_METHOD FROM information_schema.PARTITIONS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mlm_commission_ledger' LIMIT 1");
$partitionMethod = $stmt->fetchColumn();
if ($partitionMethod && $partitionMethod !== 'NULL') {
    echo "Table is ALREADY partitioned by: $partitionMethod\n";
    echo "Skipping partitioning.\n";
    exit(0);
}

// Determine year range from data
$stmt = $pdo->query("SELECT MIN(YEAR(created_at)) as min_year, MAX(YEAR(created_at)) as max_year FROM mlm_commission_ledger");
$range = $stmt->fetch(PDO::FETCH_ASSOC);
$minYear = (int)$range['min_year'];
$maxYear = (int)$range['max_year'];

// Add buffer years
$startYear = $minYear - 1;
$endYear = $maxYear + 5; // 5 years future

echo "Data year range: $minYear - $maxYear\n";
echo "Partition range: $startYear - $endYear\n";
echo "Subpartitions: 4 (HASH on tenant_id)\n\n";

// Step 1: Add created_at_year column (regular INT, not generated)
echo "2. Adding column created_at_year...\n";
try {
    $pdo->exec("ALTER TABLE mlm_commission_ledger ADD COLUMN created_at_year INT NOT NULL DEFAULT 0");
    echo "  Created column created_at_year ✓\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "  Column already exists, continuing...\n";
    } else {
        echo "  ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Step 2: Populate created_at_year from created_at
echo "3. Populating created_at_year from created_at...\n";
try {
    $stmt = $pdo->exec("UPDATE mlm_commission_ledger SET created_at_year = YEAR(created_at)");
    echo "  Updated $stmt rows ✓\n";
} catch (PDOException $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Create triggers to maintain created_at_year
echo "4. Creating triggers for created_at_year maintenance...\n";
try {
    // Insert trigger
    $pdo->exec("
        CREATE TRIGGER trg_mlm_commission_ledger_insert
        BEFORE INSERT ON mlm_commission_ledger
        FOR EACH ROW
        SET NEW.created_at_year = YEAR(NEW.created_at)
    ");
    
    // Update trigger
    $pdo->exec("
        CREATE TRIGGER trg_mlm_commission_ledger_update
        BEFORE UPDATE ON mlm_commission_ledger
        FOR EACH ROW
        SET NEW.created_at_year = YEAR(NEW.created_at)
    ");
    echo "  Created insert/update triggers ✓\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "  Triggers already exist, continuing...\n";
    } else {
        echo "  ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Step 4: Drop and recreate primary key to include partitioning columns
echo "5. Updating primary key to include partitioning columns...\n";
try {
    // Drop unique constraint first (conflicts with PK change)
    $pdo->exec("ALTER TABLE mlm_commission_ledger DROP INDEX uq_no_double_commission");
    echo "  Dropped uq_no_double_commission ✓\n";
    
    // Drop primary key, remove AUTO_INCREMENT, add composite PK
    // Must be done in single ALTER TABLE to avoid "auto column" error
    $pdo->exec("
        ALTER TABLE mlm_commission_ledger 
        DROP PRIMARY KEY, 
        MODIFY id BIGINT UNSIGNED NOT NULL, 
        ADD PRIMARY KEY (id, created_at_year, tenant_id)
    ");
    echo "  Updated PRIMARY KEY to (id, created_at_year, tenant_id) ✓\n";
    
    // Recreate unique constraint with partitioning columns
    $pdo->exec("
        ALTER TABLE mlm_commission_ledger 
        ADD UNIQUE KEY uq_no_double_commission (booking_id, receipt_id, beneficiary_user_id, commission_type, created_at_year, tenant_id)
    ");
    echo "  Recreated uq_no_double_commission with partitioning columns ✓\n";
} catch (PDOException $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 6: Apply partitioning
$partitions = [];
for ($year = $startYear; $year <= $endYear; $year++) {
    $nextYear = $year + 1;
    $partitions[] = "PARTITION p{$year} VALUES LESS THAN ({$nextYear}) 
        (SUBPARTITION p{$year}_s0 ENGINE=InnoDB,
         SUBPARTITION p{$year}_s1 ENGINE=InnoDB,
         SUBPARTITION p{$year}_s2 ENGINE=InnoDB,
         SUBPARTITION p{$year}_s3 ENGINE=InnoDB)";
}

// Add MAXVALUE partition for safety
$partitions[] = "PARTITION p_max VALUES LESS THAN MAXVALUE 
    (SUBPARTITION p_max_s0 ENGINE=InnoDB,
     SUBPARTITION p_max_s1 ENGINE=InnoDB,
     SUBPARTITION p_max_s2 ENGINE=InnoDB,
     SUBPARTITION p_max_s3 ENGINE=InnoDB)";

$partitionDef = implode(",\n", $partitions);

$alterSql = "
ALTER TABLE mlm_commission_ledger
PARTITION BY RANGE (created_at_year)
SUBPARTITION BY HASH(tenant_id)
SUBPARTITIONS 4
(
$partitionDef
)";

echo "6. Executing ALTER TABLE partitioning...\n";
echo "This may take a while for large tables...\n\n";

try {
    $startTime = microtime(true);
    $pdo->exec($alterSql);
    $elapsed = microtime(true) - $startTime;
    echo "SUCCESS: Table partitioned in " . round($elapsed, 2) . " seconds\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Verify partitioning
echo "\n7. Verifying partitioning...\n";
$stmt = $pdo->query("
    SELECT PARTITION_NAME, SUBPARTITION_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH
    FROM information_schema.PARTITIONS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mlm_commission_ledger'
    ORDER BY PARTITION_ORDINAL_POSITION, SUBPARTITION_ORDINAL_POSITION
");

echo sprintf("%-20s %-20s %10s %12s %12s\n", 'PARTITION', 'SUBPARTITION', 'ROWS', 'DATA(MB)', 'INDEX(MB)');
echo str_repeat('-', 80) . "\n";

$totalRows = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dataMb = round($row['DATA_LENGTH'] / 1024 / 1024, 2);
    $indexMb = round($row['INDEX_LENGTH'] / 1024 / 1024, 2);
    echo sprintf("%-20s %-20s %10s %12s %12s\n", 
        $row['PARTITION_NAME'], 
        $row['SUBPARTITION_NAME'] ?? '',
        $row['TABLE_ROWS'] ?? 0,
        $dataMb,
        $indexMb
    );
    $totalRows += (int)($row['TABLE_ROWS'] ?? 0);
}
echo str_repeat('-', 80) . "\n";
echo "Total estimated rows: $totalRows\n";

// Test partition pruning
echo "\n8. Testing partition pruning with EXPLAIN...\n";
$testQueries = [
    "SELECT * FROM mlm_commission_ledger WHERE created_at_year = 2025",
    "SELECT * FROM mlm_commission_ledger WHERE created_at_year = 2026 AND tenant_id = 1",
    "SELECT * FROM mlm_commission_ledger WHERE created_at >= '2026-01-01' AND created_at < '2027-01-01'",
    "SELECT * FROM mlm_commission_ledger WHERE tenant_id = 1 AND status = 'approved'",
];

foreach ($testQueries as $sql) {
    echo "\nQuery: $sql\n";
    $stmt = $pdo->query("EXPLAIN PARTITIONS $sql");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("  id=%s select_type=%s table=%s partitions=%s type=%s key=%s rows=%s Extra=%s\n",
            $row['id'], $row['select_type'], $row['table'], $row['partitions'],
            $row['type'], $row['key'], $row['rows'], $row['Extra']);
    }
}

echo "\n=== Migration Complete ===\n";
echo "\nNOTE: Application queries should use 'created_at_year' for partition pruning.\n";
echo "Example: WHERE created_at_year = 2026 instead of WHERE YEAR(created_at) = 2026\n";