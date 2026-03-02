<?php

/**
 * MAX LEVEL PROJECT DEEP ANALYSIS SCRIPT
 * Complete project structure, issues, and fixes analysis
 */

echo "🚨 MAX LEVEL PROJECT DEEP ANALYSIS STARTING...\n";
echo "📊 Analyzing complete project structure...\n\n";

// 1. Project Structure Analysis
echo "🏗️ PROJECT STRUCTURE ANALYSIS:\n";
$directories = [
    'app' => 'Core application files',
    'config' => 'Configuration files', 
    'public' => 'Public assets and entry point',
    'routes' => 'Routing configuration',
    'tests' => 'Testing infrastructure',
    'tools' => 'Development tools',
    'database' => 'Database files and migrations',
    'docs' => 'Documentation',
    'assets' => 'Frontend assets'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        $fileCount = count(glob("$dir/*", GLOB_NOSORT));
        $size = directorySize($dir);
        echo "✅ $dir: $fileCount files (" . formatBytes($size) . ") - $description\n";
    } else {
        echo "❌ $dir: MISSING - $description\n";
    }
}

// 2. Core Application Analysis
echo "\n🔍 CORE APPLICATION ANALYSIS:\n";
$coreFiles = [
    'app/Core/App.php' => 'Main application class',
    'app/Core/Controller.php' => 'Base controller',
    'app/Core/Database/Database.php' => 'Database connection',
    'app/Http/Controllers/BaseController.php' => 'HTTP base controller',
    'app/Http/Controllers/Controller.php' => 'Main controller',
    'app/Http/Controllers/HomeController.php' => 'Home page controller'
];

foreach ($coreFiles as $file => $description) {
    if (file_exists($file)) {
        $lines = count(file($file));
        $size = filesize($file);
        echo "✅ $file: $lines lines (" . formatBytes($size) . ") - $description\n";
        
        // Check for syntax errors
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);
        if ($returnCode === 0) {
            echo "   ✅ Syntax: OK\n";
        } else {
            echo "   ❌ Syntax: " . implode(", ", $output) . "\n";
        }
    } else {
        echo "❌ $file: MISSING - $description\n";
    }
}

// 3. Database Analysis
echo "\n🗄️ DATABASE ANALYSIS:\n";
if (file_exists('config/database.php')) {
    $dbConfig = include 'config/database.php';
    echo "✅ Database Config: Found\n";
    
    // Extract MySQL connection details
    $mysqlConfig = $dbConfig['connections']['mysql'] ?? [];
    echo "   Host: " . ($mysqlConfig['host'] ?? 'Not set') . "\n";
    echo "   Database: " . ($mysqlConfig['database'] ?? 'Not set') . "\n";
    echo "   Username: " . ($mysqlConfig['username'] ?? 'Not set') . "\n";
    
    // Test database connection
    try {
        $pdo = new PDO(
            "mysql:host={$mysqlConfig['host']};dbname={$mysqlConfig['database']}", 
            $mysqlConfig['username'], 
            $mysqlConfig['password']
        );
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "   ✅ Tables: " . count($tables) . " tables found\n";
        echo "   ✅ Connection: SUCCESS\n";
    } catch (Exception $e) {
        echo "   ❌ Connection: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Database Config: MISSING\n";
}

// 4. Routing Analysis
echo "\n🛣️ ROUTING ANALYSIS:\n";
$routeFiles = [
    'routes/web.php' => 'Web routes',
    'routes/api.php' => 'API routes',
    '.htaccess' => 'URL rewriting'
];

foreach ($routeFiles as $file => $description) {
    if (file_exists($file)) {
        $lines = count(file($file));
        echo "✅ $file: $lines lines - $description\n";
    } else {
        echo "❌ $file: MISSING - $description\n";
    }
}

// 5. Frontend Analysis
echo "\n🎨 FRONTEND ANALYSIS:\n";
$assetDirs = [
    'public/assets/css' => 'Stylesheets',
    'public/assets/js' => 'JavaScript files',
    'public/assets/images' => 'Images',
    'public/assets/fonts' => 'Fonts'
];

foreach ($assetDirs as $dir => $description) {
    if (is_dir($dir)) {
        $fileCount = count(glob("$dir/*", GLOB_NOSORT));
        $size = directorySize($dir);
        echo "✅ $dir: $fileCount files (" . formatBytes($size) . ") - $description\n";
    } else {
        echo "❌ $dir: MISSING - $description\n";
    }
}

// 6. Issues Detection
echo "\n🚨 ISSUES DETECTION:\n";
$issues = [];

// Check for common issues
if (!file_exists('app/Core/App.php')) {
    $issues[] = "Main App.php missing";
}

if (!file_exists('config/database.php')) {
    $issues[] = "Database configuration missing";
}

if (!is_dir('vendor')) {
    $issues[] = "Vendor directory missing - run composer install";
}

if (!file_exists('.env') && !file_exists('config/.env')) {
    $issues[] = "Environment file missing";
}

if (count($issues) > 0) {
    foreach ($issues as $issue) {
        echo "❌ $issue\n";
    }
} else {
    echo "✅ No critical issues detected\n";
}

// 7. Performance Analysis
echo "\n📈 PERFORMANCE ANALYSIS:\n";
if (function_exists('memory_get_usage')) {
    $memory = memory_get_usage(true);
    echo "✅ Memory Usage: " . formatBytes($memory) . "\n";
}

if (function_exists('memory_get_peak_usage')) {
    $peakMemory = memory_get_peak_usage(true);
    echo "✅ Peak Memory: " . formatBytes($peakMemory) . "\n";
}

echo "\n🎯 DEEP ANALYSIS COMPLETE!\n";

// Helper functions
function directorySize($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : directorySize($each);
    }
    return $size;
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

?>
