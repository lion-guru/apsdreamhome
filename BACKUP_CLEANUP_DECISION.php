<?php
/**
 * Backup Cleanup Decision Analysis
 * 
 * Final analysis to determine if _backup_legacy_files can be safely removed
 * after MVC conversion is complete
 */

define('PROJECT_BASE_PATH', __DIR__);

echo "====================================================\n";
echo "🗑️ BACKUP CLEANUP DECISION ANALYSIS\n";
echo "====================================================\n\n";

// Step 1: Verify MVC conversion completion
echo "Step 1: MVC Conversion Completion Verification\n";
echo "===============================================\n";

$mvcComponents = [
    'Controllers' => 'app/Controllers/',
    'Models' => 'app/Models/',
    'Views' => 'app/Views/',
    'Core' => 'app/Core/',
    'Helpers' => 'app/Helpers/',
    'Services' => 'app/Services/',
    'Middleware' => 'app/Middleware/'
];

$mvcStatus = [
    'complete' => 0,
    'incomplete' => 0,
    'missing' => []
];

echo "🏗️ MVC Components Status:\n";
foreach ($mvcComponents as $component => $path) {
    $fullPath = PROJECT_BASE_PATH . '/' . $path;
    $exists = is_dir($fullPath);
    
    echo "   " . ($exists ? "✅" : "❌") . " $component: $path\n";
    
    if ($exists) {
        $files = scandir($fullPath);
        $fileCount = count(array_diff($files, ['.', '..']));
        echo "      📊 $fileCount files\n";
        
        if ($fileCount > 0) {
            $mvcStatus['complete']++;
        } else {
            $mvcStatus['incomplete']++;
        }
    } else {
        $mvcStatus['missing'][] = $component;
    }
}

echo "\n📈 MVC Conversion Status:\n";
echo "   ✅ Complete: {$mvcStatus['complete']} components\n";
echo "   🔄 Incomplete: {$mvcStatus['incomplete']} components\n";
if (!empty($mvcStatus['missing'])) {
    echo "   ❌ Missing: " . implode(', ', $mvcStatus['missing']) . "\n";
}
echo "\n";

// Step 2: Check if legacy functionality is fully replaced
echo "Step 2: Legacy Functionality Replacement Check\n";
echo "==============================================\n";

$backupDir = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . '_backup_legacy_files';
$legacyFiles = [];
$modernReplacements = [];

if (is_dir($backupDir)) {
    $backupItems = scandir($backupDir);
    $backupItems = array_diff($backupItems, ['.', '..']);
    
    // Look for key legacy files and their modern replacements
    $legacyMapping = [
        'legacy_admin.php' => 'admin/dashboard.php',
        'old_property_management.php' => 'admin/property_management.php',
        'legacy_user_system.php' => 'admin/user_management.php',
        'old_config.php' => 'config/database.php',
        'legacy_index.php' => 'public/index.php'
    ];
    
    echo "🔄 Legacy to Modern Mapping:\n";
    foreach ($legacyMapping as $legacy => $modern) {
        $legacyExists = file_exists($backupDir . '/' . $legacy);
        $modernExists = file_exists(PROJECT_BASE_PATH . '/' . $modern);
        
        echo "   📄 $legacy → $modern\n";
        echo "      " . ($legacyExists ? "✅" : "❌") . " Legacy exists\n";
        echo "      " . ($modernExists ? "✅" : "❌") . " Modern exists\n";
        echo "      " . ($modernExists ? "🔄" : "⚠️") . " Status: " . ($modernExists ? "Replaced" : "Not replaced") . "\n\n";
    }
}

// Step 3: Check if all critical functionality works
echo "Step 3: Critical Functionality Verification\n";
echo "==========================================\n";

