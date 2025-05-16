<?php
// auto_admin_report_cron.php: Generate and email a daily admin report (summary only, no email sending for demo)
require_once __DIR__ . '/../includes/db_config.php';
require_once __DIR__ . '/includes/geoip_utils.php';
$conn = getDbConnection();

// Get today's stats
$stats = $conn->query("SELECT COUNT(*) as total, SUM(status = 'New') as new, SUM(status = 'Qualified') as qualified, SUM(status = 'Contacted') as contacted, SUM(status = 'Converted') as converted FROM leads")->fetch_assoc();
$revenue = $conn->query("SELECT SUM(amount) as revenue FROM leads WHERE status = 'Converted' AND DATE(updated_at) = CURDATE()") ->fetch_assoc()['revenue'];

$report = "Leads Report for " . date('Y-m-d') . ":\n";
$report .= "Total: {$stats['total']}\nNew: {$stats['new']}\nQualified: {$stats['qualified']}\nContacted: {$stats['contacted']}\nConverted: {$stats['converted']}\n";
$report .= "Today's Revenue: " . ($revenue ?: 0) . "\n";

// Upload/Notification Audit Stats for Today
$audit = $conn->query("SELECT COUNT(*) as total, SUM(slack_status='sent') as slack_ok, SUM(slack_status!='sent') as slack_fail, SUM(telegram_status='sent') as telegram_ok, SUM(telegram_status!='sent') as telegram_fail FROM upload_audit_log WHERE DATE(created_at) = CURDATE()")->fetch_assoc();
$top_uploader = $conn->query("SELECT uploader, COUNT(*) as c FROM upload_audit_log WHERE DATE(created_at) = CURDATE() GROUP BY uploader ORDER BY c DESC LIMIT 1")->fetch_assoc();

$report .= "\nUploads Today: {$audit['total']}\n";
$report .= "Slack OK: {$audit['slack_ok']} | Slack Fail: {$audit['slack_fail']}\n";
$report .= "Telegram OK: {$audit['telegram_ok']} | Telegram Fail: {$audit['telegram_fail']}\n";
$report .= "Top Uploader: " . ($top_uploader['uploader'] ?? '-') . "\n";

