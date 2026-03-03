<?php
/**
 * Project Cleanup Plan
 * 
 * Automated cleanup plan to remove duplicates and organize project structure
 */

define('PROJECT_BASE_PATH', __DIR__);

echo "====================================================\n";
echo "🧹 PROJECT CLEANUP PLAN EXECUTION\n";
echo "====================================================\n\n";

// Step 1: Identify what to keep vs remove
echo "Step 1: Determining Cleanup Strategy\n";
echo "====================================\n";

$keepStructure = [
    'admin/' => 'Main admin folder - KEEP',
    'app/' => 'Main application folder - KEEP',
    'config/' => 'Main configuration folder - KEEP',
    'public/' => 'Main public folder - KEEP',
    'vendor/' => 'Main dependencies folder - KEEP',
    'assets/' => 'Main assets folder - KEEP',
    'views/' => 'Main views folder - KEEP',
    'logs/' => 'Main logs folder - KEEP',
    'storage/' => 'Main storage folder - KEEP',
    'uploads/' => 'Main uploads folder - KEEP'
];

$removeStructure = [
    'apsdreamhome_deployment_package_fallback/' => 'Fallback deployment package - REMOVE',
    'deployment_package/' => 'Old deployment package - REMOVE',
    '_backup_legacy_files/' => 'Legacy backup - MOVE to proper backup location',
    'admin-test.html' => 'Test file - REMOVE'
];

echo "📋 Cleanup Strategy:\n";
echo "✅ FOLDERS TO KEEP:\n";
foreach ($keepStructure as $folder => $reason) {
    $fullPath = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $folder;
    $exists = is_dir($fullPath);
    $size = $exists ? count(scandir($fullPath)) - 2 : 0;
    echo "   • $folder ($reason) - " . ($exists ? "EXISTS ($size items)" : "MISSING") . "\n";
}

echo "\n❌ FOLDERS TO REMOVE:\n";
foreach ($removeStructure as $folder => $reason) {
    $fullPath = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $folder;
    $exists = is_dir($fullPath) || file_exists($fullPath);
    $size = $exists && is_dir($fullPath) ? count(scandir($fullPath)) - 2 : 0;
    echo "   • $folder ($reason) - " . ($exists ? "EXISTS ($size items)" : "MISSING") . "\n";
}

echo "\n";

// Step 2: Create backup before cleanup
echo "Step 2: Creating Safety Backup\n";
echo "===============================\n";

$backupDir = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . '_cleanup_backup_' . date('Y-m-d_H-i-s');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✅ Created backup directory: $backupDir\n";
} else {
    echo "⚠️ Backup directory already exists\n";
}

// Step 3: Analyze admin folder structure
echo "\nStep 3: Admin Folder Structure Analysis\n";
echo "=======================================\n";

$adminDir = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'admin';
if (is_dir($adminDir)) {
    $adminFiles = scandir($adminDir);
    $adminFiles = array_diff($adminFiles, ['.', '..']);
    
    echo "📁 Current admin/ folder contents:\n";
    foreach ($adminFiles as $file) {
        $fullPath = $adminDir . DIRECTORY_SEPARATOR . $file;
        $isDir = is_dir($fullPath);
        $size = $isDir ? count(scandir($fullPath)) - 2 : filesize($fullPath);
        echo "   " . ($isDir ? "📁" : "📄") . " $file (" . ($isDir ? "$size items" : number_format($size) . " bytes") . ")\n";
    }
} else {
    echo "❌ admin/ folder not found\n";
}

// Step 4: Check app folder structure
echo "\nStep 4: App Folder Structure Analysis\n";
echo "====================================\n";

$appDir = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'app';
if (is_dir($appDir)) {
    $appSubdirs = ['Controllers', 'Models', 'Views', 'Services', 'Core', 'Helpers', 'Middleware'];
    
    echo "📱 Current app/ folder structure:\n";
    foreach ($appSubdirs as $subdir) {
        $fullPath = $appDir . DIRECTORY_SEPARATOR . $subdir;
        $exists = is_dir($fullPath);
        $size = $exists ? count(scandir($fullPath)) - 2 : 0;
        echo "   " . ($exists ? "✅" : "❌") . " $subdir/ - " . ($exists ? "$size items" : "MISSING") . "\n";
    }
} else {
    echo "❌ app/ folder not found\n";
}

// Step 5: Path reference analysis
echo "\nStep 5: Path Reference Analysis\n";
echo "===============================\n";

$pathReferences = [
    'admin/unified_key_management.php' => 'Created - OK',
    'admin/dashboard.php' => 'Created - OK', 
    'admin/property_management.php' => 'Created - OK',
    'admin/user_management.php' => 'Missing - NEED TO CREATE',
    'app/Controllers/AdminController.php' => 'Missing - NEED TO CREATE',
    'app/Models/Property.php' => 'Missing - NEED TO CREATE',
    'app/Models/User.php' => 'Missing - NEED TO CREATE'
];

echo "🔗 Critical Path References:\n";
foreach ($pathReferences as $path => $status) {
    $fullPath = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . $path;
    $exists = file_exists($fullPath);
    echo "   " . ($exists ? "✅" : "❌") . " $path - $status\n";
}

