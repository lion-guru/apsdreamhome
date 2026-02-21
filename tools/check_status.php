<?php
require_once __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database::getInstance();
$pdo = $db->getConnection();

$tables = ['user', 'users', 'agents', 'associates', 'mlm_profiles'];

echo "Checking Tables:\n";
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmt->fetchColumn();
        echo "Table '$table': $count rows\n";
    } catch (Exception $e) {
        echo "Table '$table': NOT FOUND\n";
    }
}
