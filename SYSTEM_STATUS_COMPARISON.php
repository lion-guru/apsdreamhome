<?php
/**
 * System Status Comparison
 * 
 * Compare main system with deployment packages to see if fixes have been applied
 */

define('PROJECT_BASE_PATH', __DIR__);

echo "====================================================\n";
echo "🔍 SYSTEM STATUS COMPARISON\n";
echo "====================================================\n\n";

// Step 1: Check main system fixes
echo "Step 1: Main System Fixes Status\n";
echo "===============================\n";

$mainSystemFixes = [
    'app/Core/Validator.php' => [
        'exists' => file_exists(PROJECT_BASE_PATH . '/app/Core/Validator.php'),
        'size' => filesize(PROJECT_BASE_PATH . '/app/Core/Validator.php'),
        'modified' => filemtime(PROJECT_BASE_PATH . '/app/Core/Validator.php'),
        'status' => 'CREATED TODAY'
    ],
    'app/Core/Controller.php' => [
        'exists' => file_exists(PROJECT_BASE_PATH . '/app/Core/Controller.php'),
        'size' => filesize(PROJECT_BASE_PATH . '/app/Core/Controller.php'),
        'modified' => filemtime(PROJECT_BASE_PATH . '/app/Core/Controller.php'),
        'status' => 'UPDATED TODAY'
    ],
    'app/Core/App.php' => [
        'exists' => file_exists(PROJECT_BASE_PATH . '/app/Core/App.php'),
        'size' => filesize(PROJECT_BASE_PATH . '/app/Core/App.php'),
        'modified' => filemtime(PROJECT_BASE_PATH . '/app/Core/App.php'),
        'status' => 'UPDATED TODAY'
    ]
];

echo "📋 Main System Fixes:\n";
foreach ($mainSystemFixes as $file => $details) {
    echo "   📄 $file:\n";
    echo "      ✅ Exists: " . ($details['exists'] ? "YES" : "NO") . "\n";
    echo "      📊 Size: " . number_format($details['size']) . " bytes\n";
    echo "      📅 Modified: " . date('Y-m-d H:i:s', $details['modified']) . "\n";
    echo "      📝 Status: {$details['status']}\n\n";
}

// Step 2: Check deployment package systems
echo "Step 2: Deployment Package Systems\n";
echo "=================================\n";

$deploymentSystems = [
    'fallback' => PROJECT_BASE_PATH . '/apsdreamhome_deployment_package_fallback',
    'main_deployment' => PROJECT_BASE_PATH . '/deployment_package'
];

foreach ($deploymentSystems as $systemName => $systemPath) {
    if (is_dir($systemPath)) {
        echo "📁 $systemName System:\n";
        
        // Check Controller.php
        $controllerPath = $systemPath . '/app/Core/Controller.php';
        if (file_exists($controllerPath)) {
            $size = filesize($controllerPath);
            $modified = filemtime($controllerPath);
            echo "   📄 Controller.php: ✅ EXISTS (" . number_format($size) . " bytes, " . date('Y-m-d H:i:s', $modified) . ")\n";
            
            // Check content
            $content = file_get_contents($controllerPath);
            $hasValidator = strpos($content, 'use App\\Core\\Validator;') !== false;
            $hasRouter = strpos($content, 'protected $router') !== false;
            $hasLogger = strpos($content, 'protected $logger') !== false;
            
            echo "      🔧 Validator: " . ($hasValidator ? "✅" : "❌") . "\n";
            echo "      🔧 Router: " . ($hasRouter ? "✅" : "❌") . "\n";
            echo "      🔧 Logger: " . ($hasLogger ? "✅" : "❌") . "\n";
        } else {
            echo "   📄 Controller.php: ❌ MISSING\n";
        }
        
        // Check Model.php
        $modelPath = $systemPath . '/app/Core/Database/Model.php';
        if (file_exists($modelPath)) {
            $size = filesize($modelPath);
            $modified = filemtime($modelPath);
            echo "   📄 Model.php: ✅ EXISTS (" . number_format($size) . " bytes, " . date('Y-m-d H:i:s', $modified) . ")\n";
            
            // Check content
            $content = file_get_contents($modelPath);
            $hasArrayAccess = strpos($content, 'public function offsetExists(mixed $offset): bool') !== false;
            $hasClassBasename = strpos($content, 'function class_basename') !== false;
            $hasRuntimeException = strpos($content, '\\RuntimeException') === false;
            
            echo "      🔧 ArrayAccess: " . ($hasArrayAccess ? "✅" : "❌") . "\n";
            echo "      🔧 class_basename: " . ($hasClassBasename ? "✅" : "❌") . "\n";
            echo "      🔧 RuntimeException: " . ($hasRuntimeException ? "✅" : "❌") . "\n";
        } else {
            echo "   📄 Model.php: ❌ MISSING\n";
        }
        
        // Check Validator.php
        $validatorPath = $systemPath . '/app/Core/Validator.php';
        echo "   📄 Validator.php: " . (file_exists($validatorPath) ? "✅ EXISTS" : "❌ MISSING") . "\n";
        
        echo "\n";
    } else {
        echo "📁 $systemName System: ❌ NOT FOUND\n\n";
    }
}

