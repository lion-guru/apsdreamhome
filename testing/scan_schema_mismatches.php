<?php
/**
 * Proactive SQL schema-mismatch scanner.
 *
 * Finds alias.column references in SQL strings inside PHP files where the
 * column does NOT exist in the aliased table (live DB via information_schema).
 * Catches the "Unknown column (1054)" bug class at scan time instead of runtime.
 *
 * Heuristics (deliberately conservative to limit false positives):
 * - Only checks references qualified by an alias/table explicitly mapped
 *   in the same SQL string via FROM/JOIN <table> [AS] <alias>
 * - Skips: subquery-derived aliases, SELECT-list aliases (AS x), function
 *   calls, * wildcards, parameter placeholders
 *
 * Run: php testing/scan_schema_mismatches.php [--dir=app/Http/Controllers]
 */

require_once __DIR__ . '/../config/bootstrap.php';

$scanDirs = ['app/Http/Controllers', 'app/Services'];
foreach ($argv as $arg) {
    if (preg_match('/^--dir=(.+)$/', $arg, $m)) {
        $scanDirs = [$m[1]];
    }
}

// ── Load live schema ─────────────────────────────────────────────────────
$pdo = App\Core\Database\Database::getInstance()->getConnection();
$schema = []; // table => set(columns)
$rows = $pdo->query("
    SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $schema[strtolower($r['TABLE_NAME'])][strtolower($r['COLUMN_NAME'])] = true;
}
echo "Schema loaded: " . count($schema) . " tables\n";

// Tables known to be views or dynamic (skip strict checking noise if needed)

// ── Scan files ───────────────────────────────────────────────────────────
$issues = [];
$filesScanned = 0;

$extractSqlStrings = function ($code) {
    // Extract all double-quoted string literals (SQL lives in them in this codebase).
    // Handles escaped quotes \" minimally. Skips comments roughly by line heuristics.
    $strings = [];
    if (!preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/s', $code, $m)) {
        return $strings;
    }
    foreach ($m[1] as $s) {
        $up = strtoupper($s);
        if (preg_match('/\b(SELECT|INSERT|UPDATE|DELETE|JOIN)\b/', $up)) {
            $strings[] = $s;
        }
    }
    return $strings;
};

$parseAliases = function ($sql) {
    // FROM/JOIN <table> [AS] <alias> — table may be backticked
    $map = [];
    if (preg_match_all('/(?:\bFROM\b|\bJOIN\b)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?(?:\s+(?:AS\s+)?([a-zA-Z_][a-zA-Z0-9_]*))?/i', $sql, $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $table = strtolower($hit[1]);
            $alias = isset($hit[2]) ? strtolower($hit[2]) : $table;
            // Exclude keyword false-matches (ON, WHERE following JOIN)
            if (in_array($alias, ['on', 'where', 'left', 'right', 'inner', 'outer', 'cross', 'set', 'as', 'using', 'group', 'order', 'limit'], true)) {
                $alias = $table;
            }
            $map[$alias] = $table;
        }
    }
    return $map;
};

foreach ($scanDirs as $dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->getExtension() !== 'php') continue;
        $path = str_replace('\\', '/', $file->getPathname());
        if (strpos($path, '/_archive') !== false) continue;
        $code = file_get_contents($path);
        // Strip PHP comments to reduce false hits
        $code = preg_replace('/\/\/[^\n]*/', '', $code);
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);
        $filesScanned++;

        foreach ($extractSqlStrings($code) as $sql) {
            $aliases = $parseAliases($sql);
            if (!$aliases) continue;
            // Find alias.column refs
            if (!preg_match_all('/\b([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\b/', $sql, $refs, PREG_SET_ORDER)) {
                continue;
            }
            foreach ($refs as $ref) {
                $al = strtolower($ref[1]);
                $col = strtolower($ref[2]);
                if (!isset($aliases[$al])) continue;
                $table = $aliases[$al];
                if (!isset($schema[$table])) continue;      // unknown table — not our concern here
                if ($col === '*') continue;
                if (!isset($schema[$table][$col])) {
                    $line = substr_count(substr($code, 0, strpos($code, $ref[0])), "\n") + 1;
                    $key = "$path:$line";
                    $issues[$key] = sprintf('%s.%s (table `%s` has no such column)  SQL: ...%s...',
                        $ref[1], $ref[2], $table,
                        substr($sql, max(0, strpos($sql, $ref[0]) - 30), 60)
                    );
                }
            }
        }
    }
}

echo "Scanned $filesScanned files\n";
if (empty($issues)) {
    echo "CLEAN: no schema mismatches found\n";
} else {
    echo "FOUND " . count($issues) . " potential mismatch(es):\n\n";
    ksort($issues);
    foreach ($issues as $loc => $desc) {
        echo "  $loc\n        $desc\n";
    }
}
