<?php
// API to block an IP or user (admin only, POST)
require_once(__DIR__ . '/send_sms_twilio.php');
session_start();
// Email notification config
$admin_email = 'admin@apsdreamhomes.com'; // Updated to real admin email
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403); exit('Forbidden');
}
$type = $_POST['type'] ?? '';
$value = $_POST['value'] ?? '';
$blocklist_file = __DIR__ . '/blocked_entities.json';
$blocked = ["ips"=>[],"users"=>[]];
if (file_exists($blocklist_file)) {
    $blocked = json_decode(file_get_contents($blocklist_file), true);
    if (!is_array($blocked)) $blocked = ["ips"=>[],"users"=>[]];
}
if ($type === 'ip' && $value && !in_array($value, $blocked['ips'])) {
    $blocked['ips'][] = $value;
}
if ($type === 'user' && $value && !in_array($value, $blocked['users'])) {
    $blocked['users'][] = $value;
}
// Log to audit file
$audit_log = __DIR__ . '/blocklist_audit.log';
$admin = $_SESSION['username'] ?? 'admin';
if (($type === 'ip' || $type === 'user') && $value) {
    $log_entry = date('Y-m-d H:i:s') . "\tBLOCK\t$type\t$value\t$admin\n";
    file_put_contents($audit_log, $log_entry, FILE_APPEND);
    $subject = "[APS Admin] BLOCKED $type: $value";
    $body = "Action: BLOCK\nType: $type\nValue: $value\nAdmin: $admin\nTime: ".date('Y-m-d H:i:s');
    @mail($admin_email, $subject, $body);
    $success = true;
    // --- Slack notification integration ---
    function send_slack($msg) {
        $cfg_file = __DIR__ . '/scheduled_report_config.json';
        if (!file_exists($cfg_file)) return;
        $cfg = json_decode(file_get_contents($cfg_file), true);
        if (empty($cfg['slack_enabled']) || empty($cfg['slack_webhook'])) return;
        $payload = json_encode(['text' => $msg]);
        $ch = curl_init($cfg['slack_webhook']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($payload)]);
        curl_exec($ch);
        curl_close($ch);
    }
    $msg = "[APS Admin] BLOCKED $type: $value by $admin\nTime: ".date('Y-m-d H:i:s');
    if ($success) {
        send_slack($msg);
        // SMS alert
        $cfg_file = __DIR__ . '/scheduled_report_config.json';
        if (file_exists($cfg_file)) {
            $cfg = json_decode(file_get_contents($cfg_file), true);
            send_sms_twilio($msg, $cfg);
        }
    }
}
file_put_contents($blocklist_file, json_encode($blocked, JSON_PRETTY_PRINT));
echo json_encode(['success'=>true,'blocked'=>$blocked]);
?>
<html>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
