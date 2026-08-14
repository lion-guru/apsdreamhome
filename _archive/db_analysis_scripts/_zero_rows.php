<?php
/**
 * Find zero-row tables with low code refs = safe to drop
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
$zeroRow = [];
foreach ($tables as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($rows > 0) continue;

    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }
    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();

    $zeroRow[] = ['name' => $t, 'code' => $codeRef, 'fk' => $fkTo];
}

usort($zeroRow, fn($a,$b) => $a['code'] <=> $b['code']);

echo "ZERO-ROW TABLES (0 rows):\n";
echo sprintf("%-45s %s %s\n", "TABLE", "CODE_REFS", "FKs");
echo str_repeat("-", 65) . "\n";
foreach ($zeroRow as $t) {
    $flag = ($t['code'] <= 2 && $t['fk'] == 0) ? " â†� SAFE" : "";
    echo sprintf("%-45s %5d     %3d%s\n", $t['name'], $t['code'], $t['fk'], $flag);
}
echo "\nTotal zero-row: " . count($zeroRow) . "\n";
$safe = count(array_filter($zeroRow, fn($t) => $t['code'] <= 2 && $t['fk'] == 0));
echo "Safe to drop (<=2 refs, 0 FKs): $safe\n";?>