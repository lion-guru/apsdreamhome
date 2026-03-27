<?php
/**
 * APS Dream Home - Deployment Verification Script
 * 
 * This script verifies that APS Dream Home has been properly deployed
 * and all components are working correctly.
 */

echo "<!DOCTYPE html>\n";
echo "<html lang='en'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>🧪 APS Dream Home - Deployment Verification</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }\n";
echo "        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        .header { text-align: center; margin-bottom: 30px; }\n";
echo "        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }\n";
echo "        .test-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }\n";
echo "        .test-item { margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 3px; }\n";
echo "        .success { color: #28a745; font-weight: bold; }\n";
echo "        .error { color: #dc3545; font-weight: bold; }\n";
echo "        .warning { color: #ffc107; font-weight: bold; }\n";
echo "        .info { color: #17a2b8; }\n";
echo "        .progress { width: 100%; height: 20px; background: #e9ecef; border-radius: 10px; margin: 10px 0; }\n";
echo "        .progress-bar { height: 20px; background: #28a745; border-radius: 10px; text-align: center; line-height: 20px; color: white; font-weight: bold; }\n";
echo "        .summary { margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px; }\n";
echo "        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px; }\n";
echo "        .btn:hover { background: #0056b3; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <div class='container'>\n";
echo "        <div class='header'>\n";
echo "            <h1>🧪 APS Dream Home</h1>\n";
echo "            <h2>Deployment Verification</h2>\n";
echo "        </div>\n";

// Test Results Array
$tests = [];
$passed = 0;
$failed = 0;
$warnings = 0;

// Test 1: PHP Version Check
echo "        <div class='test-section'>\n";
echo "            <div class='test-title'>🔧 PHP Environment Check</div>\n";

$phpVersion = phpversion();
$requiredVersion = '8.0';
if (version_compare($phpVersion, $requiredVersion, '>=')) {
    echo "            <div class='test-item success'>✅ PHP Version: $phpVersion (Required: $requiredVersion+)</div>\n";
    $tests['php_version'] = 'PASS';
    $passed++;
} else {
    echo "            <div class='test-item error'>❌ PHP Version: $phpVersion (Required: $requiredVersion+)</div>\n";
    $tests['php_version'] = 'FAIL';
    $failed++;
}

// Test 2: Required PHP Extensions
$requiredExtensions = ['mysqli', 'gd', 'curl', 'json', 'mbstring', 'openssl'];
echo "            <div class='test-title'>📦 PHP Extensions Check</div>\n";

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "            <div class='test-item success'>✅ Extension Loaded: $ext</div>\n";
        $tests['ext_' . $ext] = 'PASS';
        $passed++;
    } else {
        echo "            <div class='test-item error'>❌ Extension Missing: $ext</div>\n";
        $tests['ext_' . $ext] = 'FAIL';
        $failed++;
    }
}

// Test 3: Database Connection
echo "            <div class='test-title'>🗄️ Database Connection Check</div>\n";

