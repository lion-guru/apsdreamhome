<?php
/**
 * Property/Plot consolidation audit
 * plots (194 rows, 146 refs), inventory_plots (417 rows, 8 refs), plot_master (77 rows)
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = ['plots', 'inventory_plots', 'plot_master'];

foreach ($tables as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "=== $t: $rows rows ===\n";
    foreach ($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  {$c['Field']} ({$c['Type']})" . ($c['Null'] === 'YES' ? ' NULL' : ' NOT NULL') . "\n";
    }

    // FKs
    $fks = $pdo->query("SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
    if ($fks) {
        echo "  FKs:\n";
        foreach ($fks as $f) echo "    $t.{$f['COLUMN_NAME']} -> {$f['REFERENCED_TABLE_NAME']}.{$f['REFERENCED_COLUMN_NAME']}\n";
    }
    echo "\n";
}?>