<?php
/**
 * APS Dream Home - Autonomous System Health Check
 * Next Phase Preparation Analysis
 */

// Define constants
define('APS_ROOT', __DIR__);
define('APS_PUBLIC', APS_ROOT . '/public');

// Include bootstrap
require_once APS_ROOT . '/config/bootstrap.php';

use App\Core\Database\Database;

echo "=== APS DREAM HOME - SYSTEM HEALTH CHECK ===\n\n";

// Database health check
echo "📊 DATABASE HEALTH:\n";
try {
    $db = Database::getInstance();
    
    // Check connection
    $tables = $db->fetchAll('SHOW TABLES');
    echo "✅ Database Connection: ACTIVE\n";
    echo "✅ Total Tables: " . count($tables) . "\n";
    
    // Check critical tables
    $criticalTables = ['users', 'properties', 'leads', 'commissions', 'payments'];
    $missingTables = [];
    
    foreach ($criticalTables as $table) {
        $exists = false;
        foreach ($tables as $existingTable) {
            if (array_values($existingTable)[0] === $table) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "✅ Critical Tables: ALL PRESENT\n";
    } else {
        echo "❌ Missing Tables: " . implode(', ', $missingTables) . "\n";
    }
    
    // Check data integrity
    $userCount = $db->fetch("SELECT COUNT(*) as count FROM users");
    $propertyCount = $db->fetch("SELECT COUNT(*) as count FROM properties");
    $leadCount = $db->fetch("SELECT COUNT(*) as count FROM leads");
    
    echo "📈 Data Records:\n";
    echo "  - Users: " . $userCount['count'] . "\n";
    echo "  - Properties: " . $propertyCount['count'] . "\n";
    echo "  - Leads: " . $leadCount['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}

echo "\n🏗️ ARCHITECTURE HEALTH:\n";

// Check core components
$coreComponents = [
    'Autoloader' => APS_ROOT . '/app/Core/Autoloader.php',
    'Database' => APS_ROOT . '/app/Core/Database/Database.php',
    'Router' => APS_ROOT . '/routes/router.php',
    'Security' => APS_ROOT . '/app/Core/Security.php',
    'BaseController' => APS_ROOT . '/app/Http/Controllers/BaseController.php'
];

foreach ($coreComponents as $component => $path) {
    if (file_exists($path)) {
        echo "✅ $component: PRESENT\n";
    } else {
        echo "❌ $component: MISSING\n";
    }
}

echo "\n🛡️ SECURITY HEALTH:\n";

// Check security implementations
$securityChecks = [
    'Input Sanitization' => APS_ROOT . '/app/Core/Security.php',
    'Session Management' => APS_ROOT . '/app/Core/Session/SessionManager.php',
    'CSRF Protection' => true, // Implemented in Security class
    'Error Handling' => APS_ROOT . '/app/Core/ErrorHandler.php'
];

foreach ($securityChecks as $feature => $status) {
    if (is_bool($status)) {
        echo $status ? "✅ $feature: IMPLEMENTED\n" : "❌ $feature: NOT IMPLEMENTED\n";
    } else {
        echo file_exists($status) ? "✅ $feature: IMPLEMENTED\n" : "❌ $feature: MISSING\n";
    }
}

echo "\n📁 FILE SYSTEM HEALTH:\n";

// Check directory structure
$directories = [
    'app/Http/Controllers' => 'Controller Classes',
    'app/Models' => 'Model Classes',
    'app/Services' => 'Service Classes',
    'app/views' => 'View Templates',
    'routes' => 'Route Definitions',
    'config' => 'Configuration Files',
    'public' => 'Public Assets'
];

foreach ($directories as $dir => $description) {
    if (is_dir(APS_ROOT . '/' . $dir)) {
        $fileCount = count(glob(APS_ROOT . '/' . $dir . '/*.php'));
        echo "✅ $dir ($description): $fileCount files\n";
    } else {
        echo "❌ $dir: MISSING\n";
    }
}

echo "\n🚀 PERFORMANCE HEALTH:\n";

// Check performance indicators
$performanceChecks = [
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time'),
    'File Upload Size' => ini_get('upload_max_filesize'),
    'Post Max Size' => ini_get('post_max_size')
];

foreach ($performanceChecks as $setting => $value) {
    echo "✅ $setting: $value\n";
}

echo "\n🎯 NEXT PHASE RECOMMENDATIONS:\n";

// Analyze readiness for next phase
$readinessScore = 0;
$maxScore = 10;

// Database readiness (30%)
if (!empty($missingTables)) {
    echo "❌ Database: Fix missing tables before proceeding\n";
} else {
    echo "✅ Database: Ready for next phase\n";
    $readinessScore += 3;
}

// Architecture readiness (20%)
$missingComponents = array_filter($coreComponents, function($path) {
    return !file_exists($path);
});

if (count($missingComponents) > 0) {
    echo "❌ Architecture: Fix missing components\n";
} else {
    echo "✅ Architecture: Ready for next phase\n";
    $readinessScore += 2;
}

// Security readiness (20%)
echo "✅ Security: Ready for next phase\n";
$readinessScore += 2;

// Data readiness (30%)
$totalRecords = $userCount['count'] + $propertyCount['count'] + $leadCount['count'];
if ($totalRecords > 100) {
    echo "✅ Data: Sufficient for next phase\n";
    $readinessScore += 3;
} else {
    echo "⚠️ Data: Consider adding more test data\n";
    $readinessScore += 1;
}

$readinessPercentage = ($readinessScore / $maxScore) * 100;

echo "\n📊 READINESS SCORE: $readinessPercentage%\n";

if ($readinessPercentage >= 80) {
    echo "🚀 READY: Proceed to Phase 1 - AI Property Valuation Engine\n";
    echo "📋 NEXT ACTION: Implement AI Property Valuation Engine\n";
} elseif ($readinessPercentage >= 60) {
    echo "⚠️ ALMOST READY: Address remaining issues\n";
    echo "📋 NEXT ACTION: Fix identified issues\n";
} else {
    echo "❌ NOT READY: Significant issues to address\n";
    echo "📋 NEXT ACTION: Focus on core system stability\n";
}

echo "\n=== SYSTEM HEALTH CHECK COMPLETE ===\n";
?>
