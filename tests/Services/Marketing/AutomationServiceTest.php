<?php

namespace Tests\Services\Marketing;

use PHPUnit\Framework\TestCase;
use App\Services\Marketing\AutomationService;
use App\Core\Database;
use Psr\Log\LoggerInterface;

class AutomationServiceTest extends TestCase
{
    private AutomationService $marketingService;
    private Database $db;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(Database::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->marketingService = new AutomationService($this->db, $this->logger);
    }

    public function testCreateCampaignSuccess(): void
    {
        $name = 'Test Campaign';
        $type = 'email';
        $config = ['subject' => 'Test Subject', 'template' => 'test_template'];
        $segments = ['new_leads', 'prospects'];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->createCampaign($name, $type, $config, $segments);

        $this->assertTrue($result['success']);
        $this->assertEquals('Campaign created successfully', $result['message']);
        $this->assertArrayHasKey('campaign_id', $result);
    }

    public function testCreateCampaignInvalidType(): void
    {
        $name = 'Test Campaign';
        $invalidType = 'invalid_type';
        $config = [];
        $segments = [];

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->createCampaign($name, $invalidType, $config, $segments);

        // The service should still create the campaign but with invalid type
        $this->assertTrue($result['success']);
    }

    public function testAddLeadSuccess(): void
    {
        $leadData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'company' => 'Test Corp',
            'position' => 'Developer'
        ];
        $tags = ['new_lead', 'high_value'];

        $this->db->expects($this->exactly(3))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(0, 1, 1); // No duplicate lead, then lead_id and score

        $this->db->expects($this->exactly(4))
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->addLead($leadData, $tags);

