<?php
/**
 * APS Dream Home - Complete Project Deep Scan
 * Comprehensive analysis of entire codebase
 */

echo "🔍 APS DREAM HOME - COMPLETE PROJECT DEEP SCAN\n";
echo "================================================\n";

// 1. Project Structure Analysis
echo "\n📁 PROJECT STRUCTURE ANALYSIS:\n";
$projectRoot = __DIR__;
$directories = [];
$files = [];
$totalSize = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectRoot));
foreach ($iterator as $item) {
    if ($item->isDir()) {
        $directories[] = str_replace($projectRoot . '/', '', $item->getPathname()) . '/';
    } else {
        $files[] = str_replace($projectRoot . '/', '', $item->getPathname());
        $totalSize += $item->getSize();
    }
}

echo "✅ Total Directories: " . count($directories) . "\n";
echo "✅ Total Files: " . count($files) . "\n";
echo "✅ Total Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";

// 2. Code Analysis by Type
echo "\n💻 CODE ANALYSIS BY TYPE:\n";

// PHP Files
$phpFiles = glob($projectRoot . '/**/*.php', GLOB_BRACE);
echo "✅ PHP Files: " . count($phpFiles) . "\n";

// JavaScript Files
$jsFiles = glob($projectRoot . '/**/*.js', GLOB_BRACE);
echo "✅ JavaScript Files: " . count($jsFiles) . "\n";

// CSS Files
$cssFiles = glob($projectRoot . '/**/*.css', GLOB_BRACE);
echo "✅ CSS Files: " . count($cssFiles) . "\n";

// HTML Files
$htmlFiles = glob($projectRoot . '/**/*.html', GLOB_BRACE);
echo "✅ HTML Files: " . count($htmlFiles) . "\n";

// JSON Files
$jsonFiles = glob($projectRoot . '/**/*.json', GLOB_BRACE);
echo "✅ JSON Files: " . count($jsonFiles) . "\n";

// 3. Framework Analysis
echo "\n🏗️ FRAMEWORK ANALYSIS:\n";

// Check for MVC Pattern
$controllersDir = $projectRoot . '/app/Http/Controllers';
$modelsDir = $projectRoot . '/app/Models';
$viewsDir = $projectRoot . '/app/views';

echo "✅ MVC Structure: " . (is_dir($controllersDir) ? 'YES' : 'NO') . "\n";
echo "✅ Controllers: " . (is_dir($controllersDir) ? count(glob($controllersDir . '/**/*.php')) : 0) . "\n";
echo "✅ Models: " . (is_dir($modelsDir) ? count(glob($modelsDir . '/**/*.php')) : 0) . "\n";
echo "✅ Views: " . (is_dir($viewsDir) ? count(glob($viewsDir . '/**/*.php')) : 0) . "\n";

// 4. Database Analysis
echo "\n🗄️ DATABASE ANALYSIS:\n";

// Check for database files
$dbFiles = glob($projectRoot . '/**/*database*.php');
echo "✅ Database Files: " . count($dbFiles) . "\n";

// Check for SQL files
$sqlFiles = glob($projectRoot . '/**/*.sql');
echo "✅ SQL Files: " . count($sqlFiles) . "\n";

// Check for migration files
$migrationFiles = glob($projectRoot . '/**/*migration*.php');
echo "✅ Migration Files: " . count($migrationFiles) . "\n";

// 5. API Analysis
echo "\n🔌 API ANALYSIS:\n";

$apiDir = $projectRoot . '/app/Http/Controllers/Api';
if (is_dir($apiDir)) {
    $apiControllers = glob($apiDir . '/*.php');
    echo "✅ API Controllers: " . count($apiControllers) . "\n";
    
    $apiRoutes = $projectRoot . '/routes/api.php';
    echo "✅ API Routes File: " . (file_exists($apiRoutes) ? 'YES' : 'NO') . "\n";
}

// 6. Frontend Analysis
echo "\n🎨 FRONTEND ANALYSIS:\n";

// Assets
$assetsDir = $projectRoot . '/assets';
if (is_dir($assetsDir)) {
    echo "✅ Assets Directory: YES\n";
    
    $cssCount = count(glob($assetsDir . '/**/*.css'));
    $jsCount = count(glob($assetsDir . '/**/*.js'));
    $imgCount = count(glob($assetsDir . '/**/*.{png,jpg,jpeg,gif,svg}', GLOB_BRACE));
    
    echo "  - CSS Files: " . $cssCount . "\n";
    echo "  - JS Files: " . $jsCount . "\n";
    echo "  - Images: " . $imgCount . "\n";
}

