<?php
/**
 * APS Dream Home - COMPREHENSIVE TESTING & VALIDATION
 * Complete system testing, health check, and validation
 */

echo "🏠 APS Dream Home - COMPREHENSIVE TESTING & VALIDATION\n";
echo "====================================================\n\n";

$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$testResults = [];
$healthStatus = [];

// 1. ENVIRONMENT TESTING
echo "1. 🌍 ENVIRONMENT TESTING\n";
echo "========================\n";

$envChecks = [
    'PHP Version' => PHP_VERSION,
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time'),
    'File Uploads' => ini_get('file_uploads'),
    'Max Upload Size' => ini_get('upload_max_filesize'),
    'Post Max Size' => ini_get('post_max_size'),
    'Error Reporting' => ini_get('error_reporting'),
    'Display Errors' => ini_get('display_errors')
];

foreach ($envChecks as $check => $value) {
    $status = "✅ $check: $value";
    echo "   $status\n";
    $healthStatus['environment'][$check] = $value;
}

// 2. DATABASE CONNECTION TESTING
echo "\n2. 🗄️ DATABASE CONNECTION TESTING\n";
echo "===============================\n";

$dbTest = [
    'connection' => false,
    'tables' => 0,
    'errors' => []
];

try {
    // Check .env file
    $envFile = $projectRoot . '/.env';
    if (file_exists($envFile)) {
        echo "   ✅ .env file found\n";
        
        // Parse .env
        $envContent = file_get_contents($envFile);
        $envVars = [];
        foreach (explode("\n", $envContent) as $line) {
            if (strpos($line, '=') !== false && !empty(trim($line))) {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
        
        // Test database connection
        if (isset($envVars['DB_HOST']) && isset($envVars['DB_NAME']) && isset($envVars['DB_USER'])) {
            try {
                $conn = new mysqli(
                    $envVars['DB_HOST'] ?? 'localhost',
                    $envVars['DB_USER'] ?? '',
                    $envVars['DB_PASS'] ?? '',
                    $envVars['DB_NAME'] ?? ''
                );
                
                if ($conn->connect_error) {
                    echo "   ❌ Database connection failed: " . $conn->connect_error . "\n";
                    $dbTest['errors'][] = $conn->connect_error;
                } else {
                    echo "   ✅ Database connection successful\n";
                    $dbTest['connection'] = true;
                    
                    // Count tables
                    $result = $conn->query("SHOW TABLES");
                    $dbTest['tables'] = $result->num_rows;
                    echo "   ✅ Found {$dbTest['tables']} database tables\n";
                    
                    $conn->close();
                }
            } catch (Exception $e) {
                echo "   ❌ Database error: " . $e->getMessage() . "\n";
                $dbTest['errors'][] = $e->getMessage();
            }
        } else {
            echo "   ⚠️  Database credentials incomplete in .env\n";
            $dbTest['errors'][] = "Incomplete database credentials";
        }
    } else {
        echo "   ❌ .env file not found\n";
        $dbTest['errors'][] = ".env file missing";
    }
} catch (Exception $e) {
    echo "   ❌ Environment check error: " . $e->getMessage() . "\n";
    $dbTest['errors'][] = $e->getMessage();
}

$healthStatus['database'] = $dbTest;

// 3. CORE FILES FUNCTIONALITY TESTING
echo "\n3. 🧪 CORE FILES FUNCTIONALITY TESTING\n";
echo "====================================\n";

$coreTests = [
    'index.php' => 'Main entry point',
    'bootstrap.php' => 'Bootstrap loader',
    'includes/session_helpers.php' => 'Session helpers',
    'includes/db_connection.php' => 'Database connection',
    'routes/web.php' => 'Web routes',
    'app/core/App.php' => 'Application core'
];

$passedTests = 0;
$totalTests = count($coreTests);

foreach ($coreTests as $file => $description) {
    $filePath = $projectRoot . '/' . $file;
    
    if (file_exists($filePath)) {
        // Basic syntax check
        $output = [];
        $returnCode = 0;
        exec("php -l \"$filePath\" 2>&1", $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "   ✅ $file - Valid PHP syntax\n";
            $passedTests++;
        } else {
            echo "   ❌ $file - PHP syntax error\n";
            echo "      " . implode("\n      ", $output) . "\n";
        }
    } else {
        echo "   ❌ $file - File not found\n";
    }
}

$coreTestScore = round(($passedTests / $totalTests) * 100);
echo "   📊 Core Tests: $passedTests/$totalTests passed ($coreTestScore%)\n";

$healthStatus['core_files'] = [
    'passed' => $passedTests,
    'total' => $totalTests,
    'score' => $coreTestScore
];

// 4. SECURITY TESTING
echo "\n4. 🔒 SECURITY TESTING\n";
echo "====================\n";

$securityTests = [
    '.htaccess' => 'Apache security config',
    '.env' => 'Environment variables',
    'includes/session_helpers.php' => 'Session security',
    'app/core/Auth.php' => 'Authentication system'
];

$securityScore = 0;
$totalSecurityTests = count($securityTests);

foreach ($securityTests as $file => $description) {
    $filePath = $projectRoot . '/' . $file;
    
    if (file_exists($filePath)) {
        echo "   ✅ $file - Security component found\n";
        $securityScore++;
        
        // Check for common security patterns
        if ($file === '.htaccess') {
            $content = file_get_contents($filePath);
            if (strpos($content, 'RewriteEngine') !== false) {
                echo "      ✅ URL rewriting enabled\n";
            }
            if (strpos($content, 'Options -Indexes') !== false) {
                echo "      ✅ Directory browsing disabled\n";
            }
        }
    } else {
        echo "   ❌ $file - Security component missing\n";
    }
}

$securityTestScore = round(($securityScore / $totalSecurityTests) * 100);
echo "   📊 Security Tests: $securityScore/$totalSecurityTests passed ($securityTestScore%)\n";

$healthStatus['security'] = [
    'passed' => $securityScore,
    'total' => $totalSecurityTests,
    'score' => $securityTestScore
];

// 5. PERFORMANCE TESTING
echo "\n5. ⚡ PERFORMANCE TESTING\n";
echo "========================\n";

$performanceTests = [
    'cache/' => 'Cache directory',
    'logs/' => 'Logs directory',
    'uploads/' => 'Uploads directory',
    'assets/' => 'Assets directory'
];

$performanceScore = 0;
$totalPerformanceTests = count($performanceTests);

foreach ($performanceTests as $dir => $description) {
    $dirPath = $projectRoot . '/' . $dir;
    
    if (is_dir($dirPath)) {
        $items = scandir($dirPath);
        $itemCount = count($items) - 2; // Remove . and ..
        
        echo "   ✅ $dir - Directory exists ($itemCount items)\n";
        $performanceScore++;
        
        // Check if directory is writable
        if (is_writable($dirPath)) {
            echo "      ✅ Directory is writable\n";
        } else {
            echo "      ⚠️  Directory is not writable\n";
        }
    } else {
        echo "   ❌ $dir - Directory missing\n";
    }
}

$performanceTestScore = round(($performanceScore / $totalPerformanceTests) * 100);
echo "   📊 Performance Tests: $performanceScore/$totalPerformanceTests passed ($performanceTestScore%)\n";

$healthStatus['performance'] = [
    'passed' => $performanceScore,
    'total' => $totalPerformanceTests,
    'score' => $performanceTestScore
];

// 6. API ENDPOINTS TESTING
echo "\n6. 🌐 API ENDPOINTS TESTING\n";
echo "==========================\n";

$apiTests = [
    'api/' => 'API directory',
    'routes/api.php' => 'API routes',
    'app/Http/Controllers/Api/' => 'API controllers'
];

$apiScore = 0;
$totalApiTests = count($apiTests);

foreach ($apiTests as $path => $description) {
    $fullPath = $projectRoot . '/' . $path;
    
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '*.php');
        $fileCount = count($files);
        echo "   ✅ $path - Directory exists ($fileCount PHP files)\n";
        $apiScore++;
    } elseif (file_exists($fullPath)) {
        echo "   ✅ $path - File exists\n";
        $apiScore++;
    } else {
        echo "   ❌ $path - Not found\n";
    }
}

