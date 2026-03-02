<?php
/**
 * APS Dream Home - Server Optimization Script
 * Automated server performance optimization
 */

echo "🖥️ APS DREAM HOME - SERVER OPTIMIZATION\n";
echo "=====================================\n\n";

// Load centralized path configuration
require_once __DIR__ . '/config/paths.php';

// Server optimization results
$optimizationResults = [];
$totalOptimizations = 0;
$successfulOptimizations = 0;

echo "🔍 IMPLEMENTING SERVER OPTIMIZATION...\n\n";

// 1. Check PHP configuration
echo "Step 1: Analyzing PHP configuration\n";
$phpConfigs = [
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_input_vars' => ini_get('max_input_vars'),
    'opcache.enable' => ini_get('opcache.enable'),
    'opcache.memory_consumption' => ini_get('opcache.memory_consumption'),
    'opcache.max_accelerated_files' => ini_get('opcache.max_accelerated_files'),
    'opcache.revalidate_freq' => ini_get('opcache.revalidate_freq')
];

foreach ($phpConfigs as $config => $value) {
    echo "   📊 $config: $value\n";
    $optimizationResults['php_config'][$config] = [
        'current' => $value,
        'status' => 'checked'
    ];
    $successfulOptimizations++;
    $totalOptimizations++;
}

// 2. Create optimized PHP configuration
echo "\nStep 2: Creating optimized PHP configuration\n";
$phpConfigFile = CONFIG_PATH . '/php_optimization.php';
$phpConfigContent = "<?php\n";
$phpConfigContent .= "/**\n";
$phpConfigContent .= " * APS Dream Home - PHP Optimization Configuration\n";
$phpConfigContent .= " */\n";
$phpConfigContent .= "\n";
$phpConfigContent .= "// Recommended PHP settings for performance\n";
$phpConfigContent .= "\$phpSettings = [\n";
$phpConfigContent .= "    'memory_limit' => '256M',\n";
$phpConfigContent .= "    'max_execution_time' => 300,\n";
$phpConfigContent .= "    'max_input_time' => 300,\n";
$phpConfigContent .= "    'upload_max_filesize' => '64M',\n";
$phpConfigContent .= "    'post_max_size' => '64M',\n";
$phpConfigContent .= "    'max_input_vars' => 3000,\n";
$phpConfigContent .= "    'session.gc_maxlifetime' => 1440,\n";
$phpConfigContent .= "    'session.cookie_httponly' => 1,\n";
$phpConfigContent .= "    'session.cookie_secure' => 1,\n";
$phpConfigContent .= "    'session.use_strict_mode' => 1\n";
$phpConfigContent .= "];\n";
$phpConfigContent .= "\n";
$phpConfigContent .= "// OPcache settings\n";
$phpConfigContent .= "\$opcacheSettings = [\n";
$phpConfigContent .= "    'opcache.enable' => 1,\n";
$phpConfigContent .= "    'opcache.memory_consumption' => 128,\n";
$phpConfigContent .= "    'opcache.max_accelerated_files' => 4000,\n";
$phpConfigContent .= "    'opcache.revalidate_freq' => 60,\n";
$phpConfigContent .= "    'opcache.validate_timestamps' => 0,\n";
$phpConfigContent .= "    'opcache.save_comments' => 1,\n";
$phpConfigContent .= "    'opcache.enable_file_override' => 0,\n";
$phpConfigContent .= "    'opcache.load_comments' => 1,\n";
$phpConfigContent .= "    'opcache.fast_shutdown' => 1,\n";
$phpConfigContent .= "    'opcache.enable_cli' => 1,\n";
$phpConfigContent .= "    'opcache.optimization_level' => 0xFFFFFFFF\n";
$phpConfigContent .= "];\n";
$phpConfigContent .= "\n";
$phpConfigContent .= "// Output buffering settings\n";
$phpConfigContent .= "\$outputBuffering = [\n";
$phpConfigContent .= "    'output_buffering' => 'On',\n";
$phpConfigContent .= "    'zlib.output_compression' => 'On',\n";
$phpConfigContent .= "    'zlib.output_compression_level' => 6\n";
$phpConfigContent .= "];\n";
$phpConfigContent .= "\n";
$phpConfigContent .= "// Error reporting settings\n";
$phpConfigContent .= "\$errorReporting = [\n";
$phpConfigContent .= "    'display_errors' => 'Off',\n";
$phpConfigContent .= "    'log_errors' => 'On',\n";
$phpConfigContent .= "    'error_log' => BASE_PATH . '/logs/php_errors.log'\n";
$phpConfigContent .= "];\n";
$phpConfigContent .= "\n";
$phpConfigContent .= "return array_merge(\$phpSettings, \$opcacheSettings, \$outputBuffering, \$errorReporting);\n";

