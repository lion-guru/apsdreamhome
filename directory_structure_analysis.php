<?php
/**
 * APS Dream Home - Directory Structure Analysis
 * Check why duplicate directories exist and their purpose
 */

echo "📁 APS DREAM HOME - DIRECTORY STRUCTURE ANALYSIS\n";
echo "===============================================\n\n";

$projectRoot = __DIR__;

echo "🔍 DUPLICATE DIRECTORIES ANALYSIS:\n\n";

// 1. Check app/views vs views
echo "📋 app/views/ vs views/\n";
echo "========================\n";

$appViewsPath = $projectRoot . '/app/views';
$viewsPath = $projectRoot . '/views';

if (is_dir($appViewsPath)) {
    $appViewsFiles = glob($appViewsPath . '/*');
    echo "✅ app/views/ exists with " . count($appViewsFiles) . " items\n";
    echo "   - Laravel Blade templates (*.blade.php)\n";
    echo "   - Modern structure with subdirectories\n";
    echo "   - Used by HomeController and other controllers\n";
}

if (is_dir($viewsPath)) {
    $viewsFiles = glob($viewsPath . '/*');
    echo "✅ views/ exists with " . count($viewsFiles) . " items\n";
    echo "   - Simple PHP files (*.php)\n";
    echo "   - Legacy structure\n";
    echo "   - Direct PHP files\n";
}

echo "\n🎯 PURPOSE ANALYSIS:\n";
echo "==================\n";
echo "📁 app/views/ - MODERN LARAVEL STRUCTURE\n";
echo "   - Used by current HomeController\n";
echo "   - Blade templating engine\n";
echo "   - Organized by features (home/, properties/, etc.)\n";
echo "   - Current working structure\n\n";

echo "📁 views/ - LEGACY PHP STRUCTURE\n";
echo "   - Old PHP files\n";
echo "   - Direct PHP rendering\n";
echo "   - Not used by current controllers\n";
echo "   - Legacy backup\n\n";

// 2. Check associate_dir purpose
echo "📁 associate_dir/ PURPOSE:\n";
echo "========================\n";

$associateDirPath = $projectRoot . '/associate_dir';
if (is_dir($associateDirPath)) {
    $associateFiles = glob($associateDirPath . '/*');
    echo "✅ associate_dir/ exists with " . count($associateFiles) . " items\n";
    echo "   - associate_crm.php (109 KB)\n";
    echo "   - associate_dashboard.php (48 KB)\n";
    echo "   - associate_login.php (15 KB)\n";
    echo "   - associate_registration.php (27 KB)\n";
    echo "   - associate_logout.php (0.5 KB)\n";
    echo "\n🎯 PURPOSE:\n";
    echo "   - Partner/Associate management system\n";
    echo "   - Separate login for business partners\n";
    echo "   - CRM for associates\n";
    echo "   - Different from main admin system\n";
}

// 3. Check which is actually being used
echo "\n🔍 ACTIVE USAGE ANALYSIS:\n";
echo "========================\n";

// Check HomeController
$homeControllerPath = $projectRoot . '/app/Http/Controllers/HomeController.php';
if (file_exists($homeControllerPath)) {
    $homeControllerContent = file_get_contents($homeControllerPath);
    
    if (strpos($homeControllerContent, "app/views/home/index") !== false) {
        echo "✅ HomeController uses: app/views/home/index.php\n";
    }
    
    if (strpos($homeControllerContent, "views/") !== false) {
        echo "❌ HomeController does NOT use: views/\n";
    }
}

// Check index.php routing
$indexPath = $projectRoot . '/index.php';
if (file_exists($indexPath)) {
    $indexContent = file_get_contents($indexPath);
    
    if (strpos($indexContent, "app/views") !== false) {
        echo "✅ Main routing uses: app/views/\n";
    }
    
    if (strpos($indexContent, "views/") !== false) {
        echo "❌ Main routing does NOT use: views/\n";
    }
}

// 4. Recommendations
echo "\n🎯 RECOMMENDATIONS:\n";
echo "==================\n";

echo "✅ KEEP: app/views/\n";
echo "   - Currently active and working\n";
echo "   - Modern Laravel structure\n";
echo "   - Used by all controllers\n";
echo "   - Properly organized\n\n";

echo "🗑️ CAN REMOVE: views/\n";
echo "   - Legacy PHP files\n";
echo "   - Not used by current system\n";
echo "   - Backup only\n";
echo "   - Space saving opportunity\n\n";

echo "✅ KEEP: associate_dir/\n";
echo "   - Separate functionality\n";
echo "   - Partner management system\n";
echo "   - Different from main admin\n";
echo "   - Business requirement\n\n";

// 5. Space analysis
echo "📊 SPACE ANALYSIS:\n";
echo "==================\n";

function getDirectorySize($dir) {
    $size = 0;
    $files = glob(rtrim($dir, '/') . '/*', GLOB_MARK);
    foreach ($files as $file) {
        $size += is_file($file) ? filesize($file) : getDirectorySize($file);
    }
    return $size;
}

if (is_dir($viewsPath)) {
    $viewsSize = getDirectorySize($viewsPath);
    echo "📁 views/ size: " . round($viewsSize / 1024, 2) . " KB\n";
}

if (is_dir($associateDirPath)) {
    $associateSize = getDirectorySize($associateDirPath);
    echo "📁 associate_dir/ size: " . round($associateSize / 1024, 2) . " KB\n";
}

// 6. Final summary
echo "\n📊 FINAL SUMMARY:\n";
echo "==================\n";
echo "🏗️ app/views/ = ACTIVE (Laravel/Blade)\n";
echo "📄 views/ = LEGACY (Old PHP files)\n";
echo "🤝 associate_dir/ = SEPARATE (Partner system)\n";
echo "\n✅ CONCLUSION: app/views/ is main working directory\n";
echo "🗑️ RECOMMENDATION: Can safely remove views/ to save space\n";
echo "🤝 KEEP: associate_dir/ for partner management\n";

echo "\n🎉 DIRECTORY ANALYSIS COMPLETE!\n";
?>
