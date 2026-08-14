<?php
/**
 * Salary tables audit
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = ['employee_salary_structure', 'salary_payments', 'salary_tracker', 'salary_structures'];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

foreach ($tables as $t) {
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t'")->fetchColumn();
    if (!$exists) { echo "[MISSING] $t\n\n"; continue; }

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $codeRef = 0; $refFiles = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $m = preg_match_all($pattern, $content);
        if ($m) { $codeRef += $m; $refFiles[] = basename($path); }
    }

    echo "=== $t: $rows rows, $codeRef refs ===\n";
    foreach ($pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
    if ($refFiles) { echo "  Refs: " . implode(', ', $refFiles) . "\n"; }
    echo "\n";
}?>