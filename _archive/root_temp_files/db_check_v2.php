<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}
$config = require __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "--- triggers check ---\n";
    $stmt = $pdo->query("SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_STATEMENT FROM information_schema.TRIGGERS");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (in_array($row['EVENT_OBJECT_TABLE'], ['investment_plans', 'investments'])) {
            echo "Trigger: {$row['TRIGGER_NAME']} | Table: {$row['EVENT_OBJECT_TABLE']} | Event: {$row['EVENT_MANIPULATION']} | Action: {$row['ACTION_STATEMENT']}\n";
        }
    }
    echo "--- check done ---\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>