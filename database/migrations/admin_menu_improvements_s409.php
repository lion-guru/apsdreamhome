<?php
/**
 * Admin Menu Improvements (S409)
 * - Hub grouping: employee/hrm/hr → hrm; township_ops plots/land → colonies; commission → mlm
 * - Messenger Routing parent menu (/admin/messenger)
 * - Telephony Health menu fix: point to dashboard page (JSON stays at same URL for compat + new API alias)
 * - Permission sync: ensure new menus have admin/super_admin perms
 *
 * Idempotent: safe to re-run.
 * Run: php database/migrations/admin_menu_improvements_s409.php
 */
require_once __DIR__ . '/../../config/bootstrap_cli.php';
use App\Core\Database\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

function ensureMenu(PDO $pdo, string $name, string $url, string $section, string $perm, string $icon) {
    $exists = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url=? LIMIT 1");
    $exists->execute([$url]);
    $row = $exists->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        // Update label/section if drifted, keep id
        $pdo->prepare("UPDATE admin_menu_items SET name=?, section=?, permission_key=?, icon=? WHERE url=?")
            ->execute([$name, $section, $perm, $icon, $url]);
        return (int)$row['id'];
    }
    $pdo->prepare("INSERT INTO admin_menu_items (name, url, section, permission_key, icon, order_index, is_active) VALUES (?, ?, ?, ?, ?, 999, 1)")
        ->execute([$name, $url, $section, $perm, $icon]);
    return (int)$pdo->lastInsertId();
}
function ensurePerm(PDO $pdo, int $menuId, string $role) {
    $exists = $pdo->prepare("SELECT 1 FROM admin_role_menu_permissions WHERE menu_item_id=? AND role=? LIMIT 1");
    $exists->execute([$menuId, $role]);
    if ($exists->fetch()) return;
    $pdo->prepare("INSERT INTO admin_role_menu_permissions (tenant_id, role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (1, ?, ?, 1, 1, 1, 0)")
        ->execute([$role, $menuId]);
}

echo "Running admin menu improvements S409...\n";

// 1. Hub grouping consolidation
$groupings = [
    // employee/hr → hrm
    "UPDATE admin_menu_items SET section='hrm' WHERE section IN ('employee','hr')",
    // township plots/land merged into colonies hub (township_ops utilities stay)
    "UPDATE admin_menu_items SET section='colonies' WHERE section IN ('plots','land')",
    // commission → mlm
    "UPDATE admin_menu_items SET section='mlm' WHERE section='commission'",
];
foreach ($groupings as $sql) {
    $n = $pdo->exec($sql);
    echo "  Grouping: $n rows updated\n";
}

// 2. Messenger Routing parent menu
$mid = ensureMenu($pdo, 'Messenger Routing', '/admin/messenger', 'system', 'system.messenger', 'fas fa-paper-plane');
ensurePerm($pdo, $mid, 'admin');
ensurePerm($pdo, $mid, 'super_admin');
echo "  Messenger menu id=$mid\n";

// 3. Telephony Health menu fix — point to dashboard page (JSON health stays at /admin/ai-calling/health for compat)
ensureMenu($pdo, 'Telephony Health', '/admin/ai-calling/dashboard', 'ai_tech', 'ai_calling', 'fas fa-phone-square-alt');
echo "  Telephony Health menu repointed to dashboard\n";

// Verify
$c = (int)$pdo->query("SELECT COUNT(*) as c FROM admin_menu_items WHERE is_active=1")->fetch()['c'];
echo "  Active menus: $c\n";
$secs = $pdo->query("SELECT DISTINCT section FROM admin_menu_items WHERE is_active=1 ORDER BY section")->fetchAll(PDO::FETCH_COLUMN);
echo "  Sections distinct: " . count($secs) . " (" . implode(',', $secs) . ")\n";
echo "Done.\n";