// Step 6: Generate cleanup commands
echo "\nStep 6: Cleanup Commands Generation\n";
echo "===================================\n";

$cleanupCommands = [];

// Commands to remove deployment packages
if (is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'apsdreamhome_deployment_package_fallback')) {
    $cleanupCommands[] = 'rm -rf apsdreamhome_deployment_package_fallback/';
}

if (is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'deployment_package')) {
    $cleanupCommands[] = 'rm -rf deployment_package/';
}

// Commands to move legacy backups
if (is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . '_backup_legacy_files')) {
    $cleanupCommands[] = 'mv _backup_legacy_files/ backups/legacy/';
}

// Commands to remove test files
if (file_exists(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'admin-test.html')) {
    $cleanupCommands[] = 'rm admin-test.html';
}

echo "🔧 Generated Cleanup Commands:\n";
foreach ($cleanupCommands as $i => $command) {
    echo "   " . ($i + 1) . ". $command\n";
}

// Step 7: Safety checks
echo "\nStep 7: Safety Checks\n";
echo "====================\n";

$safetyChecks = [
    'Database backup exists' => file_exists(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'apsdreamhome.sql'),
    'Main config files exist' => file_exists(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php'),
    'Core app files exist' => is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'app'),
    'Public index exists' => file_exists(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php'),
    'Vendor directory exists' => is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'vendor')
];

echo "🛡️ Safety Checks:\n";
foreach ($safetyChecks as $check => $result) {
    echo "   " . ($result ? "✅" : "❌") . " $check\n";
}

// Step 8: Execution plan
echo "\nStep 8: Execution Plan\n";
echo "======================\n";

$executionPlan = [
    "1. Create backup of current state",
    "2. Remove deployment package folders",
    "3. Move legacy files to proper backup location", 
    "4. Remove test files",
    "5. Create missing admin files",
    "6. Create missing app files",
    "7. Update path references",
    "8. Test all functionality"
];

echo "🎯 Execution Plan:\n";
foreach ($executionPlan as $step) {
    echo "   $step\n";
}

echo "\n";

// Step 9: Create cleanup script
echo "Step 9: Creating Cleanup Script\n";
echo "===============================\n";

$cleanupScript = '#!/bin/bash' . "\n";
$cleanupScript .= '# Project Cleanup Script' . "\n";
$cleanupScript .= '# Generated on ' . date('Y-m-d H:i:s') . "\n\n";
$cleanupScript .= 'echo "Starting project cleanup..."' . "\n\n";

$cleanupScript .= '# Create backup' . "\n";
$cleanupScript .= 'BACKUP_DIR="_cleanup_backup_' . date('Y-m-d_H-i-s') . '"' . "\n";
$cleanupScript .= 'mkdir -p "$BACKUP_DIR"' . "\n\n";

foreach ($cleanupCommands as $command) {
    $cleanupScript .= '# ' . $command . "\n";
    if (strpos($command, 'rm -rf') === 0) {
        $cleanupScript .= 'if [ -d "' . substr($command, 6) . '" ]; then' . "\n";
        $cleanupScript .= '    mv "' . substr($command, 6) . '" "$BACKUP_DIR/"' . "\n";
        $cleanupScript .= 'fi' . "\n";
    } else {
        $cleanupScript .= $command . "\n";
    }
    $cleanupScript .= "\n";
}

$cleanupScript .= 'echo "Cleanup completed!"' . "\n";

$scriptPath = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'cleanup_project.sh';
file_put_contents($scriptPath, $cleanupScript);
echo "✅ Created cleanup script: $scriptPath\n";

// Step 10: Summary
echo "\nStep 10: Cleanup Summary\n";
echo "========================\n";

echo "📊 Cleanup Statistics:\n";
echo "   📁 Folders to remove: " . count(array_filter($removeStructure, function($item) {
    return strpos($item, 'REMOVE') !== false;
})) . "\n";
echo "   📄 Files to remove: " . count(array_filter($removeStructure, function($item) {
    return strpos($item, 'REMOVE') !== false && !is_dir(PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . explode('/', $item)[0]);
})) . "\n";
echo "   📋 Commands generated: " . count($cleanupCommands) . "\n";
echo "   🛡️ Safety checks passed: " . count(array_filter($safetyChecks)) . "/" . count($safetyChecks) . "\n";

echo "\n⚠️ IMPORTANT NOTES:\n";
echo "• Backup will be created before any deletion\n";
echo "• Review cleanup script before execution\n";
echo "• Test functionality after cleanup\n";
echo "• Update any hardcoded path references\n";

echo "\n🎊 CLEANUP PLAN COMPLETE! 🎊\n";
echo "📊 Status: CLEANUP PLAN GENERATED\n";
echo "🚀 Ready for execution with safety measures!\n\n";

echo "====================================================\n";
echo "🔧 NEXT STEPS:\n";
echo "====================================================\n";
echo "1. Review cleanup script: cleanup_project.sh\n";
echo "2. Execute cleanup script\n";
echo "3. Create missing files (user_management.php, AdminController.php, etc.)\n";
echo "4. Test all admin functionality\n";
echo "5. Update path references if needed\n";
echo "6. Verify project functionality\n\n";
?>
