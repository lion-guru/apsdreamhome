<?php
define('BASE_PATH', __DIR__);
define('APP_ROOT', BASE_PATH);
require_once BASE_PATH . '/app/core/Autoloader.php';
$loader = \App\Core\Autoloader::getInstance();
$loader->register();
$loader->addNamespace('App', BASE_PATH . '/app');

use App\Core\App;

try {
    $db = App::database();
    $tables = $db->fetchAll("SHOW TABLES");
    foreach ($tables as $table) {
        // The key is usually Tables_in_dbname
        echo reset($table) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
