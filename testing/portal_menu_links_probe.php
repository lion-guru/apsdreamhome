<?php
/**
 * Portal Menu Links Probe (read-only, zero DB writes)
 *
 * Guards S217 lesson 472: every URL in PortalMenuService sidebars
 * (customer/associate/agent/employee) MUST resolve to a route registered
 * in routes/web.php. A menu entry pointing nowhere renders a 404 —
 * this probe fails loudly before users find out.
 *
 * Usage: php testing/portal_menu_links_probe.php
 * Exit 0 = all menu URLs resolve, 1 = failures (listed).
 */
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

// 1. Collect all registered route paths from web.php (literals + patterns).
$web = file_get_contents(APS_ROOT . '/routes/web.php');
$registered = [];
foreach (['get', 'post', 'put', 'delete', 'patch', 'any'] as $verb) {
    if (preg_match_all("/\\$" . "router->$verb\\s*\\(\\s*'([^']+)'/", $web, $m)) {
        foreach ($m[1] as $p) $registered[$p] = true;
    }
}

// A menu URL resolves if it exactly matches a route, or matches a
// parameterized route pattern (e.g. /user/bookings matches
// /user/bookings/{id}).
function resolves(string $url, array $registered): bool {
    if (isset($registered[$url])) return true;
    $url = rtrim($url, '/');
    if ($url === '') $url = '/';
    foreach ($registered as $pattern => $_) {
        $rx = '#^';
        foreach (preg_split('#(\\{[^}]+\\})#', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
            $rx .= preg_match('#^\\{[^}]+\\}$#', $part) ? '[^/]+' : preg_quote($part, '#');
        }
        $rx .= '$#';
        if (preg_match($rx, $url)) return true;
    }
    return false;
}

$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$failures = [];
$checked = 0;

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

echo "Portal menu links: $checked checked, " . count($failures) . " broken\n";
foreach ($failures as $f) echo "  - $f\n";
exit($failures ? 1 : 0);
