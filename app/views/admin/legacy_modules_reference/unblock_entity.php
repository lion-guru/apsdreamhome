<?php
// API to unblock an IP or user (admin only, POST)
require_once __DIR__ . '/core/init.php';

// API to unblock an IP or user (admin only, POST)
if (!isAdmin()) {
    http_response_code(403); exit('Forbidden');
}
// Email notification config
$admin_email = 'admin@apsdreamhomes.com'; // Updated to real admin email
$type = $_POST['type'] ?? '';
$value = $_POST['value'] ?? '';
$blocklist_file = __DIR__ . '/blocked_entities.json';
$blocked = ["ips"=>[],"users"=>[]];
if (file_exists($blocklist_file)) {
    $blocked = json_decode(file_get_contents($blocklist_file), true);
    if (!is_array($blocked)) $blocked = ["ips"=>[],"users"=>[]];
}
if ($type === 'ip' && $value) {
    $blocked['ips'] = array_values(array_diff($blocked['ips'], [$value]));
}
if ($type === 'user' && $value) {
    $blocked['users'] = array_values(array_diff($blocked['users'], [$value]));
}
// Log to audit file
$audit_log = __DIR__ . '/blocklist_audit.log';
$admin = $_SESSION['username'] ?? 'admin';
if (($type === 'ip' || $type === 'user') && $value) {
    $log_entry = date('Y-m-d H:i:s') . "\tUNBLOCK\t$type\t$value\t$admin\n";
    file_put_contents($audit_log, $log_entry, FILE_APPEND);
    // --- Notifications via NotificationManager ---
    require_once(dirname(__DIR__, 2) . '/includes/notification_manager.php');
    require_once(dirname(__DIR__, 2) . '/includes/email_service.php');

    try {
        $db = \App\Core\App::database();
        $emailService = new EmailService();
        $notificationManager = new NotificationManager($db, $emailService);

        $notificationManager->send([
            'email' => $admin_email,
            'template' => 'UNBLOCK_NOTIFICATION',
            'data' => [
                'entity_type' => $type,
                'entity_value' => $value,
                'admin_username' => $admin,
                'action_time' => date('Y-m-d H:i:s')
            ],
            'channels' => ['email', 'sms']
        ]);

        // Slack notification if enabled
        $cfg_file = __DIR__ . '/scheduled_report_config.json';
        if (file_exists($cfg_file)) {
            $cfg = json_decode(file_get_contents($cfg_file), true);
            if (!empty($cfg['slack_enabled']) && !empty($cfg['slack_webhook'])) {
                $slack_msg = "[APS Admin] UNBLOCKED $type: $value by $admin\nTime: ".date('Y-m-d H:i:s');
                $payload = json_encode(['text' => $slack_msg]);
                $ch = curl_init($cfg['slack_webhook']);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($payload)]);
                curl_exec($ch);
            }
        }
        $success = true;
    } catch (Exception $e) {
        error_log("Unblock Notification Error: " . $e->getMessage());
        $success = false;
    }
}
file_put_contents($blocklist_file, json_encode($blocked, JSON_PRETTY_PRINT));
echo json_encode(['success'=>$success,'blocked'=>$blocked]);

