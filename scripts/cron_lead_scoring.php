<?php
/**
 * Cron: Auto-lead-scoring for unscored leads
 * Run every 15 minutes via Task Scheduler or cron
 * 
 * Usage: php scripts/cron_lead_scoring.php [--limit=50] [--dry-run]
 */
require_once __DIR__ . '/../config/bootstrap.php';

$limit = 50;
$dryRun = false;
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) $limit = (int)substr($arg, 8);
    if ($arg === '--dry-run') $dryRun = true;
}

$db = \App\Core\Database\Database::getInstance();
$svc = new \App\Services\LeadScoringService();

echo "=== Lead Scoring Cron ===\n";
echo "Limit: $limit, Dry-run: " . ($dryRun ? 'YES' : 'NO') . "\n";

// Find unscored leads (no lead_score or score 0)
$unscored = $db->fetchAll("
    SELECT id FROM leads 
    WHERE (lead_score IS NULL OR lead_score = 0) 
      AND deleted_at IS NULL
    ORDER BY created_at DESC
    LIMIT ?
", [$limit]);

echo "Unscored leads found: " . count($unscored) . "\n";

$scored = 0;
$failed = 0;
foreach ($unscored as $lead) {
    $leadId = (int)$lead['id'];
    if ($dryRun) {
        echo "  [DRY] Would score lead #$leadId\n";
        continue;
    }
    try {
        $score = $svc->calculateScore($leadId);
        $scored++;
        echo "  Scored lead #$leadId => $score\n";
    } catch (\Throwable $e) {
        $failed++;
        echo "  FAILED lead #$leadId: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE: scored=$scored, failed=$failed, total=" . count($unscored) . " ===\n";