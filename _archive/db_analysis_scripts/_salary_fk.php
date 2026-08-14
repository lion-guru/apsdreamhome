<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$fks = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='salary_structures' AND TABLE_SCHEMA='apsdreamhome'")->fetchAll(PDO::FETCH_ASSOC);
echo "FK references to salary_structures:\n";
foreach ($fks as $f) echo "  {$f['TABLE_NAME']}.{$f['COLUMN_NAME']}\n";

// Also check code refs
$tables = ['salary_structures'];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    foreach ($tables as $t) {
        if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i", $content)) {
            echo "  Code ref: " . basename($f->getPathname()) . "\n";
        }
    }
}?>