<?php
/**
 * PHASE 8: Drop tables that are clearly fake/seed data or low-value
 * Criteria: >100 rows AND <=3 code refs AND no FKs
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$candidates = [];
foreach ($tables as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($rows < 100) continue;

    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) {
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef > 3) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $candidates[] = ['name' => $t, 'rows' => $rows, 'code' => $codeRef];
}

echo "Candidates (>=100 rows, <=3 refs, 0 FKs):\n";
foreach ($candidates as $c) {
    echo sprintf("  %-45s %5d rows  Code:%d\n", $c['name'], $c['rows'], $c['code']);
}

echo "\nDropping...\n";
$dropped = 0;
foreach ($candidates as $c) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `" . $c['name'] . "`");
        echo "✓ {$c['name']}\n";
        $dropped++;
    } catch (Exception $e) {
        echo "✗ {$c['name']}: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\nDropped: $dropped\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