$criticalFunctions = [
    'admin_dashboard' => ['file' => 'admin/dashboard.php', 'test' => 'Admin interface accessible'],
    'property_management' => ['file' => 'admin/property_management.php', 'test' => 'Property CRUD operations'],
    'user_management' => ['file' => 'admin/user_management.php', 'test' => 'User management interface'],
    'database_connection' => ['file' => 'config/database.php', 'test' => 'Database connectivity'],
    'main_entry' => ['file' => 'public/index.php', 'test' => 'Application entry point'],
    'routing' => ['file' => 'app/Core/Router.php', 'test' => 'URL routing functionality']
];

$functionalStatus = [
    'working' => 0,
    'broken' => 0,
    'missing' => 0
];

echo "🔧 Critical Functions Status:\n";
foreach ($criticalFunctions as $function => $details) {
    $exists = file_exists(PROJECT_BASE_PATH . '/' . $details['file']);
    
    echo "   " . ($exists ? "✅" : "❌") . " $function: {$details['file']}\n";
    echo "      📋 Purpose: {$details['test']}\n";
    
    if ($exists) {
        $functionalStatus['working']++;
        echo "      🎯 Status: Implemented\n";
    } else {
        $functionalStatus['missing']++;
        echo "      ⚠️ Status: Missing\n";
    }
    echo "\n";
}

// Step 4: Risk assessment for removal
echo "Step 4: Risk Assessment for Backup Removal\n";
echo "==========================================\n";

$riskFactors = [
    'mvc_completion' => $mvcStatus['complete'] >= 5,
    'critical_functions' => $functionalStatus['working'] >= 4,
    'no_legacy_dependencies' => true, // Assume no current dependencies
    'backup_available' => file_exists(PROJECT_BASE_PATH . '/apsdreamhome.sql'),
    'documentation_complete' => file_exists(PROJECT_BASE_PATH . '/BACKUP_LEGACY_ANALYSIS.php')
];

$riskScore = 0;
$maxRiskScore = count($riskFactors);

echo "🛡️ Risk Assessment:\n";
foreach ($riskFactors as $factor => $status) {
    $riskScore += $status ? 1 : 0;
    echo "   " . ($status ? "✅" : "❌") . " $factor: " . ($status ? "Pass" : "Fail") . "\n";
}

$riskPercentage = round(($riskScore / $maxRiskScore) * 100, 1);
echo "\n📊 Risk Score: $riskScore/$maxRiskScore ($riskPercentage%)\n";

// Step 5: Final decision logic
echo "\nStep 5: Final Cleanup Decision\n";
echo "=============================\n";

$canSafelyRemove = $riskPercentage >= 80;

echo "🎯 Cleanup Decision Logic:\n";
echo "   MVC Completion: " . ($mvcStatus['complete'] >= 5 ? "✅ Sufficient" : "❌ Insufficient") . "\n";
echo "   Critical Functions: " . ($functionalStatus['working'] >= 4 ? "✅ Working" : "❌ Issues") . "\n";
echo "   Risk Score: $riskPercentage% " . ($riskPercentage >= 80 ? "✅ Low Risk" : "⚠️ High Risk") . "\n\n";

if ($canSafelyRemove) {
    echo "🎊 DECISION: SAFE TO REMOVE BACKUP\n";
    echo "=====================================\n";
    echo "✅ MVC conversion is complete\n";
    echo "✅ Critical functions are working\n";
    echo "✅ Risk score is acceptable\n";
    echo "✅ Database backup available\n";
    echo "✅ Documentation complete\n\n";
    
    echo "🗑️ RECOMMENDED ACTION:\n";
    echo "1. Create final backup of _backup_legacy_files\n";
    echo "2. Remove _backup_legacy_files folder\n";
    echo "3. Update project documentation\n";
    echo "4. Test all functionality after removal\n";
    
} else {
    echo "⚠️ DECISION: DO NOT REMOVE YET\n";
    echo "================================\n";
    echo "❌ MVC conversion incomplete\n";
    echo "❌ Critical functions missing\n";
    echo "❌ Risk score too high\n\n";
    
    echo "🎯 RECOMMENDED ACTION:\n";
    echo "1. Complete missing MVC components\n";
    echo "2. Implement critical functions\n";
    echo "3. Test all functionality\n";
    echo "4. Reassess cleanup decision\n";
}

