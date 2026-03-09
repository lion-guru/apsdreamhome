<?php

namespace Tests\Feature\HumanResources;

use App\Services\HumanResources\CareerService;
use PHPUnit\Framework\TestCase;

/**
 * Career Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class CareerServiceTest extends TestCase
{
    private $careerService;
    private $testApplicationId;
    
    protected function setUp(): void
    {
        $this->careerService = new CareerService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(CareerService::class, $this->careerService);
    }
    
    /** @test */
    public function it_can_submit_application()
    {
        $data = [
            'full_name' => 'Test Applicant',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer',
            'experience' => '5 years of experience',
            'cover_letter' => 'I am interested in this position',
            'availability' => 'Immediate'
        ];
        
        // Mock file upload
        $files = [
            'resume' => [
                'name' => 'resume.pdf',
                'type' => 'application/pdf',
                'tmp_name' => sys_get_temp_dir() . '/test_resume.pdf',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        // Create a temporary file
        file_put_contents($files['resume']['tmp_name'], 'test content');
        
        $result = $this->careerService->submitApplication($data, $files);
        
        // Clean up temporary file
        unlink($files['resume']['tmp_name']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('application_id', $result);
        
        // Store ID for cleanup
        $this->testApplicationId = $result['application_id'];
    }
    
    /** @test */
    public function it_validates_required_fields()
    {
        $data = [
            'full_name' => '',
            'email' => '',
            'phone' => '',
            'position' => ''
        ];
        
        $result = $this->careerService->submitApplication($data);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('full_name', $result['errors']);
        $this->assertArrayHasKey('email', $result['errors']);
        $this->assertArrayHasKey('phone', $result['errors']);
        $this->assertArrayHasKey('position', $result['errors']);
    }
    
    /** @test */
    public function it_validates_email_format()
    {
        $data = [
            'full_name' => 'Test Applicant',
            'email' => 'invalid-email',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        $result = $this->careerService->submitApplication($data);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }
    
    /** @test */
    public function it_requires_resume_file()
    {
        $data = [
            'full_name' => 'Test Applicant',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        $result = $this->careerService->submitApplication($data);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('resume', $result['errors']);
    }
    
    /** @test */
    public function it_validates_resume_file_type()
    {
        $data = [
            'full_name' => 'Test Applicant',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        // Mock invalid file type
        $files = [
            'resume' => [
                'name' => 'resume.txt',
                'type' => 'text/plain',
                'tmp_name' => sys_get_temp_dir() . '/test_resume.txt',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        // Create a temporary file
        file_put_contents($files['resume']['tmp_name'], 'test content');
        
        $result = $this->careerService->submitApplication($data, $files);
        
        // Clean up temporary file
        unlink($files['resume']['tmp_name']);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('resume', $result['errors']);
    }
    
    /** @test */
    public function it_validates_resume_file_size()
    {
        $data = [
            'full_name' => 'Test Applicant',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        // Mock oversized file
        $files = [
            'resume' => [
                'name' => 'resume.pdf',
                'type' => 'application/pdf',
                'tmp_name' => sys_get_temp_dir() . '/test_resume.pdf',
                'error' => UPLOAD_ERR_OK,
                'size' => 10 * 1024 * 1024 // 10MB (exceeds 5MB limit)
            ]
        ];
        
        // Create a temporary file
        file_put_contents($files['resume']['tmp_name'], 'test content');
        
        $result = $this->careerService->submitApplication($data, $files);
        
        // Clean up temporary file
        unlink($files['resume']['tmp_name']);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('resume', $result['errors']);
    }
    
    /** @test */
    public function it_can_get_applications()
    {
        $result = $this->careerService->getApplications(1, 20);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('applications', $result['data']);
        $this->assertArrayHasKey('pagination', $result['data']);
        $this->assertIsArray($result['data']['applications']);
        $this->assertIsArray($result['data']['pagination']);
    }
    
    /** @test */
    public function it_can_filter_applications_by_status()
    {
        $filters = ['status' => 'pending'];
        
        $result = $this->careerService->getApplications(1, 20, $filters);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified status (if any results exist)
        if (!empty($result['data']['applications'])) {
            foreach ($result['data']['applications'] as $application) {
                $this->assertEquals('pending', $application['status']);
            }
        }
    }
    
    /** @test */
    public function it_can_search_applications()
    {
        $filters = ['search' => 'Test'];
        
        $result = $this->careerService->getApplications(1, 20, $filters);
        
        $this->assertTrue($result['success']);
        $this->assertIsArray($result['data']['applications']);
    }
    
    /** @test */
    public function it_can_get_application_details()
    {
        // First create an application
        $data = [
            'full_name' => 'Details Test Applicant',
            'email' => 'details@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        $files = [
            'resume' => [
                'name' => 'resume.pdf',
                'type' => 'application/pdf',
                'tmp_name' => sys_get_temp_dir() . '/test_resume_details.pdf',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        file_put_contents($files['resume']['tmp_name'], 'test content');
        
        $createResult = $this->careerService->submitApplication($data, $files);
        unlink($files['resume']['tmp_name']);
        
        if ($createResult['success']) {
            $applicationId = $createResult['application_id'];
            
            // Get details
            $result = $this->careerService->getApplicationDetails($applicationId);
            
            $this->assertTrue($result['success']);
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('application', $result['data']);
            $this->assertArrayHasKey('history', $result['data']);
            $this->assertEquals('Details Test Applicant', $result['data']['application']['full_name']);
            
            // Clean up
            $this->careerService->deleteApplication($applicationId);
        }
    }
    
    /** @test */
    public function it_returns_error_for_non_existent_application()
    {
        $result = $this->careerService->getApplicationDetails(99999);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }
    
    /** @test */
    public function it_can_update_application_status()
    {
        // First create an application
        $data = [
            'full_name' => 'Status Test Applicant',
            'email' => 'status@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        $files = [
            'resume' => [
                'name' => 'resume.pdf',
                'type' => 'application/pdf',
                'tmp_name' => sys_get_temp_dir() . '/test_resume_status.pdf',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        file_put_contents($files['resume']['tmp_name'], 'test content');
        
        $createResult = $this->careerService->submitApplication($data, $files);
        unlink($files['resume']['tmp_name']);
        
        if ($createResult['success']) {
            $applicationId = $createResult['application_id'];
            
            // Update status
            $result = $this->careerService->updateApplicationStatus($applicationId, 'reviewing', 'Under review');
            
            $this->assertTrue($result['success']);
            
            // Verify status was updated
            $details = $this->careerService->getApplicationDetails($applicationId);
            if ($details['success']) {
                $this->assertEquals('reviewing', $details['data']['application']['status']);
            }
            
            // Clean up
            $this->careerService->deleteApplication($applicationId);
        }
    }
    
    /** @test */
    public function it_validates_status_values()
    {
        $result = $this->careerService->updateApplicationStatus(1, 'invalid_status');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid status', $result['message']);
    }
    
    /** @test */
    public function it_can_get_available_positions()
    {
        $result = $this->careerService->getAvailablePositions();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_get_application_stats()
    {
        $result = $this->careerService->getApplicationStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required stats keys
        $requiredKeys = [
            'by_status',
            'by_position',
            'recent_applications',
            'total_applications',
            'monthly_trends'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
            $this->assertIsArray($stats[$key]);
        }
    }
    
    /** @test */
    public function it_can_delete_application()
    {
        // First create an application
        $data = [
            'full_name' => 'Delete Test Applicant',
            'email' => 'delete@example.com',
            'phone' => '1234567890',
            'position' => 'Software Developer'
        ];
        
        $files = [
            'resume' => [
                'name' => 'resume.pdf',
                'type' => 'application/pdf',
                'tmp_name' => sys_get_temp_dir() . '/test_resume_delete.pdf',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];
        
        file_put_contents($files['resume']['tmp_name'], 'test content');
        
        $createResult = $this->careerService->submitApplication($data, $files);
        unlink($files['resume']['tmp_name']);
        
        if ($createResult['success']) {
            $applicationId = $createResult['application_id'];
            
            // Delete application
            $result = $this->careerService->deleteApplication($applicationId);
            
            $this->assertTrue($result['success']);
            
            // Verify application is deleted
            $details = $this->careerService->getApplicationDetails($applicationId);
            $this->assertFalse($details['success']);
        }
    }
    
    /** @test */
    public function it_handles_pagination_correctly()
    {
        // Get first page
        $page1 = $this->careerService->getApplications(1, 5);
        
        // Get second page
        $page2 = $this->careerService->getApplications(2, 5);
        
        $this->assertTrue($page1['success']);
        $this->assertTrue($page2['success']);
        
        $this->assertLessThanOrEqual(5, count($page1['data']['applications']));
        $this->assertLessThanOrEqual(5, count($page2['data']['applications']));
        
        if (!empty($page1['data']['applications'])) {
            $this->assertEquals(1, $page1['data']['pagination']['current_page']);
        }
        
        if (!empty($page2['data']['applications'])) {
            $this->assertEquals(2, $page2['data']['pagination']['current_page']);
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test application
        if ($this->testApplicationId) {
            $this->careerService->deleteApplication($this->testApplicationId);
        }
        
        parent::tearDown();
    }
}