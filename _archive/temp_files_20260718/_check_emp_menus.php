<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// 1. Employee section menu items
echo "=== EMPLOYEE SECTION MENU ITEMS ===\n";
$rows = $pdo->query("SELECT id, name, url, section, order_index FROM admin_menu_items WHERE section = 'employee' AND is_active = 1 ORDER BY order_index")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  id={$r['id']} name={$r['name']} url={$r['url']}\n";

// 2. Role permissions
echo "\n=== EMPLOYEE ROLE PERMISSIONS (which role sees which menu) ===\n";
$rows2 = $pdo->query("SELECT rp.role, mi.name as menu_name, rp.can_view FROM admin_role_menu_permissions rp JOIN admin_menu_items mi ON rp.menu_item_id = mi.id WHERE mi.section = 'employee' ORDER BY rp.role, mi.order_index")->fetchAll(PDO::FETCH_ASSOC);
$byRole = [];
foreach ($rows2 as $r) $byRole[$r['role']][] = ($r['can_view'] ? '[Y]' : '[N]') . ' ' . $r['menu_name'];
foreach ($byRole as $role => $menus) {
    echo "  ROLE: $role (" . count($menus) . " items)\n";
    foreach ($menus as $m) echo "    $m\n";
}

// 3. Designation roles mapping
echo "\n=== EMPLOYEE_DESIGNATION_ROLES ===\n";
$rows3 = $pdo->query("SELECT designation, department, sub_role, dashboard_view FROM employee_designation_roles ORDER BY department, designation")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows3 as $r) echo "  desig={$r['designation']} dept={$r['department']} sub_role={$r['sub_role']} dash={$r['dashboard_view']}\n";

// 4. Actual employees
echo "\n=== EMPLOYEES TABLE ===\n";
$rows4 = $pdo->query("SELECT id, user_id, name, designation, department, status FROM employees ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows4 as $r) echo "  id={$r['id']} user_id={$r['user_id']} name={$r['name']} desig={$r['designation']} dept={$r['department']} status={$r['status']}\n";