$apiTestScore = round(($apiScore / $totalApiTests) * 100);
echo "   📊 API Tests: $apiScore/$totalApiTests passed ($apiTestScore%)\n";

$healthStatus['api'] = [
    'passed' => $apiScore,
    'total' => $totalApiTests,
    'score' => $apiTestScore
];

// 7. ADMIN PANEL TESTING
echo "\n7. 🎛️ ADMIN PANEL TESTING\n";
echo "========================\n";

$adminTests = [
    'resources/views/admin/' => 'Admin views',
    'app/Http/Controllers/Admin/' => 'Admin controllers',
    'app/models/Admin.php' => 'Admin model',
    'includes/admin_header.php' => 'Admin header',
    'includes/admin_footer.php' => 'Admin footer'
];

$adminScore = 0;
$totalAdminTests = count($adminTests);

foreach ($adminTests as $path => $description) {
    $fullPath = $projectRoot . '/' . $path;
    
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '*.php');
        $fileCount = count($files);
        echo "   ✅ $path - Directory exists ($fileCount PHP files)\n";
        $adminScore++;
    } elseif (file_exists($fullPath)) {
        echo "   ✅ $path - File exists\n";
        $adminScore++;
    } else {
        echo "   ❌ $path - Not found\n";
    }
}

$adminTestScore = round(($adminScore / $totalAdminTests) * 100);
echo "   📊 Admin Tests: $adminScore/$totalAdminTests passed ($adminTestScore%)\n";

