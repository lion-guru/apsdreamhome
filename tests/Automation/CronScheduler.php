<?php
/**
 * Cron Scheduler for APS Dream Home Test Automation
 * Provides automated scheduling and execution of test suites
 */

require_once 'TestAutomationSuite.php';

class CronScheduler
{
    private $automationSuite;
    private $scheduleFile;
    private $logFile;
    private $config;
    
    public function __construct()
    {
        $this->automationSuite = new TestAutomationSuite();
        $this->scheduleFile = __DIR__ . '/../../results/automation/schedule.json';
        $this->logFile = __DIR__ . '/../../results/automation/cron.log';
        $this->config = $this->loadScheduleConfig();
        
        // Ensure directories exist
        $resultsDir = __DIR__ . '/../../results/automation/';
        if (!is_dir($resultsDir)) {
            mkdir($resultsDir, 0755, true);
        }
    }
    
    private function loadScheduleConfig()
    {
        return [
            'schedules' => [
                'quick_health_check' => [
                    'enabled' => true,
                    'frequency' => 'hourly',
                    'time' => '*/30 * * * *', // Every 30 minutes
                    'mode' => 'quick',
                    'description' => 'Quick health check of critical systems'
                ],
                'daily_critical' => [
                    'enabled' => true,
                    'frequency' => 'daily',
                    'time' => '0 8 * * *', // 8:00 AM daily
                    'mode' => 'critical',
                    'description' => 'Daily critical test suite'
                ],
                'performance_benchmark' => [
                    'enabled' => true,
                    'frequency' => 'daily',
                    'time' => '0 3 * * *', // 3:00 AM daily
                    'mode' => 'performance',
                    'description' => 'Daily performance benchmarking'
                ],
                'security_audit' => [
                    'enabled' => true,
                    'frequency' => 'weekly',
                    'time' => '0 2 * * 6', // 2:00 AM Saturday
                    'mode' => 'security',
                    'description' => 'Weekly security audit'
                ],
                'full_suite' => [
                    'enabled' => true,
                    'frequency' => 'weekly',
                    'time' => '0 1 * * 0', // 1:00 AM Sunday
                    'mode' => 'full',
                    'description' => 'Weekly comprehensive test suite'
                ],
                'integration_tests' => [
                    'enabled' => true,
                    'frequency' => 'daily',
                    'time' => '0 6 * * *', // 6:00 AM daily
                    'mode' => 'integration',
                    'description' => 'Daily integration tests'
                ]
            ],
            'notifications' => [
                'email' => [
                    'enabled' => false,
                    'recipients' => ['admin@apsdreamhome.com'],
                    'on_failure' => true,
                    'on_low_pass_rate' => true,
                    'threshold' => 80
                ],
                'slack' => [
                    'enabled' => false,
                    'webhook' => '',
                    'on_failure' => true,
                    'on_low_pass_rate' => true,
                    'threshold' => 90
                ]
            ],
            'maintenance' => [
                'cleanup_days' => 30,
                'max_reports' => 100,
                'backup_reports' => true
            ]
        ];
    }
    
    public function runScheduler()
    {
        $this->log("Starting cron scheduler");
        
        $currentTime = new DateTime();
        $dueSchedules = $this->getDueSchedules($currentTime);
        
        if (empty($dueSchedules)) {
            $this->log("No schedules due at this time");
            return;
        }
        
        $this->log("Found " . count($dueSchedules) . " due schedules");
        
        foreach ($dueSchedules as $scheduleName => $schedule) {
            $this->executeScheduledTest($scheduleName, $schedule);
        }
        
        $this->performMaintenance();
        $this->log("Cron scheduler completed");
    }
    
    private function getDueSchedules(DateTime $currentTime)
    {
        $dueSchedules = [];
        $currentMinute = $currentTime->format('i');
        $currentHour = $currentTime->format('H');
        $currentDay = $currentTime->format('d');
        $currentMonth = $currentTime->format('m');
        $currentDayOfWeek = $currentTime->format('w');
        
        foreach ($this->config['schedules'] as $name => $schedule) {
            if (!$schedule['enabled']) {
                continue;
            }
            
            // Check if schedule is due based on cron expression
            if ($this->isScheduleDue($schedule['time'], $currentTime)) {
                // Check if it was already run recently
                if (!$this->wasRecentlyRun($name, $currentTime)) {
                    $dueSchedules[$name] = $schedule;
                }
            }
        }
        
        return $dueSchedules;
    }
    
