<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Group by domain
$domains = [
    'address' => [], 'bank' => [], 'kyc' => [], 'document' => [],
    'salary' => [], 'audit' => [], 'notification' => [], 'email' => [],
    'sms' => [], 'whatsapp' => [], 'property' => [], 'plot' => [],
];

foreach ($tables as $t) {
    foreach ($domains as $pattern => &$group) {
        if (stripos($t, $pattern) !== false) {
            $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $group[] = ['name' => $t, 'rows' => $rows];
        }
    }
}

foreach ($domains as $name => $group) {
    if (empty($group)) continue;
    echo "=== $name (".count($group)." tables) ===\n";
    usort($group, fn($a,$b) => $b['rows'] <=> $a['rows']);
    foreach ($group as $t) {
        echo sprintf("  %-40s %d rows\n", $t['name'], $t['rows']);
    }
    echo "\n";
}?>