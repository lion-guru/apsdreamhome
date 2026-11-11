<?php
/**
 * Seed Dead Letter Queue menu (idempotent)
 * Session 370: Admin UI for messenger failed messages
 */
return function($pdo) {
    $stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = '/admin/messenger/dead-letters' LIMIT 1");
    $stmt->execute();
    if ($stmt->fetchColumn()) {
        echo "SKIP - Dead Letter Queue menu already exists\n";
        return;
    }
    $parentId = null;
    try {
        $row = $pdo->query("SELECT id FROM admin_menu_items WHERE parent_id IS NULL AND is_active = 1 ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $parentId = $row ? (int)$row['id'] : null;
    } catch (\Throwable $e) {}
    $stmt = $pdo->prepare("INSERT INTO admin_menu_items (tenant_id, name, icon, url, parent_id, section, order_index, permission_key, is_active, created_at, updated_at) VALUES (1, 'Dead Letter Queue', 'fas fa-envelope-open', '/admin/messenger/dead-letters', ?, 'system', 999, 'system.dead_letter', 1, NOW(), NOW())");
    $stmt->execute([$parentId]);
    $menuId = (int)$pdo->lastInsertId();
    echo "ADDED Dead Letter Queue menu id=$menuId\n";
    foreach (['admin','super_admin'] as $role) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, ?, ?, 1,1,1,1)");
        $stmt->execute([$role, $menuId]);
    }
};
