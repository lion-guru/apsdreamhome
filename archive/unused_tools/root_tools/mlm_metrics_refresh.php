<?php
/**
 * MLM Metrics & Leaderboard Refresh Script
 * ----------------------------------------
 * Usage:
 *   php tools/mlm_metrics_refresh.php [--period=2025-01] [--start=YYYY-MM-DD --end=YYYY-MM-DD]
 *        [--metrics-only] [--leaderboard-only] [--dry-run]
 *
 * Populates mlm_associate_metrics and mlm_leaderboard_snapshots for the
 * specified period. Defaults to the current calendar month.
 */

require_once __DIR__ . '/../includes/config.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$options = getopt('', ['period:', 'start:', 'end:', 'metrics-only', 'leaderboard-only', 'dry-run']);

try {
    [$periodStart, $periodEnd] = determinePeriod($options);
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$dryRun = array_key_exists('dry-run', $options);
$metricsOnly = array_key_exists('metrics-only', $options);
$leaderboardOnly = array_key_exists('leaderboard-only', $options);

if ($metricsOnly && $leaderboardOnly) {
    fwrite(STDERR, "Choose either --metrics-only or --leaderboard-only, not both.\n");
    exit(1);
}

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    fwrite(STDERR, "Unable to connect to database.\n");
    exit(1);
}

$metricsSummary = null;
if (!$leaderboardOnly) {
    $metricsSummary = refreshAssociateMetrics($conn, $periodStart, $periodEnd, $dryRun);
}

$leaderboardSummary = null;
if (!$metricsOnly) {
    $leaderboardSummary = refreshLeaderboards($conn, $periodStart, $periodEnd, $dryRun);
}

fwrite(STDOUT, "\n=== MLM Metrics Refresh Complete ===\n");
fwrite(STDOUT, "Period: {$periodStart} to {$periodEnd}\n");
if ($metricsSummary) {
    fwrite(STDOUT, sprintf("Metrics processed: %d users (sales ₹%s, commissions ₹%s, recruits %d)\n",
        $metricsSummary['users'],
        number_format($metricsSummary['sales_total'], 2),
        number_format($metricsSummary['commission_total'], 2),
        $metricsSummary['recruits_total']
    ));
}
if ($leaderboardSummary) {
    foreach ($leaderboardSummary as $metricType => $info) {
        fwrite(STDOUT, sprintf(
            "Leaderboard %-20s => %d records (run #%d status: %s)\n",
            $metricType,
            $info['records'],
            $info['run_id'],
            $info['status']
        ));
    }
}

exit(0);

// -----------------------------------------------------------------------------
// Helper functions
// -----------------------------------------------------------------------------

function determinePeriod(array $options): array
{
    $today = new DateTimeImmutable('today');

    if (!empty($options['start']) || !empty($options['end'])) {
        if (empty($options['start']) || empty($options['end'])) {
            throw new InvalidArgumentException('Both --start and --end must be provided when using explicit dates.');
        }
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $options['start']);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $options['end']);
        if (!$start || !$end) {
            throw new InvalidArgumentException('Invalid --start or --end date format. Use YYYY-MM-DD.');
        }
    } elseif (!empty($options['period'])) {
        $period = DateTimeImmutable::createFromFormat('Y-m', $options['period']);
        if (!$period) {
            throw new InvalidArgumentException('Invalid --period format. Use YYYY-MM.');
        }
        $start = $period->setTime(0, 0, 0)->modify('first day of this month');
        $end = $period->setTime(0, 0, 0)->modify('last day of this month');
    } else {
        $start = $today->modify('first day of this month');
        $end = $today->modify('last day of this month');
    }

    if ($end < $start) {
        throw new InvalidArgumentException('Period end cannot be earlier than period start.');
    }

    return [$start->format('Y-m-d'), $end->format('Y-m-d')];
}