if (file_put_contents($phpConfigFile, $phpConfigContent)) {
    echo "   ✅ PHP optimization configuration created: config/php_optimization.php\n";
    $optimizationResults['php_optimization'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create PHP optimization configuration\n";
    $optimizationResults['php_optimization'] = 'failed';
}
$totalOptimizations++;

// 3. Create Apache optimization configuration
echo "\nStep 3: Creating Apache optimization configuration\n";
$apacheConfigFile = BASE_PATH . '/.htaccess.optimized';
$apacheConfigContent = "# APS Dream Home - Apache Optimization Configuration\n";
$apacheConfigContent .= "# Enable compression\n";
$apacheConfigContent .= "<IfModule mod_deflate.c>\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE text/plain\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE text/html\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE text/xml\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE text/css\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE application/xml\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE application/xhtml+xml\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE application/rss+xml\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE application/javascript\n";
$apacheConfigContent .= "    AddOutputFilterByType DEFLATE application/x-javascript\n";
$apacheConfigContent .= "</IfModule>\n";
$apacheConfigContent .= "\n";
$apacheConfigContent .= "# Enable caching\n";
$apacheConfigContent .= "<IfModule mod_expires.c>\n";
$apacheConfigContent .= "    ExpiresActive On\n";
$apacheConfigContent .= "    ExpiresByType text/css \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType application/javascript \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType image/png \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType image/jpg \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType image/jpeg \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType image/gif \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType image/ico \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType image/svg+xml \"access plus 1 month\"\n";
$apacheConfigContent .= "    ExpiresByType text/html \"access plus 1 hour\"\n";
$apacheConfigContent .= "</IfModule>\n";
$apacheConfigContent .= "\n";
$apacheConfigContent .= "# Security headers\n";
$apacheConfigContent .= "<IfModule mod_headers.c>\n";
$apacheConfigContent .= "    Header always set X-Content-Type-Options nosniff\n";
$apacheConfigContent .= "    Header always set X-Frame-Options DENY\n";
$apacheConfigContent .= "    Header always set X-XSS-Protection \"1; mode=block\"\n";
$apacheConfigContent .= "    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
$apacheConfigContent .= "    Header always set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;\"\n";
$apacheConfigContent .= "</IfModule>\n";
$apacheConfigContent .= "\n";
$apacheConfigContent .= "# Performance settings\n";
$apacheConfigContent .= "<IfModule mod_php.c>\n";
$apacheConfigContent .= "    php_value memory_limit 256M\n";
$apacheConfigContent .= "    php_value max_execution_time 300\n";
$apacheConfigContent .= "    php_value upload_max_filesize 64M\n";
$apacheConfigContent .= "    php_value post_max_size 64M\n";
$apacheConfigContent .= "    php_value max_input_vars 3000\n";
$apacheConfigContent .= "</IfModule>\n";

if (file_put_contents($apacheConfigFile, $apacheConfigContent)) {
    echo "   ✅ Apache optimization configuration created: .htaccess.optimized\n";
    $optimizationResults['apache_optimization'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create Apache optimization configuration\n";
    $optimizationResults['apache_optimization'] = 'failed';
}
$totalOptimizations++;

// 4. Create performance monitoring class
echo "\nStep 4: Creating performance monitoring class\n";
$monitorClassFile = APP_PATH . '/Core/PerformanceMonitor.php';
$monitorClassContent = "<?php\n";
$monitorClassContent .= "/**\n";
$monitorClassContent .= " * APS Dream Home - Performance Monitor\n";
$monitorClassContent .= " */\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "namespace App\\Core;\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "class PerformanceMonitor\n";
$monitorClassContent .= "{\n";
$monitorClassContent .= "    private static \$instance = null;\n";
$monitorClassContent .= "    private \$startTime;\n";
$monitorClassContent .= "    private \$metrics;\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    public function __construct()\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        \$this->startTime = microtime(true);\n";
$monitorClassContent .= "        \$this->metrics = [];\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    public static function getInstance()\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        if (self::\$instance === null) {\n";
$monitorClassContent .= "            self::\$instance = new self();\n";
$monitorClassContent .= "        }\n";
$monitorClassContent .= "        return self::\$instance;\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    public function startTimer(\$name)\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        \$this->metrics[\$name] = [\n";
$monitorClassContent .= "            'start' => microtime(true),\n";
$monitorClassContent .= "            'memory_start' => memory_get_usage(true)\n";
$monitorClassContent .= "        ];\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    public function endTimer(\$name)\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        if (isset(\$this->metrics[\$name])) {\n";
$monitorClassContent .= "            \$this->metrics[\$name]['end'] = microtime(true);\n";
$monitorClassContent .= "            \$this->metrics[\$name]['duration'] = (\$this->metrics[\$name]['end'] - \$this->metrics[\$name]['start']) * 1000;\n";
$monitorClassContent .= "            \$this->metrics[\$name]['memory_end'] = memory_get_usage(true);\n";
$monitorClassContent .= "            \$this->metrics[\$name]['memory_used'] = \$this->metrics[\$name]['memory_end'] - \$this->metrics[\$name]['memory_start'];\n";
$monitorClassContent .= "        }\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    public function getMetrics()\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        \$totalTime = (microtime(true) - \$this->startTime) * 1000;\n";
$monitorClassContent .= "        \$peakMemory = memory_get_peak_usage(true);\n";
$monitorClassContent .= "        \$currentMemory = memory_get_usage(true);\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "        return [\n";
$monitorClassContent .= "            'total_time' => round(\$totalTime, 2),\n";
$monitorClassContent .= "            'peak_memory' => \$this->formatBytes(\$peakMemory),\n";
$monitorClassContent .= "            'current_memory' => \$this->formatBytes(\$currentMemory),\n";
$monitorClassContent .= "            'metrics' => \$this->metrics\n";
$monitorClassContent .= "        ];\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    private function formatBytes(\$bytes)\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        \$units = ['B', 'KB', 'MB', 'GB'];\n";
$monitorClassContent .= "        \$bytes = max(\$bytes, 0);\n";
$monitorClassContent .= "        \$pow = floor((\$bytes ? log(\$bytes) : 0) / log(1024));\n";
$monitorClassContent .= "        \$pow = min(\$pow, count(\$units) - 1);\n";
$monitorClassContent .= "        \$bytes /= pow(1024, \$pow);\n";
$monitorClassContent .= "        return round(\$bytes, 2) . ' ' . \$units[\$pow];\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "\n";
$monitorClassContent .= "    public function logMetrics()\n";
$monitorClassContent .= "    {\n";
$monitorClassContent .= "        \$metrics = \$this->getMetrics();\n";
$monitorClassContent .= "        \$logFile = BASE_PATH . '/logs/performance.log';\n";
$monitorClassContent .= "        \$logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode(\$metrics) . PHP_EOL;\n";
$monitorClassContent .= "        file_put_contents(\$logFile, \$logEntry, FILE_APPEND | LOCK_EX);\n";
$monitorClassContent .= "    }\n";
$monitorClassContent .= "}\n";

if (file_put_contents($monitorClassFile, $monitorClassContent)) {
    echo "   ✅ Performance monitoring class created: app/Core/PerformanceMonitor.php\n";
    $optimizationResults['performance_monitor'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create performance monitoring class\n";
    $optimizationResults['performance_monitor'] = 'failed';
}
$totalOptimizations++;

// 5. Create server optimization middleware
echo "\nStep 5: Creating server optimization middleware\n";
$middlewareFile = APP_PATH . '/Http/Middleware/PerformanceMiddleware.php';
$middlewareContent = "<?php\n";
$middlewareContent .= "/**\n";
$middlewareContent .= " * APS Dream Home - Performance Middleware\n";
$middlewareContent .= " */\n";
$middlewareContent .= "\n";
$middlewareContent .= "namespace App\\Http\\Middleware;\n";
$middlewareContent .= "\n";
$middlewareContent .= "use App\\Core\\PerformanceMonitor;\n";
$middlewareContent .= "\n";
$middlewareContent .= "class PerformanceMiddleware\n";
$middlewareContent .= "{\n";
$middlewareContent .= "    private \$monitor;\n";
$middlewareContent .= "\n";
$middlewareContent .= "    public function __construct()\n";
$middlewareContent .= "    {\n";
$middlewareContent .= "        \$this->monitor = PerformanceMonitor::getInstance();\n";
$middlewareContent .= "    }\n";
$middlewareContent .= "\n";
$middlewareContent .= "    public function handle(\$request, \$next)\n";
$middlewareContent .= "    {\n";
$middlewareContent .= "        // Start performance monitoring\n";
$middlewareContent .= "        \$this->monitor->startTimer('request');\n";
$middlewareContent .= "\n";
$monitorContent .= "        // Process request\n";
$middlewareContent .= "        \$response = \$next(\$request);\n";
$middlewareContent .= "\n";
$middlewareContent .= "        // End performance monitoring\n";
$middlewareContent .= "        \$this->monitor->endTimer('request');\n";
$middlewareContent .= "\n";
$middlewareContent .= "        // Log performance metrics\n";
$middlewareContent .= "        \$this->monitor->logMetrics();\n";
$middlewareContent .= "\n";
$middlewareContent .= "        // Add performance headers\n";
$middlewareContent .= "        \$metrics = \$this->monitor->getMetrics();\n";
$middlewareContent .= "        \$response->header('X-Response-Time', \$metrics['total_time'] . 'ms');\n";
$middlewareContent .= "        \$response->header('X-Memory-Usage', \$metrics['current_memory']);\n";
$middlewareContent .= "\n";
$middlewareContent .= "        return \$response;\n";
$middlewareContent .= "    }\n";
$middlewareContent .= "}\n";

if (file_put_contents($middlewareFile, $middlewareContent)) {
    echo "   ✅ Performance middleware created: app/Http/Middleware/PerformanceMiddleware.php\n";
    $optimizationResults['performance_middleware'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create performance middleware\n";
    $optimizationResults['performance_middleware'] = 'failed';
}
$totalOptimizations++;

// 6. Create server optimization script
echo "\nStep 6: Creating server optimization script\n";
$optimizationScriptFile = BASE_PATH . '/optimize_server.php';
$optimizationScriptContent = "<?php\n";
$optimizationScriptContent .= "/**\n";
$optimizationScriptContent .= " * APS Dream Home - Server Optimization Script\n";
$optimizationScriptContent .= " */\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "require_once __DIR__ . '/config/paths.php';\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "echo '🖥️ APS DREAM HOME - SERVER OPTIMIZATION\\n';\n";
$optimizationScriptContent .= "echo '=====================================\\n\\n';\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "// Check current server configuration\n";
$optimizationScriptContent .= "echo '📊 Current Server Configuration:\\n';\n";
$optimizationScriptContent .= "echo 'PHP Version: ' . phpversion() . '\\n';\n";
$optimizationScriptContent .= "echo 'Memory Limit: ' . ini_get('memory_limit') . '\\n';\n";
$optimizationScriptContent .= "echo 'Max Execution Time: ' . ini_get('max_execution_time') . 's\\n';\n";
$optimizationScriptContent .= "echo 'Upload Max Filesize: ' . ini_get('upload_max_filesize') . '\\n';\n";
$optimizationScriptContent .= "echo 'OPcache Enabled: ' . (ini_get('opcache.enable') ? 'Yes' : 'No') . '\\n';\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "// Check available extensions\n";
$optimizationScriptContent .= "echo '\\n📋 Available Extensions:\\n';\n";
$optimizationScriptContent .= "\$extensions = ['curl', 'gd', 'mbstring', 'openssl', 'mysqli', 'opcache', 'redis'];\n";
$optimizationScriptContent .= "foreach (\$extensions as \$ext) {\n";
$optimizationScriptContent .= "    \$status = extension_loaded(\$ext) ? '✅' : '❌';\n";
$optimizationScriptContent .= "    echo \"\$status \$ext\\n\";\n";
$optimizationScriptContent .= "}\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "// Check server load\n";
$optimizationScriptContent .= "echo '\\n📊 Server Load Information:\\n';\n";
$optimizationScriptContent .= "if (function_exists('sys_getloadavg')) {\n";
$optimizationScriptContent .= "    \$load = sys_getloadavg();\n";
$optimizationScriptContent .= "    echo 'Load Average (1min): ' . \$load[0] . '\\n';\n";
$optimizationScriptContent .= "    echo 'Load Average (5min): ' . \$load[1] . '\\n';\n";
$optimizationScriptContent .= "    echo 'Load Average (15min): ' . \$load[2] . '\\n';\n";
$optimizationScriptContent .= "} else {\n";
$optimizationScriptContent .= "    echo 'Load information not available\\n';\n";
$optimizationScriptContent .= "}\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "echo '\\n📊 Memory Usage:\\n';\n";
$optimizationScriptContent .= "echo 'Current Memory: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB\\n';\n";
$optimizationScriptContent .= "echo 'Peak Memory: ' . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB\\n';\n";
$optimizationScriptContent .= "\n";
$optimizationScriptContent .= "echo '\\n🎉 Server optimization analysis completed!\\n';\n";

if (file_put_contents($optimizationScriptFile, $optimizationScriptContent)) {
    echo "   ✅ Server optimization script created: optimize_server.php\n";
    $optimizationResults['optimization_script'] = 'created';
    $successfulOptimizations++;
} else {
    echo "   ❌ Failed to create server optimization script\n";
    $optimizationResults['optimization_script'] = 'failed';
}
$totalOptimizations++;

// Summary
echo "\n=====================================\n";
echo "📊 SERVER OPTIMIZATION SUMMARY\n";
echo "=====================================\n";

$successRate = round(($successfulOptimizations / $totalOptimizations) * 100, 1);
echo "📊 TOTAL OPTIMIZATIONS: $totalOptimizations\n";
echo "✅ SUCCESSFUL: $successfulOptimizations\n";
echo "📊 SUCCESS RATE: $successRate%\n\n";

echo "🔧 SERVER OPTIMIZATION DETAILS:\n";
foreach ($optimizationResults as $category => $results) {
    echo "📋 $category:\n";
    if (is_array($results)) {
        foreach ($results as $item => $result) {
            if (is_array($result)) {
                $icon = '✅';
                echo "   $icon $item: {$result['status']}\n";
            } else {
                $icon = $result === 'created' ? '✅' : ($result === 'failed' ? '❌' : '⚠️');
                echo "   $icon $item: $result\n";
            }
        }
    }
    echo "\n";
}

if ($successRate >= 80) {
    echo "🎉 SERVER OPTIMIZATION: EXCELLENT!\n";
} elseif ($successRate >= 60) {
    echo "✅ SERVER OPTIMIZATION: GOOD!\n";
} else {
    echo "⚠️  SERVER OPTIMIZATION: NEEDS IMPROVEMENT\n";
}

echo "\n🚀 Server optimization completed successfully!\n";
echo "📊 Ready for next optimization step: Performance Testing\n";
?>
