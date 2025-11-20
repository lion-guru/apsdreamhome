<?php
// Deep database analysis script for apsdreamhome
// Usage (CLI):
//   php database/tools/analyze_database.php [--dump="path/to/dump.sql"]
//
// Produces a human-readable report comparing the live DB (information_schema)
// against the provided SQL dump (default: database/apsdreamhome (5).sql).

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';
require_once $projectRoot . '/includes/config/constants.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection \$con is not available (mysqli).\n");
    exit(1);
}

$dbName = DB_NAME ?? null;
if (!$dbName) {
    fwrite(STDERR, "[ERROR] DB_NAME not defined in constants.php.\n");
    exit(1);
}

// Args
$argv = $_SERVER['argv'] ?? [];
$dumpPath = dirname(__DIR__) . '/apsdreamhome (5).sql'; // default relative to /database
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--dump=')) {
        $dumpPath = trim(substr($arg, 7), '"\'');
    }
}

function fetchAllAssoc(mysqli $con, string $sql, string $dbName, ?string $key = null): array {
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $dbName);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) {
        if ($key !== null) {
            $out[$row[$key]] = $row;
        } else {
            $out[] = $row;
        }
    }
    $stmt->close();
    return $out;
}

// Live DB metadata
$tables = fetchAllAssoc($con, 'SELECT TABLE_NAME, ENGINE, TABLE_ROWS, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?', $dbName);
$pkTables = fetchAllAssoc($con, "SELECT TABLE_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE='PRIMARY KEY' AND TABLE_SCHEMA = ?", $dbName);
$pkSet = array_fill_keys(array_map(fn($r) => $r['TABLE_NAME'], $pkTables), true);

$indexes = fetchAllAssoc($con, 'SELECT TABLE_NAME, COUNT(1) AS idx_count FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? GROUP BY TABLE_NAME', $dbName, 'TABLE_NAME');
$columns = fetchAllAssoc($con, 'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME, ORDINAL_POSITION', $dbName);
$fk = fetchAllAssoc($con, 'SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ?', $dbName);

// Derived sets
$tableNames = array_map(fn($t) => $t['TABLE_NAME'], $tables);
$tablesWithoutPK = array_values(array_diff($tableNames, array_keys($pkSet)));
$tablesWithoutIndexes = [];
foreach ($tableNames as $tn) {
    $count = (int)($indexes[$tn]['idx_count'] ?? 0);
    if ($count === 0) $tablesWithoutIndexes[] = $tn;
}
$nonInnoDB = array_values(array_map(fn($t) => $t['TABLE_NAME'], array_filter($tables, fn($t) => strtoupper((string)$t['ENGINE']) !== 'INNODB')));

// Column heuristics
$textColumns = [];
$longVarchars = [];
foreach ($columns as $col) {
    $type = strtolower((string)$col['COLUMN_TYPE']);
    if (str_contains($type, 'text')) {
        $textColumns[] = $col;
    }
    if (preg_match('/varchar\((\d+)\)/i', $type, $m)) {
        $len = (int)$m[1];
        if ($len > 255) $longVarchars[] = $col;
    }
}

// Parse SQL dump (basic)
$dumpExists = is_file($dumpPath);
$dumpTables = [];
$dumpColumns = [];
if ($dumpExists) {
    $fh = fopen($dumpPath, 'r');
    if ($fh === false) {
        fwrite(STDERR, "[WARN] Unable to open dump file: $dumpPath\n");
    } else {
        $currentTable = null;
        while (($line = fgets($fh)) !== false) {
            $lineTrim = trim($line);
            if ($currentTable === null) {
                if (preg_match('/^CREATE\s+TABLE\s+`([^`]+)`/i', $lineTrim, $m)) {
                    $currentTable = $m[1];
                    $dumpTables[$currentTable] = true;
                    $dumpColumns[$currentTable] = [];
                }
            } else {
                if (preg_match('/^\)\s*ENGINE=/i', $lineTrim)) {
                    $currentTable = null;
                    continue;
                }
                // Column lines start with `name`
                if (preg_match('/^`([^`]+)`\s+(.+?)(,)?$/', $lineTrim, $m)) {
                    $colName = $m[1];
                    $colType = $m[2];
                    $dumpColumns[$currentTable][$colName] = $colType;
                }
            }
        }
        fclose($fh);
    }
}

// Compare dump vs live
$dumpTableNames = array_keys($dumpTables);
$missingInLive = array_values(array_diff($dumpTableNames, $tableNames));
$extraInLive = array_values(array_diff($tableNames, $dumpTableNames));

