<?php
require __DIR__ . '/routes/router.php';
$router = new Router();
require __DIR__ . '/routes/web.php';
$routes = $router->getRoutes();
echo "GET routes: " . count($routes['GET'] ?? []) . "\n";
echo "Has /api/security/score: " . (isset($routes['GET']['/api/security/score']) ? 'YES' : 'NO') . "\n";
echo "Has /api/container/test/functionality: " . (isset($routes['GET']['/api/container/test/functionality']) ? 'YES' : 'NO') . "\n";
echo "Has /api/performance-cache/stats: " . (isset($routes['GET']['/api/performance-cache/stats']) ? 'YES' : 'NO') . "\n";
echo "Has /api/events/history: " . (isset($routes['GET']['/api/events/history']) ? 'YES' : 'NO') . "\n";
echo "Has /api/farmers/search: " . (isset($routes['GET']['/api/farmers/search']) ? 'YES' : 'NO') . "\n";

// Simulate dispatch to /api/security/score
echo "\n--- Check all controllers for signature conflicts ---\n";
$dir = __DIR__ . '/app/Http/Controllers';
$files = glob($dir . '/*.php');
$baseClass = new ReflectionClass('App\Http\Controllers\BaseController');
$baseMethods = [];
foreach ($baseClass->getMethods() as $m) {
    $baseMethods[$m->getName()] = $m->getNumberOfRequiredParameters();
}
// Check SecurityController
$sc = new ReflectionClass('App\Http\Controllers\SecurityController');
$conflicts = [];
foreach ($sc->getMethods() as $m) {
    $name = $m->getName();
    if (isset($baseMethods[$name]) && $m->getNumberOfRequiredParameters() !== $baseMethods[$name]) {
        $conflicts[] = $name . " (Security: {$m->getNumberOfRequiredParameters()} params, Base: {$baseMethods[$name]} params)";
    }
}
if ($conflicts) {
    echo "Signature conflicts in SecurityController:\n";
    foreach ($conflicts as $c) echo "  - $c\n";
} else {
    echo "SecurityController: No signature conflicts\n";
}

// Check RequestMiddlewareController
if (class_exists('App\Http\Controllers\RequestMiddlewareController')) {
    $rc = new ReflectionClass('App\Http\Controllers\RequestMiddlewareController');
    $conflicts = [];
    foreach ($rc->getMethods() as $m) {
        $name = $m->getName();
        if (isset($baseMethods[$name]) && $m->getNumberOfRequiredParameters() !== $baseMethods[$name]) {
            $conflicts[] = $name . " (ReqMiddleware: {$m->getNumberOfRequiredParameters()} params, Base: {$baseMethods[$name]} params)";
        }
    }
    if ($conflicts) {
        echo "Signature conflicts in RequestMiddlewareController:\n";
        foreach ($conflicts as $c) echo "  - $c\n";
    } else {
        echo "RequestMiddlewareController: No signature conflicts\n";
    }
}
