<?php

namespace App\Core\Testing;

/**
 * Automated Test Runner - APS Dream Home
 * PHPUnit-style testing without external dependencies
 */
class TestRunner
{
    private $tests = [];
    private $results = [
        'passed' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => []
    ];
    private $currentTest = null;
    
    public function __construct()
    {
        $this->loadTests();
    }
    
    /**
     * Load all test files
     */
    private function loadTests(): void
    {
        $testDir = APP_PATH . '/../tests/';
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        $testFiles = glob($testDir . '*Test.php');
        foreach ($testFiles as $file) {
            require_once $file;
            $className = basename($file, '.php');
            if (class_exists($className)) {
                $this->tests[] = new $className();
            }
        }
    }
    
    /**
     * Run all tests
     */
    public function runAll(): array
    {
        echo "🧠 APS Dream Home Test Suite\n";
        echo str_repeat("=", 50) . "\n\n";
        
        foreach ($this->tests as $test) {
            $this->runTestClass($test);
        }
        
        return $this->generateReport();
    }
    
    /**
     * Run a single test class
     */
    private function runTestClass($testClass): void
    {
        $className = get_class($testClass);
        echo "📦 Testing: {$className}\n";
        echo str_repeat("-", 40) . "\n";
        
        $methods = get_class_methods($testClass);
        
        foreach ($methods as $method) {
            if (strpos($method, 'test') === 0) {
                $this->runTestMethod($testClass, $method);
            }
        }
        
        echo "\n";
    }
    
    /**
     * Run a single test method
     */
    private function runTestMethod($testClass, string $method): void
    {
        $this->currentTest = $method;
        
        try {
            // Setup
            if (method_exists($testClass, 'setUp')) {
                $testClass->setUp();
            }
            
            // Run test
            $testClass->$method();
            
            // Tear down
            if (method_exists($testClass, 'tearDown')) {
                $testClass->tearDown();
            }
            
            $this->results['passed']++;
            echo "  ✅ {$method}\n";
            
        } catch (\AssertionError $e) {
            $this->results['failed']++;
            $this->results['errors'][] = [
                'test' => $method,
                'error' => $e->getMessage()
            ];
            echo "  ❌ {$method}: {$e->getMessage()}\n";
        } catch (\Exception $e) {
            $this->results['failed']++;
            $this->results['errors'][] = [
                'test' => $method,
                'error' => $e->getMessage()
            ];
            echo "  💥 {$method}: {$e->getMessage()}\n";
        }
    }
    
    /**
     * Generate final report
     */
    private function generateReport(): array
    {
        echo str_repeat("=", 50) . "\n";
        echo "📊 TEST RESULTS\n";
        echo str_repeat("=", 50) . "\n";
        echo "✅ Passed:  {$this->results['passed']}\n";
        echo "❌ Failed:  {$this->results['failed']}\n";
        echo "⏭️  Skipped: {$this->results['skipped']}\n";
        echo str_repeat("=", 50) . "\n";
        
        $total = $this->results['passed'] + $this->results['failed'] + $this->results['skipped'];
        $rate = $total > 0 ? round(($this->results['passed'] / $total) * 100, 2) : 0;
        
        echo "🎯 Success Rate: {$rate}%\n";
        
        return $this->results;
    }
    
    /**
     * Assert equals
     */
    public static function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new \AssertionError($message ?: "Expected: {$expected}, Got: {$actual}");
        }
    }
    
    /**
     * Assert true
     */
    public static function assertTrue($condition, string $message = ''): void
    {
        if (!$condition) {
            throw new \AssertionError($message ?: "Expected true, got false");
        }
    }
    
    /**
     * Assert false
     */
    public static function assertFalse($condition, string $message = ''): void
    {
        if ($condition) {
            throw new \AssertionError($message ?: "Expected false, got true");
        }
    }
    
    /**
     * Assert not null
     */
    public static function assertNotNull($value, string $message = ''): void
    {
        if ($value === null) {
            throw new \AssertionError($message ?: "Expected not null");
        }
    }
    
    /**
     * Assert null
     */
    public static function assertNull($value, string $message = ''): void
    {
        if ($value !== null) {
            throw new \AssertionError($message ?: "Expected null");
        }
    }
    
    /**
     * Assert contains
     */
    public static function assertContains(string $needle, string $haystack, string $message = ''): void
    {
        if (strpos($haystack, $needle) === false) {
            throw new \AssertionError($message ?: "'{$needle}' not found in string");
        }
    }
    
    /**
     * Assert array has key
     */
    public static function assertArrayHasKey(string $key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new \AssertionError($message ?: "Key '{$key}' not found in array");
        }
    }
}

/**
 * Base Test Class
 */
abstract class TestCase
{
    protected $app;
    
    public function setUp(): void
    {
        // Override in child classes
    }
    
    public function tearDown(): void
    {
        // Override in child classes
    }
    
    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        TestRunner::assertEquals($expected, $actual, $message);
    }
    
    protected function assertTrue($condition, string $message = ''): void
    {
        TestRunner::assertTrue($condition, $message);
    }
    
    protected function assertFalse($condition, string $message = ''): void
    {
        TestRunner::assertFalse($condition, $message);
    }
    
    protected function assertNotNull($value, string $message = ''): void
    {
        TestRunner::assertNotNull($value, $message);
    }
    
    protected function assertNull($value, string $message = ''): void
    {
        TestRunner::assertNull($value, $message);
    }
    
    protected function assertContains(string $needle, string $haystack, string $message = ''): void
    {
        TestRunner::assertContains($needle, $haystack, $message);
    }
    
    protected function assertArrayHasKey(string $key, array $array, string $message = ''): void
    {
        TestRunner::assertArrayHasKey($key, $array, $message);
    }
}
