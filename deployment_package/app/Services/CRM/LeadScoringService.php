<?php

namespace App\Services\CRM;

use App\Core\Database;
use App\Models\Lead;

/**
 * Lead Scoring Service
 * AI-based lead quality rating system
 */
class LeadScoringService
{
    private $db;
    
    // Scoring weights
    private $weights = [
        'demographic' => 25,
        'engagement' => 30,
        'behavior' => 25,
        'source' => 20
    ];

    // Score thresholds
    const SCORE_HOT = 80;
    const SCORE_WARM = 60;
    const SCORE_COLD = 40;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Calculate lead score
     */
    public function calculateScore(int $leadId): array
    {
        $lead = (new Lead())->find($leadId);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $scores = [
            'demographic' => $this->calculateDemographicScore($lead),
            'engagement' => $this->calculateEngagementScore($leadId),
            'behavior' => $this->calculateBehaviorScore($leadId),
            'source' => $this->calculateSourceScore($lead)
        ];

        $totalScore = 0;
        foreach ($scores as $category => $score) {
            $totalScore += ($score['score'] / 100) * $this->weights[$category];
        }

        $totalScore = min(100, max(0, $totalScore));

        // Update lead score
        $this->updateLeadScore($leadId, $totalScore, $scores);

        return [
            'success' => true,
            'lead_id' => $leadId,
            'total_score' => round($totalScore, 2),
            'category' => $this->categorizeLead($totalScore),
            'breakdown' => $scores,
            'recommendations' => $this->getRecommendations($scores)
        ];
    }

    /**
     * Calculate demographic score
     */
    private function calculateDemographicScore(array $lead): array
    {
        $score = 0;
        $factors = [];

        // Income level (if available)
        if (!empty($lead['income_range'])) {
            $incomeScores = [
                'below_5l' => 10,
                '5l_10l' => 30,
                '10l_20l' => 60,
                '20l_50l' => 80,
                'above_50l' => 100
            ];
            $score += $incomeScores[$lead['income_range']] ?? 30;
            $factors['income'] = $lead['income_range'];
        }

        // Location proximity
        if (!empty($lead['city']) && !empty($lead['preferred_location'])) {
            if (stripos($lead['preferred_location'], $lead['city']) !== false) {
                $score += 20;
                $factors['location_match'] = true;
            }
        }

        // Profession type
        if (!empty($lead['profession'])) {
            $professionScores = [
                'business' => 80,
                'professional' => 70,
                'salaried' => 60,
                'self_employed' => 50,
                'other' => 30
            ];
            $score += $professionScores[$lead['profession']] ?? 30;
            $factors['profession'] = $lead['profession'];
        }

        // Age group (buying potential)
        if (!empty($lead['age_group'])) {
            $ageScores = [
                '25-35' => 90,
                '35-45' => 100,
                '45-55' => 70,
                '55+' => 40
            ];
            $score += $ageScores[$lead['age_group']] ?? 50;
            $factors['age_group'] = $lead['age_group'];
        }

        return [
            'score' => min(100, $score / 4),
            'factors' => $factors
        ];
    }

    /**
     * Calculate engagement score
     */
    private function calculateEngagementScore(int $leadId): array
    {
        $score = 0;
        $factors = [];

        // Email engagement
        $sql = "SELECT 
                    COUNT(*) as total_emails,
                    SUM(CASE WHEN opened = 1 THEN 1 ELSE 0 END) as opened,
                    SUM(CASE WHEN clicked = 1 THEN 1 ELSE 0 END) as clicked
                FROM email_tracking WHERE lead_id = ?";
        $emailStats = $this->db->query($sql, [$leadId])->fetch(\PDO::FETCH_ASSOC);

        if ($emailStats && $emailStats['total_emails'] > 0) {
            $openRate = $emailStats['opened'] / $emailStats['total_emails'];
            $clickRate = $emailStats['clicked'] / $emailStats['total_emails'];
            $score += $openRate * 30 + $clickRate * 20;
            $factors['email_engagement'] = round(($openRate + $clickRate) / 2 * 100, 2);
        }

        // Website visits
        $sql = "SELECT COUNT(*) as visits FROM lead_visits WHERE lead_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $visits = $this->db->query($sql, [$leadId])->fetchColumn();
        $score += min(25, $visits * 5);
        $factors['website_visits'] = $visits;

        // Property views
        $sql = "SELECT COUNT(*) as views FROM property_views pv
                JOIN leads l ON pv.user_id = l.user_id WHERE l.id = ?";
        $views = $this->db->query($sql, [$leadId])->fetchColumn();
        $score += min(25, $views * 2);
        $factors['property_views'] = $views;

        return [
            'score' => min(100, $score),
            'factors' => $factors
        ];
    }

