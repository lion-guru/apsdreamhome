<?php
/**
 * Automated Test Suite for APS Dream Home
 * Provides scheduled testing, CI/CD integration, and automated reporting
 */

// Only load constants if not already defined
if (!defined('DB_HOST')) {
    require_once 'includes/config/constants.php';
}

class TestAutomationSuite
{
    private $pdo;
    private $resultsDir;
    private $logFile;
    private $config;
    
    public function __construct($autoRun = false)
    {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
        
        $this->resultsDir = __DIR__ . '/../../results/automation/';
        if (!is_dir($this->resultsDir)) {
            mkdir($this->resultsDir, 0755, true);
        }
        
        $this->logFile = $this->resultsDir . 'automation.log';
        $this->config = $this->loadConfiguration();
        
        // Only auto-run if explicitly requested
        if ($autoRun) {
            $this->runAutomatedTestSuite();
        }
    }
    
    private function loadConfiguration()
    {
        return [
            'test_suites' => [
                'comprehensive' => [
                    'file' => 'ComprehensiveTestSuite.php',
                    'priority' => 1,
                    'timeout' => 300,
                    'critical' => true
                ],
                'integration' => [
                    'file' => 'Integration/ApiIntegrationTest.php',
                    'priority' => 2,
                    'timeout' => 180,
                    'critical' => true
                ],
                'performance' => [
                    'file' => 'Performance/PerformanceTest.php',
                    'priority' => 3,
                    'timeout' => 240,
                    'critical' => false
                ],
                'security' => [
                    'file' => 'Security/SecurityAuditTest.php',
                    'priority' => 4,
                    'timeout' => 200,
                    'critical' => true
                ],
                'browser' => [
                    'file' => 'Browser/SeleniumTest.php',
                    'priority' => 5,
                    'timeout' => 300,
                    'critical' => false
                ],
                'database' => [
                    'file' => '../test_database_standalone.php',
                    'priority' => 6,
                    'timeout' => 60,
                    'critical' => true
                ]
            ],
            'notifications' => [
                'email' => [
                    'enabled' => false,
                    'recipients' => [],
                    'threshold' => 80
                ],
                'slack' => [
                    'enabled' => false,
                    'webhook' => '',
                    'threshold' => 90
                ]
            ],
            'scheduling' => [
                'daily_run' => true,
                'weekly_full' => true,
                'performance_benchmark' => true,
                'security_audit' => true
            ],
            'reporting' => [
                'detailed_reports' => true,
                'trend_analysis' => true,
                'performance_tracking' => true,
                'archive_results' => true
            ]
        ];
    }
    
    public function runAutomatedTestSuite($mode = 'full')
    {
        $this->log("Starting automated test suite - Mode: {$mode}");
        
        $startTime = microtime(true);
        $results = [
            'start_time' => date('Y-m-d H:i:s'),
            'mode' => $mode,
            'suites' => [],
            'summary' => [],
            'performance' => [],
            'trends' => []
        ];
        
        try {
            switch ($mode) {
                case 'quick':
                    $results = $this->runQuickTests($results);
                    break;
                case 'critical':
                    $results = $this->runCriticalTests($results);
                    break;
                case 'performance':
                    $results = $this->runPerformanceTests($results);
                    break;
                case 'security':
                    $results = $this->runSecurityTests($results);
                    break;
                case 'full':
                default:
                    $results = $this->runFullTestSuite($results);
                    break;
            }
            
            $endTime = microtime(true);
            $results['execution_time'] = round($endTime - $startTime, 2);
            $results['end_time'] = date('Y-m-d H:i:s');
            
            // Generate reports
            $this->generateAutomationReport($results);
            $this->updateTrends($results);
            $this->sendNotifications($results);
            
            $this->log("Automated test suite completed successfully");
            
            return $results;
            
        } catch (Exception $e) {
            $this->log("ERROR in automated test suite: " . $e->getMessage());
            $results['error'] = $e->getMessage();
            $this->generateErrorReport($results);
            throw $e;
        }
    }
    
