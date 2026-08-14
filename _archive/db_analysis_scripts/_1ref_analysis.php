<?php
/**
 * Find remaining 1-ref tables that could be wrapped in try/catch and dropped
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$oneRef = [];
foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }
    if ($codeRef !== 1) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    // Find which file has the ref
    $refFile = '';
    foreach ($allFiles as $path => $content) {
        if (preg_match($pattern, $content)) { $refFile = basename($path); break; }
    }

    // Check if ref is in try/catch
    $inTry = false;
    foreach ($allFiles as $path => $content) {
        if (!preg_match($pattern, $content)) continue;
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (!preg_match($pattern, $line)) continue;
            for ($j = max(0, $i - 15); $j < $i; $j++) {
                if (preg_match('/try\s*\{/', $lines[$j])) { $inTry = true; break; }
            }
        }
    }

    $oneRef[] = ['name' => $t, 'rows' => $rows, 'file' => $refFile, 'inTry' => $inTry];
}

usort($oneRef, fn($a,$b) => $a['inTry'] <=> $b['inTry'] ?: $b['rows'] <=> $a['rows']);

echo "1-REF TABLES (candidates for drop):\n";
echo sprintf("%-40s %5s  %-35s %s\n", "TABLE", "ROWS", "FILE", "TRY/CATCH");
echo str_repeat("-", 95) . "\n";
foreach ($oneRef as $t) {
    $flag = $t['inTry'] ? "PROTECTED" : "UNPROTECTED";
    echo sprintf("%-40s %5d  %-35s %s\n", $t['name'], $t['rows'], $t['file'], $flag);
}
echo "\nTotal: " . count($oneRef) . "\n";
$protected = count(array_filter($oneRef, fn($t) => $t['inTry']));
echo "Protected (safe to drop): $protected\n";
echo "Unprotected (need wrapping): " . (count($oneRef) - $protected) . "\n";?>