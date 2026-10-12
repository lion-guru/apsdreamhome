<?php
/**
 * IoT Threshold Alerts (Session 222).
 *
 * NOTE vs first cut: the iot_devices/iot_readings/iot_device_catalog/iot_automations
 * family ALREADY EXISTED (IoTService + /admin/iot since S-earlier) — a proposed
 * parallel iot_telemetry table was dropped as a duplicate of iot_readings, and
 * the iot_devices CREATE was a silent IF NOT EXISTS no-op (no damage).
 * This migration therefore creates ONLY iot_alerts (new capability: threshold
 * alerts + device_offline scanning + resolve workflow).
 *
 * Idempotent: safe to re-run.
 */

require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$sqls = [];

$sqls['iot_alerts'] = "CREATE TABLE IF NOT EXISTS iot_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    device_id INT UNSIGNED NOT NULL,
    colony_id INT UNSIGNED NOT NULL DEFAULT 0,
    alert_type ENUM('battery_low','barrier_fault','device_offline','fault') NOT NULL,
    severity ENUM('info','warning','critical') NOT NULL DEFAULT 'warning',
    message VARCHAR(255) NOT NULL,
    is_resolved TINYINT(1) NOT NULL DEFAULT 0,
    resolved_by INT NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_iot_open (is_resolved, colony_id),
    INDEX idx_iot_device (device_id, alert_type, is_resolved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

foreach ($sqls as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "OK  $name\n";
    } catch (Throwable $e) {
        echo "ERR $name: " . $e->getMessage() . "\n";
    }
}

echo "=== IoT Alerts Migration Complete ===\n";
