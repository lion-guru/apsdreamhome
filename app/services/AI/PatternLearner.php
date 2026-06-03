<?php
/**
 * PatternLearner - Learns user behavior patterns
 * Self-hosted, no external API
 * Bayesian-like learning from actions
 */

namespace App\Services\AI;

use PDO;

class PatternLearner
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Record a learning event
     */
    public function record(string $actionType, ?int $userId = null, ?string $sessionId = null, array $input = [], array $output = [], array $context = [], ?int $feedbackScore = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_learning_data (user_id, session_id, action_type, input_data, output_data, context, feedback_score)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $sessionId,
            $actionType,
            json_encode($input),
            json_encode($output),
            json_encode($context),
            $feedbackScore
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get similar past actions for a given input
     * Returns top N most similar past patterns
     */
    public function findSimilar(string $actionType, array $input, int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT output_data, context, feedback_score, hit_count
            FROM ai_learning_data
            WHERE action_type = ?
            ORDER BY feedback_score DESC, hit_count DESC, learned_at DESC
            LIMIT ?
        ");
        $stmt->execute([$actionType, $limit * 5]);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scored = [];
        foreach ($candidates as $c) {
            $past = json_decode($c['output_data'], true) ?: [];
            $sim = $this->similarity($input, $past);
            if ($sim > 0.3) {
                $scored[] = [
                    'data' => $past,
                    'similarity' => $sim,
                    'feedback' => (int)$c['feedback_score'],
                    'context' => json_decode($c['context'], true) ?: []
                ];
            }
        }
        usort($scored, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        return array_slice($scored, 0, $limit);
    }

    /**
     * Increment hit count for a learning entry
     */
    public function incrementHit(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE ai_learning_data SET hit_count = hit_count + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Get top action patterns for a user (used for personalization)
     */
    public function getUserPatterns(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT action_type, COUNT(*) as cnt, AVG(feedback_score) as avg_fb
            FROM ai_learning_data
            WHERE user_id = ? AND feedback_score IS NOT NULL
            GROUP BY action_type
            ORDER BY cnt DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cosine-like similarity between two arrays (simple Jaccard for sets)
     */
    private function similarity(array $a, array $b): float
    {
        $aVals = array_values($a);
        $bVals = array_values($b);
        $intersect = count(array_intersect($aVals, $bVals));
        $union = count(array_unique(array_merge($aVals, $bVals)));
        return $union > 0 ? $intersect / $union : 0.0;
    }

    /**
     * Retrain: aggregate past learnings into better patterns
     * Run nightly via cron
     */
    public function retrain(): array
    {
        $stmt = $this->db->query("
            SELECT action_type, COUNT(*) as total,
                   AVG(feedback_score) as avg_score,
                   SUM(CASE WHEN feedback_score >= 4 THEN 1 ELSE 0 END) as good_count
            FROM ai_learning_data
            WHERE learned_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY action_type
        ");
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['patterns' => $stats, 'timestamp' => date('Y-m-d H:i:s')];
    }
}
