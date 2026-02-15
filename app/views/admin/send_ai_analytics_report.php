<?php
/**
 * Automated AI Analytics Report Sender - Secured version
 */
// Since this is likely run via CRON, we might not have a session.
// core/init.php handles DB and other basics but also enforces session by default.
// However, we can use it and bypass session if needed, or just require the essentials.
// Let's use the essentials for CRON scripts to avoid redirects.

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/notification_manager.php';
require_once __DIR__ . '/../includes/email_service.php';

// Initialize Database singleton
$db = \App\Core\App::database();

// CONFIGURATION
$admin_emails = [
    'admin1@example.com', // Replace with real admin emails
];
$from = date('Y-m-d', strtotime('-7 days'));
$to = date('Y-m-d');

// Generate CSV
$filename = 'ai_analytics_report_' . $from . '_to_' . $to . '.csv';
$filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
$csv = fopen($filepath, 'w');
fputcsv($csv, ['id', 'user_id', 'role', 'action', 'suggestion_text', 'feedback', 'notes', 'created_at']);

$sql = "SELECT id, user_id, role, action, suggestion_text, feedback, notes, created_at 
        FROM ai_interactions 
        WHERE created_at >= ? AND created_at <= ? 
        ORDER BY created_at DESC";
$from_param = $from . ' 00:00:00';
$to_param = $to . ' 23:59:59';

$results = $db->fetchAll($sql, [$from_param, $to_param]);

foreach ($results as $row) {
    fputcsv($csv, $row);
}
fclose($csv);

try {
    $emailService = new EmailService();
    $notificationManager = new NotificationManager(null, $emailService);

    foreach ($admin_emails as $email) {
        $notificationManager->send([
            'email' => $email,
            'template' => 'AI_ANALYTICS_REPORT',
            'data' => [
                'from_date' => $from,
                'to_date' => $to
            ],
            'attachments' => [
                ['path' => $filepath, 'name' => $filename]
            ],
            'channels' => ['email']
        ]);
    }
    echo "Report sent to admins.";
} catch (Exception $e) {
    echo "Failed to send report: " . $e->getMessage();
} finally {
    if (file_exists($filepath)) unlink($filepath);
}
?>