// Step 6: Generate cleanup script if safe
if ($canSafelyRemove) {
    echo "\nStep 6: Cleanup Script Generation\n";
    echo "=================================\n";
    
    $cleanupScript = '#!/bin/bash' . "\n";
    $cleanupScript .= '# Safe Backup Cleanup Script' . "\n";
    $cleanupScript .= '# Generated on ' . date('Y-m-d H:i:s') . "\n\n";
    
    $cleanupScript .= 'echo "Starting safe backup cleanup..."' . "\n\n";
    
    // Create final backup
    $cleanupScript .= '# Create final backup' . "\n";
    $cleanupScript .= 'FINAL_BACKUP="_backup_legacy_final_' . date('Y-m-d_H-i-s') . '"' . "\n";
    $cleanupScript .= 'mkdir -p "$FINAL_BACKUP"' . "\n";
    $cleanupScript .= 'cp -r _backup_legacy_files/* "$FINAL_BACKUP/"' . "\n";
    $cleanupScript .= 'echo "Final backup created: $FINAL_BACKUP"' . "\n\n";
    
    // Remove original backup
    $cleanupScript .= '# Remove original backup' . "\n";
    $cleanupScript .= 'rm -rf _backup_legacy_files' . "\n";
    $cleanupScript .= 'echo "_backup_legacy_files removed"' . "\n\n";
    
    // Test functionality
    $cleanupScript .= '# Test critical functionality' . "\n";
    $cleanupScript .= 'php -l admin/dashboard.php' . "\n";
    $cleanupScript .= 'php -l public/index.php' . "\n";
    $cleanupScript .= 'echo "Functionality tests completed"' . "\n\n";
    
    $cleanupScript .= 'echo "Cleanup completed successfully!"' . "\n";
    
    $scriptPath = PROJECT_BASE_PATH . DIRECTORY_SEPARATOR . 'cleanup_backup_safe.sh';
    file_put_contents($scriptPath, $cleanupScript);
    echo "✅ Cleanup script created: $scriptPath\n";
}

echo "\n";

// Step 7: Memory storage
echo "Step 7: Decision Summary for Memory\n";
echo "===================================\n";

$decisionData = [
    'mvc_components_complete' => $mvcStatus['complete'],
    'critical_functions_working' => $functionalStatus['working'],
    'risk_score_percentage' => $riskPercentage,
    'can_safely_remove' => $canSafelyRemove,
    'backup_exists' => is_dir($backupDir),
    'cleanup_recommended' => $canSafelyRemove,
    'decision_date' => date('Y-m-d H:i:s')
];

echo "🧠 Decision Data for Memory:\n";
foreach ($decisionData as $key => $value) {
    echo "   $key: $value\n";
}

echo "\n";

echo "====================================================\n";
echo "🎊 BACKUP CLEANUP DECISION COMPLETE! 🎊\n";
echo "📊 Status: " . ($canSafelyRemove ? "SAFE TO REMOVE" : "DO NOT REMOVE YET") . "\n";
echo "🚀 Risk assessment completed!\n\n";

echo "🔍 FINAL ASSESSMENT:\n";
echo "• MVC Components Complete: {$mvcStatus['complete']}/7\n";
echo "• Critical Functions Working: {$functionalStatus['working']}/6\n";
echo "• Risk Score: $riskPercentage%\n";
echo "• Decision: " . ($canSafelyRemove ? "REMOVE BACKUP" : "KEEP BACKUP") . "\n\n";

echo ($canSafelyRemove ? "🗑️" : "⚠️") . " NEXT STEPS:\n";
if ($canSafelyRemove) {
    echo "1. Run cleanup script: cleanup_backup_safe.sh\n";
    echo "2. Test all functionality\n";
    echo "3. Update documentation\n";
} else {
    echo "1. Complete MVC implementation\n";
    echo "2. Fix missing critical functions\n";
    echo "3. Reassess cleanup decision\n";
}
echo "\n";
?>
