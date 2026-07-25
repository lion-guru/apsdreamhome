<?php
/**
 * Cron: Unified Communication Automation
 * 
 * Run daily at 9 AM IST:
 *   php cron/automation_messaging.php
 * 
 * Handles:
 *   1. Birthday greetings (leads with DOB today)
 *   2. Festival greetings (if today is a festival)
 *   3. Newsletter processing (weekly)
 *   4. Payment reminder chasers (overdue EMIs)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$startTime = microtime(true);
$logFile = __DIR__ . '/logs/automation_' . date('Y-m-d') . '.log';

// Ensure log directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function logMsg($msg) {
    global $logFile;
    $line = date('[Y-m-d H:i:s] ') . $msg . "\n";
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

logMsg("=== Communication Automation Cron Started ===");

// Bootstrap
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';

use App\Core\Database\Database;
use App\Services\Communication\CommunicationAutomationService;

$db = Database::getInstance();
$auto = new CommunicationAutomationService();

// ─── 1. Birthday & Festival Greetings ────────────────────────
logMsg("--- Birthday & Festival Greetings ---");
try {
    $greetingResults = $auto->sendAutomatedGreetings();
    logMsg("Greetings sent: {$greetingResults['sent']}, failed: {$greetingResults['failed']}");
    if (!empty($greetingResults['errors'])) {
        foreach ($greetingResults['errors'] as $err) {
            logMsg("  ERROR: $err");
        }
    }
} catch (\Throwable $e) {
    logMsg("Greetings ERROR: " . $e->getMessage());
}

// ─── 2. Newsletter Processing (weekly — only on Mondays) ──────
if (date('N') == 1) {
    logMsg("--- Newsletter Processing (Monday) ---");
    try {
        $newsletterResults = $auto->processNewsletterAutomation();
        logMsg("Newsletter sent: " . ($newsletterResults['sent'] ?? 0) . ", failed: " . ($newsletterResults['failed'] ?? 0));
    } catch (\Throwable $e) {
        logMsg("Newsletter ERROR: " . $e->getMessage());
    }
} else {
    logMsg("--- Newsletter: Skipped (not Monday) ---");
}

// ─── 3. Payment Reminder Chasers ─────────────────────────────
logMsg("--- Payment Reminder Chasers ---");
try {
    $pdo = $db->getConnection();
    
    // Find overdue EMI installments
    $overdue = $pdo->query("
        SELECT bps.id, bps.booking_id, bps.installment_number, bps.amount, bps.due_date,
               bps.accrued_penalty, bps.reminder_count,
               pb.customer_name, pb.customer_phone, pb.customer_email
        FROM booking_payment_schedules bps
        JOIN plot_bookings pb ON pb.id = bps.booking_id
        WHERE bps.status = 'pending' 
        AND bps.due_date < CURDATE()
        AND bps.reminder_count < 3
        ORDER BY bps.due_date ASC
        LIMIT 50
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $paymentSent = 0;
    $paymentFailed = 0;
    
    foreach ($overdue as $emi) {
        $daysOverdue = (int)((time() - strtotime($emi['due_date'])) / 86400);
        $message = "Dear {$emi['customer_name']}, your EMI installment #{$emi['installment_number']} of ₹" . number_format($emi['amount']) . " was due on " . date('d M Y', strtotime($emi['due_date'])) . ". {$daysOverdue} days overdue";
        
        if ($emi['accrued_penalty'] > 0) {
            $message .= ". Late fee: ₹" . number_format($emi['accrued_penalty']);
        }
        $message .= ". Please pay at the earliest to avoid further charges. - APS Dream Home";
        
        // Send via available channel
        $sent = false;
        if (!empty($emi['customer_phone'])) {
            try {
                $smsResult = $auto->sendMessage('sms', $emi['customer_phone'], $message);
                $sent = $smsResult['success'] ?? false;
            } catch (\Throwable $e) {
                // Try WhatsApp
                try {
                    $waResult = $auto->sendMessage('whatsapp', $emi['customer_phone'], $message);
                    $sent = $waResult['success'] ?? false;
                } catch (\Throwable $e2) {}
            }
        }
        
        // Update reminder count
        $pdo->prepare("UPDATE booking_payment_schedules SET reminder_count = reminder_count + 1 WHERE id = ?")
            ->execute([$emi['id']]);
        
        if ($sent) {
            $paymentSent++;
            logMsg("  Payment reminder sent: EMI #{$emi['installment_number']} for {$emi['customer_name']} ({$daysOverdue}d overdue)");
        } else {
            $paymentFailed++;
            logMsg("  Payment reminder FAILED: EMI #{$emi['installment_number']} for {$emi['customer_name']}");
        }
    }
    
    logMsg("Payment reminders sent: $paymentSent, failed: $paymentFailed");
    
} catch (\Throwable $e) {
    logMsg("Payment chaser ERROR: " . $e->getMessage());
}

// ─── Summary ──────────────────────────────────────────────────
$elapsed = round((microtime(true) - $startTime) * 1000);
logMsg("=== Completed in {$elapsed}ms ===");
