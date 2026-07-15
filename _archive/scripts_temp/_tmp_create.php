<?php
require 'vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();
$sql = "CREATE TABLE IF NOT EXISTS calls_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NULL,
    user_id INT NULL,
    phone VARCHAR(30) NULL,
    name VARCHAR(120) NULL,
    action VARCHAR(60) NULL,
    outcome VARCHAR(60) NULL,
    method VARCHAR(30) NULL,
    duration INT NULL,
    notes TEXT NULL,
    ai_score INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$db->execute($sql);
echo "calls_log table created: " . ($db->fetch("SELECT COUNT(*) as c FROM calls_log") ? "OK" : "FAIL") . "\n";
