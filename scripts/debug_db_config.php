<?php
define('APP_ROOT', __DIR__);
require_once __DIR__ . '/app/core/App.php';
require_once __DIR__ . '/app/Helpers/env.php';

$app = new \App\Core\App(__DIR__);
$config = $app->config('database');

echo "DB Config:\n";
print_r($config);

echo "\nPDO DSN parts:\n";
echo "Driver: " . ($config['database']['driver'] ?? 'N/A') . "\n";
echo "Host: " . ($config['database']['host'] ?? 'N/A') . "\n";
echo "Database: '" . ($config['database']['database'] ?? 'N/A') . "'\n";
