<?php
/**
 * AI Lead Reminder Cron - Automated Follow-up Alerts
 * This script should be scheduled to run daily or every few hours.
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

echo "Starting AI Lead Reminder Scan...\n";

// Configuration: High score threshold and inactive hours
$SCORE_THRESHOLD = 75;
$INACTIVE_HOURS = 24;

$db = \App\Core\App::database();
$nm = new NotificationManager($db->getConnection(), new EmailService());

try {
    // Find Hot Leads (Score > 75) that haven't been updated in 24 hours
    // And don't already have a pending reminder from today
    $sql = "SELECT id, name, ai_score, updated_at, assigned_to 
            FROM leads 
            WHERE ai_score >= ? 
            AND status NOT IN ('Converted', 'Lost')
            AND updated_at <= DATE_SUB(NOW(), INTERVAL ? HOUR)
            AND id NOT IN (
                SELECT user_id
                FROM notifications 
                WHERE type = 'AI_REMINDER' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            )";

    $hot_leads = $db->fetchAll($sql, [$SCORE_THRESHOLD, $INACTIVE_HOURS]);

    $reminder_count = 0;
    foreach ($hot_leads as $lead) {
        // Add notification for the assigned admin or general admin
        $admin_to_notify = $lead['assigned_to'] ?: 1; // Default to admin with ID 1 if unassigned
        
        $nm->send([
            'user_id' => $admin_to_notify,
            'template' => 'AI_REMINDER',
            'data' => [
                'name' => $lead['name'],
                'score' => $lead['ai_score'],
                'hours' => $INACTIVE_HOURS,
                'id' => $lead['id']
            ],
            'channels' => ['db', 'email']
        ]);
        
        echo "Created reminder for Lead #{$lead['id']}: {$lead['name']}\n";
        $reminder_count++;
    }

    echo "Scan complete. $reminder_count reminders generated.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

