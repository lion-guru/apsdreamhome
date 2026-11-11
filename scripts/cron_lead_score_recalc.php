<?php
/**
 * cron_lead_score_recalc.php — daily lead score recalibration (AI employee pipeline).
 * Recalculates scores for open leads (top 200), keeps scores fresh.
 * Usage: php scripts/cron_lead_score_recalc.php [--limit=200]
 */
require_once dirname(__DIR__) . '/config/bootstrap_cli.php';

$limit = 200;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = max(10, (int) substr($arg, 8));
    }
}

$db = App\Core\Database\Database::getInstance();
$svc = new App\Services\LeadScoringService();

$leads = $db->fetchAll("SELECT id FROM leads WHERE deleted_at IS NULL AND status NOT IN ('closed_won','closed_lost') ORDER BY id DESC LIMIT ?", [$limit]) ?: [];
echo "Recalcing " . count($leads) . " open leads (limit $limit)\n";

$done = 0;
$failed = 0;
foreach ($leads as $row) {
    try {
        $r = $svc->calculateScore((int) $row['id']);
        if (!empty($r['success'])) $done++;
        else $failed++;
    } catch (Throwable $e) {
        $failed++;
    }
}
echo "Done: $done scored, $failed failed/skipped\n";
exit(0);
