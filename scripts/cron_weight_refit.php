<?php
/**
 * cron_weight_refit.php — weekly scoring weight calibration.
 * Compares score tier vs won/loss outcome per lead, prints refit signal.
 * Read-only (calibration report, no weight writes — admin approves changes).
 * Usage: php scripts/cron_weight_refit.php
 */
require_once dirname(__DIR__) . '/config/bootstrap_cli.php';

$db = App\Core\Database\Database::getInstance();

// Recent scores joined to outcomes
$rows = $db->fetchAll("
    SELECT ls.score, ls.calculated_at, l.status
    FROM lead_scores ls
    JOIN leads l ON l.id = ls.lead_id
    WHERE l.deleted_at IS NULL AND l.status IN ('closed_won', 'closed_lost')
      AND ls.calculated_at > DATE_SUB(NOW(), INTERVAL 90 DAY)
") ?: [];
if (empty($rows)) {
    echo "No recent closed outcomes with scores — nothing to calibrate\n";
    exit(0);
}

$buckets = ['hot' => ['won' => 0, 'lost' => 0], 'warm' => ['won' => 0, 'lost' => 0], 'cool' => ['won' => 0, 'lost' => 0], 'cold' => ['won' => 0, 'lost' => 0]];
foreach ($rows as $r) {
    $score = (int) $r['score'];
    $tier = $score >= 80 ? 'hot' : ($score >= 60 ? 'warm' : ($score >= 40 ? 'cool' : 'cold'));
    $outcome = $r['status'] === 'closed_won' ? 'won' : 'lost';
    $buckets[$tier][$outcome]++;
}

echo "Calibration report (90d score -> outcome):\n";
foreach ($buckets as $tier => $b) {
    $total = $b['won'] + $b['lost'];
    $rate = $total > 0 ? round($b['won'] / $total * 100, 1) : 0;
    echo "  $tier: won {$b['won']} / lost {$b['lost']} = {$rate}% close rate\n";
}

$hot = $buckets['hot'];
$hotRate = ($hot['won'] + $hot['lost']) > 0 ? $hot['won'] / ($hot['won'] + $hot['lost']) : -1;
if ($hotRate > 0 && $hotRate < 0.7) {
    echo "SIGNAL: Hot tier close rate {$hotRate} < 70% — weights over-confident, reduce engagement weight\n";
} elseif ($hotRate >= 0.7) {
    echo "SIGNAL: Hot tier healthy (>=70%)\n";
} else {
    echo "SIGNAL: insufficient hot samples\n";
}
exit(0);
