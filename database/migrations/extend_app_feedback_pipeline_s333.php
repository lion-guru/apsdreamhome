<?php
require_once 'config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$columns = [
    'source_type' => "ALTER TABLE app_feedback ADD COLUMN source_type VARCHAR(50) NULL AFTER feedback_type",
    'source_id' => "ALTER TABLE app_feedback ADD COLUMN source_id INT UNSIGNED NULL AFTER source_type",
    'survey_token' => "ALTER TABLE app_feedback ADD COLUMN survey_token VARCHAR(64) NULL AFTER screenshot_path",
    'channel' => "ALTER TABLE app_feedback ADD COLUMN channel VARCHAR(30) NULL AFTER survey_token",
    'sent_at' => "ALTER TABLE app_feedback ADD COLUMN sent_at DATETIME NULL AFTER channel",
    'responded_at' => "ALTER TABLE app_feedback ADD COLUMN responded_at DATETIME NULL AFTER sent_at",
];
foreach ($columns as $col => $sql) {
    $exists = $pdo->query("SHOW COLUMNS FROM app_feedback LIKE '$col'")->fetchColumn();
    if (!$exists) {
        $pdo->exec($sql);
        echo "ADDED $col\n";
    } else {
        echo "SKIP $col\n";
    }
}
$idx = $pdo->query("SHOW INDEX FROM app_feedback WHERE Key_name = 'idx_survey_token'")->fetchColumn();
if (!$idx) {
    $pdo->exec("ALTER TABLE app_feedback ADD UNIQUE KEY idx_survey_token (survey_token)");
    echo "ADDED idx_survey_token\n";
} else {
    echo "SKIP idx_survey_token\n";
}
$exists = $pdo->query("SELECT id FROM admin_menu_items WHERE url = '/admin/feedback-pipeline' LIMIT 1")->fetchColumn();
if (!$exists) {
    $pdo->exec("INSERT INTO admin_menu_items (tenant_id, name, url, icon, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, 'Feedback Pipeline', '/admin/feedback-pipeline', 'fas fa-comments', 7, 'crm', 95, 'crm.view', 1, NOW(), NOW())");
    $menuId = (int)$pdo->lastInsertId();
    foreach (['admin','super_admin','manager'] as $role) {
        $pdo->exec("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at) VALUES (1, '$role', $menuId, 1, 1, 1, 1, NOW(), NOW())");
    }
    echo "SEEDED menu id=$menuId\n";
} else {
    echo "SKIP menu\n";
}