$columnDiffs = [];
foreach ($dumpColumns as $tName => $cols) {
    if (!in_array($tName, $tableNames, true)) continue;
    $liveCols = [];
    foreach ($columns as $c) {
        if ($c['TABLE_NAME'] === $tName) {
            $liveCols[$c['COLUMN_NAME']] = strtolower((string)$c['COLUMN_TYPE']);
        }
    }
    $missingCols = array_values(array_diff(array_keys($cols), array_keys($liveCols)));
    $extraCols = array_values(array_diff(array_keys($liveCols), array_keys($cols)));
    $typeMismatches = [];
    foreach ($cols as $cn => $dt) {
        if (!isset($liveCols[$cn])) continue;
        $dumpType = strtolower((string)$dt);
        $liveType = $liveCols[$cn];
        if ($dumpType !== $liveType) {
            $typeMismatches[] = [
                'column' => $cn,
                'dumpType' => $dumpType,
                'liveType' => $liveType,
            ];
        }
    }
    if ($missingCols || $extraCols || $typeMismatches) {
        $columnDiffs[$tName] = [
            'missing' => $missingCols,
            'extra' => $extraCols,
            'type_mismatches' => $typeMismatches,
        ];
    }
}

// Build report
$report = [];
$report[] = "# Database Analysis Report";
$report[] = "Generated: " . date('Y-m-d H:i:s');
$report[] = "Database: `{$dbName}`";
$report[] = "Dump File: " . ($dumpExists ? $dumpPath : '(not found)');
$report[] = "";

$report[] = "## Overview";
$report[] = "- Tables: " . count($tables);
$report[] = "- With Primary Key: " . (count($tableNames) - count($tablesWithoutPK));
$report[] = "- Without Primary Key: " . count($tablesWithoutPK);
$report[] = "- Non-InnoDB Tables: " . count($nonInnoDB);
$report[] = "- Tables Without Any Index: " . count($tablesWithoutIndexes);
$report[] = "- Foreign Key Constraints: " . count($fk);
$report[] = "";

if ($tablesWithoutPK) {
    $report[] = "## Tables Without Primary Key";
    foreach ($tablesWithoutPK as $t) { $report[] = "- `{$t}`"; }
    $report[] = "";
}

if ($nonInnoDB) {
    $report[] = "## Non-InnoDB Tables";
    foreach ($nonInnoDB as $t) { $report[] = "- `{$t}`"; }
    $report[] = "";
}

if ($tablesWithoutIndexes) {
    $report[] = "## Tables Without Any Index";
    foreach ($tablesWithoutIndexes as $t) { $report[] = "- `{$t}`"; }
    $report[] = "";
}

if ($textColumns) {
    $report[] = "## Text Columns (potentially heavy)";
    foreach ($textColumns as $c) {
        $report[] = "- `{$c['TABLE_NAME']}`.`{$c['COLUMN_NAME']}` {$c['COLUMN_TYPE']}";
    }
    $report[] = "";
}

if ($longVarchars) {
    $report[] = "## VARCHAR >255 Columns";
    foreach ($longVarchars as $c) {
        $report[] = "- `{$c['TABLE_NAME']}`.`{$c['COLUMN_NAME']}` {$c['COLUMN_TYPE']}";
    }
    $report[] = "";
}

if ($dumpExists) {
    $report[] = "## Dump vs Live Differences";
    if ($missingInLive) {
        $report[] = "- Tables missing in live: " . implode(', ', array_map(fn($t) => "`$t`", $missingInLive));
    }
    if ($extraInLive) {
        $report[] = "- Extra tables in live: " . implode(', ', array_map(fn($t) => "`$t`", $extraInLive));
    }
    foreach ($columnDiffs as $t => $diff) {
        $report[] = "";
        $report[] = "### Table `{$t}`";
        if (!empty($diff['missing'])) {
            $report[] = "- Missing columns (from dump): " . implode(', ', array_map(fn($c) => "`$c`", $diff['missing']));
        }
        if (!empty($diff['extra'])) {
            $report[] = "- Extra columns (only live): " . implode(', ', array_map(fn($c) => "`$c`", $diff['extra']));
        }
        if (!empty($diff['type_mismatches'])) {
            $report[] = "- Type mismatches:";
            foreach ($diff['type_mismatches'] as $tm) {
                $report[] = "  - `{$tm['column']}` dump={$tm['dumpType']} live={$tm['liveType']}";
            }
        }
    }
    $report[] = "";
}

// Save report file
$outPath = __DIR__ . '/db_analysis_report.md';
file_put_contents($outPath, implode("\n", $report));

echo "Analysis complete. Report written to: {$outPath}\n";

