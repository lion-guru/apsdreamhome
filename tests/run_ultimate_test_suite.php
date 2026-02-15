<?php
/**
 * Ultimate Test Suite Runner for APS Dream Home
 * Runs ALL test suites with comprehensive reporting and analysis
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Ultimate Test Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; }
        .test-suite { margin: 20px 0; padding: 20px; border-left: 4px solid #007bff; background-color: #f8f9fa; border-radius: 0 8px 8px 0; }
        .success { border-left-color: #28a745; background-color: #d4edda; }
        .warning { border-left-color: #ffc107; background-color: #fff3cd; }
        .danger { border-left-color: #dc3545; background-color: #f8d7da; }
        .info { border-left-color: #17a2b8; background-color: #d1ecf1; }
        .summary { margin: 20px 0; padding: 25px; background-color: #e9ecef; border-radius: 8px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #007bff; margin-bottom: 10px; }
        .stat-label { font-size: 0.9em; color: #6c757d; text-transform: uppercase; letter-spacing: 1px; }
        .progress { width: 100%; height: 25px; background-color: #e9ecef; border-radius: 12px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); transition: width 0.5s ease; border-radius: 12px; }
        .loading { text-align: center; padding: 30px; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 15px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .final-summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; text-align: center; margin-top: 30px; }
        .btn { padding: 12px 24px; margin: 8px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 500; transition: all 0.2s; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; color: white; }
        .timeline { position: relative; padding-left: 30px; }
        .timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #007bff; }
        .timeline-item { position: relative; margin-bottom: 20px; padding: 15px; background: white; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .timeline-item::before { content: ''; position: absolute; left: -34px; top: 20px; width: 10px; height: 10px; border-radius: 50%; background: #007bff; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🧪 APS Dream Home - Ultimate Test Suite</h1>
            <p>Complete enterprise-grade testing platform with comprehensive analysis</p>
            <p><em>Running all test suites with detailed reporting and recommendations...</em></p>
        </div>";

// Function to run a test suite and capture output
function runUltimateTestSuite($suiteName, $suitePath, $description, $category) {
    echo "<div class='test-suite'>
            <h3>🔍 Running {$suiteName}</h3>
            <p><strong>Category:</strong> {$category}</p>
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
function extractUltimateResults($output) {
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

// Define all ultimate test suites
$ultimateTestSuites = [
    'comprehensive' => [
        'name' => 'Comprehensive Test Suite',
        'path' => 'tests/ComprehensiveTestSuite.php',
        'description' => 'Complete functionality testing including database, CRUD operations, and file system',
        'category' => 'Core Functionality',
        'icon' => '🏗️'
    ],
    'integration' => [
        'name' => 'API Integration Tests',
        'path' => 'tests/Integration/ApiIntegrationTest.php',
        'description' => 'API endpoints, data flow, and system integration testing',
        'category' => 'Integration',
        'icon' => '🔗'
    ],
    'performance' => [
        'name' => 'Performance Tests',
        'path' => 'tests/Performance/PerformanceTest.php',
        'description' => 'System performance, memory usage, and load testing',
        'category' => 'Performance',
        'icon' => '⚡'
    ],
    'browser' => [
        'name' => 'Browser/UI Tests',
        'path' => 'tests/Browser/SeleniumTest.php',
        'description' => 'User interface, accessibility, and user experience testing',
        'category' => 'User Experience',
        'icon' => '🌐'
    ],
    'security' => [
        'name' => 'Security Audit Tests',
        'path' => 'tests/Security/SecurityAuditTest.php',
        'description' => 'Security vulnerability assessment and protection testing',
        'category' => 'Security',
        'icon' => '🔒'
    ],
    'database' => [
        'name' => 'Database Connection Test',
        'path' => 'test_database_standalone.php',
        'description' => 'Database connectivity and basic operations validation',
        'category' => 'Infrastructure',
        'icon' => '🗄️'
    ]
];

// Check which test to run
$requestedTest = $_GET['test'] ?? 'all';

if ($requestedTest === 'all') {
    echo "<div class='summary'>
            <h2>🚀 Running Ultimate Test Suite</h2>
            <p>This comprehensive test suite will execute all available tests and provide detailed analysis...</p>
            <div class='progress'>
                <div class='progress-bar' style='width: 0%'></div>
            </div>
            <div class='timeline' id='timeline'></div>
          </div>";
    
    $allResults = [];
    $completedTests = 0;
    $totalTests = count($ultimateTestSuites);
    $startTime = microtime(true);
    
    foreach ($ultimateTestSuites as $key => $suite) {
        $suiteStartTime = microtime(true);
        
        echo "<script>
                document.querySelector('.progress-bar').style.width = '" . (($completedTests / $totalTests) * 100) . "%';
                document.getElementById('timeline').innerHTML += '<div class=\"timeline-item\"><strong>{$suite['icon']} {$suite['name']}</strong><br><small>Starting...</small></div>';
              </script>";
        
        $output = runUltimateTestSuite($suite['name'], $suite['path'], $suite['description'], $suite['category']);
        $results = extractUltimateResults($output);
        $suiteEndTime = microtime(true);
        $executionTime = round(($suiteEndTime - $suiteStartTime) * 1000, 2);
        
        $allResults[$key] = array_merge($suite, $results, ['executionTime' => $executionTime]);
        
        // Determine status class
        $statusClass = 'success';
        $statusIcon = '✅';
        if ($results['failed'] > 0) {
            $statusClass = $results['passRate'] < 50 ? 'danger' : 'warning';
            $statusIcon = $results['passRate'] < 50 ? '❌' : '⚠️';
        }
        
        // Replace loading with actual results
        echo "<script>
                var loadingDiv = document.querySelectorAll('.test-suite')[" . ($completedTests) . "];
                var statusClass = '{$statusClass}';
                var statusIcon = '{$statusIcon}';
                loadingDiv.innerHTML = `
                    <h3>{$statusIcon} {$suite['name']}</h3>
                    <p><strong>Category:</strong> {$suite['category']}</p>
                    <p><em>{$suite['description']}</em></p>
                    <div class='stats'>
                        <div class='stat-card'>
                            <div class='stat-number'>{$results['total']}</div>
                            <div class='stat-label'>Total Tests</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number' style='color: #28a745;'>{$results['passed']}</div>
                            <div class='stat-label'>Passed</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number' style='color: #dc3545;'>{$results['failed']}</div>
                            <div class='stat-label'>Failed</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number' style='color: #ffc107;'>{$results['skipped']}</div>
                            <div class='stat-label'>Skipped</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number'>{$results['passRate']}%</div>
                            <div class='stat-label'>Pass Rate</div>
                        </div>
                        <div class='stat-card'>
                            <div class='stat-number'>{$executionTime}ms</div>
                            <div class='stat-label'>Execution Time</div>
                        </div>
                    </div>
                    <div style='margin-top: 15px;'>
                        <a href='?test={$key}' class='btn btn-primary'>View Details</a>
                        <a href='{$suite['path']}' class='btn btn-success'>Run Standalone</a>
                        <span class='badge badge-{$statusClass}'>{$results['passRate']}% Pass Rate</span>
                    </div>
                `;
                loadingDiv.className = 'test-suite {$statusClass}';
                
                document.getElementById('timeline').innerHTML += '<div class=\"timeline-item\"><strong>{$suite['icon']} {$suite['name']}</strong><br><small>Completed: {$results['passRate']}% pass rate ({$executionTime}ms)</small></div>';
              </script>";
        
        $completedTests++;
        echo "<script>
                document.querySelector('.progress-bar').style.width = '" . (($completedTests / $totalTests) * 100) . "%';
              </script>";
        
        flush(); // Send output to browser
    }
    
    $endTime = microtime(true);
    $totalExecutionTime = round(($endTime - $startTime) * 1000, 2);
    
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
    
    // Determine overall status
    $overallStatus = 'success';
    $overallMessage = 'Excellent! All systems are functioning perfectly.';
    $overallIcon = '🏆';
    
    if ($overallResults['failed'] > 0) {
        if ($overallResults['passRate'] < 80) {
            $overallStatus = 'danger';
            $overallMessage = 'Critical issues found. Immediate action required.';
            $overallIcon = '🚨';
        } else {
            $overallStatus = 'warning';
            $overallMessage = 'Some issues found. Review and address as needed.';
            $overallIcon = '⚠️';
        }
    }
    
    echo "<div class='final-summary'>
            <h2>{$overallIcon} Ultimate Test Results</h2>
            <div class='stats'>
                <div class='stat-card' style='background: rgba(255,255,255,0.1);'>
                    <div class='stat-number' style='color: white;'>{$overallResults['total']}</div>
                    <div class='stat-label' style='color: rgba(255,255,255,0.8);'>Total Tests</div>
                </div>
                <div class='stat-card' style='background: rgba(255,255,255,0.1);'>
                    <div class='stat-number' style='color: #90EE90;'>{$overallResults['passed']}</div>
                    <div class='stat-label' style='color: rgba(255,255,255,0.8);'>Passed</div>
                </div>
                <div class='stat-card' style='background: rgba(255,255,255,0.1);'>
                    <div class='stat-number' style='color: #FFB6C1;'>{$overallResults['failed']}</div>
                    <div class='stat-label' style='color: rgba(255,255,255,0.8);'>Failed</div>
                </div>
                <div class='stat-card' style='background: rgba(255,255,255,0.1);'>
                    <div class='stat-number' style='color: #FFD700;'>{$overallResults['skipped']}</div>
                    <div class='stat-label' style='color: rgba(255,255,255,0.8);'>Skipped</div>
                </div>
                <div class='stat-card' style='background: rgba(255,255,255,0.1);'>
                    <div class='stat-number' style='color: #00FF00;'>{$overallResults['passRate']}%</div>
                    <div class='stat-label' style='color: rgba(255,255,255,0.8);'>Overall Pass Rate</div>
                </div>
                <div class='stat-card' style='background: rgba(255,255,255,0.1);'>
                    <div class='stat-number' style='color: #00BFFF;'>{$totalExecutionTime}ms</div>
                    <div class='stat-label' style='color: rgba(255,255,255,0.8);'>Total Time</div>
                </div>
            </div>
            <p style='font-size: 1.2em; margin-top: 20px;'>{$overallMessage}</p>
          </div>";
    
    // Category breakdown
    echo "<div class='summary'>
            <h2>📊 Category Breakdown</h2>
            <div class='stats'>";
    
    $categories = [];
    foreach ($allResults as $result) {
        $cat = $result['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = ['total' => 0, 'passed' => 0, 'failed' => 0, 'skipped' => 0];
        }
        $categories[$cat]['total'] += $result['total'];
        $categories[$cat]['passed'] += $result['passed'];
        $categories[$cat]['failed'] += $result['failed'];
        $categories[$cat]['skipped'] += $result['skipped'];
    }
    
    foreach ($categories as $catName => $catData) {
        $passRate = $catData['total'] > 0 ? round(($catData['passed'] / $catData['total']) * 100, 2) : 0;
        echo "<div class='stat-card'>
                <div class='stat-number'>{$passRate}%</div>
                <div class='stat-label'>{$catName}</div>
                <small>{$catData['passed']}/{$catData['total']} passed</small>
              </div>";
    }
    
    echo "</div></div>";
    
} elseif (isset($ultimateTestSuites[$requestedTest])) {
    $suite = $ultimateTestSuites[$requestedTest];
    echo "<div class='summary'>
            <h2>🔍 {$suite['name']}</h2>
            <p><strong>Category:</strong> {$suite['category']}</p>
            <p><em>{$suite['description']}</em></p>
          </div>";
    
    // Run the specific test suite
    include $suite['path'];
    
    echo "<div class='summary'>
            <a href='?' class='btn btn-primary'>← Back to Ultimate Suite</a>
            <a href='{$suite['path']}' class='btn btn-success'>Run Standalone</a>
          </div>";
} else {
    echo "<div class='test-suite danger'>
            <h3>❌ Test Suite Not Found</h3>
            <p>The requested test suite '{$requestedTest}' does not exist.</p>
            <a href='?' class='btn btn-primary'>← Back to Ultimate Suite</a>
          </div>";
}

echo "<div class='summary'>
        <h2>🔧 Test Environment</h2>
        <div class='stats'>
            <div class='stat-card'>
                <div class='stat-number'>" . PHP_VERSION . "</div>
                <div class='stat-label'>PHP Version</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>" . (defined('DB_HOST') ? DB_HOST : 'N/A') . "</div>
                <div class='stat-label'>Database Host</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>" . (defined('DB_NAME') ? DB_NAME : 'N/A') . "</div>
                <div class='stat-label'>Database Name</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>" . date('Y-m-d H:i:s') . "</div>
                <div class='stat-label'>Test Date</div>
            </div>
        </div>
        
        <h3>🚀 Quick Actions</h3>
        <p>";
foreach ($ultimateTestSuites as $key => $suite) {
    $btnClass = $key === 'comprehensive' ? 'btn-primary' : 'btn-success';
    echo "<a href='?test={$key}' class='btn {$btnClass}'>{$suite['icon']} {$suite['name']}</a>";
}
echo "</p>
        
        <h3>📁 Individual Test Files</h3>
        <ul>";
foreach ($ultimateTestSuites as $key => $suite) {
    echo "<li><a href='{$suite['path']}' target='_blank'>{$suite['icon']} {$suite['name']}</a> - {$suite['description']}</li>";
}
echo "</ul>
        
        <h3>📈 Test Suite Categories</h3>
        <div class='stats'>
            <div class='stat-card info'>
                <div class='stat-number'>🏗️</div>
                <div class='stat-label'>Core Functionality</div>
                <small>Database, CRUD, File System</small>
            </div>
            <div class='stat-card info'>
                <div class='stat-number'>🔗</div>
                <div class='stat-label'>Integration</div>
                <small>APIs, Data Flow</small>
            </div>
            <div class='stat-card info'>
                <div class='stat-number'>⚡</div>
                <div class='stat-label'>Performance</div>
                <small>Speed, Memory, Load</small>
            </div>
            <div class='stat-card info'>
                <div class='stat-number'>🌐</div>
                <div class='stat-label'>User Experience</div>
                <small>UI, Accessibility</small>
            </div>
            <div class='stat-card info'>
                <div class='stat-number'>🔒</div>
                <div class='stat-label'>Security</div>
                <small>Vulnerability Assessment</small>
            </div>
            <div class='stat-card info'>
                <div class='stat-number'>🗄️</div>
                <div class='stat-label'>Infrastructure</div>
                <small>Database, Connectivity</small>
            </div>
        </div>
      </div>
    </div>
</body>
</html>";
?>
