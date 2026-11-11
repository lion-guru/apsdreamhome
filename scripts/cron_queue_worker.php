<?php
/**
 * Cron: Queue Worker for Email & SMS queues
 * Processes pending items in email_queue and sms_queue tables
 * 
 * Usage: php scripts/cron_queue_worker.php [--limit=50] [--dry-run]
 * Schedule: Every 2 minutes via Task Scheduler
 */
require_once __DIR__ . '/../config/bootstrap.php';

$limit = 50;
$dryRun = false;
foreach ($argv as $arg) {
    if (strpos($arg, '--limit=') === 0) $limit = (int)substr($arg, 8);
    if ($arg === '--dry-run') $dryRun = true;
}

$db = \App\Core\Database\Database::getInstance();
echo "=== Queue Worker ===\n";
echo "Limit: $limit, Dry-run: " . ($dryRun ? 'YES' : 'NO') . "\n\n";

// Process SMS queue
$smsProcessed = 0;
$smsFailed = 0;
try {
    $smsItems = $db->fetchAll("
        SELECT * FROM sms_queue 
        WHERE status = 'pending' OR status = 'pending_manual'
        ORDER BY created_at ASC
        LIMIT ?
    ", [$limit]);

    echo "SMS queue: " . count($smsItems) . " pending\n";

    foreach ($smsItems as $item) {
        if ($dryRun) {
            echo "  [DRY] SMS #{$item['id']} to {$item['phone']}\n";
            continue;
        }
        try {
            // Check if SMSService is available and configured
            if (class_exists('\App\Services\Communication\SMSService')) {
                $smsService = new \App\Services\Communication\SMSService();
                $result = $smsService->sendSMS($item['phone'], $item['message']);
                if ($result) {
                    $db->execute("UPDATE sms_queue SET status = 'sent', sent_at = NOW() WHERE id = ?", [$item['id']]);
                    $smsProcessed++;
                    echo "  SENT SMS #{$item['id']} to {$item['phone']}\n";
                } else {
                    $db->execute("UPDATE sms_queue SET status = 'failed', error_message = 'Provider returned false' WHERE id = ?", [$item['id']]);
                    $smsFailed++;
                    echo "  FAILED SMS #{$item['id']}: provider returned false\n";
                }
            } else {
                // No SMS provider configured - mark as queued for manual sending
                $db->execute("UPDATE sms_queue SET status = 'queued_no_provider' WHERE id = ?", [$item['id']]);
                echo "  QUEUED SMS #{$item['id']} (no provider configured)\n";
            }
        } catch (\Throwable $e) {
            $db->execute("UPDATE sms_queue SET status = 'failed', error_message = ? WHERE id = ?", [$e->getMessage(), $item['id']]);
            $smsFailed++;
            echo "  ERROR SMS #{$item['id']}: " . $e->getMessage() . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "SMS queue error: " . $e->getMessage() . "\n";
}

echo "\n";

// Process Email queue
$emailProcessed = 0;
$emailFailed = 0;
try {
    $emailItems = $db->fetchAll("
        SELECT * FROM email_queue 
        WHERE status = 'pending'
        ORDER BY created_at ASC
        LIMIT ?
    ", [$limit]);

    echo "Email queue: " . count($emailItems) . " pending\n";

    foreach ($emailItems as $item) {
        if ($dryRun) {
            echo "  [DRY] Email #{$item['id']} to {$item['to_email']}\n";
            continue;
        }
        try {
            if (class_exists('\App\Services\Communication\EmailSenderService')) {
                $emailService = new \App\Services\Communication\EmailSenderService();
                $result = $emailService->send($item['to_email'], $item['subject'], $item['body']);
                if ($result) {
                    $db->execute("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?", [$item['id']]);
                    $emailProcessed++;
                    echo "  SENT Email #{$item['id']} to {$item['to_email']}\n";
                } else {
                    $db->execute("UPDATE email_queue SET status = 'failed', error_message = 'Provider returned false' WHERE id = ?", [$item['id']]);
                    $emailFailed++;
                    echo "  FAILED Email #{$item['id']}: provider returned false\n";
                }
            } else {
                $db->execute("UPDATE email_queue SET status = 'queued_no_provider' WHERE id = ?", [$item['id']]);
                echo "  QUEUED Email #{$item['id']} (no provider configured)\n";
            }
        } catch (\Throwable $e) {
            $db->execute("UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?", [$e->getMessage(), $item['id']]);
            $emailFailed++;
            echo "  ERROR Email #{$item['id']}: " . $e->getMessage() . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "Email queue error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE: SMS sent=$smsProcessed failed=$smsFailed | Email sent=$emailProcessed failed=$emailFailed ===\n";