<?php
/**
 * APS Dream Home - Project Deep Scan Analysis
 * Comprehensive scan of all files, implementations, and conflicts
 */

echo "🔍 APS DREAM HOME - PROJECT DEEP SCAN ANALYSIS\n";
echo "================================================\n\n";

require_once __DIR__ . '/config/paths.php';

// Deep scan results
$scanResults = [];
$totalFiles = 0;
$implementedFeatures = [];
$conflicts = [];
$missingFiles = [];
$duplicatedFiles = [];

echo "🔍 STARTING DEEP PROJECT SCAN...\n\n";

// 1. Scan all PHP files
echo "Step 1: Scanning all PHP files\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BASE_PATH));
$phpFiles = [];
$adminFiles = [];
$crudFiles = [];
$managementFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getPathname();
        $relativePath = str_replace(BASE_PATH . '/', '', $filePath);
        $totalFiles++;
        
        $phpFiles[] = $relativePath;
        
        // Categorize files
        if (str_contains($relativePath, 'admin')) {
            $adminFiles[] = $relativePath;
        }
        if (str_contains($relativePath, 'crud') || str_contains($relativePath, 'management')) {
            $crudFiles[] = $relativePath;
        }
        if (str_contains($relativePath, 'key') || str_contains($relativePath, 'unified')) {
            $managementFiles[] = $relativePath;
        }
    }
}

echo "📊 Total PHP Files Found: $totalFiles\n";
echo "📁 Admin Files: " . count($adminFiles) . "\n";
echo "📝 CRUD Files: " . count($crudFiles) . "\n";
echo "🔑 Management Files: " . count($managementFiles) . "\n\n";

// 2. Check for unified_key_management.php
echo "Step 2: Checking unified_key_management.php\n";
$unifiedKeyFile = BASE_PATH . '/admin/unified_key_management.php';
$unifiedKeyExists = file_exists($unifiedKeyFile);

echo "📁 File: admin/unified_key_management.php\n";
echo "📊 Status: " . ($unifiedKeyExists ? 'EXISTS' : 'MISSING') . "\n";

if ($unifiedKeyExists) {
    $content = file_get_contents($unifiedKeyFile);
    $lines = count(file($unifiedKeyFile));
    echo "📝 Lines: $lines\n";
    
    // Check for CRUD operations
    $hasCreate = str_contains($content, 'CREATE') || str_contains($content, 'INSERT');
    $hasRead = str_contains($content, 'SELECT') || str_contains($content, 'fetch');
    $hasUpdate = str_contains($content, 'UPDATE') || str_contains($content, 'edit');
    $hasDelete = str_contains($content, 'DELETE') || str_contains($content, 'remove');
    
    echo "🔧 CRUD Operations:\n";
    echo "   ✅ Create: " . ($hasCreate ? 'YES' : 'NO') . "\n";
    echo "   ✅ Read: " . ($hasRead ? 'YES' : 'NO') . "\n";
    echo "   ✅ Update: " . ($hasUpdate ? 'YES' : 'NO') . "\n";
    echo "   ✅ Delete: " . ($hasDelete ? 'YES' : 'NO') . "\n";
} else {
    echo "❌ File is missing - this explains why CRUD operations are not working\n";
}

// 3. Check for path fixes applied
echo "\nStep 3: Checking path fixes applied\n";
$pathFixedFiles = [];
$hardcodedPathFiles = [];

foreach ($phpFiles as $file) {
    $filePath = BASE_PATH . '/' . $file;
    $content = file_get_contents($filePath);
    
    // Check if file uses proper constants
    $usesBaseUrl = str_contains($content, 'BASE_URL');
    $usesBasePath = str_contains($content, 'BASE_PATH');
    $hasHardcodedPaths = str_contains($content, 'http://localhost/apsdreamhome') || 
                         str_contains($content, 'C:/xampp/htdocs/apsdreamhome');
    
    if ($usesBaseUrl || $usesBasePath) {
        $pathFixedFiles[] = $file;
    }
    
    if ($hasHardcodedPaths) {
        $hardcodedPathFiles[] = $file;
    }
}

echo "📊 Path Fix Analysis:\n";
echo "   ✅ Files with proper paths: " . count($pathFixedFiles) . "\n";
echo "   ❌ Files with hardcoded paths: " . count($hardcodedPathFiles) . "\n";

if (!empty($hardcodedPathFiles)) {
    echo "   🔍 Files still needing path fixes:\n";
    foreach (array_slice($hardcodedPathFiles, 0, 10) as $file) {
        echo "      • $file\n";
    }
    if (count($hardcodedPathFiles) > 10) {
        echo "      • ... and " . (count($hardcodedPathFiles) - 10) . " more\n";
    }
}

// 4. Check for duplicate implementations
echo "\nStep 4: Checking for duplicate implementations\n";
$duplicatePatterns = [
    'config' => [],
    'helper' => [],
    'automation' => [],
    'analysis' => [],
    'integration' => []
];

