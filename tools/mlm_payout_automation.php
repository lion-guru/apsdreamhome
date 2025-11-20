<?php
/**
 * MLM Payout Automation Script
 * ----------------------------
 * Usage:
 *   php tools/mlm_payout_automation.php [--dry-run] [--force]
 *       [--date-from="2025-01-01"] [--date-to="2025-01-31"]
 *       [--min-amount=100000] [--max-items=250]
 *       [--required-approvals=2] [--batch-reference="AUTO-202501"]
 *       [--lookback-days=7] [--interval-hours=24]
 *
 * This script creates payout batches for approved commissions using
 * the configured MLM settings (see docs/operations/payout-automation.md).
 * It can be scheduled via cron or Windows Task Scheduler.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../app/services/PayoutService.php';
require_once __DIR__ . '/../app/services/MlmSettings.php';
require_once __DIR__ . '/../app/services/NotificationService.php';

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$options = getopt('', [
    'dry-run',
    'force',
    'date-from:',
    'date-to:',
    'min-amount:',
    'max-items:',
    'required-approvals:',
    'batch-reference:',
    'lookback-days:',
    'interval-hours:'
]);

$dryRun = array_key_exists('dry-run', $options);
$force = array_key_exists('force', $options);

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

if (!$conn) {
    fwrite(STDERR, "Unable to connect to database.\n");
    exit(1);
}

$enabled = (bool) MlmSettings::getInt('payout_automation_enabled', 0);
if (!$enabled && !$force) {
    echo "Automation disabled via settings (payout_automation_enabled = 0). Use --force to override.\n";
    exit(0);
}

$intervalHours = isset($options['interval-hours'])
    ? max(1, (int) $options['interval-hours'])
    : max(1, MlmSettings::getInt('payout_automation_interval_hours', 24));

$lastRun = MlmSettings::get('payout_automation_last_run', null);
if (!$force && $lastRun) {
    $elapsedSeconds = time() - strtotime($lastRun);
    if ($elapsedSeconds < ($intervalHours * 3600)) {
        $hoursRemaining = (($intervalHours * 3600) - $elapsedSeconds) / 3600;
        echo sprintf(
            "Skipping automation: last run at %s, %.2f hour(s) remaining in interval.%s\n",
            $lastRun,
            $hoursRemaining,
            PHP_EOL
        );
        exit(0);
    }
}

$filters = [];

if (!empty($options['date-from'])) {
    $filters['date_from'] = $options['date-from'];
} else {
    $lookbackDays = isset($options['lookback-days'])
        ? max(0, (int) $options['lookback-days'])
        : max(0, MlmSettings::getInt('payout_automation_lookback_days', 7));

    if ($lookbackDays > 0) {
        $filters['date_from'] = (new DateTimeImmutable(sprintf('-%d days', $lookbackDays)))->format('Y-m-d H:i:s');
    }
}

if (!empty($options['date-to'])) {
    $filters['date_to'] = $options['date-to'];
}

$minAmount = isset($options['min-amount'])
    ? (float) $options['min-amount']
    : (float) MlmSettings::getFloat('payout_automation_min_amount', 0.0);

if ($minAmount > 0) {
    $filters['min_amount'] = $minAmount;
}

$maxItemsSetting = isset($options['max-items'])
    ? (int) $options['max-items']
    : MlmSettings::getInt('payout_automation_max_items', 0);

if ($maxItemsSetting > 0) {
    $filters['max_items'] = $maxItemsSetting;
}

$requiredApprovals = isset($options['required-approvals'])
    ? max(1, (int) $options['required-approvals'])
    : max(1, MlmSettings::getInt('payout_automation_required_approvals', 1));
$filters['required_approvals'] = $requiredApprovals;

$batchReference = !empty($options['batch-reference'])
    ? $options['batch-reference']
    : sprintf('AUTO-%s', date('Ymd-His'));
$filters['batch_reference'] = $batchReference;

$summaryContext = [
    'filters' => [
        'date_from' => $filters['date_from'] ?? null,
        'date_to' => $filters['date_to'] ?? null,
        'min_amount' => $filters['min_amount'] ?? 0.0,
        'max_items' => $filters['max_items'] ?? null,
        'required_approvals' => $filters['required_approvals'],
        'batch_reference' => $filters['batch_reference'],
    ],
    'interval_hours' => $intervalHours,
    'force' => $force,
    'dry_run' => $dryRun,
];

if ($dryRun) {
    $preview = previewApprovedCommissions($conn, $filters);
    $summaryContext['preview'] = $preview;
    echo "=== MLM Payout Automation (Dry Run) ===\n";
    echo sprintf("Eligible commissions: %d\n", $preview['count']);
    echo sprintf("Total amount: ₹%s\n", number_format($preview['total_amount'], 2));
    if (!empty($preview['earliest'])) {
        echo sprintf("Earliest commission: %s\n", $preview['earliest']);
    }
    if (!empty($preview['latest'])) {
        echo sprintf("Latest commission: %s\n", $preview['latest']);
    }
    echo sprintf(
        "Minimum amount %s met (threshold ₹%s).\n",
        $preview['meets_min_amount'] ? 'IS' : 'IS NOT',
        number_format($minAmount, 2)
    );
    echo "No database changes were made.\n";
    exit(0);
}

$service = new PayoutService();

try {
    $result = $service->createBatch($filters);
    MlmSettings::set('payout_automation_last_attempt', date('c'));
    MlmSettings::set('payout_automation_last_context', json_encode($summaryContext, JSON_UNESCAPED_UNICODE));

    if (!empty($result['success'])) {
        MlmSettings::set('payout_automation_last_run', date('c'));
        MlmSettings::set('payout_automation_last_result', json_encode($result, JSON_UNESCAPED_UNICODE));

        echo "Payout batch created successfully." . PHP_EOL;
        echo sprintf("Batch ID: %s\n", $result['batch_id']);
        echo sprintf("Reference: %s\n", $filters['batch_reference']);
        echo sprintf("Records: %s\n", $result['total_records']);
        echo sprintf("Total Amount: ₹%s\n", number_format($result['total_amount'], 2));
        echo sprintf("Required Approvals: %d\n", $filters['required_approvals']);
        exit(0);
    }

    $message = $result['message'] ?? 'Unknown error creating batch.';
    MlmSettings::set('payout_automation_last_error', $message);
    echo "No batch created: {$message}" . PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    MlmSettings::set('payout_automation_last_error', $e->getMessage());
    fwrite(STDERR, 'Payout automation failed: ' . $e->getMessage() . PHP_EOL);
    exit(2);
}

/**
 * Preview approved commissions that would be included in a payout batch.
 */
