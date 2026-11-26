<?php
/**
 * S782: idempotent migration for the external-certification registry that the
 * admin/training/certifications create/edit forms already post to.
 *
 * Live training_certifications / training_certificates are course/associate
 * based and carry none of issuing_body / credential_id / credential_url, so
 * those forms had no backing store and their controller called two ghost
 * methods (500). This table matches the form contract exactly.
 */
$root = dirname(__DIR__, 2);
require_once $root . '/config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$name = 'employee_certifications';

$exists = $db->fetch("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$name]);
if ((int)($exists['c'] ?? 0) > 0) {
    echo "SKIP: $name already exists\n";
    exit(0);
}

$db->execute("CREATE TABLE `$name` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
    `employee_id` bigint(20) unsigned NOT NULL,
    `certification_name` varchar(200) NOT NULL,
    `issuing_body` varchar(200) DEFAULT NULL,
    `issue_date` date NOT NULL,
    `expiry_date` date DEFAULT NULL,
    `credential_id` varchar(128) DEFAULT NULL,
    `credential_url` varchar(500) DEFAULT NULL,
    `status` enum('active','expired','revoked') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_emp_cert_employee` (`employee_id`),
    KEY `idx_emp_cert_tenant` (`tenant_id`),
    KEY `idx_emp_cert_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$check = $db->fetch("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$name]);
echo "CREATED: $name (verify=" . (int)($check['c'] ?? 0) . ")\n";
