<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Check routes for visits - find all routes with 'visits'
$routes = file_get_contents('routes/web.php');
$lines = explode("\n", $routes);
$found = false;
foreach ($lines as $i => $line) {
    if (strpos($line, 'visits') !== false && strpos($line, '//') === false) {
        echo "Line " . ($i+1) . ": " . trim($line) . PHP_EOL;
    }
}