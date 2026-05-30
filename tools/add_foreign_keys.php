<?php
/**
 * Script: add_foreign_keys.php
 * Purpose: Add FK constraints to apsdreamhome database with pre-flight checks.
 *
 * For each constraint:
 * 1. Check both tables exist
 * 2. Check both columns exist
 * 3. Check constraint doesn't already exist
 * 4. Check for orphaned records that would violate the FK
 * 5. If orphans exist, report them but skip
 * 6. Add FK with ON DELETE SET NULL ON UPDATE CASCADE
 */

$host = '127.0.0.1';
$port = 3307;
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';

echo "=== APS Dream Home - Foreign Key Constraint Addition ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "DB connection: OK\n\n";
} catch (Exception $e) {
    die("DB connection FAILED: " . $e->getMessage() . "\n");
}

// ─── FK definitions ───────────────────────────────────────────────
$constraints = [
    // child_table  => [child_col, parent_table, parent_col]
    'associates'  => ['user_id',  'users', 'id'],
    'employees'   => ['user_id',  'users', 'id'],
    'users'       => ['referred_by', 'users', 'id'],
    'mlm_profiles' => ['user_id', 'users', 'id'],
    'mlm_profiles_sponsor' => ['sponsor_user_id', 'users', 'id'],  // same table, different column
    'mlm_commission_ledger_beneficiary' => ['beneficiary_user_id', 'users', 'id'],
    'mlm_commission_ledger_source' => ['source_user_id', 'users', 'id'],
    'colonies'    => ['district_id', 'districts', 'id'],
    'bookings_plot' => ['plot_id', 'plots', 'id'],
    'bookings_user' => ['user_id', 'users', 'id'],
    'plots'       => ['colony_id', 'colonies', 'id'],
    'leads_assigned' => ['assigned_to', 'users', 'id'],
    'leads_converted' => ['converted_by', 'users', 'id'],
    'property_images' => ['property_id', 'user_properties', 'id'],
    'inquiries'   => ['user_id', 'users', 'id'],
    'service_interests' => ['lead_id', 'leads', 'id'],
    'payments'    => ['user_id', 'users', 'id'],
    'payouts'     => ['user_id', 'users', 'id'],
    'commissions' => ['user_id', 'users', 'id'],
    'support_tickets' => ['user_id', 'users', 'id'],
];

// Map real child table name for the aliased entries above
$tableMap = [
    'associates'  => 'associates',
    'employees'   => 'employees',
    'users'       => 'users',
    'mlm_profiles' => 'mlm_profiles',
    'mlm_profiles_sponsor' => 'mlm_profiles',
    'mlm_commission_ledger_beneficiary' => 'mlm_commission_ledger',
    'mlm_commission_ledger_source' => 'mlm_commission_ledger',
    'colonies'    => 'colonies',
    'bookings_plot' => 'bookings',
    'bookings_user' => 'bookings',
    'plots'       => 'plots',
    'leads_assigned' => 'leads',
    'leads_converted' => 'leads',
    'property_images' => 'property_images',
    'inquiries'   => 'inquiries',
    'service_interests' => 'service_interests',
    'payments'    => 'payments',
    'payouts'     => 'payouts',
    'commissions' => 'commissions',
    'support_tickets' => 'support_tickets',
];

// Convenience function to check table existence
function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return $stmt->rowCount() > 0;
}

// Check column existence and return its type
function getColumnInfo(PDO $pdo, string $table, string $column): ?array {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->fetch() ?: null;
}

// Check if constraint already exists
function constraintExists(PDO $pdo, string $table, string $column, string $referencedTable): bool {
    $sql = "SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME = ?
              AND REFERENCED_COLUMN_NAME = 'id'
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$table, $column, $referencedTable]);
    return $stmt->rowCount() > 0;
}

