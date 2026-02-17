<?php
/**
 * Automated Admin Notification Cron
 * Notifies admins about important automatic events and system status
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

// Use Database singleton
$db = \App\Core\App::database();
$nm = new NotificationManager($db, new EmailService());

// Default admin ID to notify (usually the first super admin)
$admin_id = 1;

try {
    // 1. Notify about new auto-converted leads today
    $convertedCount = $db->fetchOne("
        SELECT COUNT(*) as count 
        FROM leads 
        WHERE status = 'Converted' 
        AND DATE(updated_at) = CURDATE() 
        AND notes LIKE '%[Auto] Converted%'
    ")['count'] ?? 0;

    if ($convertedCount > 0) {
        $nm->send([
            'user_id' => $admin_id,
            'template' => 'AUTO_SUMMARY',
            'data' => [
                'title' => 'Daily Auto-Conversion Summary',
                'message' => "$convertedCount lead(s) were automatically converted today."
            ],
            'channels' => ['db', 'email']
        ]);
    }

    // 2. Notify about new automatic follow-up reminders today
    $remindersCount = $db->fetchOne("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE type = 'AI_REMINDER' 
        AND DATE(created_at) = CURDATE()
    ")['count'] ?? 0;

    if ($remindersCount > 0) {
        $nm->send([
            'user_id' => $admin_id,
            'template' => 'AUTO_SUMMARY',
            'data' => [
                'title' => 'Daily AI Reminder Summary',
                'message' => "$remindersCount follow-up reminders were generated automatically today."
            ],
            'channels' => ['db', 'email']
        ]);
    }

    // 3. Check for any critical system errors in the last 24 hours
    $errorCount = $db->fetchOne("
        SELECT COUNT(*) as count 
        FROM system_logs 
        WHERE level = 'critical' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ")['count'] ?? 0;

    if ($errorCount > 0) {
        $nm->send([
            'user_id' => $admin_id,
            'template' => 'SYSTEM_ALERT',
            'data' => [
                'title' => 'Critical System Errors Detected',
                'message' => "There have been $errorCount critical system errors in the last 24 hours. Please check the system logs."
            ],
            'channels' => ['db', 'email']
        ]);
    }

    echo "Admin notifications processed successfully.\n";

} catch (Exception $e) {
    error_log("Auto Admin Notification Cron Error: " . $e->getMessage());
    echo "Error processing admin notifications: " . $e->getMessage() . "\n";
}
?>

