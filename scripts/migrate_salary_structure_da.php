<?php
// Idempotent: add DA (dearness allowance) component to canonical salary_structures.
require_once __DIR__ . '/../config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getPdo();
$exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='salary_structures' AND COLUMN_NAME='da'")->fetchColumn();
if (!$exists) {
    $pdo->exec("ALTER TABLE salary_structures ADD COLUMN da DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER hra");
    echo "added da column\n";
} else {
    echo "da column already present\n";
}
