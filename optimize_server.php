<?php
/**
 * APS Dream Home - Server Optimization Script
 */

require_once __DIR__ . '/config/paths.php';

echo '🖥️ APS DREAM HOME - SERVER OPTIMIZATION\n';
echo '=====================================\n\n';

// Check current server configuration
echo '📊 Current Server Configuration:\n';
echo 'PHP Version: ' . phpversion() . '\n';
echo 'Memory Limit: ' . ini_get('memory_limit') . '\n';
echo 'Max Execution Time: ' . ini_get('max_execution_time') . 's\n';
echo 'Upload Max Filesize: ' . ini_get('upload_max_filesize') . '\n';
echo 'OPcache Enabled: ' . (ini_get('opcache.enable') ? 'Yes' : 'No') . '\n';

// Check available extensions
echo '\n📋 Available Extensions:\n';
$extensions = ['curl', 'gd', 'mbstring', 'openssl', 'mysqli', 'opcache', 'redis'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "$status $ext\n";
}

// Check server load
echo '\n📊 Server Load Information:\n';
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    echo 'Load Average (1min): ' . $load[0] . '\n';
    echo 'Load Average (5min): ' . $load[1] . '\n';
    echo 'Load Average (15min): ' . $load[2] . '\n';
} else {
    echo 'Load information not available\n';
}

echo '\n📊 Memory Usage:\n';
echo 'Current Memory: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB\n';
echo 'Peak Memory: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB\n';

echo '\n🎉 Server optimization analysis completed!\n';
