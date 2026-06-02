<?php
/**
 * PHASE 6: Identify truly duplicate tables
 * Group by similar prefixes/purposes and find tables that could be merged
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Find groups of 2+ tables with very similar names (likely duplicates)
$groups = [];
foreach ($tables as $t) {
    // Extract base name (remove _v2, _backup, _legacy, _unified, _test, _old suffixes)
    $base = preg_replace('/_(v\d+|backup.*|legacy.*|unified.*|test.*|old.*|new.*|copy.*)$/i', '', $t);
    $groups[$base][] = $t;
}

// Show groups with 2+ tables
echo "=== POTENTIAL DUPLICATE GROUPS ===\n\n";
foreach ($groups as $base => $list) {
    if (count($list) < 2) continue;
    echo "Base: $base\n";
    foreach ($list as $t) {
        $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        $codeRef = 0;
        $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
        foreach ($allFiles as $content) {
            $codeRef += preg_match_all($pattern, $content);
        }
        echo sprintf("  - %-45s %5d rows  Code:%d\n", $t, $rows, $codeRef);
    }
    echo "\n";
}
