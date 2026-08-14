<?php
/**
 * Deep analysis of all 223 remaining tables
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
$zeroRow = [];
$lowRef = [];
$unused = [];

foreach ($tables as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }

    $fkTo = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    $fkFrom = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME='$t' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();

    if ($rows === 0 && $codeRef === 0) {
        $zeroRow[] = ['name' => $t, 'fk_to' => $fkTo, 'fk_from' => $fkFrom];
    } elseif ($codeRef === 0 && $fkTo === 0) {
        $unused[] = ['name' => $t, 'rows' => $rows, 'fk_to' => $fkTo, 'fk_from' => $fkFrom];
    } elseif ($codeRef <= 2 && $fkTo === 0 && $rows <= 5) {
        $lowRef[] = ['name' => $t, 'rows' => $rows, 'refs' => $codeRef, 'fk_to' => $fkTo];
    }
}

echo "=== ZERO-ROW + ZERO-REF (always safe to drop) ===\n";
foreach ($zeroRow as $t) {
    echo sprintf("  %-35s FK_to:%d FK_from:%d\n", $t['name'], $t['fk_to'], $t['fk_from']);
}
echo "Count: " . count($zeroRow) . "\n\n";

echo "=== ZERO-REF + 0 FK_TO (no code refs, not referenced) ===\n";
foreach ($unused as $t) {
    echo sprintf("  %-35s %5d rows FK_from:%d\n", $t['name'], $t['rows'], $t['fk_from']);
}
echo "Count: " . count($unused) . "\n\n";

echo "=== LOW-REF (<=2 refs, 0 FK_TO, <=5 rows) ===\n";
foreach ($lowRef as $t) {
    echo sprintf("  %-35s %5d rows %d refs\n", $t['name'], $t['rows'], $t['refs']);
}
echo "Count: " . count($lowRef) . "\n\n";

$total = count($zeroRow) + count($unused) + count($lowRef);
echo "TOTAL DROP CANDIDATES: $total\n";?>