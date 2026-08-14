<?php
/**
 * RBAC Seed: Populate admin_role_menu_permissions with roleâ†’menu access defaults
 * 
 * Logic:
 * - super_admin / admin: FULL access to all 135 items (no rows needed â€” AdminMenuService bypasses)
 * - manager: access to most items except system/security settings
 * - employee: limited to operational sections (backoffice, operations)
 * - associate: MLM + limited leads
 * - agent: leads + properties + sales
 * - customer: minimal admin access (none by default)
 * - telecaller: telecalling-specific sections only
 * 
 * Run: php scripts/seed_rbac_permissions.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== RBAC PERMISSION SEEDER ===\n\n";

    // Fetch all menu items
    $items = $pdo->query("SELECT id, name, section, url FROM admin_menu_items WHERE is_active = 1 ORDER BY order_index")->fetchAll(PDO::FETCH_ASSOC);
    echo "  Menu items: " . count($items) . "\n\n";

    // Define per-role section permissions: section => [can_view, can_create, can_edit, can_delete]
    $roleSectionPerms = [
        'manager' => [
            'dashboards'    => [1, 0, 0, 0],
            'users'         => [1, 1, 1, 0],
            'projects'      => [1, 1, 1, 1],
            'properties'    => [1, 1, 1, 1],
            'colonies'      => [1, 1, 1, 1],
            'mlm'           => [1, 1, 1, 1],
            'leads'         => [1, 1, 1, 1],
            'crm'           => [1, 1, 1, 1],
            'sales'         => [1, 1, 1, 1],
            'finance'       => [1, 1, 1, 0],
            'operations'    => [1, 1, 1, 0],
            'marketing'     => [1, 1, 1, 1],
            'reports'       => [1, 0, 0, 0],
            'ai'            => [1, 0, 0, 0],
            'content'       => [1, 1, 1, 1],
            'settings'      => [1, 0, 0, 0],
            'cms'           => [1, 1, 1, 1],
            'hrm'           => [1, 1, 1, 0],
            'business'      => [1, 1, 1, 0],
            'performance'   => [1, 0, 0, 0],
            'legal'         => [1, 0, 0, 0],
        ],
        'employee' => [
            'dashboards'    => [1, 0, 0, 0],
            'operations'    => [1, 1, 0, 0],
            'leads'         => [1, 1, 0, 0],
            'crm'           => [1, 0, 0, 0],
            'reports'       => [1, 0, 0, 0],
        ],
        'associate' => [
            'dashboards'    => [1, 0, 0, 0],
            'mlm'           => [1, 1, 0, 0],
            'leads'         => [1, 1, 0, 0],
            'properties'    => [1, 0, 0, 0],
            'reports'       => [1, 0, 0, 0],
        ],
        'agent' => [
            'dashboards'    => [1, 0, 0, 0],
            'leads'         => [1, 1, 1, 0],
            'properties'    => [1, 1, 1, 0],
            'sales'         => [1, 1, 0, 0],
            'reports'       => [1, 0, 0, 0],
        ],
        'telecaller' => [
            'dashboards'    => [1, 0, 0, 0],
            'leads'         => [1, 1, 1, 0],
            'crm'           => [1, 1, 0, 0],
            'operations'    => [1, 0, 0, 0],
        ],
        'customer' => [
            'dashboards'    => [1, 0, 0, 0],
        ],
    ];

    // Clear existing non-super_admin/admin role permissions
    $pdo->exec("DELETE FROM admin_role_menu_permissions WHERE role NOT IN ('super_admin', 'admin')");
    echo "  Cleared existing role permissions (kept super_admin/admin)\n";

    $totalInserted = 0;

    foreach ($roleSectionPerms as $role => $sectionPerms) {
        $inserted = 0;

        foreach ($items as $item) {
            $section = $item['section'];

            if (isset($sectionPerms[$section])) {
                $perms = $sectionPerms[$section];
            } else {
                // Section not listed â†’ no access (skip)
                continue;
            }

            $stmt = $pdo->prepare("
                INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    can_view = VALUES(can_view),
                    can_create = VALUES(can_create),
                    can_edit = VALUES(can_edit),
                    can_delete = VALUES(can_delete)
            ");
            $stmt->execute([$role, $item['id'], $perms[0], $perms[1], $perms[2], $perms[3]]);
            $inserted++;
        }

        $totalInserted += $inserted;
        echo "  [OK] $role: $inserted menu items seeded\n";
    }

    echo "\n  Total permissions seeded: $totalInserted\n";

    // Verify
    $count = $pdo->query("SELECT COUNT(*) FROM admin_role_menu_permissions")->fetchColumn();
    echo "  Total in DB: $count rows\n";

    // Show per-role breakdown
    $r = $pdo->query("SELECT role, COUNT(*) as cnt FROM admin_role_menu_permissions GROUP BY role ORDER BY role");
    echo "\n  Role breakdown:\n";
    while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
        echo "    {$row['role']}: {$row['cnt']} items\n";
    }

    echo "\n=== SEED COMPLETE ===\n";

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
    exit(1);
}?>