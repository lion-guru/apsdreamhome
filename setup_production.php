<?php

/**
 * Production Environment Setup Script
 * Sets up production environment with proper configuration
 */

// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors in production
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Set production environment
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');

// Load production configuration
$config = require __DIR__ . '/config/deployment.php';

// Create necessary directories
$directories = [
    __DIR__ . '/logs',
    __DIR__ . '/storage/app',
    __DIR__ . '/storage/app/public',
    __DIR__ . '/storage/logs',
    __DIR__ . '/storage/cache',
    __DIR__ . '/storage/sessions',
    __DIR__ . '/backups',
    __DIR__ . '/bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: {$dir}\n";
    }
}

// Set proper file permissions
$filesToProtect = [
    '.env',
    'config/database.php',
    'config/deployment.php',
    'storage/logs/',
    'storage/app/',
    'bootstrap/cache/'
];

foreach ($filesToProtect as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        if (is_dir($path)) {
            chmod($path, 0755);
        } else {
            chmod($path, 0644);
        }
        echo "Set permissions for: {$file}\n";
    }
}

// Generate application key if not set
if (empty(getenv('APP_KEY'))) {
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    putenv("APP_KEY={$appKey}");

    // Update .env file
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        $envContent = preg_replace('/^APP_KEY=.*/m', "APP_KEY={$appKey}", $envContent);
        file_put_contents($envFile, $envContent);
        echo "Generated and set APP_KEY\n";
    }
}

// Configure PHP settings for production
ini_set('memory_limit', $config['deployment']['memory_limit']);
ini_set('max_execution_time', $config['deployment']['max_execution_time']);
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');

// Enable OPcache if available
if (function_exists('opcache_enable')) {
    ini_set('opcache.enable', '1');
    ini_set('opcache.memory_consumption', '256');
    ini_set('opcache.max_accelerated_files', '7963');
    ini_set('opcache.revalidate_freq', '0');
    ini_set('opcache.preload', $config['performance']['opcache_preload']);
    echo "OPcache enabled and configured\n";
}

// Set up error logging
$logFile = __DIR__ . '/logs/app_' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);

// Test database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['database']};charset={$config['database']['charset']}",
        $config['database']['username'],
        $config['database']['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ Database connection successful\n";

    // Test basic query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result['test'] == 1) {
        echo "✅ Database query test passed\n";
    }

} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Create health check endpoint
$healthCheckFile = __DIR__ . '/public/health.php';
$healthCheckContent = '<?php
header("Content-Type: application/json");

try {
    // Test database connection
    $config = require __DIR__ . "/../config/deployment.php";
    $pdo = new PDO(
        "mysql:host={$config["database"]["host"]};dbname={$config["database"]["database"]}",
        $config["database"]["username"],
        $config["database"]["password"]
    );

    // Get system info
    $systemInfo = [
        "status" => "healthy",
        "timestamp" => date("c"),
        "php_version" => PHP_VERSION,
        "memory_usage" => memory_get_usage(true),
        "database" => "connected",
        "uptime" => time() - $_SERVER["REQUEST_TIME"]
    ];

    http_response_code(200);
    echo json_encode($systemInfo);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "unhealthy",
        "error" => $e->getMessage(),
        "timestamp" => date("c")
    ]);
}
';

file_put_contents($healthCheckFile, $healthCheckContent);
echo "Created health check endpoint at: /health.php\n";

echo "\n🎉 Production environment setup completed successfully!\n";
echo "\n📋 Next Steps:\n";
echo "1. ✅ Configure your web server (Apache/Nginx)\n";
echo "2. ✅ Set up SSL certificate\n";
echo "3. ✅ Configure domain DNS\n";
echo "4. ✅ Run database migrations: php deploy.php\n";
echo "5. ✅ Test the application thoroughly\n";
echo "6. ✅ Enable monitoring and logging\n";
echo "7. ✅ Set up automated backups\n";

echo "\n🔗 Useful URLs:\n";
echo "• Health Check: {$config['app_url']}/health.php\n";
echo "• Application: {$config['app_url']}\n";
echo "• Admin Panel: {$config['app_url']}/admin\n";

echo "\n📞 Support Contacts:\n";
echo "• Email: {$config['maintenance']['contact_email']}\n";
echo "• Emergency: {$config['maintenance']['emergency_contact']}\n";

echo "\n🚀 Ready for production deployment!\n";
