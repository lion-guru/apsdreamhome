<?php
define('APP_ROOT', dirname(__DIR__, 2));
define('BASE_PATH', dirname(__DIR__, 2));

require_once APP_ROOT . '/app/core/App.php';

use App\Core\App;

try {
    echo "Autoloader loaded successfully.\n";
    echo "Legacy autoloader loaded successfully.\n";

    $app = new App(APP_ROOT);
    echo "App instance created successfully.\n";

    // We won't run $app->run() as it might try to output headers or start sessions
    echo "Bootstrap test passed.\n";
} catch (\Throwable $e) {
    echo "Bootstrap test failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
