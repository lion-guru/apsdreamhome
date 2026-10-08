<?php
// Idempotent: allow 'salaried' in associates.agent_type (HR salary structures).
require_once __DIR__ . '/../config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getPdo();
$type = $pdo->query("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='associates' AND COLUMN_NAME='agent_type'")->fetchColumn();
if (stripos((string)$type, "'salaried'") === false) {
    $pdo->exec("ALTER TABLE associates MODIFY COLUMN agent_type ENUM('mlm_company','freelancer','independent','salaried') NOT NULL DEFAULT 'freelancer'");
    echo "agent_type enum extended\n";
} else {
    echo "agent_type already includes salaried\n";
}
