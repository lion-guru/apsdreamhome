<?php
/**
 * Migration: Seed google_calendar service config (Gap 23 DB-backed config)
 * Session: 337
 * Idempotent: Safe to re-run, inserts only if missing
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SELECT COUNT(*) FROM service_configs WHERE service_name = 'google_calendar'")->fetchColumn();
if ((int)$exists > 0) {
    echo "SKIP google_calendar configs - already seeded\n";
} else {
    $stmt = $pdo->prepare("
        INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order)
        VALUES (1, 'google_calendar', ?, ?, ?, ?, ?, 'integrations', ?)
    ");
    $rows = [
        ['client_id', '', 'password', 'Google OAuth Client ID', 1, 10],
        ['client_secret', '', 'password', 'Google OAuth Client Secret', 1, 20],
        ['redirect_uri', '', 'text', 'OAuth Redirect URI', 0, 30],
        ['test_mode', '1', 'boolean', 'Use test mode', 0, 40],
    ];
    foreach ($rows as $row) {
        $stmt->execute($row);
    }
    echo "SEEDED google_calendar configs (4 rows)\n";
}

echo "Done.\n";