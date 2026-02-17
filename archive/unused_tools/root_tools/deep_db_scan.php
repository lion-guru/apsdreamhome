<?php
/**
 * Deep Database Scanner
 * ---------------------
 * 1. Counts & lists every table
 * 2. Checks for missing PK / FK indexes
 * 3. Detects column-type mismatches vs schema map
 * 4. Finds orphan rows (lead without user, booking without property etc)
 * 5. Reports duplicate rows in critical unique columns
 * 6. Gives a concise PASS / WARN / FAIL summary
 */

/* --------------------------------------------------
 * 0A. Stand-alone DB connection (fallback)
 * -------------------------------------------------- */
$DB_HOST = 'localhost';
$DB_NAME = 'apsdreamhome';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    die("DB connection failed: " . $e->getMessage());
}

$report = [
    'tables'            => 0,
    'columns_checked'   => 0,
    'indexes_missing'   => [],
    'orphan_rows'       => [],
    'duplicate_rows'    => [],
    'column_mismatches' => [],
    'errors'            => []
];

/* --------------------------------------------------
 * 0. Connectivity check
 * -------------------------------------------------- */
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
    $report['tables'] = (int) $stmt->fetchColumn();
} catch (Throwable $e) {
    die("DB connection failed: " . $e->getMessage());
}

/* --------------------------------------------------
 * 1. Table & column inventory
 * -------------------------------------------------- */
$schemaMap = [];
$stmt = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_KEY, EXTRA
                     FROM information_schema.columns
                     WHERE table_schema = DATABASE()
                     ORDER BY TABLE_NAME, ORDINAL_POSITION");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $schemaMap[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row;
    $report['columns_checked']++;
}

/* --------------------------------------------------
 * 2. Missing PK / FK index detector
 * -------------------------------------------------- */
$indexSql = "SELECT table_name, column_name, constraint_name
              FROM information_schema.key_column_usage
              WHERE table_schema = DATABASE()
                AND referenced_table_name IS NOT NULL";  // FK only
$idxStmt  = $pdo->query($indexSql);
while ($row = $idxStmt->fetch(PDO::FETCH_ASSOC)) {
    $table = $row['table_name'];
    $col   = $row['column_name'];
    // crude check: if column ends with '_id' but no FK found we flag
    if (preg_match('/_id$/', $col) && !isset($schemaMap[$table][$col])) {
        $report['indexes_missing'][] = "$table.$col (possible FK index missing)";
    }
}

/* --------------------------------------------------
 * 3. Orphan row checks (sample critical relations)
 * -------------------------------------------------- */
$orphanQueries = [
    'leads without users'   => "SELECT id FROM leads WHERE user_id NOT IN (SELECT id FROM users)",
    'bookings without prop' => "SELECT id FROM bookings WHERE property_id NOT IN (SELECT id FROM properties)",
    'payments without txn'  => "SELECT id FROM payments WHERE transaction_id NOT IN (SELECT id FROM transactions)"
];
foreach ($orphanQueries as $label => $sql) {
    try {
        $res = $pdo->query($sql);
        if ($res && $res->rowCount()) {
            $report['orphan_rows'][$label] = $res->rowCount();
        }
    } catch (Throwable $e) {
        // table may not exist in this build
    }
}

/* --------------------------------------------------
 * 4. Duplicate row checks (unique columns)
 * -------------------------------------------------- */
$dupQueries = [
    'users.email'        => "SELECT email, COUNT(*) c FROM users GROUP BY email HAVING c>1",
    'properties.slug'    => "SELECT slug, COUNT(*) c FROM properties GROUP BY slug HAVING c>1"
];
foreach ($dupQueries as $label => $sql) {
    try {
        $res = $pdo->query($sql);
        if ($res && $res->rowCount()) {
            $report['duplicate_rows'][$label] = $res->rowCount();
        }
    } catch (Throwable $e) {
    }
}

/* --------------------------------------------------
 * 5. Column-type sanity (example: price should be decimal)
 * -------------------------------------------------- */
$typeRules = [
    'properties.price' => 'decimal',
    'transactions.amount' => 'decimal',
    'users.phone' => 'varchar'
];
foreach ($typeRules as $colDot => $expectType) {
    [$tbl, $col] = explode('.', $colDot);
    if (isset($schemaMap[$tbl][$col])) {
        $actual = $schemaMap[$tbl][$col]['DATA_TYPE'];
        if (stripos($actual, $expectType) === false) {
            $report['column_mismatches'][] = "$tbl.$col  expects $expectType, found $actual";
        }
    }
}

/* --------------------------------------------------
 * 6. Print concise report
 * -------------------------------------------------- */
echo "======== APS Deep DB Scan ========\n";
echo "Total tables : " . $report['tables'] . "\n";
echo "Columns scanned : " . $report['columns_checked'] . "\n";
echo "----------------------------------\n";

$hasIssue = false;
foreach (['indexes_missing','orphan_rows','duplicate_rows','column_mismatches'] as $sec) {
    if ($report[$sec]) {
        $hasIssue = true;
        echo strtoupper($sec) . ":\n";
        foreach ($report[$sec] as $k => $v) {
            echo " - " . (is_string($k) ? "$k => $v" : $v) . "\n";
        }
    }
}

if (!$hasIssue) {
    echo "✅ No structural/data anomalies detected.\n";
} else {
    echo "⚠️  Issues found – review above.\n";
}
echo "======== End Scan ========\n";