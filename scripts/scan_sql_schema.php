<?php
/**
 * Static SQL-vs-live-schema auditor.
 *
 * Walks PHP files under app/, extracts literal SQL table references
 * (INSERT INTO / UPDATE / FROM / JOIN) and INSERT column lists, then
 * validates them against the live MySQL schema.
 *
 * Flags:
 *  - Queries referencing tables that do not exist ("ghost tables")
 *  - INSERT statements whose literal column lists contain columns
 *    missing from the live table ("unknown column" fatals)
 *
 * Skips dynamic identifiers ({$var}, concatenation, ? placeholders).
 * Run before commits touching SQL: php scripts/scan_sql_schema.php
 */

error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, 'DB connect failed: ' . $e->getMessage() . "\n");
    exit(1);
}

/** @var array<string,true> $tables */
$tables = [];
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) {
    $tables[strtolower($t)] = true;
}

/** @var array<string,array<string,true>> $columns cache keyed by lowercase table */
$colsCache = [];

function getColumns(PDO $pdo, string $table, array &$cache): ?array
{
    $key = strtolower($table);
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`');
        $stmt->execute();
        $cols = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cols[strtolower($row['Field'])] = true;
        }
        return $cache[$key] = $cols;
    } catch (PDOException $e) {
        return $cache[$key] = null;
    }
}

// Collect PHP files (app tree = controllers/services/models/helpers/core/views)
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/app', FilesystemIterator::SKIP_DOTS)
);

$reTable = '/(?:\bFROM\b|\bJOIN\b|\bUPDATE\b|\bINSERT\s+INTO\b|\bDELETE\s+FROM\b)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i';
$reInsert = '/\bINSERT\s+INTO\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?\s*\(([^()]*)\)/is';
$reUpdateSet = '/\bUPDATE\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?\s+(?:[a-zA-Z_][a-zA-Z0-9_]*\s+)?SET\s+/i';
$reSetCol = '/(?:\bSET\b|,)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?\s*=/i';
$reBareIdent = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

$fileCount = 0;
$missingTables = [];   // unique "table" => first ref
$badColumns = [];      // list of detail lines
$tableRefs = 0;

foreach ($files as $f) {
    if ($f->getExtension() !== 'php') continue;
    $src = file_get_contents($f->getPathname());
    if ($src === false || stripos($src, 'select') === false && stripos($src, 'insert') === false && stripos($src, 'update') === false) {
        continue;
    }
    $fileCount++;
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));

    // ---- table existence ----
    if (preg_match_all($reTable, $src, $m)) {
        foreach (array_unique(array_map('strtolower', $m[1])) as $t) {
            // skip common false positives (SQL aliases used as pseudo-tables)
            if (in_array($t, ['dual'], true)) continue;
            $tableRefs++;
            if (!isset($tables[$t])) {
                $missingTables[$t] ??= $rel;
            }
        }
    }

    // ---- INSERT column lists ----
    if (preg_match_all($reInsert, $src, $im, PREG_SET_ORDER)) {
        foreach ($im as $hit) {
            $table = strtolower($hit[1]);
            if (!isset($tables[$table])) continue; // already flagged as ghost
            $cols = getColumns($pdo, $table, $colsCache);
            if ($cols === null) continue;
            $rawCols = array_map('trim', explode(',', $hit[2]));
            foreach ($rawCols as $c) {
                $c = trim($c, " \t\n\r`'");
                if ($c === '' || !preg_match($reBareIdent, $c)) continue; // expression/placeholder
                if (!isset($cols[strtolower($c)])) {
                    // locate line number of this INSERT statement
                    $pos = strpos($src, (string)$hit[0]);
                    $line = ($pos !== false) ? substr_count(substr($src, 0, $pos), "\n") + 1 : 0;
                    $badColumns[] = sprintf('%s:%d -> %s.%s', $rel, $line, $table, $c);
                }
            }
        }
    }
    // ---- UPDATE SET columns ----
    if (preg_match_all($reUpdateSet, $src, $um, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        foreach ($um as $hit) {
            $table = strtolower($hit[1][0]);
            if (!isset($tables[$table])) continue; // ghost tables reported separately
            $ucols = getColumns($pdo, $table, $colsCache);
            if ($ucols === null) continue;
            $setStart = $hit[0][1] + strlen($hit[0][0]);
            $rest = substr($src, $setStart, 4000);
            if (preg_match('/\bWHERE\b|\bON\s+DUPLICATE\b|\bLIMIT\b/i', $rest, $wm, PREG_OFFSET_CAPTURE)) {
                $rest = substr($rest, 0, $wm[0][1]);
            }
            $uline = substr_count(substr($src, 0, $hit[0][1]), "\n") + 1;
            if (preg_match_all($reSetCol, ', ' . $rest, $sm)) {
                foreach ($sm[1] as $c) {
                    if (!isset($ucols[strtolower($c)])) {
                        $badColumns[] = sprintf('%s:%d -> %s.%s (UPDATE)', $rel, $uline, $table, $c);
                    }
                }
            }
        }
    }
}

echo "FILES SCANNED: {$fileCount}\n";
echo "TABLE REFS CHECKED: {$tableRefs}\n";

echo "\nGHOST TABLES: " . count($missingTables) . "\n";
$i = 0;
foreach ($missingTables as $t => $firstRef) {
    echo "  - {$t} (first seen: {$firstRef})\n";
    if (++$i >= 50) { echo "  ... truncated\n"; break; }
}

// dedupe bad-column findings
$badColumns = array_values(array_unique($badColumns));
echo "\nUNKNOWN INSERT COLUMNS: " . count($badColumns) . "\n";
foreach (array_slice($badColumns, 0, 800) as $b) echo "  - {$b}\n";
if (count($badColumns) > 800) echo "  ... truncated\n";
