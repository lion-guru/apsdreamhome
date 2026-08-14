<?php
/**
 * Find duplicate-name groups (tables with same base name)
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Group by base name (strip common suffixes)
$groups = [];
foreach ($tables as $t) {
    $base = preg_replace('/(_v2|_backup|_archive|_legacy|_old|_new|_copy|_temp|_test|_tmp|_bkp|_bak)$/', '', $t);
    if ($base === $t) continue; // Only tables with suffixes
    if (!isset($groups[$base])) $groups[$base] = [];
    $groups[$base][] = $t;
}

// Also group by common patterns
$patterns = [
    'notification' => [], 'email' => [], 'sms' => [], 'whatsapp' => [],
    'document' => [], 'salary' => [], 'audit' => [], 'property' => [],
    'plot' => [], 'user' => [], 'lead' => [], 'booking' => [],
];

foreach ($tables as $t) {
    foreach ($patterns as $p => &$group) {
        if (stripos($t, $p) !== false && !in_array($t, $group)) {
            $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            $group[] = "$t ($rows rows)";
        }
    }
}

echo "=== SUFFIX DUPLICATES ===\n";
foreach ($groups as $base => $dupes) {
    echo "$base: " . implode(', ', $dupes) . "\n";
}

echo "\n=== PATTERN GROUPS (>1 table) ===\n";
foreach ($patterns as $name => $tables) {
    if (count($tables) <= 1) continue;
    echo "\n$name (" . count($tables) . " tables):\n";
    foreach ($tables as $t) echo "  $t\n";
}?>