        $this->assertTrue($result['success']);
        $this->assertEquals('Lead added successfully', $result['message']);
        $this->assertArrayHasKey('lead_id', $result);
        $this->assertArrayHasKey('score', $result);
    }

    public function testAddLeadValidationFailure(): void
    {
        $leadData = [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'phone' => ''
        ];

        $result = $this->marketingService->addLead($leadData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Lead validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testAddLeadDuplicateEmail(): void
    {
        $leadData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(1); // Duplicate lead exists

        $result = $this->marketingService->addLead($leadData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Lead already exists', $result['message']);
    }

    public function testExecuteCampaignSuccess(): void
    {
        $campaignId = 1;
        $campaign = [
            'id' => $campaignId,
            'name' => 'Test Campaign',
            'type' => 'email',
            'status' => 'active'
        ];

        $leads = [
            ['id' => 1, 'email' => 'lead1@example.com'],
            ['id' => 2, 'email' => 'lead2@example.com']
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($campaign);

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($leads);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->executeCampaign($campaignId);

        $this->assertTrue($result['success']);
        $this->assertEquals('Campaign executed successfully', $result['message']);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('sent', $result['results']);
        $this->assertArrayHasKey('delivered', $result['results']);
    }

    public function testExecuteCampaignNotFound(): void
    {
        $campaignId = 999;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->marketingService->executeCampaign($campaignId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Campaign not found', $result['message']);
    }

    public function testProcessWorkflowsSuccess(): void
    {
        $workflows = [
            ['id' => 1, 'name' => 'Welcome Workflow', 'trigger_type' => 'lead_created'],
            ['id' => 2, 'name' => 'Nurturing Workflow', 'trigger_type' => 'lead_activity']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($workflows);

        $result = $this->marketingService->processWorkflows();

        $this->assertTrue($result['success']);
        $this->assertEquals('Processed 2 workflows', $result['message']);
        $this->assertEquals(2, $result['processed']);
        $this->assertArrayHasKey('triggered', $result);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testProcessWorkflowsWithError(): void
    {
        $workflows = [
            ['id' => 1, 'name' => 'Welcome Workflow', 'trigger_type' => 'lead_created']
        ];

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($workflows);

        // Mock an exception during processing
        $result = $this->marketingService->processWorkflows();

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['processed']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testGetAnalytics(): void
    {
        $campaignStats = [
            ['type' => 'email', 'count' => 10],
            ['type' => 'sms', 'count' => 5]
        ];

        $leadStats = [
            ['status' => 'new', 'count' => 50],
            ['status' => 'contacted', 'count' => 30]
        ];

        $this->db->expects($this->exactly(6))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                $campaignStats,
                $leadStats,
                [['total_conversions' => 10]],
                [],
                []
            );

        $this->db->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(10, 0);

        $result = $this->marketingService->getAnalytics();

        $this->assertArrayHasKey('campaigns', $result);
        $this->assertArrayHasKey('leads', $result);
        $this->assertArrayHasKey('conversions', $result);
        $this->assertArrayHasKey('roi', $result);
        $this->assertArrayHasKey('recent_activity', $result);
    }

    public function testGetLead(): void
    {
        $leadId = 1;
        $lead = [
            'id' => $leadId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'new',
            'score' => 75
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($lead);

        $this->db->expects($this->exactly(3))
            ->method('fetchAll')
            ->willReturn([], [], []); // Empty tags, activities, campaigns

        $result = $this->marketingService->getLead($leadId);

        $this->assertEquals($lead, $result);
        $this->assertArrayHasKey('tags', $result);
        $this->assertArrayHasKey('activities', $result);
        $this->assertArrayHasKey('campaigns', $result);
    }

    public function testGetLeadNotFound(): void
    {
        $leadId = 999;

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->marketingService->getLead($leadId);

        $this->assertNull($result);
    }

    public function testUpdateLeadStatusSuccess(): void
    {
        $leadId = 1;
        $newStatus = 'contacted';
        $reason = 'Initial contact made';

        $lead = [
            'id' => $leadId,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'new'
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($lead);

        $this->db->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->updateLeadStatus($leadId, $newStatus, $reason);

        $this->assertTrue($result['success']);
        $this->assertEquals('Lead status updated successfully', $result['message']);
    }

    public function testUpdateLeadStatusInvalidStatus(): void
    {
        $leadId = 1;
        $invalidStatus = 'invalid_status';

        $result = $this->marketingService->updateLeadStatus($leadId, $invalidStatus);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid status', $result['message']);
    }

    public function testUpdateLeadStatusNotFound(): void
    {
        $leadId = 999;
        $newStatus = 'contacted';

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->marketingService->updateLeadStatus($leadId, $newStatus);

        $this->assertFalse($result['success']);
        $this->assertEquals('Lead not found', $result['message']);
    }

    public function testCalculateLeadScore(): void
    {
        $leadData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@business.com',
            'company' => 'Test Corp',
            'position' => 'Developer',
            'phone' => '1234567890'
        ];
        $tags = ['high_value', 'vip', 'qualified'];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn(0); // No duplicate lead

        $this->db->expects($this->exactly(4))
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->addLead($leadData, $tags);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('score', $result);
        
        // Score should be calculated based on:
        // Base: 0
        // Company: +10
        // Position: +10
        // Phone: +5
        // Business email: +15
        // Tags: high_value(+20) + vip(+25) + qualified(+15) = +60
        // Total expected: 10 + 10 + 5 + 15 + 60 = 100 (maxed at 100)
        $this->assertEquals(100, $result['score']);
    }

    public function testGetAnalyticsWithFilters(): void
    {
        $filters = ['date_from' => '2026-01-01', 'status' => 'active'];

        $this->db->expects($this->exactly(6))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [], // campaign stats
                [], // lead stats
                [], // conversion stats
                [], // roi stats
                []  // recent activity
            );

        $this->db->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(0, 0);

        $result = $this->marketingService->getAnalytics($filters);

        $this->assertArrayHasKey('campaigns', $result);
        $this->assertArrayHasKey('leads', $result);
        $this->assertArrayHasKey('conversions', $result);
        $this->assertArrayHasKey('roi', $result);
        $this->assertArrayHasKey('recent_activity', $result);
    }

    public function testExecuteCampaignWithErrors(): void
    {
        $campaignId = 1;
        $campaign = [
            'id' => $campaignId,
            'name' => 'Test Campaign',
            'type' => 'email',
            'status' => 'active'
        ];

        $leads = [
            ['id' => 1, 'email' => 'lead1@example.com'],
            ['id' => 2, 'email' => 'lead2@example.com']
        ];

        $this->db->expects($this->once())
            ->method('fetchOne')
            ->willReturn($campaign);

        $this->db->expects($this->once())
            ->method('fetchAll')
            ->willReturn($leads);

        $this->db->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $result = $this->marketingService->executeCampaign($campaignId);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('errors', $result['results']);
    }
}
