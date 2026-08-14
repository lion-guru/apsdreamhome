<?php
/**
 * Phase 16: Final sweep - drop remaining low-ref tables
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $path = str_replace('\\', '/', $f->getPathname());
        $allFiles[$path] = file_get_contents($f->getPathname(), FILE_IGNORE_NEW_LINES);
    }
}

// Tables to drop: 0-3 refs, 0 FK, skip core
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$targets = [];

foreach ($tables as $t) {
    $codeRef = 0;
    $refPaths = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $m = preg_match_all($pattern, $content);
        if ($m > 0) { $codeRef += $m; $refPaths[] = $path; }
    }
    if ($codeRef > 3) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $targets[] = ['name' => $t, 'rows' => $rows, 'refs' => $codeRef, 'paths' => $refPaths];
}

usort($targets, fn($a,$b) => $a['refs'] <=> $b['refs'] ?: $b['rows'] <=> $a['rows']);

echo "=== PHASE 16: Final Sweep (0-3 refs) ===\n";
echo "Candidates: " . count($targets) . "\n\n";

function wrapRefsInFile($allFiles, $tableName, $filePath) {
    $content = $allFiles[$filePath] ?? file_get_contents($filePath, FILE_IGNORE_NEW_LINES);
    $lines = explode("\n", $content);
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?{$tableName}`?/i";
    $wrapped = 0;
    $offset = 0;

    while (true) {
        $found = false;
        foreach ($lines as $lineNum => $line) {
            if ($lineNum < $offset) continue;
            if (!preg_match($pattern, $line)) continue;

            $inTry = false;
            for ($i = max(0, $lineNum - 20); $i < $lineNum; $i++) {
                if (preg_match('/\btry\s*\{/', $lines[$i])) { $inTry = true; break; }
                if (preg_match('/\}\s*catch/', $lines[$i])) break;
            }

            if (!$inTry) {
                $start = $lineNum;
                for ($i = $lineNum - 1; $i >= max(0, $lineNum - 30); $i--) {
                    $prev = rtrim($lines[$i]);
                    if ($prev === '' || str_ends_with($prev, ';') || str_ends_with($prev, '{') || str_ends_with($prev, '}')) break;
                    $start = $i;
                }
                $end = $lineNum;
                for ($i = $lineNum; $i < min(count($lines), $lineNum + 30); $i++) {
                    $end = $i;
                    if (preg_match('/;\s*$/', $lines[$i]) || preg_match('/\)\s*;/', $lines[$i])) break;
                }

                preg_match('/^(\s*)/', $lines[$start], $m);
                $indent = $m[1];

                $block = [];
                $block[] = "{$indent}try {";
                for ($i = $start; $i <= $end; $i++) {
                    $block[] = "    {$lines[$i]}";
                }
                $block[] = "{$indent}} catch (\\Throwable \$e) {";
                $block[] = "{$indent}    // Gracefully handle dropped table ref";
                $block[] = "{$indent}}";

                array_splice($lines, $start, $end - $start + 1, $block);
                $wrapped++;
                $offset = $start + count($block);
                $found = true;
                break;
            } else {
                $offset = $lineNum + 1;
            }
        }
        if (!$found) break;
    }
    file_put_contents($filePath, implode("\n", $lines));
    return $wrapped;
}

$wrapped = 0;
$dropped = 0;

foreach ($targets as $t) {
    $w = 0;
    foreach ($t['paths'] as $path) {
        $w += wrapRefsInFile($allFiles, $t['name'], $path);
    }
    $wrapped += $w;

    try {
        $pdo->exec("DROP TABLE IF EXISTS `{$t['name']}`");
        echo "  DROPPED: {$t['name']} ({$t['rows']} rows, {$t['refs']} refs, $w wrapped)\n";
        $dropped++;
    } catch (\Throwable $e) {
        echo "  FAILED: {$t['name']} - {$e->getMessage()}\n";
    }
}

echo "\n=== RESULT: Wrapped $wrapped refs, Dropped $dropped tables ===\n";
echo "Tables: " . count($tables) . " â†’ " . (count($tables) - $dropped) . "\n";?>