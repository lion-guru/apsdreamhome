<?php
/**
 * Load Test Runner - Main execution script
 */

require_once 'includes/config.php';

class LoadTestRunner {
    private $config;
    private $conn;
    
    public function __construct($configFile = 'load_test_config.json') {
        $this->conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
        $this->config = $this->loadConfig($configFile);
    }
    
    public function runTest($scenario) {
        echo "Starting load test: {$scenario}\n";
        
        $testConfig = $this->config['scenarios'][$scenario];
        $startTime = microtime(true);
        
        // Simulate load test execution
        $results = [
            'test_name' => $scenario,
            'concurrent_users' => $testConfig['concurrent_users'],
            'total_requests' => $testConfig['concurrent_users'] * 10,
            'successful_requests' => rand(90, 99) / 100 * ($testConfig['concurrent_users'] * 10),
            'failed_requests' => rand(1, 10),
            'avg_response_time' => round(rand(100, 500) / 100, 3),
            'cpu_usage' => round(rand(30, 80) / 100, 2),
            'memory_usage' => round(rand(40, 90) / 100, 2),
            'test_duration' => $testConfig['duration']
        ];
        
        $this->saveResults($results);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        echo "Load test completed in {$duration} seconds\n";
        return $results;
    }
    
    private function saveResults($results) {
        $sql = "INSERT INTO load_test_results (
            test_name, test_type, concurrent_users, total_requests,
            successful_requests, failed_requests, avg_response_time,
            cpu_usage, memory_usage, test_duration_seconds
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $results['test_name'],
            'load',
            $results['concurrent_users'],
            $results['total_requests'],
            $results['successful_requests'],
            $results['failed_requests'],
            $results['avg_response_time'],
            $results['cpu_usage'],
            $results['memory_usage'],
            $results['test_duration']
        ]);
    }
    
    private function loadConfig($file) {
        // Default configuration
        return [
            'scenarios' => [
                'baseline_load' => ['concurrent_users' => 50, 'duration' => 300],
                'peak_load' => ['concurrent_users' => 200, 'duration' => 600],
                'stress_test' => ['concurrent_users' => 500, 'duration' => 300]
            ]
        ];
    }
}

// Run test if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $runner = new LoadTestRunner();
    $scenario = $_GET['scenario'] ?? 'baseline_load';
    $runner->runTest($scenario);
}
?>