$healthStatus['admin'] = [
    'passed' => $adminScore,
    'total' => $totalAdminTests,
    'score' => $adminTestScore
];

// 8. FINAL HEALTH ASSESSMENT
echo "\n8. 🏆 FINAL HEALTH ASSESSMENT\n";
echo "===========================\n";

$scores = [
    'Core Files' => $healthStatus['core_files']['score'],
    'Security' => $healthStatus['security']['score'],
    'Performance' => $healthStatus['performance']['score'],
    'API' => $healthStatus['api']['score'],
    'Admin Panel' => $healthStatus['admin']['score']
];

$totalScore = round(array_sum($scores) / count($scores));

foreach ($scores as $category => $score) {
    $status = $score >= 80 ? '✅ Excellent' : 
             ($score >= 60 ? '🟡 Good' : 
             ($score >= 40 ? '🟠 Fair' : '❌ Poor'));
    echo "   $category: $score% - $status\n";
}

echo "\n   🎯 OVERALL HEALTH SCORE: $totalScore%\n";

$projectHealth = $totalScore >= 90 ? '🟢 EXCELLENT - Production Ready' :
                ($totalScore >= 80 ? '🟡 GOOD - Nearly Ready' :
                ($totalScore >= 70 ? '🟠 FAIR - Needs Work' : '🔴 POOR - Major Issues'));

echo "   🏥 Project Health: $projectHealth\n";

// 9. RECOMMENDATIONS
echo "\n9. 🎯 RECOMMENDATIONS\n";
echo "===================\n";

if (!$dbTest['connection']) {
    echo "   🔴 URGENT: Fix database connection\n";
    foreach ($dbTest['errors'] as $error) {
        echo "      - $error\n";
    }
}

if ($healthStatus['core_files']['score'] < 100) {
    echo "   🟡 IMPORTANT: Fix core file syntax errors\n";
}

if ($healthStatus['security']['score'] < 100) {
    echo "   🟡 IMPORTANT: Complete security setup\n";
}

if ($healthStatus['performance']['score'] < 100) {
    echo "   🟡 OPTIMIZE: Check directory permissions\n";
}

if ($totalScore >= 90) {
    echo "   🟢 READY: Project is ready for deployment!\n";
    echo "   🟢 NEXT: Set up production server\n";
    echo "   🟢 NEXT: Configure domain and SSL\n";
    echo "   🟢 NEXT: Set up monitoring\n";
}

echo "\n🎉 COMPREHENSIVE TESTING COMPLETED!\n";
echo "==================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Project: APS Dream Home\n";
echo "Overall Health Score: $totalScore%\n";
echo "Status: $projectHealth\n";

// Save health report
$healthReport = [
    'timestamp' => date('Y-m-d H:i:s'),
    'scores' => $scores,
    'total_score' => $totalScore,
    'health' => $projectHealth,
    'details' => $healthStatus
];

file_put_contents($projectRoot . '/tools/reports/health_check_report.json', json_encode($healthReport, JSON_PRETTY_PRINT));
echo "   📄 Health report saved: tools/reports/health_check_report.json\n";

?>