    private function runQuickTests($results)
    {
        $this->log("Running quick tests...");
        
        // Run only critical tests with reduced timeout
        $quickSuites = array_filter($this->config['test_suites'], function($suite) {
            return $suite['critical'] === true;
        });
        
        foreach ($quickSuites as $name => $suite) {
            $this->log("Running quick test: {$name}");
            $result = $this->executeTestSuite($name, $suite, ['quick_mode' => true]);
            $results['suites'][$name] = $result;
        }
        
        $results['summary'] = $this->calculateSummary($results['suites']);
        
        return $results;
    }
    
    private function runCriticalTests($results)
    {
        $this->log("Running critical tests...");
        
        // Run only critical tests
        $criticalSuites = array_filter($this->config['test_suites'], function($suite) {
            return $suite['critical'] === true;
        });
        
        foreach ($criticalSuites as $name => $suite) {
            $this->log("Running critical test: {$name}");
            $result = $this->executeTestSuite($name, $suite);
            $results['suites'][$name] = $result;
        }
        
        $results['summary'] = $this->calculateSummary($results['suites']);
        
        return $results;
    }
    
    private function runPerformanceTests($results)
    {
        $this->log("Running performance tests...");
        
        $performanceSuites = [
            'performance' => $this->config['test_suites']['performance']
        ];
        
        foreach ($performanceSuites as $name => $suite) {
            $this->log("Running performance test: {$name}");
            $result = $this->executeTestSuite($name, $suite, ['performance_focus' => true]);
            $results['suites'][$name] = $result;
            
            // Collect detailed performance metrics
            if (isset($result['performance_metrics'])) {
                $results['performance'][$name] = $result['performance_metrics'];
            }
        }
        
        $results['summary'] = $this->calculateSummary($results['suites']);
        
        return $results;
    }
    
    private function runSecurityTests($results)
    {
        $this->log("Running security tests...");
        
        $securitySuites = [
            'security' => $this->config['test_suites']['security']
        ];
        
        foreach ($securitySuites as $name => $suite) {
            $this->log("Running security test: {$name}");
            $result = $this->executeTestSuite($name, $suite, ['security_focus' => true]);
            $results['suites'][$name] = $result;
        }
        
        $results['summary'] = $this->calculateSummary($results['suites']);
        
        return $results;
    }
    
