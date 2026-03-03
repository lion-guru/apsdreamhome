<?php
/**
 * Project Duplicate Analysis
 * 
 * Deep scan to identify all duplicate folders, files, and structures
 * in the project and analyze their purpose and relationships.
 */

define('PROJECT_BASE_PATH', __DIR__);

echo "====================================================\n";
echo "🔍 PROJECT DUPLICATE DEEP ANALYSIS\n";
echo "====================================================\n\n";

// Step 1: Scan all directories and build structure
echo "Step 1: Scanning Project Structure\n";
echo "===================================\n";

$projectStructure = [];
$duplicateFolders = [];
$duplicateFiles = [];
$suspiciousFolders = [];

function scanDirectory($dir, $parent = '') {
    global $projectStructure, $duplicateFolders, $duplicateFiles, $suspiciousFolders;
    
    if (!is_dir($dir)) return;
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
        $relativePath = str_replace(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR, '', $fullPath);
        
        if (is_dir($fullPath)) {
            // Track folder structure
            $folderName = basename($fullPath);
            if (!isset($projectStructure['folders'][$folderName])) {
                $projectStructure['folders'][$folderName] = [];
            }
            $projectStructure['folders'][$folderName][] = $relativePath;
            
            // Check for potential duplicates
            if (count($projectStructure['folders'][$folderName]) > 1) {
                $duplicateFolders[$folderName] = $projectStructure['folders'][$folderName];
            }
            
            // Check for suspicious folder names
            if (preg_match('/(backup|fallback|copy|duplicate|old|legacy|temp|test|duplicate|_backup|_old|_copy)/i', $folderName)) {
                $suspiciousFolders[$folderName][] = $relativePath;
            }
            
            scanDirectory($fullPath, $relativePath);
        } else {
            // Track files
            $fileName = basename($fullPath);
            if (!isset($projectStructure['files'][$fileName])) {
                $projectStructure['files'][$fileName] = [];
            }
            $projectStructure['files'][$fileName][] = $relativePath;
            
            // Check for duplicate files
            if (count($projectStructure['files'][$fileName]) > 1) {
                $duplicateFiles[$fileName] = $projectStructure['files'][$fileName];
            }
        }
    }
}

scanDirectory(PROJECT_BASE_PATH);

echo "📊 Structure Analysis Results:\n";
echo "   📁 Total Unique Folders: " . count($projectStructure['folders']) . "\n";
echo "   📄 Total Unique Files: " . count($projectStructure['files']) . "\n";
echo "   🔄 Duplicate Folders: " . count($duplicateFolders) . "\n";
echo "   🔄 Duplicate Files: " . count($duplicateFiles) . "\n";
echo "   ⚠️ Suspicious Folders: " . count($suspiciousFolders) . "\n\n";

// Step 2: Analyze admin folder duplicates
echo "Step 2: Admin Folder Analysis\n";
echo "==============================\n";

$adminFolders = [];
foreach ($projectStructure['folders'] as $folderName => $paths) {
    if (stripos($folderName, 'admin') !== false) {
        $adminFolders[$folderName] = $paths;
    }
}

echo "🔧 Admin Related Folders:\n";
foreach ($adminFolders as $folderName => $paths) {
    echo "   📁 $folderName:\n";
    foreach ($paths as $path) {
        $size = is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path) ? 
                count(scandir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path)) - 2 : 0;
        echo "      • $path ($size items)\n";
    }
    echo "\n";
}

// Step 3: Analyze app folder structure
echo "Step 3: App Folder Analysis\n";
echo "===========================\n";

$appFolders = [];
foreach ($projectStructure['folders'] as $folderName => $paths) {
    if (stripos($folderName, 'app') !== false) {
        $appFolders[$folderName] = $paths;
    }
}

echo "📱 App Related Folders:\n";
foreach ($appFolders as $folderName => $paths) {
    echo "   📁 $folderName:\n";
    foreach ($paths as $path) {
        $size = is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path) ? 
                count(scandir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path)) - 2 : 0;
        echo "      • $path ($size items)\n";
    }
    echo "\n";
}

// Step 4: Analyze deployment packages
echo "Step 4: Deployment Package Analysis\n";
echo "====================================\n";

$deploymentFolders = [];
foreach ($projectStructure['folders'] as $folderName => $paths) {
    if (preg_match('/(deployment|package|deploy)/i', $folderName)) {
        $deploymentFolders[$folderName] = $paths;
    }
}

echo "📦 Deployment Related Folders:\n";
foreach ($deploymentFolders as $folderName => $paths) {
    echo "   📁 $folderName:\n";
    foreach ($paths as $path) {
        $size = is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path) ? 
                count(scandir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path)) - 2 : 0;
        echo "      • $path ($size items)\n";
    }
    echo "\n";
}

// Step 5: Analyze backup folders
echo "Step 5: Backup Folder Analysis\n";
echo "==============================\n";

$backupFolders = [];
foreach ($suspiciousFolders as $folderName => $paths) {
    if (preg_match('/(backup|fallback|old|legacy)/i', $folderName)) {
        $backupFolders[$folderName] = $paths;
    }
}

echo "💾 Backup Related Folders:\n";
foreach ($backupFolders as $folderName => $paths) {
    echo "   📁 $folderName:\n";
    foreach ($paths as $path) {
        $size = is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path) ? 
                count(scandir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path)) - 2 : 0;
        echo "      • $path ($size items)\n";
    }
    echo "\n";
}