function refreshAssociateMetrics(mysqli $conn, string $periodStart, string $periodEnd, bool $dryRun): array
{
    $fromTs = $periodStart . ' 00:00:00';
    $toTs = $periodEnd . ' 23:59:59';

    $metrics = [];

    // Commission & sales aggregates
    $stmt = $conn->prepare(
        'SELECT beneficiary_user_id AS user_id,
                SUM(amount) AS total_commission,
                SUM(IFNULL(sale_amount, 0)) AS total_sales
         FROM mlm_commission_ledger
         WHERE created_at BETWEEN ? AND ?
           AND status IN (\'approved\', \'paid\')
         GROUP BY beneficiary_user_id'
    );
    $stmt->bind_param('ss', $fromTs, $toTs);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $userId = (int) $row['user_id'];
        $metrics[$userId] = $metrics[$userId] ?? baseMetric($userId);
        $metrics[$userId]['commissions_amount'] = (float) $row['total_commission'];
        $metrics[$userId]['sales_amount'] = (float) $row['total_sales'];
    }
    $stmt->close();

    // Recruits count
    $stmt = $conn->prepare(
        'SELECT referrer_user_id AS user_id, COUNT(*) AS recruits
         FROM mlm_referrals
         WHERE created_at BETWEEN ? AND ?
         GROUP BY referrer_user_id'
    );
    $stmt->bind_param('ss', $fromTs, $toTs);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $userId = (int) $row['user_id'];
        $metrics[$userId] = $metrics[$userId] ?? baseMetric($userId);
        $metrics[$userId]['recruits_count'] = (int) $row['recruits'];
    }
    $stmt->close();

    // Active team members (newly linked descendants)
    $stmt = $conn->prepare(
        'SELECT ancestor_user_id AS user_id, COUNT(DISTINCT descendant_user_id) AS team_members
         FROM mlm_network_tree
         WHERE created_at BETWEEN ? AND ?
         GROUP BY ancestor_user_id'
    );
    $stmt->bind_param('ss', $fromTs, $toTs);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $userId = (int) $row['user_id'];
        $metrics[$userId] = $metrics[$userId] ?? baseMetric($userId);
        $metrics[$userId]['active_team_count'] = (int) $row['team_members'];
    }
    $stmt->close();

    if (empty($metrics)) {
        return ['users' => 0, 'sales_total' => 0.0, 'commission_total' => 0.0, 'recruits_total' => 0];
    }

    // Fetch rank labels for impacted users
    $userIds = array_keys($metrics);
    $rankMap = fetchRanks($conn, $userIds);

    if (!$dryRun) {
        $insert = $conn->prepare(
            'INSERT INTO mlm_associate_metrics
             (user_id, period_start, period_end, sales_amount, commissions_amount, recruits_count, active_team_count, rank_label, snapshot_json, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                 sales_amount = VALUES(sales_amount),
                 commissions_amount = VALUES(commissions_amount),
                 recruits_count = VALUES(recruits_count),
                 active_team_count = VALUES(active_team_count),
                 rank_label = VALUES(rank_label),
                 snapshot_json = VALUES(snapshot_json),
                 updated_at = NOW()'
        );
    }

    $salesTotal = 0.0;
    $commissionTotal = 0.0;
    $recruitsTotal = 0;

    foreach ($metrics as $userId => $row) {
        $salesTotal += $row['sales_amount'];
        $commissionTotal += $row['commissions_amount'];
        $recruitsTotal += $row['recruits_count'];
        $rankLabel = $rankMap[$userId] ?? null;
        $snapshot = json_encode([
            'sales_amount' => $row['sales_amount'],
            'commissions_amount' => $row['commissions_amount'],
            'recruits_count' => $row['recruits_count'],
            'active_team_count' => $row['active_team_count'],
        ], JSON_UNESCAPED_UNICODE);

        if ($dryRun) {
            continue;
        }

        $insert->bind_param(
            'issddiiss',
            $userId,
            $periodStart,
            $periodEnd,
            $row['sales_amount'],
            $row['commissions_amount'],
            $row['recruits_count'],
            $row['active_team_count'],
            $rankLabel,
            $snapshot
        );
        $insert->execute();
    }

    if (!$dryRun) {
        $insert->close();
    }

    return [
        'users' => count($metrics),
        'sales_total' => $salesTotal,
        'commission_total' => $commissionTotal,
        'recruits_total' => $recruitsTotal,
    ];
}

