<?php
/**
 * PHASE 11: Auto-wrap unprotected SQL refs in try/catch, then drop
 * This makes the code more robust AND allows dropping
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = $f->getPathname();
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Find tables that are droppable but need try/catch wrapping
$wrapAndDrop = [];
foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path) {
        $content = file_get_contents($path);
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef > 3) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    $isView = $pdo->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($isView === 'VIEW') continue;

    $wrapAndDrop[] = ['name' => $t, 'rows' => $rows, 'code' => $codeRef];
}

echo "Candidates for wrap+drop: " . count($wrapAndDrop) . "\n";
echo "Tables list (top 20):\n";
foreach (array_slice($wrapAndDrop, 0, 20) as $c) {
    echo sprintf("  %-45s %d rows  %d refs\n", $c['name'], $c['rows'], $c['code']);
}
echo "...\n";