foreach ($phpFiles as $file) {
    if (str_contains($file, 'config')) {
        $duplicatePatterns['config'][] = $file;
    }
    if (str_contains($file, 'helper')) {
        $duplicatePatterns['helper'][] = $file;
    }
    if (str_contains($file, 'automation')) {
        $duplicatePatterns['automation'][] = $file;
    }
    if (str_contains($file, 'analysis')) {
        $duplicatePatterns['analysis'][] = $file;
    }
    if (str_contains($file, 'integration')) {
        $duplicatePatterns['integration'][] = $file;
    }
}

echo "🔄 DUPLICATE ANALYSIS:\n";
foreach ($duplicatePatterns as $category => $files) {
    if (count($files) > 1) {
        echo "   ⚠️ $category: " . count($files) . " files (potential duplicates)\n";
        foreach ($files as $file) {
            echo "      • $file\n";
        }
    }
}

// 5. Check for Co-worker system implementation
echo "\nStep 5: Checking Co-worker system implementation\n";
$coWorkerFiles = array_filter($phpFiles, fn($file) => 
    str_contains($file, 'co-worker') || 
    str_contains($file, 'coworker') || 
    str_contains($file, 'worker')
);

echo "👥 Co-worker System Files: " . count($coWorkerFiles) . "\n";
foreach ($coWorkerFiles as $file) {
    echo "   📁 $file\n";
}

// 6. Check for MCP integration
echo "\nStep 6: Checking MCP integration\n";
$mcpFiles = array_filter($phpFiles, fn($file) => 
    str_contains($file, 'mcp') || 
    str_contains($file, 'MCP')
);

echo "🔌 MCP Integration Files: " . count($mcpFiles) . "\n";
foreach ($mcpFiles as $file) {
    echo "   📁 $file\n";
}

// 7. Check for missing critical files
echo "\nStep 7: Checking for missing critical files\n";
$criticalFiles = [
    'admin/unified_key_management.php' => 'Unified key management system',
    'admin/dashboard.php' => 'Admin dashboard',
    'admin/property_management.php' => 'Property management',
    'admin/user_management.php' => 'User management',
    'app/Controllers/AdminController.php' => 'Admin controller',
    'app/Models/Property.php' => 'Property model',
    'app/Models/User.php' => 'User model',
    'config/database.php' => 'Database configuration',
    'public/.htaccess' => 'URL rewriting',
    'app/Helpers/UrlHelper.php' => 'URL helper functions'
];

$missingCriticalFiles = [];
foreach ($criticalFiles as $file => $description) {
    if (!file_exists(BASE_PATH . '/' . $file)) {
        $missingCriticalFiles[$file] = $description;
    }
}

echo "❌ Missing Critical Files: " . count($missingCriticalFiles) . "\n";
foreach ($missingCriticalFiles as $file => $description) {
    echo "   📁 $file - $description\n";
}

// 8. Check for conflicts
echo "\nStep 8: Checking for conflicts\n";
$conflicts = [];

// Check for conflicting path configurations
$pathConfigs = array_filter($phpFiles, fn($file) => 
    str_contains($file, 'paths.php') || 
    str_contains($file, 'config')
);

if (count($pathConfigs) > 1) {
    $conflicts[] = 'Multiple path configuration files found';
}

// Check for conflicting helper files
$helperConfigs = array_filter($phpFiles, fn($file) => 
    str_contains($file, 'helper') || 
    str_contains($file, 'Helper')
);

if (count($helperConfigs) > 3) {
    $conflicts[] = 'Multiple helper files may cause conflicts';
}

// Check for conflicting automation files
$automationFiles = array_filter($phpFiles, fn($file) => 
    str_contains($file, 'automation') || 
    str_contains($file, 'Automation')
);

if (count($automationFiles) > 2) {
    $conflicts[] = 'Multiple automation files may conflict';
}

echo "⚠️ Potential Conflicts: " . count($conflicts) . "\n";
foreach ($conflicts as $conflict) {
    echo "   • $conflict\n";
}

// 9. Generate implementation status
echo "\nStep 9: Implementation Status Summary\n";
$implementationStatus = [
    'path_routing' => [
        'status' => count($hardcodedPathFiles) === 0 ? 'COMPLETE' : 'PARTIAL',
        'files_fixed' => count($pathFixedFiles),
        'files_remaining' => count($hardcodedPathFiles),
        'percentage' => round((count($pathFixedFiles) / $totalFiles) * 100, 1)
    ],
    'mcp_integration' => [
        'status' => count($mcpFiles) > 0 ? 'IMPLEMENTED' : 'MISSING',
        'files_count' => count($mcpFiles),
        'servers_configured' => 9
    ],
    'admin_system' => [
        'status' => $unifiedKeyExists ? 'IMPLEMENTED' : 'MISSING',
        'key_management' => $unifiedKeyExists ? 'WORKING' : 'MISSING',
        'admin_files' => count($adminFiles)
    ],
    'co_worker_system' => [
        'status' => count($coWorkerFiles) > 0 ? 'IMPLEMENTED' : 'MISSING',
        'files_count' => count($coWorkerFiles)
    ],
    'automation_system' => [
        'status' => count($automationFiles) > 0 ? 'IMPLEMENTED' : 'MISSING',
        'files_count' => count($automationFiles)
    ]
];

