<?php
/**
 * PatternLearner - Learns user behavior patterns
 * Self-hosted, no external API
 * Bayesian-like learning from actions
 */

namespace App\Services\AI;

use PDO;
use \App\Traits\ServiceTenantTrait;

class PatternLearner
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;

    private $defaultIntents = [
        'buy_property' => ['en' => ['buy','purchase','want to buy','looking to buy','need a property'], 'hi' => ['खरीदना','लेना है','चाहिए']],
        'sell_property' => ['en' => ['sell','want to sell','have property to sell','list my property'], 'hi' => ['बेचना','बेचना है','बेचनी है']],
        'rent_property' => ['en' => ['rent','rental','on rent','for rent','lease'], 'hi' => ['किराए','किराये','रेंट']],
        'greeting' => ['en' => ['hello','hi','hey','namaste'], 'hi' => ['नमस्ते','नमस्कार','हैलो']],
        'thanks' => ['en' => ['thanks','thank you','thx','appreciated'], 'hi' => ['धन्यवाद','शुक्रिया']],
    ];

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    /**
     * Record a learning event
     */
    public function record(string $actionType, ?int $userId = null, ?string $sessionId = null, array $input = [], array $output = [], array $context = [], ?int $feedbackScore = null): int
    {
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['user_id', 'session_id', 'action_type', 'input_data', 'output_data', 'context', 'feedback_score'], $tenantCols);
        $values  = array_merge([$userId, $sessionId, $actionType, json_encode($input), json_encode($output), json_encode($context), $feedbackScore], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $stmt = $this->db->prepare("INSERT INTO ai_learning_data ($colStr) VALUES ($placeholders)");
        $stmt->execute($values);
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
            WHERE action_type = ?{$this->tenantSql()}
            ORDER BY feedback_score DESC, hit_count DESC, learned_at DESC
            LIMIT ?
        ");
        $params = array_merge([$actionType, $limit * 5], $this->tenantId() > 1 ? [$this->tenantId()] : []);
        $stmt->execute($params);
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
        $stmt = $this->db->prepare("UPDATE ai_learning_data SET hit_count = hit_count + 1 WHERE id = ?{$this->tenantSql()}");
        $params = [$id];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
    }

    /**
     * Get top action patterns for a user (used for personalization)
     */
    public function getUserPatterns(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT action_type, COUNT(*) as cnt, AVG(feedback_score) as avg_fb
            FROM ai_learning_data
            WHERE user_id = ?{$this->tenantSql()} AND feedback_score IS NOT NULL
            GROUP BY action_type
            ORDER BY cnt DESC
            LIMIT ?
        ");
        $params = array_merge([$userId, $limit], $this->tenantId() > 1 ? [$this->tenantId()] : []);
        $stmt->execute($params);
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
        $stmt = $this->db->prepare("
            SELECT action_type, COUNT(*) as total,
                   AVG(feedback_score) as avg_score,
                   SUM(CASE WHEN feedback_score >= 4 THEN 1 ELSE 0 END) as good_count
            FROM ai_learning_data
            WHERE learned_at > DATE_SUB(NOW(), INTERVAL 7 DAY){$this->tenantSql()}
            GROUP BY action_type
        ");
        $stmt->execute($this->tenantId() > 1 ? [$this->tenantId()] : []);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['patterns' => $stats, 'timestamp' => date('Y-m-d H:i:s')];
    }

    /**
     * Add new learned pattern
     */
    public function learn(string $intent, string $pattern, string $type = 'keyword', float $weight = 1.0, string $language = 'en'): int
    {
        $tenantData = $this->tenantInsertData();
        $tenantCols = array_keys($tenantData);
        $tenantVals = array_values($tenantData);
        $columns = array_merge(['intent_name', 'pattern_text', 'pattern_type', 'weight', 'language'], $tenantCols);
        $values  = array_merge([$intent, $pattern, $type, $weight, $language], $tenantVals);
        $colStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $stmt = $this->db->prepare("
            INSERT INTO ai_intent_patterns ($colStr)
            VALUES ($placeholders)
            ON DUPLICATE KEY UPDATE weight = weight + 0.1, hit_count = hit_count + 1
        ");
        $stmt->execute($values);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Record successful match for reinforcement
     */
    public function reinforce(int $patternId, bool $success): void
    {
        $col = $success ? 'success_count' : 'hit_count';
        $stmt = $this->db->prepare("UPDATE ai_intent_patterns SET $col = $col + 1 WHERE id = ?{$this->tenantSql()}");
        $params = [$patternId];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
    }

    private function seedDefaultIntents(): void
    {
        try {
            $tid = $this->tenantId();
            $sql = "SELECT COUNT(*) FROM ai_intent_patterns WHERE language = 'en'" . ($tid > 1 ? " AND tenant_id = ?" : "");
            $stmt = $this->db->prepare($sql);
            $stmt->execute($tid > 1 ? [$tid] : []);
            if ((int)$stmt->fetchColumn() > 0) return;

            foreach ($this->defaultIntents as $intent => $langs) {
                foreach ($langs as $lang => $patterns) {
                    foreach ($patterns as $p) {
                        try {
                            $tenantData = $this->tenantInsertData();
                            $columns = array_merge(['intent_name', 'pattern_text', 'pattern_type', 'language', 'weight'], array_keys($tenantData));
                            $values  = array_merge([$intent, $p, 'keyword', $lang, 1.0], array_values($tenantData));
                            $colStr = implode(', ', $columns);
                            $placeholders = implode(', ', array_fill(0, count($values), '?'));
                            $ins = $this->db->prepare("INSERT INTO ai_intent_patterns ($colStr) VALUES ($placeholders)");
                            $ins->execute($values);
                        } catch (\Exception $e) {
                            error_log($e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    private function matchPattern(string $text, string $pattern, string $type): float
    {
        $text = strtolower($text);
        $pattern = strtolower($pattern);
        return match ($type) {
            'regex' => @preg_match($pattern, $text) ? 1.0 : 0.0,
            'exact' => $text === $pattern ? 1.0 : 0.0,
            'phrase' => stripos($text, $pattern) !== false ? 0.9 : 0.0,
            default => stripos($text, $pattern) !== false ? 0.7 : 0.0,
        };
    }

    private function detectLanguage(string $text): string
    {
        if (preg_match('/[\x{0900}-\x{097F}]/u', $text)) return 'hi';
        return 'en';
    }
}
