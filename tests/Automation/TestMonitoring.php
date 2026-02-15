<?php
/**
 * Test Monitoring and Alerting System for APS Dream Home
 * Provides real-time monitoring, alerting, and health checks for the test infrastructure
 */

// Only include dependencies if not already included
if (!class_exists('TestAutomationSuite')) {
    require_once 'TestAutomationSuite.php';
}
if (!class_exists('CronScheduler')) {
    require_once 'CronScheduler.php';
}
if (!class_exists('CIIntegration')) {
    require_once 'CIIntegration.php';
}

class TestMonitoring
{
    private $automationSuite;
    private $cronScheduler;
    private $ciIntegration;
    private $config;
    private $resultsDir;
    private $logFile;
    private $metricsFile;
    
    public function __construct()
    {
        $this->automationSuite = new TestAutomationSuite();
        $this->cronScheduler = new CronScheduler();
        $this->ciIntegration = new CIIntegration();
        
        $this->resultsDir = __DIR__ . '/../../results/monitoring/';
        if (!is_dir($this->resultsDir)) {
            mkdir($this->resultsDir, 0755, true);
        }
        
        $this->logFile = $this->resultsDir . 'monitoring.log';
        $this->metricsFile = $this->resultsDir . 'metrics.json';
        $this->config = $this->loadMonitoringConfig();
    }
    
    private function loadMonitoringConfig()
    {
        return [
            'monitoring' => [
                'enabled' => true,
                'interval' => 300, // 5 minutes
                'health_checks' => [
                    'database' => true,
                    'file_system' => true,
                    'memory_usage' => true,
                    'disk_space' => true,
                    'test_suites' => true,
                    'ci_pipeline' => true
                ],
                'thresholds' => [
                    'memory_usage' => 80, // percentage
                    'disk_usage' => 85, // percentage
                    'test_execution_time' => 300, // seconds
                    'test_pass_rate' => 80, // percentage
                    'ci_execution_time' => 600, // seconds
                    'response_time' => 5000 // milliseconds
                ]
            ],
            'alerting' => [
                'enabled' => true,
                'channels' => [
                    'email' => [
                        'enabled' => false,
                        'recipients' => ['admin@apsdreamhome.com'],
                        'severity_levels' => ['critical', 'warning']
                    ],
                    'slack' => [
                        'enabled' => false,
                        'webhook_url' => '',
                        'channel' => '#alerts',
                        'severity_levels' => ['critical', 'warning']
                    ],
                    'webhook' => [
                        'enabled' => false,
                        'url' => '',
                        'headers' => [],
                        'severity_levels' => ['critical']
                    ]
                ],
                'rules' => [
                    'test_failure_rate' => [
                        'enabled' => true,
                        'threshold' => 20, // percentage
                        'time_window' => 3600, // 1 hour
                        'severity' => 'critical'
                    ],
                    'ci_pipeline_failure' => [
                        'enabled' => true,
                        'threshold' => 1, // count
                        'time_window' => 1800, // 30 minutes
                        'severity' => 'critical'
                    ],
                    'performance_degradation' => [
                        'enabled' => true,
                        'threshold' => 30, // percentage increase
                        'time_window' => 7200, // 2 hours
                        'severity' => 'warning'
                    ],
                    'system_resource_usage' => [
                        'enabled' => true,
                        'threshold' => 90, // percentage
                        'severity' => 'critical'
                    ]
                ]
            ],
            'dashboard' => [
                'enabled' => true,
                'refresh_interval' => 30, // seconds
                'retention_period' => 7, // days
                'charts' => [
                    'test_trends' => true,
                    'performance_metrics' => true,
                    'system_health' => true,
                    'ci_pipeline_status' => true
                ]
            ],
            'reporting' => [
                'enabled' => true,
                'daily_summary' => true,
                'weekly_report' => true,
                'monthly_analysis' => true,
                'export_formats' => ['json', 'csv', 'html']
            ]
        ];
    }
    
    public function runHealthChecks()
    {
        $this->log("Starting comprehensive health checks");
        
        $startTime = microtime(true);
        $healthChecks = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => 'healthy',
            'checks' => [],
            'metrics' => [],
            'alerts' => []
        ];
        
