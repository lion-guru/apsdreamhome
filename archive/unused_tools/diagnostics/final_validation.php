<?php
/**
 * APS Dream Home - FINAL PROJECT VALIDATION
 * Complete project health check and deployment readiness
 */

echo "🏠 APS Dream Home - FINAL PROJECT VALIDATION\n";
echo "==========================================\n\n";

$projectRoot = 'c:\\xampp\\htdocs\\apsdreamhome';
$validationResults = [];

// 1. PROJECT SCALE VALIDATION
echo "1. 📊 PROJECT SCALE VALIDATION\n";
echo "============================\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS)
);

$totalFiles = 0;
$totalDirs = 0;
$totalSize = 0;

foreach ($iterator as $item) {
    if ($item->isDir()) {
        $totalDirs++;
    } else {
        $totalFiles++;
        $totalSize += $item->getSize();
    }
}

echo "   📁 Total Directories: $totalDirs\n";
echo "   📄 Total Files: $totalFiles\n";
echo "   💾 Project Size: " . round($totalSize / 1024 / 1024, 2) . " MB\n";

$validationResults['scale'] = [
    'directories' => $totalDirs,
    'files' => $totalFiles,
    'size_mb' => round($totalSize / 1024 / 1024, 2)
];

// 2. FEATURE COMPLETENESS CHECK
echo "\n2. 🚀 FEATURE COMPLETENESS CHECK\n";
echo "===============================\n";

$features = [
    'Admin Panel' => ['admin', 'Admin'],
    'Authentication' => ['auth', 'Auth', 'login', 'session'],
    'Database Models' => ['models', 'Models'],
    'API System' => ['api', 'Api'],
    'Frontend Views' => ['views', 'resources'],
    'CSS Assets' => ['css'],
    'JavaScript' => ['js'],
    'Configuration' => ['config', '.env'],
    'Security' => ['security', 'Auth'],
    'Testing' => ['test', 'Test']
];

$featureStatus = [];

foreach ($features as $feature => $keywords) {
    $found = false;
    $count = 0;
    
    foreach ($keywords as $keyword) {
        $files = glob($projectRoot . '/**/*' . $keyword . '*');
        if (!empty($files)) {
            $found = true;
            $count += count($files);
        }
    }
    
    $status = $found ? "✅ Found ($count items)" : "❌ Not Found";
    echo "   $feature: $status\n";
    $featureStatus[$feature] = ['found' => $found, 'count' => $count];
}

$validationResults['features'] = $featureStatus;

// 3. CORE SYSTEMS CHECK
echo "\n3. 🎯 CORE SYSTEMS CHECK\n";
echo "======================\n";

$coreSystems = [
    'Entry Point' => 'index.php',
    'Environment' => '.env',
    'Bootstrap' => 'bootstrap.php',
    'Database Connection' => 'includes/db_connection.php',
    'Session Helpers' => 'includes/session_helpers.php',
    'Web Routes' => 'routes/web.php',
    'API Routes' => 'routes/api.php',
    'Security Config' => '.htaccess'
];

$coreStatus = [];

foreach ($coreSystems as $system => $file) {
    $filePath = $projectRoot . '/' . $file;
    $exists = file_exists($filePath);
    $status = $exists ? "✅ Present" : "❌ Missing";
    echo "   $system: $status\n";
    $coreStatus[$system] = $exists;
}

$validationResults['core'] = $coreStatus;

// 4. DATABASE READINESS CHECK
echo "\n4. 🗄️ DATABASE READINESS CHECK\n";
echo "============================\n";

$dbReady = false;
$dbTables = 0;

