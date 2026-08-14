<?php
/**
 * Create ai_chatbot_training table for AI chatbot training data
 * Run once: php scripts/create_ai_chatbot_training.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$sql = "CREATE TABLE IF NOT EXISTS ai_chatbot_training (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intent VARCHAR(100) NOT NULL,
    examples JSON NOT NULL,
    responses JSON NOT NULL,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_intent (intent),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

try {
    $db->query($sql);
    echo "ai_chatbot_training table created OK\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}?>