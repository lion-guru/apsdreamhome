<?php
/**
 * Migration: Partition booking_payment_schedules by due_date_year + tenant_id HASH
 * 
 * Strategy: 
 * 1. Add regular column due_date_year (INT) populated via trigger
 * 2. RANGE by due_date_year, SUBPARTITION by HASH(tenant_id) with 4 subpartitions
 * 3. Primary key must include partitioning columns: (id, due_date_year, tenant_id)
 * 4. All unique keys must include partitioning columns
 * 
 * Run with: php database/migrations/partition_schedules.php
 * 
 * IMPORTANT: Test on copy table first!
 * CREATE TABLE booking_payment_schedules_partitioned LIKE booking_payment_schedules;
 * Then run partitioning on the copy, verify, then RENAME TABLE.
 * 
 * For zero-downtime: Use pt-online-schema-change or native ALGORITHM=INPLACE
 * Note: Partitioning changes require ALGORITHM=COPY (table rebuild)
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

echo "=== booking_payment_schedules Partitioning Migration ===\n\n";

// Check current table structure
echo "1. Checking current table structure...\n";
$stmt = $pdo->query("SHOW CREATE TABLE booking_payment_schedules");
$createTable = $stmt->fetchColumn(1);
echo "Current table exists: YES\n";

// Check if already partitioned
$stmt = $pdo->query("SELECT PARTITION_METHOD FROM information_schema.PARTITIONS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_payment_schedules' LIMIT 1");
$partitionMethod = $stmt->fetchColumn();
if ($partitionMethod && $partitionMethod !== 'NULL') {
    echo "Table is ALREADY partitioned by: $partitionMethod\n";
    echo "Skipping partitioning.\n";
    exit(0);
}

// Determine year range from data
$stmt = $pdo->query("SELECT MIN(YEAR(due_date)) as min_year, MAX(YEAR(due_date)) as max_year FROM booking_payment_schedules");
$range = $stmt->fetch(PDO::FETCH_ASSOC);
$minYear = (int)$range['min_year'];
$maxYear = (int)$range['max_year'];

// Add buffer years
$startYear = $minYear - 1;
$endYear = $maxYear + 5; // 5 years future

echo "Data year range: $minYear - $maxYear\n";
echo "Partition range: $startYear - $endYear\n";
echo "Subpartitions: 4 (HASH on tenant_id)\n\n";

// Step 1: Add due_date_year column (regular INT, not generated)
echo "2. Adding column due_date_year...\n";
try {
    $pdo->exec("ALTER TABLE booking_payment_schedules ADD COLUMN due_date_year INT NOT NULL DEFAULT 0");
    echo "  Created column due_date_year ✓\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "  Column already exists, continuing...\n";
    } else {
        echo "  ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Step 2: Populate due_date_year from due_date
echo "3. Populating due_date_year from due_date...\n";
try {
    $stmt = $pdo->exec("UPDATE booking_payment_schedules SET due_date_year = YEAR(due_date)");
    echo "  Updated $stmt rows ✓\n";
} catch (PDOException $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Create triggers to maintain due_date_year
echo "4. Creating triggers for due_date_year maintenance...\n";
try {
    // Insert trigger
    $pdo->exec("
        CREATE TRIGGER trg_booking_payment_schedules_insert
        BEFORE INSERT ON booking_payment_schedules
        FOR EACH ROW
        SET NEW.due_date_year = YEAR(NEW.due_date)
    ");
    
    // Update trigger
    $pdo->exec("
        CREATE TRIGGER trg_booking_payment_schedules_update
        BEFORE UPDATE ON booking_payment_schedules
        FOR EACH ROW
        SET NEW.due_date_year = YEAR(NEW.due_date)
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
    $pdo->exec("ALTER TABLE booking_payment_schedules DROP INDEX uniq_booking_inst");
    echo "  Dropped uniq_booking_inst ✓\n";
    
    // Drop primary key, remove AUTO_INCREMENT, add composite PK
    // Must be done in single ALTER TABLE to avoid "auto column" error
    $pdo->exec("
        ALTER TABLE booking_payment_schedules 
        DROP PRIMARY KEY, 
        MODIFY id BIGINT UNSIGNED NOT NULL, 
        ADD PRIMARY KEY (id, due_date_year, tenant_id)
    ");
    echo "  Updated PRIMARY KEY to (id, due_date_year, tenant_id) ✓\n";
    
    // Recreate unique constraint with partitioning columns
    $pdo->exec("
        ALTER TABLE booking_payment_schedules 
        ADD UNIQUE KEY uniq_booking_inst (booking_id, installment_no, due_date_year, tenant_id)
    ");
    echo "  Recreated uniq_booking_inst with partitioning columns ✓\n";
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
ALTER TABLE booking_payment_schedules
PARTITION BY RANGE (due_date_year)
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
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'booking_payment_schedules'
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
    "SELECT * FROM booking_payment_schedules WHERE due_date_year = 2026",
    "SELECT * FROM booking_payment_schedules WHERE due_date_year = 2027 AND tenant_id = 1",
    "SELECT * FROM booking_payment_schedules WHERE due_date >= '2026-01-01' AND due_date < '2027-01-01'",
    "SELECT * FROM booking_payment_schedules WHERE tenant_id = 1 AND status = 'pending'",
    "SELECT * FROM booking_payment_schedules WHERE booking_id = 123 AND due_date_year = 2026",
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
echo "\nNOTE: Application queries should use 'due_date_year' for partition pruning.\n";
echo "Example: WHERE due_date_year = 2026 instead of WHERE YEAR(due_date) = 2026\n";