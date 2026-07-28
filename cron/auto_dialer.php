<?php
// Auto-Dialer Cron — Processes scheduled AI calls automatically
// Features: pending calls, calling hours check, agent limits, EMI reminders
// Run: php cron/auto_dialer.php
// Schedule: */5 * * * * via cron

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;
use App\Services\Voice\AsteriskService;
use App\Services\Voice\VoiceCallService;

set_time_limit(300);
date_default_timezone_set('Asia/Kolkata');

$_LOG_DIR = __DIR__ . '/logs';
if (!is_dir($_LOG_DIR)) mkdir($_LOG_DIR, 0755, true);
$_LOG_FILE = $_LOG_DIR . '/auto_dialer_' . date('Y-m-d') . '.log';

function dialerLog(string $msg) {
    global $_LOG_FILE;
    $line = date('Y-m-d H:i:s') . " - $msg\n";
    file_put_contents($_LOG_FILE, $line, FILE_APPEND);
    echo $line;
}

dialerLog("=== Auto-Dialer Started ===");

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Set tenant context for service compatibility
    $cronTenantId = 1;
    if (class_exists('\App\Core\Middleware\TenantContext')) {
        \App\Core\Middleware\TenantContext::setById($cronTenantId, $pdo);
    }
    $cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
    $cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
    $cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

    $asterisk = new AsteriskService();
    $voiceService = new VoiceCallService();

    // ── Step 1: Check calling hours ──
    $hour = (int)date('H');
    if ($hour < 9 || $hour >= 20) {
        dialerLog("Outside calling hours (9AM-8PM). Skipping.");
        exit(0);
    }

    // ── Step 2: Check Asterisk connectivity ──
    $connected = $asterisk->ping();
    if (!$connected) {
        dialerLog("Asterisk AMI not reachable. Skipping this cycle.");
        exit(0);
    }
    dialerLog("Asterisk AMI connected.");

    // ── Step 3: Get active agents with capacity ──
    $agents = $db->fetchAll(
        "SELECT agent_id, agent_name, current_calls, max_concurrent_calls, daily_call_limit, 
                total_calls_made, status
         FROM ai_calling_agents 
         WHERE status = 'active' AND current_calls < max_concurrent_calls"
    );

    if (empty($agents)) {
        dialerLog("No agents available (all at capacity or inactive).");
        exit(0);
    }
    dialerLog("Available agents: " . count($agents));

    // ── Step 4: Get pending scheduled calls ──
    $pendingCalls = $db->fetchAll(
        "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.property_interest
         FROM ai_calling_schedule s
         LEFT JOIN leads l ON l.id = s.lead_id
         WHERE s.status = 'pending' 
           AND s.scheduled_date <= CURDATE()
           AND (s.scheduled_time <= DATE_FORMAT(NOW(), '%H:%i:%s') OR s.scheduled_date < CURDATE())
           AND s.attempt_count < s.max_attempts
         ORDER BY 
           FIELD(s.priority, 'urgent', 'high', 'medium', 'low'),
           s.scheduled_date ASC, 
           s.scheduled_time ASC
         LIMIT 50"
    );

    dialerLog("Pending calls found: " . count($pendingCalls));

    // ── Step 5: Process each call ──
    $agentIndex = 0;
    $callsInitiated = 0;
    $callsSkipped = 0;
    $agentCount = count($agents);

    foreach ($pendingCalls as $call) {
        $agent = $agents[$agentIndex % $agentCount];
        $agentIndex++;

        // Check agent daily limit
        if ($agent['total_calls_made'] >= $agent['daily_call_limit']) {
            $agentName = $agent['agent_name'];
            dialerLog("Agent {$agentName} reached daily limit. Skipping.");
            $callsSkipped++;
            continue;
        }

        // Check agent concurrency
        if ($agent['current_calls'] >= $agent['max_concurrent_calls']) {
            $agentName = $agent['agent_name'];
            dialerLog("Agent {$agentName} at max concurrency. Skipping.");
            $callsSkipped++;
            continue;
        }

        $phone = $call['phone'] ?: $call['lead_phone'];
        if (empty($phone)) {
            dialerLog("No phone number for schedule #{$call['id']}. Skipping.");
            $db->execute(
                "UPDATE ai_calling_schedule SET status = 'failed', result_notes = 'No phone number', updated_at = NOW() WHERE id = ?{$cronTenantSql}",
                [$call['id']]
            );
            continue;
        }

        // Initiate call via VoiceCallService
        $result = $voiceService->initiateCall($call['id']);

        if ($result['success']) {
            $script = $call['script_template'] ?? 'default';
            $leadName = $call['lead_name'] ?? 'Unknown';
            $sessionId = $result['session_id'] ?? '';
            $scheduleId = $call['id'];
            $leadId = $call['lead_id'] ?? '';

            $callResult = $asterisk->makeCall($phone, $script, [
                'caller_id' => '',
                'variables' => [
                    'SCHEDULE_ID' => (string)$scheduleId,
                    'SESSION_ID' => (string)$sessionId,
                    'LEAD_ID' => (string)$leadId,
                    'LEAD_NAME' => $leadName,
                ]
            ]);

            if ($callResult['success']) {
                $callId = $callResult['call_id'];
                $agentName = $agent['agent_name'];
                dialerLog("OK Call initiated: {$leadName} ($phone) via Agent {$agentName} CallID: {$callId}");
                $callsInitiated++;
            } else {
                $failMsg = $callResult['message'] ?? 'unknown';
                dialerLog("FAIL Call failed: {$leadName} ($phone) - {$failMsg}");
                $db->execute(
                    "UPDATE ai_calling_schedule SET status = 'pending', attempt_count = attempt_count - 1 WHERE id = ?{$cronTenantSql}",
                    [$call['id']]
                );
            }
        } else {
            $errMsg = $result['error'] ?? 'unknown';
            dialerLog("Session failed for schedule #{$call['id']}: {$errMsg}");
        }

        // Small delay between calls
        usleep(500000);
    }

    // ── Step 6: Process EMI reminder calls ──
    dialerLog("Checking EMI reminders...");
    
    $overdueEMIs = $db->fetchAll(
        "SELECT bps.*, pb.customer_id, u.name as customer_name, u.phone as customer_phone,
                p.plot_number, c.name as colony_name
         FROM booking_payment_schedules bps
         JOIN plot_bookings pb ON pb.id = bps.booking_id
         JOIN users u ON u.id = pb.customer_id
         LEFT JOIN plots p ON p.id = pb.plot_id
         LEFT JOIN colonies c ON c.id = p.colony_id
         WHERE bps.status IN ('pending', 'overdue')
           AND bps.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
           AND bps.due_date >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)
           AND (bps.reminder_count IS NULL OR bps.reminder_count < 3)
         ORDER BY bps.due_date ASC
         LIMIT 20"
    );

    $emiCount = count($overdueEMIs);
    dialerLog("Overdue/upcoming EMI installments: {$emiCount}");

    foreach ($overdueEMIs as $emi) {
        if (empty($emi['customer_phone'])) continue;

        $daysOverdue = (int)((strtotime($emi['due_date']) - strtotime(date('Y-m-d'))) / 86400);
        $amount = number_format($emi['amount'], 0);
        
        if ($daysOverdue < 0) {
            $script = 'emi_overdue';
            $priority = 'high';
        } elseif ($daysOverdue == 0) {
            $script = 'emi_today';
            $priority = 'urgent';
        } else {
            $script = 'emi_upcoming';
            $priority = 'medium';
        }

        // Check if already scheduled
        $existing = $db->fetch(
            "SELECT id FROM ai_calling_schedule 
             WHERE lead_id = ? AND status IN ('pending', 'processing') 
               AND script_template = ? AND DATE(scheduled_date) = CURDATE()",
            [$emi['customer_id'], $script]
        );

        if ($existing) continue;

        $plotNum = $emi['plot_number'] ?? '';
        $colonyName = $emi['colony_name'] ?? '';
        $custName = $emi['customer_name'];
        $notesData = json_encode([
            'type' => 'emi_reminder',
            'installment_id' => $emi['id'],
            'booking_id' => $emi['booking_id'],
            'amount' => $emi['amount'],
            'due_date' => $emi['due_date'],
            'days_overdue' => $daysOverdue,
            'customer_name' => $custName,
            'plot_number' => $plotNum,
            'colony_name' => $colonyName,
        ]);

        $agentId = $agents[0]['agent_id'];
        $db->execute(
            "INSERT INTO ai_calling_schedule 
             (lead_id, phone, priority, scheduled_date, scheduled_time, ai_agent_id, 
              script_template, max_attempts, status, notes, created_by, created_at, updated_at{$cronTenantCol})
             VALUES (?, ?, ?, CURDATE(), DATE_FORMAT(NOW() + INTERVAL 10 MINUTE, '%H:%i:%s'), 
                     ?, ?, 3, 'pending', ?, 0, NOW(), NOW(){$cronTenantVal})",
            [
                $emi['customer_id'],
                $emi['customer_phone'],
                $priority,
                $agentId,
                $script,
                $notesData,
            ]
        );

        $db->execute(
            "UPDATE booking_payment_schedules SET reminder_count = COALESCE(reminder_count, 0) + 1, last_reminder_at = NOW() WHERE id = ?{$cronTenantSql}",
                [$emi['id']]
        );

        dialerLog("EMI reminder: {$custName} Rs{$amount}, {$daysOverdue}d overdue - {$script}");
    }

    // ── Step 7: Summary ──
    $summaryFile = $_LOG_DIR . '/dialer_summary_' . date('Y-m-d') . '.json';
    $summary = [
        'time' => date('Y-m-d H:i:s'),
        'initiated' => $callsInitiated,
        'skipped' => $callsSkipped,
        'emi_reminders' => $emiCount,
        'agents' => $agentCount,
    ];
    $existingSummary = [];
    if (file_exists($summaryFile)) {
        $existingSummary = json_decode(file_get_contents($summaryFile), true) ?: [];
    }
    $existingSummary[] = $summary;
    file_put_contents($summaryFile, json_encode($existingSummary, JSON_PRETTY_PRINT));

    dialerLog("=== Summary: {$callsInitiated} initiated, {$callsSkipped} skipped, {$emiCount} EMI reminders ===");

} catch (\Exception $e) {
    $errMsg = $e->getMessage();
    dialerLog("FATAL ERROR: {$errMsg}");
    exit(1);
}

dialerLog("=== Auto-Dialer Completed ===\n");
exit(0);
