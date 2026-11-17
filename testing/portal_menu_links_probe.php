<?php
/**
 * Portal & Admin Menu Links Probe (read-only, zero DB writes)
 *
 * Guards S217 lesson 472: every URL in PortalMenuService sidebars
 * (customer/associate/agent/employee) and AdminMenuService sidebar
 * MUST resolve to a route registered across the application route files.
 * A menu entry pointing nowhere renders a 404 — this probe fails loudly
 * before users or admins find out.
 *
 * Usage: php testing/portal_menu_links_probe.php
 * Exit 0 = all menu URLs resolve, 1 = failures (listed).
 */
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

// 1. Collect all registered route paths from all active route files (literals + patterns).
$routeFiles = [
    APS_ROOT . '/routes/web.php',
    APS_ROOT . '/routes/land.php',
    APS_ROOT . '/routes/gap_builds.php',
    APS_ROOT . '/routes/ai.php',
    APS_ROOT . '/routes/employee.php',
];

$registered = [];
foreach ($routeFiles as $rf) {
    if (!file_exists($rf)) continue;
    $content = file_get_contents($rf);
    foreach (['get', 'post', 'put', 'delete', 'patch', 'any'] as $verb) {
        if (preg_match_all("/\\$" . "router->$verb\\s*\\(\\s*'([^']+)'/", $content, $m)) {
            foreach ($m[1] as $p) $registered[$p] = true;
        }
        if (preg_match_all('/\\$' . 'router->' . $verb . '\\s*\\(\\s*"([^"]+)"/', $content, $m)) {
            foreach ($m[1] as $p) $registered[$p] = true;
        }
    }
}

// A menu URL resolves if it exactly matches a route, or matches a
// parameterized route pattern (e.g. /user/bookings matches
// /user/bookings/{id}).
function resolves(string $url, array $registered): bool {
    $path = parse_url($url, PHP_URL_PATH) ?? $url;
    if (isset($registered[$path])) return true;
    $path = rtrim($path, '/');
    if ($path === '') $path = '/';
    foreach ($registered as $pattern => $_) {
        $rx = '#^';
        foreach (preg_split('#(\\{[^}]+\\})#', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
            $rx .= preg_match('#^\\{[^}]+\\}$#', $part) ? '[^/]+' : preg_quote($part, '#');
        }
        $rx .= '$#';
        if (preg_match($rx, $path)) return true;
    }
    return false;
}

$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$failures = [];
$checked = 0;

// 2. Check Customer / Associate / Agent / Employee portal menus
foreach (['customer', 'associate', 'agent', 'employee'] as $role) {
    $svc = new \App\Services\PortalMenuService($pdo, 1, $role);
    foreach ($svc->getMenu() as $section) {
        foreach ($section['items'] as $item) {
            $url = $item['url'] ?? '';
            // Skip non-app links (logout is handled by auth controllers;
            // external anchors carry their own scheme).
            if ($url === '' || preg_match('~^(https?://|#|mailto:|tel:)~', $url)) continue;
            $checked++;
            if (!resolves($url, $registered)) {
                $failures[] = "$role menu '{$item['label']}' → $url (no route)";
            }
        }
    }
}

// 3. Check Admin sidebar menu items
$adminMenuService = new \App\Services\AdminMenuService();
$adminItems = $adminMenuService->getMenuItems(\App\Http\Middleware\RBACManager::ROLE_SUPER_ADMIN);

$checkAdminTree = function ($items) use (&$checkAdminTree, $registered, &$checked, &$failures) {
    foreach ($items as $item) {
        $url = $item['url'] ?? '';
        if ($url !== '' && $url !== '#' && !preg_match('~^(https?://|mailto:|tel:)~', $url)) {
            $checked++;
            if (!resolves($url, $registered)) {
                $failures[] = "admin menu '{$item['name']}' (ID {$item['id']}) → $url (no route)";
            }
        }
        if (!empty($item['children']) && is_array($item['children'])) {
            $checkAdminTree($item['children']);
        }
    }
};
$checkAdminTree($adminItems);

echo "Portal & Admin menu links: $checked checked, " . count($failures) . " broken\n";
foreach ($failures as $f) echo "  - $f\n";
exit($failures ? 1 : 0);
