<?php
/**
 * APS Dream Home - Production Deployment Checklist
 * Comprehensive validation for production readiness
 */

echo "=== APS DREAM HOME - PRODUCTION DEPLOYMENT CHECKLIST ===\n\n";

// Initialize checklist results
$checklist = [
    'system_requirements' => false,
    'database_connection' => false,
    'file_permissions' => false,
    'security_config' => false,
    'api_endpoints' => false,
    'page_accessibility' => false,
    'error_handling' => false,
    'performance_optimization' => false,
    'backup_system' => false,
    'monitoring_tools' => false
];

$checksPassed = 0;
$totalChecks = count($checklist);

echo "🔍 Running comprehensive deployment validation...\n\n";

// 1. System Requirements Check
echo "1️⃣ SYSTEM REQUIREMENTS CHECK:\n";
$phpVersion = PHP_VERSION;
$requiredVersion = '8.0.0';
$versionCheck = version_compare($phpVersion, $requiredVersion, '>=');

if ($versionCheck) {
    echo "✅ PHP Version: $phpVersion (>= $requiredVersion)\n";
    $checklist['system_requirements'] = true;
    $checksPassed++;
} else {
    echo "❌ PHP Version: $phpVersion (requires >= $requiredVersion)\n";
}

// Check required extensions
$requiredExtensions = ['mysqli', 'pdo', 'json', 'mbstring', 'curl', 'gd'];
$extensionsOk = true;
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        echo "❌ Missing extension: $ext\n";
        $extensionsOk = false;
    }
}
if ($extensionsOk) {
    echo "✅ All required extensions loaded\n";
} else {
    $checklist['system_requirements'] = false;
}
echo "\n";

