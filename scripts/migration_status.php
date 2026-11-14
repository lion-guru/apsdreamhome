<?php
/**
 * Migration Status — read-only evidence scanner.
 *
 * For every file in database/migrations/*.php, parse the DDL it contains
 * (CREATE TABLE / ALTER TABLE ... ADD `col`) and check whether the objects
 * the migration targets actually exist in the live database.
 *
 * Produces a per-file table of:
 *    creates   - tables the migration creates; whether each exists live
 *    alters    - tables the migration alters; whether each column exists live
 *
 * This does NOT prove a migration ran; it proves whether its targets exist
 * right now. A file whose targets all exist "cannot have failed loudly" —
 * absence of every target "cannot have succeeded". Files with zero parseable
 * DDL are reported as SKIP (not failures).
 *
 * Usage:  php scripts/migration_status.php [--json]
 * Exit code: 0 always (informational); CI gate should use the schema scanner instead.
 * Network/DB safe: read-only SELECTs against information_schema.
 */

$json = in_array('--json', $argv ?? [], true);
$root = dirname(__DIR__);
require_once $root . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// object existence maps (one query per object class is cheaper than per-check)
$existingTables = [];
foreach ($pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $existingTables[strtolower($t)] = true;
}
$existingColumns = [];
foreach ($pdo->query("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = DATABASE()")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $existingColumns[strtolower($r['table_name'])][strtolower($r['column_name'])] = true;
}

$files = glob($root . '/database/migrations/*.php');
sort($files);

$filesFullyPresent = 0;   // every parseable target object exists
$filesPartlyPresent = 0;  // some exist, some don't
$filesNothingPresent = 0; // none exist
$filesUnparseable = 0;

$report = ['generated_at' => date('c'), 'files' => []];
$rows = [];

foreach ($files as $file) {
    $name = basename($file);
    $sql = file_get_contents($file);

    // Token stream in order: CREATE TABLE / ALTER TABLE / ADD `col`.
    preg_match_all(
        '/CREATE\s+TABLE(?:\s+IF(?:\s+NOT)?\s+EXISTS)?\s+`?(?!IF\b|NOT\b|EXISTS\b|AND\b|WITH\b|FUNCTION\b|THEN\b)([A-Za-z_][A-Za-z0-9_]*)`?'
        . '|ALTER\s+TABLE\s+(?:`?([A-Za-z_][A-Za-z0-9_]*)`?)(?:\s+RENAME\s+(?:TO|AS)\s+`?([A-Za-z_][A-Za-z0-9_]*)`?)?'
        . '|\bADD\s+(?:CONSTRAINT|UNIQUE\s+(?:KEY|INDEX)|UNIQUE|PRIMARY\s+KEY|FOREIGN\s+KEY|KEY|INDEX)\s+`?([A-Za-z_][A-Za-z0-9_]*)`?'
        . '|\bADD\s+(?:COLUMN\s+)?(?:IF\s+NOT\s+EXISTS\s+)?`([A-Za-z_][A-Za-z0-9_]*)`/i',
        $sql, $all, PREG_SET_ORDER
    );

    $createdTables = [];
    $alterTargets = [];   // table => [added columns seen]
    $currentAlter = null;

    foreach ($all as $m) {
        if (($m[1] ?? '') !== '') { // CREATE TABLE
            $t = strtolower($m[1]);
            if (!in_array($t, $createdTables, true)) $createdTables[] = $t;
            $currentAlter = null;
            continue;
        }
        if (($m[2] ?? '') !== '') { // ALTER TABLE [RENAME TO x]
            $currentAlter = strtolower($m[2]);
            continue;
        }
        // group 4 = KEY/INDEX/CONSTRAINT name (ignored on purpose)
        $col = ($m[5] ?? '') !== '' ? strtolower($m[5]) : null;
        if ($currentAlter === null || $col === null) continue;
        $alterTargets[$currentAlter][] = $col;
    }

    $created = [];
    $missingTables = [];
    foreach ($createdTables as $t) {
        if (isset($existingTables[$t])) { $created[] = $t; } else { $missingTables[] = $t; }
    }

    $alterColsChecked = 0;
    $alterColsFound = 0;
    $missingColumns = [];
    foreach ($alterTargets as $table => $cols) {
        foreach (array_unique($cols) as $col) {
            // skip obvious non-column names (only when they're not actual columns)
            if (isset($existingColumns[$table][$col])) {
                $alterColsFound++; $alterColsChecked++;
            } else {
                $alterColsChecked++;
                $missingColumns[] = "$table.$col";
            }
        }
    }

    $totalTargets = count($createdTables) + $alterColsChecked;
    $totalFound = count($created) + $alterColsFound;

    if ($totalTargets === 0) {
        $filesUnparseable++;
        $status = 'SKIP';
    } elseif ($totalFound === $totalTargets) {
        $filesFullyPresent++;
        $status = 'PRESENT';
    } elseif ($totalFound === 0) {
        $filesNothingPresent++;
        $status = 'ABSENT';
    } else {
        $filesPartlyPresent++;
        $status = 'PARTIAL';
    }

    $rows[] = [$status, $name, count($createdTables), count($created), count($missingTables), $alterColsChecked, $alterColsFound, $missingTables, $missingColumns];
    $report['files'][] = [
        'file' => $name,
        'status' => $status,
        'tables_created' => $createdTables,
        'tables_missing' => $missingTables,
        'alter_columns_checked' => $alterColsChecked,
        'alter_columns_found' => $alterColsFound,
        'alter_columns_missing' => $missingColumns,
    ];
}

// summary
$total = count($rows);
$absentList = array_values(array_filter($rows, fn($r) => $r[0] === 'ABSENT' || $r[0] === 'PARTIAL'));
usort($absentList, fn($a, $b) => count($b[7]) + count($b[8]) <=> count($a[7]) + count($a[8]));

echo "== migration status (live-object evidence) ==\n";
echo "files scanned: $total\n";
echo "parseable target evidence: " . ($total - $filesUnparseable) . " files\n";
echo "  PRESENT (all targets exist):  $filesFullyPresent\n";
echo "  PARTIAL (some targets exist): $filesPartlyPresent\n";
echo "  ABSENT  (no target exists):   $filesNothingPresent\n";
echo "  SKIP    (no parseable DDL):   $filesUnparseable\n\n";

echo "== files needing owner attention (PARTIAL / ABSENT) ==\n";
foreach ($absentList as [$status, $name, $tc, $ct, $mt, $ac, $acf, $missT, $missC]) {
    printf("  [%s] %s\n", $status, $name);
    if ($missT) echo "        missing tables : " . implode(', ', $missT) . "\n";
    if ($missC) echo "        missing cols   : " . implode(', ', array_slice($missC, 0, 8)) . (count($missC) > 8 ? " +" . (count($missC) - 8) . " more" : '') . "\n";
}

if ($json) {
    file_put_contents($root . '/backups/migration_status_' . date('Y-m-d_His') . '.json', json_encode($report, JSON_PRETTY_PRINT));
    echo "\nwrote backups/migration_status_" . date('Y-m-d_His') . ".json\n";
}

echo "\nNOTE: existence != proof of application (table may predate the migration with same name).\n";
echo "Files with all-targets-present are not necessarily recorded in schema_migrations (registry is the proof of record).\n";
exit(0);
