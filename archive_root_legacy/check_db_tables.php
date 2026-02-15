<?php
require_once __DIR__ . '/app/Models/Database.php';
$db = \App\Models\Database::getInstance();

$tables = [
    'user',
    'admin',
    'ai_chatbot_config',
    'ai_chatbot_interactions',
    'ai_config',
    'ai_lead_scores',
    'ai_logs',
    'ai_workflows',
    'bookings',
    'properties',
    'commission_transactions',
    'expenses'
];

echo "Database Table Check:\n";
echo "--------------------\n";

foreach ($tables as $table) {
    try {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt && $stmt->rowCount() > 0;
        echo ($exists ? "[OK]" : "[MISSING]") . " $table\n";
    } catch (\Exception $e) {
        echo "[ERROR] $table: " . $e->getMessage() . "\n";
    }
}
