<?php
/**
 * Deep Project Scan - Find missing Controllers, Views, Models
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "=== DEEP PROJECT SCAN ===\n\n";

$routesFile = file_get_contents(__DIR__ . '/../routes/web.php');
preg_match_all('/\$router->get\(\'([^(\']+)\',\s*\'([^\']+)\'/', $routesFile, $matches);

$routes = [];
foreach ($matches[1] as $i => $route) {
    $handler = $matches[2][$i];
    $routes[$route] = $handler;
}

echo "Found " . count($routes) . " routes in web.php\n\n";

// Find all controllers
$controllerFiles = glob(__DIR__ . '/../app/Http/Controllers/**/*.php');
$controllers = [];
foreach ($controllerFiles as $file) {
    $name = basename($file, '.php');
    $controllers[$name] = $file;
}
echo "Found " . count($controllers) . " controller files\n\n";

// Find all views
$viewFiles = glob(__DIR__ . '/../app/views/admin/**/*.php');
$views = [];
foreach ($viewFiles as $file) {
    $name = basename($file, '.php');
    $views[$name] = $file;
}
echo "Found " . count($views) . " view files\n\n";

echo "=== CHECKING ADMIN ROUTES ===\n\n";

$missingControllers = [];
$missingViews = [];
$workingRoutes = [];

foreach ($routes as $route => $handler) {
    if (strpos($route, '/admin/') !== 0) continue;
    
    // Extract controller name
    $parts = explode('@', $handler);
    $controllerName = str_replace('App\\Http\\Controllers\\Admin\\', '', $parts[0]);
    $method = $parts[1] ?? 'index';
    
    // Check if controller exists
    if (!isset($controllers[$controllerName])) {
        $missingControllers[$route] = $controllerName;
    } else {
        // Controller exists, check view
        $viewName = strtolower(str_replace('Controller', '', $controllerName));
        // Simple view name mapping
        if (!isset($views[$viewName])) {
            // Try alternate names
            $alternates = [
                $viewName . '/index',
                'admin/' . $viewName,
                str_replace('-', '_', $viewName)
            ];
            $found = false;
            foreach ($alternates as $alt) {
                if (isset($views[$alt])) { $found = true; break; }
            }
            if (!$found) {
                $missingViews[$route] = $viewName;
            }
        }
        $workingRoutes[] = $route;
    }
}

echo "✅ WORKING ADMIN ROUTES (" . count($workingRoutes) . "):\n";
foreach (array_slice($workingRoutes, 0, 30) as $r) echo "   - $r\n";
if (count($workingRoutes) > 30) echo "   ... and " . (count($workingRoutes) - 30) . " more\n";

echo "\n❌ MISSING CONTROLLERS:\n";
foreach ($missingControllers as $route => $ctrl) {
    echo "   - $route => $ctrl\n";
}

echo "\n⚠️  POTENTIALLY MISSING VIEWS:\n";
foreach (array_slice($missingViews, 0, 20) as $route => $view) {
    echo "   - $route => $view\n";
}

// Get all database tables
echo "\n=== DATABASE TABLES ===\n";
$tables = $db->fetchAll("SHOW TABLES");
echo "Total tables: " . count($tables) . "\n";
$tableNames = array_values(array_map(function($t) { return array_values($t)[0]; }, $tables));
sort($tableNames);
foreach ($tableNames as $t) {
    echo "   - $t\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Create missing controllers:\n";
foreach (array_unique(array_values($missingControllers)) as $ctrl) {
    echo "   - $ctrl.php\n";
}

echo "\n2. Create missing views (sample):\n";
$viewSamples = array_slice($missingViews, 0, 10);
foreach ($viewSamples as $route => $view) {
    $viewPath = __DIR__ . "/../app/views/admin/$view/index.php";
    echo "   - app/views/admin/$view/index.php\n";
}