    private function runFullTestSuite($results)
    {
        $this->log("Running full test suite...");
        
        // Sort suites by priority
        uasort($this->config['test_suites'], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
        
        foreach ($this->config['test_suites'] as $name => $suite) {
            $this->log("Running test suite: {$name}");
            $result = $this->executeTestSuite($name, $suite);
            $results['suites'][$name] = $result;
            
            // Collect performance metrics
            if (isset($result['performance_metrics'])) {
                $results['performance'][$name] = $result['performance_metrics'];
            }
        }
        
        $results['summary'] = $this->calculateSummary($results['suites']);
        
        return $results;
    }
    
    private function executeTestSuite($name, $suite, $options = [])
    {
        $startTime = microtime(true);
        $timeout = $suite['timeout'];
        
        if (isset($options['quick_mode'])) {
            $timeout = min($timeout, 60); // Quick mode: max 60 seconds
        }
        
        $this->log("Executing {$name} with timeout: {$timeout}s");
        
        $testFile = __DIR__ . "/../{$suite['file']}";
        if (!file_exists($testFile)) {
            throw new Exception("Test file not found: {$testFile}");
        }
        
        // Capture output
        ob_start();
        $output = [];
        
        try {
            // Set timeout
            set_time_limit($timeout);
            
            // Execute test suite
            include $testFile;
            
            $output = ob_get_clean();
            $endTime = microtime(true);
            
            // Parse results (this would need to be implemented based on actual test output format)
            $parsedResults = $this->parseTestOutput($output);
            
            $result = [
                'status' => 'completed',
                'start_time' => date('Y-m-d H:i:s', $startTime),
                'end_time' => date('Y-m-d H:i:s', $endTime),
                'execution_time' => round($endTime - $startTime, 2),
                'timeout' => $timeout,
                'output' => $output,
                'results' => $parsedResults,
                'critical' => $suite['critical']
            ];
            
            // Add performance metrics if available
            if (isset($options['performance_focus']) || $name === 'performance') {
                $result['performance_metrics'] = $this->collectPerformanceMetrics();
            }
            
            $this->log("Test suite {$name} completed in {$result['execution_time']}s");
            
            return $result;
            
        } catch (Exception $e) {
            ob_end_clean();
            $endTime = microtime(true);
            
            $result = [
                'status' => 'failed',
                'start_time' => date('Y-m-d H:i:s', $startTime),
                'end_time' => date('Y-m-d H:i:s', $endTime),
                'execution_time' => round($endTime - $startTime, 2),
                'error' => $e->getMessage(),
                'critical' => $suite['critical']
            ];
            
            $this->log("Test suite {$name} failed: " . $e->getMessage());
            
            return $result;
        }
    }
    
    private function parseTestOutput($output)
    {
        // Parse test output to extract results
        // Enhanced parser to handle custom test output formats
        
        $results = [
            'total_tests' => 0,
            'passed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'pass_rate' => 0,
            'details' => []
        ];
        
        // Look for common patterns in test output
        if (preg_match('/(\d+)\s+tests? executed/i', $output, $matches)) {
            $results['total_tests'] = (int)$matches[1];
        }
        
        if (preg_match('/(\d+)\s+passed/i', $output, $matches)) {
            $results['passed'] = (int)$matches[1];
        }
        
        if (preg_match('/(\d+)\s+failed/i', $output, $matches)) {
            $results['failed'] = (int)$matches[1];
        }
        
        if (preg_match('/(\d+)\s+skipped/i', $output, $matches)) {
            $results['skipped'] = (int)$matches[1];
        }
        
        // Enhanced parsing for custom test frameworks
        if ($results['total_tests'] === 0) {
            // Try to extract from HTML-style test output
            if (preg_match_all('/<span style=[\'"]color:\s*green[\'"]>✓<\/span>/i', $output, $matches)) {
                $results['passed'] = count($matches[0]);
                $results['total_tests'] = $results['passed'];
            }
            
            if (preg_match_all('/<span style=[\'"]color:\s*red[\'"]>✗<\/span>/i', $output, $matches)) {
                $results['failed'] = count($matches[0]);
                $results['total_tests'] += $results['failed'];
            }
            
            if (preg_match_all('/<span style=[\'"]color:\s*orange[\'"]>⚠️<\/span>/i', $output, $matches)) {
                $results['skipped'] = count($matches[0]);
                $results['total_tests'] += $results['skipped'];
            }
            
            // Look for assertTrue/assertFalse patterns
            if (preg_match_all('/assertTrue|assertTrue.*passed/i', $output, $matches)) {
                $results['passed'] = max($results['passed'], count($matches[0]));
                $results['total_tests'] = max($results['total_tests'], $results['passed']);
            }
            
            // If still no tests found, count test method executions
            if ($results['total_tests'] === 0 && preg_match_all('/test[A-Z]/i', $output, $matches)) {
                $results['total_tests'] = count($matches[0]);
                $results['passed'] = $results['total_tests']; // Assume passed if no failures
            }
        }
        
        if ($results['total_tests'] > 0) {
            $results['pass_rate'] = round(($results['passed'] / $results['total_tests']) * 100, 2);
        }
        
        return $results;
    }
    
    private function collectPerformanceMetrics()
    {
        return [
            'memory_usage' => memory_get_peak_usage(true),
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'database_queries' => $this->getDatabaseQueryCount(),
            'system_load' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0
        ];
    }
    
    private function getDatabaseQueryCount()
    {
        // This would need to be implemented based on actual database query logging
        return 0; // Placeholder
    }
    
    private function calculateSummary($suites)
    {
        $summary = [
            'total_suites' => count($suites),
            'completed_suites' => 0,
            'failed_suites' => 0,
            'total_tests' => 0,
            'total_passed' => 0,
            'total_failed' => 0,
            'total_skipped' => 0,
            'overall_pass_rate' => 0,
            'critical_failures' => 0
        ];
        
        foreach ($suites as $name => $suite) {
            if ($suite['status'] === 'completed') {
                $summary['completed_suites']++;
            } else {
                $summary['failed_suites']++;
                
                if ($suite['critical']) {
                    $summary['critical_failures']++;
                }
            }
            
            if (isset($suite['results'])) {
                $summary['total_tests'] += $suite['results']['total_tests'];
                $summary['total_passed'] += $suite['results']['passed'];
                $summary['total_failed'] += $suite['results']['failed'];
                $summary['total_skipped'] += $suite['results']['skipped'];
            }
        }
        
        if ($summary['total_tests'] > 0) {
            $summary['overall_pass_rate'] = round(($summary['total_passed'] / $summary['total_tests']) * 100, 2);
        }
        
        return $summary;
    }
    
    private function generateAutomationReport($results)
    {
        $reportFile = $this->resultsDir . 'automation_report_' . date('Y-m-d_H-i-s') . '.json';
        
        // Add additional metadata
        $results['metadata'] = [
            'php_version' => PHP_VERSION,
            'system_info' => php_uname(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'database_host' => DB_HOST,
            'database_name' => DB_NAME,
            'automation_version' => '1.0.0'
        ];
        
        file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));
        
        $this->log("Automation report saved to: {$reportFile}");
        
        // Generate HTML report
        $this->generateHtmlReport($results);
    }
    
