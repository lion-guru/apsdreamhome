<?php
require __DIR__ . '/../vendor/autoload.php';
$db = App\Core\Database\Database::getInstance();

echo "Creating crm_lead_scores_history table...\n";
$db->query("CREATE TABLE IF NOT EXISTS crm_lead_scores_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    old_score INT DEFAULT 0,
    new_score INT DEFAULT 0,
    score_change INT DEFAULT 0,
    reason VARCHAR(255) DEFAULT NULL,
    factors JSON DEFAULT NULL,
    scored_by VARCHAR(50) DEFAULT 'system',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead_id (lead_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK\n";

echo "Creating crm_lead_sources_extended table...\n";
$db->query("CREATE TABLE IF NOT EXISTS crm_lead_sources_extended (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    source_name VARCHAR(100) DEFAULT NULL,
    campaign_name VARCHAR(200) DEFAULT NULL,
    medium VARCHAR(100) DEFAULT NULL,
    utm_source VARCHAR(200) DEFAULT NULL,
    utm_medium VARCHAR(200) DEFAULT NULL,
    utm_campaign VARCHAR(200) DEFAULT NULL,
    utm_term VARCHAR(200) DEFAULT NULL,
    utm_content VARCHAR(200) DEFAULT NULL,
    referrer_url VARCHAR(500) DEFAULT NULL,
    landing_page VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead_id (lead_id),
    INDEX idx_source (source_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK\n";

echo "\nDone!\n";?>