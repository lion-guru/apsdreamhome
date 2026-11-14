<?php
/**
 * AI Auto-Dialer Cron - runs the scheduled AI outbound calls.
 *
 * Usage:
 *   php scripts/cron_ai_autodialer.php            # Dry-run (prints what it would dial)
 *   php scripts/cron_ai_autodialer.php --execute  # Actually places calls
 *   php scripts/cron_ai_autodialer.php --execute --force  # Ignore business-hours window
 *
 * SAFETY: default is dry-run. Placing real outbound calls to customers requires
 * an explicit --execute flag. The Twilio trial account dials only verified numbers;
 * test-lead numbers must be Twilio-verified on the account or calls fail with 21219.
 *
 * Schema (verified): ai_calling_schedule(
 *   id, campaign_id, tenant_id, lead_id, user_id,
 *   phone, priority, scheduled_date, scheduled_time, script_template,
 *   max_attempts, attempt_count, status ENUM('pending','processing','completed','failed','cancelled')
 * )
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\AI\VoiceService;
use App\Core\Database\Database;

$execute = in_array('--execute', $argv, true);
$force   = in_array('--force',   $argv, true);

echo "=== AI Auto-Dialer Cron ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Mode: " . ($execute ? 'EXECUTE' : 'DRY-RUN') . "\n";

// Business hours (9 AM - 8 PM) unless forced
$hour = (int) date('H');
if (!$force && ($hour < 9 || $hour >= 20)) {
    echo "Outside business hours (9 AM - 8 PM). Skipping. Use --force to override.\n";
    exit(0);
}

$db = Database::getInstance()->getConnection();

// Check provider is reachable before doing anything
$svc = new VoiceService();
$status = $svc->getProvidersStatus();
if (empty($status)) {
    echo "No voice providers configured. Nothing to call.\n";
    echo "(voice_provider_configs is empty - seed it from /admin/service-configs)\n";
    exit(0);
}
echo "Providers available: " . implode(', ', array_keys($status)) . "\n";

// Fail-safe verify: ensure the target table + columns exist before querying
try {
    $stmt = $db->prepare("DESCRIBE ai_calling_schedule");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $cols[] = isset($c['Field']) ? $c['Field'] : reset($c);
}
} catch (\Throwable $e) {
    echo "ERROR: ai_calling_schedule unavailable: " . $e->getMessage() . "\n";
    exit(1);
}
foreach (['phone', 'status', 'scheduled_date', 'scheduled_time', 'max_attempts', 'attempt_count'] as $needed) {
    if (!in_array($needed, $cols, true)) {
        echo "ERROR: ai_calling_schedule is missing required column '{$needed}'.\n";
        exit(1);
    }
}

// Find calls due now (today, scheduled time reached, attempts not exhausted)
$stmt = $db->prepare(
    "SELECT id, lead_id, phone, script_template, attempt_count, max_attempts
       FROM ai_calling_schedule
      WHERE status = 'pending'
        AND scheduled_date <= CURDATE()
        AND (scheduled_time IS NULL OR scheduled_time <= CURTIME())
        AND attempt_count < max_attempts
      ORDER BY
        FIELD(priority, 'urgent', 'high', 'medium', 'low') ASC,
        id ASC
      LIMIT 10"
);
$stmt->execute();
$due = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($due)) {
    echo "No due calls to process.\n";
    exit(0);
}

echo "Found " . count($due) . " due call(s).\n";

$processed = 0;
$failed = 0;
foreach ($due as $call) {
    $id = (int) $call['id'];
    $attemptsLeft = (int) $call['max_attempts'] - (int) $call['attempt_count'];

    if (!$execute) {
        echo "  DRY-RUN id={$id} phone={$call['phone']} script={$call['script_template']} attempts_left={$attemptsLeft}\n";
        continue;
    }

    try {
        $result = $svc->initiateCall($id);
        if (!empty($result['success'])) {
            $processed++;
            echo "  SENT id={$id} phone={$call['phone']} sid=" . ($result['call_sid'] ?? '?') . "\n";
        } else {
            $failed++;
            echo "  FAIL id={$id} error=" . ($result['error'] ?? 'unknown') . "\n";
        }
    } catch (\Throwable $e) {
        $failed++;
        echo "  ERROR id={$id}: " . $e->getMessage() . "\n";
    }
}

echo "\nProcessed: $processed | Failed: $failed\n";

// Stuck-call watchdog: processing > 15 min without completion = stale
$stmt = $db->prepare(
    "UPDATE ai_calling_schedule
        SET status = 'failed',
            result_notes = 'Timed out (no status callback within 15 min)',
            updated_at = NOW()
      WHERE status = 'processing'
        AND updated_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
);
$stmt->execute();
$timedOut = $stmt->rowCount();
if ($timedOut > 0) {
    echo "Marked $timedOut stale call(s) as failed.\n";
}

exit(0);
