<?php
/**
 * Migration: Seed GCS (Google Cloud Storage) service config
 * Session: 344
 * Idempotent: Safe to re-run, inserts only if missing
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SELECT COUNT(*) FROM service_configs WHERE service_name = 'gcs'")->fetchColumn();
if ((int)$exists > 0) {
    echo "SKIP gcs configs - already seeded\n";
} else {
    $stmt = $pdo->prepare("
        INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order)
        VALUES (1, 'gcs', ?, ?, ?, ?, ?, 'integrations', ?)
    ");
    $rows = [
        ['project_id', '', 'text', 'Google Cloud Project ID', 0, 10],
        ['bucket', '', 'text', 'GCS Bucket Name', 0, 20],
        ['key_file', '', 'text', 'Path to Service Account JSON Key File', 0, 30],
        ['key_json', '', 'password', 'Service Account JSON (alternative to key_file)', 1, 40],
        ['test_mode', '1', 'boolean', 'Use test mode (skip real API calls)', 0, 50],
    ];
    foreach ($rows as $row) {
        $stmt->execute($row);
    }
    echo "SEEDED gcs configs (5 rows)\n";
}

echo "Done.\n";