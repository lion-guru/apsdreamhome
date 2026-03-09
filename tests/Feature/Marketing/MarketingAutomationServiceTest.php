<?php

namespace Tests\Feature\Marketing;

use App\Services\Marketing\MarketingAutomationService;
use PHPUnit\Framework\TestCase;

/**
 * Marketing Automation Service Test - APS Dream Home
 * Custom MVC testing without Laravel dependencies
 */
class MarketingAutomationServiceTest extends TestCase
{
    private $marketingService;
    private $testLeadId;
    
    protected function setUp(): void
    {
        $this->marketingService = new MarketingAutomationService();
    }
    
    /** @test */
    public function it_can_be_initialized()
    {
        $this->assertInstanceOf(MarketingAutomationService::class, $this->marketingService);
    }
    
    /** @test */
    public function it_can_capture_lead()
    {
        $name = 'John Doe';
        $email = 'john.doe@example.com';
        $phone = '+1234567890';
        $source = 'website';
        $campaign = 'summer_sale';
        
        $result = $this->marketingService->captureLead($name, $email, $phone, $source, $campaign);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('lead_id', $result);
        $this->assertStringContainsString('Lead captured successfully', $result['message']);
        
        // Store for cleanup
        $this->testLeadId = $result['lead_id'];
    }
    
    /** @test */
    public function it_validates_required_lead_fields()
    {
        $result = $this->marketingService->captureLead('', '', '', '', '');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to capture lead', $result['message']);
    }
    
    /** @test */
    public function it_can_get_lead_by_email()
    {
        // First create a test lead
        $this->createTestLead();
        
        $lead = $this->marketingService->getLeadByEmail('john.doe@example.com');
        
        $this->assertNotNull($lead);
        $this->assertEquals('John Doe', $lead['name']);
        $this->assertEquals('john.doe@example.com', $lead['email']);
        $this->assertEquals('website', $lead['source']);
    }
    
    /** @test */
    public function it_returns_null_for_non_existent_email()
    {
        $lead = $this->marketingService->getLeadByEmail('nonexistent@example.com');
        
        $this->assertNull($lead);
    }
    
    /** @test */
    public function it_can_get_lead_by_id()
    {
        // First create a test lead
        $this->createTestLead();
        
        $lead = $this->marketingService->getLead($this->testLeadId);
        
        $this->assertNotNull($lead);
        $this->assertEquals($this->testLeadId, $lead['id']);
        $this->assertEquals('John Doe', $lead['name']);
        $this->assertEquals('john.doe@example.com', $lead['email']);
        $this->assertArrayHasKey('analytics', $lead);
    }
    
    /** @test */
    public function it_returns_null_for_non_existent_lead_id()
    {
        $lead = $this->marketingService->getLead(99999);
        
        $this->assertNull($lead);
    }
    
    /** @test */
    public function it_can_get_leads()
    {
        // First create a test lead
        $this->createTestLead();
        
        $result = $this->marketingService->getLeads();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_leads()
    {
        // Create leads with different statuses
        $this->createTestLead();
        
        // Update lead status
        $this->marketingService->updateLeadStatus($this->testLeadId, 'interested');
        
        // Filter by status
        $result = $this->marketingService->getLeads(['status' => 'interested']);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified status (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $lead) {
                $this->assertEquals('interested', $lead['status']);
            }
        }
    }
    
    /** @test */
    public function it_can_calculate_lead_score()
    {
        // First create a test lead
        $this->createTestLead();
        
        $result = $this->marketingService->calculateLeadScore($this->testLeadId);
        
        $this->assertTrue($result);
        
        // Verify score was calculated
        $lead = $this->marketingService->getLead($this->testLeadId);
        if ($lead) {
            $this->assertGreaterThan(0, $lead['score']);
        }
    }
    
    /** @test */
    public function it_can_update_lead_status()
    {
        // First create a test lead
        $this->createTestLead();
        
        $result = $this->marketingService->updateLeadStatus($this->testLeadId, 'contacted');
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Lead status updated successfully', $result['message']);
        
        // Verify status was updated
        $lead = $this->marketingService->getLead($this->testLeadId);
        if ($lead) {
            $this->assertEquals('contacted', $lead['status']);
        }
    }
    
