<?php
/**
 * Audit Hot Indexes — finds missing indexes on tables with >1000 rows
 *
 * Usage: php scripts/audit_hot_indexes.php
 * Safe: READ-ONLY — never runs CREATE INDEX, only reports.
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3307);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apsdreamhome');
define('MIN_ROWS', 1000);

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== APS Dream Home — Hot Index Audit ===\n";
echo "Threshold: tables with more than " . number_format(MIN_ROWS) . " rows\n\n";

// ── Step 1: find hot tables ──────────────────────────────────────────────────
$hotStmt = $pdo->query("
    SELECT TABLE_NAME, TABLE_ROWS
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '" . DB_NAME . "'
      AND TABLE_ROWS > " . MIN_ROWS . "
    ORDER BY TABLE_ROWS DESC
");
$hotTables = $hotStmt->fetchAll(PDO::FETCH_ASSOC);

echo count($hotTables) . " table(s) exceed " . number_format(MIN_ROWS) . " rows:\n\n";

foreach ($hotTables as $t) {
    echo "  {$t['TABLE_NAME']} — " . number_format($t['TABLE_ROWS']) . " rows\n";
}
echo "\n";

// ── Step 2: expected indexes per table ───────────────────────────────────────
$expectedIndexes = [
    'plot_bookings' => [
        ['cols' => ['user_id'],         'name' => 'idx_plot_bookings_user_id'],
        ['cols' => ['plot_id'],         'name' => 'idx_plot_bookings_plot_id'],
        ['cols' => ['status'],          'name' => 'idx_plot_bookings_status'],
        ['cols' => ['booking_date'],    'name' => 'idx_plot_bookings_booking_date'],
    ],
    'booking_payment_schedules' => [
        ['cols' => ['booking_id'],      'name' => 'idx_bps_booking_id'],
        ['cols' => ['status'],          'name' => 'idx_bps_status'],
        ['cols' => ['due_date'],        'name' => 'idx_bps_due_date'],
    ],
    'leads' => [
        ['cols' => ['status'],          'name' => 'idx_leads_status'],
        ['cols' => ['assigned_to'],     'name' => 'idx_leads_assigned_to'],
        ['cols' => ['lead_source'],     'name' => 'idx_leads_lead_source'],
    ],
    'notifications' => [
        ['cols' => ['user_id', 'is_read'], 'name' => 'idx_notif_user_read'],
    ],
    'user_properties' => [
        ['cols' => ['user_id'],         'name' => 'idx_up_user_id'],
        ['cols' => ['status'],          'name' => 'idx_up_status'],
    ],
    'admin_menu_items' => [
        ['cols' => ['section', 'order_index'], 'name' => 'idx_ami_section_order'],
    ],
    'site_content' => [
        ['cols' => ['section', 'content_key'], 'name' => 'idx_sc_section_key'],
    ],
    'daily_operations_log' => [
        ['cols' => ['operation_date'],  'name' => 'idx_dol_operation_date'],
    ],
    'employee_attendance' => [
        ['cols' => ['employee_id', 'attendance_date'], 'name' => 'idx_ea_emp_date'],
    ],
    'lead_pipeline' => [
        ['cols' => ['stage'],           'name' => 'idx_lp_stage'],
        ['cols' => ['assigned_to'],     'name' => 'idx_lp_assigned_to'],
    ],
];

// ── Step 3: for each hot table, fetch existing indexes ───────────────────────
$missingByTable = [];

foreach ($hotTables as $t) {
    $table = $t['TABLE_NAME'];
    if (!isset($expectedIndexes[$table])) {
        continue;
    }

    $idxStmt = $pdo->prepare("
        SELECT COLUMN_NAME, INDEX_NAME, NON_UNIQUE
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ORDER BY INDEX_NAME, SEQ_IN_INDEX
    ");
    $idxStmt->execute([DB_NAME, $table]);
    $rows = $idxStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build a set of "column lists" that already have an index
    // For each INDEX_NAME, collect the ordered columns
    $existingIndexCols = [];
    foreach ($rows as $r) {
        $name = $r['INDEX_NAME'];
        if ($name === 'PRIMARY') continue;
        if (!isset($existingIndexCols[$name])) {
            $existingIndexCols[$name] = [];
        }
        $existingIndexCols[$name][] = $r['COLUMN_NAME'];
    }

    // Flatten: build a set of every column combination that is already covered
    // We check: is there any existing index whose leading columns match?
    $covered = [];
    foreach ($existingIndexCols as $idxName => $cols) {
        $covered[implode(',', $cols)] = true;
    }

    $tableMissing = [];
    foreach ($expectedIndexes[$table] as $exp) {
        $key = implode(',', $exp['cols']);

        // Check if fully covered by an existing index
        $found = false;
        foreach ($existingIndexCols as $idxCols) {
            if ($idxCols === $exp['cols']) {
                $found = true;
                break;
            }
            // Also accept a multi-column index where the leading columns match
            if (array_slice($idxCols, 0, count($exp['cols'])) === $exp['cols']) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $tableMissing[] = $exp;
        }
    }

    if (!empty($tableMissing)) {
        $missingByTable[$table] = $tableMissing;
    }
}

// ── Step 4: report ───────────────────────────────────────────────────────────
$allMissing = [];

foreach ($missingByTable as $table => $missing) {
    echo "─── {$table} ───\n";
    echo "  Existing indexes:\n";

    $idxStmt = $pdo->prepare("
        SELECT INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols, NON_UNIQUE
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME != 'PRIMARY'
        GROUP BY INDEX_NAME, NON_UNIQUE
        ORDER BY INDEX_NAME
    ");
    $idxStmt->execute([DB_NAME, $table]);
    $existing = $idxStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($existing)) {
        echo "    (none)\n";
    } else {
        foreach ($existing as $e) {
            $type = $e['NON_UNIQUE'] ? 'INDEX' : 'UNIQUE';
            echo "    {$e['INDEX_NAME']} → ({$e['cols']}) [{$type}]\n";
        }
    }

    echo "  Missing indexes:\n";
    foreach ($missing as $m) {
        $colList = implode('_', $m['cols']);
        $sql = "CREATE INDEX {$m['name']} ON `{$table}` (`" . implode('`, `', $m['cols']) . "`);";
        echo "    ✗ {$m['name']} on ({$colList})\n";
        echo "      SQL: {$sql}\n";
        $allMissing[] = ['table' => $table, 'index' => $m['name'], 'sql' => $sql];
    }
    echo "\n";
}

// ── Step 5: summary ──────────────────────────────────────────────────────────
echo "════════════════════════════════════════════════════════════════════\n";
echo "SUMMARY\n";
echo "════════════════════════════════════════════════════════════════════\n";
echo "Hot tables checked:     " . count($hotTables) . "\n";
echo "Tables with expected:   " . count($expectedIndexes) . "\n";
echo "Missing indexes found:  " . count($allMissing) . "\n";

if (!empty($allMissing)) {
    echo "\n-- FULL SQL TO CREATE ALL MISSING INDEXES --\n";
    echo "-- WARNING: Do NOT run blindly. Review first. This is READ-ONLY report.\n\n";
    foreach ($allMissing as $m) {
        echo $m['sql'] . "\n";
    }
} else {
    echo "\nAll expected indexes are present. Nothing to create.\n";
}

echo "\nDone. " . date('Y-m-d H:i:s') . "\n";
