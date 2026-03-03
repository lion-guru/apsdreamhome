<?php
/**
 * Automated Maintenance Script
 * Run this script daily for system maintenance
 */

echo "🔧 Automated Maintenance\n";
echo "=======================\n";

// 1. Database maintenance
echo "🗄️ Database Maintenance...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
    
    // Optimize tables
    $tables = ["api_keys", "properties", "users", "leads", "projects"];
    foreach ($tables as $table) {
        $pdo->exec("OPTIMIZE TABLE $table");
        echo "✅ Optimized $table\n";
    }
    
    // Clear old logs
    $pdo->exec("DELETE FROM api_requests WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    echo "✅ Cleared old API logs\n";
    
} catch (PDOException $e) {
    echo "❌ Database maintenance failed: " . $e->getMessage() . "\n";
}

// 2. File system maintenance
echo "\n📁 File System Maintenance...\n";

// Clear cache
$cacheDir = __DIR__ . "/cache";
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . "/*");
    foreach ($files as $file) {
        if (is_file($file) && (time() - filemtime($file)) > 86400) { // 24 hours
            unlink($file);
        }
    }
    echo "✅ Cleared old cache files\n";
}

// 3. Log rotation
echo "\n📋 Log Rotation...\n";
$logFiles = ["logs/debug_output.log", "logs/error.log"];
foreach ($logFiles as $logFile) {
    $fullPath = __DIR__ . "/" . $logFile;
    if (file_exists($fullPath) && filesize($fullPath) > 5 * 1024 * 1024) { // 5MB
        $backupFile = $fullPath . "." . date("Y-m-d");
        rename($fullPath, $backupFile);
        echo "✅ Rotated log: $logFile\n";
    }
}

echo "\n🎉 Maintenance Complete!\n";
?>