<?php
/**
 * Test Automation Script for APS Dream Home
 * Automated testing with CI/CD integration
 */

class TestAutomation {
    private $config;
    private $results = [];
    
    public function __construct() {
        $this->config = [
            "test_types" => ["unit", "integration", "legacy", "security", "performance"],
            "timeout" => 300, // 5 minutes
            "parallel" => 4
        ];
    }
    
    public function runAutomatedTests() {
        echo "Starting Automated Test Suite...\n\n";
        
        $startTime = microtime(true);
        
        // Run different test types
        foreach ($this->config["test_types"] as $testType) {
            echo "Running $testType tests...\n";
            $this->runTestType($testType);
        }
        
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) / 60, 2);
        
        echo "\n=== Test Automation Complete ===\n";
        echo "Duration: {$duration} minutes\n";
        echo "Results: " . json_encode($this->results, JSON_PRETTY_PRINT) . "\n";
        
        // Generate CI/CD report
        $this->generateCICDReport();
    }
    
    private function runTestType($testType) {
        switch ($testType) {
            case "unit":
                $this->runUnitTests();
                break;
            case "integration":
                $this->runIntegrationTests();
                break;
            case "legacy":
                $this->runLegacyTests();
                break;
            case "security":
                $this->runSecurityTests();
                break;
            case "performance":
                $this->runPerformanceTests();
                break;
        }
    }
    
    private function runUnitTests() {
        // Run PHPUnit unit tests
        $command = "vendor/bin/phpunit tests/Unit --log-junit tests/results/unit-junit.xml";
        $output = $this->executeCommand($command);
        
        $this->results["unit"] = [
            "status" => $this->parseTestResult($output),
            "output" => $output
        ];
    }
    
    private function runIntegrationTests() {
        // Run integration tests
        $command = "vendor/bin/phpunit tests/Integration --log-junit tests/results/integration-junit.xml";
        $output = $this->executeCommand($command);
        
        $this->results["integration"] = [
            "status" => $this->parseTestResult($output),
            "output" => $output
        ];
    }
    
    private function runLegacyTests() {
        // Run legacy component tests
        $command = "php tests/EnhancedTestSuite.php";
        $output = $this->executeCommand($command);
        
        $this->results["legacy"] = [
            "status" => $this->parseTestResult($output),
            "output" => $output
        ];
    }
    
    private function runSecurityTests() {
        // Run security tests
        $command = "vendor/bin/phpunit tests/Security --log-junit tests/results/security-junit.xml";
        $output = $this->executeCommand($command);
        
        $this->results["security"] = [
            "status" => $this->parseTestResult($output),
            "output" => $output
        ];
    }
    
    private function runPerformanceTests() {
        // Run performance tests
        $command = "php tests/PerformanceTestSuite.php";
        $output = $this->executeCommand($command);
        
        $this->results["performance"] = [
            "status" => $this->parseTestResult($output),
            "output" => $output
        ];
    }
    
    private function executeCommand($command) {
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];
        
        $process = proc_open($command, $descriptorspec, $pipes, __DIR__ . "/..");
        
        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            $exitCode = proc_close($process);
            
            return [
                "output" => $output,
                "error" => $error,
                "exit_code" => $exitCode
            ];
        }
        
        return ["output" => "", "error" => "Failed to execute command", "exit_code" => 1];
    }
    
    private function parseTestResult($output) {
        if (isset($output["exit_code"]) && $output["exit_code"] === 0) {
            return "passed";
        }
        
        $outputText = $output["output"] . $output["error"];
        
        if (strpos($outputText, "FAILURES") !== false) {
            return "failed";
        }
        
        if (strpos($outputText, "ERRORS") !== false) {
            return "error";
        }
        
        return "unknown";
    }
    
    private function generateCICDReport() {
        $report = [
            "timestamp" => date("Y-m-d H:i:s"),
            "summary" => [
                "total" => count($this->results),
                "passed" => 0,
                "failed" => 0,
                "errors" => 0
            ],
            "details" => $this->results
        ];
        
        foreach ($this->results as $result) {
            switch ($result["status"]) {
                case "passed":
                    $report["summary"]["passed"]++;
                    break;
                case "failed":
                    $report["summary"]["failed"]++;
                    break;
                case "error":
                    $report["summary"]["errors"]++;
                    break;
            }
        }
        
        file_put_contents(__DIR__ . "/../test-automation-report.json", json_encode($report, JSON_PRETTY_PRINT));
        echo "CI/CD report saved to: test-automation-report.json\n";
        
        // Exit with appropriate code for CI/CD
        if ($report["summary"]["failed"] > 0 || $report["summary"]["errors"] > 0) {
            exit(1);
        }
    }
}

// Run automation if called directly
if (php_sapi_name() === "cli") {
    $automation = new TestAutomation();
    $automation->runAutomatedTests();
}
?>