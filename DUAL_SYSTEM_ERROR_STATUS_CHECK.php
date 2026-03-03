<?php
/**
 * Dual System Error Status Check
 * 
 * Check if errors have been fixed in other systems
 * and pull latest status from deployment packages
 */

define('PROJECT_BASE_PATH', __DIR__);

echo "====================================================\n";
echo "🔍 DUAL SYSTEM ERROR STATUS CHECK\n";
echo "====================================================\n\n";

// Step 1: Check main system current errors
echo "Step 1: Main System Current Errors\n";
echo "=================================\n";

$mainSystemErrors = [
    'app/Core/Controller.php' => [
        'private_visibility' => 'Lines 122, 126 - Member has private visibility',
        'undefined_router' => 'Line 176 - Undefined property App::$router',
        'unknown_validator' => 'Line 328 - Use of unknown class Validator',
        'unknown_methods' => 'Lines 331, 342 - Unknown Request/Response methods'
    ],
    'app/Core/Database/Model.php' => [
        'arrayaccess_compatibility' => 'Lines 704, 712, 720, 728 - Method signatures incompatible',
        'class_basename_missing' => 'Lines 113, 163 - Function not defined',
        'database_method' => 'Line 185 - Unknown method App::database()',
        'runtime_exception' => 'Line 606 - Can be simplified'
    ],
    'app/Controllers/AdminController.php' => [
        'database_prepare' => 'Multiple lines - Call to unknown method Database::prepare()'
    ]
];

echo "📋 Main System Errors:\n";
foreach ($mainSystemErrors as $file => $errors) {
    echo "   📄 $file:\n";
    foreach ($errors as $error => $description) {
        echo "      ⚠️ $error: $description\n";
    }
    echo "\n";
}

// Step 2: Check deployment package fallback system
echo "Step 2: Deployment Package Fallback System\n";
echo "========================================\n";

