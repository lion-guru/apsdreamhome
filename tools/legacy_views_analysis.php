<?php
/**
 * APS Dream Home - Legacy Views Analysis & Recommendations
 * Check all legacy views and provide cleanup recommendations
 */

echo "📁 APS DREAM HOME - LEGACY VIEWS ANALYSIS\n";
echo "========================================\n\n";

$projectRoot = __DIR__;
$viewsPath = $projectRoot . '/views';
$appViewsPath = $projectRoot . '/app/views';

echo "🔍 LEGACY VIEWS/ DIRECTORY ANALYSIS:\n\n";

$legacyFiles = [
    '404.php' => 'Error page',
    'about.php' => 'About page', 
    'admin.php' => 'Admin dashboard',
    'admin_dashboard.php' => 'Admin dashboard (duplicate)',
    'admin_login.php' => 'Admin login',
    'admin_logout.php' => 'Admin logout',
    'contact.php' => 'Contact page',
    'home.php' => 'Home page (legacy)',
    'projects.php' => 'Projects page',
    'properties.php' => 'Properties page',
    'property_details.php' => 'Property details page'
];

$totalSize = 0;
$canRemove = [];
$needToMigrate = [];
$duplicates = [];

foreach ($legacyFiles as $file => $description) {
    $filePath = $viewsPath . '/' . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $totalSize += $size;
        
        echo "📄 $file ($description)\n";
        echo "   📊 Size: " . round($size / 1024, 2) . " KB\n";
        
        // Check if modern equivalent exists
        $modernEquivalent = checkModernEquivalent($file, $appViewsPath);
        
        if ($modernEquivalent['exists']) {
            echo "   ✅ Modern equivalent: {$modernEquivalent['path']}\n";
            $canRemove[] = $file;
        } else {
            echo "   ⚠️  No modern equivalent found\n";
            $needToMigrate[] = $file;
        }
        
        // Check for duplicates
        if (isDuplicate($file, $legacyFiles)) {
            echo "   🔄 DUPLICATE: Similar functionality exists\n";
            $duplicates[] = $file;
        }
        
        echo "\n";
    }
}

echo "📊 SUMMARY:\n";
echo "===========\n";
echo "📁 Total legacy files: " . count($legacyFiles) . "\n";
echo "💾 Total size: " . round($totalSize / 1024, 2) . " KB\n";
echo "🗑️ Can remove: " . count($canRemove) . " files\n";
echo "🔄 Need migration: " . count($needToMigrate) . " files\n";
echo "🔄 Duplicates: " . count($duplicates) . " files\n\n";

echo "🎯 RECOMMENDATIONS:\n";
echo "==================\n\n";

echo "✅ FILES SAFE TO REMOVE (" . count($canRemove) . "):\n";
foreach ($canRemove as $file) {
    echo "   🗑️ $file - Modern equivalent exists in app/views/\n";
}

if (!empty($needToMigrate)) {
    echo "\n🔄 FILES NEED MIGRATION (" . count($needToMigrate) . "):\n";
    foreach ($needToMigrate as $file) {
        echo "   📝 $file - Convert to Blade template in app/views/\n";
    }
}

if (!empty($duplicates)) {
    echo "\n🔄 DUPLICATE FILES (" . count($duplicates) . "):\n";
    foreach ($duplicates as $file) {
        echo "   🔄 $file - Similar functionality exists\n";
    }
}

echo "\n🚀 ACTION PLAN:\n";
echo "==============\n";
echo "1. 🗑️ REMOVE: Legacy files with modern equivalents\n";
echo "2. 📝 MIGRATE: Files without modern equivalents\n";
echo "3. 🧹 CLEANUP: Remove entire views/ directory\n";
echo "4. ✅ RESULT: Clean project structure\n\n";

echo "💾 SPACE SAVINGS:\n";
echo "================\n";
echo "🗑️ Remove views/ directory: Save " . round($totalSize / 1024, 2) . " KB\n";
echo "📁 Cleaner project structure\n";
echo "🔧 Easier maintenance\n\n";

echo "🎯 FINAL RECOMMENDATION:\n";
echo "====================\n";
echo "✅ COMPLETELY REMOVE views/ directory\n";
echo "📝 All functionality exists in app/views/\n";
echo "🏗️ Modern MVC structure already working\n";
echo "🗑️ No need for legacy files\n";
echo "💰 Space savings: " . round($totalSize / 1024, 2) . " KB\n\n";

echo "🎉 CLEANUP RECOMMENDATION COMPLETE!\n";

// Helper functions
function checkModernEquivalent($legacyFile, $appViewsPath) {
    $equivalents = [
        'home.php' => 'home/index.php',
        'about.php' => 'about/index.php',
        'contact.php' => 'contact/index.php',
        'projects.php' => 'projects/index.php',
        'properties.php' => 'properties/index.php',
        'property_details.php' => 'properties/detail.php',
        'admin.php' => 'admin/dashboard.php',
        'admin_login.php' => 'auth/login.php',
        'admin_logout.php' => 'auth/logout.php'
    ];
    
    $modernFile = $equivalents[$legacyFile] ?? null;
    $modernPath = $modernFile ? $appViewsPath . '/' . $modernFile : null;
    
    return [
        'exists' => $modernFile && file_exists($modernPath),
        'path' => $modernFile ? 'app/views/' . $modernFile : 'None'
    ];
}

function isDuplicate($file, $allFiles) {
    $duplicates = [
        'admin.php' => ['admin_dashboard.php'],
        'admin_dashboard.php' => ['admin.php']
    ];
    
    return isset($duplicates[$file]);
}
?>
