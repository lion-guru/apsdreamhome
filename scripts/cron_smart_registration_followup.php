<?php
/*
 * Cron Script: Smart Registration Followup
 *
 * Process abandoned registrations and send reminders via WhatsApp/Email/SMS
 *
 * Usage: php cron_smart_registration_followup.php
 * Schedule: every 6 hours
 */

// Set working directory
chdir(__DIR__);

// Include autoloader
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

use App\Services\RegistrationFollowupService;
use App\Core\Database\Database;

// Initialize
$followupService = new RegistrationFollowupService();

echo "=== Smart Registration Followup Cron ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// Process abandoned registrations
echo "Processing abandoned registrations...\n";
$results = $followupService->processAbandonedRegistrations();

// Display results
echo "\n--- Results ---\n";
echo "Total processed: " . ($results['processed'] ?? 0) . "\n";
echo "WhatsApp sent: " . ($results['whatsapp_sent'] ?? 0) . "\n";
echo "Email sent: " . ($results['email_sent'] ?? 0) . "\n";
echo "SMS sent: " . ($results['sms_sent'] ?? 0) . "\n";
echo "Failed: " . ($results['failed'] ?? 0) . "\n";

// Get stats
echo "\n--- Statistics (Last 30 Days) ---\n";
$stats = $followupService->getStats();
if ($stats) {
    echo "Total sessions: " . ($stats['total_sessions'] ?? 0) . "\n";
    echo "Pending OTP: " . ($stats['pending_otp'] ?? 0) . "\n";
    echo "OTP sent: " . ($stats['otp_sent'] ?? 0) . "\n";
    echo "OTP verified: " . ($stats['otp_verified'] ?? 0) . "\n";
    echo "Account created: " . ($stats['account_created'] ?? 0) . "\n";
    echo "Profile incomplete: " . ($stats['profile_incomplete'] ?? 0) . "\n";
    echo "Profile complete: " . ($stats['profile_complete'] ?? 0) . "\n";
    echo "Abandoned: " . ($stats['abandoned'] ?? 0) . "\n";
    echo "Avg completion: " . round($stats['avg_completion'] ?? 0, 1) . "%\n";
    echo "WhatsApp reminders sent: " . ($stats['whatsapp_reminders'] ?? 0) . "\n";
    echo "Email reminders sent: " . ($stats['email_reminders'] ?? 0) . "\n";
    echo "SMS reminders sent: " . ($stats['sms_reminders'] ?? 0) . "\n";
}

// Calculate conversion rate
$total = $stats['total_sessions'] ?? 0;
$completed = $stats['profile_complete'] ?? 0;
$conversionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
echo "\nConversion rate: {$conversionRate}%\n";

echo "\nCompleted: " . date('Y-m-d H:i:s') . "\n";
echo "=== End ===\n";
?>
