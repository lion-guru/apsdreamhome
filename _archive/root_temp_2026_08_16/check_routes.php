<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check routes for visits
$routes = file_get_contents('routes/web.php');
if (strpos($routes, 'visits') !== false) {
    // Find visits routes
    preg_match_all('/visits[^"\n]*/', $routes, $matches);
    print_r($matches[0]);
}

// Check which controller handles /admin/visits
echo "Routes containing visits:\n";
exec("grep -n 'visits' routes/web.php 2>/dev/null | head -20", $output);
print_r($output);