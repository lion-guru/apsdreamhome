<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Employee section menu item IDs (verified from DB)
$items = [
    // Common items (all employees)
    'dashboard'   => 136,
    'tasks'       => 137,
    'attendance'  => 138,
    'leaves'      => 139,
    'payroll'     => 140,
    'performance' => 141,
    'documents'   => 142,
    'profile'     => 143,
    'settings'    => 144,
    'logout'      => 145,
    // Finance
    'finance_dashboard'  => 198,
    'reports'            => 199,
    'tax'                => 200,
    // Sales
    'sales_dashboard'    => 201,
    'leads'              => 202,
    'deals'              => 203,
    // HR
    'hr_dashboard'       => 204,
    'employees'          => 205,
    'recruitment'        => 206,
    // IT
    'it_dashboard'       => 207,
    'infrastructure'     => 208,
    // Legal
    'legal_dashboard'    => 209,
    'compliance'         => 210,
    // Land
    'land_dashboard'     => 211,
    'surveys'            => 212,
    // Construction
    'construction_dashboard' => 213,
    'projects'           => 214,
    'quality'            => 215,
    // Marketing
    'marketing_dashboard'=> 216,
    'campaigns'          => 217,
    // Operations
    'ops_dashboard'      => 218,
    'vendors'            => 219,
    // Customer Success
    'cs_dashboard'       => 220,
    'complaints'         => 221,
];

$common = array_values(array_slice($items, 0, 10)); // IDs 136-145

// ALL department-specific item IDs
$allDeptIds = array_values(array_slice($items, 10)); // IDs 198-221

// Sub-role => department-specific menu keys
$roleMenus = [
    // Generic fallback (no matching designation)
    'employee' => [],
    
    // Finance
    'employee_finance_manager'  => ['finance_dashboard','reports','tax'],
    'employee_finance_executive' => ['reports','tax'],
    
    // Sales
    'employee_sales_manager'    => ['sales_dashboard','leads','deals'],
    'employee_sales_executive'  => ['leads'],
    
    // HR
    'employee_hr_manager'       => ['hr_dashboard','employees','recruitment'],
    'employee_hr_executive'     => ['employees'],
    
    // IT
    'employee_it_manager'       => ['it_dashboard','infrastructure'],
    'employee_it_executive'     => ['infrastructure'],
    
    // Legal
    'employee_legal_advisor'    => ['legal_dashboard','compliance'],
    'employee_legal_executive'  => ['compliance'],
    
    // Land
    'employee_land_manager'     => ['land_dashboard','surveys'],
    'employee_land_executive'   => ['surveys'],
    
    // Construction
    'employee_project_manager'  => ['construction_dashboard','projects','quality'],
    'employee_site_engineer'    => ['quality'],
    
    // Marketing
    'employee_marketing_manager'   => ['marketing_dashboard','campaigns'],
    'employee_marketing_executive' => ['campaigns'],
    
    // Operations
    'employee_ops_manager'     => ['ops_dashboard','vendors'],
    'employee_ops_executive'   => ['vendors'],
    
    // Customer Success
    'employee_cs_manager'      => ['cs_dashboard','complaints'],
    'employee_cs_executive'    => ['complaints'],
    
    // Telecalling
    'employee_telecaller'      => ['leads'],
    'employee_telecaller_lead' => ['leads','deals'],
    
    // Director (sees everything)
    'director' => array_keys(array_filter($items, fn($k) => !in_array($k, ['dashboard','tasks','attendance','leaves','payroll','performance','documents','profile','settings','logout']), ARRAY_FILTER_USE_KEY)),
];

echo "=== PHASE 1: Remove ALL employee section permissions ===\n";
$stmt = $pdo->prepare("
    DELETE rp FROM admin_role_menu_permissions rp
    INNER JOIN admin_menu_items mi ON rp.menu_item_id = mi.id
    WHERE mi.section = 'employee'
");
$stmt->execute();
echo "  Deleted all employee section permissions\n";

echo "\n=== PHASE 2: Insert correct role-based permissions ===\n";

$insertStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_edit, can_delete, created_at)
    VALUES (?, ?, 1, 0, 0, NOW())
");

$totalInserted = 0;

foreach ($roleMenus as $role => $deptMenus) {
    // Merge common + department-specific IDs
    $deptIds = array_map(fn($k) => $items[$k], $deptMenus);
    $allIds = array_unique(array_merge($common, $deptIds));
    
    $count = 0;
    foreach ($allIds as $id) {
        $insertStmt->execute([$role, $id]);
        $count++;
        $totalInserted++;
    }
    $menuNames = array_map(fn($k) => $items[$k] ?? $k, $deptMenus);
    echo "  {$role}: {$count} items (common=10 + dept=" . count($deptMenus) . ")\n";
}

// Also give admin/super_admin FULL access to employee section
foreach (['admin', 'super_admin'] as $role) {
    foreach ($items as $key => $id) {
        $insertStmt->execute([$role, $id]);
        $totalInserted++;
    }
    echo "  {$role}: 34 items (full access)\n";
}

echo "\nTotal permissions inserted: {$totalInserted}\n";

echo "\n=== PHASE 3: Verify ===\n";
$verify = $pdo->query("
    SELECT rp.role, COUNT(*) as cnt 
    FROM admin_role_menu_permissions rp 
    INNER JOIN admin_menu_items mi ON rp.menu_item_id = mi.id 
    WHERE mi.section = 'employee' 
    GROUP BY rp.role 
    ORDER BY rp.role
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($verify as $r) {
    echo "  {$r['role']}: {$r['cnt']} items\n";
}

echo "\n=== PHASE 4: Verify 'employee' role items (should be 10 common only) ===\n";
$empItems = $pdo->query("
    SELECT mi.id, mi.name FROM admin_role_menu_permissions rp
    INNER JOIN admin_menu_items mi ON rp.menu_item_id = mi.id
    WHERE rp.role = 'employee' AND mi.section = 'employee'
    ORDER BY mi.order_index
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($empItems as $e) {
    echo "  [{$e['id']}] {$e['name']}\n";
}

echo "\n=== PHASE 5: Verify 'employee_hr_manager' items (should be 13 = 10 common + HR dept) ===\n";
$hrItems = $pdo->query("
    SELECT mi.id, mi.name FROM admin_role_menu_permissions rp
    INNER JOIN admin_menu_items mi ON rp.menu_item_id = mi.id
    WHERE rp.role = 'employee_hr_manager' AND mi.section = 'employee'
    ORDER BY mi.order_index
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($hrItems as $e) {
    echo "  [{$e['id']}] {$e['name']}\n";
}

echo "\nDONE! Employee RBAC permissions fixed.\n";?>