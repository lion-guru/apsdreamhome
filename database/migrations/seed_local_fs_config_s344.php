<?php
/**
 * Migration: Seed Local Filesystem Storage config
 * Session: 344
 * Idempotent: Safe to re-run, inserts only if missing
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SELECT COUNT(*) FROM service_configs WHERE service_name = 'local_fs'")->fetchColumn();
if ((int)$exists > 0) {
    echo "SKIP local_fs configs - already seeded\n";
} else {
    $stmt = $pdo->prepare("
        INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order)
        VALUES (1, 'local_fs', ?, ?, ?, ?, 0, 'storage', ?)
    ");
    $rows = [
        ['root_path', 'C:/xampp/htdocs/apsdreamhome/storage/files', 'text', 'Local filesystem storage root path', 10],
        ['base_url', '', 'text', 'Public base URL for file URLs (e.g. https://files.example.com)', 20],
        ['public_path', 'storage/files', 'text', 'Web-accessible sub-path under root', 30],
    ];
    foreach ($rows as $row) {
        $stmt->execute($row);
    }
    echo "SEEDED local_fs configs (3 rows)\n";
}

echo "Done.\n";