    private function isScheduleDue($cronExpression, DateTime $currentTime)
    {
        // Simple cron expression parser
        // Format: minute hour day month day_of_week
        
        $parts = explode(' ', $cronExpression);
        if (count($parts) !== 5) {
            return false;
        }
        
        $minute = $parts[0];
        $hour = $parts[1];
        $day = $parts[2];
        $month = $parts[3];
        $dayOfWeek = $parts[4];
        
        $currentMinute = (int)$currentTime->format('i');
        $currentHour = (int)$currentTime->format('H');
        $currentDay = (int)$currentTime->format('d');
        $currentMonth = (int)$currentTime->format('m');
        $currentDayOfWeek = (int)$currentTime->format('w');
        
        // Check each part
        if (!$this->matchesCronPart($minute, $currentMinute)) {
            return false;
        }
        
        if (!$this->matchesCronPart($hour, $currentHour)) {
            return false;
        }
        
        if (!$this->matchesCronPart($day, $currentDay)) {
            return false;
        }
        
        if (!$this->matchesCronPart($month, $currentMonth)) {
            return false;
        }
        
        if (!$this->matchesCronPart($dayOfWeek, $currentDayOfWeek)) {
            return false;
        }
        
        return true;
    }
    
    private function matchesCronPart($cronPart, $currentValue)
    {
        // Handle wildcard
        if ($cronPart === '*') {
            return true;
        }
        
        // Handle step values (e.g., */30)
        if (strpos($cronPart, '*/') === 0) {
            $step = (int)substr($cronPart, 2);
            return $currentValue % $step === 0;
        }
        
        // Handle specific values
        if (is_numeric($cronPart)) {
            return (int)$cronPart === $currentValue;
        }
        
        // Handle comma-separated values
        if (strpos($cronPart, ',') !== false) {
            $values = explode(',', $cronPart);
            return in_array($currentValue, array_map('intval', $values));
        }
        
        return false;
    }
    
    private function wasRecentlyRun($scheduleName, DateTime $currentTime)
    {
        $scheduleData = $this->loadScheduleData();
        
        if (!isset($scheduleData[$scheduleName])) {
            return false;
        }
        
        $lastRun = new DateTime($scheduleData[$scheduleName]['last_run']);
        $interval = $lastRun->diff($currentTime);
        
        // Don't run if it was executed in the last 55 minutes (to prevent double execution)
        return $interval->i < 55 && $interval->h === 0 && $interval->d === 0;
    }
    
    private function loadScheduleData()
    {
        if (!file_exists($this->scheduleFile)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($this->scheduleFile), true);
        return $data ?: [];
    }
    
    private function saveScheduleData($scheduleData)
    {
        file_put_contents($this->scheduleFile, json_encode($scheduleData, JSON_PRETTY_PRINT));
    }
    
