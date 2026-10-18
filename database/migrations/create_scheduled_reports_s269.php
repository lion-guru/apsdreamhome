<?php

$pdo = new PDO("mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS scheduled_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    title VARCHAR(255) NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    frequency VARCHAR(20) NOT NULL DEFAULT 'weekly',
    format VARCHAR(20) NOT NULL DEFAULT 'csv',
    parameters TEXT NULL,
    recipients TEXT NULL,
    day_of_week VARCHAR(20) NULL,
    day_of_month VARCHAR(10) NULL,
    next_run DATETIME NULL,
    last_run DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status_next (tenant_id, status, next_run)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$exists = $pdo->query("SELECT COUNT(*) FROM scheduled_reports")->fetchColumn();
echo "OK scheduled_reports rows=" . $exists . "\n";
echo "DONE\n";