function previewApprovedCommissions(mysqli $conn, array $filters): array
{
    $conditions = [];
    $types = '';
    $params = [];

    if (!empty($filters['date_from'])) {
        $conditions[] = 'created_at >= ?';
        $types .= 's';
        $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $conditions[] = 'created_at <= ?';
        $types .= 's';
        $params[] = $filters['date_to'];
    }

    $sql = 'SELECT amount, created_at FROM mlm_commission_ledger WHERE status = \"approved\"';

    if ($conditions) {
        $sql .= ' AND ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY created_at ASC';

    $limit = null;
    if (!empty($filters['max_items'])) {
        $limit = (int) $filters['max_items'];
        if ($limit > 0) {
            $sql .= ' LIMIT ?';
            $types .= 'i';
            $params[] = $limit;
        }
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new RuntimeException('Unable to prepare preview query: ' . $conn->error);
    }

    if ($types !== '') {
        bindStatementParams($stmt, $types, $params);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $count = count($result);
    $total = array_reduce($result, static function ($carry, $row) {
        return $carry + (float) ($row['amount'] ?? 0);
    }, 0.0);

    return [
        'count' => $count,
        'total_amount' => $total,
        'earliest' => $count ? ($result[0]['created_at'] ?? null) : null,
        'latest' => $count ? ($result[$count - 1]['created_at'] ?? null) : null,
        'meets_min_amount' => $total >= (float) ($filters['min_amount'] ?? 0.0),
    ];
}

/**
 * Bind parameters to a mysqli statement using references.
 */
function bindStatementParams(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types === '') {
        return;
    }

    $bindParams = [];
    $bindParams[] = $types;

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    if (!call_user_func_array([$stmt, 'bind_param'], $bindParams)) {
        throw new RuntimeException('Failed to bind parameters: ' . $stmt->error);
    }
}
