<?php
$host='127.0.0.1'; $port=3307; $db='apsdreamhome'; $user='root'; $pass='';
$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
$pdo->exec('CREATE TABLE IF NOT EXISTS crm_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value VARCHAR(500) NOT NULL DEFAULT "1",
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$defaults = [
    'crm_enabled' => '1',
    'crm_lead_create_roles' => 'admin,manager,associate,agent',
    'crm_lead_delete_roles' => 'admin,manager',
    'crm_auto_assign_enabled' => '1',
    'crm_auto_assign_method' => 'round_robin',
    'crm_scoring_enabled' => '1',
    'crm_scoring_hot_threshold' => '70',
    'crm_scoring_warm_threshold' => '40',
    'crm_drip_enabled' => '1',
    'crm_sla_enabled' => '1',
    'crm_sla_response_hours' => '24',
    'crm_trash_retention_days' => '30',
    'crm_export_enabled' => '1',
    'crm_import_enabled' => '1',
    'crm_kanban_enabled' => '1',
];

$stmt = $pdo->prepare("INSERT INTO crm_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
foreach ($defaults as $k => $v) {
    $stmt->execute([$k, $v]);
}

echo "crm_settings table created with " . count($defaults) . " default settings\n";
