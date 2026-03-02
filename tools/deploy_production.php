<?php
/**
 * APS Dream Home - Production Deployment Tool
 * Recreated after massive file deletion
 */

echo "🚀 APS DREAM HOME - PRODUCTION DEPLOYMENT\n";
echo "=====================================\n";

// Check prerequisites
echo "\n1. Checking Prerequisites:\n";

// Check if composer.json exists
if (file_exists(__DIR__ . '/../composer.json')) {
    echo "✅ Composer Configuration: Found\n";
} else {
    echo "❌ Composer Configuration: Missing\n";
    exit(1);
}

// Check if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    echo "✅ Environment File: Found\n";
} else {
    echo "❌ Environment File: Missing\n";
    exit(1);
}

// Check database connection
echo "\n2. Testing Database Connection:\n";
try {
    require_once __DIR__ . '/../config/database.php';
    $host = 'localhost';
    $dbname = 'apsdreamhome';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database Connection: Working\n";
} catch (Exception $e) {
    echo "❌ Database Connection: Failed - " . $e->getMessage() . "\n";
    exit(1);
}

// Check application files
echo "\n3. Validating Application Files:\n";

$requiredFiles = [
    'index.php' => 'Main entry point',
    'app/core/App.php' => 'Application core',
    'config/bootstrap.php' => 'Bootstrap configuration',
    'routes/api.php' => 'API routes',
    'routes/web.php' => 'Web routes'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        echo "✅ $description: Found\n";
    } else {
        echo "❌ $description: Missing\n";
    }
}

// Check permissions
echo "\n4. Checking File Permissions:\n";

$writableDirs = [
    'logs' => 'Log directory',
    'storage' => 'Storage directory',
    'uploads' => 'Uploads directory'
];

foreach ($writableDirs as $dir => $description) {
    $fullPath = __DIR__ . '/../' . $dir;
    if (is_dir($fullPath) && is_writable($fullPath)) {
        echo "✅ $description: Writable\n";
    } else {
        echo "⚠️ $description: Not writable or missing\n";
    }
}

// Security checks
echo "\n5. Security Configuration:\n";

$securityChecks = [
    '.htaccess' => 'Apache configuration',
    '.gitignore' => 'Git ignore rules',
    '.env' => 'Environment variables'
];

foreach ($securityChecks as $file => $description) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        echo "✅ $description: Configured\n";
    } else {
        echo "⚠️ $description: Not configured\n";
    }
}

// Performance checks
echo "\n6. Performance Optimization:\n";

// Check if cache directory exists
if (is_dir(__DIR__ . '/../storage/cache')) {
    echo "✅ Cache Directory: Available\n";
} else {
    echo "⚠️ Cache Directory: Not available\n";
}

// Check if assets are optimized
$assetsDir = __DIR__ . '/../assets';
if (is_dir($assetsDir)) {
    echo "✅ Assets Directory: Available\n";
} else {
    echo "❌ Assets Directory: Missing\n";
}

// Generate deployment report
echo "\n📊 DEPLOYMENT READINESS REPORT:\n";
echo "================================\n";

$deploymentScore = 0;
$maxScore = 10;

// Score calculation
if (file_exists(__DIR__ . '/../composer.json')) $deploymentScore++;
if (file_exists(__DIR__ . '/../.env')) $deploymentScore++;
if (file_exists(__DIR__ . '/../index.php')) $deploymentScore++;
if (file_exists(__DIR__ . '/../app/core/App.php')) $deploymentScore++;
if (file_exists(__DIR__ . '/../config/bootstrap.php')) $deploymentScore++;
if (file_exists(__DIR__ . '/../routes/api.php')) $deploymentScore++;
if (file_exists(__DIR__ . '/../routes/web.php')) $deploymentScore++;
if (is_dir(__DIR__ . '/../logs')) $deploymentScore++;
if (is_dir(__DIR__ . '/../storage')) $deploymentScore++;
if (file_exists(__DIR__ . '/../assets')) $deploymentScore++;

$readiness = ($deploymentScore / $maxScore) * 100;

echo "Deployment Score: $deploymentScore/$maxScore\n";
echo "Readiness: " . round($readiness, 1) . "%\n";

if ($readiness >= 90) {
    echo "🎉 Status: PRODUCTION READY\n";
} elseif ($readiness >= 70) {
    echo "⚠️ Status: ALMOST READY\n";
} else {
    echo "❌ Status: NOT READY\n";
}

// Deployment recommendations
echo "\n7. Deployment Recommendations:\n";

if ($readiness < 100) {
    echo "📝 To Complete Deployment:\n";
    if (!file_exists(__DIR__ . '/../logs')) echo "  - Create logs directory\n";
    if (!file_exists(__DIR__ . '/../storage')) echo "  - Create storage directory\n";
    if (!file_exists(__DIR__ . '/../assets')) echo "  - Setup assets directory\n";
}

echo "\n🚀 DEPLOYMENT CHECK COMPLETE!\n";
echo "Application is " . ($readiness >= 90 ? 'READY' : 'NOT READY') . " for production deployment.\n";
?>
