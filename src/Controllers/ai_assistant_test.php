<?php
/**
 * Comprehensive Test Suite for AI Assistant
 */
require_once __DIR__ . '/includes/env_loader.php';
require_once __DIR__ . '/includes/db_security_upgrade.php';
require_once __DIR__ . '/includes/classes/AIAssistant.php';

class AIAssistantTest {
    private $dbSecurity;
    private $aiAssistant;
    private $testResults = [];

    public function __construct() {
        try {
            $this->dbSecurity = new DatabaseSecurityUpgrade();
            $this->aiAssistant = new AIAssistant($this->dbSecurity);
        } catch (Exception $e) {
            $this->logTestFailure('Initialization', $e->getMessage());
        }
    }

    /**
     * Run all test cases
     */
    public function runTests() {
        $this->testValidSuggestionGeneration();
        $this->testInvalidUserContext();
        $this->testMissingContextFields();
        $this->generateTestReport();
    }

    /**
     * Test successful AI suggestion generation
     */
    private function testValidSuggestionGeneration() {
        try {
            $context = [
                'property_type' => 'apartment',
                'budget' => '500000',
                'location' => 'Mumbai'
            ];
            $suggestions = $this->aiAssistant->generateSuggestions(1, $context);
            
            $this->testResults[] = [
                'test' => 'Valid Suggestion Generation',
                'status' => count($suggestions) > 0 ? 'PASS' : 'FAIL',
                'details' => 'Generated ' . count($suggestions) . ' suggestions'
            ];
        } catch (Exception $e) {
            $this->logTestFailure('Valid Suggestion Generation', $e->getMessage());
        }
    }

    /**
     * Test handling of invalid user context
     */
    private function testInvalidUserContext() {
        try {
            $this->aiAssistant->generateSuggestions(0, []);
            $this->testResults[] = [
                'test' => 'Invalid User Context',
                'status' => 'FAIL',
                'details' => 'Did not throw exception for invalid user ID'
            ];
        } catch (Exception $e) {
            $this->testResults[] = [
                'test' => 'Invalid User Context',
                'status' => 'PASS',
                'details' => 'Correctly handled invalid user context'
            ];
        }
    }

    /**
     * Test handling of missing context fields
     */
    private function testMissingContextFields() {
        try {
            $incompleteContexts = [
                ['property_type' => 'villa'],
                ['budget' => '1000000'],
                ['location' => 'Delhi']
            ];

            foreach ($incompleteContexts as $context) {
                try {
                    $this->aiAssistant->generateSuggestions(1, $context);
                    $this->testResults[] = [
                        'test' => 'Missing Context Fields',
                        'status' => 'FAIL',
                        'details' => 'Did not throw exception for incomplete context: ' . json_encode($context)
                    ];
                } catch (Exception $e) {
                    // Expected behavior
                }
            }

            $this->testResults[] = [
                'test' => 'Missing Context Fields',
                'status' => 'PASS',
                'details' => 'Correctly handled incomplete context scenarios'
            ];
        } catch (Exception $e) {
            $this->logTestFailure('Missing Context Fields', $e->getMessage());
        }
    }

    /**
     * Log test failures
     */
    private function logTestFailure($testName, $errorMessage) {
        $this->testResults[] = [
            'test' => $testName,
            'status' => 'FAIL',
            'details' => $errorMessage
        ];
        error_log("Test Failure in {$testName}: {$errorMessage}");
    }

    /**
     * Generate comprehensive test report
     */
    private function generateTestReport() {
        $reportPath = __DIR__ . '/logs/ai_assistant_test_report.json';
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_tests' => count($this->testResults),
            'passed_tests' => count(array_filter($this->testResults, function($result) {
                return $result['status'] === 'PASS';
            })),
            'failed_tests' => count(array_filter($this->testResults, function($result) {
                return $result['status'] === 'FAIL';
            })),
            'results' => $this->testResults
        ];

        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        // Output to console/log
        echo "AI Assistant Test Report:\n";
        echo "Total Tests: {$report['total_tests']}\n";
        echo "Passed: {$report['passed_tests']}\n";
        echo "Failed: {$report['failed_tests']}\n";
        
        foreach ($this->testResults as $result) {
            echo "{$result['test']}: {$result['status']} - {$result['details']}\n";
        }
    }
}

// Run tests if script is executed directly
$test = new AIAssistantTest();
$test->runTests();
