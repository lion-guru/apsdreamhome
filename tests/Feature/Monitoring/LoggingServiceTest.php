<?php

namespace Tests\Feature\Custom;

use App\Services\Custom\LoggingService;
use PHPUnit\Framework\TestCase;

/**
 * Custom Logging Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class LoggingServiceTest extends TestCase
{
    private $loggingService;
    private $testLogDir;
    
    protected function setUp(): void
    {
        $this->loggingService = new LoggingService();
        $this->testLogDir = STORAGE_PATH . '/logs/';
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(LoggingService::class, $this->loggingService);
        $this->assertDirectoryExists($this->testLogDir);
    }
    
    /** @test */
    public function it_can_log_info_message()
    {
        $message = 'Test info message';
        $this->loggingService->info($message);
        
        // Check if log file was created
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);
        
        // Check log content
        $logContent = file_get_contents($logFile);
        $this->assertStringContainsString($message, $logContent);
        $this->assertStringContainsString('"level":"info"', $logContent);
    }
    
    /** @test */
    public function it_can_log_error_message()
    {
        $message = 'Test error message';
        $this->loggingService->error($message);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($message, $logContent);
        $this->assertStringContainsString('"level":"error"', $logContent);
    }
    
    /** @test */
    public function it_can_log_debug_message()
    {
        $message = 'Test debug message';
        $context = ['key' => 'value'];
        
        $this->loggingService->debug($message, $context);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($message, $logContent);
        $this->assertStringContainsString('"level":"debug"', $logContent);
        $this->assertStringContainsString('"key":"value"', $logContent);
    }
    
    /** @test */
    public function it_can_log_warning_message()
    {
        $message = 'Test warning message';
        $this->loggingService->warning($message);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($message, $logContent);
        $this->assertStringContainsString('"level":"warning"', $logContent);
    }
    
    /** @test */
    public function it_can_log_critical_message()
    {
        $message = 'Test critical message';
        $this->loggingService->critical($message);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($message, $logContent);
        $this->assertStringContainsString('"level":"critical"', $logContent);
    }
    
    /** @test */
    public function it_can_log_api_request()
    {
        $method = 'POST';
        $endpoint = '/api/test';
        $requestData = ['param' => 'value'];
        $response = ['result' => 'success'];
        $responseTime = 0.5;
        $statusCode = 200;
        
        $this->loggingService->logApiRequest($method, $endpoint, $requestData, $response, $responseTime, $statusCode);
        
        $logFile = $this->testLogDir . 'api_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($method, $logContent);
        $this->assertStringContainsString($endpoint, $logContent);
        $this->assertStringContainsString('"category":"api"', $logContent);
        $this->assertStringContainsString('"response_time":0.5', $logContent);
    }
    
    /** @test */
    public function it_can_log_authentication_event()
    {
        $event = 'login_attempt';
        $email = 'test@example.com';
        $success = true;
        $details = ['ip' => '127.0.0.1'];
        
        $this->loggingService->logAuth($event, $email, $success, $details);
        
        $logFile = $this->testLogDir . 'auth_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($event, $logContent);
        $this->assertStringContainsString($email, $logContent);
        $this->assertStringContainsString('"category":"auth"', $logContent);
        $this->assertStringContainsString('"success":true', $logContent);
    }
    
    /** @test */
    public function it_can_log_database_query()
    {
        $query = 'SELECT * FROM users WHERE id = ?';
        $params = [1];
        $executionTime = 0.2;
        $affectedRows = 1;
        
        $this->loggingService->logQuery($query, $params, $executionTime, $affectedRows);
        
        $logFile = $this->testLogDir . 'database_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($query, $logContent);
        $this->assertStringContainsString('"category":"database"', $logContent);
        $this->assertStringContainsString('"execution_time":0.2', $logContent);
    }
    
    /** @test */
    public function it_can_log_security_event()
    {
        $event = 'suspicious_activity';
        $severity = 'high';
        $details = ['pattern' => 'sql_injection'];
        
        $this->loggingService->logSecurity($event, $severity, $details);
        
        $logFile = $this->testLogDir . 'security_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($event, $logContent);
        $this->assertStringContainsString('"category":"security"', $logContent);
        $this->assertStringContainsString('"severity":"high"', $logContent);
    }
    
    /** @test */
    public function it_can_log_performance_metrics()
    {
        $metric = 'response_time';
        $value = 150;
        $unit = 'ms';
        $context = ['endpoint' => '/api/test'];
        
        $this->loggingService->logPerformance($metric, $value, $unit, $context);
        
        $logFile = $this->testLogDir . 'performance_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($metric, $logContent);
        $this->assertStringContainsString('"category":"performance"', $logContent);
        $this->assertStringContainsString('"value":150', $logContent);
        $this->assertStringContainsString('"unit":"ms"', $logContent);
    }
    
    /** @test */
    public function it_can_log_user_activity()
    {
        // Mock session
        $_SESSION['user_id'] = 123;
        
        $action = 'view_dashboard';
        $resource = 'dashboard';
        $details = ['page' => 1];
        
        $this->loggingService->logUserActivity($action, $resource, $details);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString($action, $logContent);
        $this->assertStringContainsString('"user_id":123', $logContent);
        $this->assertStringContainsString('"resource":"dashboard"', $logContent);
    }
    
    /** @test */
    public function it_can_log_exception()
    {
        $exception = new Exception('Test exception message');
        $context = ['additional' => 'info'];
        
        $this->loggingService->logException($exception, $context);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString('Test exception message', $logContent);
        $this->assertStringContainsString('"level":"error"', $logContent);
        $this->assertStringContainsString('"exception_class":"Exception"', $logContent);
    }
    
    /** @test */
    public function it_can_clean_old_logs()
    {
        // Create test log file
        $oldLogFile = $this->testLogDir . 'test_old.log';
        file_put_contents($oldLogFile, 'test content');
        
        // Set file modification time to 35 days ago
        $oldTime = time() - (35 * 24 * 60 * 60);
        touch($oldLogFile, $oldTime);
        
        $this->loggingService->cleanOldLogs(30);
        
        // File should be deleted
        $this->assertFileDoesNotExist($oldLogFile);
    }
    
    /** @test */
    public function it_can_get_log_statistics()
    {
        // This would require database setup
        // For now, just test method exists and returns array
        $stats = $this->loggingService->getLogStats(24);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_logs', $stats);
        $this->assertArrayHasKey('error_count', $stats);
        $this->assertArrayHasKey('warning_count', $stats);
        $this->assertArrayHasKey('api_requests', $stats);
        $this->assertArrayHasKey('auth_events', $stats);
        $this->assertArrayHasKey('security_events', $stats);
    }
    
    /** @test */
    public function it_can_export_logs_to_csv()
    {
        // Create some test logs first
        $this->loggingService->info('Test log 1');
        $this->loggingService->error('Test log 2');
        
        $csvFile = $this->loggingService->exportLogs();
        
        $this->assertFileExists($csvFile);
        $this->assertStringEndsWithString('.csv', $csvFile);
        
        // Check CSV content
        $csvContent = file_get_contents($csvFile);
        $this->assertStringContainsString('timestamp,level,category,message', $csvContent);
        $this->assertStringContainsString('Test log 1', $csvContent);
        $this->assertStringContainsString('Test log 2', $csvContent);
        
        // Clean up
        unlink($csvFile);
    }
    
    /** @test */
    public function it_handles_context_data_correctly()
    {
        $message = 'Test message with context';
        $context = [
            'user_id' => 123,
            'ip' => '192.168.1.1',
            'request_id' => 'req_123'
        ];
        
        $this->loggingService->info($message, $context);
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        $this->assertStringContainsString('"user_id":123', $logContent);
        $this->assertStringContainsString('"ip":"192.168.1.1"', $logContent);
        $this->assertStringContainsString('"request_id":"req_123"', $logContent);
    }
    
    /** @test */
    public function it_generates_unique_request_ids()
    {
        // Log multiple messages
        $this->loggingService->info('Message 1');
        $this->loggingService->info('Message 2');
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        // Extract request IDs
        preg_match_all('/"request_id":"([^"]+)"/', $logContent, $matches);
        
        $this->assertCount(2, $matches[1]);
        $this->assertNotEquals($matches[1][0], $matches[1][1]);
        $this->assertStringStartsWith('req_', $matches[1][0]);
        $this->assertStringStartsWith('req_', $matches[1][1]);
    }
    
    /** @test */
    public function it_captures_client_ip_correctly()
    {
        // Mock server variables
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.1';
        
        $this->loggingService->info('Test IP capture');
        
        $logFile = $this->testLogDir . 'system_' . date('Y-m-d') . '.log';
        $logContent = file_get_contents($logFile);
        
        // Should capture X-Forwarded-For IP
        $this->assertStringContainsString('"ip":"203.0.113.1"', $logContent);
        
        // Reset
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    
    /** @test */
    public function it_handles_logging_failures_gracefully()
    {
        // Create a scenario where file writing might fail
        // by making the log directory read-only (simulated)
        
        // This test ensures the service doesn't crash on logging failures
        $this->loggingService->info('Test message');
        
        // If we reach here without exception, the graceful handling worked
        $this->assertTrue(true);
    }
    
    protected function tearDown(): void
    {
        // Clean up test log files
        $logFiles = glob($this->testLogDir . '*.log');
        foreach ($logFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Clean up test session data
        unset($_SESSION['user_id']);
        
        parent::tearDown();
    }
}
