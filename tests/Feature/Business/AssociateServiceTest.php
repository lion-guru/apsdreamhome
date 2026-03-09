<?php

namespace Tests\Feature\Business;

use App\Services\Business\AssociateService;
use App\Models\Associate;
use PHPUnit\Framework\TestCase;

/**
 * Associate Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class AssociateServiceTest extends TestCase
{
    private $associateService;
    private $testAssociateId;
    
    protected function setUp(): void
    {
        $this->associateService = new AssociateService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(AssociateService::class, $this->associateService);
    }
    
    /** @test */
    public function it_can_create_associate()
    {
        $data = [
            'name' => 'Test Associate',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Test Street',
            'commission_rate' => 5.5,
            'status' => 'active'
        ];
        
        $result = $this->associateService->createAssociate($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('Test Associate', $result['data']['name']);
        $this->assertEquals('test@example.com', $result['data']['email']);
        
        // Store ID for cleanup
        $this->testAssociateId = $result['data']['id'];
    }
    
    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $data = [
            'name' => '',
            'email' => ''
        ];
        
        $result = $this->associateService->createAssociate($data);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('name', $result['errors']);
        $this->assertArrayHasKey('email', $result['errors']);
    }
    
    /** @test */
    public function it_validates_email_format()
    {
        $data = [
            'name' => 'Test Associate',
            'email' => 'invalid-email'
        ];
        
        $result = $this->associateService->createAssociate($data);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }
    
    /** @test */
    public function it_prevents_duplicate_email()
    {
        // Create first associate
        $data1 = [
            'name' => 'First Associate',
            'email' => 'duplicate@example.com'
        ];
        
        $result1 = $this->associateService->createAssociate($data1);
        $this->assertTrue($result1['success']);
        
        // Try to create second associate with same email
        $data2 = [
            'name' => 'Second Associate',
            'email' => 'duplicate@example.com'
        ];
        
        $result2 = $this->associateService->createAssociate($data2);
        
        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('Email already exists', $result2['message']);
    }
    
    /** @test */
    public function it_can_get_all_associates()
    {
        // Create test associate
        $data = [
            'name' => 'List Test Associate',
            'email' => 'listtest@example.com'
        ];
        
        $createResult = $this->associateService->createAssociate($data);
        $this->assertTrue($createResult['success']);
        
        // Get all associates
        $result = $this->associateService->getAllAssociates(1, 20);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['data']);
        $this->assertGreaterThan(0, count($result['data']));
    }
    
    /** @test */
    public function it_can_get_associate_details()
    {
        // Create test associate
        $data = [
            'name' => 'Details Test Associate',
            'email' => 'detailstest@example.com'
        ];
        
        $createResult = $this->associateService->createAssociate($data);
        $this->assertTrue($createResult['success']);
        
        $associateId = $createResult['data']['id'];
        
        // Get associate details
        $result = $this->associateService->getAssociateDetails($associateId);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('associate', $result['data']);
        $this->assertArrayHasKey('metrics', $result['data']);
        $this->assertArrayHasKey('monthly_performance', $result['data']);
        $this->assertEquals('Details Test Associate', $result['data']['associate']['name']);
    }
    
    /** @test */
    public function it_returns_error_for_non_existent_associate()
    {
        $result = $this->associateService->getAssociateDetails(99999);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['message']);
    }
    
    /** @test */
    public function it_can_update_associate()
    {
        // Create test associate
        $data = [
            'name' => 'Update Test Associate',
            'email' => 'updatetest@example.com'
        ];
        
        $createResult = $this->associateService->createAssociate($data);
        $this->assertTrue($createResult['success']);
        
        $associateId = $createResult['data']['id'];
        
        // Update associate
        $updateData = [
            'name' => 'Updated Associate Name',
            'phone' => '9876543210'
        ];
        
        $result = $this->associateService->updateAssociate($associateId, $updateData);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Updated Associate Name', $result['data']['name']);
        $this->assertEquals('9876543210', $result['data']['phone']);
    }
    
    /** @test */
    public function it_can_delete_associate()
    {
        // Create test associate
        $data = [
            'name' => 'Delete Test Associate',
            'email' => 'deletetest@example.com'
        ];
        
        $createResult = $this->associateService->createAssociate($data);
        $this->assertTrue($createResult['success']);
        
        $associateId = $createResult['data']['id'];
        
        // Delete associate
        $result = $this->associateService->deleteAssociate($associateId);
        
        $this->assertTrue($result['success']);
        
        // Verify associate is deleted
        $deletedAssociate = Associate::find($associateId);
        $this->assertNull($deletedAssociate);
    }
    
    /** @test */
    public function it_can_update_commission_rate()
    {
        // Create test associate
        $data = [
            'name' => 'Commission Test Associate',
            'email' => 'commissiontest@example.com'
        ];
        
        $createResult = $this->associateService->createAssociate($data);
        $this->assertTrue($createResult['success']);
        
        $associateId = $createResult['data']['id'];
        
        // Update commission rate
        $result = $this->associateService->updateCommissionRate($associateId, 7.5);
        
        $this->assertTrue($result['success']);
        
        // Verify commission rate was updated
        $associate = Associate::find($associateId);
        $this->assertEquals(7.5, $associate->commission_rate);
    }
    
    /** @test */
    public function it_validates_commission_rate_range()
    {
        // Create test associate
        $data = [
            'name' => 'Rate Validation Test',
            'email' => 'ratevalidation@example.com'
        ];
        
        $createResult = $this->associateService->createAssociate($data);
        $this->assertTrue($createResult['success']);
        
        $associateId = $createResult['data']['id'];
        
        // Try to set invalid commission rate
        $result = $this->associateService->updateCommissionRate($associateId, 150);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('between 0 and 100', $result['message']);
    }
    
    /** @test */
    public function it_can_get_performance_report()
    {
        $result = $this->associateService->getPerformanceReport();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('performance', $result['data']);
        $this->assertArrayHasKey('summary', $result['data']);
        $this->assertIsArray($result['data']['performance']);
    }
    
    /** @test */
    public function it_can_get_top_performers()
    {
        $result = $this->associateService->getTopPerformers(5, 'month');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertLessThanOrEqual(5, count($result['data']));
    }
    
    /** @test */
    public function it_can_export_associates()
    {
        $result = $this->associateService->exportAssociates('csv');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('file', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertStringEndsWith('.csv', $result['file']);
        $this->assertFileExists($result['path']);
        
        // Clean up export file
        if (file_exists($result['path'])) {
            unlink($result['path']);
        }
    }
    
    /** @test */
    public function it_can_filter_associates_by_status()
    {
        // Create associates with different statuses
        $activeData = [
            'name' => 'Active Associate',
            'email' => 'active@example.com',
            'status' => 'active'
        ];
        
        $inactiveData = [
            'name' => 'Inactive Associate',
            'email' => 'inactive@example.com',
            'status' => 'inactive'
        ];
        
        $this->associateService->createAssociate($activeData);
        $this->associateService->createAssociate($inactiveData);
        
        // Filter by active status
        $result = $this->associateService->getAllAssociates(1, 20, ['status' => 'active']);
        
        $this->assertTrue($result['success']);
        
        // Verify all results are active
        foreach ($result['data'] as $associate) {
            $this->assertEquals('active', $associate['status']);
        }
    }
    
    /** @test */
    public function it_can_search_associates()
    {
        // Create test associate
        $data = [
            'name' => 'Search Test Associate',
            'email' => 'searchtest@example.com'
        ];
        
        $this->associateService->createAssociate($data);
        
        // Search associates
        $result = $this->associateService->getAllAssociates(1, 20, ['search' => 'Search Test']);
        
        $this->assertTrue($result['success']);
        
        // Verify search results contain the test associate
        $found = false;
        foreach ($result['data'] as $associate) {
            if (strpos($associate['name'], 'Search Test') !== false) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue($found);
    }
    
    /** @test */
    public function it_handles_pagination_correctly()
    {
        // Create multiple test associates
        for ($i = 1; $i <= 25; $i++) {
            $data = [
                'name' => "Pagination Test Associate $i",
                'email' => "paginationtest$i@example.com"
            ];
            
            $this->associateService->createAssociate($data);
        }
        
        // Get first page
        $page1 = $this->associateService->getAllAssociates(1, 10);
        
        // Get second page
        $page2 = $this->associateService->getAllAssociates(2, 10);
        
        $this->assertTrue($page1['success']);
        $this->assertTrue($page2['success']);
        
        $this->assertEquals(10, count($page1['data']));
        $this->assertEquals(10, count($page2['data']));
        $this->assertEquals(1, $page1['pagination']['current_page']);
        $this->assertEquals(2, $page2['pagination']['current_page']);
        $this->assertGreaterThan($page2['pagination']['last_page'], $page2['pagination']['current_page']);
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->testAssociateId) {
            $associate = Associate::find($this->testAssociateId);
            if ($associate) {
                $associate->delete();
            }
        }
        
        // Clean up any test associates created during tests
        $testEmails = [
            'test@example.com',
            'listtest@example.com',
            'detailstest@example.com',
            'updatetest@example.com',
            'deletetest@example.com',
            'commissiontest@example.com',
            'ratevalidation@example.com',
            'active@example.com',
            'inactive@example.com',
            'searchtest@example.com'
        ];
        
        foreach ($testEmails as $email) {
            $associate = Associate::findByEmail($email);
            if ($associate) {
                $associate->delete();
            }
        }
        
        // Clean up pagination test associates
        for ($i = 1; $i <= 25; $i++) {
            $associate = Associate::findByEmail("paginationtest$i@example.com");
            if ($associate) {
                $associate->delete();
            }
        }
        
        parent::tearDown();
    }
}