    private function executeScheduledTest($scheduleName, $schedule)
    {
        $this->log("Executing scheduled test: {$scheduleName} ({$schedule['description']})");
        
        $startTime = new DateTime();
        
        try {
            $results = $this->automationSuite->runAutomatedTestSuite($schedule['mode']);
            
            $endTime = new DateTime();
            $executionTime = $endTime->getTimestamp() - $startTime->getTimestamp();
            
            // Update schedule data
            $scheduleData = $this->loadScheduleData();
            $scheduleData[$scheduleName] = [
                'last_run' => $startTime->format('Y-m-d H:i:s'),
                'execution_time' => $executionTime,
                'status' => 'completed',
                'pass_rate' => $results['summary']['overall_pass_rate'],
                'total_tests' => $results['summary']['total_tests'],
                'failed_tests' => $results['summary']['total_failed'],
                'critical_failures' => $results['summary']['critical_failures']
            ];
            
            $this->saveScheduleData($scheduleData);
            
            $this->log("Scheduled test {$scheduleName} completed successfully");
            $this->log("Results: {$results['summary']['overall_pass_rate']}% pass rate, {$executionTime}s execution time");
            
            // Send notifications if needed
            $this->sendScheduledNotifications($scheduleName, $schedule, $results);
            
        } catch (Exception $e) {
            $endTime = new DateTime();
            $executionTime = $endTime->getTimestamp() - $startTime->getTimestamp();
            
            // Update schedule data with failure
            $scheduleData = $this->loadScheduleData();
            $scheduleData[$scheduleName] = [
                'last_run' => $startTime->format('Y-m-d H:i:s'),
                'execution_time' => $executionTime,
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
            
            $this->saveScheduleData($scheduleData);
            
            $this->log("ERROR: Scheduled test {$scheduleName} failed: " . $e->getMessage());
            
            // Send failure notifications
            $this->sendFailureNotifications($scheduleName, $schedule, $e);
        }
    }
    
    private function sendScheduledNotifications($scheduleName, $schedule, $results)
    {
        $passRate = $results['summary']['overall_pass_rate'];
        $criticalFailures = $results['summary']['critical_failures'];
        
        // Check for failure notifications
        if ($this->config['notifications']['email']['on_failure'] && $criticalFailures > 0) {
            $this->sendEmailNotification(
                "🚨 Critical Test Failure: {$scheduleName}",
                "The scheduled test '{$scheduleName}' encountered {$criticalFailures} critical failures.",
                $results
            );
        }
        
        // Check for low pass rate notifications
        if ($this->config['notifications']['email']['on_low_pass_rate'] && 
            $passRate < $this->config['notifications']['email']['threshold']) {
            $this->sendEmailNotification(
                "⚠️ Low Pass Rate: {$scheduleName}",
                "The scheduled test '{$scheduleName}' achieved only {$passRate}% pass rate (threshold: {$this->config['notifications']['email']['threshold']}%).",
                $results
            );
        }
        
        // Slack notifications
        if ($this->config['notifications']['slack']['on_failure'] && $criticalFailures > 0) {
            $this->sendSlackNotification(
                "🚨 Critical Test Failure: {$scheduleName}",
                "The scheduled test '{$scheduleName}' encountered {$criticalFailures} critical failures.",
                $results
            );
        }
        
        if ($this->config['notifications']['slack']['on_low_pass_rate'] && 
            $passRate < $this->config['notifications']['slack']['threshold']) {
            $this->sendSlackNotification(
                "⚠️ Low Pass Rate: {$scheduleName}",
                "The scheduled test '{$scheduleName}' achieved only {$passRate}% pass rate.",
                $results
            );
        }
    }
    
    private function sendFailureNotifications($scheduleName, $schedule, Exception $e)
    {
        if ($this->config['notifications']['email']['on_failure']) {
            $this->sendEmailNotification(
                "🚨 Scheduled Test Failed: {$scheduleName}",
                "The scheduled test '{$scheduleName}' failed to execute: " . $e->getMessage(),
                null
            );
        }
        
        if ($this->config['notifications']['slack']['on_failure']) {
            $this->sendSlackNotification(
                "🚨 Scheduled Test Failed: {$scheduleName}",
                "The scheduled test '{$scheduleName}' failed to execute: " . $e->getMessage(),
                null
            );
        }
    }
    
    private function sendEmailNotification($subject, $message, $results = null)
    {
        if (!$this->config['notifications']['email']['enabled']) {
            return;
        }
        
        // Email implementation would go here
        $this->log("EMAIL: {$subject} - {$message}");
    }
    
    private function sendSlackNotification($title, $message, $results = null)
    {
        if (!$this->config['notifications']['slack']['enabled']) {
            return;
        }
        
        // Slack implementation would go here
        $this->log("SLACK: {$title} - {$message}");
    }
    
    private function performMaintenance()
    {
        $this->log("Performing maintenance tasks");
        
        // Clean up old reports
        $this->cleanupOldReports();
        
        // Backup recent reports
        if ($this->config['maintenance']['backup_reports']) {
            $this->backupReports();
        }
        
        // Update schedule statistics
        $this->updateScheduleStatistics();
        
        $this->log("Maintenance completed");
    }
    
    private function cleanupOldReports()
    {
        $resultsDir = __DIR__ . '/../../results/automation/';
        $cutoffDate = new DateTime();
        $cutoffDate->sub(new DateInterval("P{$this->config['maintenance']['cleanup_days']}D"));
        
        $files = glob($resultsDir . 'automation_report_*.json');
        $deletedCount = 0;
        
        foreach ($files as $file) {
            $fileTime = new DateTime('@' . filemtime($file));
            if ($fileTime < $cutoffDate) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        // Clean up HTML reports too
        $htmlFiles = glob($resultsDir . 'automation_report_*.html');
        foreach ($htmlFiles as $file) {
            $fileTime = new DateTime('@' . filemtime($file));
            if ($fileTime < $cutoffDate) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $this->log("Cleaned up {$deletedCount} old report files");
        }
    }
    
    private function backupReports()
    {
        $resultsDir = __DIR__ . '/../../results/automation/';
        $backupDir = $resultsDir . 'backups/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $today = date('Y-m-d');
        $backupFile = $backupDir . "backup_{$today}.json";
        
        // Get latest report
        $files = glob($resultsDir . 'automation_report_*.json');
        if (empty($files)) {
            return;
        }
        
        $latestFile = max($files);
        
        // Copy to backup directory
        copy($latestFile, $backupFile);
        
        $this->log("Created backup: {$backupFile}");
    }
    
    private function updateScheduleStatistics()
    {
        $scheduleData = $this->loadScheduleData();
        $stats = [
            'total_schedules' => count($this->config['schedules']),
            'enabled_schedules' => 0,
            'last_24h_runs' => 0,
            'last_7d_runs' => 0,
            'success_rate' => 0,
            'average_execution_time' => 0
        ];
        
        $now = new DateTime();
        $last24h = clone $now;
        $last24h->sub(new DateInterval('PT24H'));
        
        $last7d = clone $now;
        $last7d->sub(new DateInterval('P7D'));
        
        $totalRuns = 0;
        $successfulRuns = 0;
        $totalExecutionTime = 0;
        
        foreach ($this->config['schedules'] as $name => $schedule) {
            if ($schedule['enabled']) {
                $stats['enabled_schedules']++;
            }
            
            if (isset($scheduleData[$name])) {
                $lastRun = new DateTime($scheduleData[$name]['last_run']);
                
                if ($lastRun > $last24h) {
                    $stats['last_24h_runs']++;
                }
                
                if ($lastRun > $last7d) {
                    $stats['last_7d_runs']++;
                }
                
                $totalRuns++;
                if ($scheduleData[$name]['status'] === 'completed') {
                    $successfulRuns++;
                    $totalExecutionTime += $scheduleData[$name]['execution_time'] ?? 0;
                }
            }
        }
        
        if ($totalRuns > 0) {
            $stats['success_rate'] = round(($successfulRuns / $totalRuns) * 100, 2);
            $stats['average_execution_time'] = round($totalExecutionTime / $successfulRuns, 2);
        }
        
        $statsFile = $this->scheduleFile;
        $fullData = $scheduleData;
        $fullData['statistics'] = $stats;
        $fullData['last_updated'] = $now->format('Y-m-d H:i:s');
        
        $this->saveScheduleData($fullData);
        
        $this->log("Updated schedule statistics");
    }
    
    public function getScheduleStatus()
    {
        $scheduleData = $this->loadScheduleData();
        $status = [];
        
        foreach ($this->config['schedules'] as $name => $schedule) {
            $status[$name] = [
                'description' => $schedule['description'],
                'enabled' => $schedule['enabled'],
                'frequency' => $schedule['frequency'],
                'mode' => $schedule['mode'],
                'cron' => $schedule['time'],
                'last_run' => null,
                'status' => 'never_run',
                'pass_rate' => null,
                'execution_time' => null
            ];
            
            if (isset($scheduleData[$name])) {
                $status[$name]['last_run'] = $scheduleData[$name]['last_run'];
                $status[$name]['status'] = $scheduleData[$name]['status'];
                $status[$name]['pass_rate'] = $scheduleData[$name]['pass_rate'] ?? null;
                $status[$name]['execution_time'] = $scheduleData[$name]['execution_time'] ?? null;
                
                if (isset($scheduleData[$name]['error'])) {
                    $status[$name]['error'] = $scheduleData[$name]['error'];
                }
            }
        }
        
        return $status;
    }
    
    public function getScheduleStatistics()
    {
        $scheduleData = $this->loadScheduleData();
        
        if (!isset($scheduleData['statistics'])) {
            $this->updateScheduleStatistics();
            $scheduleData = $this->loadScheduleData();
        }
        
        return $scheduleData['statistics'] ?? [];
    }
    
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] CRON: {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        echo $logEntry;
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $options = getopt('h', ['help', 'status', 'statistics']);
    
    if (isset($options['h']) || isset($options['help'])) {
        echo "APS Dream Home - Cron Scheduler\n";
        echo "Usage: php CronScheduler.php [options]\n\n";
        echo "Options:\n";
        echo "  --status        Show current schedule status\n";
        echo "  --statistics    Show schedule statistics\n";
        echo "  -h, --help      Show this help message\n\n";
        echo "Examples:\n";
        echo "  php CronScheduler.php              # Run scheduler\n";
        echo "  php CronScheduler.php --status      # Show status\n";
        echo "  php CronScheduler.php --statistics  # Show statistics\n";
        exit(0);
    }
    
    try {
        $scheduler = new CronScheduler();
        
        if (isset($options['status'])) {
            $status = $scheduler->getScheduleStatus();
            
            echo "=== Schedule Status ===\n\n";
            
            foreach ($status as $name => $info) {
                $enabled = $info['enabled'] ? '✅' : '❌';
                $lastRun = $info['last_run'] ? $info['last_run'] : 'Never';
                $statusIcon = $info['status'] === 'completed' ? '✅' : 
                             ($info['status'] === 'failed' ? '❌' : '⏳');
                
                echo "{$enabled} {$name}\n";
                echo "  Description: {$info['description']}\n";
                echo "  Frequency: {$info['frequency']} ({$info['cron']})\n";
                echo "  Mode: {$info['mode']}\n";
                echo "  Last Run: {$lastRun}\n";
                echo "  Status: {$statusIcon} {$info['status']}\n";
                
                if ($info['pass_rate'] !== null) {
                    echo "  Pass Rate: {$info['pass_rate']}%\n";
                }
                
                if ($info['execution_time'] !== null) {
                    echo "  Execution Time: {$info['execution_time']}s\n";
                }
                
                if (isset($info['error'])) {
                    echo "  Error: {$info['error']}\n";
                }
                
                echo "\n";
            }
            
        } elseif (isset($options['statistics'])) {
            $stats = $scheduler->getScheduleStatistics();
            
            echo "=== Schedule Statistics ===\n\n";
            echo "Total Schedules: {$stats['total_schedules']}\n";
            echo "Enabled Schedules: {$stats['enabled_schedules']}\n";
            echo "Runs (Last 24h): {$stats['last_24h_runs']}\n";
            echo "Runs (Last 7d): {$stats['last_7d_runs']}\n";
            echo "Success Rate: {$stats['success_rate']}%\n";
            echo "Average Execution Time: {$stats['average_execution_time']}s\n";
            echo "Last Updated: {$stats['last_updated']}\n";
            
        } else {
            // Run the scheduler
            $scheduler->runScheduler();
        }
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    // Web interface
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $scheduler = new CronScheduler();
        
        try {
            switch ($action) {
                case 'run':
                    $scheduler->runScheduler();
                    echo json_encode(['status' => 'success', 'message' => 'Scheduler executed']);
                    break;
                    
                case 'status':
                    $status = $scheduler->getScheduleStatus();
                    echo json_encode(['status' => 'success', 'data' => $status]);
                    break;
                    
                case 'statistics':
                    $stats = $scheduler->getScheduleStatistics();
                    echo json_encode(['status' => 'success', 'data' => $stats]);
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
    <title>APS Dream Home - Cron Scheduler</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .button:hover { background: #0056b3; }
        .button.success { background: #28a745; }
        .button.success:hover { background: #218838; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; flex: 1; }
        .schedule-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .schedule-card { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .schedule-card.disabled { opacity: 0.6; }
        .status-good { color: #28a745; }
        .status-bad { color: #dc3545; }
        .status-pending { color: #6c757d; }
        .loading { display: none; text-align: center; margin: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>⏰ APS Dream Home Cron Scheduler</h1>
            <p>Automated Test Scheduling and Execution</p>
        </div>
        
        <div style='text-align: center; margin: 30px 0;'>
            <button class='button success' onclick='runScheduler()'>🔄 Run Scheduler Now</button>
            <button class='button' onclick='refreshStatus()'>🔄 Refresh Status</button>
        </div>
        
        <div class='loading' id='loading'>
            <h3>⏳ Executing Scheduler...</h3>
            <p>Please wait while the scheduler runs...</p>
        </div>
        
        <div id='statistics' class='stats'></div>
        
        <h2>📅 Schedule Status</h2>
        <div id='schedule-grid' class='schedule-grid'></div>
    </div>
    
    <script>
        function runScheduler() {
            document.getElementById('loading').style.display = 'block';
            
            fetch('CronScheduler.php?action=run')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    if (data.status === 'success') {
                        alert('Scheduler executed successfully!');
                        refreshStatus();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    alert('Error: ' + error.message);
                });
        }
        
        function refreshStatus() {
            loadStatistics();
            loadScheduleStatus();
        }
        
        function loadStatistics() {
            fetch('CronScheduler.php?action=statistics')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayStatistics(data.data);
                    }
                });
        }
        
        function loadScheduleStatus() {
            fetch('CronScheduler.php?action=status')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        displayScheduleStatus(data.data);
                    }
                });
        }
        
        function displayStatistics(stats) {
            const html = \`
                <div class='stat-card'>
                    <h3>\${stats.total_schedules}</h3>
                    <p>Total Schedules</p>
                </div>
                <div class='stat-card'>
                    <h3>\${stats.enabled_schedules}</h3>
                    <p>Enabled</p>
                </div>
                <div class='stat-card'>
                    <h3>\${stats.last_24h_runs}</h3>
                    <p>Runs (24h)</p>
                </div>
                <div class='stat-card'>
                    <h3>\${stats.success_rate}%</h3>
                    <p>Success Rate</p>
                </div>
                <div class='stat-card'>
                    <h3>\${stats.average_execution_time}s</h3>
                    <p>Avg Time</p>
                </div>
            \`;
            
            document.getElementById('statistics').innerHTML = html;
        }
        
        function displayScheduleStatus(status) {
            let html = '';
            
            for (const [name, info] of Object.entries(status)) {
                const enabled = info.enabled ? '' : 'disabled';
                const enabledIcon = info.enabled ? '✅' : '❌';
                const lastRun = info.last_run || 'Never';
                const statusClass = info.status === 'completed' ? 'status-good' : 
                                 (info.status === 'failed' ? 'status-bad' : 'status-pending');
                const statusIcon = info.status === 'completed' ? '✅' : 
                                 (info.status === 'failed' ? '❌' : '⏳');
                
                html += \`
                    <div class='schedule-card \${enabled}'>
                        <h4>\${enabledIcon} \${name}</h4>
                        <p><strong>\${info.description}</strong></p>
                        <p><em>\${info.frequency}</em> (\${info.cron})</p>
                        <p>Mode: \${info.mode}</p>
                        <p>Last Run: \${lastRun}</p>
                        <p>Status: <span class='\${statusClass}'>\${statusIcon} \${info.status}</span></p>
                \`;
                
                if (info.pass_rate !== null) {
                    html += \`<p>Pass Rate: \${info.pass_rate}%</p>\`;
                }
                
                if (info.execution_time !== null) {
                    html += \`<p>Execution Time: \${info.execution_time}s</p>\`;
                }
                
                if (info.error) {
                    html += \`<p style='color: #dc3545; font-size: 0.9em;'>Error: \${info.error}</p>\`;
                }
                
                html += \`</div>\`;
            }
            
            document.getElementById('schedule-grid').innerHTML = html;
        }
        
        // Load initial data
        window.onload = function() {
            refreshStatus();
        };
    </script>
</body>
</html>";
    }
}
?>
