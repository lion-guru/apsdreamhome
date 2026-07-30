<?php
/**
 * LeadScorer - AI-powered lead scoring
 * Self-hosted, no external API
 * Combines: engagement + intent + budget + timing
 */

namespace App\Services\AI;

use PDO;

class LeadScorer
{
    private $db;
    private $pdo;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    /**
     * Score a single lead
     * Returns total score (0-100), grade (A-F), breakdown
     */
    public function score(int $leadId): array
    {
        // Get lead data
        $lead = $this->getLead($leadId);
        if (!$lead) return ['score' => 0, 'grade' => 'F', 'error' => 'Lead not found'];

        $intentScore = $this->scoreIntent($lead);
        $engagementScore = $this->scoreEngagement($lead);
        $budgetScore = $this->scoreBudget($lead);
        $timingScore = $this->scoreTiming($lead);

        $total = $intentScore + $engagementScore + $budgetScore + $timingScore;
        $total = min(100, $total);

        $grade = match (true) {
            $total >= 80 => 'A',
            $total >= 60 => 'B',
            $total >= 40 => 'C',
            $total >= 20 => 'D',
            default => 'F'
        };

        $predictedAction = $this->predictAction($total, $grade, $lead);
        $confidence = $this->calculateConfidence($lead);

        // Save score
        $this->saveScore($leadId, $total, $grade, [
            'intent' => $intentScore,
            'engagement' => $engagementScore,
            'budget' => $budgetScore,
            'timing' => $timingScore
        ], $predictedAction, $confidence);

        return [
            'score' => $total,
            'grade' => $grade,
            'intent' => $intentScore,
            'engagement' => $engagementScore,
            'budget' => $budgetScore,
            'timing' => $timingScore,
            'predicted_action' => $predictedAction,
            'confidence' => $confidence
        ];
    }

    private function getLead(int $leadId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->execute([$leadId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function scoreIntent(array $lead): int
    {
        $score = 0;
        $source = strtolower($lead['source'] ?? '');
        $status = strtolower($lead['status'] ?? '');

        // Source quality
        $sourceScores = [
            'referral' => 30, 'direct' => 25, 'website' => 20, 'whatsapp' => 22,
            'facebook' => 15, 'instagram' => 12, 'google_ads' => 18, 'walk_in' => 28
        ];
        $score += $sourceScores[$source] ?? 10;

        // Status progression
        $statusScores = [
            'new' => 5, 'contacted' => 10, 'interested' => 20,
            'qualified' => 25, 'viewing' => 30, 'negotiating' => 35
        ];
        $score += $statusScores[$status] ?? 0;

        return min(30, $score);
    }

    private function scoreEngagement(array $lead): int
    {
        $score = 0;
        $leadId = $lead['id'];

        // Count activities
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lead_activities WHERE lead_id = ?");
            $stmt->execute([$leadId]);
            $activityCount = (int)$stmt->fetchColumn();
            $score += min(15, $activityCount * 3);
        } catch (\Exception $e) { error_log($e->getMessage()); }

        // Count notes
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lead_notes WHERE lead_id = ?");
            $stmt->execute([$leadId]);
            $noteCount = (int)$stmt->fetchColumn();
            $score += min(10, $noteCount * 2);
        } catch (\Exception $e) { error_log($e->getMessage()); }

        // Has phone/email
        if (!empty($lead['phone'])) $score += 5;
        if (!empty($lead['email'])) $score += 5;

        return min(30, $score);
    }

    private function scoreBudget(array $lead): int
    {
        $score = 0;
        $budget = (float)($lead['budget'] ?? 0);

        if ($budget > 0) {
            if ($budget >= 5000000) $score = 25;       // 50L+
            elseif ($budget >= 2000000) $score = 20;   // 20L+
            elseif ($budget >= 1000000) $score = 15;   // 10L+
            elseif ($budget >= 500000) $score = 10;    // 5L+
            else $score = 5;
        }

        return $score;
    }

    private function scoreTiming(array $lead): int
    {
        $score = 0;
        $created = strtotime($lead['created_at'] ?? 'now');
        $daysOld = (time() - $created) / 86400;

        // Fresh leads score higher
        if ($daysOld < 1) $score = 20;
        elseif ($daysOld < 7) $score = 15;
        elseif ($daysOld < 30) $score = 10;
        elseif ($daysOld < 90) $score = 5;
        else $score = 0;

        // Last activity recency
        try {
            $stmt = $this->db->prepare("SELECT MAX(created_at) FROM lead_activities WHERE lead_id = ?");
            $stmt->execute([$lead['id']]);
            $lastActivity = $stmt->fetchColumn();
            if ($lastActivity) {
                $daysSince = (time() - strtotime($lastActivity)) / 86400;
                if ($daysSince < 3) $score += 10;
                elseif ($daysSince < 14) $score += 5;
            }
        } catch (\Exception $e) { error_log($e->getMessage()); }

        return min(20, $score);
    }

    private function predictAction(int $total, string $grade, array $lead): string
    {
        return match (true) {
            $total >= 80 => 'High intent - Schedule site visit within 48 hours',
            $total >= 60 => 'Warm lead - Phone call within 24 hours',
            $total >= 40 => 'Engaged - Send property recommendations',
            $total >= 20 => 'Cold lead - Add to nurture campaign',
            default => 'Unqualified - Re-evaluate after 30 days'
        };
    }

    private function calculateConfidence(array $lead): float
    {
        $factors = 0;
        if (!empty($lead['phone'])) $factors++;
        if (!empty($lead['email'])) $factors++;
        if (!empty($lead['budget'])) $factors++;
        if (!empty($lead['source'])) $factors++;
        return round($factors / 4, 2);
    }

    private function saveScore(int $leadId, int $total, string $grade, array $factors, string $action, float $confidence): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_lead_scores
            (lead_id, score, factors, intent_score, engagement_score, budget_score, timing_score, grade, predicted_action, confidence)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $leadId,
            $total,
            json_encode($factors),
            $factors['intent'] ?? 0,
            $factors['engagement'] ?? 0,
            $factors['budget'] ?? 0,
            $factors['timing'] ?? 0,
            $grade,
            $action,
            $confidence
        ]);
    }

    /**
     * Batch score all unscored leads
     */
    public function scoreAllUnscored(int $limit = 100): int
    {
        $stmt = $this->db->query("
            SELECT l.id FROM leads l
            LEFT JOIN ai_lead_scores s ON l.id = s.lead_id AND s.scored_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
            WHERE s.id IS NULL
            LIMIT $limit
        ");
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->score((int)$row['id']);
            $count++;
        }
        return $count;
    }
}
