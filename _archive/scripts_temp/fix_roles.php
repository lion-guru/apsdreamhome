<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

echo "=== ALL USER ROLES ===\n";
$rows = $db->fetchAll("SELECT DISTINCT role FROM users ORDER BY role");
foreach($rows as $r) echo $r['role'] . "\n";

echo "\n=== MISSING TEST USERS ===\n";
// We need test users for: cto, cfo, director (C-suite roles with RBAC permissions)
$needed = ['cto', 'cfo', 'director'];
foreach ($needed as $role) {
    $exists = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role=?", [$role]);
    echo $role . ': ' . ($exists > 0 ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n=== SET ALL PASSWORDS TO Aps@2026 ===\n";
$hash = password_hash('Aps@2026', PASSWORD_DEFAULT);
$countBefore = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status='active'");
$db->query("UPDATE users SET password=? WHERE status='active'", [$hash]);
echo "Updated all active users: $countBefore rows\n";

echo "\n=== CREATE C-SUITE TEST USERS ===\n";
$cSuite = [
    ['CTO User', 'cto@apsdreamhome.com', 'cto', 'Technology'],
    ['CFO User', 'cfo@apsdreamhome.com', 'cfo', 'Finance'],
    ['Director User', 'director@apsdreamhome.com', 'director', 'Operations'],
];
foreach ($cSuite as $u) {
    $check = $db->fetchColumn("SELECT COUNT(*) FROM users WHERE email=?", [$u[1]]);
    if ($check == 0) {
        $db->query(
            "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())",
            [$u[0], $u[1], $hash, $u[2]]
        );
        $userId = $db->getPdo()->lastInsertId();
        
        // Create employee record
        $db->query(
            "INSERT INTO employees (user_id, name, email, designation, department, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())",
            [$userId, $u[0], $u[1], strtoupper($u[2]), $u[3]]
        );
        
        echo "Created: " . $u[1] . " (role=" . $u[2] . ", id=$userId)\n";
    } else {
        echo "Already exists: " . $u[1] . "\n";
    }
}

echo "\n=== CREATE MISSING ROLE PERMISSIONS FOR TELECALLER ===\n";
// Telecaller needs CRUD for leads only
$telecallerMenu = $db->fetchAll("SELECT id FROM admin_menu_items WHERE name IN ('Leads Manager', 'Lead Kanban') AND is_active=1");
foreach ($telecallerMenu as $m) {
    $exists = $db->fetchColumn("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role='telecaller' AND menu_item_id=?", [$m['id']]);
    if ($exists == 0) {
        $db->query(
            "INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES ('telecaller', ?, 1, 1, 1, 0)",
            [$m['id']]
        );
        echo "Added telecaller CRUD for menu " . $m['id'] . "\n";
    }
}

echo "\nDone!\n";?>