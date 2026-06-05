<?php
/**
 * Verify the global exception handler installed by BaseController.
 * This file intentionally throws — DO NOT LINK FROM UI.
 */
declare(strict_types=1);

// PSR-4 autoloader (project root, not __DIR__)
$projectRoot = dirname(__DIR__);
spl_autoload_register(function ($class) use ($projectRoot) {
    $prefix = 'App\\';
    $baseDir = $projectRoot . '/app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require_once $file;
});

chdir($projectRoot);

// Simulate what BaseController does
$previous = set_exception_handler(null);
set_exception_handler(function ($exception) use ($previous) {
    try {
        \App\Services\Monitoring\ErrorTrackerService::captureException($exception, [
            'url'    => '/test/exception-hook',
            'method' => 'CLI',
        ]);
    } catch (\Throwable $e) {
        echo "[monitoring capture failed: {$e->getMessage()}]\n";
    }
    if (is_callable($previous)) {
        call_user_func($previous, $exception);
    } else {
        echo "Uncaught: " . $exception->getMessage() . "\n";
    }
});

// Trigger
throw new \RuntimeException("TEST_EXCEP: synthetic error to verify monitoring_errors capture");
