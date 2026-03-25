<?php
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Autoloader.php';

$routes_file = file_get_contents('routes/web.php');
preg_match_all('/\$router->(get|post|put|delete|any)\(\'(.*?)\',\s*\'(.*?)\'\)/', $routes_file, $matches, PREG_SET_ORDER);

$duplicates = [];
$route_map = [];
$broken_controllers = [];
$broken_methods = [];

echo "=================================================\n";
echo "   MAXIMUM DEEP SCAN: ROUTE & WORKFLOW AUDIT     \n";
echo "=================================================\n\n";

echo "Total Routes Found: " . count($matches) . "\n\n";

foreach ($matches as $match) {
    $method = strtoupper($match[1]);
    $path = $match[2];
    $handler = $match[3];
    
    $key = "$method $path";
    if (isset($route_map[$key])) {
        $duplicates[$key][] = $handler;
    } else {
        $route_map[$key] = [$handler];
    }
    
    // Check handler
    if (strpos($handler, '@') !== false) {
        list($controller_rel, $method_name) = explode('@', $handler);
        
        // Normalize controller path
        if (strpos($controller_rel, 'App\\Http\\Controllers\\') === 0) {
            $controller = $controller_rel;
        } else {
            $controller = 'App\\Http\\Controllers\\' . ltrim($controller_rel, '\\');
        }
        
        $controller_file = str_replace(['App\\', '\\'], ['app/', '/'], $controller) . '.php';
        
        if (!file_exists($controller_file)) {
            $broken_controllers[] = "$key -> $handler (File missing: $controller_file)";
        } else {
            require_once $controller_file;
            if (!class_exists($controller)) {
                $broken_controllers[] = "$key -> $handler (Class missing: $controller)";
            } elseif (!method_exists($controller, $method_name)) {
                $broken_methods[] = "$key -> $handler (Method missing: $method_name in $controller)";
            }
        }
    }
}

// Add duplicates back
foreach ($duplicates as $key => $handlers) {
    $route_map[$key] = array_merge($route_map[$key], $handlers);
}

echo "--- DUPLICATE ROUTES (Conflicting paths) ---\n";
$dup_count = 0;
foreach ($route_map as $key => $handlers) {
    if (count($handlers) > 1) {
        $dup_count++;
        echo "- $key is mapped " . count($handlers) . " times:\n";
        foreach ($handlers as $h) {
            echo "  -> $h\n";
        }
    }
}
if ($dup_count == 0) echo "None found.\n";

echo "\n--- BROKEN CONTROLLERS (Missing File or Class) ---\n";
foreach ($broken_controllers as $b) {
    echo "- $b\n";
}
if (empty($broken_controllers)) echo "None found.\n";

echo "\n--- BROKEN METHODS (Method doesn't exist) ---\n";
foreach ($broken_methods as $b) {
    echo "- $b\n";
}
if (empty($broken_methods)) echo "None found.\n";

echo "\nDone.\n";
