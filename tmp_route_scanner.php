<?php
$routes_file = file_get_contents('routes/web.php');
preg_match_all('/\$router->(get|post|put|delete|any)\(\'(.*?)\',\s*\'(.*?)\'\)/', $routes_file, $matches, PREG_SET_ORDER);

$duplicates = [];
$route_map = [];
$broken_controllers = [];
$broken_methods = [];

$out = "# MAXIMUM DEEP SCAN: ROUTE & WORKFLOW AUDIT\n\n";
$out .= "Total Routes Found: " . count($matches) . "\n\n";

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
    
    if (strpos($handler, '@') !== false) {
        list($controller_rel, $method_name) = explode('@', $handler);
        
        if (strpos($controller_rel, 'App\\Http\\Controllers\\') === 0) {
            $controller = $controller_rel;
        } else {
            $controller = 'App\\Http\\Controllers\\' . ltrim($controller_rel, '\\');
        }
        
        $controller_file = str_replace(['App\\', '\\'], ['app/', '/'], $controller) . '.php';
        
        if (!file_exists($controller_file)) {
            $broken_controllers[] = "- `$key` -> `$handler` (File missing: `$controller_file`)";
        } else {
            $content = file_get_contents($controller_file);
            $class_name = basename($controller_file, '.php');
            if (!preg_match('/class\s+' . $class_name . '/i', $content)) {
                $broken_controllers[] = "- `$key` -> `$handler` (Class missing: `$class_name`)";
            } elseif (!preg_match('/function\s+' . $method_name . '\s*\(/i', $content)) {
                $broken_methods[] = "- `$key` -> `$handler` (Method missing: `$method_name` in `$controller`)";
            }
        }
    }
}

foreach ($duplicates as $key => $handlers) {
    if (!isset($route_map[$key])) continue;
    foreach($handlers as $h) {
        if (!in_array($h, $route_map[$key])) {
            $route_map[$key][] = $h;
        }
    }
}

$out .= "## DUPLICATE ROUTES (Conflicting paths)\n";
$dup_count = 0;
foreach ($route_map as $key => $handlers) {
    if (count($handlers) > 1) {
        $dup_count++;
        $out .= "### `$key` is mapped " . count($handlers) . " times:\n";
        foreach ($handlers as $h) {
            $out .= "- `$h`\n";
        }
    }
}
if ($dup_count == 0) $out .= "None found.\n";

$out .= "\n## BROKEN CONTROLLERS (Missing File or Class)\n";
foreach ($broken_controllers as $b) {
    $out .= "$b\n";
}
if (empty($broken_controllers)) $out .= "None found.\n";

$out .= "\n## BROKEN METHODS (Method doesn't exist)\n";
foreach ($broken_methods as $b) {
    $out .= "$b\n";
}
if (empty($broken_methods)) $out .= "None found.\n";

$out .= "\nDone.\n";
file_put_contents('PROJECT_WORKFLOW_AUDIT.md', $out);
echo "Report generated at PROJECT_WORKFLOW_AUDIT.md\n";

