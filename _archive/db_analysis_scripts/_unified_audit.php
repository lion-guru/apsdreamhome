<?php
/**
 * Audit unified polymorphic tables candidates
 * user_addresses, user_bank_details, user_kyc
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = [
    'user_addresses', 'user_bank_details', 'user_kyc',
    'user_bank_accounts',  // potential duplicate of user_bank_details
];

foreach ($tables as $t) {
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t'")->fetchColumn();
    if (!$exists) { echo "[MISSING] $t\n\n"; continue; }

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "=== $t: $rows rows ===\n";

    // Columns
    foreach ($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  " . $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
    }

    // FKs
    $fks = $pdo->query("SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
    if ($fks) { echo "  FKs:\n"; foreach ($fks as $f) echo "    $t.{$f['COLUMN_NAME']} -> {$f['REFERENCED_TABLE_NAME']}.{$f['REFERENCED_COLUMN_NAME']}\n"; }

    // Code refs
    $allFiles = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
    foreach ($iter as $f) { if ($f->isFile() && $f->getExtension() === 'php') $allFiles[$f->getPathname()] = file_get_contents($f->getPathname()); }
    $refs = 0; $refFiles = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) { $m = preg_match_all($pattern, $content); if ($m) { $refs += $m; $refFiles[] = basename($path) . "($m)"; } }
    echo "  Code refs: $refs\n";
    foreach ($refFiles as $f) echo "    - $f\n";

    // Sample data
    if ($rows > 0) { echo "  Sample:\n"; $sample = $pdo->query("SELECT * FROM `$t` LIMIT 2")->fetchAll(PDO::FETCH_ASSOC); foreach ($sample as $r) echo "    " . json_encode($r) . "\n"; }
    echo "\n";
}?>