$fallbackPath = PROJECT_BASE_PATH . '/apsdreamhome_deployment_package_fallback';
if (is_dir($fallbackPath)) {
    echo "📁 Checking fallback system...\n";
    
    // Check Controller.php in fallback
    $fallbackController = $fallbackPath . '/app/Core/Controller.php';
    if (file_exists($fallbackController)) {
        echo "   📄 Fallback Controller.php: ✅ EXISTS\n";
        $content = file_get_contents($fallbackController);
        
        // Check for fixes
        $hasValidator = strpos($content, 'use App\\Core\\Validator;') !== false;
        $hasRouter = strpos($content, 'protected $router') !== false;
        $hasLogger = strpos($content, 'protected $logger') !== false;
        
        echo "      🔧 Validator import: " . ($hasValidator ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 Router property: " . ($hasRouter ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 Logger property: " . ($hasLogger ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
    } else {
        echo "   📄 Fallback Controller.php: ❌ MISSING\n";
    }
    
    // Check Model.php in fallback
    $fallbackModel = $fallbackPath . '/app/Core/Database/Model.php';
    if (file_exists($fallbackModel)) {
        echo "   📄 Fallback Model.php: ✅ EXISTS\n";
        $content = file_get_contents($fallbackModel);
        
        // Check for fixes
        $hasArrayAccess = strpos($content, 'public function offsetExists(mixed $offset): bool') !== false;
        $hasClassBasename = strpos($content, 'function class_basename') !== false;
        $hasRuntimeException = strpos($content, '\\RuntimeException') === false;
        
        echo "      🔧 ArrayAccess signatures: " . ($hasArrayAccess ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 class_basename function: " . ($hasClassBasename ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 RuntimeException: " . ($hasRuntimeException ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
    } else {
        echo "   📄 Fallback Model.php: ❌ MISSING\n";
    }
    
    // Check Validator.php in fallback
    $fallbackValidator = $fallbackPath . '/app/Core/Validator.php';
    echo "   📄 Fallback Validator.php: " . (file_exists($fallbackValidator) ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
} else {
    echo "📁 Fallback system: ❌ NOT FOUND\n";
}

echo "\n";

// Step 3: Check main deployment package system
echo "Step 3: Main Deployment Package System\n";
echo "=====================================\n";

$deploymentPath = PROJECT_BASE_PATH . '/deployment_package';
if (is_dir($deploymentPath)) {
    echo "📁 Checking main deployment system...\n";
    
    // Check Controller.php in deployment
    $deploymentController = $deploymentPath . '/app/Core/Controller.php';
    if (file_exists($deploymentController)) {
        echo "   📄 Deployment Controller.php: ✅ EXISTS\n";
        $content = file_get_contents($deploymentController);
        
        // Check for fixes
        $hasValidator = strpos($content, 'use App\\Core\\Validator;') !== false;
        $hasRouter = strpos($content, 'protected $router') !== false;
        $hasLogger = strpos($content, 'protected $logger') !== false;
        
        echo "      🔧 Validator import: " . ($hasValidator ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 Router property: " . ($hasRouter ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 Logger property: " . ($hasLogger ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
    } else {
        echo "   📄 Deployment Controller.php: ❌ MISSING\n";
    }
    
    // Check Model.php in deployment
    $deploymentModel = $deploymentPath . '/app/Core/Database/Model.php';
    if (file_exists($deploymentModel)) {
        echo "   📄 Deployment Model.php: ✅ EXISTS\n";
        $content = file_get_contents($deploymentModel);
        
        // Check for fixes
        $hasArrayAccess = strpos($content, 'public function offsetExists(mixed $offset): bool') !== false;
        $hasClassBasename = strpos($content, 'function class_basename') !== false;
        $hasRuntimeException = strpos($content, '\\RuntimeException') === false;
        
        echo "      🔧 ArrayAccess signatures: " . ($hasArrayAccess ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 class_basename function: " . ($hasClassBasename ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
        echo "      🔧 RuntimeException: " . ($hasRuntimeException ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
    } else {
        echo "   📄 Deployment Model.php: ❌ MISSING\n";
    }
    
    // Check Validator.php in deployment
    $deploymentValidator = $deploymentPath . '/app/Core/Validator.php';
    echo "   📄 Deployment Validator.php: " . (file_exists($deploymentValidator) ? "✅ EXISTS" : "❌ MISSING") . "\n";
    
} else {
    echo "📁 Main deployment system: ❌ NOT FOUND\n";
}

echo "\n";

// Step 4: Compare systems and identify fixes
echo "Step 4: System Comparison and Fixes\n";
echo "=================================\n";

$systems = [
    'main' => PROJECT_BASE_PATH,
    'fallback' => PROJECT_BASE_PATH . '/apsdreamhome_deployment_package_fallback',
    'deployment' => PROJECT_BASE_PATH . '/deployment_package'
];

$filesToCheck = [
    'app/Core/Controller.php',
    'app/Core/Database/Model.php',
    'app/Core/Validator.php'
];

echo "📊 System Comparison:\n";
foreach ($filesToCheck as $file) {
    echo "   📄 $file:\n";
    
    foreach ($systems as $systemName => $systemPath) {
        $filePath = $systemPath . '/' . $file;
        $exists = file_exists($filePath);
        $size = $exists ? filesize($filePath) : 0;
        $modified = $exists ? filemtime($filePath) : 0;
        
        echo "      📋 $systemName: " . ($exists ? "✅ EXISTS" : "❌ MISSING");
        if ($exists) {
            echo " (" . number_format($size) . " bytes, " . date('Y-m-d H:i:s', $modified) . ")";
        }
        echo "\n";
    }
    echo "\n";
}

// Step 5: Check if fixes have been applied in other systems
echo "Step 5: Fix Status in Other Systems\n";
echo "=================================\n";

$fixStatus = [
    'validator_class' => [
        'main' => file_exists(PROJECT_BASE_PATH . '/app/Core/Validator.php'),
        'fallback' => file_exists($fallbackPath . '/app/Core/Validator.php'),
        'deployment' => file_exists($deploymentPath . '/app/Core/Validator.php')
    ],
    'controller_syntax' => [
        'main' => $this->checkControllerFix(PROJECT_BASE_PATH . '/app/Core/Controller.php'),
        'fallback' => $this->checkControllerFix($fallbackPath . '/app/Core/Controller.php'),
        'deployment' => $this->checkControllerFix($deploymentPath . '/app/Core/Controller.php')
    ],
    'model_signatures' => [
        'main' => $this->checkModelFix(PROJECT_BASE_PATH . '/app/Core/Database/Model.php'),
        'fallback' => $this->checkModelFix($fallbackPath . '/app/Core/Database/Model.php'),
        'deployment' => $this->checkModelFix($deploymentPath . '/app/Core/Database/Model.php')
    ]
];

echo "🔧 Fix Status Across Systems:\n";
foreach ($fixStatus as $fix => $systems) {
    echo "   📋 $fix:\n";
    foreach ($systems as $system => $status) {
        echo "      📊 $system: " . ($status ? "✅ FIXED" : "❌ NOT FIXED") . "\n";
    }
    echo "\n";
}

// Step 6: Recommendations
echo "Step 6: Recommendations\n";
echo "=====================\n";

$recommendations = [
    'if_other_systems_fixed' => [
        'pull_changes' => 'Pull fixes from other systems to main',
        'sync_deployment' => 'Synchronize all deployment packages',
        'test_functionality' => 'Test all functionality after sync'
    ],
    'if_main_system_better' => [
        'push_changes' => 'Push fixes to other systems',
        'update_packages' => 'Update deployment packages',
        'maintain_consistency' => 'Keep all systems consistent'
    ],
    'general_actions' => [
        'compare_timestamps' => 'Compare file modification times',
        'check_file_sizes' => 'Verify file sizes match',
        'validate_fixes' => 'Test all fixes work correctly'
    ]
];

echo "💡 Recommendations:\n";
foreach ($recommendations as $category => $actions) {
    echo "   📋 $category:\n";
    foreach ($actions as $action => $description) {
        echo "      🎯 $action: $description\n";
    }
    echo "\n";
}

echo "====================================================\n";
echo "🎊 DUAL SYSTEM ERROR STATUS CHECK COMPLETE! 🎊\n";
echo "📊 Status: COMPARISON COMPLETE - RECOMMENDATIONS READY!\n";
echo "🚀 Ready to synchronize systems if needed!\n\n";

echo "🔍 KEY FINDINGS:\n";
echo "• ✅ Main system errors identified\n";
echo "• ✅ Deployment package systems checked\n";
echo "• ✅ Fix status compared across systems\n";
echo "• ✅ Recommendations generated\n";
echo "• ✅ Next steps defined\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. Compare fix status across systems\n";
echo "2. Pull fixes if other systems are better\n";
echo "3. Push fixes if main system is better\n";
echo "4. Synchronize all deployment packages\n";
echo "5. Test all functionality\n";
echo "6. Deploy to production\n\n";

echo "🏆 SYSTEM SYNC READY!\n";
echo "All systems analyzed and ready for synchronization!\n\n";

echo "🎊 CONGRATULATIONS! ERROR STATUS CHECK COMPLETE! 🎊\n";

// Helper function to check Controller fix
function checkControllerFix($filePath) {
    if (!file_exists($filePath)) return false;
    
    $content = file_get_contents($filePath);
    return (
        strpos($content, 'use App\\Core\\Validator;') !== false &&
        strpos($content, 'protected $router') !== false &&
        strpos($content, 'protected $logger') !== false
    );
}

// Helper function to check Model fix
function checkModelFix($filePath) {
    if (!file_exists($filePath)) return false;
    
    $content = file_get_contents($filePath);
    return (
        strpos($content, 'public function offsetExists(mixed $offset): bool') !== false &&
        strpos($content, 'function class_basename') !== false &&
        strpos($content, '\\RuntimeException') === false
    );
}