    /** @test */
    public function it_validates_invalid_lead_status()
    {
        $result = $this->marketingService->updateLeadStatus(1, 'invalid_status');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid status', $result['message']);
    }
    
    /** @test */
    public function it_can_assign_lead_score()
    {
        // First create a test lead
        $this->createTestLead();
        
        $score = 25;
        $result = $this->marketingService->assignLeadScore($this->testLeadId, $score);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Lead score assigned successfully', $result['message']);
        
        // Verify score was added
        $lead = $this->marketingService->getLead($this->testLeadId);
        if ($lead) {
            $this->assertGreaterThanOrEqual($score, $lead['score']);
        }
    }
    
    /** @test */
    public function it_can_create_email_campaign()
    {
        $name = 'Test Campaign';
        $subject = 'Test Subject';
        $content = 'This is a test campaign content';
        $targetAudience = ['source' => 'website', 'score_min' => 20];
        
        $result = $this->marketingService->createEmailCampaign($name, $subject, $content, $targetAudience);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('campaign_id', $result);
        $this->assertStringContainsString('Campaign created successfully', $result['message']);
        
        // Store for cleanup
        $this->testCampaignId = $result['campaign_id'];
    }
    
    /** @test */
    public function it_validates_required_campaign_fields()
    {
        $result = $this->marketingService->createEmailCampaign('', '', '', []);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to create campaign', $result['message']);
    }
    
    /** @test */
    public function it_can_get_campaigns()
    {
        // First create a test campaign
        $this->createTestCampaign();
        
        $result = $this->marketingService->getCampaigns();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
    }
    
    /** @test */
    public function it_can_filter_campaigns()
    {
        // Create a test campaign
        $this->createTestCampaign();
        
        // Filter by type
        $result = $this->marketingService->getCampaigns(['type' => 'email']);
        
        $this->assertTrue($result['success']);
        
        // Verify all results have the specified type (if any results exist)
        if (!empty($result['data'])) {
            foreach ($result['data'] as $campaign) {
                $this->assertEquals('email', $campaign['type']);
            }
        }
    }
    
    /** @test */
    public function it_can_trigger_automation()
    {
        // First create a test lead
        $this->createTestLead();
        
        $result = $this->marketingService->triggerAutomation('lead_signup', $this->testLeadId);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('Automation triggered successfully', $result['message']);
    }
    
    /** @test */
    public function it_can_get_dashboard_data()
    {
        // First create a test lead
        $this->createTestLead();
        
        $result = $this->marketingService->getDashboardData();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $dashboard = $result['data'];
        
        // Check required dashboard keys
        $requiredKeys = [
            'total_leads',
            'new_leads_today',
            'new_leads_this_week',
            'conversion_rate',
            'top_campaigns',
            'lead_sources',
            'pipeline_stages',
            'recent_activity'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $dashboard);
        }
        
