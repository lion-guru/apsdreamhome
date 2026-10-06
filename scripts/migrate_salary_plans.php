<?php
// Idempotent: salary_plans table backing admin/salary/plans (page+store+update exist, table was missing).
require_once __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getPdo();
$exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='salary_plans'")->fetchColumn();
if (!$exists) {
    $pdo->exec("CREATE TABLE salary_plans (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        name VARCHAR(150) NOT NULL,
        description TEXT NULL,
        base_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
        bonus_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        benefits_json TEXT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "created salary_plans\n";
} else {
    echo "salary_plans already present\n";
}
