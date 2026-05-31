<?php
// Define APP_ROOT to avoid error in Autoloader
define('APP_ROOT', __DIR__);
require_once 'app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Load bootstrap for full app setup
require_once 'config/bootstrap.php';

$configService = \App\Core\ConfigService::getInstance();
$dbConfig = $configService->getDatabaseConfig();

echo "Database Configuration from ConfigService:\n";
print_r($dbConfig);

echo "\nGlobal \$config Variable:\n";
global $config;
print_r($config);

try {
    $db = \App\Core\Database\Database::getInstance();
    echo "\nConnection Success!\n";
} catch (\Exception $e) {
    echo "\nConnection Failed: " . $e->getMessage() . "\n";
}
