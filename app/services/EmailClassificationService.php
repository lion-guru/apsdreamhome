<?php
/**
 * EmailClassificationService — Classify incoming emails/inquiries
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Config\Config;

class EmailClassificationService
{
    private $db;
    private $categories = [
        'inquiry' => [
            'keywords' => ['interested', 'enquiry', 'inquiry', 'information', 'details', 'price', 'cost', 'available', 'availability', 'looking for', 'want to buy', 'want to purchase', 'plot', 'property', 'home', 'house', 'flat', 'apartment'],
            'weight' => 1.0,
        ],
        'complaint' => [
            'keywords' => ['complaint', 'issue', 'problem', 'wrong', 'bad', 'poor', 'disappointed', 'unhappy', 'unsatisfied', 'delay', 'late', 'not working', 'broken', 'defect', 'refund', 'cancel'],
            'weight' => 1.2,
        ],
        'support' => [
            'keywords' => ['help', 'support', 'assist', 'guide', 'how to', 'process', 'procedure', 'document', 'documentation', 'paperwork', 'form', 'application', 'status', 'update', 'tracking'],
            'weight' => 1.0,
        ],
        'spam' => [
            'keywords' => ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'free money', 'earn money', 'work from home', 'crypto', 'bitcoin', 'investment opportunity', 'click here', 'buy now', 'limited offer', 'urgent', 'act now', 'loan', 'credit card', 'insurance'],
            'weight' => 2.0,
        ],
        'referral' => [
            'keywords' => ['refer', 'recommend', 'friend', 'family', 'colleague', 'referral code', 'referral program', 'introduce', 'introduced'],
            'weight' => 1.1,
        ],
        'booking' => [
            'keywords' => ['book', 'booking', 'reserve', 'reservation', 'schedule', 'appointment', 'site visit', 'visit', 'tour', 'inspect', 'see the plot', 'see the property'],
            'weight' => 1.1,
        ],
        'payment' => [
            'keywords' => ['payment', 'pay', 'paid', 'emi', 'installment', 'instalment', 'due', 'overdue', 'receipt', 'transaction', 'transfer', 'bank', 'upi', 'razorpay', 'gateway', 'refund', 'penalty', 'late fee'],
            'weight' => 1.2,
        ],
        'legal' => [
            'keywords' => ['registry', 'registration', 'deed', 'agreement', 'contract', 'legal', 'lawyer', 'advocate', 'stamp duty', 'mutation', 'title', 'ownership', 'transfer', 'noc', 'no objection', 'court', 'case', 'dispute'],
            'weight' => 1.3,
        ],
    ];

    // $db property already declared above
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Classify email/inquiry text
     */
    public function classify(string $text, array $metadata = []): array
    {
        $textLower = strtolower($text);
        $scores = [];

        foreach ($this->categories as $category => $config) {
            $score = 0;
            $matchedKeywords = [];

            foreach ($config['keywords'] as $keyword) {
                if (stripos($textLower, strtolower($keyword)) !== false) {
                    $score += $config['weight'];
                    $matchedKeywords[] = $keyword;
                }
            }

            // Boost for metadata hints
            if (!empty($metadata['subject'])) {
                $subjectLower = strtolower($metadata['subject']);
                foreach ($config['keywords'] as $keyword) {
                    if (stripos($subjectLower, strtolower($keyword)) !== false) {
                        $score += $config['weight'] * 1.5;
                    }
                }
            }

            if ($score > 0) {
                $scores[$category] = [
                    'score' => round($score, 2),
                    'matched_keywords' => $matchedKeywords,
                ];
            }
        }

        // Sort by score descending
        arsort($scores);

        // Get top category
        $topCategory = array_key_first($scores) ?? 'inquiry';
        $confidence = $scores[$topCategory]['score'] ?? 0;

        // Normalize confidence (0-100)
        $maxPossibleScore = 100; // theoretical max
        $normalizedConfidence = min(100, round(($confidence / $maxPossibleScore) * 100));

        return [
            'category' => $topCategory,
            'confidence' => min(100, $normalizedConfidence),
            'all_scores' => $scores,
            'is_spam' => $topCategory === 'spam',
        ];
    }

    /**
     * Classify + log (feeds getStats and future learning)
     */
    public function classifyAndLog(string $text, array $metadata = [], string $source = 'manual'): array
    {
        $result = $this->classify($text, $metadata);
        try {
            $tenantId = method_exists($this, 'tenantId') ? $this->tenantId() : 1;
            $this->db->insert('email_classification_log', [
                'category' => $result['category'],
                'confidence' => $result['confidence'],
                'source' => $source,
                'tenant_id' => $tenantId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('EmailClassificationService log: ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * Classify batch of messages
     */
    public function classifyBatch(array $messages): array
    {
        $results = [];
        foreach ($messages as $index => $message) {
            $text = $message['text'] ?? $message['body'] ?? $message['content'] ?? '';
            $metadata = $message['metadata'] ?? [];
            $results[] = array_merge(['index' => $index], $this->classify($text, $metadata));
        }
        return $results;
    }

    /**
     * Get category suggestions for manual override
     */
    public function getCategorySuggestions(string $text): array
    {
        $result = $this->classify($text);
        
        // Return top 3 categories with confidence
        $categories = array_slice($result['all_scores'] ?? [], 0, 3, true);
        
        return array_map(function($cat, $data) {
            return [
                'category' => $cat,
                'confidence' => $data['score'],
                'matched_keywords' => $data['matched_keywords'],
            ];
        }, array_keys($categories), $categories);
    }

    /**
     * Learn from user feedback (reinforcement learning)
     */
    public function learnFromFeedback(string $text, string $correctCategory, array $metadata = []): void
    {
        // Store feedback for model improvement
        try {
            $this->db->insert('email_classification_feedback', [
                'text' => substr($text, 0, 5000),
                'predicted_category' => $this->classify($text)['category'],
                'correct_category' => $correctCategory,
                'metadata' => json_encode($metadata),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('EmailClassificationService learnFromFeedback: ' . $e->getMessage());
        }
    }

    /**
     * Get classification stats for admin dashboard
     */
    public function getStats(): array
    {
        $stats = [];

        // Total classified
        $stats['total'] = $this->db->fetchColumn("SELECT COUNT(*) FROM email_classification_log") ?? 0;

        // By category
        $byCategory = $this->db->fetchAll("
            SELECT category, COUNT(*) as count, AVG(confidence) as avg_confidence
            FROM email_classification_log
            GROUP BY category
            ORDER BY count DESC
        ");
        $stats['by_category'] = $byCategory;

        // Spam rate
        $spamCount = $this->db->fetchColumn("SELECT COUNT(*) FROM email_classification_log WHERE category = 'spam'") ?? 0;
        $total = $stats['total'] ?: 1;
        $stats['spam_rate'] = round(($spamCount / $total) * 100, 1);

        // Average confidence
        $avgConf = $this->db->fetchColumn("SELECT AVG(confidence) FROM email_classification_log");
        $stats['avg_confidence'] = round($avgConf ?? 0, 1);

        return $stats;
    }
}