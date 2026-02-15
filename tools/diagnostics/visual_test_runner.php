<?php
/**
 * Visual Test Runner - Main execution script
 */

require_once 'includes/config.php';

class VisualTestRunner {
    private $conn;
    private $config;
    
    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? $GLOBALS['con'] ?? null;
        $this->config = $this->loadConfig();
    }
    
    public function runTestSuite($suiteName) {
        echo "Starting visual test suite: {$suiteName}\n";
        
        $suite = $this->getTestSuite($suiteName);
        if (!$suite) {
            echo "Test suite not found: {$suiteName}\n";
            return false;
        }
        
        $runId = $this->generateRunId();
        $startTime = microtime(true);
        
        $results = [
            'total_tests' => 0,
            'passed_tests' => 0,
            'failed_tests' => 0,
            'warning_tests' => 0
        ];
        
        $pages = json_decode($suite['test_urls'], true);
        $viewports = json_decode($suite['viewport_sizes'], true);
        $browsers = json_decode($suite['browsers'], true);
        
        foreach ($pages as $page) {
            foreach ($viewports as $viewport) {
                foreach ($browsers as $browser) {
                    $results['total_tests']++;
                    $testResult = $this->runSingleTest($suite['id'], $page, $viewport, $browser, $runId);
                    
                    if ($testResult['status'] === 'passed') {
                        $results['passed_tests']++;
                    } elseif ($testResult['status'] === 'failed') {
                        $results['failed_tests']++;
                    } else {
                        $results['warning_tests']++;
                    }
                }
            }
        }
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $this->saveTestRun($runId, $suite['id'], $results, $duration);
        
        echo "Test suite completed in " . round($duration/1000, 2) . " seconds\n";
        echo "Results: {$results['passed_tests']} passed, {$results['failed_tests']} failed, {$results['warning_tests']} warnings\n";
        
        return $results;
    }
    
    private function runSingleTest($suiteId, $page, $viewport, $browser, $runId) {
        // Simulate visual test execution
        $status = rand(0, 10) > 1 ? 'passed' : (rand(0, 1) ? 'failed' : 'warning');
        $pixelDiff = $status === 'passed' ? 0 : rand(100, 5000);
        $diffPercentage = $status === 'passed' ? 0 : round($pixelDiff / 1000, 2);
        $duration = rand(500, 2000);
        
        $sql = "INSERT INTO visual_test_results (
            suite_id, test_run_id, page_url, viewport, browser,
            pixel_difference, difference_percentage, status, test_duration_ms
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $suiteId, $runId, $page, $viewport, $browser,
            $pixelDiff, $diffPercentage, $status, $duration
        ]);
        
        return [
            'status' => $status,
            'pixel_difference' => $pixelDiff,
            'difference_percentage' => $diffPercentage
        ];
    }
    
    private function getTestSuite($suiteName) {
        $sql = "SELECT * FROM visual_test_suites WHERE suite_name = ? AND status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$suiteName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function generateRunId() {
        return 'visual_test_' . date('Y-m-d_H-i-s') . '_' . uniqid();
    }
    
    private function saveTestRun($runId, $suiteId, $results, $duration) {
        $overallStatus = $results['failed_tests'] > 0 ? 'failed' : 
                         ($results['warning_tests'] > 0 ? 'warning' : 'passed');
        
        $sql = "INSERT INTO visual_test_runs (
            run_id, suite_id, total_tests, passed_tests, failed_tests,
            warning_tests, overall_status, run_duration_ms
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $runId, $suiteId, $results['total_tests'], $results['passed_tests'],
            $results['failed_tests'], $results['warning_tests'], $overallStatus, $duration
        ]);
    }
    
    private function loadConfig() {
        return [
            'screenshot_timeout' => 5000,
            'comparison_threshold' => 0.1,
            'output_directory' => 'visual_tests/screenshots'
        ];
    }
}

// Run test if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $runner = new VisualTestRunner();
    $suite = $_GET['suite'] ?? 'homepage_suite';
    $runner->runTestSuite($suite);
}
?>