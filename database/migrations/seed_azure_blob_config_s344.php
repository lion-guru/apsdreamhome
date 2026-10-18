<?php
/**
 * Migration: Seed Azure Blob Storage service config
 * Session: 344
 * Idempotent: Safe to re-run, inserts only if missing
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SELECT COUNT(*) FROM service_configs WHERE service_name = 'azure_blob'")->fetchColumn();
if ((int)$exists > 0) {
    echo "SKIP azure_blob configs - already seeded\n";
} else {
    $stmt = $pdo->prepare("
        INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order)
        VALUES (1, 'azure_blob', ?, ?, ?, ?, ?, 'storage', ?)
    ");
    $rows = [
        ['connection_string', '', 'password', 'Azure Storage Connection String (DefaultEndpointsProtocol=https;AccountName=xxx;AccountKey=xxx;EndpointSuffix=core.windows.net)', 1, 10],
        ['container', '', 'text', 'Container Name', 0, 20],
        ['test_mode', '1', 'boolean', 'Use test mode (skip real API calls)', 0, 30],
    ];
    foreach ($rows as $row) {
        $stmt->execute($row);
    }
    echo "SEEDED azure_blob configs (3 rows)\n";
}

echo "Done.\n";