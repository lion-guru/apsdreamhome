<?php
/**
 * Full ref count for all 219 remaining tables
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
$results = [];

foreach ($tables as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }

    $fkTo = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();

    $results[] = ['name' => $t, 'rows' => $rows, 'refs' => $codeRef, 'fk' => $fkTo];
}

usort($results, fn($a,$b) => $a['refs'] <=> $b['refs'] ?: $a['rows'] <=> $b['rows']);

echo sprintf("%-40s %6s %5s %4s\n", "TABLE", "ROWS", "REFS", "FKs");
echo str_repeat("-", 60) . "\n";

$zeroRef = 0; $lowRef = 0; $medRef = 0; $highRef = 0;
foreach ($results as $t) {
    $flag = '';
    if ($t['refs'] === 0) { $flag = '***'; $zeroRef++; }
    elseif ($t['refs'] <= 3) { $flag = '**'; $lowRef++; }
    elseif ($t['refs'] <= 8) { $flag = '*'; $medRef++; }
    else { $highRef++; }

    echo sprintf("%-40s %6d %5d %4d %s\n", $t['name'], $t['rows'], $t['refs'], $t['fk'], $flag);
}

echo "\n=== SUMMARY ===\n";
echo "0 refs (drop candidates): $zeroRef\n";
echo "1-3 refs (wrap+drop candidates): $lowRef\n";
echo "4-8 refs (selective drop): $medRef\n";
echo "9+ refs (keep): $highRef\n";
echo "Total: " . count($results) . "\n";?>