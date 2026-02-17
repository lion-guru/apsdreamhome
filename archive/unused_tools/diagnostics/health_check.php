<?php
/**
 * Advanced Health Check & Diagnostics
 * Part of the production-readiness suite
 */

require_once __DIR__ . '/../../bootstrap.php';

use App\Core\App;
use App\Core\Database;

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'environment' => [
        'php_version' => PHP_VERSION,
        'os' => PHP_OS,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'debug_mode' => defined('APP_DEBUG') ? APP_DEBUG : false,
    ],
    'checks' => []
];

// 1. Check Database
try {
    $db = App::database();
    if ($db && $db->getConnection()) {
        $health['checks']['database'] = [
            'status' => 'healthy',
            'driver' => 'PDO',
            'performance' => $db->getPerformanceStats()
        ];
    } else {
        throw new Exception("Database instance not available");
    }
} catch (Exception $e) {
    $health['checks']['database'] = [
        'status' => 'unhealthy',
        'error' => $e->getMessage()
    ];
    $health['status'] = 'degraded';
}

// 2. Check Directories
$dirs = [
    'storage' => __DIR__ . '/../../storage',
    'logs' => __DIR__ . '/../../storage/logs',
    'cache' => __DIR__ . '/../../storage/cache',
    'uploads' => __DIR__ . '/../../public/uploads',
];

foreach ($dirs as $key => $path) {
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
    
    $isWritable = is_writable($path);
    $health['checks']['filesystem'][$key] = [
        'path' => $path,
        'writable' => $isWritable,
        'status' => $isWritable ? 'healthy' : 'unhealthy'
    ];
    
    if (!$isWritable) {
        $health['status'] = 'degraded';
    }
}

// 3. Check Critical Extensions
$extensions = ['pdo_mysql', 'mbstring', 'gd', 'curl', 'json', 'openssl'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $health['checks']['extensions'][$ext] = [
        'loaded' => $loaded,
        'status' => $loaded ? 'healthy' : 'unhealthy'
    ];
    if (!$loaded) {
        $health['status'] = 'degraded';
    }
}

// 4. Resource Usage
$memoryLimit = ini_get('memory_limit');
$memoryUsage = memory_get_usage(true) / 1024 / 1024;
$health['resources'] = [
    'memory_limit' => $memoryLimit,
    'memory_usage_mb' => round($memoryUsage, 2),
    'load_avg' => function_exists('sys_getloadavg') ? sys_getloadavg() : 'N/A'
];

http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
?>