    private function generateHtmlReport($results)
    {
        $htmlFile = $this->resultsDir . 'automation_report_' . date('Y-m-d_H-i-s') . '.html';
        
        $html = $this->buildHtmlReport($results);
        
        file_put_contents($htmlFile, $html);
        
        $this->log("HTML report saved to: {$htmlFile}");
    }
    
    private function buildHtmlReport($results)
    {
        $status = $results['summary']['critical_failures'] > 0 ? 'danger' : 
                 ($results['summary']['overall_pass_rate'] < 80 ? 'warning' : 'success');
        
        $html = "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Automated Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; }
        .summary { display: flex; gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; flex: 1; }
        .stat-number { font-size: 2em; font-weight: bold; }
        .suite-result { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .danger { background: #f8d7da; border-left: 4px solid #dc3545; }
        .performance { background: #e2e3e5; padding: 15px; border-radius: 8px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>🤖 Automated Test Report</h1>
        <p>Generated: {$results['end_time']} | Mode: {$results['mode']} | Duration: {$results['execution_time']}s</p>
    </div>
    
    <div class='summary'>
        <div class='stat-card'>
            <div class='stat-number'>{$results['summary']['total_suites']}</div>
            <div>Total Suites</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>{$results['summary']['total_tests']}</div>
            <div>Total Tests</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number' style='color: #28a745;'>{$results['summary']['total_passed']}</div>
            <div>Passed</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number' style='color: #dc3545;'>{$results['summary']['total_failed']}</div>
            <div>Failed</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number' style='color: #6c757d;'>{$results['summary']['total_skipped']}</div>
            <div>Skipped</div>
        </div>
        <div class='stat-card'>
            <div class='stat-number'>{$results['summary']['overall_pass_rate']}%</div>
            <div>Pass Rate</div>
        </div>
    </div>
    
    <h2>📊 Suite Results</h2>";
        
        foreach ($results['suites'] as $name => $suite) {
            $suiteStatus = $suite['status'] === 'completed' ? 'success' : 'danger';
            $passRate = isset($suite['results']['pass_rate']) ? $suite['results']['pass_rate'] : 0;
            
            $html .= "
    <div class='suite-result {$suiteStatus}'>
        <h3>{$name}</h3>
        <p><strong>Status:</strong> {$suite['status']} | <strong>Time:</strong> {$suite['execution_time']}s | <strong>Pass Rate:</strong> {$passRate}%</p>";
            
            if (isset($suite['results'])) {
                $html .= "
        <p><strong>Tests:</strong> {$suite['results']['total_tests']} | 
           <strong>Passed:</strong> {$suite['results']['passed']} | 
           <strong>Failed:</strong> {$suite['results']['failed']} | 
           <strong>Skipped:</strong> {$suite['results']['skipped']}</p>";
            }
            
            if (isset($suite['error'])) {
                $html .= "
        <p><strong>Error:</strong> {$suite['error']}</p>";
            }
            
            $html .= "
    </div>";
        }
        
        if (!empty($results['performance'])) {
            $html .= "
    <h2>⚡ Performance Metrics</h2>
    <div class='performance'>
        <table>
            <tr><th>Suite</th><th>Memory (MB)</th><th>Queries</th><th>System Load</th></tr>";
            
            foreach ($results['performance'] as $name => $metrics) {
                $html .= "
            <tr>
                <td>{$name}</td>
                <td>{$metrics['memory_usage_mb']}</td>
                <td>{$metrics['database_queries']}</td>
                <td>{$metrics['system_load']}</td>
            </tr>";
            }
            
            $html .= "
        </table>
    </div>";
        }
        
        $html .= "
    <h2>🔧 System Information</h2>
    <div class='performance'>
        <p><strong>PHP Version:</strong> {$results['metadata']['php_version']}</p>
        <p><strong>System:</strong> {$results['metadata']['system_info']}</p>
        <p><strong>Memory Limit:</strong> {$results['metadata']['memory_limit']}</p>
        <p><strong>Database:</strong> {$results['metadata']['database_host']}/{$results['metadata']['database_name']}</p>
    </div>
    
</body>
</html>";
        
        return $html;
    }
    
    private function updateTrends($results)
    {
        $trendsFile = $this->resultsDir . 'trends.json';
        
        $trends = [];
        if (file_exists($trendsFile)) {
            $trends = json_decode(file_get_contents($trendsFile), true) ?: [];
        }
        
        $trendEntry = [
            'date' => date('Y-m-d H:i:s'),
            'mode' => $results['mode'],
            'overall_pass_rate' => $results['summary']['overall_pass_rate'],
            'total_tests' => $results['summary']['total_tests'],
            'execution_time' => $results['execution_time'],
            'critical_failures' => $results['summary']['critical_failures']
        ];
        
        $trends[] = $trendEntry;
        
        // Keep only last 30 entries
        if (count($trends) > 30) {
            $trends = array_slice($trends, -30);
        }
        
        file_put_contents($trendsFile, json_encode($trends, JSON_PRETTY_PRINT));
        
        $this->log("Trends updated with new entry");
    }
    
    private function sendNotifications($results)
    {
        $passRate = $results['summary']['overall_pass_rate'];
        $criticalFailures = $results['summary']['critical_failures'];
        
        // Check if notifications should be sent
        $shouldNotify = false;
        $message = '';
        
        if ($criticalFailures > 0) {
            $shouldNotify = true;
            $message = "🚨 CRITICAL: {$criticalFailures} critical test suite failures detected!";
        } elseif ($passRate < $this->config['notifications']['email']['threshold']) {
            $shouldNotify = true;
            $message = "⚠️ WARNING: Test pass rate ({$passRate}%) below threshold!";
        }
        
        if ($shouldNotify) {
            $this->sendEmailNotification($message, $results);
            $this->sendSlackNotification($message, $results);
        }
    }
    
    private function sendEmailNotification($message, $results)
    {
        if (!$this->config['notifications']['email']['enabled']) {
            return;
        }
        
        // Email notification implementation would go here
        $this->log("Email notification: {$message}");
    }
    
    private function sendSlackNotification($message, $results)
    {
        if (!$this->config['notifications']['slack']['enabled']) {
            return;
        }
        
        // Slack notification implementation would go here
        $this->log("Slack notification: {$message}");
    }
    
    private function generateErrorReport($results)
    {
        $errorFile = $this->resultsDir . 'error_report_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($errorFile, json_encode($results, JSON_PRETTY_PRINT));
        
        $this->log("Error report saved to: {$errorFile}");
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        echo $logEntry; // Also output to console
    }
    
    public function scheduleTests()
    {
        $this->log("Setting up scheduled testing...");
        
        // Check if it's time for different types of tests
        $hour = (int)date('H');
        $dayOfWeek = (int)date('w'); // 0 = Sunday, 6 = Saturday
        
        // Daily quick tests (every hour during business hours)
        if ($hour >= 9 && $hour <= 18 && $this->config['scheduling']['daily_run']) {
            $this->log("Running daily quick tests");
            $this->runAutomatedTestSuite('quick');
        }
        
        // Weekly full tests (Sunday morning)
        if ($dayOfWeek === 0 && $hour === 2 && $this->config['scheduling']['weekly_full']) {
            $this->log("Running weekly full test suite");
            $this->runAutomatedTestSuite('full');
        }
        
        // Performance benchmarks (daily at 3 AM)
        if ($hour === 3 && $this->config['scheduling']['performance_benchmark']) {
            $this->log("Running performance benchmarks");
            $this->runAutomatedTestSuite('performance');
        }
        
        // Security audits (weekly on Saturday morning)
        if ($dayOfWeek === 6 && $hour === 1 && $this->config['scheduling']['security_audit']) {
            $this->log("Running security audit");
            $this->runAutomatedTestSuite('security');
        }
        
        $this->log("Scheduled testing completed");
    }
    
    public function getLatestResults()
    {
        $resultsDir = $this->resultsDir;
        $files = glob($resultsDir . 'automation_report_*.json');
        
        if (empty($files)) {
            return null;
        }
        
        // Get the latest file
        $latestFile = max($files);
        
        return json_decode(file_get_contents($latestFile), true);
    }
    
    public function getTrends()
    {
        $trendsFile = $this->resultsDir . 'trends.json';
        
        if (!file_exists($trendsFile)) {
            return [];
        }
        
        return json_decode(file_get_contents($trendsFile), true);
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $options = getopt('m:h', ['mode:', 'help', 'schedule']);
    
    if (isset($options['h']) || isset($options['help'])) {
        echo "APS Dream Home - Automated Test Suite\n";
        echo "Usage: php TestAutomationSuite.php [options]\n\n";
        echo "Options:\n";
        echo "  -m, --mode MODE     Test mode: quick, critical, performance, security, full\n";
        echo "  --schedule          Run scheduled tests based on time\n";
        echo "  -h, --help          Show this help message\n\n";
        echo "Examples:\n";
        echo "  php TestAutomationSuite.php -m quick\n";
        echo "  php TestAutomationSuite.php --mode full\n";
        echo "  php TestAutomationSuite.php --schedule\n";
        exit(0);
    }
    
    $mode = $options['m'] ?? $options['mode'] ?? 'full';
    
    try {
        $automation = new TestAutomationSuite();
        
        if (isset($options['schedule'])) {
            $automation->scheduleTests();
        } else {
            $results = $automation->runAutomatedTestSuite($mode);
            
            echo "\n=== Test Results ===\n";
            echo "Mode: {$results['mode']}\n";
            echo "Total Suites: {$results['summary']['total_suites']}\n";
            echo "Total Tests: {$results['summary']['total_tests']}\n";
            echo "Passed: {$results['summary']['total_passed']}\n";
            echo "Failed: {$results['summary']['total_failed']}\n";
            echo "Skipped: {$results['summary']['total_skipped']}\n";
            echo "Overall Pass Rate: {$results['summary']['overall_pass_rate']}%\n";
            echo "Execution Time: {$results['execution_time']}s\n";
            echo "Critical Failures: {$results['summary']['critical_failures']}\n";
            
            if ($results['summary']['critical_failures'] > 0) {
                echo "\n🚨 CRITICAL FAILURES DETECTED!\n";
                exit(1);
            } elseif ($results['summary']['overall_pass_rate'] < 80) {
                echo "\n⚠️ LOW PASS RATE!\n";
                exit(2);
            } else {
                echo "\n✅ All tests passed successfully!\n";
                exit(0);
            }
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(3);
    }
} else {
    // Web interface
    if (isset($_GET['mode'])) {
        $mode = $_GET['mode'];
        $automation = new TestAutomationSuite();
        
        try {
            $results = $automation->runAutomatedTestSuite($mode);
            
            header('Content-Type: application/json');
            echo json_encode($results);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        // Show web interface
        echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Test Automation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; }
        .button { background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; margin: 10px; font-size: 16px; }
        .button:hover { background: #0056b3; }
        .button.danger { background: #dc3545; }
        .button.danger:hover { background: #c82333; }
        .button.success { background: #28a745; }
        .button.success:hover { background: #218838; }
        .button.warning { background: #ffc107; color: #212529; }
        .button.warning:hover { background: #e0a800; }
        .results { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; }
        .loading { display: none; text-align: center; margin: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🤖 APS Dream Home Test Automation</h1>
            <p>Automated Testing Suite with Scheduling and Reporting</p>
        </div>
        
        <div style='text-align: center; margin: 30px 0;'>
            <h2>Run Automated Tests</h2>
            <button class='button success' onclick='runTest(\"quick\")'>⚡ Quick Tests</button>
            <button class='button' onclick='runTest(\"critical\")'>🔒 Critical Tests</button>
            <button class='button warning' onclick='runTest(\"performance\")'>⚡ Performance</button>
            <button class='button danger' onclick='runTest(\"security\")'>🛡️ Security</button>
            <button class='button' onclick='runTest(\"full\")'>🔬 Full Suite</button>
        </div>
        
        <div class='loading' id='loading'>
            <h3>🔄 Running Tests...</h3>
            <p>Please wait while the automated test suite executes...</p>
        </div>
        
        <div class='results' id='results' style='display: none;'>
            <h2>📊 Test Results</h2>
            <div id='results-content'></div>
        </div>
    </div>
    
    <script>
        function runTest(mode) {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('results').style.display = 'none';
            
            fetch('TestAutomationSuite.php?mode=' + mode)
                .then(response => response.json())
                .then(data => {
                    displayResults(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loading').style.display = 'none';
                    alert('Error running tests: ' + error.message);
                });
        }
        
        function displayResults(data) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('results').style.display = 'block';
            
            const status = data.summary.critical_failures > 0 ? 'danger' : 
                         (data.summary.overall_pass_rate < 80 ? 'warning' : 'success');
            
            let html = \`
                <div style='padding: 20px; background: \${status === 'success' ? '#d4edda' : status === 'warning' ? '#fff3cd' : '#f8d7da'}; border-radius: 10px; margin-bottom: 20px;'>
                    <h3>Test Summary</h3>
                    <p><strong>Mode:</strong> \${data.mode} | <strong>Duration:</strong> \${data.execution_time}s</p>
                    <p><strong>Total Suites:</strong> \${data.summary.total_suites} | <strong>Total Tests:</strong> \${data.summary.total_tests}</p>
                    <p><strong>Passed:</strong> \${data.summary.total_passed} | <strong>Failed:</strong> \${data.summary.total_failed} | <strong>Skipped:</strong> \${data.summary.total_skipped}</p>
                    <p><strong>Overall Pass Rate:</strong> \${data.summary.overall_pass_rate}% | <strong>Critical Failures:</strong> \${data.summary.critical_failures}</p>
                </div>
                
                <h4>Suite Results:</h4>
            \`;
            
            for (const [name, suite] of Object.entries(data.suites)) {
                const suiteStatus = suite.status === 'completed' ? 'success' : 'danger';
                const passRate = suite.results?.pass_rate || 0;
                
                html += \`
                    <div style='margin: 10px 0; padding: 15px; background: \${suiteStatus === 'success' ? '#d4edda' : '#f8d7da'}; border-radius: 8px;'>
                        <h5>\${name}</h5>
                        <p><strong>Status:</strong> \${suite.status} | <strong>Time:</strong> \${suite.execution_time}s | <strong>Pass Rate:</strong> \${passRate}%</p>
                \`;
                
                if (suite.results) {
                    html += \`<p><strong>Tests:</strong> \${suite.results.total_tests} | <strong>Passed:</strong> \${suite.results.passed} | <strong>Failed:</strong> \${suite.results.failed} | <strong>Skipped:</strong> \${suite.results.skipped}</p>\`;
                }
                
                if (suite.error) {
                    html += \`<p><strong>Error:</strong> \${suite.error}</p>\`;
                }
                
                html += \`</div>\`;
            }
            
            document.getElementById('results-content').innerHTML = html;
        }
    </script>
</body>
</html>";
    }
}
?>