// Forward upload audit log entries to SIEM
function forward_upload_audit_log_to_siem($row) {
    global $SIEM_ENDPOINT;
    if (!$SIEM_ENDPOINT) return;
    $ip = $row['ip_address'] ?? '';
    $geo = lookup_ip_geolocation($ip);
    $local_time = '';
    if (!empty($geo['timezone'])) {
        $dt = new DateTime('now', new DateTimeZone($geo['timezone']));
        $local_time = $dt->format('Y-m-d H:i:s');
    }
    $row['geo_city'] = $geo['city'] ?? '';
    $row['geo_region'] = $geo['region'] ?? '';
    $row['geo_country'] = $geo['country'] ?? '';
    $row['geo_timezone'] = $geo['timezone'] ?? '';
    $row['geo_lat'] = $geo['lat'] ?? '';
    $row['geo_lon'] = $geo['lon'] ?? '';
    $row['local_time'] = $local_time;
    $payload = json_encode($row);
    $ch = curl_init($SIEM_ENDPOINT);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Forward this day's upload audit log entries to SIEM (for daily/weekly/monthly report runs)
$today = date('Y-m-d');
$res = $conn->query("SELECT * FROM upload_audit_log WHERE DATE(created_at) = '$today'");
while ($row = $res && $res->num_rows ? $res->fetch_assoc() : false) {
    forward_upload_audit_log_to_siem($row);
}

// Send incident notification to webhook if configured
function notify_incident_webhook($payload) {
    global $INCIDENT_WEBHOOK_URL;
    if (!$INCIDENT_WEBHOOK_URL) return;
    $ch = curl_init($INCIDENT_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Incident webhook: alert if >5 failed notifications today
$failures = $conn->query("SELECT COUNT(*) as c FROM upload_audit_log WHERE (slack_status!='sent' OR telegram_status!='sent') AND DATE(created_at)=CURDATE()")->fetch_assoc();
if (($failures['c'] ?? 0) > 5) {
    $payload = [
        'event' => 'excessive_failed_notifications',
        'failures' => $failures['c'],
        'date' => date('Y-m-d'),
        'timestamp' => date('c'),
        'summary' => "There have been {$failures['c']} failed Slack/Telegram notifications today."
    ];
    notify_incident_webhook($payload);
}

// Weekly summary (run on Sundays)
if (date('w') == 0) {
    $weekStart = date('Y-m-d', strtotime('last monday', strtotime('tomorrow')));
    $weekEnd = date('Y-m-d');
    $audit = $conn->query("SELECT COUNT(*) as total, SUM(slack_status='sent') as slack_ok, SUM(slack_status!='sent') as slack_fail, SUM(telegram_status='sent') as telegram_ok, SUM(telegram_status!='sent') as telegram_fail FROM upload_audit_log WHERE created_at BETWEEN '$weekStart 00:00:00' AND '$weekEnd 23:59:59'")->fetch_assoc();
    $top_uploader = $conn->query("SELECT uploader, COUNT(*) as c FROM upload_audit_log WHERE created_at BETWEEN '$weekStart 00:00:00' AND '$weekEnd 23:59:59' GROUP BY uploader ORDER BY c DESC LIMIT 1")->fetch_assoc();
    $access = $conn->query("SELECT COUNT(*) as views, SUM(action='export') as exports, SUM(action='drilldown') as drilldowns FROM audit_access_log WHERE accessed_at BETWEEN '$weekStart 00:00:00' AND '$weekEnd 23:59:59'")->fetch_assoc();
    $report .= "\n--- Weekly Upload/Audit Summary ($weekStart to $weekEnd) ---\n";
    $report .= "Uploads: {$audit['total']}\nSlack OK: {$audit['slack_ok']} | Slack Fail: {$audit['slack_fail']}\nTelegram OK: {$audit['telegram_ok']} | Telegram Fail: {$audit['telegram_fail']}\n";
    $report .= "Top Uploader: " . ($top_uploader['uploader'] ?? '-') . "\n";
    $report .= "Audit Log Views: {$access['views']} | Exports: {$access['exports']} | Drilldowns: {$access['drilldowns']}\n";
}

// Monthly compliance summary (run on 1st of the month)
if (date('j') == 1) {
    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-d');
    $audit = $conn->query("SELECT COUNT(*) as total, SUM(slack_status='sent') as slack_ok, SUM(slack_status!='sent') as slack_fail, SUM(telegram_status='sent') as telegram_ok, SUM(telegram_status!='sent') as telegram_fail FROM upload_audit_log WHERE created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59'")->fetch_assoc();
    $top_uploader = $conn->query("SELECT uploader, COUNT(*) as c FROM upload_audit_log WHERE created_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59' GROUP BY uploader ORDER BY c DESC LIMIT 1")->fetch_assoc();
    $access = $conn->query("SELECT COUNT(*) as views, SUM(action='export') as exports, SUM(action='drilldown') as drilldowns FROM audit_access_log WHERE accessed_at BETWEEN '$monthStart 00:00:00' AND '$monthEnd 23:59:59'")->fetch_assoc();
    $monthly_report = "\n--- Monthly Compliance Summary ($monthStart to $monthEnd) ---\n";
    $monthly_report .= "Uploads: {$audit['total']}\nSlack OK: {$audit['slack_ok']} | Slack Fail: {$audit['slack_fail']}\nTelegram OK: {$audit['telegram_ok']} | Telegram Fail: {$audit['telegram_fail']}\n";
    $monthly_report .= "Top Uploader: " . ($top_uploader['uploader'] ?? '-') . "\n";
    $monthly_report .= "Audit Log Views: {$access['views']} | Exports: {$access['exports']} | Drilldowns: {$access['drilldowns']}\n";
    foreach ($admin_emails as $email) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apsdreamhoms44@gmail.com';
        $mail->Password = '128125@Aps';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('apsdreamhoms44@gmail.com', 'Dream Home Admin');
        $mail->addAddress($email);
        $mail->Subject = 'Monthly Compliance Summary - ' . $monthEnd;
        $mail->Body = $monthly_report;
        @$mail->send();
    }
}

// Log report to admin_reports table (could also email)
$stmt = $conn->prepare("INSERT INTO admin_reports (report_date, report_text) VALUES (CURDATE(), ?)");
$stmt->bind_param('s', $report);
$stmt->execute();

// Send daily report to admins via email
require_once __DIR__ . '/mail.php';
$admin_emails = ['techguruabhay@gmail.com']; // Add more admin emails as needed
foreach ($admin_emails as $email) {
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apsdreamhoms44@gmail.com';
    $mail->Password = '128125@Aps';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('apsdreamhoms44@gmail.com', 'Dream Home Admin');
    $mail->addAddress($email);
    $mail->Subject = 'Daily Admin Report - ' . date('Y-m-d');
    $mail->Body = $report;
    @$mail->send();
}

if (date('w') == 0) {
    foreach ($admin_emails as $email) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'apsdreamhoms44@gmail.com';
        $mail->Password = '128125@Aps';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('apsdreamhoms44@gmail.com', 'Dream Home Admin');
        $mail->addAddress($email);
        $mail->Subject = 'Weekly Admin Audit Summary - ' . date('Y-m-d');
        $mail->Body = $report;
        @$mail->send();
    }
}
?>
