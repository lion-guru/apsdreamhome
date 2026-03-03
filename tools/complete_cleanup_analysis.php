<?php
/**
 * APS Dream Home - Complete Project Cleanup Analysis
 * Check what else can be cleaned up for better organization
 */

echo "🧹 APS DREAM HOME - COMPLETE PROJECT CLEANUP ANALYSIS\n";
echo "================================================\n\n";

$projectRoot = __DIR__;

echo "🔍 POTENTIAL CLEANUP CATEGORIES:\n\n";

// 1. Documentation files
echo "📄 DOCUMENTATION FILES ANALYSIS:\n";
echo "================================\n";

$docFiles = [
    'API_ROUTING_CRITICAL_UPDATE.md',
    'API_ROUTING_ISSUE_ANALYSIS.md', 
    'API_ROUTING_RESOLUTION_SUCCESS.md',
    'COMPLETE_PROJECT_UNDERSTANDING.md',
    'CO_WORKER_DEPLOYMENT_STATUS.md',
    'CO_WORKER_FINAL_DEPLOYMENT_STATUS.md',
    'DATABASE_RESTORE_REPORT.md',
    'DELETED_FILES_REPORT.md',
    'DEPLOYMENT.md',
    'DEPLOYMENT_100_PERCENT_SUCCESS.md',
    'DEPLOYMENT_PACKAGE_ZARURAT_ANALYSIS.md',
    'DEVELOPMENT_ROADMAP.md',
    'ERROR_FIX_REPORT.md',
    'ESSENTIAL_TASKS_COMPLETED.md',
    'FINAL_DEPLOYMENT_STATUS.md',
    'FINAL_SYSTEM_VERIFICATION_REPORT.json',
    'FOLDER_DEEP_SCAN_REPORT.md',
    'GITHUB_INTEGRATION_GUIDE.md',
    'GITHUB_ZARURAT_ANALYSIS.md',
    'MCP_INTEGRATION_COMPLETE.md',
    'MCP_SERVERS_INSTALLATION_REPORT.md',
    'MCP_TOOLS_ADDITION_GUIDE.md',
    'MULTI_SYSTEM_DEPLOYMENT_COMPLETE.md',
    'MULTI_SYSTEM_DEPLOYMENT_GUIDE.md',
    'NEXT_STEPS_ROADMAP.md',
    'PHASE_2_DAY_1_ADMIN_SUCCESS.md',
    'PHASE_2_DAY_1_BENEFITS.md',
    'PHASE_2_DAY_1_EXECUTION.md',
    'PHASE_2_DAY_2_ADMIN_SYSTEM_COMPLETE.md',
    'PHASE_2_DAY_2_COMPLETE_SUCCESS.md',
    'PHASE_2_DAY_2_COMPREHENSIVE_TESTING_PLAN.md',
    'PHASE_2_DAY_2_EXECUTION.md',
    'PHASE_2_DAY_2_EXECUTION_START.md',
    'PHASE_2_DAY_2_MAJOR_PROGRESS.md',
    'PHASE_2_DAY_2_TESTING_RESULTS.md',
    'PHASE_2_PRODUCTION_OPTIMIZATION.md',
    'PHASE_3_COMPLETE_SUCCESS.md',
    'PRODUCTION_DEPLOYMENT_GUIDE.md',
    'PROJECT_ANALYSIS_REPORT.md',
    'PROJECT_COMPLETION_CERTIFICATE_FINAL.md',
    'PROJECT_COMPLETION_REPORT.md',
    'PROJECT_DEEP_ANALYSIS.md',
    'PROJECT_HANDOVER_GUIDE.md',
    'PROJECT_PROGRESS.json',
    'PROJECT_STRATEGIC_PLAN.md',
    'RECOVERY_PROGRESS_REPORT.md',
    'ROOT_CAUSE_ANALYSIS.md',
    'ROUTING_ANALYSIS.md',
    'SETUP.md',
    'SQLITE_MCP_INSTALLATION_GUIDE.md',
    'SYSTEM_STATUS_COMPLETE.md',
    'SYSTEM_STATUS_REPORT.md',
    'ULTIMATE_AUTONOMOUS_SUCCESS.md',
    'ULTIMATE_COMPLETION_REPORT.md',
    'ULTIMATE_ENTERPRISE_CERTIFIED.md',
    'ULTIMATE_FINAL_SUMMARY.md',
    'ULTIMATE_MISSION_COMPLETION.md',
    'ULTIMATE_PERFORMANCE_OPTIMIZED.md',
    'ULTIMATE_PRODUCTION_READY_SUCCESS.md',
    'ULTIMATE_PROJECT_COMPLETION_SUMMARY.md',
    'ULTIMATE_TRANSCENDENCE.md'
];

$totalDocSize = 0;
$docCount = 0;

foreach ($docFiles as $file) {
    $filePath = $projectRoot . '/' . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $totalDocSize += $size;
        $docCount++;
        echo "📄 $file (" . round($size / 1024, 2) . " KB)\n";
    }
}

echo "\n📊 DOCUMENTATION SUMMARY:\n";
echo "   📁 Total documentation files: $docCount\n";
echo "   💾 Total size: " . round($totalDocSize / 1024, 2) . " KB\n";
echo "   🗑️ Can be moved to docs/ directory\n\n";

// 2. Analysis files
echo "🔍 ANALYSIS FILES:\n";
echo "==================\n";

$analysisFiles = [
    'directory_structure_analysis.php',
    'legacy_views_analysis.php', 
    'mvc_pattern_analysis.php',
    'complete_ide_error_fix.php',
    'final_ide_verification.php',
    'fix_all_ide_errors.php',
    'fix_syntax_errors.php',
    'mcp_ide_integration.php',
    'secure_key_management.php',
    'simple_validator.php',
    'unified_key_integration.php'
];

