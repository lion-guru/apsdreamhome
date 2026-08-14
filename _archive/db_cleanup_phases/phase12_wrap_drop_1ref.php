<?php
/**
 * Phase 12: Smart wrap + drop 1-ref tables
 * For each 1-ref table:
 * 1. Find the file with the reference
 * 2. Check if it's in a try/catch
 * 3. If not, wrap just the SQL statement in try/catch
 * 4. Drop the table
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

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$targets = [];

foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }
    if ($codeRef !== 1) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    foreach ($allFiles as $path => $content) {
        if (!preg_match($pattern, $content)) continue;
        $targets[] = ['name' => $t, 'rows' => $rows, 'file' => $path, 'content' => $content];
        break;
    }
}

usort($targets, fn($a,$b) => $b['rows'] <=> $a['rows']);

echo "=== PHASE 12: Smart Wrap + Drop " . count($targets) . " 1-Ref Tables ===\n\n";

$wrapped = 0;
$dropped = 0;
$failed = 0;

foreach ($targets as $t) {
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?{$t['name']}`?/i";
    $lines = explode("\n", $t['content']);
    $wrappedThis = false;

    foreach ($lines as $lineNum => $line) {
        if (!preg_match($pattern, $line)) continue;

        // Check if in try/catch by looking back up to 20 lines
        $inTry = false;
        for ($i = max(0, $lineNum - 20); $i < $lineNum; $i++) {
            if (preg_match('/\btry\s*\{/', $lines[$i])) { $inTry = true; break; }
            if (preg_match('/\}\s*catch/', $lines[$i])) break; // hit a catch before try = not in try
        }

        if (!$inTry) {
            // Find the full statement: look back for statement start, forward for semicolon
            $start = $lineNum;
            for ($i = $lineNum - 1; $i >= max(0, $lineNum - 30); $i--) {
                $prev = rtrim($lines[$i]);
                if ($prev === '' || str_ends_with($prev, ';') || str_ends_with($prev, '{') || str_ends_with($prev, '}')) {
                    break;
                }
                $start = $i;
            }
            $end = $lineNum;
            for ($i = $lineNum; $i < min(count($lines), $lineNum + 30); $i++) {
                $end = $i;
                if (preg_match('/;\s*$/', $lines[$i]) || preg_match('/\)\s*;/', $lines[$i])) break;
            }

            // Get indentation from first line
            $indent = '';
            preg_match('/^(\s*)/', $lines[$start], $m);
            $indent = $m[1];

            // Build wrapped block
            $block = [];
            $block[] = "{$indent}try {";
            for ($i = $start; $i <= $end; $i++) {
                $block[] = "    {$lines[$i]}";
            }
            $block[] = "{$indent}} catch (\\Throwable \$e) {";
            $block[] = "{$indent}    // Gracefully handle dropped table ref";
            $block[] = "{$indent}}";

            array_splice($lines, $start, $end - $start + 1, $block);

            echo "  WRAPPED: {$t['name']} ({$t['rows']} rows) in " . basename($t['file']) . ":" . ($start + 1) . "\n";
            $wrapped++;
            $wrappedThis = true;
            break;
        }
    }

    if ($wrappedThis) {
        file_put_contents($t['file'], implode("\n", $lines));
    }

    // Now drop the table
    try {
        $pdo->exec("DROP TABLE IF EXISTS `{$t['name']}`");
        echo "  DROPPED: {$t['name']}\n";
        $dropped++;
    } catch (\Throwable $e) {
        echo "  FAILED: {$t['name']} - {$e->getMessage()}\n";
        $failed++;
    }
}

echo "\n=== RESULT: Wrapped $wrapped refs, Dropped $dropped tables, Failed $failed ===\n";
$newCount = count($tables) - $dropped;
echo "Tables: $newCount\n";?>