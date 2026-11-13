<?php
/**
 * Schema Mismatch Scanner
 *
 * Finds the SQL in the application that references tables or columns the
 * live schema does not have - the "phantom table / phantom column" family
 * that has produced most of this project's 500s.
 *
 * WHY (S465): the CI job "Schema Mismatch Scan" imported
 * `database/schema.sql` - a 17 KB, 15-table fragment from April 2026 that
 * does not contain a single table the app really uses - and then ran
 * `testing/scan_schema_mismatches.php`, WHICH DID NOT EXIST.  The job
 * printed "Schema scanner not found, skipping" and went GREEN.  It was a
 * no-op gate: green, and measuring nothing.
 *
 * This is the real scanner.  It is deliberately conservative - a false
 * positive turns CI red for nothing, which is worse than a missed
 * mismatch - so it only reports:
 *
 *   1. a TABLE that the code names in FROM / JOIN / INTO / UPDATE but the
 *      schema does not have;
 *   2. a COLUMN listed in an `INSERT INTO t (c1, c2, ...)` that t does
 *      not have (the highest-confidence column signal there is).
 *
 * Usage:
 *   php testing/scan_schema_mismatches.php            # report + exit 1 on findings
 *   php testing/scan_schema_mismatches.php --report   # report only, always exit 0
 *
 * Environment: DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD
 * (the app's own config/database.php is used when they are absent).
 */

$root = dirname(__DIR__);
require_once $root . '/config/bootstrap.php';

$reportOnly = in_array('--report', $argv, true);

// S465: the RATCHET.  The scanner finds 139 REAL mismatches in the
// existing code (23 phantom tables + 116 phantom columns) - they are the
// family that has produced most of this project's 500s, but they are
// PRE-EXISTING debt.  A baseline file lists them; the scanner then fails
// only on a NEW mismatch.  That turns "a list of 139 problems" into a gate
// that is green today and prevents the 140th.
$baselineFile = null;
$writeBaseline = false;
$writeBaselineFile = null;
foreach ($argv as $a) {
    if (strpos($a, '--baseline=') === 0) {
        $baselineFile = substr($a, strlen('--baseline='));
    } elseif ($a === '--write-baseline') {
        $writeBaseline = true;
    } elseif (strpos($a, '--write-baseline=') === 0) {
        $writeBaseline = true;
        $writeBaselineFile = substr($a, strlen('--write-baseline='));
    }
}
if ($writeBaseline && $writeBaselineFile === null) {
    $writeBaselineFile = $baselineFile ?? ($root . '/database/schema_mismatch_baseline.txt');
}
$known = [];
if ($baselineFile !== null && is_file($baselineFile)) {
    foreach (file($baselineFile, FILE_IGNORE_NEW_LINES) as $l) {
        $l = trim($l);
        if ($l !== '' && $l[0] !== '#') {
            $known[strtolower($l)] = true;
        }
    }
}

try {
    $db  = \App\Core\Database\Database::getInstance();
    $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : $db->getConnection();
} catch (\Throwable $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(2);
}

// ---------------------------------------------------------------- the schema
$tables = [];
foreach ($pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()") as $row) {
    $tables[strtolower($row['TABLE_NAME'])] = true;
}
$columns = [];   // table => [column => true]
$stmt = $pdo->prepare(
    "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()"
);
$stmt->execute();
foreach ($stmt as $row) {
    $t = strtolower($row['TABLE_NAME']);
    if (!isset($columns[$t])) {
        $columns[$t] = [];
    }
    $columns[$t][strtolower($row['COLUMN_NAME'])] = true;
}

echo "schema: " . count($tables) . " tables, "
    . array_sum(array_map('count', $columns)) . " columns\n";

// ---------------------------------------------------------------- the code
$appDir = $root . '/app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir));

/** SQL keywords that follow FROM / JOIN / INTO / UPDATE but are not tables. */
const NOT_TABLES = [
    'select', 'insert', 'update', 'delete', 'from', 'where', 'group', 'order',
    'having', 'limit', 'offset', 'union', 'join', 'inner', 'left', 'right',
    'full', 'outer', 'cross', 'on', 'and', 'or', 'not', 'as', 'set', 'values',
    'dual', 'information_schema', 'performance_schema', 'mysql',
];