// 7. Configuration Analysis
echo "\n⚙️ CONFIGURATION ANALYSIS:\n";

$configDir = $projectRoot . '/config';
if (is_dir($configDir)) {
    $configFiles = glob($configDir . '/*.php');
    echo "✅ Config Files: " . count($configFiles) . "\n";
    
    foreach ($configFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
}

// 8. Testing Analysis
echo "\n🧪 TESTING ANALYSIS:\n";

$testFiles = glob($projectRoot . '/test*.php');
echo "✅ Test Files: " . count($testFiles) . "\n";

$testHtmlFiles = glob($projectRoot . '/*test*.html');
echo "✅ Test HTML Files: " . count($testHtmlFiles) . "\n";

// 9. Documentation Analysis
echo "\n📚 DOCUMENTATION ANALYSIS:\n";

$mdFiles = glob($projectRoot . '/*.md');
echo "✅ Markdown Files: " . count($mdFiles) . "\n";

foreach ($mdFiles as $file) {
    echo "  - " . basename($file) . "\n";
}

// 10. Security Analysis
echo "\n🔒 SECURITY ANALYSIS:\n";

// Check for .env files
$envFiles = glob($projectRoot . '/.env*');
echo "✅ Environment Files: " . count($envFiles) . "\n";

// Check for .htaccess
$htaccess = $projectRoot . '/.htaccess';
echo "✅ .htaccess: " . (file_exists($htaccess) ? 'YES' : 'NO') . "\n";

// Check for gitignore
$gitignore = $projectRoot . '/.gitignore';
echo "✅ .gitignore: " . (file_exists($gitignore) ? 'YES' : 'NO') . "\n";

// 11. Dependencies Analysis
echo "\n📦 DEPENDENCIES ANALYSIS:\n";

$composerJson = $projectRoot . '/composer.json';
if (file_exists($composerJson)) {
    $composerData = json_decode(file_get_contents($composerJson), true);
    echo "✅ Composer Dependencies: " . count($composerData['require'] ?? []) . "\n";
    
    if (isset($composerData['require'])) {
        foreach ($composerData['require'] as $package => $version) {
            echo "  - $package: $version\n";
        }
    }
}

// 12. Performance Analysis
echo "\n⚡ PERFORMANCE ANALYSIS:\n";

// Large files
$largeFiles = [];
foreach ($iterator as $item) {
    if (!$item->isDir() && $item->getSize() > 1024 * 1024) { // > 1MB
        $largeFiles[] = [
            'file' => str_replace($projectRoot . '/', '', $item->getPathname()),
            'size' => number_format($item->getSize() / 1024 / 1024, 2) . ' MB'
        ];
    }
}

echo "✅ Large Files (>1MB): " . count($largeFiles) . "\n";
foreach ($largeFiles as $file) {
    echo "  - " . $file['file'] . " (" . $file['size'] . ")\n";
}

// 13. Issues Analysis
echo "\n🚨 ISSUES ANALYSIS:\n";

// Duplicate files
$duplicates = [];
foreach ($files as $file) {
    $basename = basename($file);
    if (isset($duplicates[$basename])) {
        $duplicates[$basename][] = $file;
    } else {
        $duplicates[$basename] = [$file];
    }
}

$duplicateCount = 0;
foreach ($duplicates as $basename => $occurrences) {
    if (count($occurrences) > 1) {
        $duplicateCount += count($occurrences) - 1;
        echo "⚠️ Duplicate: $basename (" . count($occurrences) . " occurrences)\n";
    }
}

echo "✅ Total Duplicates: " . $duplicateCount . "\n";

// 14. Summary
echo "\n📊 PROJECT SUMMARY:\n";
echo "=====================================\n";
echo "Project: APS Dream Home\n";
echo "Type: PHP Web Application\n";
echo "Framework: Custom MVC\n";
echo "Database: MySQL\n";
echo "Total Files: " . count($files) . "\n";
echo "Total Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";
echo "Controllers: " . (is_dir($controllersDir) ? count(glob($controllersDir . '/**/*.php')) : 0) . "\n";
echo "Models: " . (is_dir($modelsDir) ? count(glob($modelsDir . '/**/*.php')) : 0) . "\n";
echo "Views: " . (is_dir($viewsDir) ? count(glob($viewsDir . '/**/*.php')) : 0) . "\n";
echo "API Endpoints: " . (is_dir($apiDir) ? count(glob($apiDir . '/*.php')) : 0) . "\n";
echo "Test Files: " . (count($testFiles) + count($testHtmlFiles)) . "\n";
echo "Documentation: " . count($mdFiles) . "\n";

echo "\n✅ DEEP SCAN COMPLETE!\n";
?>
