<?php
/**
 * Auto-score all unscored leads
 * Run via cron: php scripts/_cron_lead_scoring.php
 * Or schedule in scheduled_tasks table
 */
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

use App\Core\Database\Database;
use App\Services\AI\LeadScorer;

$db = Database::getInstance();
$scorer = new LeadScorer($db);

echo "=== Lead Auto-Scoring Cron ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// Get unscored leads or leads scored > 7 days ago
$leads = $db->fetchAll(
    "SELECT l.id, l.name, l.status, l.created_at,
            als.score as last_score, als.scored_at
     FROM leads l
     LEFT JOIN ai_lead_scores als ON l.id = als.lead_id
     WHERE als.id IS NULL
        OR als.scored_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
     ORDER BY l.created_at DESC
     LIMIT 100"
);

echo "Leads to score: " . count($leads) . "\n\n";

$scored = 0;
$failed = 0;

foreach ($leads as $lead) {
    try {
        $result = $scorer->score($lead['id']);
        if (!isset($result['error'])) {
            echo "  [{$result['grade']}] {$lead['name']}: {$result['score']}/100\n";
            $scored++;
        } else {
            echo "  SKIP: {$lead['name']}: {$result['error']}\n";
            $failed++;
        }
    } catch (\Exception $e) {
        echo "  ERROR: {$lead['name']}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n=== Results ===\n";
echo "Scored: $scored\n";
echo "Failed: $failed\n";
echo "Completed: " . date('Y-m-d H:i:s') . "\n";