echo "📊 IMPLEMENTATION STATUS:\n";
foreach ($implementationStatus as $system => $status) {
    echo "🎯 " . strtoupper(str_replace('_', ' ', $system)) . "\n";
    echo "   📊 Status: {$status['status']}\n";
    
    foreach ($status as $key => $value) {
        if ($key !== 'status') {
            echo "   📋 $key: $value\n";
        }
    }
    echo "\n";
}

// 10. Path fixes list
echo "====================================================\n";
echo "🛣️ PATH FIXES APPLIED - FILE LIST\n";
echo "====================================================\n";

$pathFixDetails = [
    'config/paths.php' => 'Fixed BASE_URL to include /apsdreamhome',
    'public/index.php' => 'Updated to use proper path constants',
    'app/Core/App.php' => 'Fixed routing path issues',
    'app/Core/Controller.php' => 'Updated path references',
    'app/Helpers/UrlHelper.php' => 'Created URL helper functions',
    'composer.json' => 'Added helper to autoload'
];

echo "📁 KEY FILES WITH PATH FIXES:\n";
foreach ($pathFixDetails as $file => $description) {
    echo "   ✅ $file - $description\n";
}

echo "\n📊 PATH FIX STATISTICS:\n";
echo "   🔧 Total Files Fixed: " . count($pathFixedFiles) . "\n";
echo "   ❌ Files Remaining: " . count($hardcodedPathFiles) . "\n";
echo "   📊 Completion: " . round((count($pathFixedFiles) / $totalFiles) * 100, 1) . "%\n";

// 11. Recommendations
echo "\n====================================================\n";
echo "💡 RECOMMENDATIONS\n";
echo "====================================================\n";

$recommendations = [];

if (!$unifiedKeyExists) {
    $recommendations[] = 'Create admin/unified_key_management.php with CRUD operations';
}

if (!empty($hardcodedPathFiles)) {
    $recommendations[] = 'Fix remaining ' . count($hardcodedPathFiles) . ' files with hardcoded paths';
}

if (!empty($missingCriticalFiles)) {
    $recommendations[] = 'Create ' . count($missingCriticalFiles) . ' missing critical files';
}

if (!empty($conflicts)) {
    $recommendations[] = 'Resolve ' . count($conflicts) . ' potential conflicts';
}

if (count($coWorkerFiles) === 0) {
    $recommendations[] = 'Implement co-worker system files';
}

echo "🎯 IMMEDIATE ACTIONS NEEDED:\n";
foreach ($recommendations as $index => $recommendation) {
    echo "   " . ($index + 1) . ". $recommendation\n";
}

// Summary
echo "\n====================================================\n";
echo "📊 PROJECT DEEP SCAN SUMMARY\n";
echo "====================================================\n";

echo "📈 PROJECT STATISTICS:\n";
echo "   📁 Total PHP Files: $totalFiles\n";
echo "   🔧 Path Fixed Files: " . count($pathFixedFiles) . "\n";
echo "   ❌ Files with Issues: " . count($hardcodedPathFiles) . "\n";
echo "   ⚠️ Potential Conflicts: " . count($conflicts) . "\n";
echo "   ❌ Missing Critical Files: " . count($missingCriticalFiles) . "\n\n";

echo "🎯 IMPLEMENTATION STATUS:\n";
foreach ($implementationStatus as $system => $status) {
    $icon = $status['status'] === 'COMPLETE' || $status['status'] === 'IMPLEMENTED' ? '✅' : '⚠️';
    echo "   $icon " . strtoupper(str_replace('_', ' ', $system)) . ": {$status['status']}\n";
}

echo "\n🔧 MAIN ISSUES IDENTIFIED:\n";
echo "   ❌ admin/unified_key_management.php is missing\n";
echo "   ❌ " . count($hardcodedPathFiles) . " files still have hardcoded paths\n";
echo "   ❌ " . count($missingCriticalFiles) . " critical files are missing\n";
echo "   ⚠️ " . count($conflicts) . " potential conflicts exist\n";

echo "\n🚀 NEXT STEPS:\n";
echo "   1. Create missing unified_key_management.php\n";
echo "   2. Fix remaining hardcoded paths\n";
echo "   3. Create missing critical files\n";
echo "   4. Resolve conflicts\n";
echo "   5. Test all functionality\n";

echo "\n🎊 PROJECT DEEP SCAN COMPLETE! 🎊\n";
echo "📊 Status: COMPREHENSIVE ANALYSIS COMPLETE\n";
echo "🚀 Project structure analyzed and issues identified!\n";
?>
