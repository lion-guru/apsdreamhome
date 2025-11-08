<?php

namespace App\Core;

/**
 * Comprehensive Testing Framework
 * PHPUnit-based testing with custom test runners and reporting
 */
class TestFramework
{
    private static $instance = null;
    private $testResults = [];
    private $testSuite = [];

    private function __construct()
    {
        // Initialize PHPUnit if available
        if (class_exists('PHPUnit\Framework\TestCase')) {
            $this->initializePHPUnit();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize PHPUnit framework
     */
    private function initializePHPUnit(): void
    {
        // PHPUnit is available, use it for comprehensive testing
        $this->testSuite = new \PHPUnit\Framework\TestSuite('APS Dream Home Tests');
    }

    /**
     * Run all tests
     */
    public function runTests(): array
    {
        $results = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'time' => 0,
            'tests' => []
        ];

        $startTime = microtime(true);

        // Run database tests
        $results['tests'][] = $this->runDatabaseTests();

        // Run API tests
        $results['tests'][] = $this->runApiTests();

        // Run model tests
        $results['tests'][] = $this->runModelTests();

        // Run controller tests
        $results['tests'][] = $this->runControllerTests();

        // Run security tests
        $results['tests'][] = $this->runSecurityTests();

        // Calculate totals
        foreach ($results['tests'] as $test) {
            $results['total'] += $test['total'];
            $results['passed'] += $test['passed'];
            $results['failed'] += $test['failed'];
            $results['errors'] += $test['errors'];
        }

        $results['time'] = microtime(true) - $startTime;

        return $results;
    }

    /**
     * Run database tests
     */
    private function runDatabaseTests(): array
    {
        $tests = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'tests' => [],
            'name' => 'Database Tests'
        ];

        try {
            // Test database connection
            $db = Database::getInstance();
            $connection = $db->getConnection();

            if ($connection) {
                $tests['tests'][] = [
                    'name' => 'Database Connection',
                    'status' => 'passed',
                    'message' => 'Database connection successful'
                ];
                $tests['passed']++;
            } else {
                $tests['tests'][] = [
                    'name' => 'Database Connection',
                    'status' => 'failed',
                    'message' => 'Database connection failed'
                ];
                $tests['failed']++;
            }

            // Test query execution
            $stmt = $db->query("SELECT 1 as test");
            $result = $stmt->fetch();

            if ($result && $result['test'] == 1) {
                $tests['tests'][] = [
                    'name' => 'Query Execution',
                    'status' => 'passed',
                    'message' => 'Query execution successful'
                ];
                $tests['passed']++;
            } else {
                $tests['tests'][] = [
                    'name' => 'Query Execution',
                    'status' => 'failed',
                    'message' => 'Query execution failed'
                ];
                $tests['failed']++;
            }

        } catch (\Exception $e) {
            $tests['tests'][] = [
                'name' => 'Database Exception',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $tests['errors']++;
        }

        $tests['total'] = count($tests['tests']);
        return $tests;
    }

    /**
     * Run API tests
     */
    private function runApiTests(): array
    {
        $tests = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'tests' => [],
            'name' => 'API Tests'
        ];

        $apiEndpoints = [
            'api/properties',
            'api/property',
            'api/inquiry/submit'
        ];

        foreach ($apiEndpoints as $endpoint) {
            try {
                $url = "http://localhost/apsdreamhomefinal/" . $endpoint;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($error) {
                    $tests['tests'][] = [
                        'name' => $endpoint,
                        'status' => 'error',
                        'message' => 'CURL Error: ' . $error
                    ];
                    $tests['errors']++;
                } elseif ($httpCode >= 200 && $httpCode < 300) {
                    $tests['tests'][] = [
                        'name' => $endpoint,
                        'status' => 'passed',
                        'message' => 'HTTP ' . $httpCode . ' - OK'
                    ];
                    $tests['passed']++;
                } else {
                    $tests['tests'][] = [
                        'name' => $endpoint,
                        'status' => 'failed',
                        'message' => 'HTTP ' . $httpCode . ' - Failed'
                    ];
                    $tests['failed']++;
                }

            } catch (\Exception $e) {
                $tests['tests'][] = [
                    'name' => $endpoint,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $tests['errors']++;
            }
        }

        $tests['total'] = count($tests['tests']);
        return $tests;
    }

    /**
     * Run model tests
     */
    private function runModelTests(): array
    {
        $tests = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'tests' => [],
            'name' => 'Model Tests'
        ];

        try {
            // Test if models can be instantiated
            $modelFiles = glob(__DIR__ . '/../models/*.php');

            foreach ($modelFiles as $modelFile) {
                $modelName = basename($modelFile, '.php');

                if ($modelName !== 'Model') { // Skip base model
                    try {
                        $className = 'App\\Models\\' . $modelName;

                        if (class_exists($className)) {
                            $tests['tests'][] = [
                                'name' => $modelName,
                                'status' => 'passed',
                                'message' => 'Model class exists and is loadable'
                            ];
                            $tests['passed']++;
                        } else {
                            $tests['tests'][] = [
                                'name' => $modelName,
                                'status' => 'failed',
                                'message' => 'Model class not found: ' . $className
                            ];
                            $tests['failed']++;
                        }
                    } catch (\Exception $e) {
                        $tests['tests'][] = [
                            'name' => $modelName,
                            'status' => 'error',
                            'message' => $e->getMessage()
                        ];
                        $tests['errors']++;
                    }
                }
            }

        } catch (\Exception $e) {
            $tests['tests'][] = [
                'name' => 'Model Loading',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $tests['errors']++;
        }

        $tests['total'] = count($tests['tests']);
        return $tests;
    }

    /**
     * Run controller tests
     */
    private function runControllerTests(): array
    {
        $tests = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'tests' => [],
            'name' => 'Controller Tests'
        ];

        try {
            // Test if controllers can be instantiated
            $controllerFiles = glob(__DIR__ . '/../controllers/*.php');

            foreach ($controllerFiles as $controllerFile) {
                $controllerName = basename($controllerFile, '.php');

                if ($controllerName !== 'Controller') { // Skip base controller
                    try {
                        $className = 'App\\Controllers\\' . $controllerName;

                        if (class_exists($className)) {
                            $tests['tests'][] = [
                                'name' => $controllerName,
                                'status' => 'passed',
                                'message' => 'Controller class exists and is loadable'
                            ];
                            $tests['passed']++;
                        } else {
                            $tests['tests'][] = [
                                'name' => $controllerName,
                                'status' => 'failed',
                                'message' => 'Controller class not found: ' . $className
                            ];
                            $tests['failed']++;
                        }
                    } catch (\Exception $e) {
                        $tests['tests'][] = [
                            'name' => $controllerName,
                            'status' => 'error',
                            'message' => $e->getMessage()
                        ];
                        $tests['errors']++;
                    }
                }
            }

        } catch (\Exception $e) {
            $tests['tests'][] = [
                'name' => 'Controller Loading',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $tests['errors']++;
        }

        $tests['total'] = count($tests['tests']);
        return $tests;
    }

    /**
     * Run security tests
     */
    private function runSecurityTests(): array
    {
        $tests = [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'tests' => [],
            'name' => 'Security Tests'
        ];

        try {
            // Test .htaccess security headers
            $htaccessContent = file_get_contents(__DIR__ . '/../../.htaccess');

            if (strpos($htaccessContent, 'X-Frame-Options') !== false) {
                $tests['tests'][] = [
                    'name' => 'X-Frame-Options Header',
                    'status' => 'passed',
                    'message' => 'X-Frame-Options header configured'
                ];
                $tests['passed']++;
            } else {
                $tests['tests'][] = [
                    'name' => 'X-Frame-Options Header',
                    'status' => 'failed',
                    'message' => 'X-Frame-Options header missing'
                ];
                $tests['failed']++;
            }

            if (strpos($htaccessContent, 'X-Content-Type-Options') !== false) {
                $tests['tests'][] = [
                    'name' => 'X-Content-Type-Options Header',
                    'status' => 'passed',
                    'message' => 'X-Content-Type-Options header configured'
                ];
                $tests['passed']++;
            } else {
                $tests['tests'][] = [
                    'name' => 'X-Content-Type-Options Header',
                    'status' => 'failed',
                    'message' => 'X-Content-Type-Options header missing'
                ];
                $tests['failed']++;
            }

        } catch (\Exception $e) {
            $tests['tests'][] = [
                'name' => 'Security Configuration',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $tests['errors']++;
        }

        $tests['total'] = count($tests['tests']);
        return $tests;
    }

    /**
     * Generate test report
     */
    public function generateReport(): string
    {
        $results = $this->runTests();

        $report = "=== APS Dream Home - Test Report ===\n\n";
        $report .= "Total Tests: {$results['total']}\n";
        $report .= "Passed: {$results['passed']}\n";
        $report .= "Failed: {$results['failed']}\n";
        $report .= "Errors: {$results['errors']}\n";
        $report .= "Execution Time: " . number_format($results['time'], 2) . " seconds\n\n";

        foreach ($results['tests'] as $testSuite) {
            $report .= "=== {$testSuite['name']} ===\n";
            $report .= "Total: {$testSuite['total']}\n";
            $report .= "Passed: {$testSuite['passed']}\n";
            $report .= "Failed: {$testSuite['failed']}\n";
            $report .= "Errors: {$testSuite['errors']}\n\n";

            foreach ($testSuite['tests'] as $test) {
                $status = strtoupper($test['status']);
                $report .= "  [{$status}] {$test['name']}: {$test['message']}\n";
            }
            $report .= "\n";
        }

        $report .= "=== Summary ===\n";
        $successRate = $results['total'] > 0 ? ($results['passed'] / $results['total']) * 100 : 0;
        $report .= "Success Rate: " . number_format($successRate, 2) . "%\n";

        if ($results['failed'] > 0 || $results['errors'] > 0) {
            $report .= "❌ Some tests failed or had errors. Please review the issues above.\n";
        } else {
            $report .= "✅ All tests passed successfully!\n";
        }

        return $report;
    }

    /**
     * Run performance benchmark tests
     */
    public function runPerformanceTests(): array
    {
        $benchmarks = [];

        // Database performance test
        $start = microtime(true);
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM properties", null, null, true);
        $result = $stmt->fetch();
        $end = microtime(true);

        $benchmarks['database_query'] = [
            'time' => $end - $start,
            'result' => $result['count'] ?? 'N/A'
        ];

        // Cache performance test
        $cache = Cache::getInstance();
        $start = microtime(true);
        $cache->set('test_key', 'test_value', 60);
        $cachedValue = $cache->get('test_key');
        $end = microtime(true);

        $benchmarks['cache_operation'] = [
            'time' => $end - $start,
            'result' => $cachedValue === 'test_value' ? 'SUCCESS' : 'FAILED'
        ];

        return $benchmarks;
    }
}

/**
 * Global test helper functions
 */
function run_tests(): array
{
    return TestFramework::getInstance()->runTests();
}

function test_report(): string
{
    return TestFramework::getInstance()->generateReport();
}

function performance_benchmark(): array
{
    return TestFramework::getInstance()->runPerformanceTests();
}

?>