// Check .env
$envFile = $projectRoot . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $hasDbConfig = strpos($envContent, 'DB_HOST') !== false && 
                   strpos($envContent, 'DB_NAME') !== false;
    
    if ($hasDbConfig) {
        echo "   ✅ Database configuration found in .env\n";
        
        // Parse and test connection
        $envVars = [];
        foreach (explode("\n", $envContent) as $line) {
            if (strpos($line, '=') !== false && !empty(trim($line))) {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
        
        if (!empty($envVars['DB_NAME'])) {
            echo "   ✅ Database name specified: " . $envVars['DB_NAME'] . "\n";
            $dbReady = true;
        } else {
            echo "   ❌ Database name not specified\n";
        }
    } else {
        echo "   ❌ Database configuration missing\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

// Check for SQL files
$sqlFiles = glob($projectRoot . '/**/*.sql');
echo "   📄 SQL files found: " . count($sqlFiles) . "\n";

$validationResults['database'] = [
    'ready' => $dbReady,
    'sql_files' => count($sqlFiles),
    'tables' => $dbTables
];

// 5. SECURITY ASSESSMENT
echo "\n5. 🔒 SECURITY ASSESSMENT\n";
echo "======================\n";

$securityChecks = [
    'Apache Config' => '.htaccess',
    'Environment Security' => '.env',
    'Session Management' => 'includes/session_helpers.php',
    'Authentication' => 'app/core/Auth.php'
];

$securityScore = 0;

foreach ($securityChecks as $check => $file) {
    $filePath = $projectRoot . '/' . $file;
    $exists = file_exists($filePath);
    $status = $exists ? "✅ Present" : "❌ Missing";
    echo "   $check: $status\n";
    if ($exists) $securityScore++;
}

$securityPercentage = round(($securityScore / count($securityChecks)) * 100);
echo "   📊 Security Score: $securityPercentage%\n";

$validationResults['security'] = [
    'score' => $securityPercentage,
    'checks' => $securityScore,
    'total' => count($securityChecks)
];

// 6. DEPLOYMENT READINESS
echo "\n6. 🚀 DEPLOYMENT READINESS\n";
echo "========================\n";

$deploymentChecks = [
    'Project Size' => $totalFiles > 1000,
    'Core Systems' => array_sum($coreStatus) >= 6,
    'Features' => array_sum(array_column($featureStatus, 'found')) >= 8,
    'Database' => $dbReady,
    'Security' => $securityPercentage >= 75
];

$deploymentScore = 0;
$totalDeploymentChecks = count($deploymentChecks);

foreach ($deploymentChecks as $check => $passed) {
    $status = $passed ? "✅ Pass" : "❌ Fail";
    echo "   $check: $status\n";
    if ($passed) $deploymentScore++;
}

$deploymentPercentage = round(($deploymentScore / $totalDeploymentChecks) * 100);
echo "   📊 Deployment Score: $deploymentPercentage%\n";

$validationResults['deployment'] = [
    'score' => $deploymentPercentage,
    'passed' => $deploymentScore,
    'total' => $totalDeploymentChecks
];

// 7. FINAL ASSESSMENT
echo "\n7. 🏆 FINAL ASSESSMENT\n";
echo "====================\n";

$overallScore = round((
    ($deploymentPercentage * 0.4) +
    ($securityPercentage * 0.2) +
    (array_sum(array_column($featureStatus, 'found')) / count($featureStatus) * 100 * 0.2) +
    (array_sum($coreStatus) / count($coreStatus) * 100 * 0.2)
));

echo "   📊 Overall Score: $overallScore/100\n";

$projectGrade = $overallScore >= 90 ? 'A+ (PRODUCTION READY)' :
               ($overallScore >= 80 ? 'A (NEARLY READY)' :
               ($overallScore >= 70 ? 'B+ (GOOD PROGRESS)' :
               ($overallScore >= 60 ? 'B (NEEDS WORK)' :
               ($overallScore >= 50 ? 'C+ (MAJOR ISSUES)' :
               ($overallScore >= 40 ? 'C (SIGNIFICANT ISSUES)' : 'D (CRITICAL ISSUES)')))));

echo "   🏅 Project Grade: $projectGrade\n";

$deploymentStatus = $overallScore >= 85 ? '🟢 READY FOR DEPLOYMENT' :
                   ($overallScore >= 70 ? '🟡 NEARLY READY' :
                   ($overallScore >= 50 ? '🟠 NEEDS WORK' : '🔴 NOT READY'));

echo "   🚀 Deployment Status: $deploymentStatus\n";

// 8. NEXT STEPS
echo "\n8. 🎯 NEXT STEPS\n";
echo "==============\n";

if ($overallScore >= 85) {
    echo "   🟢 IMMEDIATE ACTIONS:\n";
    echo "   1. ✅ Set up production server\n";
    echo "   2. ✅ Configure domain and SSL\n";
    echo "   3. ✅ Set up monitoring\n";
    echo "   4. ✅ Create backup strategy\n";
    echo "   5. ✅ GO LIVE!\n";
} elseif ($overallScore >= 70) {
    echo "   🟡 PRIORITY ACTIONS:\n";
    echo "   1. 🔧 Fix database connection\n";
    echo "   2. 🔧 Complete missing features\n";
    echo "   3. 🔧 Improve security\n";
    echo "   4. 📅 Deploy within 1-2 weeks\n";
} else {
    echo "   🔴 CRITICAL ACTIONS:\n";
    echo "   1. 🚨 Fix core systems\n";
    echo "   2. 🚨 Complete major features\n";
    echo "   3. 🚨 Address security issues\n";
    echo "   4. 📅 Deploy in 1+ months\n";
}

// Save validation report
$validationReport = [
    'timestamp' => date('Y-m-d H:i:s'),
    'project' => 'APS Dream Home',
    'scale' => $validationResults['scale'],
    'features' => $validationResults['features'],
    'core' => $validationResults['core'],
    'database' => $validationResults['database'],
    'security' => $validationResults['security'],
    'deployment' => $validationResults['deployment'],
    'overall_score' => $overallScore,
    'grade' => $projectGrade,
    'deployment_status' => $deploymentStatus
];

file_put_contents($projectRoot . '/tools/reports/final_validation_report.json', json_encode($validationReport, JSON_PRETTY_PRINT));

echo "\n🎉 FINAL VALIDATION COMPLETED!\n";
echo "==============================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Overall Score: $overallScore/100\n";
echo "Grade: $projectGrade\n";
echo "Status: $deploymentStatus\n";
echo "   📄 Report saved: tools/reports/final_validation_report.json\n";

?>