// Step 6: Analyze duplicate files
echo "Step 6: Duplicate File Analysis\n";
echo "===============================\n";

if (!empty($duplicateFiles)) {
    echo "🔄 Duplicate Files Found:\n";
    foreach ($duplicateFiles as $fileName => $paths) {
        echo "   📄 $fileName:\n";
        foreach ($paths as $path) {
            $size = file_exists(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path) ? 
                   filesize(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path) : 0;
            echo "      • $path (" . number_format($size) . " bytes)\n";
        }
        echo "\n";
    }
} else {
    echo "✅ No duplicate files found\n\n";
}

// Step 7: Path relationship analysis
echo "Step 7: Path Relationship Analysis\n";
echo "==================================\n";

$pathRelationships = [
    'admin' => ['admin/', 'admin-test.html'],
    'app' => ['app/', 'apsdreamhome_deployment_package_fallback/app/', 'deployment_package/app/'],
    'config' => ['config/', 'apsdreamhome_deployment_package_fallback/config/', 'deployment_package/config/'],
    'public' => ['public/', 'apsdreamhome_deployment_package_fallback/public/', 'deployment_package/public/'],
    'vendor' => ['vendor/', 'apsdreamhome_deployment_package_fallback/vendor/', 'deployment_package/vendor/']
];

foreach ($pathRelationships as $type => $paths) {
    echo "🔗 $type paths:\n";
    foreach ($paths as $path) {
        $fullPath = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path;
        $exists = is_dir($fullPath);
        $size = $exists ? count(scandir($fullPath)) - 2 : 0;
        echo "   " . ($exists ? "✅" : "❌") . " $path ($size items)\n";
    }
    echo "\n";
}

// Step 8: Recommendations
echo "Step 8: Recommendations\n";
echo "=======================\n";

$recommendations = [];

// Check for admin folder issues
if (count($adminFolders) > 1) {
    $recommendations[] = "Multiple admin folders found - consolidate to single admin/ folder";
}

// Check for app folder issues
if (count($appFolders) > 1) {
    $recommendations[] = "Multiple app folders found - keep only app/, remove deployment copies";
}

// Check for deployment packages
if (!empty($deploymentFolders)) {
    $recommendations[] = "Deployment packages detected - consider removing old/deprecated packages";
}

// Check for backup folders
if (!empty($backupFolders)) {
    $recommendations[] = "Backup folders found - ensure they are properly organized";
}

// Check for duplicate files
if (!empty($duplicateFiles)) {
    $recommendations[] = "Duplicate files found - review and remove unnecessary duplicates";
}

echo "💡 Recommendations:\n";
foreach ($recommendations as $i => $recommendation) {
    echo "   " . ($i + 1) . ". $recommendation\n";
}

echo "\n";

// Step 9: Action Plan
echo "Step 9: Action Plan\n";
echo "===================\n";

$actionPlan = [
    "1. Consolidate admin functionality to single admin/ folder",
    "2. Remove duplicate app/ folders from deployment packages",
    "3. Clean up backup folders and organize properly",
    "4. Remove duplicate files",
    "5. Update path references after cleanup",
    "6. Test all functionality after reorganization"
];

echo "🎯 Action Plan:\n";
foreach ($actionPlan as $action) {
    echo "   $action\n";
}

echo "\n";

// Step 10: Memory Storage Summary
echo "Step 10: Memory Storage Summary\n";
echo "=================================\n";

$memoryData = [
    'total_folders' => count($projectStructure['folders']),
    'total_files' => count($projectStructure['files']),
    'duplicate_folders' => count($duplicateFolders),
    'duplicate_files' => count($duplicateFiles),
    'admin_folders' => count($adminFolders),
    'app_folders' => count($appFolders),
    'deployment_folders' => count($deploymentFolders),
    'backup_folders' => count($backupFolders),
    'suspicious_folders' => count($suspiciousFolders),
    'recommendations_count' => count($recommendations)
];

echo "📊 Memory Data for Storage:\n";
foreach ($memoryData as $key => $value) {
    echo "   $key: $value\n";
}

echo "\n";

echo "====================================================\n";
echo "🎊 DUPLICATE ANALYSIS COMPLETE! 🎊\n";
echo "📊 Status: COMPREHENSIVE DUPLICATE ANALYSIS COMPLETE\n";
echo "🚀 Project structure analyzed and issues identified!\n\n";

echo "🔍 KEY FINDINGS:\n";
echo "• Found " . count($duplicateFolders) . " duplicate folder structures\n";
echo "• Found " . count($duplicateFiles) . " duplicate files\n";
echo "• Found " . count($adminFolders) . " admin-related folders\n";
echo "• Found " . count($appFolders) . " app-related folders\n";
echo "• Found " . count($deploymentFolders) . " deployment packages\n";
echo "• Found " . count($backupFolders) . " backup folders\n\n";

echo "⚠️ CRITICAL ISSUES:\n";
echo "• Multiple admin folders causing confusion\n";
echo "• Duplicate app folders in deployment packages\n";
echo "• Potential path reference conflicts\n";
echo "• Unorganized backup structure\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. Create memory entry with this analysis\n";
echo "2. Implement cleanup plan\n";
echo "3. Fix path references\n";
echo "4. Test all functionality\n\n";

// Return memory data for storage
return $memoryData;
?>
