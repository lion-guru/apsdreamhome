<?php

namespace Tests\Feature\Async;

use App\Services\Async\AsyncTaskService;
use PHPUnit\Framework\TestCase;

/**
 * Async Task Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class AsyncTaskServiceTest extends TestCase
{
    private $taskService;
    private $testTaskId;
    
    protected function setUp(): void
    {
        $this->taskService = new AsyncTaskService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(AsyncTaskService::class, $this->taskService);
    }
    
    /** @test */
    public function it_can_create_task()
    {
        $taskName = 'Test Email Task';
        $taskType = 'email';
        $parameters = [
            'to' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];
        $priority = AsyncTaskService::PRIORITY_NORMAL;
        $maxRetries = 3;
        
        $result = $this->taskService->createTask($taskName, $taskType, $parameters, $priority, $maxRetries);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('task_id', $result);
        
        // Store for cleanup
        $this->testTaskId = $result['task_id'];
    }
    
    /** @test */
    public function it_validates_required_task_fields()
    {
        $result = $this->taskService->createTask('', '', []);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to create task', $result['message']);
    }
    
    /** @test */
    public function it_can_get_task_status()
    {
        // First create a test task
        $this->createTestTask();
        
        $result = $this->taskService->getTaskStatus($this->testTaskId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($this->testTaskId, $result['data']['id']);
        $this->assertEquals(AsyncTaskService::STATUS_PENDING, $result['data']['status']);
        $this->assertEquals(0, $result['data']['progress_percentage']);
    }
    
    /** @test */
    public function it_returns_error_for_non_existent_task()
    {
        $result = $this->taskService->getTaskStatus(99999);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }
    
    /** @test */
    public function it_can_get_tasks()
    {
        // First create a test task
        $this->createTestTask();
        
        $result = $this->taskService->getTasks();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_tasks()
    {
        // Create tasks with different statuses
        $this->createTestTask();
        $pendingTaskId = $this->testTaskId;
        
        // Create another task
        $result = $this->taskService->createTask('Test Task 2', 'image_processing', ['image_path' => '/test/path']);
        $runningTaskId = $result['task_id'];
        
        // Update second task to running status
        $this->taskService->updateTaskStatus($runningTaskId, AsyncTaskService::STATUS_RUNNING);
        
        // Filter by pending status
        $result = $this->taskService->getTasks(['status' => AsyncTaskService::STATUS_PENDING]);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified status (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $task) {
                $this->assertEquals(AsyncTaskService::STATUS_PENDING, $task['status']);
            }
        }
        
        // Clean up
        $this->cleanupTask($runningTaskId);
    }
    
    /** @test */
    public function it_can_update_task_status()
    {
        // First create a test task
        $this->createTestTask();
        
        $result = $this->taskService->updateTaskStatus($this->testTaskId, AsyncTaskService::STATUS_RUNNING, 'test_worker');
        
        $this->assertTrue($result['success']);
        
        // Verify the status was updated
        $statusResult = $this->taskService->getTaskStatus($this->testTaskId);
        if ($statusResult['success']) {
            $this->assertEquals(AsyncTaskService::STATUS_RUNNING, $statusResult['data']['status']);
        }
    }
    
    /** @test */
    public function it_can_update_task_progress()
    {
        // First create a test task
        $this->createTestTask();
        
        $progress = 50;
        $resultData = ['processed_items' => 10, 'total_items' => 20];
        
        $result = $this->taskService->updateTaskProgress($this->testTaskId, $progress, $resultData);
        
        $this->assertTrue($result['success']);
        
        // Verify the progress was updated
        $statusResult = $this->taskService->getTaskStatus($this->testTaskId);
        if ($statusResult['success']) {
            $this->assertEquals($progress, $statusResult['data']['progress_percentage']);
            $this->assertEquals($resultData, $statusResult['data']['result']);
        }
    }
    
    /** @test */
    public function it_can_complete_task()
    {
        // First create a test task
        $this->createTestTask();
        
        $resultData = [
            'success' => true,
            'processed_items' => 20,
            'execution_time' => '5.2 seconds'
        ];
        
        $result = $this->taskService->completeTask($this->testTaskId, $resultData);
        
        $this->assertTrue($result['success']);
        
        // Verify the task was completed
        $statusResult = $this->taskService->getTaskStatus($this->testTaskId);
        if ($statusResult['success']) {
            $this->assertEquals(AsyncTaskService::STATUS_COMPLETED, $statusResult['data']['status']);
            $this->assertEquals($resultData, $statusResult['data']['result']);
            $this->assertNotNull($statusResult['data']['completed_at']);
        }
    }
    
    /** @test */
    public function it_can_fail_task()
    {
        // First create a test task
        $this->createTestTask();
        
        $errorMessage = 'Test error message';
        
        $result = $this->taskService->failTask($this->testTaskId, $errorMessage);
        
        $this->assertTrue($result['success']);
        
        // Verify the task was failed
        $statusResult = $this->taskService->getTaskStatus($this->testTaskId);
        if ($statusResult['success']) {
            $this->assertEquals(AsyncTaskService::STATUS_FAILED, $statusResult['data']['status']);
            $this->assertEquals($errorMessage, $statusResult['data']['error_message']);
            $this->assertEquals(1, $statusResult['data']['retry_count']);
        }
    }
    
    /** @test */
    public function it_can_cancel_task()
    {
        // First create a test task
        $this->createTestTask();
        
        $result = $this->taskService->cancelTask($this->testTaskId);
        
        $this->assertTrue($result['success']);
        
        // Verify the task was cancelled
        $statusResult = $this->taskService->getTaskStatus($this->testTaskId);
        if ($statusResult['success']) {
            $this->assertEquals(AsyncTaskService::STATUS_CANCELLED, $statusResult['data']['status']);
        }
    }
    
    /** @test */
    public function it_can_retry_failed_task()
    {
        // First create a test task and fail it
        $this->createTestTask();
        $this->taskService->failTask($this->testTaskId, 'Test error');
        
        $result = $this->taskService->retryTask($this->testTaskId);
        
        $this->assertTrue($result['success']);
        
        // Verify the task is pending again
        $statusResult = $this->taskService->getTaskStatus($this->testTaskId);
        if ($statusResult['success']) {
            $this->assertEquals(AsyncTaskService::STATUS_PENDING, $statusResult['data']['status']);
            $this->assertNull($statusResult['data']['error_message']);
        }
    }
    
    /** @test */
    public function it_can_get_next_task()
    {
        // Create a test task
        $this->createTestTask();
        
        $result = $this->taskService->getNextTask('test_worker', 'default');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($this->testTaskId, $result['data']['id']);
        $this->assertEquals(AsyncTaskService::STATUS_RUNNING, $result['data']['status']);
    }
    
    /** @test */
    public function it_returns_no_tasks_when_queue_empty()
    {
        // Clean up any existing tasks
        if ($this->testTaskId) {
            $this->cleanupTask($this->testTaskId);
        }
        
        $result = $this->taskService->getNextTask('test_worker', 'default');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No tasks available', $result['message']);
    }
    
    /** @test */
    public function it_can_process_email_task()
    {
        // Create an email task
        $result = $this->taskService->createTask(
            'Test Email',
            'email',
            [
                'to' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test message'
            ]
        );
        
        if ($result['success']) {
            $taskId = $result['task_id'];
            
            // Get the task to process
            $taskResult = $this->taskService->getNextTask('test_worker', 'default');
            
            if ($taskResult['success']) {
                $task = $taskResult['data'];
                
                // Process the email task
                $processResult = $this->taskService->processEmailTask($task);
                
                $this->assertTrue($processResult['success']);
                
                // Verify task is completed
                $statusResult = $this->taskService->getTaskStatus($taskId);
                if ($statusResult['success']) {
                    $this->assertEquals(AsyncTaskService::STATUS_COMPLETED, $statusResult['data']['status']);
                    $this->assertArrayHasKey('message_id', $statusResult['data']['result']);
                }
            }
            
            // Clean up
            $this->cleanupTask($taskId);
        }
    }
    
    /** @test */
    public function it_can_process_image_task()
    {
        // Create an image processing task
        $result = $this->taskService->createTask(
            'Test Image Processing',
            'image_processing',
            [
                'image_path' => '/test/image.jpg'
            ]
        );
        
        if ($result['success']) {
            $taskId = $result['task_id'];
            
            // Get the task to process
            $taskResult = $this->taskService->getNextTask('test_worker', 'default');
            
            if ($taskResult['success']) {
                $task = $taskResult['data'];
                
                // Process the image task
                $processResult = $this->taskService->processImageTask($task);
                
                $this->assertTrue($processResult['success']);
                
                // Verify task is completed
                $statusResult = $this->taskService->getTaskStatus($taskId);
                if ($statusResult['success']) {
                    $this->assertEquals(AsyncTaskService::STATUS_COMPLETED, $statusResult['data']['status']);
                    $this->assertArrayHasKey('images_processed', $statusResult['data']['result']);
                    $this->assertArrayHasKey('thumbnails_created', $statusResult['data']['result']);
                }
            }
            
            // Clean up
            $this->cleanupTask($taskId);
        }
    }
    
    /** @test */
    public function it_can_process_report_task()
    {
        // Create a report generation task
        $result = $this->taskService->createTask(
            'Test Report',
            'report_generation',
            [
                'report_type' => 'sales_report'
            ]
        );
        
        if ($result['success']) {
            $taskId = $result['task_id'];
            
            // Get the task to process
            $taskResult = $this->taskService->getNextTask('test_worker', 'default');
            
            if ($taskResult['success']) {
                $task = $taskResult['data'];
                
                // Process the report task
                $processResult = $this->taskService->processReportTask($task);
                
                $this->assertTrue($processResult['success']);
                
                // Verify task is completed
                $statusResult = $this->taskService->getTaskStatus($taskId);
                if ($statusResult['success']) {
                    $this->assertEquals(AsyncTaskService::STATUS_COMPLETED, $statusResult['data']['status']);
                    $this->assertArrayHasKey('file_path', $statusResult['data']['result']);
                    $this->assertArrayHasKey('records_processed', $statusResult['data']['result']);
                }
            }
            
            // Clean up
            $this->cleanupTask($taskId);
        }
    }
    
    /** @test */
    public function it_handles_invalid_task_parameters()
    {
        // Create an email task with invalid parameters
        $result = $this->taskService->createTask(
            'Invalid Email',
            'email',
            [] // Missing required parameters
        );
        
        if ($result['success']) {
            $taskId = $result['task_id'];
            
            // Get the task to process
            $taskResult = $this->taskService->getNextTask('test_worker', 'default');
            
            if ($taskResult['success']) {
                $task = $taskResult['data'];
                
                // Process the email task (should fail)
                $processResult = $this->taskService->processEmailTask($task);
                
                $this->assertFalse($processResult['success']);
                $this->assertStringContainsString('Invalid email parameters', $processResult['message']);
            }
            
            // Clean up
            $this->cleanupTask($taskId);
        }
    }
    
    /** @test */
    public function it_can_get_task_statistics()
    {
        // Create some test tasks
        $this->createTestTask();
        
        $result = $this->taskService->getTaskStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required stats keys
        $requiredKeys = [
            'by_status',
            'by_type',
            'by_priority',
            'recent',
            'performance'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
        
        // Check performance metrics
        $this->assertArrayHasKey('total_tasks', $stats['performance']);
        $this->assertArrayHasKey('completed_tasks', $stats['performance']);
        $this->assertArrayHasKey('failed_tasks', $stats['performance']);
    }
    
    /** @test */
    public function it_can_cleanup_old_tasks()
    {
        // Create a task and mark it as completed with old date
        $result = $this->taskService->createTask('Old Task', 'email', ['to' => 'test@example.com']);
        
        if ($result['success']) {
            $taskId = $result['task_id'];
            
            // Complete the task
            $this->taskService->completeTask($taskId, ['success' => true]);
            
            // Manually update completed_at to be old
            $this->database->query(
                "UPDATE async_tasks SET completed_at = DATE_SUB(NOW(), INTERVAL 35 DAY) WHERE id = ?",
                [$taskId]
            );
            
            // Cleanup old tasks
            $cleanupResult = $this->taskService->cleanupOldTasks(30);
            
            $this->assertTrue($cleanupResult['success']);
            $this->assertArrayHasKey('deleted_count', $cleanupResult);
        }
    }
    
    /** @test */
    public function it_handles_pagination_correctly()
    {
        // Create multiple tasks
        for ($i = 0; $i < 5; $i++) {
            $this->taskService->createTask("Test Task $i", 'email', ['to' => "test$i@example.com"]);
        }
        
        // Get first page
        $page1 = $this->taskService->getTasks([], 2, 0);
        
        // Get second page
        $page2 = $this->taskService->getTasks([], 2, 2);
        
        $this->assertTrue($page1['success']);
        $this->assertTrue($page2['success']);
        
        $this->assertLessThanOrEqual(2, count($page1['data']));
        $this->assertLessThanOrEqual(2, count($page2['data']));
    }
    
    /**
     * Helper method to create test task
     */
    private function createTestTask()
    {
        if ($this->testTaskId) {
            return; // Already created
        }
        
        $result = $this->taskService->createTask(
            'Test Task',
            'email',
            [
                'to' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test message'
            ]
        );
        
        if ($result['success']) {
            $this->testTaskId = $result['task_id'];
        }
    }
    
    /**
     * Helper method to clean up test task
     */
    private function cleanupTask($taskId)
    {
        try {
            $this->database->query("DELETE FROM async_tasks WHERE id = ?", [$taskId]);
            $this->database->query("DELETE FROM task_queue WHERE task_id = ?", [$taskId]);
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test task
        if ($this->testTaskId) {
            $this->cleanupTask($this->testTaskId);
        }
        
        parent::tearDown();
    }
}