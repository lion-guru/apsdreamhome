<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

echo "=== MENU ITEMS PER ROLE ===\n";
$roles = ['admin','super_admin','manager','employee','associate','agent','customer','telecaller',
    'employee_finance_manager','employee_finance_executive','employee_sales_manager','employee_sales_executive',
    'employee_telecaller','employee_telecaller_lead','employee_marketing_manager','employee_marketing_executive',
    'employee_hr_manager','employee_hr_executive','employee_it_manager','employee_it_executive',
    'employee_land_manager','employee_land_executive','employee_legal_advisor','employee_legal_executive',
    'employee_cs_manager','employee_cs_executive','employee_ops_manager','employee_ops_executive',
    'employee_project_manager','employee_site_engineer','director','ceo','cfo','coo','cto','cmo','chro'];

foreach($roles as $role) {
    $count = $db->fetchColumn(
        "SELECT COUNT(*) FROM admin_role_menu_permissions rp 
         INNER JOIN admin_menu_items mi ON mi.id = rp.menu_item_id 
         WHERE rp.role = ? AND rp.can_view = 1 AND mi.is_active = 1",
        [$role]
    );
    echo "$role: $count menu items\n";
}

echo "\n=== ADMIN ROLE CRUD STATUS ===\n";
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

echo "\n=== SUPER_ADMIN CRUD ===\n";
$rows = $db->fetchAll(
    "SELECT mi.name, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete 
     FROM admin_role_menu_permissions rp
     INNER JOIN admin_menu_items mi ON mi.id = rp.menu_item_id
     WHERE rp.role = 'super_admin' AND mi.is_active = 1
     ORDER BY mi.order_index LIMIT 30"
);
foreach($rows as $r) {
    echo $r['name'] . ' | v=' . $r['can_view'] . ' c=' . $r['can_create'] . ' e=' . $r['can_edit'] . ' d=' . $r['can_delete'] . "\n";
}

echo "\n=== ADMIN MENU ITEMS COUNT BY SECTION ===\n";
$rows = $db->fetchAll("SELECT section, COUNT(*) as cnt FROM admin_menu_items WHERE is_active=1 GROUP BY section ORDER BY section");
foreach($rows as $r) {
    echo $r['section'] . ': ' . $r['cnt'] . "\n";
}?>