    /**
     * Calculate behavior score
     */
    private function calculateBehaviorScore(int $leadId): array
    {
        $score = 0;
        $factors = [];

        // Inquiry frequency
        $sql = "SELECT COUNT(*) FROM inquiries WHERE lead_id = ?";
        $inquiries = $this->db->query($sql, [$leadId])->fetchColumn();
        $score += min(20, $inquiries * 10);
        $factors['inquiries'] = $inquiries;

        // Property visits scheduled
        $sql = "SELECT COUNT(*) FROM property_visits WHERE lead_id = ? AND status = 'completed'";
        $visits = $this->db->query($sql, [$leadId])->fetchColumn();
        $score += min(30, $visits * 15);
        $factors['property_visits'] = $visits;

        // Favorites saved
        $sql = "SELECT COUNT(*) FROM property_favorites WHERE lead_id = ?";
        $favorites = $this->db->query($sql, [$leadId])->fetchColumn();
        $score += min(15, $favorites * 5);
        $factors['favorites'] = $favorites;

        // Documents uploaded
        $sql = "SELECT COUNT(*) FROM lead_documents WHERE lead_id = ?";
        $docs = $this->db->query($sql, [$leadId])->fetchColumn();
        $score += min(20, $docs * 10);
        $factors['documents_uploaded'] = $docs;

        // Response time (how quickly they respond)
        $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) as avg_response
                FROM lead_communications WHERE lead_id = ? AND first_response_at IS NOT NULL";
        $avgResponse = $this->db->query($sql, [$leadId])->fetchColumn();

        if ($avgResponse !== null) {
            if ($avgResponse < 1) $score += 15;
            elseif ($avgResponse < 6) $score += 10;
            elseif ($avgResponse < 24) $score += 5;
            $factors['avg_response_hours'] = round($avgResponse, 2);
        }

        return [
            'score' => min(100, $score),
            'factors' => $factors
        ];
    }

    /**
     * Calculate source score
     */
    private function calculateSourceScore(array $lead): array
    {
        $sourceScores = [
            'referral' => 90,
            'website_form' => 70,
            'google_ads' => 60,
            'facebook' => 50,
            'instagram' => 50,
            'whatsapp' => 75,
            'phone_call' => 80,
            'walk_in' => 85,
            'property_portal' => 55,
            'email_campaign' => 45,
            'other' => 30
        ];

        $source = $lead['source'] ?? 'other';
        $score = $sourceScores[$source] ?? 30;

        // Bonus for referral leads
        if ($source === 'referral' && !empty($lead['referrer_id'])) {
            $score += 10;
        }

        return [
            'score' => min(100, $score),
            'factors' => ['source' => $source]
        ];
    }

    /**
     * Categorize lead based on score
     */
    private function categorizeLead(float $score): string
    {
        if ($score >= self::SCORE_HOT) return 'hot';
        if ($score >= self::SCORE_WARM) return 'warm';
        if ($score >= self::SCORE_COLD) return 'cold';
        return 'very_cold';
    }

    /**
     * Get recommendations for improving score
     */
    private function getRecommendations(array $scores): array
    {
        $recommendations = [];

        if ($scores['engagement']['score'] < 50) {
            $recommendations[] = 'Send targeted email campaigns to increase engagement';
        }

        if ($scores['behavior']['score'] < 50) {
            $recommendations[] = 'Schedule property visit to increase conversion likelihood';
        }

        if ($scores['demographic']['score'] < 50) {
            $recommendations[] = 'Collect more demographic information through follow-up';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Lead is well-qualified. Focus on closing the deal';
        }

        return $recommendations;
    }

    /**
     * Update lead score in database
     */
    private function updateLeadScore(int $leadId, float $score, array $breakdown): void
    {
        $this->db->query(
            "UPDATE leads SET score = ?, score_breakdown = ?, score_updated_at = NOW() WHERE id = ?",
            [$score, json_encode($breakdown), $leadId]
        );
    }

    /**
     * Batch score all leads
     */
    public function scoreAllLeads(): array
    {
        $sql = "SELECT id FROM leads WHERE status NOT IN ('converted', 'lost')";
        $leads = $this->db->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        $results = ['scored' => 0, 'errors' => 0];

        foreach ($leads as $leadId) {
            $result = $this->calculateScore($leadId);
            if ($result['success']) {
                $results['scored']++;
            } else {
                $results['errors']++;
            }
        }

        return $results;
    }

    /**
     * Get leads by score category
     */
    public function getLeadsByCategory(string $category): array
    {
        $thresholds = [
            'hot' => [self::SCORE_HOT, 100],
            'warm' => [self::SCORE_WARM, self::SCORE_HOT - 1],
            'cold' => [self::SCORE_COLD, self::SCORE_WARM - 1],
            'very_cold' => [0, self::SCORE_COLD - 1]
        ];

        [$min, $max] = $thresholds[$category] ?? [0, 100];

        return $this->db->query(
            "SELECT * FROM leads WHERE score >= ? AND score <= ? ORDER BY score DESC",
            [$min, $max]
        )->fetchAll(\PDO::FETCH_ASSOC);
    }
}