        try {
            // Database health check
            if ($this->config['monitoring']['health_checks']['database']) {
                $healthChecks['checks']['database'] = $this->checkDatabaseHealth();
            }
            
            // File system health check
            if ($this->config['monitoring']['health_checks']['file_system']) {
                $healthChecks['checks']['file_system'] = $this->checkFileSystemHealth();
            }
            
            // Memory usage check
            if ($this->config['monitoring']['health_checks']['memory_usage']) {
                $healthChecks['checks']['memory_usage'] = $this->checkMemoryUsage();
            }
            
            // Disk space check
            if ($this->config['monitoring']['health_checks']['disk_space']) {
                $healthChecks['checks']['disk_space'] = $this->checkDiskSpace();
            }
            
            // Test suites health check
            if ($this->config['monitoring']['health_checks']['test_suites']) {
                $healthChecks['checks']['test_suites'] = $this->checkTestSuitesHealth();
            }
            
            // CI pipeline health check
            if ($this->config['monitoring']['health_checks']['ci_pipeline']) {
                $healthChecks['checks']['ci_pipeline'] = $this->checkCIPipelineHealth();
            }
            
            // Collect system metrics
            $healthChecks['metrics'] = $this->collectSystemMetrics();
            
            // Determine overall status
            $healthChecks['overall_status'] = $this->determineOverallHealth($healthChecks['checks']);
            
            // Check for alerts
            $healthChecks['alerts'] = $this->checkAlertRules($healthChecks);
            
            // Send alerts if needed
            if (!empty($healthChecks['alerts'])) {
                $this->sendAlerts($healthChecks['alerts']);
            }
            
            // Save health check results
            $this->saveHealthCheckResults($healthChecks);
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $this->log("Health checks completed in {$executionTime}s - Status: {$healthChecks['overall_status']}");
            
            return $healthChecks;
            
        } catch (Exception $e) {
            $this->log("Health checks failed: " . $e->getMessage());
            $healthChecks['overall_status'] = 'error';
            $healthChecks['error'] = $e->getMessage();
            
            return $healthChecks;
        }
    }
    
    private function checkDatabaseHealth()
    {
        $startTime = microtime(true);
        
        try {
            // Test database connection
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5
                ]
            );
            
            // Test basic query
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            // Check table existence
            $tables = ['properties', 'projects', 'users', 'inquiries'];
            $existingTables = [];
            
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->rowCount() > 0) {
                    $existingTables[] = $table;
                }
            }
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // milliseconds
            
            $status = [
                'status' => 'healthy',
                'response_time' => $responseTime,
                'connection' => 'success',
                'tables_found' => count($existingTables),
                'tables_expected' => count($tables),
                'missing_tables' => array_diff($tables, $existingTables)
            ];
            
            // Check thresholds
            if ($responseTime > $this->config['monitoring']['thresholds']['response_time']) {
                $status['status'] = 'warning';
                $status['issue'] = 'Slow response time';
            }
            
            if (count($existingTables) < count($tables)) {
                $status['status'] = 'critical';
                $status['issue'] = 'Missing database tables';
            }
            
            return $status;
            
        } catch (PDOException $e) {
            return [
                'status' => 'critical',
                'error' => $e->getMessage(),
                'connection' => 'failed'
            ];
        }
    }
    
    private function checkFileSystemHealth()
    {
        $checks = [];
        
        // Check if required directories exist and are writable
        $directories = [
            'tests' => __DIR__ . '/../',
            'results' => __DIR__ . '/../../results/',
            'automation' => __DIR__ . '/../../results/automation/',
            'monitoring' => $this->resultsDir,
            'logs' => __DIR__ . '/../../results/logs/'
        ];
        
        $allWritable = true;
        $allExist = true;
        
        foreach ($directories as $name => $path) {
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);
            
            $checks[$name] = [
                'path' => $path,
                'exists' => $exists,
                'writable' => $writable
            ];
            
            if (!$exists) {
                $allExist = false;
            }
            if (!$writable) {
                $allWritable = false;
            }
        }
        
        $status = [
            'status' => $allExist && $allWritable ? 'healthy' : 'warning',
            'directories' => $checks,
            'all_exist' => $allExist,
            'all_writable' => $allWritable
        ];
        
        if (!$allExist) {
            $status['status'] = 'critical';
            $status['issue'] = 'Missing directories';
        } elseif (!$allWritable) {
            $status['issue'] = 'Non-writable directories';
        }
        
        return $status;
    }
    
    private function checkMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $memoryUsagePercent = ($memoryUsage / $memoryLimit) * 100;
        
        $status = [
            'status' => 'healthy',
            'current_usage' => $this->formatBytes($memoryUsage),
            'memory_limit' => $this->formatBytes($memoryLimit),
            'usage_percent' => round($memoryUsagePercent, 2),
            'peak_usage' => $this->formatBytes(memory_get_peak_usage(true))
        ];
        
        $threshold = $this->config['monitoring']['thresholds']['memory_usage'];
        
        if ($memoryUsagePercent > $threshold) {
            $status['status'] = 'critical';
            $status['issue'] = 'High memory usage';
        } elseif ($memoryUsagePercent > ($threshold - 10)) {
            $status['status'] = 'warning';
            $status['issue'] = 'Elevated memory usage';
        }
        
        return $status;
    }
    
    private function checkDiskSpace()
    {
        $path = __DIR__ . '/../../';
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = ($usedSpace / $totalSpace) * 100;
        
        $status = [
            'status' => 'healthy',
            'total_space' => $this->formatBytes($totalSpace),
            'used_space' => $this->formatBytes($usedSpace),
            'free_space' => $this->formatBytes($freeSpace),
            'usage_percent' => round($usagePercent, 2)
        ];
        
        $threshold = $this->config['monitoring']['thresholds']['disk_usage'];
        
        if ($usagePercent > $threshold) {
            $status['status'] = 'critical';
            $status['issue'] = 'Low disk space';
        } elseif ($usagePercent > ($threshold - 5)) {
            $status['status'] = 'warning';
            $status['issue'] = 'Elevated disk usage';
        }
        
        return $status;
    }
    
    private function checkTestSuitesHealth()
    {
        $status = [
            'status' => 'healthy',
            'suites' => [],
            'last_run' => null,
            'recent_failures' => 0
        ];
        
        try {
            // Get latest test results
            $latestResults = $this->automationSuite->getLatestResults();
            
            if ($latestResults) {
                $status['last_run'] = $latestResults['end_time'];
                $status['overall_pass_rate'] = $latestResults['summary']['overall_pass_rate'];
                $status['critical_failures'] = $latestResults['summary']['critical_failures'];
                
                // Check individual suites
                foreach ($latestResults['suites'] as $name => $suite) {
                    $suiteStatus = [
                        'name' => $name,
                        'status' => $suite['status'],
                        'pass_rate' => $suite['results']['pass_rate'] ?? 0,
                        'execution_time' => $suite['execution_time']
                    ];
                    
                    if ($suite['status'] === 'failed') {
                        $suiteStatus['status'] = 'critical';
                        $status['recent_failures']++;
                    } elseif ($suiteStatus['pass_rate'] < $this->config['monitoring']['thresholds']['test_pass_rate']) {
                        $suiteStatus['status'] = 'warning';
                    }
                    
                    $status['suites'][] = $suiteStatus;
                }
                
                // Determine overall status
                if ($status['critical_failures'] > 0) {
                    $status['status'] = 'critical';
                    $status['issue'] = 'Critical test failures';
                } elseif ($status['overall_pass_rate'] < $this->config['monitoring']['thresholds']['test_pass_rate']) {
                    $status['status'] = 'warning';
                    $status['issue'] = 'Low pass rate';
                }
            } else {
                $status['status'] = 'warning';
                $status['issue'] = 'No recent test results found';
            }
            
        } catch (Exception $e) {
            $status['status'] = 'critical';
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    private function checkCIPipelineHealth()
    {
        $status = [
            'status' => 'healthy',
            'last_run' => null,
            'recent_runs' => [],
            'failure_rate' => 0
        ];
        
        try {
            // Check for recent CI results
            $ciResultsDir = __DIR__ . '/../../results/ci/';
            $ciFiles = glob($ciResultsDir . 'ci-results-*.json');
            
            if (!empty($ciFiles)) {
                // Get the latest CI results
                $latestFile = max($ciFiles);
                $latestResults = json_decode(file_get_contents($latestFile), true);
                
                if ($latestResults) {
                    $status['last_run'] = $latestResults['end_time'];
                    $status['platform'] = $latestResults['platform'];
                    $status['branch'] = $latestResults['branch'];
                    $status['overall_status'] = $latestResults['status'];
                    $status['pass_rate'] = $latestResults['results']['summary']['overall_pass_rate'];
                    
                    // Analyze recent runs
                    $recentFiles = array_slice($ciFiles, -10); // Last 10 runs
                    $failures = 0;
                    
                    foreach ($recentFiles as $file) {
                        $results = json_decode(file_get_contents($file), true);
                        if ($results && $results['status'] === 'failed') {
                            $failures++;
                        }
                        
                        $status['recent_runs'][] = [
                            'timestamp' => $results['end_time'] ?? 'unknown',
                            'status' => $results['status'] ?? 'unknown',
                            'platform' => $results['platform'] ?? 'unknown'
                        ];
                    }
                    
                    $status['failure_rate'] = round(($failures / count($recentFiles)) * 100, 2);
                    
                    // Determine health status
                    if ($latestResults['status'] === 'failed') {
                        $status['status'] = 'critical';
                        $status['issue'] = 'Latest CI run failed';
                    } elseif ($status['failure_rate'] > 20) {
                        $status['status'] = 'warning';
                        $status['issue'] = 'High CI failure rate';
                    }
                }
            } else {
                $status['status'] = 'warning';
                $status['issue'] = 'No CI results found';
            }
            
        } catch (Exception $e) {
            $status['status'] = 'critical';
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
    
    private function collectSystemMetrics()
    {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'cpu_load' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0,
            'disk_usage' => $this->getDiskUsage(),
            'php_version' => PHP_VERSION,
            'process_id' => getmypid(),
            'uptime' => $this->getSystemUptime()
        ];
    }
    
    private function determineOverallHealth($checks)
    {
        $statuses = [];
        
        foreach ($checks as $name => $check) {
            $statuses[] = $check['status'] ?? 'unknown';
        }
        
        // If any check is critical, overall status is critical
        if (in_array('critical', $statuses)) {
            return 'critical';
        }
        
        // If any check is warning, overall status is warning
        if (in_array('warning', $statuses)) {
            return 'warning';
        }
        
        // If all checks are healthy, overall status is healthy
        if (in_array('healthy', $statuses) && !in_array('unknown', $statuses)) {
            return 'healthy';
        }
        
        return 'unknown';
    }
    
    private function checkAlertRules($healthChecks)
    {
        $alerts = [];
        $rules = $this->config['alerting']['rules'];
        
        foreach ($rules as $ruleName => $rule) {
            if (!$rule['enabled']) {
                continue;
            }
            
            $alert = $this->evaluateAlertRule($ruleName, $rule, $healthChecks);
            
            if ($alert) {
                $alerts[] = $alert;
            }
        }
        
        return $alerts;
    }
    
    private function evaluateAlertRule($ruleName, $rule, $healthChecks)
    {
        switch ($ruleName) {
            case 'test_failure_rate':
                if (isset($healthChecks['checks']['test_suites']['recent_failures'])) {
                    $failures = $healthChecks['checks']['test_suites']['recent_failures'];
                    if ($failures >= $rule['threshold']) {
                        return [
                            'rule' => $ruleName,
                            'severity' => $rule['severity'],
                            'message' => "Test failure rate alert: {$failures} recent failures detected",
                            'threshold' => $rule['threshold'],
                            'actual' => $failures,
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                break;
                
            case 'ci_pipeline_failure':
                if (isset($healthChecks['checks']['ci_pipeline']['overall_status'])) {
                    $status = $healthChecks['checks']['ci_pipeline']['overall_status'];
                    if ($status === 'failed') {
                        return [
                            'rule' => $ruleName,
                            'severity' => $rule['severity'],
                            'message' => "CI pipeline failure detected",
                            'threshold' => $rule['threshold'],
                            'actual' => 1,
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                break;
                
            case 'performance_degradation':
                // This would require historical data comparison
                // For now, we'll skip this implementation
                break;
                
            case 'system_resource_usage':
                if (isset($healthChecks['checks']['memory_usage']['usage_percent'])) {
                    $memoryUsage = $healthChecks['checks']['memory_usage']['usage_percent'];
                    if ($memoryUsage >= $rule['threshold']) {
                        return [
                            'rule' => $ruleName,
                            'severity' => $rule['severity'],
                            'message' => "High memory usage detected: {$memoryUsage}%",
                            'threshold' => $rule['threshold'],
                            'actual' => $memoryUsage,
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                
                if (isset($healthChecks['checks']['disk_space']['usage_percent'])) {
                    $diskUsage = $healthChecks['checks']['disk_space']['usage_percent'];
                    if ($diskUsage >= $rule['threshold']) {
                        return [
                            'rule' => $ruleName,
                            'severity' => $rule['severity'],
                            'message' => "High disk usage detected: {$diskUsage}%",
                            'threshold' => $rule['threshold'],
                            'actual' => $diskUsage,
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                break;
        }
        
        return null;
    }
    
    private function sendAlerts($alerts)
    {
        foreach ($alerts as $alert) {
            $severity = $alert['severity'];
            
            // Send email alerts
            if ($this->config['alerting']['channels']['email']['enabled'] &&
                in_array($severity, $this->config['alerting']['channels']['email']['severity_levels'])) {
                $this->sendEmailAlert($alert);
            }
            
            // Send Slack alerts
            if ($this->config['alerting']['channels']['slack']['enabled'] &&
                in_array($severity, $this->config['alerting']['channels']['slack']['severity_levels'])) {
                $this->sendSlackAlert($alert);
            }
            
            // Send webhook alerts
            if ($this->config['alerting']['channels']['webhook']['enabled'] &&
                in_array($severity, $this->config['alerting']['channels']['webhook']['severity_levels'])) {
                $this->sendWebhookAlert($alert);
            }
        }
    }
    
    private function sendEmailAlert($alert)
    {
        $recipients = $this->config['alerting']['channels']['email']['recipients'];
        $subject = "APS Dream Home Test Alert: {$alert['severity']} - {$alert['rule']}";
        $message = $this->buildEmailAlertMessage($alert);
        
        // Email implementation would go here
        $this->log("EMAIL ALERT: {$subject} - {$alert['message']}");
    }
    
    private function sendSlackAlert($alert)
    {
        $webhookUrl = $this->config['alerting']['channels']['slack']['webhook_url'];
        $channel = $this->config['alerting']['channels']['slack']['channel'];
        
        if (empty($webhookUrl)) {
            $this->log("Slack webhook URL not configured for alerts");
            return;
        }
        
        $message = $this->buildSlackAlertMessage($alert);
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $this->log("Slack alert sent successfully: {$alert['rule']}");
        } else {
            $this->log("Failed to send Slack alert: HTTP {$httpCode}");
        }
    }
    
    private function sendWebhookAlert($alert)
    {
        $url = $this->config['alerting']['channels']['webhook']['url'];
        $headers = $this->config['alerting']['channels']['webhook']['headers'];
        
        if (empty($url)) {
            $this->log("Webhook URL not configured for alerts");
            return;
        }
        
        $payload = [
            'alert' => $alert,
            'timestamp' => date('Y-m-d H:i:s'),
            'source' => 'apsdreamhome-test-monitoring'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Content-Type: application/json'], $headers));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $this->log("Webhook alert sent successfully: {$alert['rule']}");
        } else {
            $this->log("Failed to send webhook alert: HTTP {$httpCode}");
        }
    }
    
    private function buildEmailAlertMessage($alert)
    {
        $severityColors = [
            'critical' => '#dc3545',
            'warning' => '#ffc107',
            'info' => '#17a2b8'
        ];
        
        $color = $severityColors[$alert['severity']] ?? '#6c757d';
        
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .alert { padding: 20px; border-radius: 8px; background-color: {$color}; color: white; }
                .details { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
            </style>
        </head>
        <body>
            <div class='alert'>
                <h2>🚨 Test Alert: {$alert['rule']}</h2>
                <p><strong>Severity:</strong> {$alert['severity']}</p>
                <p><strong>Message:</strong> {$alert['message']}</p>
                <p><strong>Time:</strong> {$alert['timestamp']}</p>
            </div>
            
            <div class='details'>
                <h3>Details</h3>
                <p><strong>Threshold:</strong> {$alert['threshold']}</p>
                <p><strong>Actual:</strong> {$alert['actual']}</p>
            </div>
        </body>
        </html>";
    }
    
    private function buildSlackAlertMessage($alert)
    {
        $colors = [
            'critical' => '#dc3545',
            'warning' => '#ffc107',
            'info' => '#17a2b8'
        ];
        
        $icons = [
            'critical' => ':rotating_light:',
            'warning' => ':warning:',
            'info' => ':information_source:'
        ];
        
        $color = $colors[$alert['severity']] ?? '#6c757d';
        $icon = $icons[$alert['severity']] ?? ':grey_question:';
        
        return [
            'channel' => $this->config['alerting']['channels']['slack']['channel'],
            'username' => 'APS Dream Home Alerts',
            'icon_emoji' => ':robot_face:',
            'attachments' => [
                [
                    'color' => $color,
                    'title' => "{$icon} Test Alert: {$alert['rule']}",
                    'fields' => [
                        [
                            'title' => 'Severity',
                            'value' => ucfirst($alert['severity']),
                            'short' => true
                        ],
                        [
                            'title' => 'Message',
                            'value' => $alert['message'],
                            'short' => false
                        ],
                        [
                            'title' => 'Threshold',
                            'value' => $alert['threshold'],
                            'short' => true
                        ],
                        [
                            'title' => 'Actual',
                            'value' => $alert['actual'],
                            'short' => true
                        ],
                        [
                            'title' => 'Time',
                            'value' => $alert['timestamp'],
                            'short' => true
                        ]
                    ]
                ]
            ]
        ];
    }
    
    private function saveHealthCheckResults($healthChecks)
    {
        $resultsFile = $this->resultsDir . 'health_check_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($resultsFile, json_encode($healthChecks, JSON_PRETTY_PRINT));
        
        // Update latest results
        $latestFile = $this->resultsDir . 'latest_health_check.json';
        file_put_contents($latestFile, json_encode($healthChecks, JSON_PRETTY_PRINT));
        
        // Update metrics
        $this->updateMetrics($healthChecks);
        
        $this->log("Health check results saved: {$resultsFile}");
    }
    
    private function updateMetrics($healthChecks)
    {
        $metrics = [];
        
        if (file_exists($this->metricsFile)) {
            $metrics = json_decode(file_get_contents($this->metricsFile), true) ?: [];
        }
        
        $metrics[] = [
            'timestamp' => $healthChecks['timestamp'],
            'overall_status' => $healthChecks['overall_status'],
            'memory_usage' => $healthChecks['metrics']['memory_usage'] ?? 0,
            'disk_usage' => $healthChecks['metrics']['disk_usage'] ?? 0,
            'cpu_load' => $healthChecks['metrics']['cpu_load'] ?? 0,
            'alerts_count' => count($healthChecks['alerts'])
        ];
        
        // Keep only last 1000 entries
        if (count($metrics) > 1000) {
            $metrics = array_slice($metrics, -1000);
        }
        
        file_put_contents($this->metricsFile, json_encode($metrics, JSON_PRETTY_PRINT));
    }
    
    private function parseMemoryLimit($limit)
    {
        $limit = strtolower($limit);
        $multiplier = 1;
        
        if (strpos($limit, 'g') !== false) {
            $multiplier = 1024 * 1024 * 1024;
        } elseif (strpos($limit, 'm') !== false) {
            $multiplier = 1024 * 1024;
        } elseif (strpos($limit, 'k') !== false) {
            $multiplier = 1024;
        }
        
        return (int)preg_replace('/[^0-9]/', '', $limit) * $multiplier;
    }
    
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function getDiskUsage()
    {
        $path = __DIR__ . '/../../';
        $totalSpace = disk_total_space($path);
        $usedSpace = $totalSpace - disk_free_space($path);
        
        return round(($usedSpace / $totalSpace) * 100, 2);
    }
    
    private function getSystemUptime()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0;
        }
        
        return 0;
    }
    
    public function getMonitoringDashboard()
    {
        $data = [
            'current_status' => null,
            'metrics' => [],
            'alerts' => [],
            'trends' => []
        ];
        
        // Get latest health check
        $latestFile = $this->resultsDir . 'latest_health_check.json';
        if (file_exists($latestFile)) {
            $data['current_status'] = json_decode(file_get_contents($latestFile), true);
        }
        
        // Get metrics
        if (file_exists($this->metricsFile)) {
            $data['metrics'] = json_decode(file_get_contents($this->metricsFile), true) ?: [];
        }
        
        // Get recent alerts
        $alertFiles = glob($this->resultsDir . 'alert_*.json');
        foreach ($alertFiles as $file) {
            $alert = json_decode(file_get_contents($file), true);
            if ($alert) {
                $data['alerts'][] = $alert;
            }
        }
        
        // Sort alerts by timestamp
        usort($data['alerts'], function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return $data;
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] MONITORING: {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        echo $logEntry;
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    // Fix option parsing - include short options for all commands
    $options = getopt('h', ['help', 'health-check', 'dashboard', 'status']);
    
    if (isset($options['h']) || isset($options['help'])) {
        echo "APS Dream Home - Test Monitoring System\n";
        echo "Usage: php TestMonitoring.php [options]\n\n";
        echo "Options:\n";
        echo "  --health-check    Run comprehensive health checks\n";
        echo "  --dashboard       Show monitoring dashboard data\n";
        echo "  --status          Show current system status\n";
        echo "  -h, --help        Show this help message\n\n";
        echo "Examples:\n";
        echo "  php TestMonitoring.php --health-check\n";
        echo "  php TestMonitoring.php --status\n";
        exit(0);
    }
    
    try {
        $monitoring = new TestMonitoring();
        
        if (isset($options['health-check'])) {
            $results = $monitoring->runHealthChecks();
            
            echo "\n=== Health Check Results ===\n";
            echo "Overall Status: {$results['overall_status']}\n";
            echo "Timestamp: {$results['timestamp']}\n";
            
            foreach ($results['checks'] as $name => $check) {
                echo "\n{$name}:\n";
                echo "  Status: {$check['status']}\n";
                if (isset($check['issue'])) {
                    echo "  Issue: {$check['issue']}\n";
                }
            }
            
            if (!empty($results['alerts'])) {
                echo "\nAlerts:\n";
                foreach ($results['alerts'] as $alert) {
                    echo "  [{$alert['severity']}] {$alert['rule']}: {$alert['message']}\n";
                }
            }
            
            if ($results['overall_status'] === 'critical') {
                exit(2);
            } elseif ($results['overall_status'] === 'warning') {
                exit(1);
            }
        }
        
        if (isset($options['status'])) {
            $dashboard = $monitoring->getMonitoringDashboard();
            
            echo "=== System Status ===\n";
            
            if ($dashboard && isset($dashboard['current_status']) && $dashboard['current_status']) {
                $status = $dashboard['current_status'];
                echo "Overall Status: " . (isset($status['overall_status']) ? $status['overall_status'] : 'unknown') . "\n";
                echo "Last Check: " . (isset($status['timestamp']) ? $status['timestamp'] : 'unknown') . "\n";
                
                echo "\nComponent Status:\n";
                if (isset($status['checks']) && is_array($status['checks'])) {
                    foreach ($status['checks'] as $name => $check) {
                        echo "  {$name}: {$check['status']}\n";
                    }
                }
            } else {
                echo "No health check data available\n";
            }
            
            echo "\nRecent Alerts: " . (isset($dashboard['alerts']) && is_array($dashboard['alerts']) ? count($dashboard['alerts']) : 0) . "\n";
        }
        
        if (isset($options['dashboard'])) {
            $dashboard = $monitoring->getMonitoringDashboard();
            echo json_encode($dashboard, JSON_PRETTY_PRINT);
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    // Web interface
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $monitoring = new TestMonitoring();
        
        try {
            switch ($action) {
                case 'health-check':
                    $results = $monitoring->runHealthChecks();
                    echo json_encode(['status' => 'success', 'data' => $results]);
                    break;
                    
                case 'dashboard':
                    $dashboard = $monitoring->getMonitoringDashboard();
                    echo json_encode(['status' => 'success', 'data' => $dashboard]);
                    break;
                    
                default:
                    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
                    break;
            }
            
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        // Show web interface
        echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Test Monitoring</title>
    <script src=\"https://cdn.jsdelivr.net/npm/chart.js\"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .status-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-healthy { border-left: 4px solid #28a745; }
        .status-warning { border-left: 4px solid #ffc107; }
        .status-critical { border-left: 4px solid #dc3545; }
        .metrics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .chart-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .alerts-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .alert { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .alert-critical { background: #f8d7da; border-left: 4px solid #dc3545; }
        .alert-warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .button:hover { background: #0056b3; }
        .loading { display: none; text-align: center; margin: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔍 APS Dream Home Test Monitoring</h1>
            <p>Real-time monitoring and alerting for test infrastructure</p>
            <button class='button' onclick='runHealthCheck()'>🔄 Run Health Check</button>
            <button class='button' onclick='refreshDashboard()'>📊 Refresh Dashboard</button>
        </div>
        
        <div class='loading' id='loading'>
            <h3>🔄 Running Health Checks...</h3>
            <p>Please wait while the system health is being checked...</p>
        </div>
        
        <div id='status-grid' class='status-grid'></div>
        
        <div class='metrics-grid'>
            <div class='chart-container'>
                <h3>📈 System Metrics</h3>
                <canvas id='metricsChart'></canvas>
            </div>
            <div class='chart-container'>
                <h3>📊 Health Trends</h3>
                <canvas id='trendsChart'></canvas>
            </div>
        </div>
        
        <div class='alerts-section'>
            <h3>🚨 Recent Alerts</h3>
            <div id='alerts-container'></div>
        </div>
    </div>
    
    <script>
        let metricsChart = null;
        let trendsChart = null;
        
        function runHealthCheck() {
            document.getElementById('loading').style.display = 'block';
            
            fetch('TestMonitoring.php?action=health-check')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    if (data.status === 'success') {
                        displayHealthCheckResults(data.data);
                        refreshDashboard();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    alert('Error: ' + error.message);
                });
        }
        
        function refreshDashboard() {
            fetch('TestMonitoring.php?action=dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayDashboard(data.data);
                    }
                });
        }
        
        function displayHealthCheckResults(results) {
            displayStatusCards(results.checks);
            displayAlerts(results.alerts);
        }
        
        function displayDashboard(data) {
            if (data.current_status) {
                displayStatusCards(data.current_status.checks);
                displayAlerts(data.current_status.alerts);
            }
            
            displayMetrics(data.metrics);
            displayTrends(data.metrics);
        }
        
        function displayStatusCards(checks) {
            const container = document.getElementById('status-grid');
            let html = '';
            
            for (const [name, check] of Object.entries(checks)) {
                const statusClass = 'status-' + check.status;
                const icon = check.status === 'healthy' ? '✅' : 
                             check.status === 'warning' ? '⚠️' : '❌';
                
                html += \`
                    <div class='status-card \${statusClass}'>
                        <h3>\${icon} \${name.replace(/_/g, ' ').toUpperCase()}</h3>
                        <p><strong>Status:</strong> \${check.status}</p>
                \`;
                
                if (check.issue) {
                    html += \`<p><strong>Issue:</strong> \${check.issue}</p>\`;
                }
                
                if (check.usage_percent) {
                    html += \`<p><strong>Usage:</strong> \${check.usage_percent}%</p>\`;
                }
                
                if (check.response_time) {
                    html += \`<p><strong>Response Time:</strong> \${check.response_time}ms</p>\`;
                }
                
                html += \`</div>\`;
            }
            
            container.innerHTML = html;
        }
        
        function displayAlerts(alerts) {
            const container = document.getElementById('alerts-container');
            
            if (alerts.length === 0) {
                container.innerHTML = '<p>No recent alerts</p>';
                return;
            }
            
            let html = '';
            alerts.slice(0, 10).forEach(alert => {
                const alertClass = 'alert-' + alert.severity;
                html += \`
                    <div class='alert \${alertClass}'>
                        <h4>\${alert.rule}</h4>
                        <p>\${alert.message}</p>
                        <small>\${alert.timestamp}</small>
                    </div>
                \`;
            });
            
            container.innerHTML = html;
        }
        
        function displayMetrics(metrics) {
            const ctx = document.getElementById('metricsChart').getContext('2d');
            
            if (metricsChart) {
                metricsChart.destroy();
            }
            
            const recentMetrics = metrics.slice(-20);
            
            metricsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: recentMetrics.map(m => new Date(m.timestamp).toLocaleTimeString()),
                    datasets: [{
                        label: 'Memory Usage (MB)',
                        data: recentMetrics.map(m => (m.memory_usage / 1024 / 1024).toFixed(2)),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Disk Usage (%)',
                        data: recentMetrics.map(m => m.disk_usage),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'CPU Load',
                        data: recentMetrics.map(m => m.cpu_load),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        function displayTrends(metrics) {
            const ctx = document.getElementById('trendsChart').getContext('2d');
            
            if (trendsChart) {
                trendsChart.destroy();
            }
            
            const recentMetrics = metrics.slice(-20);
            
            trendsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: recentMetrics.map(m => new Date(m.timestamp).toLocaleTimeString()),
                    datasets: [{
                        label: 'Alerts Count',
                        data: recentMetrics.map(m => m.alerts_count),
                        backgroundColor: '#dc3545'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        
        // Auto-refresh every 30 seconds
        setInterval(refreshDashboard, 30000);
        
        // Load initial data
        window.onload = function() {
            refreshDashboard();
        };
    </script>
</body>
</html>";
    }
}
?>