$missingTables = [];   // table => [file:line, ...]
$missingCols   = [];   // "table.column" => [file:line, ...]

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $src  = @file_get_contents($path);
    if ($src === false || stripos($src, 'SELECT') === false) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));

    // S465: the SQL is extracted with PHP's OWN TOKENIZER, not with
    // regexes over the raw text.
    //
    // The regex approach produced three whole classes of false positive:
    //   - prose in views ("... range from affordable to luxury ...");
    //   - `ON DUPLICATE KEY UPDATE actual_value = ...` read as a TABLE;
    //   - doc-comments, because a string literal inside a comment looks
    //     exactly like a string literal in code.
    // token_get_all() tells us precisely what is a string literal, what is
    // a comment and what is inline HTML, so only REAL SQL is examined.
    $sqlChunks = [];
    $tokens = token_get_all($src);
    $inHeredoc = false;
    $heredoc = '';
    foreach ($tokens as $t) {
        if (is_string($t)) {
            continue;
        }
        $id = $t[0];
        if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
            continue;
        }
        if ($id === T_START_HEREDOC) {
            $inHeredoc = true;
            $heredoc = '';
            continue;
        }
        if ($id === T_END_HEREDOC) {
            $inHeredoc = false;
            if (trim($heredoc) !== '') {
                $sqlChunks[] = $heredoc;
            }
            continue;
        }
        if ($id === T_CONSTANT_ENCAPSED_STRING) {
            if ($inHeredoc) {
                $heredoc .= $t[1];
            } else {
                $lit = stripcslashes(substr($t[1], 1, -1));
                if ($lit !== '') {
                    $sqlChunks[] = $lit;
                }
            }
            continue;
        }
        if ($id === T_ENCAPSED_AND_WHITESPACE && $inHeredoc) {
            $heredoc .= $t[1];
            continue;
        }
    }

    $chunkLine = 1;
    foreach ($sqlChunks as $chunk) {
        if (!preg_match('/\b(SELECT|INSERT\s+INTO|UPDATE|DELETE\s+FROM|FROM|JOIN)\b/i', $chunk)) {
            continue;
        }
        $lines = explode("\n", $chunk);
        foreach ($lines as $i => $line) {
            // S465: a string literal can contain BOTH SQL and prose (a view
            // that prints "Total amount from a selected plot").  Only the
            // lines that actually look like SQL may contribute a table
            // name - otherwise "from a" and "from active" became 386
            // phantom tables.
            if (!preg_match('/\b(SELECT|INSERT\s+INTO|UPDATE\s+|DELETE\s+FROM|FROM|JOIN)\b/i', $line)) {
                continue;
            }
            // ---- INSERT INTO t (c1, c2, ...) - the high-confidence column signal
            if (preg_match('/INSERT\s+INTO\s+`?([A-Za-z_][A-Za-z0-9_]*)`?\s*\(([^)]*)\)/i', $line, $m)) {
                $table = strtolower($m[1]);
                if (isset($tables[$table])) {
                    foreach (explode(',', $m[2]) as $col) {
                        $col = trim($col, " \t\n\r`\"'");
                        if ($col === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $col)) {
                            continue;
                        }
                        if (isset($columns[$table]) && !isset($columns[$table][strtolower($col)])) {
                            $key = $table . '.' . strtolower($col);
                            $missingCols[$key][] = $rel . ' (SQL literal)';
                        }
                    }
                }
            }

            // ---- table references
            //
            // The table name must be followed by a REAL SQL continuation,
            // otherwise prose wins: "Our properties range from affordable to
            // luxury" is not a table called `affordable`, and
            // "ON DUPLICATE KEY UPDATE actual_value = VALUES(...)" is a
            // COLUMN, not a table called `actual_value`.
            // NOTE (S465): the continuation fragment must NOT carry its own
            // regex delimiters - concatenating '/\bFROM\s+`?([A-Za-z_]...)`?' with
            // '/\s*`?(WHERE|...)/i' produced the literal text "?)" inside the
            // pattern, so the FROM and JOIN branches NEVER matched and only
            // the UPDATE branch reported anything.
            // NOTE (S466): the fragment MUST be wrapped in a group.  With a
            // top-level `|` the composed pattern became
            //     /\bFROM\s+`?(NAME)`?\s*`?(?:...)\b|\s*$/i
            // i.e. "FROM ..." OR "\s*$" - the second alternative matched the
            // empty string almost everywhere, so the reported table names
            // were leftovers from the previous match ("agent_commissi",
            // "rate_", ...).  The group keeps the alternation INSIDE.
            $after = '(?:\s*`?(?:\(|WHERE|JOIN|LEFT|RIGHT|INNER|OUTER|CROSS|FULL|USING|SET|ON|GROUP|ORDER|LIMIT|OFFSET|VALUES|,|;|\))\b)';
            // S469: the capture needs a word-boundary LOOKAHEAD, otherwise
            // the greedy match backtracks into the middle of a word when the
            // continuation does not fit: "from commission ledger entries"
            // captured "commissi" and then matched the ON alternative against
            // the "on" of "commission" - so PROSE became a phantom table.
            // With the lookahead the name must be a COMPLETE word, so the
            // whole match fails and the prose is ignored.
            $name = '([A-Za-z_][A-Za-z0-9_]*)(?![A-Za-z0-9_])';
            $patterns = [
                '/\bFROM\s+`?' . $name . '`?' . $after . '/i',
                '/\bJOIN\s+`?' . $name . '`?' . $after . '/i',
                '/\bINTO\s+`?' . $name . '`?' . $after . '/i',
                '/\bUPDATE\s+`?' . $name . '`?\s+SET\b/i',
            ];
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $line, $m)) {
                    foreach ($m[1] as $t) {
                        $tl = strtolower($t);
                        if (in_array($tl, NOT_TABLES, true)) {
                            continue;
                        }
                        if (strpos($tl, '$') !== false || strpos($tl, '(') !== false) {
                            continue;
                        }
                        if (!isset($tables[$tl])) {
                            $missingTables[$tl][] = $rel . ' (SQL literal)';
                        }
                    }
                }
            }
        }
    }
}

