<?php
/**
 * MLM Schema Consolidation Analysis
 * Find which tables are real vs duplicates
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$mlmTables = array_filter($allTables, fn($t) => preg_match('/^(mlm_|network_|wallet_|commission_|payout_|associate_)/i', $t));
sort($mlmTables);

echo "=== MLM TABLES (47 total) ===\n\n";

foreach ($mlmTables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    // Check FK references
    $fkTo = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$t' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();
    $fkFrom = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = '$t' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();

    // Check code references
    $codeRef = 0;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
    foreach ($iter as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $content = file_get_contents($f->getPathname());
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i", $content)) $codeRef++;
        }
    }

    $status = $count > 0 ? '✓' : '·';
    $dupMarker = '';
    if ($count < 5 && $fkTo == 0) $dupMarker = ' [EMPTY+ORPHAN]';
    elseif ($count < 5 && $codeRef == 0) $dupMarker = ' [EMPTY+UNUSED]';
    elseif ($codeRef == 0 && $fkTo == 0) $dupMarker = ' [ORPHAN]';

    echo sprintf("%s %-40s %4d rows  FK→:%d FK←:%d  Code:%d%s\n",
        $status, $t, $count, $fkFrom, $fkTo, $codeRef, $dupMarker);
}

echo "\n=== COMMISSION TABLE GROUP ===\n\n";
$commissionTables = array_filter($mlmTables, fn($t) => preg_match('/commission/i', $t));
foreach ($commissionTables as $t) {
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_COLUMN);
    echo "- $t: " . implode(', ', array_slice($cols, 0, 6)) . "...\n";
}

echo "\n=== TREE/NETWORK TABLE GROUP ===\n\n";
$treeTables = array_filter($mlmTables, fn($t) => preg_match('/(tree|network|level)/i', $t));
foreach ($treeTables as $t) {
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_COLUMN);
    echo "- $t: " . implode(', ', array_slice($cols, 0, 6)) . "...\n";
}

echo "\n=== PAYOUT TABLE GROUP ===\n\n";
$payoutTables = array_filter($mlmTables, fn($t) => preg_match('/payout/i', $t));
foreach ($payoutTables as $t) {
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_COLUMN);
    echo "- $t: " . implode(', ', array_slice($cols, 0, 6)) . "...\n";
}
