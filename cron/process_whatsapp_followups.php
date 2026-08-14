<?php
/**
 * WhatsApp Follow-up Processor Cron
 * Sends WhatsApp messages after calls, EMI reminders, and lead nurture sequences
 * 
 * Run: php cron/process_whatsapp_followups.php
 * Schedule: every 15 minutes
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;
use App\Services\Communication\WhatsAppWebService;

set_time_limit(120);
date_default_timezone_set('Asia/Kolkata');

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$logFile = $logDir . '/whatsapp_followup_' . date('Y-m-d') . '.log';

function waLog(string $msg): void {
    global $logFile;
    $line = date('Y-m-d H:i:s') . " - $msg\n";
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

waLog("=== WhatsApp Follow-up Processor Started ===");

try {
    $db = Database::getInstance();
    $wa = new WhatsAppWebService();

    // Set tenant context for TenantContext consumers
    $cronTenantId = 1;
    if (class_exists('\App\Core\Middleware\TenantContext')) {
        \App\Core\Middleware\TenantContext::setById($cronTenantId, $db->getConnection());
    }
    $cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
    $cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
    $cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

    // Check WhatsApp connection
    $status = $wa->isConnected();
    if (isset($status['error']) || empty($status['connected'])) {
        waLog("WhatsApp not connected: " . ($status['error'] ?? json_encode($status)));
        waLog("Skipping this cycle. Reconnect via admin panel.");
        exit(0);
    }
    waLog("WhatsApp connected.");

    // â”€â”€ 1. Post-call follow-ups â”€â”€
    // Find completed calls where follow-up is due
    $followups = $db->fetchAll(
        "SELECT acs.id as session_id, acs.phone, acs.ai_summary, acs.customer_response,
                l.name as lead_name, l.property_interest,
                u.name as user_name
         FROM ai_call_sessions acs
         LEFT JOIN leads l ON acs.lead_id = l.id
         LEFT JOIN users u ON u.phone = acs.phone
         WHERE acs.status = 'completed'
           AND acs.customer_response IN ('interested', 'followup_needed', 'site_visit')
           AND acs.ended_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
           AND acs.ended_at <= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
         ORDER BY acs.ended_at DESC
         LIMIT 20"
    );

    waLog("Post-call follow-ups to send: " . count($followups));

    foreach ($followups as $call) {
        $phone = preg_replace('/[^0-9]/', '', $call['phone']);
        if (strlen($phone) === 10) $phone = '91' . $phone;
        if (strlen($phone) < 12) continue;

        $name = $call['lead_name'] ?: $call['user_name'] ?: 'Customer';
        $response = $call['customer_response'] ?? 'interested';
        $summary = $call['ai_summary'] ?? '';

        // Build follow-up message based on call outcome
        $message = match ($response) {
            'site_visit' => "Namaste {$name}! ðŸ� \n\n" .
                "APS Dream Home se baat hui thi aaj. Aapne site visit ke liye interest dikhaya.\n\n" .
                "Kya aapko koi specific date aur time chahiye site visit ke liye?\n\n" .
                "ðŸ“� Office: Raghunath Nagri, Gorakhpur\n" .
                "ðŸ“ž 7007444842\n\n" .
                "Reply karein ya call karein! ðŸ™�",
            'interested' => "Namaste {$name}! ðŸ�¡\n\n" .
                "APS Dream Home mein aapki baat hui thi property ke baare mein.\n\n" .
                "Humari available properties:\n" .
                "â€¢ Plots from â‚¹5 lakh onwards\n" .
                "â€¢ EMI options: 12-60 months\n" .
                "â€¢ Bank loan: 80% tak\n\n" .
                "Kya aapko pricing details chahiye? Reply karein! ðŸ˜Š",
            'followup_needed' => "Namaste {$name}! ðŸ‘‹\n\n" .
                "APS Dream Home se call hui thi. Hum aapse dobara baat karna chahte hain.\n\n" .
                "Aapko koi sawaal hai ya property ke baare mein aur jaanna hai?\n\n" .
                "ðŸ“ž 7007444842 pe call karein ya yahan reply karein! ðŸ™�",
            default => "Namaste {$name}!\n\nAPS Dream Home - Gorakhpur. Aapki baat hui thi hamari team se.\n\nKoi sawaal? Reply karein! ðŸ™�"
        };

        // Check if already sent
        $alreadySent = $db->fetch(
            "SELECT id FROM whatsapp_followup_log 
             WHERE session_id = ? AND followup_type = 'post_call' AND sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$call['session_id']]
        );
        if ($alreadySent) continue;

        // Send WhatsApp
        $result = $wa->sendMessage($phone, $message);
        $sent = !isset($result['error']);

        // Log
        $db->execute(
            "INSERT INTO whatsapp_followup_log (session_id, phone, followup_type, message, status, sent_at, created_at{$cronTenantCol})
             VALUES (?, ?, 'post_call', ?, ?, NOW(), NOW(){$cronTenantVal})",
            [$call['session_id'], $phone, $message, $sent ? 'sent' : 'failed']
        );

        waLog(($sent ? "SENT" : "FAIL") . " post-call follow-up to {$name} ({$phone})");
    }

    // â”€â”€ 2. EMI reminder WhatsApp messages â”€â”€
    $emiReminders = $db->fetchAll(
        "SELECT bps.id as installment_id, bps.amount, bps.due_date,
                pb.customer_id,
                u.name as customer_name, u.phone as customer_phone
         FROM booking_payment_schedules bps
         JOIN plot_bookings pb ON pb.id = bps.booking_id
         JOIN users u ON u.id = pb.customer_id
         WHERE bps.status IN ('pending', 'overdue')
           AND bps.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
           AND bps.due_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         ORDER BY bps.due_date ASC
         LIMIT 30"
    );

    waLog("EMI WhatsApp reminders to send: " . count($emiReminders));

    foreach ($emiReminders as $emi) {
        $phone = preg_replace('/[^0-9]/', '', $emi['customer_phone']);
        if (strlen($phone) === 10) $phone = '91' . $phone;
        if (strlen($phone) < 12) continue;

        $amount = number_format($emi['amount']);
        $dueDate = date('d M Y', strtotime($emi['due_date']));
        $daysLeft = (int)((strtotime($emi['due_date']) - strtotime(date('Y-m-d'))) / 86400);

        if ($daysLeft < 0) {
            $message = "âš ï¸� APS Dream Home - EMI Reminder\n\n" .
                "Namaste {$emi['customer_name']}!\n\n" .
                "Aapki â‚¹{$amount} ki EMI OVERDUE hai.\n" .
                "Due date tha: {$dueDate}\n\n" .
                "Kripya jald se jald payment karein.\n" .
                "ðŸ“ž 7007444842\n" .
                "ðŸ“� Raghunath Nagri, Gorakhpur\n\n" .
                "Online payment ke liye reply karein! ðŸ™�";
        } elseif ($daysLeft == 0) {
            $message = "ðŸ“… APS Dream Home - EMI Due Today\n\n" .
                "Namaste {$emi['customer_name']}!\n\n" .
                "Aaj aapki â‚¹{$amount} ki EMI due hai.\n\n" .
                "Payment karein:\n" .
                "â€¢ Cash: Raghunath Nagri office\n" .
                "â€¢ Online: UPI/Bank transfer\n\n" .
                "ðŸ“ž 7007444842 ðŸ™�";
        } else {
            $message = "ðŸ“‹ APS Dream Home - EMI Reminder\n\n" .
                "Namaste {$emi['customer_name']}!\n\n" .
                "Aapki â‚¹{$amount} ki EMI {$daysLeft} din mein due hai.\n" .
                "Due date: {$dueDate}\n\n" .
                "Payment ki taiyari kar lein.\n" .
                "ðŸ“ž 7007444842 ðŸ™�";
        }

        // Check if already sent today
        $alreadySent = $db->fetch(
            "SELECT id FROM whatsapp_followup_log 
             WHERE installment_id = ? AND followup_type = 'emi_reminder' AND DATE(sent_at) = CURDATE()",
            [$emi['installment_id']]
        );
        if ($alreadySent) continue;

        $result = $wa->sendMessage($phone, $message);
        $sent = !isset($result['error']);

        $db->execute(
            "INSERT INTO whatsapp_followup_log (installment_id, phone, followup_type, message, status, sent_at, created_at{$cronTenantCol})
             VALUES (?, ?, 'emi_reminder', ?, ?, NOW(), NOW(){$cronTenantVal})",
            [$emi['installment_id'], $phone, $message, $sent ? 'sent' : 'failed']
        );

        waLog(($sent ? "SENT" : "FAIL") . " EMI reminder to {$emi['customer_name']} (â‚¹{$amount}, {$daysLeft}d)");
    }

    // â”€â”€ 3. New registration welcome messages â”€â”€
    $newUsers = $db->fetchAll(
        "SELECT id, name, phone, role, created_at
         FROM users
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
           AND phone IS NOT NULL AND phone != ''
         ORDER BY created_at DESC
         LIMIT 10"
    );

    waLog("New user welcome messages: " . count($newUsers));

    foreach ($newUsers as $user) {
        $phone = preg_replace('/[^0-9]/', '', $user['phone']);
        if (strlen($phone) === 10) $phone = '91' . $phone;
        if (strlen($phone) < 12) continue;

        $name = $user['name'] ?: 'Customer';

        // Check if already sent
        $alreadySent = $db->fetch(
            "SELECT id FROM whatsapp_followup_log 
             WHERE user_id = ? AND followup_type = 'welcome' AND sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$user['id']]
        );
        if ($alreadySent) continue;

        $message = "ðŸŽ‰ Welcome to APS Dream Home, {$name}!\n\n" .
            "Aapka account successfully ban gaya hai.\n\n" .
            "Humse contact karein:\n" .
            "ðŸ“ž 7007444842\n" .
            "ðŸ“� Raghunath Nagri, Gorakhpur\n" .
            "ðŸŒ� apsdreamhome.com\n\n" .
            "Property dekhne ke liye reply karein! ðŸ� ";

        $result = $wa->sendMessage($phone, $message);
        $sent = !isset($result['error']);

        $db->execute(
            "INSERT INTO whatsapp_followup_log (user_id, phone, followup_type, message, status, sent_at, created_at{$cronTenantCol})
             VALUES (?, ?, 'welcome', ?, ?, NOW(), NOW(){$cronTenantVal})",
            [$user['id'], $phone, $message, $sent ? 'sent' : 'failed']
        );

        waLog(($sent ? "SENT" : "FAIL") . " welcome message to {$name} ({$phone})");
    }

    // Summary
    $todaySent = $db->fetch("SELECT COUNT(*) as c FROM whatsapp_followup_log WHERE DATE(sent_at) = CURDATE() AND status = 'sent'")['c'] ?? 0;
    $todayFailed = $db->fetch("SELECT COUNT(*) as c FROM whatsapp_followup_log WHERE DATE(sent_at) = CURDATE() AND status = 'failed'")['c'] ?? 0;

    waLog("=== Summary: {$todaySent} sent, {$todayFailed} failed today ===");

} catch (\Exception $e) {
    $errMsg = $e->getMessage();
    waLog("FATAL ERROR: {$errMsg}");
    exit(1);
}

waLog("=== WhatsApp Follow-up Processor Completed ===\n");
exit(0);?>