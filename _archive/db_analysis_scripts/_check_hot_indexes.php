<?php
// Check for hot tables that may be missing indexes
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$hotTables = ['users', 'properties', 'leads', 'bookings', 'payments', 'commissions', 'payouts', 'mlm_commission_ledger', 'colonies', 'plots', 'notifications', 'inquiries', 'user_properties', 'wallet_points', 'wallet_transactions', 'document_requirements', 'commission_records'];

echo "Index analysis for hot tables:\n";
echo str_repeat("=", 80) . "\n";

foreach ($hotTables as $t) {
    if (!in_array($t, $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN))) {
        echo "[SKIP] $t - doesn't exist\n";
        continue;
    }

    // Get row count
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    // Get indexes
    $idxStmt = $pdo->query("SHOW INDEX FROM `$t`");
    $indexes = [];
    while ($row = $idxStmt->fetch(PDO::FETCH_ASSOC)) {
        $indexes[$row['Key_name']][] = $row['Column_name'];
    }

    echo sprintf("[%s] %s (%d rows) - %d indexes\n",
        $rows > 1000 ? 'HOT' : 'ok',
        $t, $rows, count($indexes)
    );

    // Show indexes
    foreach ($indexes as $name => $cols) {
        echo sprintf("  %s: %s\n", $name, implode(', ', $cols));
    }
    echo "\n";
}?>