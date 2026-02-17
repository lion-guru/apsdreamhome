<?php
// retry_failed_notifications.php: Retry failed Slack/Telegram notifications for uploads
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();
require_once __DIR__ . '/includes/integration_helpers.php';
require_once __DIR__ . '/includes/upload_audit_log.php';

// Find failed Slack notifications
$failed = $db->fetchAll("SELECT * FROM upload_audit_log WHERE (slack_status != 'sent' OR slack_status IS NULL OR slack_status = '') OR (telegram_status != 'sent' OR telegram_status IS NULL OR telegram_status = '') ORDER BY created_at DESC LIMIT 50");

foreach ($failed as $row) {
    $message = "[RETRY] Upload Event: {$row['event_type']}\n" .
        "Entity: {$row['entity_table']} #{$row['entity_id']}\n" .
        "File: {$row['file_name']}\n" .
        ($row['drive_file_id'] ? "Drive: https://drive.google.com/file/d/{$row['drive_file_id']}/view\n" : '') .
        "Uploader: {$row['uploader']}\n" .
        "Date: {$row['created_at']}";
    $slack_status = $row['slack_status'];
    $telegram_status = $row['telegram_status'];
    // Retry Slack
    if ($slack_status !== 'sent') {
        $slack_status = send_slack_notification($message) ? 'sent' : 'fail';
    }
    // Retry Telegram
    if ($telegram_status !== 'sent') {
        $telegram_status = send_telegram_notification($message) ? 'sent' : 'fail';
    }
    // Update audit log
    $db->execute("UPDATE upload_audit_log SET slack_status=:slack, telegram_status=:telegram WHERE id=:id", [
        'slack' => $slack_status,
        'telegram' => $telegram_status,
        'id' => $row['id']
    ]);
}
echo "Notification retry complete.";
