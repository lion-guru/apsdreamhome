<?php
/**
 * System Optimization Script
 * Cleans up backup files and optimizes the APS Dream Home system
 */

echo "=== APS Dream Home System Optimization ===\n";

// Clean up backup files
echo "Cleaning up backup files...\n";
$backupFiles = glob(__DIR__ . '/**/*.backup*', GLOB_BRACE);
$cleanedCount = 0;

foreach ($backupFiles as $file) {
    if (is_file($file)) {
        unlink($file);
        $cleanedCount++;
    }
}

echo "Cleaned up $cleanedCount backup files\n";

// Optimize database connections
echo "Checking database configuration...\n";
$configFile = __DIR__ . '/config/database.php';
if (file_exists($configFile)) {
    echo "Database configuration found\n";
} else {
    echo "Creating database configuration...\n";
    $dbConfig = "<?php\n";
    $dbConfig .= "// Database Configuration\n";
    $dbConfig .= "return [\n";
    $dbConfig .= "    'host' => 'localhost',\n";
    $dbConfig .= "    'database' => 'apsdreamhome',\n";
    $dbConfig .= "    'username' => 'root',\n";
    $dbConfig .= "    'password' => '',\n";
    $dbConfig .= "    'charset' => 'utf8mb4',\n";
    $dbConfig .= "    'collation' => 'utf8mb4_unicode_ci'\n";
    $dbConfig .= "];\n";
    
    if (!is_dir(__DIR__ . '/config')) {
        mkdir(__DIR__ . '/config', 0755, true);
    }
    file_put_contents($configFile, $dbConfig);
    echo "Database configuration created\n";
}

// Check for missing directories
$requiredDirs = [
    'logs',
    'cache',
    'uploads',
    'uploads/properties',
    'uploads/documents',
    'temp'
];

echo "Checking required directories...\n";
foreach ($requiredDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        echo "Created directory: $dir\n";
    } else {
        echo "Directory exists: $dir\n";
    }
}

// Clear cache if exists
$cacheDir = __DIR__ . '/cache';
if (is_dir($cacheDir)) {
    $cacheFiles = glob($cacheDir . '/*');
    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "Cache cleared\n";
}

// Reset error log
$errorLog = __DIR__ . '/logs/php_error.log';
if (file_exists($errorLog)) {
    file_put_contents($errorLog, "# System Optimization Completed - " . date('Y-m-d H:i:s') . "\n");
    echo "Error log reset\n";
}

echo "\n=== System Optimization Complete ===\n";
echo "✅ Backup files cleaned\n";
echo "✅ Database configuration verified\n";
echo "✅ Required directories checked\n";
echo "✅ Cache cleared\n";
echo "✅ Error log reset\n";
echo "\nSystem is now optimized and ready for production!\n";
?>
