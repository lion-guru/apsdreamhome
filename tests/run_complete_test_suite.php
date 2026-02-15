<?php
/**
 * Complete Test Suite Runner for APS Dream Home
 * Runs all test suites and provides comprehensive reporting
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Complete Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; }
        .test-suite { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background-color: #f8f9fa; }
        .success { border-left-color: #28a745; background-color: #d4edda; }
        .warning { border-left-color: #ffc107; background-color: #fff3cd; }
        .danger { border-left-color: #dc3545; background-color: #f8d7da; }
        .summary { margin: 20px 0; padding: 20px; background-color: #e9ecef; border-radius: 8px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: white; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2em; font-weight: bold; color: #007bff; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .progress { width: 100%; height: 20px; background-color: #e9ecef; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); transition: width 0.3s ease; }
        .loading { text-align: center; padding: 20px; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🧪 APS Dream Home - Complete Test Suite</h1>
            <p>Comprehensive testing platform for real estate management system</p>
        </div>";

// Function to run a test suite and capture output
function runTestSuite($suiteName, $suitePath, $description) {
    echo "<div class='test-suite'>
            <h3>🔍 Running {$suiteName}</h3>
            <p><em>{$description}</em></p>
            <div class='loading'>
                <div class='spinner'></div>
                <p>Executing tests...</p>
            </div>
          </div>";
    
    ob_start();
    include $suitePath;
    $output = ob_get_clean();
    
    return $output;
}

// Function to extract test results from output
function extractResults($output) {
    $results = ['passed' => 0, 'failed' => 0, 'skipped' => 0, 'total' => 0, 'passRate' => 0];
    
    // Extract total tests
    if (preg_match('/Total Tests:\s*(\d+)/', $output, $matches)) {
        $results['total'] = (int)$matches[1];
    }
    
    // Extract passed tests
    if (preg_match('/Passed:\s*<span[^>]*>(\d+)<\/span>/', $output, $matches)) {
        $results['passed'] = (int)$matches[1];
    }
    
    // Extract failed tests
    if (preg_match('/Failed:\s*<span[^>]*>(\d+)<\/span>/', $output, $matches)) {
        $results['failed'] = (int)$matches[1];
    }
    
    // Extract skipped tests
    if (preg_match('/Skipped:\s*<span[^>]*>(\d+)<\/span>/', $output, $matches)) {
        $results['skipped'] = (int)$matches[1];
    }
    
    // Calculate pass rate
    if ($results['total'] > 0) {
        $results['passRate'] = round(($results['passed'] / $results['total']) * 100, 2);
    }
    
    return $results;
}

// Define all test suites
$testSuites = [
    'comprehensive' => [
        'name' => 'Comprehensive Test Suite',
        'path' => 'tests/ComprehensiveTestSuite.php',
        'description' => 'Complete functionality testing including database, CRUD operations, and file system'
    ],
    'integration' => [
        'name' => 'API Integration Tests',
        'path' => 'tests/Integration/ApiIntegrationTest.php',
        'description' => 'API endpoints, data flow, and system integration testing'
    ],
    'performance' => [
        'name' => 'Performance Tests',
        'path' => 'tests/Performance/PerformanceTest.php',
        'description' => 'System performance, memory usage, and load testing'
    ],
    'database' => [
        'name' => 'Database Connection Test',
        'path' => 'test_database_standalone.php',
        'description' => 'Database connectivity and basic operations'
    ]
];

// Check which test to run
$requestedTest = $_GET['test'] ?? 'all';

if ($requestedTest === 'all') {
    echo "<div class='summary'>
            <h2>🚀 Running All Test Suites</h2>
            <p>This will execute all available test suites and provide a comprehensive report...</p>
            <div class='progress'>
                <div class='progress-bar' style='width: 0%'></div>
            </div>
          </div>";
    
    $allResults = [];
    $completedTests = 0;
    $totalTests = count($testSuites);
    
    foreach ($testSuites as $key => $suite) {
        echo "<script>
                document.querySelector('.progress-bar').style.width = '" . (($completedTests / $totalTests) * 100) . "%';
              </script>";
        
        $output = runTestSuite($suite['name'], $suite['path'], $suite['description']);
        $results = extractResults($output);
        $allResults[$key] = array_merge($suite, $results);
        
        // Replace loading with actual results
        echo "<script>
                var loadingDiv = document.querySelectorAll('.test-suite')[" . ($completedTests) . "];
                loadingDiv.innerHTML = `
                    <h3>" . ($results['failed'] > 0 ? '⚠️' : '✅') . " {$suite['name']}</h3>
                    <p><em>{$suite['description']}</em></p>
                    <div class='stats'>
                        <div class='stat-card'>
                            <div class='stat-number'>{$results['total']}</div>
                            <div>Total Tests</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number' style='color: #28a745;'>{$results['passed']}</div>
                            <div>Passed</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number' style='color: #dc3545;'>{$results['failed']}</div>
                            <div>Failed</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number' style='color: #ffc107;'>{$results['skipped']}</div>
                            <div>Skipped</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number'>{$results['passRate']}%</div>
                            <div>Pass Rate</div>
                        </div>
                    </div>
                    <a href='?test={$key}' class='btn btn-primary'>View Details</a>
                    <a href='{$suite['path']}' class='btn btn-success'>Run Standalone</a>
                `;
                loadingDiv.className = 'test-suite " . ($results['failed'] > 0 ? 'warning' : ($results['passRate'] == 100 ? 'success' : '')) . "';
              </script>";
        
        $completedTests++;
        echo "<script>
                document.querySelector('.progress-bar').style.width = '" . (($completedTests / $totalTests) * 100) . "%';
              </script>";
        
        flush(); // Send output to browser
    }
    
    // Calculate overall results
    $overallResults = [
        'total' => array_sum(array_column($allResults, 'total')),
        'passed' => array_sum(array_column($allResults, 'passed')),
        'failed' => array_sum(array_column($allResults, 'failed')),
        'skipped' => array_sum(array_column($allResults, 'skipped'))
    ];
    
    if ($overallResults['total'] > 0) {
        $overallResults['passRate'] = round(($overallResults['passed'] / $overallResults['total']) * 100, 2);
    } else {
        $overallResults['passRate'] = 0;
    }
    
    echo "<div class='summary'>
            <h2>📊 Overall Test Results</h2>
            <div class='stats'>
                <div class='stat-card'>
                    <div class='stat-number'>{$overallResults['total']}</div>
                    <div>Total Tests</div>
                </div>
                <div class='stat-card'>
                    <div class='stat-number' style='color: #28a745;'>{$overallResults['passed']}</div>
                    <div>Passed</div>
                </div>
                <div class='stat-card'>
                    <div class='stat-number' style='color: #dc3545;'>{$overallResults['failed']}</div>
                    <div>Failed</div>
                </div>
                <div class='stat-card'>
                    <div class='stat-number' style='color: #ffc107;'>{$overallResults['skipped']}</div>
                    <div>Skipped</div>
                </div>
                <div class='stat-card'>
                    <div class='stat-number'>{$overallResults['passRate']}%</div>
                    <div>Overall Pass Rate</div>
                </div>
            </div>";
    
    if ($overallResults['failed'] > 0) {
        echo "<div class='test-suite warning'>
                <h3>⚠️ Action Required</h3>
                <p>Some tests failed. Please review the detailed results above and address any issues.</p>
              </div>";
    } else {
        echo "<div class='test-suite success'>
                <h3>✅ Excellent!</h3>
                <p>All test suites are passing. The APS Dream Home system is functioning correctly.</p>
              </div>";
    }
    
    echo "</div>";
    
} elseif (isset($testSuites[$requestedTest])) {
    $suite = $testSuites[$requestedTest];
    echo "<div class='summary'>
            <h2>🔍 {$suite['name']}</h2>
            <p><em>{$suite['description']}</em></p>
          </div>";
    
    // Run the specific test suite
    include $suite['path'];
    
    echo "<div class='summary'>
            <a href='?' class='btn btn-primary'>← Back to All Tests</a>
            <a href='{$suite['path']}' class='btn btn-success'>Run Standalone</a>
          </div>";
} else {
    echo "<div class='test-suite danger'>
            <h3>❌ Test Suite Not Found</h3>
            <p>The requested test suite '{$requestedTest}' does not exist.</p>
            <a href='?' class='btn btn-primary'>← Back to All Tests</a>
          </div>";
}

echo "<div class='summary'>
        <h2>🔧 Test Environment</h2>
        <div class='stats'>
            <div class='stat-card'>
                <div class='stat-number'>" . PHP_VERSION . "</div>
                <div>PHP Version</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>" . (defined('DB_HOST') ? DB_HOST : 'N/A') . "</div>
                <div>Database Host</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>" . (defined('DB_NAME') ? DB_NAME : 'N/A') . "</div>
                <div>Database Name</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>" . date('Y-m-d H:i:s') . "</div>
                <div>Test Date</div>
            </div>
        </div>
        
        <h3>🚀 Quick Actions</h3>
        <p>
            <a href='?test=comprehensive' class='btn btn-primary'>Run Comprehensive Tests</a>
            <a href='?test=integration' class='btn btn-primary'>Run Integration Tests</a>
            <a href='?test=performance' class='btn btn-primary'>Run Performance Tests</a>
            <a href='?test=database' class='btn btn-primary'>Run Database Tests</a>
        </p>
        
        <h3>📁 Individual Test Files</h3>
        <ul>";
foreach ($testSuites as $key => $suite) {
    echo "<li><a href='{$suite['path']}' target='_blank'>{$suite['name']}</a> - {$suite['description']}</li>";
}
echo "</ul>
      </div>
    </div>
</body>
</html>";
?>
