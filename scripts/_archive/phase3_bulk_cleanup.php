<?php
/**
 * PHASE 3: BULK CLEANUP - Drop tables with 0 code refs AND 0 incoming FKs
 * Conservative: only drop if BOTH conditions met
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

// Pre-load all code refs
$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}
echo "Files scanned: " . count($allFiles) . "\n\n";

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$drops = [];
foreach ($tables as $t) {
    // Check code refs
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) {
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef > 0) continue;

    // Check incoming FKs
    $fkTo = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$t' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();
    if ($fkTo > 0) continue;

    // Check if it's a view (views get treated as tables in SHOW TABLES)
    $isView = $pdo->query("
        SELECT TABLE_TYPE FROM information_schema.TABLES
        WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();
    if ($isView === 'VIEW') continue;  // Don't drop views, only base tables

    // Check if view references it (MySQL way)
    try {
        $viewRefs = $pdo->query("
            SELECT COUNT(*) FROM information_schema.VIEWS
            WHERE VIEW_DEFINITION LIKE '%`$t`%' AND TABLE_SCHEMA = 'apsdreamhome'
        ")->fetchColumn();
        if ($viewRefs > 0) continue;
    } catch (Exception $e) {}

    $drops[] = $t;
}

echo "Candidates (0 code refs, 0 FKs, 0 views): " . count($drops) . "\n\n";

$dropped = 0;
$skipped = 0;
foreach ($drops as $t) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        echo "✓ $t\n";
        $dropped++;
    } catch (Exception $e) {
        echo "✗ $t: {$e->getMessage()}\n";
        $skipped++;
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== SUMMARY ===\n";
echo "Dropped: $dropped\n";
echo "Skipped: $skipped\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
