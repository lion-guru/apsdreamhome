<?php
/**
 * Idempotent migration: AI employee desk (named AI staff with task queue).
 * `ai_employees` = roster (role, status, workload); `ai_employee_tasks` = work queue.
 *
 * Re-run = SKIP.
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

$db = App\Core\Database\Database::getInstance();

$n = $db->fetchOne("SELECT COUNT(*) as n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_employees'");
if ((int) ($n['n'] ?? 0) === 0) {
    $db->execute("
        CREATE TABLE ai_employees (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            role VARCHAR(50) NOT NULL DEFAULT 'telecaller',
            status ENUM('active','paused','offline') NOT NULL DEFAULT 'paused',
            workload INT NOT NULL DEFAULT 0,
            scope JSON,
            last_active TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_name (name)
        )
    ");
    echo "ai_employees: CREATED\n";
} else {
    echo "ai_employees: SKIP (exists)\n";
}

$n = $db->fetchOne("SELECT COUNT(*) as n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ai_employee_tasks'");
if ((int) ($n['n'] ?? 0) === 0) {
    $db->execute("
        CREATE TABLE ai_employee_tasks (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id INT UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL,
            lead_id INT NULL,
            payload JSON,
            status ENUM('pending','claimed','done','failed','skipped') NOT NULL DEFAULT 'pending',
            result JSON,
            claimed_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id, status),
            INDEX idx_lead (lead_id)
        )
    ");
    echo "ai_employee_tasks: CREATED\n";
} else {
    echo "ai_employee_tasks: SKIP (exists)\n";
}

// Seed first AI employee (paused — flipped active by admin)
$db->execute("
    INSERT IGNORE INTO ai_employees (name, role, status, scope)
    VALUES ('Aira', 'telecaller', 'paused', JSON_OBJECT('max_leads_per_run', 5, 'channels', JSON_ARRAY('call_log', 'whatsapp_draft')))
");
$n = $db->fetchOne("SELECT COUNT(*) as n FROM ai_employees WHERE name = 'Aira'");
echo "Aira seeded: " . (int) ($n['n'] ?? 0) . "\n";

// Menu seed (admin sidebar)
$menuExists = $db->fetchOne("SELECT id FROM admin_menu_items WHERE url = '/admin/ai-employee'");
if (!$menuExists) {
    $db->execute("
        INSERT INTO admin_menu_items (name, icon, url, section, order_index, permission_key, is_active)
        VALUES ('AI Employee Desk', 'fas fa-robot', '/admin/ai-employee', 'system', 99, 'system.ai_employee', 1)
    ");
    echo "menu AI Employee Desk: SEEDED\n";
} else {
    echo "menu AI Employee Desk: SKIP (exists)\n";
}
exit(0);
