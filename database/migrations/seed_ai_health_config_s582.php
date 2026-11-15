<?php
// Idempotent: seed AI health flag in service_configs
$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$check = $pdo->prepare("SELECT id FROM service_configs WHERE service_name = 'ai_health' AND config_key = 'degraded' LIMIT 1");
$check->execute();
$row = $check->fetchColumn();
if ($row) {
    echo "SKIP ai_health.degraded (exists id={$row})\n";
    exit(0);
}
$stmt = $pdo->prepare(
    "INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, config_type, group_name, description, is_secret)
     VALUES (1, 'ai_health', 'degraded', '0', 'boolean', 'ai', 'Written by cron_ai_health.php. 1 = AI engines degraded; 0 = healthy. Public read-only via /api/ai/health.', 0)"
);
$stmt->execute();
echo "SEEDED ai_health.degraded id=" . $pdo->lastInsertId() . "\n";