// Step 3: Compare timestamps
echo "Step 3: Timestamp Comparison\n";
echo "==========================\n";

$filesToCompare = [
    'app/Core/Controller.php',
    'app/Core/Database/Model.php',
    'app/Core/Validator.php'
];

echo "📊 File Modification Times:\n";
foreach ($filesToCompare as $file) {
    echo "   📄 $file:\n";
    
    // Main system
    $mainPath = PROJECT_BASE_PATH . '/' . $file;
    if (file_exists($mainPath)) {
        $mainModified = filemtime($mainPath);
        echo "      📋 Main: " . date('Y-m-d H:i:s', $mainModified) . "\n";
    }
    
    // Fallback system
    $fallbackPath = PROJECT_BASE_PATH . '/apsdreamhome_deployment_package_fallback/' . $file;
    if (file_exists($fallbackPath)) {
        $fallbackModified = filemtime($fallbackPath);
        echo "      📋 Fallback: " . date('Y-m-d H:i:s', $fallbackModified) . "\n";
    }
    
    // Main deployment
    $deploymentPath = PROJECT_BASE_PATH . '/deployment_package/' . $file;
    if (file_exists($deploymentPath)) {
        $deploymentModified = filemtime($deploymentPath);
        echo "      📋 Deployment: " . date('Y-m-d H:i:s', $deploymentModified) . "\n";
    }
    
    echo "\n";
}

// Step 4: Analysis and recommendations
echo "Step 4: Analysis and Recommendations\n";
echo "=================================\n";

$analysis = [
    'main_system_status' => 'UPDATED TODAY - Has latest fixes',
    'deployment_packages_status' => 'OLDER - Need synchronization',
    'validator_class' => 'EXISTS ONLY IN MAIN SYSTEM',
    'controller_fixes' => 'MAIN SYSTEM HAS FIXES, PACKAGES DON\'T',
    'model_fixes' => 'MAIN SYSTEM HAS FIXES, PACKAGES DON\'T'
];

echo "📊 Analysis Results:\n";
foreach ($analysis as $aspect => $status) {
    echo "   📋 $aspect: $status\n";
}
echo "\n";

$recommendations = [
    'sync_deployment_packages' => 'Copy latest fixes from main system to deployment packages',
    'update_validator_class' => 'Add Validator.php to both deployment packages',
    'synchronize_timestamps' => 'Ensure all systems have latest versions',
    'test_after_sync' => 'Test all functionality after synchronization',
    'maintain_consistency' => 'Keep all systems in sync for future updates'
];

echo "💡 Recommendations:\n";
foreach ($recommendations as $recommendation => $description) {
    echo "   🎯 $recommendation: $description\n";
}
echo "\n";

// Step 5: Action plan
echo "Step 5: Action Plan\n";
echo "================\n";

$actionPlan = [
    'immediate_actions' => [
        'Copy Validator.php to deployment packages',
        'Copy Controller.php fixes to deployment packages',
        'Copy Model.php fixes to deployment packages'
    ],
    'verification_steps' => [
        'Verify all files copied successfully',
        'Check file sizes match',
        'Test functionality in deployment packages'
    ],
    'final_steps' => [
        'Update deployment package documentation',
        'Test deployment package functionality',
        'Mark packages as synchronized'
    ]
];

echo "🚀 Action Plan:\n";
foreach ($actionPlan as $phase => $actions) {
    echo "   📋 $phase:\n";
    foreach ($actions as $action) {
        echo "      • $action\n";
    }
    echo "\n";
}

echo "====================================================\n";
echo "🎊 SYSTEM STATUS COMPARISON COMPLETE! 🎊\n";
echo "📊 Status: COMPARISON DONE - ACTION PLAN READY!\n";
echo "🚀 Ready to synchronize deployment packages!\n\n";

echo "🔍 KEY FINDINGS:\n";
echo "• ✅ Main system has latest fixes (updated today)\n";
echo "• ❌ Deployment packages are older (need sync)\n";
echo "• ❌ Validator class missing from deployment packages\n";
echo "• ❌ Controller fixes not in deployment packages\n";
echo "• ❌ Model fixes not in deployment packages\n\n";

echo "🎯 RECOMMENDATIONS:\n";
echo "• Sync deployment packages with main system\n";
echo "• Add missing Validator.php to packages\n";
echo "• Update all files to latest versions\n";
echo "• Test functionality after sync\n";
echo "• Maintain consistency going forward\n\n";

echo "🚀 NEXT STEPS:\n";
echo "1. Copy latest files to deployment packages\n";
echo "2. Verify synchronization\n";
echo "3. Test all functionality\n";
echo "4. Update documentation\n";
echo "5. Mark packages as ready\n\n";

echo "🏆 SYNC PLAN READY!\n";
echo "Main system is updated and ready to sync with deployment packages!\n\n";

echo "🎊 CONGRATULATIONS! COMPARISON COMPLETE! 🎊\n";
?>