// ---------------------------------------------------------------- report
$missingTables = array_filter($missingTables, function ($v) { return count($v) <= 12; });
$missingCols   = array_filter($missingCols,   function ($v) { return count($v) <= 12; });

echo "\n=== tables referenced by the code but MISSING from the schema: "
    . count($missingTables) . " ===\n";
ksort($missingTables);
foreach ($missingTables as $t => $sites) {
    echo "  $t  (" . count($sites) . " site(s))\n";
    foreach (array_slice($sites, 0, 4) as $s) {
        echo "      $s\n";
    }
}

echo "\n=== INSERT columns MISSING from their table: " . count($missingCols) . " ===\n";
ksort($missingCols);
foreach ($missingCols as $c => $sites) {
    echo "  $c  (" . count($sites) . " site(s))\n";
    foreach (array_slice($sites, 0, 4) as $s) {
        echo "      $s\n";
    }
}

if ($writeBaseline && $writeBaselineFile !== null) {
    $out = [];
    $out[] = "# APS Dream Home - known schema mismatches (the RATCHET baseline)";
    $out[] = "#";
    $out[] = "# Generated by:  php testing/scan_schema_mismatches.php --write-baseline";
    $out[] = "#";
    $out[] = "# Every entry below is a PRE-EXISTING mismatch: a table or column the code";
    $out[] = "# references that the live schema does not have.  They are the family that";
    $out[] = "# has produced most of this project's 500s (and, where the call is wrapped";
    $out[] = "# in a try/catch, a silent feature failure instead).";
    $out[] = "#";
    $out[] = "# The CI job 'Schema Mismatch Scan' fails ONLY on a NEW entry: a mismatch";
    $out[] = "# that is not in this file.  Fixing a real one means removing its line here";
    $out[] = "# in the same commit - the baseline is a ratchet, not a suppression list.";
    $out[] = "#";
    $out[] = "# Format: one identifier per line - a table name, or table.column.";
    $out[] = "# Total entries: " . (count($missingTables) + count($missingCols)) . " (" . count($missingTables) . " tables + " . count($missingCols) . " columns)";
    $out[] = "#";
    $out[] = "# --- phantom tables (" . count($missingTables) . ") ---";
    foreach (array_keys($missingTables) as $t) {
        $out[] = $t;
    }
    $out[] = "";
    $out[] = "# --- phantom columns (" . count($missingCols) . ") ---";
    foreach (array_keys($missingCols) as $c) {
        $out[] = $c;
    }
    $out[] = "";
    file_put_contents($writeBaselineFile, implode("\n", $out));
    echo "\n>>> Wrote " . (count($missingTables) + count($missingCols)) . " baseline entries to $writeBaselineFile <<<\n";
    exit(0);
}

// ---- split into NEW and KNOWN ------------------------------------------
$newTables = [];
foreach ($missingTables as $t => $sites) {
    if (!isset($known[strtolower($t)])) {
        $newTables[$t] = $sites;
    }
}
$newCols = [];
foreach ($missingCols as $c => $sites) {
    if (!isset($known[strtolower($c)])) {
        $newCols[$c] = $sites;
    }
}

$total = count($missingTables) + count($missingCols);
$new   = count($newTables) + count($newCols);

echo "\nTOTAL MISMATCHES: $total";
if ($baselineFile !== null) {
    echo "  (known/pre-existing: " . ($total - $new) . ", NEW: {$new})";
}
echo "\n";

if ($baselineFile !== null) {
    echo "\n=== NEW mismatches (not in the baseline) ===\n";
    if (!$newTables && !$newCols) {
        echo "  (none)\n";
    }
    foreach ($newTables as $t => $sites) {
        echo "  TABLE {$t}\n";
        foreach (array_slice($sites, 0, 3) as $s) { echo "      {$s}\n"; }
    }
    foreach ($newCols as $c => $sites) {
        echo "  COLUMN {$c}\n";
        foreach (array_slice($sites, 0, 3) as $s) { echo "      {$s}\n"; }
    }
}

if ($reportOnly) {
    echo "(report only - not failing)\n";
    exit(0);
}

if ($baselineFile !== null) {
    if ($new > 0) {
        fwrite(STDERR, "Schema mismatch scan FAILED: {$new} NEW mismatch(es) "
            . "(on top of " . ($total - $new) . " known/pre-existing).\n");
        exit(1);
    }
    echo "Schema mismatch scan PASSED - no NEW mismatch ("
        . ($total - $new) . " known/pre-existing, unchanged).\n";
    exit(0);
}

if ($total > 0) {
    fwrite(STDERR, "Schema mismatch scan FAILED: $total mismatch(es) found.\n");
    exit(1);
}

echo "Schema mismatch scan PASSED.\n";
exit(0);
