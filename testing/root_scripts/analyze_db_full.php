<?php
require __DIR__ . '/config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
sort($tables);

echo "==========================================\n";
echo "  APS DREAM HOME - DATABASE ANALYSIS\n";
echo "  Total Tables: " . count($tables) . "\n";
echo "==========================================\n\n";

// Group tables by category
$categories = [
    'users' => [],
    'leads' => [],
    'customers' => [],
    'properties' => [],
    'plots' => [],
    'bookings' => [],
    'mlm' => [],
    'associates' => [],
    'agents' => [],
    'employees' => [],
    'payments' => [],
    'commissions' => [],
    'visits' => [],
    'tasks' => [],
    'tickets' => [],
    'gallery' => [],
    'content' => [],
    'settings' => [],
    'logs' => [],
    'other' => []
];

foreach ($tables as $table) {
    $categorized = false;
    foreach (array_keys($categories) as $cat) {
        if (strpos($table, $cat) !== false) {
            $categories[$cat][] = $table;
            $categorized = true;
            break;
        }
    }
    if (!$categorized) {
        $categories['other'][] = $table;
    }
}

// Print by category
foreach ($categories as $cat => $list) {
    if (!empty($list)) {
        echo "\n📁 " . strtoupper($cat) . " (" . count($list) . " tables)\n";
        echo str_repeat('-', 40) . "\n";
        foreach ($list as $t) {
            echo "  • $t\n";
        }
    }
}

echo "\n\n✅ ANALYSIS COMPLETE\n";
