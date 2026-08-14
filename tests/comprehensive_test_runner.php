<?php
/**
 * Comprehensive Test Runner - APS Dream Home
 * Tests all major components and reports issues
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

class ComprehensiveTestRunner
{
    private $database;
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct()
    {
        $this->database = Database::getInstance();
    }
    
    public function runAllTests(): array
    {
        echo "\n";
        echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
        echo "â•‘     APS DREAM HOME - COMPREHENSIVE TEST SUITE              â•‘\n";
        echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n\n";
        
        $startTime = microtime(true);
        
        // Database Tests
        $this->testDatabaseConnection();
        $this->testTableIntegrity();
        $this->testForeignKeyConstraints();
        
        // Service Tests
        $this->testServicesExist();
        $this->testServiceMethods();
        
        // API Tests
        $this->testAPIRoutes();
        $this->testMiddleware();
        
        // Security Tests
        $this->testSecurityConfiguration();
        $this->testFilePermissions();
        
        // Performance Tests
        $this->testQueryPerformance();
        $this->testCacheConfiguration();
        
        // Integration Tests
        $this->testPaymentGateways();
        $this->testNotificationChannels();
        
        $endTime = microtime(true);
        $totalTime = round($endTime - $startTime, 2);
        
        $this->printSummary($totalTime);
        
        return [
            'results' => $this->results,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'total_time' => $totalTime
        ];
    }
    
    /**
     * Test database connection
     */
    private function testDatabaseConnection(): void
    {
        echo "ðŸ—„ï¸�  Testing Database Connection...\n";
        
        try {
            $pdo = $this->database->getConnection();
            $stmt = $pdo->query("SELECT 1");
            
            if ($stmt) {
                $this->pass('Database Connection');
            } else {
                $this->fail('Database Connection', 'Cannot execute query');
            }
            
            // Test MySQL version
            $version = $pdo->query("SELECT VERSION()")->fetchColumn();
            echo "   âœ“ MySQL Version: $version\n";
            
        } catch (\Exception $e) {
            $this->fail('Database Connection', $e->getMessage());
        }
    }
    
    /**
     * Test table integrity
     */
    private function testTableIntegrity(): void
    {
        echo "\nðŸ“Š Testing Table Integrity...\n";
        
        $criticalTables = [
            'users', 'properties', 'leads', 'bookings',
            'payment_transactions', 'chat_conversations',
            'loyalty_points', 'files', 'scheduled_tasks',
            'notifications', 'analytics_events'
        ];
        
        $missingTables = [];
        $corruptTables = [];
        
        foreach ($criticalTables as $table) {
            try {
                $stmt = $this->database->query("SHOW TABLES LIKE " . $this->database->getConnection()->quote($table));
                
                if (!$stmt->fetch()) {
                    $missingTables[] = $table;
                    continue;
                }
                
                // Check if table has data
                $countStmt = $this->database->prepare("SELECT COUNT(*) FROM $table");
                $countStmt->execute();
                $count = $countStmt->fetchColumn();
                
                echo "   âœ“ Table '$table' exists ($count rows)\n";
                
            } catch (\Exception $e) {
                $corruptTables[] = "$table: " . $e->getMessage();
            }
        }
        
        if (empty($missingTables) && empty($corruptTables)) {
            $this->pass('Table Integrity');
        } else {
            if (!empty($missingTables)) {
                $this->fail('Table Integrity', 'Missing tables: ' . implode(', ', $missingTables));
            }
            if (!empty($corruptTables)) {
                $this->fail('Table Integrity', 'Corrupt tables: ' . implode(', ', $corruptTables));
            }
        }
    }
    
    /**
     * Test foreign key constraints
     */
    private function testForeignKeyConstraints(): void
    {
        echo "\nðŸ”— Testing Foreign Key Constraints...\n";
        
        $constraints = [
            ['table' => 'properties', 'column' => 'created_by', 'references' => 'users'],
            ['table' => 'bookings', 'column' => 'property_id', 'references' => 'properties'],
            ['table' => 'chat_messages', 'column' => 'session_id', 'references' => 'chat_sessions'],
            ['table' => 'loyalty_transactions', 'column' => 'user_id', 'references' => 'users']
        ];
        
        $brokenConstraints = [];
        
        foreach ($constraints as $constraint) {
            try {
                // Check if orphaned records exist
                $sql = "SELECT COUNT(*) FROM {$constraint['table']} t 
                    LEFT JOIN {$constraint['references']} r ON t.{$constraint['column']} = r.id 
                    WHERE t.{$constraint['column']} IS NOT NULL AND r.id IS NULL";
                
                $count = $this->database->query($sql)->fetchColumn();
                
                if ($count > 0) {
                    $brokenConstraints[] = "{$constraint['table']}.{$constraint['column']} has $count orphaned records";
                } else {
                    echo "   âœ“ FK: {$constraint['table']}.{$constraint['column']} â†’ {$constraint['references']}\n";
                }
            } catch (\Exception $e) {
                $brokenConstraints[] = "{$constraint['table']}: " . $e->getMessage();
            }
        }
        
        if (empty($brokenConstraints)) {
            $this->pass('Foreign Key Constraints');
        } else {
            $this->warning('Foreign Key Constraints', implode('; ', $brokenConstraints));
        }
    }
    
    /**
     * Test services exist
     */
    private function testServicesExist(): void
    {
        echo "\nâš™ï¸�  Testing Services...\n";
        
        $services = [
            'App\Services\Payment\PaymentGatewayService',
            'App\Services\Communication\ChatService',
            'App\Services\Cache\CacheService',
            'App\Services\Queue\QueueService',
            'App\Services\Map\MapService',
            'App\Services\I18n\LocalizationService',
            'App\Services\NotificationService',
            'App\Services\Analytics\AdvancedAnalyticsService',
            'App\Services\Scheduler\TaskSchedulerService',
            'App\Services\File\FileManagerService',
            'App\Services\Loyalty\LoyaltyRewardsService',
            'App\Services\Auth\JWTAuthService',
            'App\Services\Search\AdvancedSearchService',
            'App\Services\Finance\EMICalculatorService'
        ];
        
        $missingServices = [];
        
        foreach ($services as $service) {
            if (class_exists($service)) {
                echo "   âœ“ $service\n";
            } else {
                $missingServices[] = $service;
                echo "   âœ— $service NOT FOUND\n";
            }
        }
        
        if (empty($missingServices)) {
            $this->pass('Services Existence');
        } else {
            $this->fail('Services Existence', 'Missing: ' . implode(', ', $missingServices));
        }
    }
    
    /**
     * Test service methods
     */
    private function testServiceMethods(): void
    {
        echo "\nðŸ”§ Testing Service Methods...\n";
        
        $testCases = [
            ['service' => 'App\Services\Cache\CacheService', 'method' => 'get'],
            ['service' => 'App\Services\Cache\CacheService', 'method' => 'set'],
            ['service' => 'App\Services\Loyalty\LoyaltyRewardsService', 'method' => 'getOrCreateAccount'],
            ['service' => 'App\Services\NotificationService', 'method' => 'notify']
        ];
        
        $failedMethods = [];
        
        foreach ($testCases as $test) {
            try {
                if (class_exists($test['service'])) {
                    $reflection = new ReflectionClass($test['service']);
                    
                    if ($reflection->hasMethod($test['method'])) {
                        echo "   âœ“ {$test['service']}::{$test['method']}\n";
                    } else {
                        $failedMethods[] = "{$test['service']}::{$test['method']} missing";
                        echo "   âœ— {$test['service']}::{$test['method']} NOT FOUND\n";
                    }
                }
            } catch (\Exception $e) {
                $failedMethods[] = "{$test['service']}: " . $e->getMessage();
            }
        }
        
        if (empty($failedMethods)) {
            $this->pass('Service Methods');
        } else {
            $this->warning('Service Methods', implode('; ', $failedMethods));
        }
    }
    
    /**
     * Test API routes
     */
    private function testAPIRoutes(): void
    {
        echo "\nðŸŒ� Testing API Routes...\n";
        
        $apiFile = __DIR__ . '/../routes/api.php';
        
        if (!file_exists($apiFile)) {
            $this->fail('API Routes', 'api.php not found');
            return;
        }
        
        $content = file_get_contents($apiFile);
        
        // Count API routes
        $routeCount = substr_count($content, '$router->');
        $mobileRoutes = substr_count($content, '/api/v');
        
        echo "   âœ“ Total routes defined: $routeCount\n";
        echo "   âœ“ Mobile API routes: $mobileRoutes\n";
        
        // Check for critical routes
        $criticalRoutes = [
            '/api/v2/mobile/auth/login',
            '/api/v1/search/properties',
            '/api/v1/finance/emi-calculate'
        ];
        
        $missingRoutes = [];
        foreach ($criticalRoutes as $route) {
            if (strpos($content, $route) === false) {
                $missingRoutes[] = $route;
            }
        }
        
        if (empty($missingRoutes)) {
            $this->pass('API Routes');
        } else {
            $this->fail('API Routes', 'Missing: ' . implode(', ', $missingRoutes));
        }
    }
    
    /**
     * Test middleware
     */
    private function testMiddleware(): void
    {
        echo "\nðŸ›¡ï¸�  Testing Middleware...\n";
        
        $middlewareDir = __DIR__ . '/../app/Http/Middleware';
        
        if (!is_dir($middlewareDir)) {
            $this->fail('Middleware', 'Middleware directory not found');
            return;
        }
        
        $middlewareFiles = glob($middlewareDir . '/*.php');
        $middlewareCount = count($middlewareFiles);
        
        echo "   âœ“ Middleware files found: $middlewareCount\n";
        
        // Check for critical middleware
        $criticalMiddleware = [
            'ApiAuthMiddleware',
            'RateLimitMiddleware',
            'Cors'
        ];
        
        $found = [];
        foreach ($middlewareFiles as $file) {
            $filename = basename($file, '.php');
            $found[] = $filename;
        }
        
        $missing = array_diff($criticalMiddleware, $found);
        
        if (empty($missing)) {
            $this->pass('Middleware');
        } else {
            $this->warning('Middleware', 'Missing: ' . implode(', ', $missing));
        }
    }
    
    /**
     * Test security configuration
     */
    private function testSecurityConfiguration(): void
    {
        echo "\nðŸ”’ Testing Security Configuration...\n";
        
        $issues = [];
        
        // Check for .env file
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            $issues[] = '.env file not found';
        } else {
            echo "   âœ“ .env file exists\n";
        }
        
        // Check for JWT secret
        $jwtFile = __DIR__ . '/../app/Services/Auth/JWTAuthService.php';
        if (file_exists($jwtFile)) {
            $content = file_get_contents($jwtFile);
            if (strpos($content, 'aps_dream_home_secret_key_2026') !== false) {
                echo "   âœ“ JWT Service configured\n";
            }
        }
        
        // Check for CSRF protection
        $csrfFiles = glob(__DIR__ . '/../app/Views/**/*.php');
        $csrfCount = 0;
        foreach ($csrfFiles as $file) {
            if (strpos(file_get_contents($file), 'csrf_token') !== false) {
                $csrfCount++;
            }
        }
        echo "   âœ“ CSRF tokens in $csrfCount view files\n";
        
        if (empty($issues)) {
            $this->pass('Security Configuration');
        } else {
            $this->warning('Security Configuration', implode('; ', $issues));
        }
    }
    
    /**
     * Test file permissions
     */
    private function testFilePermissions(): void
    {
        echo "\nðŸ“� Testing File Permissions...\n";
        
        $paths = [
            'storage/logs' => 0755,
            'storage/cache' => 0755,
            'storage/uploads' => 0755,
            'public/uploads' => 0755
        ];
        
        $basePath = __DIR__ . '/../';
        $permissionIssues = [];
        
        foreach ($paths as $path => $required) {
            $fullPath = $basePath . $path;
            
            if (!is_dir($fullPath)) {
                // Try to create
                if (@mkdir($fullPath, $required, true)) {
                    echo "   âœ“ Created directory: $path\n";
                } else {
                    $permissionIssues[] = "$path missing and cannot create";
                    echo "   âœ— Cannot create: $path\n";
                }
                continue;
            }
            
            // Check if writable
            if (is_writable($fullPath)) {
                echo "   âœ“ $path is writable\n";
            } else {
                $permissionIssues[] = "$path not writable";
                echo "   âœ— $path not writable\n";
            }
        }
        
        if (empty($permissionIssues)) {
            $this->pass('File Permissions');
        } else {
            $this->warning('File Permissions', implode('; ', $permissionIssues));
        }
    }
    
    /**
     * Test query performance
     */
    private function testQueryPerformance(): void
    {
        echo "\nâš¡ Testing Query Performance...\n";
        
        $slowQueries = [];
        
        // Test common queries
        $queries = [
            'Property search' => "SELECT * FROM properties WHERE status = 'available' LIMIT 20",
            'User count' => "SELECT COUNT(*) FROM users",
            'Recent bookings' => "SELECT * FROM bookings ORDER BY created_at DESC LIMIT 10",
            'Loyalty points' => "SELECT * FROM loyalty_points ORDER BY points DESC LIMIT 10"
        ];
        
        foreach ($queries as $name => $sql) {
            $start = microtime(true);
            
            try {
                $stmt = $this->database->query($sql);
                $stmt->fetchAll();
                
                $time = round((microtime(true) - $start) * 1000, 2);
                
                if ($time > 100) {
                    $slowQueries[] = "$name: {$time}ms";
                    echo "   âš  $name: {$time}ms (slow)\n";
                } else {
                    echo "   âœ“ $name: {$time}ms\n";
                }
            } catch (\Exception $e) {
                echo "   âœ— $name: ERROR - " . $e->getMessage() . "\n";
            }
        }
        
        if (empty($slowQueries)) {
            $this->pass('Query Performance');
        } else {
            $this->warning('Query Performance', 'Slow queries: ' . implode(', ', $slowQueries));
        }
    }
    
    /**
     * Test cache configuration
     */
    private function testCacheConfiguration(): void
    {
        echo "\nðŸ’¾ Testing Cache Configuration...\n";
        
        try {
            $cacheService = new \App\Services\Cache\CacheService('file');
            
            // Test set
            $testKey = 'test_' . time();
            $cacheService->set($testKey, 'test_value', 60);
            
            // Test get
            $value = $cacheService->get($testKey);
            
            if ($value === 'test_value') {
                echo "   âœ“ Cache set/get working\n";
                $this->pass('Cache Configuration');
            } else {
                $this->fail('Cache Configuration', 'Cache get returned wrong value');
            }
            
            // Cleanup
            $cacheService->delete($testKey);
            
        } catch (\Exception $e) {
            $this->fail('Cache Configuration', $e->getMessage());
        }
    }
    
    /**
     * Test payment gateways
     */
    private function testPaymentGateways(): void
    {
        echo "\nðŸ’³ Testing Payment Gateways...\n";
        
        try {
            $paymentService = new \App\Services\Payment\PaymentGatewayService('razorpay');
            
            // Test order creation
            $result = $paymentService->createOrder(1000, [
                'user_id' => 1,
                'entity_type' => 'test'
            ]);
            
            if ($result['success']) {
                echo "   âœ“ Razorpay order creation working\n";
                echo "   âœ“ Order ID generated: " . $result['order_id'] . "\n";
                $this->pass('Payment Gateway - Razorpay');
            } else {
                $this->warning('Payment Gateway', 'Order creation failed: ' . $result['error']);
            }
            
        } catch (\Exception $e) {
            $this->warning('Payment Gateway', $e->getMessage());
        }
    }
    
    /**
     * Test notification channels
     */
    private function testNotificationChannels(): void
    {
        echo "\nðŸ“¢ Testing Notification Channels...\n";
        
        try {
            $db = \App\Core\Database\Database::getInstance();
            $notificationService = new \App\Services\NotificationService($db);
            
            // Test template retrieval
            $templates = ['booking_confirmed', 'payment_received', 'site_visit_reminder'];
            $found = 0;
            
            foreach ($templates as $template) {
                // Check if template exists in database
                $stmt = $this->database->prepare("SELECT 1 FROM notification_templates WHERE template_code = ?");
                $stmt->execute([$template]);
                if ($stmt->fetch()) {
                    $found++;
                    echo "   âœ“ Template: $template\n";
                } else {
                    echo "   âœ— Template missing: $template\n";
                }
            }
            
            if ($found === count($templates)) {
                $this->pass('Notification Channels');
            } else {
                $this->warning('Notification Channels', 'Some templates missing');
            }
            
        } catch (\Exception $e) {
            $this->warning('Notification Channels', $e->getMessage());
        }
    }
    
    /**
     * Record pass
     */
    private function pass(string $test): void
    {
        $this->results[] = ['test' => $test, 'status' => 'PASS'];
        echo "   âœ… PASSED: $test\n";
    }
    
    /**
     * Record fail
     */
    private function fail(string $test, string $message): void
    {
        $this->results[] = ['test' => $test, 'status' => 'FAIL', 'message' => $message];
        $this->errors[] = "$test: $message";
        echo "   â�Œ FAILED: $test - $message\n";
    }
    
    /**
     * Record warning
     */
    private function warning(string $test, string $message): void
    {
        $this->results[] = ['test' => $test, 'status' => 'WARNING', 'message' => $message];
        $this->warnings[] = "$test: $message";
        echo "   âš ï¸�  WARNING: $test - $message\n";
    }
    
    /**
     * Print summary
     */
    private function printSummary(float $totalTime): void
    {
        echo "\n";
        echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—\n";
        echo "â•‘                    TEST SUMMARY                            â•‘\n";
        echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�\n";
        echo "\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = count($this->errors);
        $warnings = count($this->warnings);
        $total = count($this->results);
        
        echo "Total Tests: $total\n";
        echo "âœ… Passed: $passed\n";
        echo "â�Œ Failed: $failed\n";
        echo "âš ï¸�  Warnings: $warnings\n";
        echo "â�±ï¸�  Total Time: {$totalTime}s\n";
        echo "\n";
        
        if ($failed > 0) {
            echo "â�Œ CRITICAL ISSUES FOUND:\n";
            foreach ($this->errors as $error) {
                echo "   â€¢ $error\n";
            }
            echo "\n";
        }
        
        if ($warnings > 0) {
            echo "âš ï¸�  WARNINGS:\n";
            foreach ($this->warnings as $warning) {
                echo "   â€¢ $warning\n";
            }
            echo "\n";
        }
        
        if ($failed === 0 && $warnings === 0) {
            echo "ðŸŽ‰ ALL TESTS PASSED! System is healthy.\n";
        } elseif ($failed === 0) {
            echo "âœ… System functional with minor warnings.\n";
        } else {
            echo "âš ï¸�  System has critical issues that need fixing.\n";
        }
        
        echo "\n";
    }
}

// Run tests
$tester = new ComprehensiveTestRunner();
$results = $tester->runAllTests();

// Save results to file
$reportFile = __DIR__ . '/test_report_' . date('Y-m-d_H-i-s') . '.json';
file_put_contents($reportFile, json_encode($results, JSON_PRETTY_PRINT));
echo "ðŸ“„ Report saved to: $reportFile\n";?>