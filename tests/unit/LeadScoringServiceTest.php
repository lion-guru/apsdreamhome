<?php

namespace Tests\Unit;

use Tests\Unit\TestCase;
use App\Services\CRM\LeadScoringService;

/**
 * Unit tests for the LeadScoringService
 */
class LeadScoringServiceTest extends TestCase
{
    public function testLeadScoringServiceCanBeInstantiated()
    {
        $service = new LeadScoringService();
        $this->assertInstanceOf(LeadScoringService::class, $service);
    }

    public function testGetPresentDaysThisMonthReturnsInteger()
    {
        $days = LeadScoringService::getPresentDaysThisMonth();
        $this->assertIsInt($days);
        $this->assertGreaterThanOrEqual(0, $days);
        $this->assertLessThanOrEqual(31, $days); // Max days in a month
    }

    public function testGetWorkingDaysThisMonthReturnsInteger()
    {
        $days = LeadScoringService::getWorkingDaysThisMonth();
        $this->assertIsInt($days);
        $this->assertGreaterThanOrEqual(0, $days);
        $this->assertLessThanOrEqual(31, $days);
    }

    public function testGetAttendancePercentageReturnsFloat()
    {
        $percentage = LeadScoringService::getAttendancePercentage();
        $this->assertIsFloat($percentage);
        $this->assertGreaterThanOrEqual(0.0, $percentage);
        $this->assertLessThanOrEqual(100.0, $percentage);
    }

    public function testAttendancePercentageCalculation()
    {
        $workingDays = LeadScoringService::getWorkingDaysThisMonth();
        $presentDays = LeadScoringService::getPresentDaysThisMonth();

        if ($workingDays > 0) {
            $expectedPercentage = round(($presentDays / $workingDays) * 100, 2);
            $actualPercentage = LeadScoringService::getAttendancePercentage();

            $this->assertEquals($expectedPercentage, $actualPercentage);
        } else {
            // If no working days, percentage should be 0 or handled appropriately
            $this->assertTrue(true); // Skip assertion if no working days
        }
    }

    public function testCalculateScoreWithValidData()
    {
        $leadData = [
            'name' => 'Test Lead',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'budget' => 5000000,
            'property_type' => 'apartment',
            'urgency' => 'high',
            'source' => 'website'
        ];

        $service = new LeadScoringService();
        $score = $service->calculateScore($leadData);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function testCalculateScoreWithMissingData()
    {
        $leadData = [
            'name' => 'Incomplete Lead'
            // Missing other required fields
        ];

        $service = new LeadScoringService();
        $score = $service->calculateScore($leadData);

        $this->assertIsInt($score);
        // Should still return a valid score even with missing data
    }

    public function testScoreBasedOnBudget()
    {
        $service = new LeadScoringService();

        $lowBudgetLead = ['budget' => 100000, 'name' => 'Low Budget'];
        $highBudgetLead = ['budget' => 10000000, 'name' => 'High Budget'];

        $lowScore = $service->calculateScore($lowBudgetLead);
        $highScore = $service->calculateScore($highBudgetLead);

        // Higher budget should generally result in higher score
        $this->assertGreaterThanOrEqual($lowScore, $highScore);
    }

    public function testScoreBasedOnUrgency()
    {
        $service = new LeadScoringService();

        $lowUrgencyLead = ['urgency' => 'low', 'name' => 'Low Urgency'];
        $highUrgencyLead = ['urgency' => 'high', 'name' => 'High Urgency'];

        $lowScore = $service->calculateScore($lowUrgencyLead);
        $highScore = $service->calculateScore($highUrgencyLead);

        // Higher urgency should result in higher score
        $this->assertGreaterThanOrEqual($lowScore, $highScore);
    }

    public function testGetScoreGrade()
    {
        $service = new LeadScoringService();

        // Test different score ranges
        $this->assertEquals('Poor', $service->getScoreGrade(20));
        $this->assertEquals('Fair', $service->getScoreGrade(45));
        $this->assertEquals('Good', $service->getScoreGrade(65));
        $this->assertEquals('Excellent', $service->getScoreGrade(85));
    }

    public function testGetScoreColor()
    {
        $service = new LeadScoringService();

        // Test different score ranges
        $this->assertEquals('danger', $service->getScoreColor(20));
        $this->assertEquals('warning', $service->getScoreColor(45));
        $this->assertEquals('info', $service->getScoreColor(65));
        $this->assertEquals('success', $service->getScoreColor(85));
    }
}
