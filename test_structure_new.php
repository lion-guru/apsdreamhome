<?php
/**
 * Test file to verify new structure works
 */

// Load the bootstrap first
require_once __DIR__ . '/config/bootstrap.php';

// Test if constants are defined
echo "Testing new structure...\n";
echo "APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NOT DEFINED') . "\n";
echo "APP_ROOT: " . (defined('APP_ROOT') ? APP_ROOT : 'NOT DEFINED') . "\n";
echo "ENVIRONMENT: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'NOT DEFINED') . "\n";

// Test if we can load config
echo "Testing config loading...\n";
try {
    $config_file = __DIR__ . '/config/application.php';
    if (file_exists($config_file)) {
        $config = require $config_file;
        echo "Config loaded successfully\n";
        echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "\n";
        echo "ASSET_URL: " . (defined('ASSET_URL') ? ASSET_URL : 'NOT DEFINED') . "\n";
    } else {
        echo "Config file not found: $config_file\n";
    }
} catch (Exception $e) {
    echo "Error loading config: " . $e->getMessage() . "\n";
}

// Test if we can create application instance
echo "Testing application instance...\n";
try {
    $app = app();
    echo "App instance created successfully\n";
    echo "Debug mode: " . ($app->isDebug() ? 'true' : 'false') . "\n";
} catch (Exception $e) {
    echo "Error creating app instance: " . $e->getMessage() . "\n";
}

// Test if router can be created
echo "Testing router...\n";
try {
    $router = new App\Core\Router();
    echo "Router created successfully\n";
} catch (Exception $e) {
    echo "Error creating router: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";
?>