// 2. Database Connection Check
echo "2️⃣ DATABASE CONNECTION CHECK:\n";
try {
    $mysqli = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($mysqli->connect_error) {
        echo "❌ Database connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "✅ Database connected successfully\n";
        
        // Check table count
        $result = $mysqli->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        echo "✅ Found $tableCount tables in database\n";
        
        // Test a simple query
        $testResult = $mysqli->query("SELECT 1 as test");
        if ($testResult) {
            echo "✅ Database query test passed\n";
            $checklist['database_connection'] = true;
            $checksPassed++;
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. File Permissions Check
echo "3️⃣ FILE PERMISSIONS CHECK:\n";
$criticalDirs = ['logs', 'cache', 'uploads', 'uploads/properties', 'uploads/documents'];
$permissionsOk = true;

foreach ($criticalDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (!is_dir($fullPath)) {
        echo "❌ Missing directory: $dir\n";
        $permissionsOk = false;
    } elseif (!is_writable($fullPath)) {
        echo "❌ Directory not writable: $dir\n";
        $permissionsOk = false;
    } else {
        echo "✅ Directory $dir is accessible\n";
    }
}

if ($permissionsOk) {
    $checklist['file_permissions'] = true;
    $checksPassed++;
}
echo "\n";

// 4. Security Configuration Check
echo "4️⃣ SECURITY CONFIGURATION CHECK:\n";
$securityChecks = [
    'display_errors' => ini_get('display_errors') == '0',
    'allow_url_include' => ini_get('allow_url_include') == '0',
    'file_uploads' => ini_get('file_uploads') == '1',
    'max_execution_time' => ini_get('max_execution_time') > 0
];

$securityOk = true;
foreach ($securityChecks as $setting => $check) {
    if ($check) {
        echo "✅ $setting is properly configured\n";
    } else {
        echo "⚠️ $setting may need attention\n";
        $securityOk = false;
    }
}

if ($securityOk) {
    $checklist['security_config'] = true;
    $checksPassed++;
}
echo "\n";

// 5. API Endpoints Check
echo "5️⃣ API ENDPOINTS CHECK:\n";
$apiEndpoints = [
    '/api' => 'API Root',
    '/api/health' => 'Health Check',
    '/api/properties' => 'Properties API',
    '/api/leads' => 'Leads API',
    '/api/analytics' => 'Analytics API'
];

$apiOk = true;
foreach ($apiEndpoints as $endpoint => $name) {
    $url = 'http://localhost/apsdreamhome' . $endpoint;
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✅ $name ($endpoint)\n";
    } else {
        echo "❌ $name ($endpoint) - Failed to respond\n";
        $apiOk = false;
    }
}

if ($apiOk) {
    $checklist['api_endpoints'] = true;
    $checksPassed++;
}
echo "\n";

// 6. Page Accessibility Check
echo "6️⃣ PAGE ACCESSIBILITY CHECK:\n";
$criticalPages = [
    '/' => 'Home',
    '/about' => 'About',
    '/contact' => 'Contact',
    '/properties' => 'Properties',
    '/login' => 'Login',
    '/admin' => 'Admin'
];

$pagesOk = true;
foreach ($criticalPages as $page => $name) {
    $url = 'http://localhost/apsdreamhome' . $page;
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        echo "✅ $name ($page)\n";
    } else {
        echo "❌ $name ($page) - Not accessible\n";
        $pagesOk = false;
    }
}

if ($pagesOk) {
    $checklist['page_accessibility'] = true;
    $checksPassed++;
}
echo "\n";

// 7. Error Handling Check
echo "7️⃣ ERROR HANDLING CHECK:\n";
$errorLog = __DIR__ . '/logs/php_error.log';
if (file_exists($errorLog)) {
    $errorContent = file_get_contents($errorLog);
    $recentErrors = substr($errorContent, -1000); // Check last 1000 characters
    
    if (strpos($recentErrors, 'Fatal error') !== false || strpos($recentErrors, 'Parse error') !== false) {
        echo "❌ Critical errors found in log\n";
    } else {
        echo "✅ No critical errors in error log\n";
        $checklist['error_handling'] = true;
        $checksPassed++;
    }
} else {
    echo "❌ Error log file not found\n";
}
echo "\n";

// 8. Performance Optimization Check
echo "8️⃣ PERFORMANCE OPTIMIZATION CHECK:\n";
$startTime = microtime(true);
$memoryStart = memory_get_usage();

// Simulate some work
for ($i = 0; $i < 1000; $i++) {
    $test = md5($i);
}

$endTime = microtime(true);
$memoryEnd = memory_get_usage();

$processingTime = ($endTime - $startTime) * 1000;
$memoryUsed = ($memoryEnd - $memoryStart) / 1024 / 1024;

if ($processingTime < 100 && $memoryUsed < 10) {
    echo "✅ Performance test passed ({$processingTime}ms, {$memoryUsed}MB)\n";
    $checklist['performance_optimization'] = true;
    $checksPassed++;
} else {
    echo "⚠️ Performance may need optimization ({$processingTime}ms, {$memoryUsed}MB)\n";
}
echo "\n";

// 9. Backup System Check
echo "9️⃣ BACKUP SYSTEM CHECK:\n";
$backupFiles = glob(__DIR__ . '/*.backup*');
if (count($backupFiles) === 0) {
    echo "✅ System cleaned (no backup files found)\n";
    $checklist['backup_system'] = true;
    $checksPassed++;
} else {
    echo "⚠️ Found " . count($backupFiles) . " backup files\n";
}
echo "\n";

// 10. Monitoring Tools Check
echo "🔟 MONITORING TOOLS CHECK:\n";
$monitoringFiles = [
    'health_check.php',
    'system_monitor.php',
    'monitor.html'
];

$monitoringOk = true;
foreach ($monitoringFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file is available\n";
    } else {
        echo "❌ $file is missing\n";
        $monitoringOk = false;
    }
}

if ($monitoringOk) {
    $checklist['monitoring_tools'] = true;
    $checksPassed++;
}
echo "\n";

// Final Summary
echo "📊 DEPLOYMENT READINESS SUMMARY:\n";
echo "================================\n";
echo "Checks Passed: $checksPassed/$totalChecks\n";
echo "Success Rate: " . round(($checksPassed / $totalChecks) * 100, 1) . "%\n\n";

foreach ($checklist as $check => $status) {
    $icon = $status ? "✅" : "❌";
    $checkName = ucwords(str_replace('_', ' ', $check));
    echo "$icon $checkName\n";
}

echo "\n";

if ($checksPassed === $totalChecks) {
    echo "🎉 DEPLOYMENT STATUS: ✅ READY FOR PRODUCTION\n";
    echo "🚀 APS Dream Home system is fully prepared for deployment!\n";
} else {
    echo "⚠️ DEPLOYMENT STATUS: ⚠️ NEEDS ATTENTION\n";
    echo "🔧 Please address the failed checks before deployment.\n";
}

echo "\n📅 Deployment Check Completed: " . date('Y-m-d H:i:s') . "\n";
echo "🏆 APS Dream Home - Production Deployment System\n";
?>