$analysisSize = 0;
$analysisCount = 0;

foreach ($analysisFiles as $file) {
    $filePath = $projectRoot . '/' . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $analysisSize += $size;
        $analysisCount++;
        echo "🔍 $file (" . round($size / 1024, 2) . " KB)\n";
    }
}

echo "\n📊 ANALYSIS SUMMARY:\n";
echo "   🔍 Total analysis files: $analysisCount\n";
echo "   💾 Total size: " . round($analysisSize / 1024, 2) . " KB\n";
echo "   🗑️ Can be moved to tools/ directory\n\n";

// 3. Test files
echo "🧪 TEST FILES:\n";
echo "==============\n";

$testFiles = [
    'admin-test.html',
    'api-test.html',
    'api_test_report.json',
    'auth-test.html',
    'routing-test.html',
    'test-system.js',
    'test_results.json',
    'test-powershell-autosync.bat',
    'powershell-test.bat',
    'powershell-fixed-simple.bat'
];

$testSize = 0;
$testCount = 0;

foreach ($testFiles as $file) {
    $filePath = $projectRoot . '/' . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $testSize += $size;
        $testCount++;
        echo "🧪 $file (" . round($size / 1024, 2) . " KB)\n";
    }
}

echo "\n📊 TEST FILES SUMMARY:\n";
echo "   🧪 Total test files: $testCount\n";
echo "   💾 Total size: " . round($testSize / 1024, 2) . " KB\n";
echo "   🗑️ Can be moved to tests/ directory\n\n";

// 4. Backup files
echo "💾 BACKUP FILES:\n";
echo "================\n";

$backupFiles = [
    '.env.backup.2026-02-22-19-44-05',
    '.htaccess.optimized',
    '.htaccess.production',
    'apsdreamhome (6).sql',
    'database_production_setup.sql',
    'property_details.php.backup.2026-02-22-19-56-15',
    'property_details.php.backup.2026-02-25-07-31-16'
];

$backupSize = 0;
$backupCount = 0;

foreach ($backupFiles as $file) {
    $filePath = $projectRoot . '/' . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        $backupSize += $size;
        $backupCount++;
        echo "💾 $file (" . round($size / 1024, 2) . " KB)\n";
    }
}

echo "\n📊 BACKUP SUMMARY:\n";
echo "   💾 Total backup files: $backupCount\n";
echo "   💾 Total size: " . round($backupSize / 1024, 2) . " KB\n";
echo "   🗑️ Can be moved to backups/ directory\n\n";

// 5. Empty directories
echo "📁 EMPTY DIRECTORIES:\n";
echo "====================\n";

$emptyDirs = [
    'backup_cleanup/',
    'backups/',
    'co_worker_uploads/',
    'dev_tools/',
    'docs/',
    'reports/',
    'temp/',
    'uploads/'
];

foreach ($emptyDirs as $dir) {
    $dirPath = $projectRoot . '/' . $dir;
    if (is_dir($dirPath)) {
        $items = glob($dirPath . '/*');
        $itemCount = count($items);
        echo "📁 $dir (" . $itemCount . " items)\n";
        if ($itemCount == 0) {
            echo "   🗑️ Can be removed\n";
        }
    }
}

// 6. Configuration duplicates
echo "⚙️ CONFIGURATION FILES:\n";
echo "========================\n";

$configFiles = [
    '.htaccess',
    '.htaccess.optimized',
    '.htaccess.production',
    'nginx-production.conf',
    'nginx.production.conf',
    'vite.config.js',
    'vite.config.enhanced.js'
];

foreach ($configFiles as $file) {
    $filePath = $projectRoot . '/' . $file;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        echo "⚙️ $file (" . round($size / 1024, 2) . " KB)\n";
    }
}

// 7. Summary
echo "\n🎯 CLEANUP RECOMMENDATIONS:\n";
echo "==========================\n\n";

$totalCleanupSize = $totalDocSize + $analysisSize + $testSize + $backupSize;

echo "📊 TOTAL CLEANUP POTENTIAL:\n";
echo "   📄 Documentation: $docCount files (" . round($totalDocSize / 1024, 2) . " KB)\n";
echo "   🔍 Analysis: $analysisCount files (" . round($analysisSize / 1024, 2) . " KB)\n";
echo "   🧪 Tests: $testCount files (" . round($testSize / 1024, 2) . " KB)\n";
echo "   💾 Backups: $backupCount files (" . round($backupSize / 1024, 2) . " KB)\n";
echo "   💾 Total potential savings: " . round($totalCleanupSize / 1024, 2) . " KB\n\n";

echo "🚀 RECOMMENDED ACTIONS:\n";
echo "======================\n";
echo "1. 📁 CREATE: docs/ directory\n";
echo "2. 📁 MOVE: All .md documentation files to docs/\n";
echo "3. 📁 CREATE: tools/ directory\n";
echo "4. 📁 MOVE: All analysis .php files to tools/\n";
echo "5. 📁 MOVE: All test files to tests/\n";
echo "6. 📁 MOVE: All backup files to backups/\n";
echo "7. 🗑️ REMOVE: Empty directories\n";
echo "8. ⚙️ CLEAN: Duplicate configuration files\n\n";

echo "🎯 FINAL BENEFITS:\n";
echo "==================\n";
echo "💾 Space savings: " . round($totalCleanupSize / 1024, 2) . " KB\n";
echo "📁 Better organization\n";
echo "🔧 Easier maintenance\n";
echo "📱 Cleaner project structure\n";
echo "⚡ Faster development\n\n";

echo "🎉 CLEANUP ANALYSIS COMPLETE!\n";
?>
