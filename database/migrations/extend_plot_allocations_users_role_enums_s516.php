<?php
/**
 * Widen three enums so live code can write what it writes (Session 516).
 * Idempotent: each ALTER runs ONLY when the value is absent.
 * Members appended at END => existing indices preserved. Safe to re-run.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

function columnMeta(\PDO $pdo, string $table, string $column): ?array {
    $row = $pdo->query(
        "SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS
           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'"
    )->fetch(\PDO::FETCH_ASSOC);
    return $row ?: null;
}

function widen(\PDO $pdo, string $table, string $column, string $newMember): void {
    $row = columnMeta($pdo, $table, $column);
    if (!$row) { echo "SKIP $table.$column (column absent)\n"; return; }
    if (strpos($row['COLUMN_TYPE'], "'" . $newMember . "'") !== false) {
        echo "SKIP $table.$column ('$newMember' already present)\n";
        return;
    }
    if (!preg_match('/^enum\((.*)\)$/i', $row['COLUMN_TYPE'], $m)) {
        echo "SKIP $table.$column (not enum)\n";
        return;
    }
    $members = array_map('trim', explode(',', $m[1]));
    $members[] = "'" . $newMember . "'";
    $null    = strtolower($row['IS_NULLABLE']) === 'yes' ? ' NULL' : ' NOT NULL';
    $default = '';
    $d = $row['COLUMN_DEFAULT'];
    if ($d !== null && $d !== 'NULL') {
        // MariaDB returns the DEFAULT expression with its quotes included —
        // use it verbatim, do not re-wrap or it doubles into a syntax error.
        $default = " DEFAULT " . $d;
    } elseif (strtolower($row['IS_NULLABLE']) === 'yes') {
        $default = " DEFAULT NULL";
    }
    $sql = "ALTER TABLE `$table` MODIFY `$column` ENUM(" . implode(',', $members) . "){$null}{$default}";
    $pdo->exec($sql);
    echo "FIXED $table.$column ('$newMember' appended)\n";
}

widen($pdo, 'plot_allocations', 'status', 'rejected');
widen($pdo, 'users', 'role', 'farmer');
widen($pdo, 'employee_asset_assignments', 'condition_returned', 'transferred');

foreach ([['plot_allocations','status'], ['users','role'], ['employee_asset_assignments','condition_returned']] as [$t, $c]) {
    $type = columnMeta($pdo, $t, $c)['COLUMN_TYPE'] ?? 'n/a';
    echo "VERIFY {$t}.{$c}: {$type}\n";
}
echo "Done.\n";
