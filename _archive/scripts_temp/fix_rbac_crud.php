<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

// Fix 1: Grant full CRUD to super_admin and admin for all menu items they have access to
echo "=== Fixing super_admin CRUD permissions ===\n";
$db->query("UPDATE admin_role_menu_permissions SET can_create=1, can_edit=1, can_delete=1 WHERE role='super_admin'");
echo "super_admin: done\n";

echo "=== Fixing admin CRUD permissions ===\n";
$db->query("UPDATE admin_role_menu_permissions SET can_create=1, can_edit=1, can_delete=1 WHERE role='admin'");
echo "admin: done\n";

// Fix 2: Grant limited CRUD to manager (create + edit, no delete)
echo "=== Fixing manager CRUD permissions ===\n";
$db->query("UPDATE admin_role_menu_permissions SET can_create=1, can_edit=1 WHERE role='manager'");
echo "manager: done\n";

// Fix 3: Grant limited CRUD to C-suite roles
$csuiteRoles = ['ceo','cfo','coo','cto','cmo','chro','director'];
foreach ($csuiteRoles as $r) {
    $db->query("UPDATE admin_role_menu_permissions SET can_create=1, can_edit=1 WHERE role=?", [$r]);
    echo "$r: done\n";
}

// Verify
echo "\n=== VERIFICATION ===\n";
$rows = $db->fetchAll(
    "SELECT rp.role, SUM(CASE WHEN rp.can_create=1 THEN 1 ELSE 0 END) as create_count,
            SUM(CASE WHEN rp.can_edit=1 THEN 1 ELSE 0 END) as edit_count,
            SUM(CASE WHEN rp.can_delete=1 THEN 1 ELSE 0 END) as delete_count,
            COUNT(*) as total
     FROM admin_role_menu_permissions rp
     GROUP BY rp.role ORDER BY rp.role"
);
foreach($rows as $r) {
    echo $r['role'] . ': total=' . $r['total'] . ' create=' . $r['create_count'] . ' edit=' . $r['edit_count'] . ' delete=' . $r['delete_count'] . "\n";
}

echo "\nDone!\n";