        // Verify data types
        $this->assertIsInt($dashboard['total_leads']);
        $this->assertIsInt($dashboard['new_leads_today']);
        $this->assertIsInt($dashboard['new_leads_this_week']);
        $this->assertIsFloat($dashboard['conversion_rate']);
        $this->assertIsArray($dashboard['top_campaigns']);
        $this->assertIsArray($dashboard['lead_sources']);
        $this->assertIsArray($dashboard['pipeline_stages']);
        $this->assertIsArray($dashboard['recent_activity']);
    }
    
    /** @test */
    public function it_can_get_lead_statistics()
    {
        // First create a test lead
        $this->createTestLead();
        
        $result = $this->marketingService->getLeadStats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        
        // Check required stats keys
        $requiredKeys = [
            'status_distribution',
            'source_distribution',
            'score_distribution',
            'monthly_trends'
        ];
        
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
        
        // Verify data types
        $this->assertIsArray($stats['status_distribution']);
        $this->assertIsArray($stats['source_distribution']);
        $this->assertIsArray($stats['score_distribution']);
        $this->assertIsArray($stats['monthly_trends']);
    }
    
    /** @test */
    public function it_handles_duplicate_lead_capture()
    {
        // Create first lead
        $this->createTestLead();
        $firstLeadId = $this->testLeadId;
        
        // Try to create same lead again
        $result = $this->marketingService->captureLead(
            'John Doe Updated',
            'john.doe@example.com',
            '+1234567890',
            'google',
            'updated_campaign'
        );
        
        $this->assertTrue($result['success']);
        
        // Should return the same lead ID (updated)
        $this->assertEquals($firstLeadId, $result['lead_id']);
        
        // Verify the lead was updated
        $lead = $this->marketingService->getLead($firstLeadId);
        if ($lead) {
            $this->assertEquals('google', $lead['source']);
            $this->assertEquals('updated_campaign', $lead['campaign']);
        }
    }
    
    /** @test */
    public function it_handles_pagination_correctly()
    {
        // Create multiple leads
        for ($i = 0; $i < 5; $i++) {
            $this->marketingService->captureLead(
                "Test User $i",
                "test$i@example.com",
                "+123456789$i",
                'website',
                'test_campaign'
            );
        }
        
        // Get first page
        $page1 = $this->marketingService->getLeads([], 2, 0);
        
        // Get second page
        $page2 = $this->marketingService->getLeads([], 2, 2);
        
        $this->assertTrue($page1['success']);
        $this->assertTrue($page2['success']);
        
        $this->assertLessThanOrEqual(2, count($page1['data']));
        $this->assertLessThanOrEqual(2, count($page2['data']));
    }
    
    /** @test */
    public function it_calculates_different_source_scores()
    {
        // Test different sources
        $sources = ['website', 'google', 'facebook', 'referral', 'direct'];
        $leadIds = [];
        
        foreach ($sources as $source) {
            $result = $this->marketingService->captureLead(
                "Test User $source",
                "test$source@example.com",
                "+1234567890",
                $source,
                'test_campaign'
            );
            
            if ($result['success']) {
                $leadIds[] = $result['lead_id'];
            }
        }
        
        // Calculate scores for all leads
        foreach ($leadIds as $leadId) {
            $this->marketingService->calculateLeadScore($leadId);
        }
        
        // Verify different sources have different base scores
        $websiteLead = $this->marketingService->getLeadByEmail('testwebsite@example.com');
        $directLead = $this->marketingService->getLeadByEmail('testdirect@example.com');
        
        if ($websiteLead && $directLead) {
            $this->assertLessThan($directLead['score'], $websiteLead['score']);
        }
        
        // Clean up
        foreach ($leadIds as $leadId) {
            $this->cleanupLead($leadId);
        }
    }
    
    /**
     * Helper method to create test lead
     */
    private function createTestLead()
    {
        if ($this->testLeadId) {
            return; // Already created
        }
        
        $result = $this->marketingService->captureLead(
            'John Doe',
            'john.doe@example.com',
            '+1234567890',
            'website',
            'summer_sale'
        );
        
        if ($result['success']) {
            $this->testLeadId = $result['lead_id'];
        }
    }
    
    /**
     * Helper method to create test campaign
     */
    private function createTestCampaign()
    {
        if (isset($this->testCampaignId)) {
            return; // Already created
        }
        
        $result = $this->marketingService->createEmailCampaign(
            'Test Campaign',
            'Test Subject',
            'This is a test campaign content',
            ['source' => 'website']
        );
        
        if ($result['success']) {
            $this->testCampaignId = $result['campaign_id'];
        }
    }
    
    /**
     * Helper method to clean up test lead
     */
    private function cleanupLead($leadId)
    {
        try {
            $this->database->query("DELETE FROM marketing_leads WHERE id = ?", [$leadId]);
            $this->database->query("DELETE FROM marketing_analytics WHERE lead_id = ?", [$leadId]);
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
    
    /**
     * Helper method to clean up test campaign
     */
    private function cleanupCampaign($campaignId)
    {
        try {
            $this->database->query("DELETE FROM marketing_campaigns WHERE id = ?", [$campaignId]);
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test lead
        if ($this->testLeadId) {
            $this->cleanupLead($this->testLeadId);
        }
        
        // Clean up test campaign
        if (isset($this->testCampaignId)) {
            $this->cleanupCampaign($this->testCampaignId);
        }
        
        parent::tearDown();
    }
}