function refreshLeaderboards(mysqli $conn, string $periodStart, string $periodEnd, bool $dryRun): array
{
    $snapshotDate = $periodEnd;

    $metricsStmt = $conn->prepare(
        'SELECT user_id, sales_amount, commissions_amount, recruits_count, active_team_count
         FROM mlm_associate_metrics
         WHERE period_start = ? AND period_end = ?'
    );
    $metricsStmt->bind_param('ss', $periodStart, $periodEnd);
    $metricsStmt->execute();
    $metricsResult = $metricsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $metricsStmt->close();

    if (!$metricsResult) {
        fwrite(STDOUT, "No metrics found for the specified period. Leaderboard refresh skipped.\n");
        return [];
    }

    $metricConfigurations = [
        'sales_monthly' => ['key' => 'sales_amount', 'tie' => 'commissions_amount'],
        'commission_monthly' => ['key' => 'commissions_amount', 'tie' => 'sales_amount'],
        'recruits_monthly' => ['key' => 'recruits_count', 'tie' => 'active_team_count'],
    ];

    $summary = [];

    foreach ($metricConfigurations as $metricType => $config) {
        $filtered = array_filter($metricsResult, function ($row) use ($config) {
            return (float) $row[$config['key']] > 0;
        });

        if (empty($filtered)) {
            $summary[$metricType] = ['records' => 0, 'run_id' => null, 'status' => 'skipped'];
            continue;
        }

        usort($filtered, function ($a, $b) use ($config) {
            $primary = $config['key'];
            $tie = $config['tie'];
            $comp = $b[$primary] <=> $a[$primary];
            return $comp !== 0 ? $comp : ($b[$tie] <=> $a[$tie]);
        });

        $ranked = [];
        $previousValue = null;
        $previousRank = 0;
        foreach ($filtered as $index => $row) {
            $currentValue = (float) $row[$config['key']];
            if ($previousValue !== null && abs($currentValue - $previousValue) < 0.00001) {
                $rank = $previousRank;
            } else {
                $rank = $index + 1;
                $previousValue = $currentValue;
                $previousRank = $rank;
            }
            $ranked[] = [
                'user_id' => (int) $row['user_id'],
                'rank' => $rank,
                'metric_value' => $currentValue,
                'tie_breaker' => (float) $row[$config['tie']],
                'payload' => json_encode($row, JSON_UNESCAPED_UNICODE),
            ];
        }

        if ($dryRun) {
            $summary[$metricType] = ['records' => count($ranked), 'run_id' => null, 'status' => 'dry-run'];
            continue;
        }

        $runId = logLeaderboardRun($conn, $metricType, $snapshotDate);

        $deleteStmt = $conn->prepare('DELETE FROM mlm_leaderboard_snapshots WHERE metric_type = ? AND snapshot_date = ?');
        $deleteStmt->bind_param('ss', $metricType, $snapshotDate);
        $deleteStmt->execute();
        $deleteStmt->close();

        $insertStmt = $conn->prepare(
            'INSERT INTO mlm_leaderboard_snapshots
             (snapshot_date, metric_type, user_id, run_id, rank_position, metric_value, tie_breaker, payload_json, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        foreach ($ranked as $row) {
            $insertStmt->bind_param(
                'ssiiidds',
                $snapshotDate,
                $metricType,
                $row['user_id'],
                $runId,
                $row['rank'],
                $row['metric_value'],
                $row['tie_breaker'],
                $row['payload']
            );
            $insertStmt->execute();
        }
        $insertStmt->close();

        finalizeLeaderboardRun($conn, $runId, count($ranked), 'complete');

        $summary[$metricType] = ['records' => count($ranked), 'run_id' => $runId, 'status' => 'complete'];
    }

    return $summary;
}

function baseMetric(int $userId): array
{
    return [
        'user_id' => $userId,
        'sales_amount' => 0.0,
        'commissions_amount' => 0.0,
        'recruits_count' => 0,
        'active_team_count' => 0,
    ];
}

function fetchRanks(mysqli $conn, array $userIds): array
{
    if (empty($userIds)) {
        return [];
    }
    $idList = implode(',', array_map('intval', $userIds));
    $sql = "SELECT user_id, current_level FROM mlm_profiles WHERE user_id IN ({$idList})";
    $result = $conn->query($sql);
    $rankMap = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rankMap[(int) $row['user_id']] = $row['current_level'];
        }
        $result->free();
    }
    return $rankMap;
}

function logLeaderboardRun(mysqli $conn, string $metricType, string $snapshotDate): int
{
    $stmt = $conn->prepare(
        'INSERT INTO mlm_leaderboard_runs (metric_type, snapshot_date, status, processed_records, notes, created_at)
         VALUES (?, ?, \'pending\', 0, NULL, NOW())'
    );
    $stmt->bind_param('ss', $metricType, $snapshotDate);
    $stmt->execute();
    $runId = $stmt->insert_id;
    $stmt->close();
    return $runId;
}

function finalizeLeaderboardRun(mysqli $conn, int $runId, int $records, string $status): void
{
    $stmt = $conn->prepare(
        'UPDATE mlm_leaderboard_runs SET status = ?, processed_records = ?, completed_at = NOW() WHERE id = ?'
    );
    $stmt->bind_param('sii', $status, $records, $runId);
    $stmt->execute();
    $stmt->close();
}
