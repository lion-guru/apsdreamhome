<?php
/**
 * Migrate old audit_log to new audit_logs
 */
require __DIR__ . '/../vendor/autoload.php';

$db = \App\Core\Database\Database::getInstance();

$oldLogs = $db->fetchAll('SELECT * FROM audit_log ORDER BY created_at ASC');
echo "Found " . count($oldLogs) . " logs to migrate\n";

$migrated = 0;
foreach ($oldLogs as $log) {
    try {
        $db->insert('audit_logs', [
            'user_id' => (int)($log['user_id'] ?? 0),
            'user_role' => $log['user_role'] ?? 'unknown',
            'action' => $log['action'] ?? 'unknown',
            'action_type' => 'update',
            'entity_type' => null,
            'entity_id' => null,
            'description' => $log['details'] ?? null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => $log['ip_address'] ?? null,
            'user_agent' => null,
            'request_url' => null,
            'request_method' => null,
            'session_id' => null,
            'status' => 'success',
            'error_message' => null,
            'metadata' => json_encode(['old_table' => true]),
            'created_at' => $log['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
        $migrated++;
    } catch (Exception $e) {
        error_log("Migration error for log ID {$log['id']}: " . $e->getMessage());
    }
}

echo "Migrated $migrated logs\n";