<?php
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
$zeroRef = [];
foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }
    if ($codeRef === 0) {
        $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $fkTo = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
        $zeroRef[] = ['name' => $t, 'rows' => $rows, 'fk' => $fkTo];
    }
}

echo "ZERO-REF TABLES (no code refs):\n";
foreach ($zeroRef as $t) {
    echo sprintf("  %-35s %5d rows FK:%d\n", $t['name'], $t['rows'], $t['fk']);
}
echo "\nTotal: " . count($zeroRef) . "\n";
$droppable = count(array_filter($zeroRef, fn($t) => $t['fk'] == 0));
echo "Droppable (0 FK): $droppable\n";?>