<?php
$lines = file('C:/xampp/htdocs/apsdreamhome/routes/web.php');
$adminRouteUrls = [];
$count = 0;
foreach ($lines as $i => $line) {
    if (preg_match('#/admin/[a-z0-9_-]+#i', $line, $m)) {
        $count++;
        // Extract the URL path
        if (preg_match("#['\"](/admin/[a-z0-9_/-]+)['\"]#i", $line, $u)) {
            $url = rtrim($u[1], '/');
            $adminRouteUrls[$url] = true;
        } elseif (preg_match('#/admin/[a-z0-9_/-]+#i', $line, $u2)) {
            $url = rtrim($u2[0], '/');
            $adminRouteUrls[$url] = true;
        }
    }
}
echo "Lines matching /admin/*: $count\n";
echo "Unique admin route URLs extracted: " . count($adminRouteUrls) . "\n\n";
ksort($adminRouteUrls);
echo implode("\n", array_keys($adminRouteUrls)) . "\n";?>