// Count orphaned records
function countOrphans(PDO $pdo, string $childTable, string $childCol, string $parentTable, string $parentCol = 'id'): int {
    $sql = "SELECT COUNT(*) AS cnt FROM `$childTable` c
            LEFT JOIN `$parentTable` p ON c.`$childCol` = p.`$parentCol`
            WHERE c.`$childCol` IS NOT NULL AND p.`$parentCol` IS NULL";
    $stmt = $pdo->query($sql);
    return (int)$stmt->fetch()['cnt'];
}

// Generate constraint name
function constraintName(string $childTable, string $childCol): string {
    return "fk_{$childTable}_{$childCol}";
}

// ─── Process each constraint ──────────────────────────────────────
$success = 0;
$skipped = 0;
$failed  = 0;

foreach ($constraints as $alias => [$childCol, $parentTable, $parentCol]) {
    $childTable = $tableMap[$alias];
    $fkName = constraintName($childTable, $childCol);
    $label  = "`$childTable`.`$childCol` -> `$parentTable`.`$parentCol`";

    echo "─── $label ───\n";

    try {
        // 1. Check tables exist
        if (!tableExists($pdo, $childTable)) {
            echo "  SKIP: child table `$childTable` does not exist\n";
            $skipped++;
            continue;
        }
        if (!tableExists($pdo, $parentTable)) {
            echo "  SKIP: parent table `$parentTable` does not exist\n";
            $skipped++;
            continue;
        }
        echo "  [OK] Tables exist\n";

        // 2. Check columns exist
        $childColInfo = getColumnInfo($pdo, $childTable, $childCol);
        if (!$childColInfo) {
            echo "  SKIP: column `$childCol` does not exist in `$childTable`\n";
            $skipped++;
            continue;
        }
        $parentColInfo = getColumnInfo($pdo, $parentTable, $parentCol);
        if (!$parentColInfo) {
            echo "  SKIP: column `$parentCol` does not exist in `$parentTable`\n";
            $skipped++;
            continue;
        }
        echo "  [OK] Columns exist: child={$childColInfo['Type']}, parent={$parentColInfo['Type']}\n";

        // Optional: warn if types don't match
        if (strtolower($childColInfo['Type']) !== strtolower($parentColInfo['Type'])) {
            echo "  WARN: type mismatch child({$childColInfo['Type']}) vs parent({$parentColInfo['Type']})\n";
        }

        // 3. Check constraint doesn't already exist
        if (constraintExists($pdo, $childTable, $childCol, $parentTable)) {
            echo "  SKIP: constraint already exists\n";
            $skipped++;
            continue;
        }
        echo "  [OK] No existing constraint\n";

        // 4. Check for orphaned records
        $orphans = countOrphans($pdo, $childTable, $childCol, $parentTable, $parentCol);
        if ($orphans > 0) {
            echo "  SKIP: $orphans orphaned record(s) found in `$childTable`.`$childCol` " .
                 "with no matching `$parentTable`.`$parentCol`\n";
            $skipped++;
            continue;
        }
        echo "  [OK] No orphaned records\n";

        // 5. Determine ON DELETE action based on child column nullability
        $onDelete = ($childColInfo['Null'] === 'NO') ? 'RESTRICT' : 'SET NULL';
        // Self-referencing FK on users.referred_by: always SET NULL
        if ($childCol === 'referred_by' && $childTable === 'users') {
            $onDelete = 'SET NULL';
        }
        $sql = "ALTER TABLE `$childTable`
                ADD CONSTRAINT `$fkName`
                FOREIGN KEY (`$childCol`) REFERENCES `$parentTable`(`$parentCol`)
                ON DELETE $onDelete ON UPDATE CASCADE";
        $pdo->exec($sql);
        echo "  [ADDED] Constraint `$fkName` added successfully\n";
        $success++;

    } catch (Exception $e) {
        echo "  FAIL: " . $e->getMessage() . "\n";
        $failed++;
    }
    echo "\n";
}

// ─── Summary ──────────────────────────────────────────────────────
echo "=== Summary ===\n";
echo "  Added:   $success\n";
echo "  Skipped: $skipped\n";
echo "  Failed:  $failed\n";
echo "  Total:   " . count($constraints) . "\n";
echo "=== Done ===\n";