try {
    $mysqli = new mysqli("localhost", "root", "", "apsdreamhome");
    if ($mysqli->connect_error) {
        echo "            <div class='test-item error'>❌ Database Connection: Failed - " . $mysqli->connect_error . "</div>\n";
        $tests['database_connection'] = 'FAIL';
        $failed++;
    } else {
        echo "            <div class='test-item success'>✅ Database Connection: Successful</div>\n";
        $tests['database_connection'] = 'PASS';
        $passed++;
        
        // Test 4: Database Tables Check
        echo "            <div class='test-title'>📊 Database Tables Check</div>\n";
        
        $result = $mysqli->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        
        if ($tableCount > 0) {
            echo "            <div class='test-item success'>✅ Database Tables: $tableCount tables found</div>\n";
            $tests['database_tables'] = 'PASS';
            $passed++;
        } else {
            echo "            <div class='test-item error'>❌ Database Tables: No tables found</div>\n";
            $tests['database_tables'] = 'FAIL';
            $failed++;
        }
        
        // Test 5: Sample Data Check
        echo "            <div class='test-title'>📋 Sample Data Check</div>\n";
        
        $tables = ['users', 'properties', 'projects'];
        $dataFound = 0;
        
        foreach ($tables as $table) {
            $result = $mysqli->query("SELECT COUNT(*) as count FROM $table LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $count = $row['count'];
                echo "            <div class='test-item success'>✅ Table '$table': $count records</div>\n";
                $dataFound++;
                $passed++;
            } else {
                echo "            <div class='test-item warning'>⚠️ Table '$table': No data or table missing</div>\n";
                $warnings++;
            }
        }
        
        if ($dataFound > 0) {
            $tests['sample_data'] = 'PASS';
        } else {
            $tests['sample_data'] = 'PARTIAL';
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "            <div class='test-item error'>❌ Database Connection Exception: " . $e->getMessage() . "</div>\n";
    $tests['database_connection'] = 'FAIL';
    $failed++;
}

// Test 6: File Structure Check
echo "            <div class='test-title'>📁 File Structure Check</div>\n";

$requiredFiles = [
    'app/Core/Controller.php',
    'app/Http/Controllers/BaseController.php',
    'app/Http/Controllers/Controller.php',
    'public/index.php',
    'config/database.php',
    'composer.json',
    '.htaccess'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "            <div class='test-item success'>✅ File Exists: $file</div>\n";
        $tests['file_' . str_replace(['/', '.'], ['_', ''], $file)] = 'PASS';
        $passed++;
    } else {
        echo "            <div class='test-item error'>❌ File Missing: $file</div>\n";
        $tests['file_' . str_replace(['/', '.'], ['_', ''], $file)] = 'FAIL';
        $failed++;
    }
}

// Test 7: Directory Permissions Check
echo "            <div class='test-title'>🔐 Directory Permissions Check</div>\n";

$requiredDirs = ['app', 'public', 'config', 'storage'];
foreach ($requiredDirs as $dir) {
    if (is_dir($dir) && is_readable($dir)) {
        echo "            <div class='test-item success'>✅ Directory Readable: $dir</div>\n";
        $tests['dir_' . $dir] = 'PASS';
        $passed++;
    } else {
        echo "            <div class='test-item error'>❌ Directory Not Readable: $dir</div>\n";
        $tests['dir_' . $dir] = 'FAIL';
        $failed++;
    }
}

// Test 8: Web Server Configuration
echo "            <div class='test-title'>🌐 Web Server Check</div>\n";

if (isset($_SERVER['SERVER_SOFTWARE'])) {
    echo "            <div class='test-item success'>✅ Web Server: " . $_SERVER['SERVER_SOFTWARE'] . "</div>\n";
    $tests['web_server'] = 'PASS';
    $passed++;
} else {
    echo "            <div class='test-item info'>ℹ️ Web Server: Running from CLI</div>\n";
    $tests['web_server'] = 'CLI';
    $passed++;
}

// Test 9: Memory and Performance
echo "            <div class='test-title'>⚡ Performance Check</div>\n";

$memoryLimit = ini_get('memory_limit');
$maxExecutionTime = ini_get('max_execution_time');
echo "            <div class='test-item info'>ℹ️ Memory Limit: $memoryLimit</div>\n";
echo "            <div class='test-item info'>ℹ️ Max Execution Time: $maxExecutionTime seconds</div>\n";

if (strpos($memoryLimit, 'M') !== false && intval($memoryLimit) >= 128) {
    echo "            <div class='test-item success'>✅ Memory Limit: Adequate ($memoryLimit)</div>\n";
    $tests['memory_limit'] = 'PASS';
    $passed++;
} else {
    echo "            <div class='test-item warning'>⚠️ Memory Limit: May be insufficient ($memoryLimit)</div>\n";
    $tests['memory_limit'] = 'WARNING';
    $warnings++;
}

// Calculate Success Rate
$totalTests = $passed + $failed;
$successRate = $totalTests > 0 ? round(($passed / $totalTests) * 100, 2) : 0;

// Summary Section
echo "        </div>\n";
echo "        <div class='summary'>\n";
echo "            <div class='test-title'>📊 Deployment Summary</div>\n";

echo "            <div class='progress'>\n";
echo "                <div class='progress-bar' style='width: $successRate%'>$successRate% SUCCESS RATE</div>\n";
echo "            </div>\n";

echo "            <div class='test-item'>✅ Tests Passed: $passed</div>\n";
echo "            <div class='test-item'>❌ Tests Failed: $failed</div>\n";
echo "            <div class='test-item'>⚠️ Warnings: $warnings</div>\n";
echo "            <div class='test-item'>📈 Success Rate: $successRate%</div>\n";

// Overall Status
if ($failed === 0 && $warnings === 0) {
    echo "            <div class='test-item success' style='font-size: 20px; margin-top: 20px;'>🎉 DEPLOYMENT SUCCESSFUL!</div>\n";
    echo "            <div class='test-item success'>✅ APS Dream Home is ready for production use</div>\n";
} elseif ($failed === 0 && $warnings > 0) {
    echo "            <div class='test-item warning' style='font-size: 20px; margin-top: 20px;'>⚠️ DEPLOYMENT SUCCESSFUL WITH WARNINGS</div>\n";
    echo "            <div class='test-item warning'>⚠️ APS Dream Home is functional but has warnings</div>\n";
} else {
    echo "            <div class='test-item error' style='font-size: 20px; margin-top: 20px;'>❌ DEPLOYMENT NEEDS ATTENTION</div>\n";
    echo "            <div class='test-item error'>❌ APS Dream Home has issues that need to be resolved</div>\n";
}

echo "            <div class='test-title'>📋 System Information</div>\n";
echo "            <div class='test-item info'>ℹ️ PHP Version: " . phpversion() . "</div>\n";
echo "            <div class='test-item info'>ℹ️ Server OS: " . PHP_OS . "</div>\n";
echo "            <div class='test-item info'>ℹ️ Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</div>\n";
echo "            <div class='test-item info'>ℹ️ Verification Time: " . date('Y-m-d H:i:s') . "</div>\n";

echo "            <div class='test-title'>🔧 Next Steps</div>\n";
if ($failed === 0) {
    echo "            <div class='test-item success'>✅ 1. Test main application features</div>\n";
    echo "            <div class='test-item success'>✅ 2. Verify user registration/login</div>\n";
    echo "            <div class='test-item success'>✅ 3. Test admin panel access</div>\n";
    echo "            <div class='test-item success'>✅ 4. Report deployment status to admin</div>\n";
} else {
    echo "            <div class='test-item error'>❌ 1. Fix failed tests above</div>\n";
    echo "            <div class='test-item error'>❌ 2. Re-run verification script</div>\n";
    echo "            <div class='test-item error'>❌ 3. Contact admin for support</div>\n";
    echo "            <div class='test-item error'>❌ 4. Review setup instructions</div>\n";
}

echo "            <div style='margin-top: 30px; text-align: center;'>\n";
echo "                <button class='btn' onclick='window.print()'>🖨️ Print Report</button>\n";
echo "                <button class='btn' onclick='window.location.reload()'>🔄 Refresh Test</button>\n";
echo "            </div>\n";

echo "        </div>\n";
echo "    </div>\n";
echo "</body>\n";
echo "</html>\n";

// Store test results for potential logging
file_put_contents('deployment_verification_results.json', json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'tests' => $tests,
    'summary' => [
        'passed' => $passed,
        'failed' => $failed,
        'warnings' => $warnings,
        'success_rate' => $successRate
    ]
], JSON_PRETTY_PRINT));

?>
