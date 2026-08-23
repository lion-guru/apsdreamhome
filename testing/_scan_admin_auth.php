<?php
declare(strict_types=1);
// Scan all /admin/* routes -> controller@method, detect missing auth checks
$web = file_get_contents(__DIR__ . '/../routes/web.php');
$routes = [];
foreach (['get', 'post'] as $verb) {
    preg_match_all("/\\\$router->{$verb}\('(\/admin[^']*)',\s*'([^']+)'/", $web, $m);
    foreach ($m[1] as $i => $url) {
        $routes[] = ['verb' => strtoupper($verb), 'url' => $url, 'handler' => $m[2][$i]];
    }
}

$byCtrl = [];
foreach ($routes as $r) {
    if (!str_contains($r['handler'], '@')) continue;
    [$cls, $meth] = explode('@', $r['handler'], 2);
    $cls = str_replace('\\\\', '\\', $cls); // normalize escaped backslashes from source
    $byCtrl[$cls][] = $r + ['method' => $meth];
}

$unprotected = [];
$protected = 0;
$problems = [];

function resolveControllerFile(string $cls): ?string {
    $base = __DIR__ . '/../app/Http/Controllers/';
    $map = [
        'App\\Http\\Controllers\\' => '',
        'Admin\\' => 'Admin/', 'Front\\' => 'Front/', 'Auth\\' => 'Auth/',
        'Employee\\' => 'Employee/', 'MLM\\' => 'MLM/', 'AI\\' => 'AI/',
        'Api\\' => 'Api/', 'Property\\' => 'Property/', 'Tech\\' => 'Tech/',
        'Business\\' => 'Business/', 'Reports\\' => 'Reports/', 'Utility\\' => 'Utility/',
        'Career\\' => 'Career/', 'Communication\\' => 'Communication/',
        'Marketing\\' => 'Marketing/', 'Media\\' => 'Media/', 'Payment\\' => 'Payment/',
        'Analytics\\' => 'Analytics/', 'Async\\' => 'Async/', 'User\\' => 'User/',
        'Notification\\' => 'Notification/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($cls, $prefix)) {
            $rel = substr($cls, strlen($prefix));
            $f = $base . $dir . str_replace('\\', '/', $rel) . '.php';
            return file_exists($f) ? $f : null;
        }
    }
    // bare class name -> Controllers root or Admin/
    foreach ([$base . $cls . '.php', $base . 'Admin/' . $cls . '.php'] as $f) {
        if (file_exists($f)) return $f;
    }
    return null;
}

foreach ($byCtrl as $cls => $ctrlRoutes) {
    $file = resolveControllerFile($cls);
    if ($file === null) { $problems[] = "FILE MISSING: $cls"; continue; }
    $src = file_get_contents($file);
    // Check if constructor enforces auth (covers all methods)
    $ctorAuth = false;
    if (preg_match('/function\s+__construct\s*\([^)]*\)\s*\{/', $src, $cm, PREG_OFFSET_CAPTURE)) {
        $ctorBody = substr($src, $cm[0][1], 2000);
        $ctorAuth = (bool)preg_match(
            '/requireAdmin|requirePermission|checkMenuPermission|enforceAdmin|requireRole|authenticateAdmin|verifyAdminAccess|isLoggedIn/i',
            substr($ctorBody, 0, 1500)
        );
    }
    foreach ($ctrlRoutes as $r) {
        if ($ctorAuth) { $protected++; continue; }
        if (!preg_match('/function\s+' . preg_quote($r['method'], '/') . '\s*\(/', $src)) {
            $problems[] = "METHOD MISSING: {$cls}@{$r['method']} ({$r['verb']} {$r['url']})";
            continue;
        }
        preg_match('/function\s+' . preg_quote($r['method'], '/') . '\s*\([^)]*\)\s*[{:]?/', $src, $mm, PREG_OFFSET_CAPTURE);
        if (!$mm) { $problems[] = "LOCATE FAIL: {$cls}@{$r['method']}"; continue; }
        $body = substr($src, $mm[0][1], 2500);
        $hasAuth = (bool)preg_match(
            '/requireAdmin|requirePermission|checkMenuPermission|enforceAdmin|requireRole|authenticateAdmin|verifyAdminAccess|isLoggedIn|admin_id\s*\?\?|AuthMiddleware/i',
            substr($body, 0, 1500)
        );
        if ($hasAuth) { $protected++; }
        else { $unprotected[] = "{$r['verb']} {$r['url']}  =>  {$cls}@{$r['method']}"; }
    }
}

echo "TOTAL admin routes scanned: " . count($routes) . "\n";
echo "WITH auth check: $protected\n";
echo "WITHOUT auth check: " . count(array_unique($unprotected)) . "\n\n";
echo "=== UNPROTECTED ADMIN ROUTES ===\n";
foreach (array_unique($unprotected) as $u) echo "$u\n";
echo "\n=== BROKEN REFERENCES ===\n";
foreach (array_unique($problems) as $p) echo "$p\n";
